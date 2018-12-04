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
define('SECTION_PAGE', 'editpost');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('interaction', 'forum');
require_once('group.php');
require_once(get_config('docroot') . 'interaction/lib.php');
require_once('antispam.php');
safe_require('artefact', 'file');
define('SUBSECTIONHEADING', get_string('nameplural', 'interaction.forum'));

$postid = param_integer('id', 0);
$page = get_config('wwwroot') . 'interaction/forum/editpost.php';
if ($postid == 0) { // post reply
    unset($postid);
    $parentid = param_integer('parent');
    $page .= '?parent=' . $parentid;
}
else { // edit post
    $post = get_record_sql(
        'SELECT p.subject, p.body, p.parent, p.topic, p.poster, ' . db_format_tsfield('p.ctime', 'ctime') . ', p.sent
        FROM {interaction_forum_post} p
        WHERE p.id = ?
        AND p.deleted != 1
        AND p.parent IS NOT NULL',
        array($postid)
    );
    if (!$post) {
        throw new NotFoundException(get_string('cantfindpost', 'interaction.forum', $postid));
    }
    $parentid = $post->parent;
    $page .= '?id=' . $postid;
}

if (!$parentid) {
    throw new NotFoundException(get_string('cantfindpost', 'interaction.forum', $parentid));
}

$parent = get_record_sql(
    'SELECT p.subject, p.body, p.topic, p.parent, p.poster, p.approved, p.deleted, ' . db_format_tsfield('p.ctime', 'ctime') . ', m.user AS moderator, t.id AS topic, t.forum, t.closed AS topicclosed, p2.subject AS topicsubject, f.group AS "group", f.title AS forumtitle, g.name AS groupname, COUNT(p3.id)
    FROM {interaction_forum_post} p
    INNER JOIN {interaction_forum_topic} t ON (p.topic = t.id AND t.deleted != 1)
    INNER JOIN {interaction_forum_post} p2 ON (p2.topic = t.id AND p2.parent IS NULL)
    INNER JOIN {interaction_instance} f ON (t.forum = f.id AND f.deleted != 1)
    LEFT JOIN (
        SELECT m.forum, m.user
        FROM {interaction_forum_moderator} m
        INNER JOIN {usr} u ON (m.user = u.id AND u.deleted = 0)
    ) m ON (m.forum = f.id AND m.user = p.poster)
    INNER JOIN {group} g ON (g.id = f.group AND g.deleted = ?)
    INNER JOIN {interaction_forum_post} p3 ON (p.poster = p3.poster)
    INNER JOIN {interaction_forum_topic} t2 ON (t2.deleted != 1 AND p3.topic = t2.id)
    INNER JOIN {interaction_instance} f2 ON (t2.forum = f2.id AND f2.deleted != 1 AND f2.group = f.group)
    WHERE p.id = ?
    GROUP BY 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16',
    array(0, $parentid)
);

