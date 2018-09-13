<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-resume
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', true);
define('MENUITEM', 'create/resume');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'resume');
define('SECTION_PAGE', 'index');
define('MENUITEM_SUBPAGE', 'license');

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('resume', 'artefact.resume'));
define('SUBSECTIONHEADING', get_string('license',  'artefact.resume'));
require_once('license.php');
safe_require('artefact', 'resume');

if (!get_config('licensemetadata')) {
    redirect('/artefact/resume');
}

$personalinformation = null;
try {
    $personalinformation = artefact_instance_from_type('personalinformation');
}
catch (Exception $e) { }

$form = array(
    'name' => 'resumelicense',
    'plugintype' => 'artefact',
    'pluginname' => 'resume',
    'elements' => array(
        'license' => license_form_el_basic($personalinformation),
        'license_advanced' => license_form_el_advanced($personalinformation),
        'submit' => array(
            'type' => 'submit',
            'class' => 'btn-primary',
            'value' => get_string('save')
        ),
    ),
);
$form = pieform($form);

function resumelicense_submit(Pieform $form, $values) {
    global $personalinformation, $USER;
    $userid = $USER->get('id');

    if (empty($personalinformation)) {
        $personalinformation = new ArtefactTypePersonalinformation(0, array(
            'owner' => $userid,
            'title' => get_string('personalinformation', 'artefact.resume'),
        ));
    }
    if (get_config('licensemetadata')) {
        $personalinformation->set('license', $values['license']);
        $personalinformation->set('licensor', $values['licensor']);
        $personalinformation->set('licensorurl', $values['licensorurl']);
    }
    $personalinformation->commit();

    $result = array(
        'error'   => false,
        'message' => get_string('resumesaved', 'artefact.resume'),
        'goto'    => get_config('wwwroot') . 'artefact/resume/license.php',
    );
    if ($form->submitted_by_js()) {
        $SESSION->add_ok_msg($result['message']);
        $form->json_reply(PIEFORM_OK, $result, false);
    }
    $form->reply(PIEFORM_OK, $result);
}

$smarty = smarty(array('artefact/resume/js/simpleresumefield.js'));
setpageicon($smarty, 'icon-star');
$smarty->assign('licensesform', $form);
$smarty->assign('SUBPAGENAV', PluginArtefactResume::submenu_items());
$smarty->display('artefact:resume:licenses.tpl');
