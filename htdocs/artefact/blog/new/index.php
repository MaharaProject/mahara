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
require_once('pieforms/pieform.php');
safe_require('artefact', 'blog');
$section = false;
if ($institutionname = param_alphanum('institution', null)) {
    require_once(get_config('libroot') . 'institution.php');
    $section = 'institution';
    if ($institutionname == 'mahara') {
        $section = 'site';
    }
    PluginArtefactBlog::set_blog_nav(true, $institutionname);
}
$title = ($section == 'institution') ? get_string('newblog' .  $section, 'artefact.blog', institution_display_name($institutionname)) : get_string('newblog' . $section,'artefact.blog');
define('TITLE', $title . ': ' . get_string('blogsettings','artefact.blog'));

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
            'class' => 'btn-success',
            'value' => array(
                get_string('createblog', 'artefact.blog'),
                get_string('cancel', 'artefact.blog')
            )
        )
    )
);
$form['elements']['institution'] = array('type' => 'hidden', 'value' => ($institutionname) ? $institutionname : 0);

$form = pieform($form);

$smarty =& smarty();
$smarty->assign_by_ref('form', $form);
$smarty->assign('PAGEHEADING', TITLE);
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
    if ($data['value'] != false) {
        ArtefactTypeBlog::new_blog(null, $values);
        redirect('/artefact/blog/index.php?institution=' . $data['value']);
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
    if ($data['value'] != false) {
        redirect('/artefact/blog/index.php?institution=' . $data['value']);
    }
    else {
        redirect('/artefact/blog/index.php');
    }
}
