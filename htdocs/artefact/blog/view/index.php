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

define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'blog');
define('SECTION_PAGE', 'view');

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');

safe_require('artefact', 'blog');
if (!PluginArtefactBlog::is_active()) {
    throw new AccessDeniedException(get_string('plugindisableduser', 'mahara', get_string('Blog','artefact.blog')));
}

$id = param_integer('id', null);

if ($blogpost = param_integer('blogpost', null)) {
    $post = ArtefactTypeBlogPost::get_post_data($blogpost);
    $id = $post->blogid;
    $limit = 1;
    $offset = $post->offset;
}

$institutionname = $groupid = null;
$title = get_string('viewblog','artefact.blog');
if ($institution = param_alphanum('institution', null)) {
    if ($institution == 'mahara') {
        $institutionname = $institution;
        if (!($USER->get('admin'))) {
            throw new AccessDeniedException();
        }
        $title = get_string('siteblogs', 'artefact.blog');
    }
    else {
        $s = institution_selector_for_page($institution, get_config('wwwroot') . 'artefact/blog/view/index.php');
        $institutionname = $s['institution'];
        if (!($USER->get('admin') || $USER->is_institutional_admin())) {
            throw new AccessDeniedException();
        }
        $title = get_string('institutionblogs', 'artefact.blog');
    }
}
else if ($groupid = param_alphanum('group', null)) {
    $group = get_group_by_id($groupid, false, true, true);
    $title = get_string('groupblogs', 'artefact.blog', $group->name);
}
else if ($id) {
    $blogobj = new ArtefactTypeBlog($id);
    $institution = $institutionname = $blogobj->get('institution');
    $groupid = $blogobj->get('group');
    if ($groupid) {
        $group = get_group_by_id($groupid, false, true, true);
    }
    $title = get_string('viewbloggroup', 'artefact.blog', $blogobj->get('title'));
    if ($institution && $institution != 'mahara') {
        $s = institution_selector_for_page($institution, get_config('wwwroot') . 'artefact/blog/view/index.php');
    }
}

PluginArtefactBlog::set_blog_nav($institution, $institutionname, $groupid);
if ($institutionname === false) {
    $smarty = smarty();
    $smarty->display('admin/users/noinstitutions.tpl');
    exit;
}
if ($groupid) {
    define('SUBSECTIONHEADING', $title);
    define('TITLE', $group->name);
}
else {
    define('TITLE', $title);
}

if ($changepoststatus = param_integer('changepoststatus', null)) {
    ArtefactTypeBlogpost::changepoststatus_form($changepoststatus);
}
if ($delete = param_integer('delete', null)) {
    ArtefactTypeBlogpost::delete_form($delete);
}

if (is_null($id)) {
    if ($institutionname) {
        $records = get_records_select_array('artefact', "artefacttype = 'blog' AND \"institution\" = ?", array($institutionname), 'id ASC');
        if (!$records || count($records) > 1) {
            // There are either no blogs for this institution or more than one so we need to send them to journal list page
            // so they can add one or chose a particular blog by id.
            redirect("/artefact/blog/index.php?institution=$institutionname");
            exit;
        }
    }
    else if ($groupid) {
        $records = get_records_select_array('artefact', "artefacttype = 'blog' AND \"group\" = ?", array($groupid), 'id ASC');
        if (!$records || count($records) > 1) {
            // There are either no blogs for this group or more than one so we need to send them to journal list page
            // so they can add one or chose a particular blog by id.
            redirect("/artefact/blog/index.php?group=$groupid");
            exit;
        }
    }
    else {
        if (!$records = get_records_select_array('artefact', "artefacttype = 'blog' AND \"owner\" = ?", array($USER->get('id')), 'id ASC')) {
            die_info(get_string('nodefaultblogfound', 'artefact.blog', get_config('wwwroot')));
        }
    }
    if ($records) {
        if (count($records) > 1) {
            // no id supplied and more than one journal so go to journal list page
            redirect("/artefact/blog/index.php");
            exit;
        }
        $id = $records[0]->id;
        $blog = new ArtefactTypeBlog($id, $records[0]);
    }
}
else {
    $blog = new ArtefactTypeBlog($id);
}

