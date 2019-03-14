<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'configsite/privacy');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'legal');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('legal', 'admin'));

$versionid = param_integer('id', null);
$fs = param_alpha('fs', 'privacy');

if (!is_logged_in()) {
    throw new AccessDeniedException();
}
// Get the site privacy statement and T&C.
$data = get_institution_versioned_content('mahara');

// Add to an array the latest versions of both T&C and privacy statement.
$latestVersions = array();
foreach ($data as $key => $content) {
    if ($content->current != null) {
        array_push($latestVersions, $key);
    }
    $content->displayname = display_name($content->userid, null, true);
    $content->userdeleted = get_field('usr', 'deleted', 'id', $content->userid);
}

$selectedtab = $fs;
if ($versionid) {
    if ($pageoptions = get_record('site_content_version', 'id', $versionid, 'institution', 'mahara')) {
        $selectedtab = $pageoptions->type;
        $form = pieform(array(
            'name'              => 'editsitepage',
            'jsform'            => false,
            'jssuccesscallback' => 'contentSaved',
            'elements'          => array(
                'version' => array(
                    'type'         => 'text',
                    'title'        => get_string('version', 'admin'),
                    'description'  => get_string($data[$versionid]->type . 'lastversion', 'admin', $pageoptions->version),
                    'defaultvalue' => '',
                    'rules' => array(
                        'required'    => true,
                        'maxlength' => 15
                    )
                ),
                'pageinstitution' => array('type' => 'hidden', 'value' => 'mahara'),
                'activetab' => array('type' => 'hidden', 'value' => $selectedtab),
                'pagetext' => array(
                    'name'        => 'pagetext',
                    'type'        => 'wysiwyg',
                    'rows'        => 25,
                    'cols'        => 100,
                    'title'       => get_string('pagetext', 'admin'),
                    'defaultvalue' => $pageoptions->content,
                    'rules'       => array(
                        'maxlength' => 1000000,
                        'required' => true
                    )
                ),
                'submit' => array(
                    'class' => 'btn-primary',
                    'type'  => 'submitcancel',
                    'value' => array(get_string('savechanges', 'admin'), get_string('cancel')),
                    'goto'  => get_config('wwwroot') . 'admin/site/privacy.php?fs=' . $selectedtab,
                ),
            )
        ));
    }
    else {
        throw new ViewNotFoundException(get_string('siteprivacystatementnotfound', 'error', $versionid));
    }
}

function editsitepage_validate(Pieform $form, $values) {
    // Check if the version entered by the user already exists for a specific content type.
    if (record_exists('site_content_version', 'institution', $values['pageinstitution'], 'version', $values['version'], 'type', $values['activetab'])) {
        $form->set_error('version', get_string('versionalreadyexist', 'admin', get_string($values['activetab'] . 'lowcase', 'admin'), $values['version']));
    }
}

function editsitepage_submit(Pieform $form, $values) {
    global $USER, $SESSION;

    $data = new stdClass();
    $data->content = $values['pagetext'];
    $data->author = $USER->get('id');
    $data->institution = $values['pageinstitution'];
    $data->ctime = db_format_timestamp(time());
    $data->version = $values['version'];
    $data->type = $values['activetab'];

    try {
        $id = insert_record('site_content_version', $data, 'id', true);
        if ($id) {
            require_once('embeddedimage.php');
            $pagetext = EmbeddedImage::prepare_embedded_images($values['pagetext'], 'staticpages', $id);
            // If there is an embedded image, update the src so users can have visibility
            if ($values['pagetext'] != $pagetext) {
                // Update the pagetext with any embedded image info
                $updated = new stdClass();
                $updated->id = $id;
                $updated->content = $pagetext;
                update_record('site_content_version', $updated, 'id');
            }
            // Auto accept the PS/T&C to avoid situation in which
            // the admin is asked to agree to the PS/T&C he has just created.
            save_user_reply_to_agreement($USER->get('id'), $id, 1);
        }
        $SESSION->add_ok_msg(get_string('pagesaved', 'admin'));
    }
    catch (SQLException $e) {
        $SESSION->add_ok_msg(get_string('savefailed', 'admin'));
    }
    redirect(get_config('wwwroot').'admin/site/privacy.php?fs=' . $values['activetab']);
}

// JQuery logic for tab hide/show and to keep the same tab active on page refresh.
$js = <<< EOF
$(function() {
    checkActiveTab('$selectedtab');
})
EOF;

if ($versionid && $pageoptions) {
    $smarty = smarty(array('adminsitepages', 'privacy'), array(), array('admin' => array('discardpageedits')));
    $smarty->assign('pageeditform', $form);
    $smarty->assign('content', $pageoptions->content);
    $smarty->assign('version', $pageoptions->version);
}
else {
    $smarty = smarty(array('privacy'));
}
setpageicon($smarty, 'icon-umbrella');
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('results', $data);
$smarty->assign('selectedtab', $selectedtab);
$smarty->assign('latestVersions', $latestVersions);
$smarty->assign('versionid', $versionid);
$smarty->assign('link', "admin/site/privacy.php?id=");
$smarty->display('admin/site/privacy.tpl');
