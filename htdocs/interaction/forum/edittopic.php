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
define('SECTION_PAGE', 'edittopic');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('interaction', 'forum');
require_once('group.php');
require_once(get_config('docroot') . 'interaction/lib.php');
require_once('antispam.php');
safe_require('artefact', 'file');
require_once('embeddedimage.php');
define('SUBSECTIONHEADING', get_string('nameplural', 'interaction.forum'));

$userid = $USER->get('id');
$topicid = param_integer('id', 0);
$returnto = param_alpha('returnto', 'topic');

$page = get_config('wwwroot') . 'interaction/forum/edittopic.php';
if ($topicid == 0) { // new topic
    unset($topicid);
    $forumid = param_integer('forum');
    $page .= '?forum=' . $forumid;
}
else { // edit topic
    $topic = get_record_sql(
        'SELECT p.subject, p.id AS postid, p.body, p.poster, p.topic AS id, ' . db_format_tsfield('p.ctime', 'ctime') . ', t.sticky, t.closed, f.id AS forum, p.sent
        FROM {interaction_forum_post} p
        INNER JOIN {interaction_forum_topic} t ON (p.topic = t.id AND t.deleted != 1)
        INNER JOIN {interaction_instance} f ON (f.id = t.forum AND f.deleted != 1)
        WHERE p.parent IS NULL
        AND p.topic = ?',
        array($topicid)
    );
    $forumid = $topic->forum;
    $page .= '?id=' . $topic->id;
    if (!$topic) {
        throw new NotFoundException(get_string('cantfindtopic', 'interaction.forum', $topicid));
    }
}

$forum = get_record_sql(
    'SELECT f.group AS groupid, f.title, g.name AS groupname, g.grouptype
    FROM {interaction_instance} f
    INNER JOIN {group} g ON (g.id = f.group AND g.deleted = 0)
    WHERE f.id = ?
    AND f.deleted != 1',
    array($forumid)
);

if (!$forum) {
    throw new NotFoundException(get_string('cantfindforum', 'interaction.forum', $forumid));
}

$forumconfig = get_records_assoc('interaction_forum_instance_config', 'forum', $forumid, '', 'field,value');

define('GROUP', $forum->groupid);
$membership = user_can_access_forum((int)$forumid);
$moderator = (bool)($membership & INTERACTION_FORUM_MOD);
$admintutor = (bool) group_get_user_admintutor_groups();

if (!$membership || (isset($forumconfig['createtopicusers']) && $forumconfig['createtopicusers']->value == 'moderators' && !$moderator)) {
    throw new AccessDeniedException(get_string('cantaddtopic', 'interaction.forum'));
}

if (!group_within_edit_window($forum->groupid)) {
    throw new AccessDeniedException(get_string('cantaddtopic', 'interaction.forum'));
}

if (!isset($topicid)) { // new topic
    $topictype = get_string('addtopic','interaction.forum');
    define('TITLE', $forum->title . ' - ' . $topictype);
}
else { // edit topic
    $topictype = get_string('edittopic','interaction.forum');
    define('TITLE', $forum->title . ' - ' . $topictype);

    // no record for edits to own posts with 30 minutes
    if (user_can_edit_post($topic->poster, $topic->ctime)) {
        $topic->editrecord = false;
        $timeleft = ceil(get_config_plugin('interaction', 'forum', 'postdelay') - (time() - $topic->ctime) / 60);
    }
    else if ($moderator) {
        $topic->editrecord = true;
    }
    else if (user_can_edit_post($topic->poster, $topic->ctime, $USER->get('id'), false)) {
        $SESSION->add_error_msg(get_string('postaftertimeout', 'interaction.forum', get_config_plugin('interaction', 'forum', 'postdelay')));
        redirect('/interaction/forum/topic.php?id=' . $topicid);
    }
    else {
        throw new AccessDeniedException(get_string('cantedittopic', 'interaction.forum'));
    }
}

$folder = param_integer('folder', 0);
$browse = (int) param_variable('browse', 0);
$highlight = null;
if ($file = param_integer('file', 0)) {
    $highlight = array($file);
}

