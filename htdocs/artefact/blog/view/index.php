<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-blog
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'content/blogs');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'blog');
define('SECTION_PAGE', 'view');

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
define('TITLE', get_string('viewblog','artefact.blog'));
safe_require('artefact', 'blog');
require_once(get_config('libroot') . 'pieforms/pieform.php');
if (!PluginArtefactBlog::is_active()) {
    throw new AccessDeniedException(get_string('plugindisableduser', 'mahara', get_string('blog','artefact.blog')));
}
if ($changepoststatus = param_integer('changepoststatus', null)) {
    ArtefactTypeBlogpost::changepoststatus_form($changepoststatus);
}
if ($delete = param_integer('delete', null)) {
    ArtefactTypeBlogpost::delete_form($delete);
}

$id = param_integer('id', null);

if ($blogpost = param_integer('blogpost', null)) {
    $post = ArtefactTypeBlogPost::get_post_data($blogpost);
    $id = $post->blogid;
    $limit = 1;
    $setlimit = 1;
    $offset = $post->offset;
}

if (is_null($id)) {
    if (!$records = get_records_select_array(
            'artefact',
            "artefacttype = 'blog' AND \"owner\" = ?",
            array($USER->get('id')),
            'id ASC'
        )) {
        die_info(get_string('nodefaultblogfound', 'artefact.blog', get_config('wwwroot')));
    }
    $id = $records[0]->id;
    $blog = new ArtefactTypeBlog($id, $records[0]);
}
else {
    $blog = new ArtefactTypeBlog($id);
}
$blog->check_permission();

if (!isset($limit)) {
    $limit = param_integer('limit', 5);
}

if (!isset($setlimit)) {
    $setlimit = 0;
}

if (!isset($offset)) {
    $offset = param_integer('offset', 0);
}

$posts = ArtefactTypeBlogPost::get_posts($id, $limit, $offset);
$template = 'artefact:blog:posts.tpl';
$pagination = array(
    'baseurl'    => get_config('wwwroot') . 'artefact/blog/view/index.php?id=' . $id,
    'id'         => 'blogpost_pagination',
    'jsonscript' => 'artefact/blog/view/index.json.php',
    'datatable'  => 'postlist',
    'setlimit'   => $setlimit,
);
ArtefactTypeBlogPost::render_posts($posts, $template, array(), $pagination);

$strpublished = json_encode(get_string('published', 'artefact.blog'));
$strdraft = json_encode(get_string('draft', 'artefact.blog'));
$strchangepoststatuspublish = json_encode(get_string('publish', 'artefact.blog'));
$strchangepoststatusunpublish = json_encode(get_string('unpublish', 'artefact.blog'));
$js = <<<EOF
function changepoststatus_success(form, data) {
    if ($('changepoststatus_' + data.id + '_currentpoststatus').value == 0) {
        removeElementClass($('posttitle_' + data.id), 'draft');
        addElementClass($('posttitle_' + data.id), 'published');
        $('poststatus' + data.id).innerHTML = {$strpublished};
        $('changepoststatus_' + data.id + '_submit').value = {$strchangepoststatusunpublish};
    }
    else {
        removeElementClass($('posttitle_' + data.id), 'published');
        addElementClass($('posttitle_' + data.id), 'draft');
        $('poststatus' + data.id).innerHTML = {$strdraft};
        $('changepoststatus_' + data.id + '_submit').value = {$strchangepoststatuspublish};
    }
}
function delete_success(form, data) {
    addElementClass('postdetails_' + data.id, 'hidden');
    if ($('postfiles_' + data.id)) {
        addElementClass('postfiles_' + data.id, 'hidden');
    }
    addElementClass('postdescription_' + data.id, 'hidden');
    addElementClass('posttitle_' + data.id, 'hidden');
    var results = \$j('#blogpost_pagination div.results').html();
    var oldcount = parseInt(results, 10);
    var newcount = oldcount - 1;
    \$j('#blogpost_pagination div.results').html(results.replace(oldcount, newcount));
    progressbarUpdate('blogpost', true);
}
EOF;

$smarty = smarty(array('paginator', 'expandable'));
$smarty->assign('PAGEHEADING', $blog->get('title'));
$smarty->assign('INLINEJAVASCRIPT', $js);

if (!$USER->get_account_preference('multipleblogs')) {
    $blogcount = count_records('artefact', 'artefacttype', 'blog', 'owner', $USER->get('id'));
    if ($blogcount == 1) {
        $smarty->assign('enablemultipleblogstext', 1);
    }
    else if ($blogcount > 1) {
        $smarty->assign('hiddenblogsnotification', 1);
    }
}

$smarty->assign_by_ref('blog', $blog);
$smarty->assign_by_ref('posts', $posts);
$smarty->display('artefact:blog:view.tpl');
exit;

function changepoststatus_submit(Pieform $form, $values) {
    $blogpost = new ArtefactTypeBlogPost((int) $values['changepoststatus']);
    $blogpost->check_permission();
    $newpoststatus = !($values['currentpoststatus']);
    $blogpost->changepoststatus($newpoststatus);
    if ($newpoststatus) {
        $strmessage = get_string('blogpostpublished', 'artefact.blog');
    }
    else {
        $strmessage = get_string('blogpostunpublished', 'artefact.blog');
    }
    $form->reply(PIEFORM_OK, array(
        'message' => $strmessage,
        'goto' => get_config('wwwroot') . 'artefact/blog/view/index.php?id=' . $blogpost->get('parent'),
        'id' => $values['changepoststatus'],
    ));
}

function delete_submit(Pieform $form, $values) {
    $blogpost = new ArtefactTypeBlogPost((int) $values['delete']);
    $blogpost->check_permission();
    if ($blogpost->get('locked')) {
        $form->reply(PIEFORM_ERR, get_string('submittedforassessment', 'view'));
    }
    $blogpost->delete();
    $form->reply(PIEFORM_OK, array(
        'message' => get_string('blogpostdeleted', 'artefact.blog'),
        'goto' => get_config('wwwroot') . 'artefact/blog/view/index.php?id=' . $blogpost->get('parent'),
        'id' => $values['delete'],
    ));
}
