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
define('MENUITEM', 'engage/index');
define('MENUITEM_SUBPAGE', 'forums');
define('SECTION_PLUGINTYPE', 'interaction');
define('SECTION_PLUGINNAME', 'forum');
define('SECTION_PAGE', 'deletepost');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('interaction' ,'forum');
require_once('group.php');
require_once(get_config('docroot') . 'interaction/lib.php');
define('SUBSECTIONHEADING', get_string('nameplural', 'interaction.forum'));

$postid = param_integer('id');
$post = get_record_sql(
    'SELECT p.subject, p.body, p.topic, p.parent, p.poster, p.approved, ' . db_format_tsfield('p.ctime', 'ctime') . ', m.user AS moderator, t.forum, p2.subject AS topicsubject, f.group, f.title AS forumtitle, g.name AS groupname, COUNT(p3.id)
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
    INNER JOIN {interaction_forum_post} p3 ON (p.poster = p3.poster AND p3.deleted != 1)
    INNER JOIN {interaction_forum_topic} t2 ON (t2.deleted != 1 AND p3.topic = t2.id)
    INNER JOIN {interaction_instance} f2 ON (t2.forum = f2.id AND f2.deleted != 1 AND f2.group = f.group)
    WHERE p.id = ?
    AND p.deleted != 1
    GROUP BY 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13',
    array(0, $postid)
);

if (!$post) {
    throw new NotFoundException(get_string('cantfindpost', 'interaction.forum', $postid));
}

$membership = user_can_access_forum((int)$post->forum);
$moderator = (bool)($membership & INTERACTION_FORUM_MOD);

if (!$moderator || ($post->group && !group_within_edit_window($post->group))) {
    throw new AccessDeniedException(get_string('cantdeletepost', 'interaction.forum'));
}

if (!$post->parent) {
    throw new AccessDeniedException(get_string('cantdeletethispost', 'interaction.forum'));
}

define('GROUP', $post->group);

define('TITLE', $post->topicsubject . ' - ' . get_string('deletepost', 'interaction.forum'));
$post->ctime = relative_date(get_string('strftimerecentfullrelative', 'interaction.forum'), get_string('strftimerecentfull'), $post->ctime);

$form = pieform(array(
    'name'     => 'deletepost',
    'renderer' => 'div',
    'autofocus' => false,
    'elements' => array(
        'title' => array(
            'value' => get_string('deletepostsure', 'interaction.forum'),
        ),
        'submit' => array(
            'type'  => 'submitcancel',
            'class' => 'btn-secondary',
            'value' => array(get_string('yes'), get_string('no')),
            'goto'  => get_config('wwwroot') . 'interaction/forum/topic.php?id=' . $post->topic . '&post=' . $postid
        ),
        'post' => array(
            'type' => 'hidden',
            'value' => $postid
        ),
        'topic' => array(
            'type' => 'hidden',
            'value' => $post->topic
        ),
        'parent' => array(
            'type' => 'hidden',
            'value' => $post->parent
        )
    )
));

function deletepost_submit(Pieform $form, $values) {
    global $SESSION, $USER;

    $postid = $values['post'];
    $objectionable = get_record_sql("SELECT fp.id
            FROM {interaction_forum_post} fp
            JOIN {objectionable} o
            ON (o.objecttype = 'forum' AND o.objectid = fp.id)
            WHERE fp.id = ?
            AND o.resolvedby IS NULL
            AND o.resolvedtime IS NULL", array($postid));

    if ($objectionable !== false) {
        // Trigger activity.
        $data = new stdClass();
        $data->postid     = $postid;
        $data->message    = '';
        $data->reporter   = $USER->get('id');
        $data->ctime      = time();
        $data->event      = DELETE_OBJECTIONABLE_POST;
        activity_occurred('reportpost', $data, 'interaction', 'forum');
    }

    update_record(
        'interaction_forum_post',
        array('deleted' => 1),
        array('id' => $postid)
    );
    // Delete embedded images in the forum post description
    require_once('embeddedimage.php');
    EmbeddedImage::delete_embedded_images('post', $postid);
    delete_records('interaction_forum_post_attachment', 'post', $postid);

    $SESSION->add_ok_msg(get_string('deletepostsuccess', 'interaction.forum'));
    // Figure out which parent record to redirect us to. If the parent record is deleted,
    // keep moving up the chain until you find one that's not deleted.
    $postrec = new stdClass();
    $postrec->parent = $values['parent'];
    do {
        $postrec = get_record('interaction_forum_post', 'id', $postrec->parent, null, null, null, null, 'id, deleted, parent');
    } while ($postrec && $postrec->deleted && $postrec->parent);
    $redirecturl = get_config('wwwroot') . 'interaction/forum/topic.php?id=' . $values['topic'];
    if ($postrec && $postrec->parent) {
        $redirecturl .= '&post=' . $postrec->id;
    }
    redirect($redirecturl);
}
$poster = new User();
$poster->find_by_id($post->poster);

$smarty = smarty();
$smarty->assign('subheading', TITLE);
$smarty->assign('post', $post);
$smarty->assign('deleteduser', $poster->get('deleted'));
$smarty->assign('poster', $poster);
$smarty->assign('deleteform', $form);
$smarty->assign('groupadmins', group_get_admin_ids($post->group));
$smarty->display('interaction:forum:deletepost.tpl');
