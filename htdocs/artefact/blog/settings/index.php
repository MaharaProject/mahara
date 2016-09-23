<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-internal
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'blog');
define('SECTION_PAGE', 'settings');

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
define('TITLE', get_string('blogsettings','artefact.blog'));
require_once('license.php');
safe_require('artefact', 'blog');
if (!PluginArtefactBlog::is_active()) {
    throw new AccessDeniedException(get_string('plugindisableduser', 'mahara', get_string('Blog','artefact.blog')));
}

$id = param_integer('id');
$blog = new ArtefactTypeBlog($id);

$institution = $institutionname = $groupid = null;
if ($blog->get('institution')) {
    $institution = true;
    $institutionname = $blog->get('institution');
}
else if ($blog->get('group')) {
    $groupid = $blog->get('group');
}

PluginArtefactBlog::set_blog_nav($institution, $institutionname, $groupid);

$blog->check_permission(true);
if ($blog->get('locked')) {
    throw new AccessDeniedException(get_string('submittedforassessment', 'view'));
}


$form = pieform(array(
    'name' => 'editblog',
    'method' => 'post',
    'action' => '',
    'plugintype' => 'artefact',
    'pluginname' => 'blog',
    'elements' => array(
        'id' => array(
            'type'          => 'hidden',
            'value'         => $id
        ),
        'institution' => array(
            'type'          => 'hidden',
            'value'         => ($institutionname) ? $institutionname : 0
        ),
        'group' => array(
            'type'          => 'hidden',
            'value'         => ($groupid) ? $groupid : 0
        ),
        'title' => array(
            'type'          => 'text',
            'title'         => get_string('blogtitle', 'artefact.blog'),
            'description'   => get_string('blogtitledesc', 'artefact.blog'),
            'rules' => array(
                'required'    => true
            ),
            'defaultvalue'  => $blog->get('title')
        ),
        'description' => array(
            'type'          => 'wysiwyg',
            'rows'          => 10,
            'cols'          => 70,
            'title'         => get_string('blogdesc', 'artefact.blog'),
            'description'   => get_string('blogdescdesc', 'artefact.blog'),
            'rules' => array(
                'maxlength'   => 65536,
                'required'    => false
            ),
            'defaultvalue'  => $blog->get('description')
        ),
        'tags'       => array(
            'defaultvalue' => $blog->get('tags'),
            'type'         => 'tags',
            'title'        => get_string('tags'),
            'description'  => get_string('tagsdescprofile'),
            'help' => true,
        ),
        'license' => license_form_el_basic($blog),
        'licensing_advanced' => license_form_el_advanced($blog),
        'submit' => array(
            'type'  => 'submitcancel',
            'class' => 'btn-primary',
            'value' => array(
                get_string('savesettings', 'artefact.blog'),
                get_string('cancel', 'artefact.blog')
            )
        )
    )
));

$smarty = smarty();

if (!$institutionname && !$groupid) {
    if (!$USER->get_account_preference('multipleblogs')
        && count_records('artefact', 'artefacttype', 'blog', 'owner', $USER->get('id')) == 1) {
        $smarty->assign('enablemultipleblogstext', 1);
    }
}

$smarty->assign('editform', $form);
$smarty->assign('blog', $blog);
$smarty->display('artefact:blog:settings.tpl');
exit;

/**
 * This function is called to update the blog details.
 */
function editblog_submit(Pieform $form, $values) {
    global $USER;

    ArtefactTypeBlog::edit_blog($USER, $values);
    $institution = $form->get_element('institution');
    $group = $form->get_element('group');
    if (!empty($institution['value'])) {
        redirect('/artefact/blog/view/index.php?id=' . $values['id'] . '&institution=' . $institution['value']);
    }
    else if (!empty($group['value'])) {
        redirect('/artefact/blog/view/index.php?id=' . $values['id'] . '&group=' . $group['value']);
    }
    else {
        redirect('/artefact/blog/view/index.php?id=' . $values['id']);
    }
}

/**
 * This function is called to cancel the form submission. It redirects the user
 * back to the blog.
 */
function editblog_cancel_submit(Pieform $form) {
    $id = param_integer('id');
    if ($data = $form->get_element('institution')) {
        redirect('/artefact/blog/view/index.php?id=' . $id . '&institution=' . $data['value']);
    }
    else if ($data = $form->get_element('group')) {
        redirect('/artefact/blog/view/index.php?id=' . $id . '&group=' . $data['value']);
    }
    else {
        redirect('/artefact/blog/view/index.php?id=' . $id);
    }
}
