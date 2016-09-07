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
define('SECTION_PAGE', 'new');

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
require_once('license.php');
safe_require('artefact', 'blog');

if ($groupid = param_alphanum('group', null)) {
    define('SUBSECTIONHEADING', get_string('newblog','artefact.blog'));
}

$institutionname = $groupid = null;
if ($institutionname = param_alphanum('institution', null)) {
    require_once(get_config('libroot') . 'institution.php');
    $section = 'institution';
    if ($institutionname == 'mahara') {
        if (!$USER->get('admin')) {
            throw new AccessDeniedException(get_string('youarenotasiteadmin', 'artefact.blog'));
        }
        $section = 'site';
    }
    else {
        if (!$USER->get('admin') && !$USER->is_institutional_admin($institutionname)) {
            throw new AccessDeniedException(get_string('youarenotanadminof', 'artefact.blog', $institutionname));
        }
    }
    define('TITLE', get_string('newblog' . $section, 'artefact.blog', institution_display_name($institutionname)) . ': ' . get_string('blogsettings','artefact.blog'));
    PluginArtefactBlog::set_blog_nav(true, $institutionname);
}
else if ($groupid = param_alphanum('group', null)) {
    require_once('group.php');
    $group = get_record('group', 'id', $groupid, 'deleted', 0);
    $USER->reset_grouproles();
    if (!isset($USER->grouproles[$group->id])) {
        throw new AccessDeniedException(get_string('youarenotamemberof', 'artefact.blog', $group->name));
    }
    if (!group_role_can_edit_views($groupid, $USER->grouproles[$group->id])) {
        throw new AccessDeniedException(get_string('youarenotaneditingmemberof', 'artefact.blog', $group->name));
    }
    define('TITLE', $group->name);
    PluginArtefactBlog::set_blog_nav(false, null, $groupid);
}
else {
    define('TITLE', get_string('newblog', 'artefact.blog') . ': ' . get_string('blogsettings','artefact.blog'));
}

$form = array(
    'name' => 'newblog',
    'method' => 'post',
    'action' => '',
    'plugintype' => 'artefact',
    'pluginname' => 'blog',
    'elements' => array(
        'title' => array(
            'type'        => 'text',
            'title'       => get_string('blogtitle', 'artefact.blog'),
            'description' => get_string('blogtitledesc', 'artefact.blog'),
            'rules' => array(
                'required'    => true
            ),
        ),
        'description' => array(
            'type'        => 'wysiwyg',
            'rows'        => 10,
            'cols'        => 70,
            'title'       => get_string('blogdesc', 'artefact.blog'),
            'description' => get_string('blogdescdesc', 'artefact.blog'),
            'rules' => array(
                'maxlength'   => 65536,
                'required'    => false
            ),
        ),
        'tags'        => array(
            'type'        => 'tags',
            'title'       => get_string('tags'),
            'description' => get_string('tagsdescprofile'),
            'help'        => true,
        ),
        'license' => license_form_el_basic(null),
        'licensing_advanced' => license_form_el_advanced(null),
        'submit' => array(
            'type'  => 'submitcancel',
            'class' => 'btn-primary',
            'value' => array(
                get_string('createblog', 'artefact.blog'),
                get_string('cancel', 'artefact.blog')
            )
        )
    )
);
$form['elements']['institution'] = array('type' => 'hidden', 'value' => ($institutionname) ? $institutionname : 0);
$form['elements']['group'] = array('type' => 'hidden', 'value' => ($groupid) ? $groupid : 0);

$form = pieform($form);

$smarty = smarty();
$smarty->assign('form', $form);
$smarty->display('form.tpl');
exit;

/**
 * This function gets called to submit the new blog.
 *
 * @param array
 */
function newblog_submit(Pieform $form, $values) {
    global $USER;

    $data = $form->get_element('institution');
    $group = $form->get_element('group');
    if ($data['value'] != false) {
        ArtefactTypeBlog::new_blog(null, $values);
        redirect('/artefact/blog/index.php?institution=' . $data['value']);
    }
    else if ($group['value'] != false) {
        ArtefactTypeBlog::new_blog(null, $values);
        redirect('/artefact/blog/index.php?group=' . $group['value']);
    }
    else {
        ArtefactTypeBlog::new_blog($USER, $values);
        redirect('/artefact/blog/index.php');
    }
}

/**
 * This function gets called to cancel a submission.
 */
function newblog_cancel_submit(Pieform $form) {
    $data = $form->get_element('institution');
    $group = $form->get_element('group');
    if ($data['value'] != false) {
        redirect('/artefact/blog/index.php?institution=' . $data['value']);
    }
    if ($group['value'] != false) {
        redirect('/artefact/blog/index.php?group=' . $group['value']);
    }
    else {
        redirect('/artefact/blog/index.php');
    }
}
