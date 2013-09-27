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
define('MENUITEM', 'content/blogs');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'blog');
define('SECTION_PAGE', 'new');

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
define('TITLE', get_string('newblog','artefact.blog') . ': ' . get_string('blogsettings','artefact.blog'));
require_once('license.php');
require_once('pieforms/pieform.php');
safe_require('artefact', 'blog');

$form = pieform(array(
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
            'value' => array(
                get_string('createblog', 'artefact.blog'),
                get_string('cancel', 'artefact.blog')
            )
        )
    )
));

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

    ArtefactTypeBlog::new_blog($USER, $values);
    redirect('/artefact/blog/index.php');
}

/**
 * This function gets called to cancel a submission.
 */
function newblog_cancel_submit() {
    redirect('/artefact/blog/index.php');
}
