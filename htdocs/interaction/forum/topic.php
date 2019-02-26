<?php
/**
 *
 * @package    mahara
 * @subpackage interaction-forum
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
define('MENUITEM', 'engage/index');
define('MENUITEM_SUBPAGE', 'forums');
define('SECTION_PLUGINTYPE', 'interaction');
define('SECTION_PLUGINNAME', 'forum');
define('SECTION_PAGE', 'topic');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('interaction', 'forum');
require_once('group.php');
require_once(get_config('docroot') . 'interaction/lib.php');
define('SUBSECTIONHEADING', get_string('nameplural', 'interaction.forum'));

$topicid = param_integer('id');

$topic = get_record_sql(
    'SELECT p.subject, p.poster, p.id AS firstpost, ' . db_format_tsfield('p.ctime', 'ctime') . ', t.id, f.group AS groupid, g.name AS groupname, f.id AS forumid, f.title AS forumtitle, t.closed, sf.forum AS forumsubscribed, st.topic AS topicsubscribed
    FROM {interaction_forum_topic} t
    INNER JOIN {interaction_instance} f ON (t.forum = f.id AND f.deleted != 1)
    INNER JOIN {group} g ON (g.id = f.group AND g.deleted = 0)
    INNER JOIN {interaction_forum_post} p ON (p.topic = t.id AND p.parent IS NULL)
    LEFT JOIN {interaction_forum_subscription_forum} sf ON (sf.forum = f.id AND sf.user = ?)
    LEFT JOIN {interaction_forum_subscription_topic} st ON (st.topic = t.id AND st.user = ?)
    WHERE t.id = ?
    AND t.deleted != 1',
    array($USER->get('id'), $USER->get('id'), $topicid)
);

if (!$topic) {
    throw new NotFoundException(get_string('cantfindtopic', 'interaction.forum', $topicid));
}

define('GROUP', $topic->groupid);

$group = get_group_by_id($topic->groupid, true);
$publicgroup = $group->public;
$ineditwindow = group_within_edit_window($group);
$feedlink = get_config('wwwroot') . 'interaction/forum/atom.php?type=t&id=' . $topic->id;

$membership = user_can_access_forum((int)$topic->forumid);
$moderator = $ineditwindow && (bool)($membership & INTERACTION_FORUM_MOD);

$forumconfig = get_records_assoc('interaction_forum_instance_config', 'forum', $topic->forumid, '', 'field,value');
$indentmode = isset($forumconfig['indentmode']) ? $forumconfig['indentmode']->value : 'full_indent';
$maxindentdepth = isset($forumconfig['maxindent']) ? $forumconfig['maxindent']->value : 10;

if (!$membership
    && !get_field('group', 'public', 'id', $topic->groupid)) {
    $objection = param_integer('objection', 0);
    $errorstr = ($objection) ? get_string('accessdeniedobjection', 'error') : get_string('cantviewtopic', 'interaction.forum');
    throw new GroupAccessDeniedException($errorstr, $objection);
}
$topic->canedit = ($moderator || user_can_edit_post($topic->poster, $topic->ctime)) && $ineditwindow;

define('TITLE', $topic->forumtitle . ' - ' . $topic->subject);

$groupadmins = group_get_admin_ids($topic->groupid);

if ($membership && !$topic->forumsubscribed) {
    $topic->subscribe = pieform(array(
        'name'     => 'subscribe_topic',
        'renderer' => 'div',
        'plugintype' => 'interaction',
        'pluginname' => 'forum',
        'class' => 'btn-group btn-group-top',
        'autofocus' => false,
        'elements' => array(
            'submit' => array(
               'type'  => 'button',
               'usebuttontag' => true,
               'class' => 'btn-secondary',
               'value' => $topic->topicsubscribed ? '<span class="icon icon-times icon-lg text-danger left" role="presentation" aria-hidden="true"></span>'. get_string('unsubscribefromtopic', 'interaction.forum') : '<span class="icon icon-star icon-lg left" role="presentation" aria-hidden="true"></span>' . get_string('subscribetotopic', 'interaction.forum'),
               'help' => false
            ),
            'topic' => array(
                'type' => 'hidden',
                'value' => $topicid
            ),
            'type' => array(
                'type' => 'hidden',
                'value' => $topic->topicsubscribed ? 'unsubscribe' : 'subscribe'
            )
        )
   ));
}
// posts pagination params
$offset = param_integer('offset', 0);
$limit  = param_integer('limit', 10);
$postid = param_integer('post', 0);


if (!empty($postid)) {
    // validates the $postid
    $post = get_record('interaction_forum_post', 'id', $postid, 'deleted', '0', null, null, 'id, path');
    if (!$post) {
        throw new NotFoundException("The post with ID '$postid' is not found or deleted!");
    }

    // post being approved
    $action = param_variable('action', null);
    if (isset($action) && $action == 'approve') {
        if (!$moderator && !$USER->get('admin')) {
            throw new GroupAccessDeniedException(get_string('cantapproveposts', 'interaction.forum'));
        }
        if (set_field_select('interaction_forum_post', 'approved', 1, 'id = ?', array($postid))) {
            $SESSION->add_ok_msg(get_string('postapprovesuccessful', 'interaction.forum'));
        }
        else {
            $SESSION->add_error_msg(get_string('postnotapprovederror', 'interaction.forum'), false);
        }
    }
    // caculated offset value to jump to the page of the post
    $offset = count_records_select('interaction_forum_post', 'topic = ? AND path < ?', array($topicid, $post->path));
    $offset = $offset - ($offset % $limit);
    redirect(get_config('wwwroot') . 'interaction/forum/topic.php?id=' . $topicid . '&offset=' . $offset . '&limit=' . $limit . '#post' . $postid);
}


$order = ($indentmode == 'no_indent') ? 'p.ctime, p.id' : 'p.path, p.ctime, p.id';

$sql = 'SELECT p.id, p.parent, p.path, p.poster, p.subject, p.body, ' . db_format_tsfield('p.ctime', 'ctime') . ', p.deleted, p.approved
FROM {interaction_forum_post} p
WHERE p.topic = ?';
$values = array($topicid);
if (!$moderator) {
    $sql .= ' AND (p.approved=1 OR p.poster= ?) ';
    $values[] = $USER->get('id');
}
$sql .= ' ORDER BY ' . $order;

$posts = get_records_sql_array(
    $sql,
    $values,
    $offset,
    $limit
);
if ($posts) {
    foreach ($posts as $post) {
        $post->filecount = 0;
        if ($post->attachments = get_records_sql_array("
                    SELECT a.*, aff.size, aff.fileid, pa.post
                    FROM {artefact} a
                    JOIN {interaction_forum_post_attachment} pa ON pa.attachment = a.id
                    LEFT JOIN {artefact_file_files} aff ON aff.artefact = a.id
                    WHERE pa.post = ?", array($post->id))) {
            $post->filecount = count($post->attachments);
            safe_require('artefact', 'file');
            foreach ($post->attachments as $file) {
                $file->icon = call_static_method(generate_artefact_class_name($file->artefacttype), 'get_icon', array('id' => $file->id, 'post' => $post->id));
            }
        }
    }
}

// This is only needed for the 'no_indent' option
$lastpostid = null;
if ($indentmode == 'no_indent') {
    $lastpost = get_record_select('interaction_forum_post', 'topic = ?  AND deleted != 1 ORDER by ctime DESC, id DESC LIMIT 1', array($topicid));
    $lastpostid = $lastpost->id;
}
// Get extra info of posts
$prevdeletedid = false;
if (is_array($posts) || is_object($posts)) {
    foreach ($posts as $postid => $post) {
        // Get the number of posts
        $post->postcount = get_postcount($post->poster);

        $post->canedit = $post->parent && ($moderator || user_can_edit_post($post->poster, $post->ctime)) && $ineditwindow;
        $post->ctime = relative_date(get_string('strftimerecentfullrelative', 'interaction.forum'), get_string('strftimerecentfull'), $post->ctime);
        // Get post edit records
        $post->edit = get_postedits($post->id);
        // Get moderator info
        $post->moderator = is_moderator($post->poster)? $post->poster : null;
        // Update the subject of posts
        $post->subject = !empty($post->subject) ? $post->subject : get_string('re', 'interaction.forum', get_ancestorpostsubject($post->id));
        // If this is the own post
        $post->ownpost = ($USER->get('id') == $post->poster) ? true : false;
        // Reported reason data
        $post->reports = get_records_select_array('objectionable',
                            'objecttype = ? AND objectid = ? AND resolvedby IS NULL AND resolvedtime IS NULL',
                            array('forum', $post->id));


        // Consolidate deleted message posts by the same author into one "X posts by Spammer Joe were deleted"
        if ($post->deleted) {
            if ($prevdeletedid && $posts[$prevdeletedid]->poster == $post->poster) {
                $posts[$prevdeletedid]->deletedcount++;
                unset($posts[$postid]);
            }
            else {
                $prevdeletedid = $postid;
                $post->deletedcount = 1;
            }
        }
        else {
            $prevdeletedid = false;
        }
    }
}

// If the user has internal notifications for this topic, mark them
// all as read.  Obviously there's no guarantee the user will actually
// read all the posts on this page, but better than letting the unread
// notifications grow too fast.  Unfortunately the only way to find
// notifications on this topic is to look for the url of this page.
execute_sql('
    UPDATE {notification_internal_activity}
    SET "read" = 1
    WHERE "read" = 0 AND usr = ? AND url LIKE ? || \'%\' AND type = (
        SELECT id FROM {activity_type} WHERE name = ?
    )',
    array(
        $USER->get('id'),
        get_config('wwwroot') . 'interaction/forum/topic.php?id=' . $topicid . '&post=',
        'newpost',
    )
);

// renders a page of posts
$posts = buildpostlist($posts, $indentmode, $maxindentdepth);
// adds posts pagination
$postcount = count_records_sql('SELECT COUNT(id) FROM {interaction_forum_post} WHERE topic = ?', array($topicid));
$pagination = build_pagination(array(
        'url' => get_config('wwwroot') . 'interaction/forum/topic.php?id=' . $topicid,
        'count' => $postcount,
        'limit' => $limit,
        'offset' => $offset,
));

$headers = array();
if ($publicgroup) {
    $headers[] = '<link rel="alternate" type="application/atom+xml" href="' . $feedlink . '">';
}


$smarty = smarty(array(), $headers);
$smarty->assign('topic', $topic);
$smarty->assign('membership', $membership);
$smarty->assign('moderator', $moderator);
$smarty->assign('lastpostid', $lastpostid);
$smarty->assign('posts', $posts);
$smarty->assign('pagination', $pagination['html']);
$smarty->display('interaction:forum:topic.tpl');

/*
 * Render a page of posts
 *
 * @param array $posts list of posts
 * @param string $mode ('no_indent', 'max_indent', 'full_indent')
 * @param int $max_depth the maximum depth to indent to
 */
