<?php
/**
 *
 * @package    mahara
 * @subpackage interaction-forum
 * @author     Lancaster University
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'engage/index');
define('MENUITEM_SUBPAGE', 'forums');
define('SECTION_PLUGINTYPE', 'interaction');
define('SECTION_PLUGINNAME', 'forum');
define('SECTION_PAGE', 'reportpost');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('interaction' ,'forum');
require_once('group.php');
require_once(get_config('docroot') . 'interaction/lib.php');
define('SUBSECTIONHEADING', get_string('nameplural', 'interaction.forum'));

$postid = param_integer('id');
$post = get_record_sql(
    'SELECT p.subject, p.body, p.topic, p.parent, p.poster, ' . db_format_tsfield('p.ctime', 'ctime') . ',
            m.user AS moderator, t.forum, p2.subject AS topicsubject, f.group, f.title AS forumtitle, g.name AS groupname
    FROM {interaction_forum_post} p
    INNER JOIN {interaction_forum_topic} t ON (p.topic = t.id AND t.deleted != 1)
    INNER JOIN {interaction_forum_post} p2 ON (p2.topic = t.id AND p2.parent IS NULL)
    INNER JOIN {interaction_instance} f ON (t.forum = f.id AND f.deleted != 1)
    INNER JOIN {group} g ON (g.id = f.group AND g.deleted = ?)
    LEFT JOIN (
        SELECT m.forum, m.user
        FROM {interaction_forum_moderator} m
        INNER JOIN {usr} u ON (m.user = u.id AND u.deleted = 0)
    ) m ON (m.forum = f.id AND m.user = p.poster)
    WHERE p.id = ?
    AND p.deleted != 1',
    array(0, $postid)
);

if (!$post) {
    throw new NotFoundException(get_string('cantfindpost', 'interaction.forum', $postid));
}
$attachments = array();
// Check if the post has any attachments
if ($postattachments = get_records_sql_array("
        SELECT a.*, aff.size, aff.fileid, pa.post
        FROM {artefact} a
        JOIN {interaction_forum_post_attachment} pa ON pa.attachment = a.id
        LEFT JOIN {artefact_file_files} aff ON aff.artefact = a.id
        WHERE pa.post = ?", array($postid))) {
    safe_require('artefact', 'file');
    foreach ($postattachments as $file) {
        $file->icon = call_static_method(generate_artefact_class_name($file->artefacttype), 'get_icon', array('id' => $file->id, 'post' => $postid));
    }
}
$post->attachments = $postattachments;
$membership = user_can_access_forum((int)$post->forum);
$moderator = (bool)($membership & INTERACTION_FORUM_MOD);

if (!$membership && !get_field('group', 'public', 'id', $post->group)) {
    throw new GroupAccessDeniedException(get_string('cantviewtopic', 'interaction.forum'));
}

define('GROUP', $post->group);

define('TITLE', $post->topicsubject . ' - ' . get_string('rejectpost', 'interaction.forum'));
//$post->ctime = relative_date(get_string('strftimerecentfullrelative', 'interaction.forum'), get_string('strftimerecentfull'), $post->ctime);

$form = pieform(array(
    'name'     => 'rejectpost',
    'autofocus' => false,
    'elements' => array(
        'message' => array(
            'type'  => 'textarea',
            'title' => get_string('reason'),
            'rows'  => 5,
            'cols'  => 80,
            'rules' => array('required' => true),
        ),
        'submit' => array(
            'type'  => 'submitcancel',
            'class' => 'btn-primary',
            'value' => array(get_string('notifyauthor', 'interaction.forum'), get_string('cancel')),
            'goto'  => get_config('wwwroot') . 'interaction/forum/topic.php?id=' . $post->topic . '&post=' . $postid
        ),
        'topic' => array(
            'type' => 'hidden',
            'value' => $post->topic
        ),
    )
));

function rejectpost_validate(Pieform $form, $values) {
    if (isset($values['message']) && strlen(trim($values['message'])) == 0) {
        $form->set_error('message', get_string('reasonempty', 'interaction.forum'));
    }
}

function rejectpost_submit(Pieform $form, $values) {
    global $SESSION, $USER, $postid;

    $postinfo = get_record_sql('
        SELECT ' . db_format_tsfield('p.ctime', 'ctime') . ', p.body, p.poster,
        f.id as forumid
        FROM {interaction_forum_post} p
        INNER JOIN {interaction_forum_topic} t ON p.topic = t.id
        INNER JOIN {interaction_instance} f ON (t.forum = f.id AND f.deleted = 0)
        WHERE p.id = ?',
        array($postid)
    );

    delete_records('interaction_forum_post_attachment', 'post', $postid);
    delete_records('interaction_forum_post', 'id', $postid);
    $redirecturl = get_config('wwwroot') . 'interaction/forum/topic.php?id=' . $values['topic'] ;

    // if it was the first post in the topic, then we need to delete the topic
    if (0 == count_records('interaction_forum_post', 'topic', $values['topic'])) {
        delete_records('interaction_forum_topic', 'id', $values['topic']);

        $redirecturl = get_config('wwwroot') . 'interaction/forum/view.php?id=' .  $postinfo->forumid;
    }
    // Trigger activity.
    $data = new stdClass();
    $data->topicid      = $values['topic'];
    $data->forumid      = $postinfo->forumid;
    $data->postbody     = $postinfo->body;
    $data->poster       = $postinfo->poster;
    $data->postedtime   = $postinfo->ctime;
    $data->reason       = $values['message'];
    $data->event        = POST_REJECTED;
    activity_occurred('postmoderation', $data, 'interaction', 'forum');

    $SESSION->add_ok_msg(get_string('rejectpostsuccess', 'interaction.forum'));
    redirect($redirecturl);
}

$poster = new User();
$poster->find_by_id($post->poster);

$smarty = smarty();
$smarty->assign('deleteduser', $poster->get('deleted'));
$smarty->assign('subheading', TITLE);
$smarty->assign('post', $post);
$smarty->assign('poster', $poster);
$smarty->assign('reportform', $form);
$smarty->assign('groupadmins', group_get_admin_ids($post->group));
$smarty->display('interaction:forum:reportpost.tpl');
