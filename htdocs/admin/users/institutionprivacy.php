<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Stacey Walker
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('INSTITUTIONALADMIN', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'institutionprivacy');
define('MENUITEM', 'manageinstitutions/privacy');
require_once('institution.php');

define('TITLE', get_string('legal', 'admin'));
$versionid = param_integer('id', null);
$fs = param_alpha('fs', 'privacy');

if (!is_logged_in()) {
    throw new AccessDeniedException();
}

$institutionelement = get_institution_selector(false);

if (empty($institutionelement)) {
    $smarty = smarty();
    $smarty->display('admin/users/noinstitutions.tpl');
    exit;
}

$institution = param_alphanum('institution', null);
if (!$institution || !$USER->can_edit_institution($institution)) {
    $institution = empty($institutionelement['value']) ? $institutionelement['defaultvalue'] : $institutionelement['value'];
}
else if (!empty($institution)) {
    $institutionelement['defaultvalue'] = $institution;
}
$institutionselector = pieform(array(
    'name' => 'usertypeselect',
    'class' => 'form-inline',
    'elements' => array(
        'institution' => $institutionelement,
    )
));
$wwwroot = get_config('wwwroot');

// The "Add one" link displayed when an institution has no privay statement of its own.
$href = $wwwroot . 'admin/users/institutionprivacy.php?institution=' . $institution . '&id=0&fs=' . $fs;

// Get the institution's privacy statements and T&Cs.
$privacies = get_institution_versioned_content($institution);

// Add to an array the latest versions of both T&C and privacy statement.
$latestVersions = array(); $types = array();
if ($privacies) {
    foreach ($privacies as $key => $content) {
        if ($content->current != null) {
            array_push($latestVersions, $key);
        }
        if (!in_array($content->type, $types)) {
            // Useful in case an institution has just one type of content.
            // Will use the $types to know on which tab to display the versions table.
            array_push($types, $content->type);
        }
        $content->displayname = display_name($content->userid, null, true);
        $content->userdeleted = get_field('usr', 'deleted', 'id', $content->userid);
    }
}
// Add 0 to $latestVersions, to allow the creation of a first privacy/T&C
if (count($types) <= 1) {
    array_push($latestVersions, 0);
}
// Site privacy and T&C to display in an expandable panel.
$sitecontent = get_latest_privacy_versions(array('mahara'));
$selectedtab = $fs;
$form = false;
if ($versionid !== null) {
    $pageoptions = get_record('site_content_version', 'id', $versionid, 'institution', $institution);
    if ($versionid === 0 || $pageoptions) {
        $selectedtab = ($versionid === 0) ? $selectedtab : $pageoptions->type;
        $form = pieform(array(
            'name'              => 'editsitepage',
            'jsform'            => false,
            'jssuccesscallback' => 'contentSaved',
            'elements'          => array(
                'version' => array(
                    'type'         => 'text',
                    'title'        => get_string('version', 'admin'),
                    'description'  => $pageoptions ? get_string($privacies[$versionid]->type . 'lastversion', 'admin', $pageoptions->version) : '',
                    'defaultvalue' => '',
                    'rules' => array(
                        'required'    => true,
                        'maxlength' => 15
                    )
                ),
                'pageinstitution' => array('type' => 'hidden', 'value' => $institution),
                'activetab' => array('type' => 'hidden', 'value' => $selectedtab),
                'pagetext' => array(
                    'name'        => 'pagetext',
                    'type'        => 'wysiwyg',
                    'rows'        => 25,
                    'cols'        => 100,
                    'title'       => get_string('pagetext', 'admin'),
                    'defaultvalue' => $pageoptions ? $pageoptions->content : '',
                    'rules'       => array(
                        'maxlength' => 1000000,
                        'required' => true
                    )
                ),
                'submit' => array(
                    'class' => 'btn-primary',
                    'type'  => 'submitcancel',
                    'value' => array(get_string('savechanges', 'admin'), get_string('cancel')),
                    'goto'  => get_config('wwwroot') . 'admin/users/institutionprivacy.php?institution=' . $institution . '&fs=' . $selectedtab,
                ),
            )
        ));
    }
    else {
        throw new ViewNotFoundException(get_string('institutionprivacystatementnotfound', 'error', $institutionelement['options'][$institution], $versionid));
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
    redirect(get_config('wwwroot').'admin/users/institutionprivacy.php?institution=' . $values['pageinstitution'] . '&fs=' . $values['activetab']);
}

$js = <<< EOF
$(function() {
  checkActiveTab('$selectedtab');
  $('#usertypeselect_institution').on('change', reloadUsers);
});
EOF;

$smarty = smarty(array('privacy'));
setpageicon($smarty, 'icon-umbrella');

$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('href', $href);
$smarty->assign('sitecontent', $sitecontent);
$smarty->assign('versionid', $versionid);
$smarty->assign('results', $privacies);
$smarty->assign('pageeditform', $form);
$smarty->assign('institution', $institution);
$smarty->assign('latestVersions', $latestVersions);
$smarty->assign('institutionselector', $institutionselector);
$smarty->assign('types', implode(' ', $types));
$smarty->assign('link', "admin/users/institutionprivacy.php?institution={$institution}&id=");
$smarty->display('admin/users/institutionprivacy.tpl');