function buildpostlist($posts, $mode, $max_depth) {
    switch ($mode) {
        case 'no_indent':
            $max_depth = 1;
            break;
        case 'max_indent':
            break;
        case 'full_indent':
        default:
            $max_depth = -1;
            break;
    }
    $html = '';
    if (is_array($posts) || is_object($posts)) {
        foreach ($posts as $post) {
            // calculates the indent tabs for the post
            $indent = ($max_depth == 1) ? 1 : count(explode('/', $post->path, $max_depth));
            $html .= renderpost($post, $indent, $mode);
        }
    }
    return $html;
}


/*
 * Renders a post
 *
 * @param object $post post object
 * @param int $indent indent value
 * @param char $mode the indenttion mode. Can be 'no_indent', 'max_indent', 'full_indent'
 * @return string html output
 */
function renderpost($post, $indent, $mode) {
    global $moderator, $topic, $groupadmins, $membership, $ineditwindow, $USER, $offset;
    $reportedaction = ($moderator && !empty($post->reports));
    $highlightreported = false;
    if ($reportedaction) {
        $highlightreported = true;
        $reportedreason = array();
        $objections = array();
        foreach ($post->reports as $report) {
            $reportedreason['msg_' . strtotime($report->reportedtime)] = array(
                'type' => 'html',
                'value' => get_string('reportedpostdetails', 'interaction.forum', display_default_name($report->reportedby),
                            strftime(get_string('strftimedaydatetime'), strtotime($report->reportedtime)), $report->report),
            );
            $objections[] = $report->id;
        }
        $post->postnotobjectionableform = pieform(array(
            'name'     => 'postnotobjectionable_' . $post->id,
            'validatecallback' => 'postnotobjectionable_validate',
            'successcallback'  => 'postnotobjectionable_submit',
            'renderer' => 'div',
            'class' => 'form-condensed',
            'plugintype' => 'interaction',
            'pluginname' => 'forum',
            'autofocus' => false,
            'elements' => array(
                'objection' => array(
                    'type' => 'hidden',
                    'value' => implode(',', $objections),
                ),
                'text' => array(
                    'type' => 'html',
                    'class' => 'postnotobjectionable',
                    'value' => get_string('postnotobjectionable', 'interaction.forum'),
                ),
                'submit' => array(
                   'type'  => 'submit',
                   'class' => 'btn-secondary',
                   'value' => get_string('postnotobjectionablesubmit', 'interaction.forum'),
                ),
                'postid' => array(
                    'type' => 'hidden',
                    'value' => $post->id,
                ),
                'details' => array(
                    'type'         => 'fieldset',
                    'class' => 'last',
                    'collapsible'  => true,
                    'collapsed'    => true,
                    'legend'       => get_string('reporteddetails', 'interaction.forum'),
                    'elements'     => $reportedreason,
                ),
            )
        ));
    }
    else if (!empty($post->reports)) {
        foreach ($post->reports as $report) {
            if ($report->reportedby == $USER->get('id')) {
                $highlightreported = true;
                break;
            }
        }
    }

    $poster = new User();
    $poster->find_by_id($post->poster);
    $smarty = smarty_core();
    $smarty->assign('LOGGEDIN', $USER->is_logged_in());
    $smarty->assign('post', $post);
    $smarty->assign('poster', $poster);
    $smarty->assign('deleteduser', $poster->get('deleted'));
    $smarty->assign('width', 100 - $indent*2);
    $smarty->assign('groupadmins', $groupadmins);
    $smarty->assign('moderator', $moderator);
    $smarty->assign('membership', $membership);
    $smarty->assign('chronological', ($mode == 'no_indent') ? true : false);
    $smarty->assign('closed', $topic->closed);
    $smarty->assign('ineditwindow', $ineditwindow);
    $smarty->assign('highlightreported', $highlightreported);
    $smarty->assign('reportedaction', $reportedaction);
    $smarty->assign('offset', $offset);
    $smarty->assign('topicid', $topic->id);
    return $smarty->fetch('interaction:forum:post.tpl');
}