$instance = new InteractionForumInstance($forumid);
$currenttab = array('type' => 'user', 'id' => $USER->get('id'));
$mailsent = (isset($topic) && !empty($topic->sent)) ? true : false;
$editform = array(
    'name'              => 'edittopic',
    'successcallback'   => isset($topic) ? 'edittopic_submit' : 'addtopic_submit',
    'method'            => 'post',
    'jsform'            => true,
    'newiframeonsubmit' => true,
    'jssuccesscallback' => 'edittopic_callback',
    'jserrorcallback'   => 'edittopic_callback',
    'plugintype'        => 'interaction',
    'pluginname'        => 'forum',
    'configdirs'        => array(get_config('libroot') . 'form/', get_config('docroot') . 'artefact/file/form/'),
    'elements' => array(
        'subject' => array(
            'type'         => 'text',
            'title'        => get_string('Subject', 'interaction.forum'),
            'defaultvalue' => isset($topic) ? $topic->subject : null,
            'rules'        => array(
                'required' => true,
                'maxlength' => 255
            )
        ),
        'body' => array(
            'type'         => 'wysiwyg',
            'title'        => get_string('Body', 'interaction.forum'),
            'rows'         => 18,
            'cols'         => 70,
            'defaultvalue' => isset($topic) ? $topic->body : null,
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
            'defaultvalue'       => $instance->attachment_id_list(isset($topic) ? $topic->postid : 0),
            'selectlistcallback' => 'artefact_get_records_by_id',
            'selectcallback'     => 'add_attachment',
            'unselectcallback'   => 'delete_attachment',
            'tabs'               => $currenttab,
        ),
        'sticky' => array(
            'type'         => 'switchbox',
            'title'        => get_string('Sticky', 'interaction.forum'),
            'description'  => get_string('stickydescription', 'interaction.forum'),
            'defaultvalue' => isset($topic) && $topic->sticky == 1 ? 'checked' : null
        ),
        'closed' => array(
            'type'         => 'switchbox',
            'title'        => get_string('Closed', 'interaction.forum'),
            'description'  => get_string('closeddescription', 'interaction.forum'),
            'defaultvalue' => isset($topic) ? $topic->closed : !empty($forumconfig['closetopics']->value),
        ),
        'sendnow' => array(
            'type'         => 'switchbox',
            'title'        => get_string('sendnow', 'interaction.forum'),
            'description'  => get_string('sendnowdescription', 'interaction.forum', get_config_plugin('interaction', 'forum', 'postdelay')),
            'disabled'     => isset($topic) && !empty($topic->sent),
            'defaultvalue' => false,
        ),
        'submitpost'   => array(
            'type'  => 'submitcancel',
            'class' => 'btn-primary',
            'value'       => array(
                isset($topic) ? get_string('save') : get_string('Post','interaction.forum'),
                get_string('cancel')
            ),
            'goto'      => get_config('wwwroot') . 'interaction/forum/' . (isset($topic) && $returnto != 'view'  ? 'topic.php?id='.$topicid : 'view.php?id='.$forumid)
        ),
        'post' => array(
            'type' => 'hidden',
            'value' => isset($topic) ? $topic->postid : false
        ),
        'editrecord' => array(
            'type' => 'hidden',
            'value' => isset($topic) ? $topic->editrecord : false
        )
    ),
);

if (!$moderator) {
    if (!group_sendnow($forum->groupid) && !$admintutor) {
        unset($editform['elements']['sendnow']);
    }
    unset($editform['elements']['sticky']);
    unset($editform['elements']['closed']);
}

if (isset($topic) && (!empty($topic->sent) || !user_can_edit_post($topic->poster, $topic->ctime))) {
    unset($editform['elements']['sendnow']);
}

$editform = pieform($editform);