if ($parent) {
    $parent->filecount = 0;
    if ($parent->attachments = get_records_sql_array("
                  SELECT a.*, aff.size, aff.fileid, pa.post
                  FROM {artefact} a
                  JOIN {interaction_forum_post_attachment} pa ON pa.attachment = a.id
                  LEFT JOIN {artefact_file_files} aff ON aff.artefact = a.id
                  WHERE pa.post = ?", array($parentid))) {
        $parent->filecount = count($parent->attachments);
        safe_require('artefact', 'file');
        foreach ($parent->attachments as $file) {
            $file->icon = call_static_method(generate_artefact_class_name($file->artefacttype), 'get_icon', array('id' => $file->id, 'post' => $parentid));
        }
    }
}
define('GROUP', $parent->group);

$membership = user_can_access_forum((int)$parent->forum);
$moderator = (bool)($membership & INTERACTION_FORUM_MOD);
$admintutor = (bool) group_get_user_admintutor_groups();
$poster = new User();

if (!isset($postid)) { // post reply
    if ($parent->deleted) {
        throw new NotFoundException(get_string('cantfindpost', 'interaction.forum', $parentid));
    }
    if (!group_within_edit_window($parent->group)) {
        throw new AccessDeniedException(get_string('cantaddposttoforum', 'interaction.forum'));
    }
    if (!$membership) {
        throw new AccessDeniedException(get_string('cantaddposttoforum', 'interaction.forum'));
    }
    if (!$moderator && $parent->topicclosed) {
        throw new AccessDeniedException(get_string('cantaddposttotopic', 'interaction.forum'));
    }
    $action = get_string('postreply', 'interaction.forum');
    define('TITLE', $parent->topicsubject . ' - ' . $action);
    $poster->find_by_id($parent->poster);
}
else { // edit post
    if (!group_within_edit_window($parent->group)) {
        throw new AccessDeniedException(get_string('canteditpost', 'interaction.forum'));
    }
    // no record for edits to own posts with 30 minutes
    if (user_can_edit_post($post->poster, $post->ctime)) {
        $post->editrecord = false;
        $timeleft = ceil(get_config_plugin('interaction', 'forum', 'postdelay') - (time() - $post->ctime) / 60);
    }
    else if ($moderator) {
        $post->editrecord = true;
    }
    else if (user_can_edit_post($post->poster, $post->ctime, $USER->get('id'), false)) {
        $SESSION->add_error_msg(get_string('postaftertimeout', 'interaction.forum', get_config_plugin('interaction', 'forum', 'postdelay')));
        redirect('/interaction/forum/topic.php?id=' . $parent->topic);
    }
    else {
        throw new AccessDeniedException(get_string('canteditpost', 'interaction.forum'));
    }
    $action = get_string('editpost', 'interaction.forum');
    define('TITLE', $parent->topicsubject . ' - ' . $action);
    $poster->find_by_id($post->poster);
}

$parent->ctime = relative_date(get_string('strftimerecentfullrelative', 'interaction.forum'), get_string('strftimerecentfull'), $parent->ctime);

$folder = param_integer('folder', 0);
$browse = (int) param_variable('browse', 0);
$highlight = null;
if ($file = param_integer('file', 0)) {
    $highlight = array($file);
}

$instance = new InteractionForumInstance($parent->forum);
$currenttab = array('type' => 'user', 'id' => $USER->get('id'));
$mailsent = (isset($post) && !empty($post->sent)) ? true : false;
$editform = array(
    'name'     => 'editpost',
    'successcallback' => isset($post) ? 'editpost_submit' : 'addpost_submit',
    'method'            => 'post',
    'jsform'            => true,
    'newiframeonsubmit' => true,
    'jssuccesscallback' => 'editpost_callback',
    'jserrorcallback'   => 'editpost_callback',
    'plugintype'        => 'interaction',
    'pluginname'        => 'forum',
    'configdirs'        => array(get_config('libroot') . 'form/', get_config('docroot') . 'artefact/file/form/'),
    'elements' => array(
        'subject' => array(
            'type'         => 'text',
            'title'        => get_string('Subject', 'interaction.forum'),
            'defaultvalue' => isset($post) ? $post->subject : null,
            'rules'        => array(
                'maxlength' => 255
            ),
            'hidewhenempty' => true,
            'expandtext'    => get_string('clicksetsubject', 'interaction.forum'),
        ),
        'body' => array(
            'type'         => 'wysiwyg',
            'title'        => get_string('Body', 'interaction.forum'),
            'rows'         => 18,
            'cols'         => 70,
            'defaultvalue' => isset($post) ? $post->body : null,
            'rules'        => array(
                'required'  => true,
                'maxlength' => 1000000,
            ),
        ),
        'filebrowser' => array(
            'type'         => 'filebrowser',
            'title'        => get_string('attachments', 'artefact.blog'),
            'folder'       => $folder,
            'highlight'    => $highlight,
            'browse'       => $browse,
            'page'         => $page . '&browse=1',
            'browsehelp'   => 'browsemyfiles',
            'config'       => array(
                'upload'          => true,
                'uploadplaces'    => array('user', 'group'),
                'uploadagreement' => get_config_plugin('artefact', 'file', 'uploadagreement'),
                'resizeonuploaduseroption' => get_config_plugin('artefact', 'file', 'resizeonuploaduseroption'),
                'resizeonuploaduserdefault' => $USER->get_account_preference('resizeonuploaduserdefault'),
                'createfolder'    => false,
                'edit'            => true,
                'noselect'        => $mailsent,
                'select'          => true,
                'alwaysopen'      => false,
            ),
            'defaultvalue'       => $instance->attachment_id_list(isset($postid) ? $postid : 0),
            'selectlistcallback' => 'artefact_get_records_by_id',
            'selectcallback'     => 'add_attachment',
            'unselectcallback'   => 'delete_attachment',
            'tabs'               => $currenttab,
        ),
        'sendnow' => array(
            'type'         => 'switchbox',
            'title'        => get_string('sendnow', 'interaction.forum'),
            'description'  => get_string('sendnowdescription', 'interaction.forum', get_config_plugin('interaction', 'forum', 'postdelay')),
            'disabled'     => isset($post) && !empty($post->sent),
            'defaultvalue' => false,
        ),
        'submitpost'   => array(
            'type'  => 'submitcancel',
            'class' => 'btn-primary',
            'value'       => array(
                isset($post) ? get_string('save') : get_string('Post','interaction.forum'),
                get_string('cancel')
            ),
            'goto'      => get_config('wwwroot') . 'interaction/forum/topic.php?id=' . $parent->topic . '&post=' . (isset($postid) ? $postid : $parentid)
        ),
        'topic' => array(
            'type' => 'hidden',
            'value' => $parent->topic
        ),
        'editrecord' => array(
            'type' => 'hidden',
            'value' => isset($post) ? $post->editrecord : false
        )
    ),
);

if ((!$moderator && !$admintutor && !group_sendnow($parent->group)) || get_config_plugin('interaction', 'forum', 'postdelay') <= 0) {
    unset($editform['elements']['sendnow']);
}

if (isset($post) && (!empty($post->sent) || !user_can_edit_post($post->poster, $post->ctime))) {
    unset($editform['elements']['sendnow']);
}

$editform = pieform($editform);

function editpost_validate(Pieform $form, $values) {
    if ($baddomain = get_first_blacklisted_domain($values['body'])) {
        $form->set_error('body', get_string('blacklisteddomaininurl', 'mahara', $baddomain));
    }
    $result = probation_validate_content($values['body']);
    if ($result !== true) {
        $form->set_error('body', get_string('newuserscantpostlinksorimages1'));
    }
}

function get_groupid_from_postid($postid) {
    $groupid = get_field_sql("SELECT i.group FROM {interaction_instance} i
                              INNER JOIN {interaction_forum_topic} t ON i.id = t.forum
                              INNER JOIN {interaction_forum_post} p on p.topic = t.id
                              WHERE p.id =?", array($postid));
    return $groupid;
}

function reply_needs_approval($topicid) {
    $needsapproval = get_field_sql("SELECT c.value FROM {interaction_forum_instance_config} c
                              INNER JOIN {interaction_forum_topic} t
                              ON t.forum = c.forum WHERE field = 'moderateposts' AND t.id = ?", array($topicid));
    return ($needsapproval == 'replies' || $needsapproval == 'postsandreplies');
}

function is_logged_user_moderator($topicid) {
    global $USER;
    return (count_records_sql(
       'SELECT COUNT(*)
        FROM {interaction_forum_moderator} m
        INNER JOIN {interaction_instance} f ON (m.forum = f.id AND f.deleted != 1)
        INNER JOIN {interaction_forum_topic} t ON (t.forum = f.id)
        WHERE t.id = ? AND m.user = ?',
        array($topicid, $USER->get('id'))) != 0 );
}

function editpost_submit(Pieform $form, $values) {
    global $USER, $SESSION, $parent;
    require_once('embeddedimage.php');
    $postid = param_integer('id');
    $mailsent = get_field('interaction_forum_post', 'sent', 'id', $postid);
    $groupid = get_groupid_from_postid($postid);
    $newbody = EmbeddedImage::prepare_embedded_images($values['body'], 'post', $postid, $groupid);
    db_begin();
    update_record(
        'interaction_forum_post',
        array(
            'subject' => $values['subject'],
            'body' => PluginInteractionForum::prepare_post_body($newbody, $postid),
        ),
        array('id' => $postid)
    );
    if ($values['editrecord']) {
        insert_record(
            'interaction_forum_edit',
            (object)array(
                'user' => $USER->get('id'),
                'post' => $postid,
                'ctime' => db_format_timestamp(time())
            )
        );
    }
    // Attachments
    $instance = new InteractionForumInstance($parent->forum);
    update_attachments($instance, $values['filebrowser'], $postid, $mailsent);

    db_commit();

    $result = array(
        'error'   => false,
        'message' => get_string('editpostsuccess', 'interaction.forum'),
        'goto'    => get_config('wwwroot') . 'interaction/forum/topic.php?id=' . $values['topic'] . '&post=' . $postid,
    );
    if ($form->submitted_by_js()) {
        // Redirect back to the note page from within the iframe
        $SESSION->add_ok_msg($result['message']);
        $form->json_reply(PIEFORM_OK, $result, false);
    }
    $form->reply(PIEFORM_OK, $result);
}

function addpost_submit(Pieform $form, $values) {
    global $USER, $SESSION;
    require_once('embeddedimage.php');
    $parentid = param_integer('parent');
    $post = (object)array(
        'topic'   => $values['topic'],
        'poster'  => $USER->get('id'),
        'parent'  => $parentid,
        'subject' => $values['subject'],
        'body'    => $values['body'],
        'ctime'   =>  db_format_timestamp(time())
    );

    if (reply_needs_approval($values['topic']) && !is_logged_user_moderator($values['topic']) && !$USER->get('admin')) {
        $post->approved = 0;
    }

    $sendnow = isset($values['sendnow']) && $values['sendnow'] ? 1 : 0;
    // See if the same content has been submitted in the last 5 seconds. If so, don't add this post.
    $oldpost = get_record_select('interaction_forum_post', 'topic = ? AND poster = ? AND parent = ? AND subject = ? AND body = ? AND ctime > ?',
        array($post->topic, $post->poster, $post->parent, $post->subject, $post->body, db_format_timestamp(time() - 5)),
        'id');
    if ($oldpost) {
        redirect(get_config('wwwroot') . 'interaction/forum/topic.php?id=' . $values['topic'] . '&post=' . $oldpost->id);
    }
    $postrec = new stdClass();
    $postid = $postrec->id = insert_record('interaction_forum_post', $post, 'id', true);
    $postrec->path = get_field('interaction_forum_post', 'path', 'id', $parentid) . '/' . sprintf('%010d', $postrec->id);
    update_record('interaction_forum_post', $postrec);

    if (isset($post->approved) && $post->approved == 0) {
        $forumid = get_field('interaction_forum_topic', 'forum', 'id', $values['topic']);
           // Trigger activity.
        $data = new stdClass();
        $data->topicid      = $values['topic'];
        $data->forumid      = $forumid;
        $data->postbody     = $values['body'];
        $data->poster       = $USER->get('id');
        $data->postedtime   = time();
        $data->reason       = '';
        $data->event        = POST_NEEDS_APPROVAL;
        activity_occurred('postmoderation', $data, 'interaction', 'forum');
    }

    // Rewrite the post id into links in the body
    $groupid = get_groupid_from_postid($postid);
    $newbody = EmbeddedImage::prepare_embedded_images($post->body, 'post', $postid, $groupid);
    $newbody = PluginInteractionForum::prepare_post_body($newbody, $postid);
    if (!empty($newbody) && $newbody != $post->body) {
        set_field('interaction_forum_post', 'body', $newbody, 'id', $postid);
    }
    // Attachments
    $forumid = get_field('interaction_forum_topic', 'forum', 'id', $post->topic);
    $instance = new InteractionForumInstance($forumid);
    update_attachments($instance, $values['filebrowser'], $postid);

    if ($sendnow == 0) {
      $delay = get_config_plugin('interaction', 'forum', 'postdelay');
    }
    else {
      $delay = 0;
    }
    if (!is_null($delay) && $delay == 0) {
        PluginInteractionForum::interaction_forum_new_post(array($postid));
    }

    if (is_using_probation() && $post->parent) {
        $parentposter = get_field('interaction_forum_post', 'poster', 'id', $post->parent);
        vouch_for_probationary_user($parentposter);
    }

    $result = array(
        'error'   => false,
        'message' => get_string('addpostsuccess', 'interaction.forum'),
        'goto'    => get_config('wwwroot') . 'interaction/forum/topic.php?id=' . $values['topic'] . '&post=' . $postid,
    );
    if ($form->submitted_by_js()) {
        // Redirect back to the note page from within the iframe
        $SESSION->add_ok_msg($result['message']);
        $form->json_reply(PIEFORM_OK, $result, false);
    }
    $form->reply(PIEFORM_OK, $result);
}

function add_attachment($attachmentid) {
    global $parent, $postid;
    $instance = new InteractionForumInstance($parent->forum);
    if ($instance) {
        $instance->attach($postid, $attachmentid);
    }
}

function delete_note_attachment($attachmentid) {
    global $parent, $postid;
    $instance = new InteractionForumInstance($parent->forum);
    if ($instance) {
        $instance->detach($postid, $attachmentid);
    }
}

$inlinejs = <<<EOF
function editpost_callback(form, data) {
    editpost_filebrowser.callback(form, data);
};
EOF;

$smarty = smarty();
$smarty->assign('deleteduser', $poster->get('deleted'));
$smarty->assign('poster', $poster);
$smarty->assign('editform', $editform);
$smarty->assign('moderator', $moderator);
$smarty->assign('parent', $parent);
$smarty->assign('action', $action);
$smarty->assign('groupadmins', group_get_admin_ids($parent->group));

if (isset($inlinejs)) {
    $smarty->assign('INLINEJAVASCRIPT', $inlinejs);
}

if (!isset($timeleft)) {
    $timeleft = 0;
}
$smarty->assign('timeleft', $timeleft);
$smarty->display('interaction:forum:editpost.tpl');