function subscribe_topic_validate(Pieform $form, $values) {
    if (!is_logged_in()) {
        // This page is public, so the access denied exception will cause a
        // login attempt
        throw new AccessDeniedException();
    }
}

function subscribe_topic_submit(Pieform $form, $values) {
    global $USER;
    if ($values['type'] == 'subscribe') {
        insert_record(
            'interaction_forum_subscription_topic',
            (object)array(
                'topic' => $values['topic'],
                'user'  => $USER->get('id'),
                'key'   => PluginInteractionForum::generate_unsubscribe_key(),
            )
        );
    }
    else {
        delete_records(
            'interaction_forum_subscription_topic',
            'topic', $values['topic'],
            'user', $USER->get('id')
        );
    }
    redirect('/interaction/forum/topic.php?id=' . $values['topic']);
}

function postnotobjectionable_validate(Pieform $form, $values) {
    global $moderator;
    if (!$moderator) {
        throw new AccessDeniedException(get_string('cantmakenonobjectionable', 'interaction.forum'));
    }
}

function postnotobjectionable_submit(Pieform $form, $values) {
    global $SESSION, $USER, $topicid;

    db_begin();

    $objections = explode(',', $values['objection']);

    // Mark records as resolved.
    foreach ($objections as $objection) {
        $todb = new stdClass();
        $todb->resolvedby = $USER->get('id');
        $todb->resolvedtime = db_format_timestamp(time());

        update_record('objectionable', $todb, array('id' => $objection));
    }

    // Trigger activity.
    $data = new stdClass();
    $data->postid     = $values['postid'];
    $data->message    = '';
    $data->reporter   = $USER->get('id');
    $data->ctime      = time();
    $data->event      = MAKE_NOT_OBJECTIONABLE;
    activity_occurred('reportpost', $data, 'interaction', 'forum');

    db_commit();

    $SESSION->add_ok_msg(get_string('postnotobjectionablesuccess', 'interaction.forum'));

    $redirecturl = get_config('wwwroot') . 'interaction/forum/topic.php?id=' . $topicid . '&post=' . $values['postid'];
    redirect($redirecturl);
}