function addtopic_validate(Pieform $form, $values) {
    if ($baddomain = get_first_blacklisted_domain($values['body'])) {
        $form->set_error('body', get_string('blacklisteddomaininurl', 'mahara', $baddomain));
    }
    $result = probation_validate_content($values['body']);
    if ($result !== true) {
        $form->set_error('body', get_string('newuserscantpostlinksorimages1'));
    }
}

function edittopic_validate(Pieform $form, $values) {
    if ($baddomain = get_first_blacklisted_domain($values['body'])) {
        $form->set_error('body', get_string('blacklisteddomaininurl', 'mahara', $baddomain));
    }
    $result = probation_validate_content($values['body']);
    if ($result !== true) {
        $form->set_error('body', get_string('newuserscantpostlinksorimages1'));
    }
}

function post_needs_approval($topicid) {
    $needsapproval = get_field_sql("SELECT c.value FROM {interaction_forum_instance_config} c
                              INNER JOIN {interaction_forum_topic} t
                              ON t.forum = c.forum WHERE field = 'moderateposts' AND t.id = ?", array($topicid));
    return ($needsapproval == 'posts' || $needsapproval == 'postsandreplies');
}


function addtopic_submit(Pieform $form, $values) {
    global $USER, $SESSION, $moderator;
    $forumid = param_integer('forum');
    $groupid = get_field('interaction_instance', '"group"', 'id', $forumid);

    db_begin();
    $topicid = insert_record(
        'interaction_forum_topic',
        (object)array(
            'forum' => $forumid,
            'sticky' => isset($values['sticky']) && $values['sticky'] ? 1 : 0,
            'closed' => isset($values['closed']) && $values['closed'] ? 1 : 0
        ), 'id', true
    );
    $sendnow = isset($values['sendnow']) && $values['sendnow'] ? 1 : 0;
    $post = (object)array(
        'topic'   => $topicid,
        'poster'  => $USER->get('id'),
        'subject' => $values['subject'],
        'body'    => $values['body'],
        'ctime'   =>  db_format_timestamp(time())
    );

    if (post_needs_approval($topicid) && !$moderator && ! $USER->get('admin')) {
        $post->approved = 0;
    }

    $postid = insert_record('interaction_forum_post', $post, 'id', true);
    set_field('interaction_forum_post', 'path', sprintf('%010d', $postid), 'id', $postid);
    // Rewrite the post id into links in the body
    $newbody = EmbeddedImage::prepare_embedded_images($post->body, 'topic', $topicid, $groupid);
    $newbody = PluginInteractionForum::prepare_post_body($newbody, $postid);
    if (!empty($newbody) && $newbody != $post->body) {
        set_field('interaction_forum_post', 'body', $newbody, 'id', $postid);
    }

    // Attachments
    $instance = new InteractionForumInstance($forumid);
    update_attachments($instance, $values['filebrowser'], $postid);

    if (!record_exists('interaction_forum_subscription_forum', 'user', $USER->get('id'), 'forum', $forumid)) {
        insert_record('interaction_forum_subscription_topic', (object)array(
            'user'  => $USER->get('id'),
            'topic' => $topicid,
            'key'   => PluginInteractionForum::generate_unsubscribe_key(),
        ));
    }
    db_commit();
    if ($sendnow == 0) {
      $delay = get_config_plugin('interaction', 'forum', 'postdelay');
    }
    else {
      $delay = 0;
    }
    if (!is_null($delay) && $delay == 0) {
        PluginInteractionForum::interaction_forum_new_post(array($postid));
    }

    if (isset($post->approved) && $post->approved == 0) {
        $forumid = get_field('interaction_forum_topic', 'forum', 'id', $topicid);
           // Trigger activity.
        $data = new stdClass();
        $data->topicid      = $topicid;
        $data->forumid      = $forumid;
        $data->postbody     = $values['body'];
        $data->poster       = $USER->get('id');
        $data->postedtime   = time();
        $data->reason       = '';
        $data->event        = POST_NEEDS_APPROVAL;
        activity_occurred('postmoderation', $data, 'interaction', 'forum');
    }

    $result = array(
        'error'   => false,
        'message' => get_string('addtopicsuccess', 'interaction.forum'),
        'goto'    => get_config('wwwroot') . 'interaction/forum/topic.php?id=' . $topicid,
    );
    if ($form->submitted_by_js()) {
        // Redirect back to the note page from within the iframe
        $SESSION->add_ok_msg($result['message']);
        $form->json_reply(PIEFORM_OK, $result, false);
    }
    $form->reply(PIEFORM_OK, $result);
}

function edittopic_submit(Pieform $form, $values) {
    global $SESSION, $USER, $topic;
    $topicid = param_integer('id');
    $mailsent = get_field('interaction_forum_post', 'sent', 'id', $topic->postid);
    $returnto = param_alpha('returnto', 'topic');
    $groupid = get_field_sql("SELECT DISTINCT i.group FROM {interaction_instance} i
                              INNER JOIN {interaction_forum_topic} t ON i.id = t.forum
                              WHERE t.id =?", array($topicid));
    db_begin();
    // check the post content actually changed
    // otherwise topic could have been set as sticky/closed
    $postchanged = $values['subject'] != $topic->subject || $values['body'] != $topic->body;
    if ($postchanged) {
        $newbody = EmbeddedImage::prepare_embedded_images($values['body'], 'topic', $topicid, $groupid);
        update_record(
            'interaction_forum_post',
            array(
                'subject' => $values['subject'],
                'body' => PluginInteractionForum::prepare_post_body($newbody, $values['post']),
            ),
            array('id' => $values['post'])
        );
    }
    if ($values['editrecord'] && $postchanged) {
        insert_record(
            'interaction_forum_edit',
            (object)array(
                'user' => $USER->get('id'),
                'post' => $values['post'],
                'ctime' => db_format_timestamp(time())
            )
        );
    }
    if(isset($values['sticky'])){
        update_record(
            'interaction_forum_topic',
            array(
                'sticky' => isset($values['sticky']) && $values['sticky'] == 1 ? 1 : 0,
                'closed' => isset($values['closed']) && $values['closed'] == 1 ? 1 : 0
            ),
            array('id' => $topicid)
        );
    }
    // Attachments
    $instance = new InteractionForumInstance($topic->forum);
    update_attachments($instance, $values['filebrowser'], $topic->postid, $mailsent);

    db_commit();

    $result = array(
        'error'   => false,
        'message' => get_string('edittopicsuccess', 'interaction.forum'),
    );
    if ($returnto == 'view') {
        $result['goto'] = get_config('wwwroot') . 'interaction/forum/view.php?id=' . $topic->forum;
    }
    else {
        $result['goto'] = get_config('wwwroot') . 'interaction/forum/topic.php?id=' . $topicid;
    }

    if ($form->submitted_by_js()) {
        // Redirect back to the note page from within the iframe
        $SESSION->add_ok_msg($result['message']);
        $form->json_reply(PIEFORM_OK, $result, false);
    }
    $form->reply(PIEFORM_OK, $result);
}

function add_attachment($attachmentid) {
    global $forumid, $topic;
    $instance = new InteractionForumInstance($forumid);
    if ($instance) {
        $instance->attach($topic->postid, $attachmentid);
    }
}

function delete_note_attachment($attachmentid) {
    global $forumid, $topic;
    $instance = new InteractionForumInstance($forumid);
    if ($instance) {
        $instance->detach($topic->postid, $attachmentid);
    }
}

$inlinejs = <<<EOF
function edittopic_callback(form, data) {
    edittopic_filebrowser.callback(form, data);
};
EOF;

$smarty = smarty();
$smarty->assign('heading', $forum->groupname);
$smarty->assign('subheading', $topictype);
$smarty->assign('moderator', $moderator);
$smarty->assign('editform', $editform);
if (!isset($timeleft)) {
    $timeleft = 0;
}
$smarty->assign('timeleft', $timeleft);
if (isset($inlinejs)) {
    $smarty->assign('INLINEJAVASCRIPT', $inlinejs);
}
$smarty->display('interaction:forum:edittopic.tpl');
