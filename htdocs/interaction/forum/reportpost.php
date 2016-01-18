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
define('MENUITEM', 'groups/forums');
define('SECTION_PLUGINTYPE', 'interaction');
define('SECTION_PLUGINNAME', 'forum');
define('SECTION_PAGE', 'reportpost');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('interaction' ,'forum');
require_once('group.php');
require_once(get_config('docroot') . 'interaction/lib.php');

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

$membership = user_can_access_forum((int)$post->forum);
$moderator = (bool)($membership & INTERACTION_FORUM_MOD);

if (!$membership && !get_field('group', 'public', 'id', $post->group)) {
    throw new GroupAccessDeniedException(get_string('cantviewtopic', 'interaction.forum'));
}

define('GROUP', $post->group);

define('TITLE', $post->topicsubject . ' - ' . get_string('reportpost', 'interaction.forum'));
$post->ctime = relative_date(get_string('strftimerecentfullrelative', 'interaction.forum'), get_string('strftimerecentfull'), $post->ctime);

$form = pieform(array(
    'name'     => 'reportpost',
    'autofocus' => false,
    'elements' => array(
        'message' => array(
            'type'  => 'textarea',
            'title' => get_string('complaint', 'interaction.forum'),
            'rows'  => 5,
            'cols'  => 80,
        ),
        'submit' => array(
            'type'  => 'submitcancel',
            'class' => 'btn-primary',
            'value' => array(get_string('notifyadministrator', 'interaction.forum'), get_string('cancel')),
            'goto'  => get_config('wwwroot') . 'interaction/forum/topic.php?id=' . $post->topic . '&post=' . $postid
        ),
        'topic' => array(
            'type' => 'hidden',
            'value' => $post->topic
        ),
    )
));


function reportpost_submit(Pieform $form, $values) {
    global $SESSION, $USER, $postid;
    $ctime = time();

    $objection = new stdClass();
    $objection->objecttype = 'forum';
    $objection->objectid   = $postid;
    $objection->reportedby = $USER->get('id');
    $objection->report = $values['message'];
    $objection->reportedtime = db_format_timestamp($ctime);
    insert_record('objectionable',  $objection);

    // Trigger activity.
    $data = new StdClass();
    $data->postid     = $postid;
    $data->message    = $values['message'];
    $data->reporter   = $USER->get('id');
    $data->ctime      = $ctime;
    $data->event      = REPORT_OBJECTIONABLE;
    activity_occurred('reportpost', $data, 'interaction', 'forum');

    $SESSION->add_ok_msg(get_string('reportpostsuccess', 'interaction.forum'));

    $redirecturl = get_config('wwwroot') . 'interaction/forum/topic.php?id=' . $values['topic'] . '&post=' . $postid;
    redirect($redirecturl);
}

$smarty = smarty();
$smarty->assign('subheading', TITLE);
$smarty->assign('post', $post);
$smarty->assign('reportform', $form);
$smarty->assign('groupadmins', group_get_admin_ids($post->group));
$smarty->display('interaction:forum:reportpost.tpl');