/* Return the number of posts submitted by a poster
 *
 * @param int $posterid ID of the poster
 * @return int the number of posts
 */
function get_postcount($posterid) {
    return get_string('postsvariable', 'interaction.forum', count_records_sql(
       'SELECT COUNT(id)
        FROM {interaction_forum_post}
        WHERE deleted != 1 AND poster = ?', array($posterid)));
}

/* Return the edit records of a post
 *
 * @param int $postid ID of the post
 * @return array the edit records
 */
function get_postedits($postid) {
    ($postedits = get_records_sql_array(
       'SELECT ' . db_format_tsfield('e.ctime', 'edittime') . ', e.user AS editor, m2.user AS editormoderator, us.deleted
        FROM {interaction_forum_edit} e
        LEFT JOIN {interaction_forum_post} p ON p.id = e.post
        LEFT JOIN {interaction_forum_topic} t ON t.id = p.topic
        LEFT JOIN (
            SELECT m.forum, m.user
            FROM {interaction_forum_moderator} m
            INNER JOIN {usr} u ON (m.user = u.id AND u.deleted = 0)
        ) m2 ON (m2.forum = t.forum AND m2.user = e.user)
        LEFT JOIN {usr} us ON us.id = e.user
        WHERE e.post = ?
        ORDER BY e.ctime',
        array($postid)
    )) || ($postedits = array());
    $editrecs = array();
    foreach ($postedits as $postedit) {
        $postedit->edittime = relative_date(get_string('strftimerecentfullrelative', 'interaction.forum'), get_string('strftimerecentfull'), $postedit->edittime);
        $editrecs[] = array(
            'editormoderator' => $postedit->editormoderator,
            'editor' => $postedit->editor,
            'edittime' => $postedit->edittime,
            'deleteduser' => $postedit->deleted,
        );
    }
    return $editrecs;
}

/* Check if the poster is the moderator of the forum in which the post is
 *
 * @param int $postid ID of the post
 * @return true if yes, false if else
 */
function is_moderator($postid) {
    return (count_records_sql(
       'SELECT COUNT(m.user)
        FROM {interaction_forum_moderator} m
        INNER JOIN {usr} u ON (m.user = u.id AND u.deleted = 0)
        INNER JOIN {interaction_instance} f ON (m.forum = f.id AND f.deleted != 1)
        INNER JOIN {interaction_forum_topic} t ON (t.forum = f.id)
        INNER JOIN {interaction_forum_post} p ON (p.topic = t.id AND p.poster = m.user)
        WHERE p.id = ?',
        array($postid)) == 1);
}