if (!empty($blog)) {
    $blog->check_permission();
}

if (!isset($limit)) {
    $limit = param_integer('limit', 10);
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
    'setlimit'   => true,
);
ArtefactTypeBlogPost::render_posts($posts, $template, array(), $pagination);

$strpublished = json_encode(get_string('published', 'artefact.blog'));
$strdraft = json_encode(get_string('draft', 'artefact.blog'));
$strchangepoststatuspublish = json_encode(get_string('publish', 'artefact.blog'));
$strchangepoststatusunpublish = json_encode(get_string('unpublish', 'artefact.blog'));
$js = <<<EOF
function changepoststatus_success(form, data) {

    if (jQuery('#changepoststatus_' + data.id + '_currentpoststatus').val() === "0") {
        jQuery('#posttitle_' + data.id).removeClass( 'draft');
        jQuery('#posttitle_' + data.id).addClass('published');
        jQuery('#poststatus' + data.id).html({$strpublished});
        jQuery('#changepoststatus_' + data.id + '_submit').html('<span class="icon icon-times icon-lg left text-danger" role="presentation" aria-hidden="true"></span> ' + {$strchangepoststatusunpublish} +
          '<span class="sr-only">' + {$strchangepoststatusunpublish} + ' "' + data.title + '"</span>');
    }
    else {
        jQuery('#posttitle_' + data.id).removeClass('published');
        jQuery('#posttitle_' + data.id).addClass('draft');
        jQuery('#poststatus' + data.id).html({$strdraft});
        jQuery('#changepoststatus_' + data.id + '_submit').html('<span class="icon icon-check icon-lg left text-success" role="presentation" aria-hidden="true"></span>' + {$strchangepoststatuspublish} +
        '<span class="sr-only">' + {$strchangepoststatuspublish} + ' "' + data.title + '"</span>');
    }
}
function delete_success(form, data) {
    jQuery('#postdetails_' + data.id).addClass('d-none');
    if (jQuery('#postfiles_' + data.id).length) {
      jQuery('#postfiles_' + data.id).addClass('hidden');
    }
    jQuery('#postdescription_' + data.id).addClass('d-none');
    jQuery('#posttitle_' + data.id).addClass('hidden');
    var results = jQuery('#blogpost_pagination div.results').html();
    var oldcount = parseInt(results, 10);
    var newcount = oldcount - 1;
    jQuery('#blogpost_pagination div.results').html(results.replace(oldcount, newcount));
    progressbarUpdate('blogpost', true);
}
EOF;
$blogtitle = $blog->get('title');
$smarty = smarty(array('paginator'));
setpageicon($smarty, 'icon icon-book');
$smarty->assign('PAGEHEADING', !empty($blogtitle) && !$groupid ? $blogtitle : TITLE);
if (!empty($institutionname)) {
    $smarty->assign('institution', $institutionname);
    if ($institutionname != 'mahara') {
        $smarty->assign('institutionselector', $s['institutionselector']);
        $js .= $s['institutionselectorjs'];
    }
}
$smarty->assign('INLINEJAVASCRIPT', $js);
if ($institutionname) {
    require_once(get_config('libroot') . 'institution.php');
    $institution = new Institution($institutionname);
    $smarty->assign('institutiondisplayname', $institution->displayname);
}
else if (!$groupid && !$USER->get_account_preference('multipleblogs')) {
    $blogcount = count_records('artefact', 'artefacttype', 'blog', 'owner', $USER->get('id'));
    if ($blogcount == 1) {
        $smarty->assign('enablemultipleblogstext', 1);
    }
    else if ($blogcount > 1) {
        $smarty->assign('hiddenblogsnotification', 1);
    }
}

$smarty->assign('canedit', (!empty($group) ? $group->canedit : true));

$smarty->assign('blog', $blog);
$smarty->assign('posts', $posts);
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
        'title' => hsc($blogpost->get('title')),
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
