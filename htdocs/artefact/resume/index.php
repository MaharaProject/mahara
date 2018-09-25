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
define('MENUITEM_SUBPAGE', 'index');

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('resume', 'artefact.resume'));
define('SUBSECTIONHEADING', get_string('introduction',  'artefact.resume'));
require_once('pieforms/pieform/elements/calendar.php');
safe_require('artefact', 'resume');

if (!PluginArtefactResume::is_active()) {
    throw new AccessDeniedException(get_string('plugindisableduser', 'mahara', get_string('resume','artefact.resume')));
}

$defaults = array(
    'coverletter' => array(
        'default' => '',
    ),
);
$coverletterform = pieform(simple_resumefield_form($defaults, 'artefact/resume/index.php', array(
    'editortitle' => get_string('coverletter', 'artefact.resume')
)));

// load up all the artefacts this user already has....
$personalinformation = null;
try {
    $personalinformation = artefact_instance_from_type('personalinformation');
}
catch (Exception $e) { }

$personalinformationform = pieform(array(
    'name'        => 'personalinformation',
    'plugintype'  => 'artefact',
    'pluginname'  => 'resume',
    'jsform'      => true,
    'method'      => 'post',
    'class'       => 'form-group-nested',
    'elements'    => array(
        'personalinfomation' => array(
            'type' => 'fieldset',
            'legend' => get_string('personalinformation', 'artefact.resume'),
            'elements' => array(
                'dateofbirth' => array(
                    'type'       => 'calendar',
                    'caloptions' => array(
                        'showsTime'      => false,
                    ),
                    'defaultvalue' => (
                            (!empty($personalinformation) && null !== $personalinformation->get_composite('dateofbirth'))
                            ? $personalinformation->get_composite('dateofbirth')+3600
                            : null
                    ),
                    'title' => get_string('dateofbirth', 'artefact.resume'),
                    'description' => get_string('dateofbirthformatguide1', 'mahara', pieform_element_calendar_human_readable_dateformat()),
                ),
                'placeofbirth' => array(
                    'type' => 'text',
                    'defaultvalue' => ((!empty($personalinformation))
                        ? $personalinformation->get_composite('placeofbirth') : null),
                    'title' => get_string('placeofbirth', 'artefact.resume'),
                    'size' => 30,
                ),
                'citizenship' => array(
                    'type' => 'text',
                    'defaultvalue' => ((!empty($personalinformation))
                        ? $personalinformation->get_composite('citizenship') : null),
                    'title' => get_string('citizenship', 'artefact.resume'),
                    'size' => 30,
                ),
                'visastatus' => array(
                    'type' => 'text',
                    'defaultvalue' => ((!empty($personalinformation))
                        ? $personalinformation->get_composite('visastatus') : null),
                    'title' => get_string('visastatus', 'artefact.resume'),
                    'help'  => true,
                    'size' => 30,
                ),
                'gender' => array(
                    'type' => 'radio',
                    'defaultvalue' => ((!empty($personalinformation))
                        ? $personalinformation->get_composite('gender') : null),
                    'options' => array(
                        '' => get_string('gendernotspecified', 'artefact.resume'),
                        'female' => get_string('woman', 'artefact.resume'),
                        'male'   => get_string('man', 'artefact.resume'),
                    ),
                    'title' => get_string('gender1', 'artefact.resume'),
                ),
                'maritalstatus' => array(
                    'type' => 'text',
                    'defaultvalue' => ((!empty($personalinformation))
                        ? $personalinformation->get_composite('maritalstatus') :  null),
                    'title' => get_string('maritalstatus', 'artefact.resume'),
                    'size' => 30,
                ),
                'save' => array(
                    'type' => 'submit',
                    'value' => get_string('save'),
                    'class' => 'btn-primary'
                ),
            ),
        ),
    ),
));


$smarty = smarty(array('artefact/resume/js/simpleresumefield.js'));
setpageicon($smarty, 'icon-star');
$smarty->assign('coverletterform', $coverletterform);
$smarty->assign('personalinformationform',$personalinformationform);
$smarty->assign('SUBPAGENAV', PluginArtefactResume::submenu_items());
$smarty->display('artefact:resume:index.tpl');

function personalinformation_validate(Pieform $form, $values) {
    if (!empty($values['dateofbirth'])) {
        if ($values['dateofbirth'] > time()) {
            $form->json_reply(PIEFORM_ERR, get_string('dateofbirthinvalid1','artefact.resume'));
        }
    }
}

function personalinformation_submit(Pieform $form, $values) {
    global $personalinformation, $USER;
    $userid = $USER->get('id');
    $errors = array();

    try {
        if (empty($personalinformation)) {
            $personalinformation = new ArtefactTypePersonalinformation(0, array(
                'owner' => $userid,
                'title' => get_string('personalinformation', 'artefact.resume'),
            ));
        }
        foreach (array_keys(ArtefactTypePersonalInformation::get_composite_fields()) as $field) {
            $personalinformation->set_composite($field, $values[$field]);
        }
        $personalinformation->commit();
    }
    catch (Exception $e) {
        $errors['personalinformation'] = true;
    }

    if (empty($errors)) {
        $form->json_reply(PIEFORM_OK, get_string('resumesaved','artefact.resume'));
    }
    else {
        $message = '';
        foreach (array_keys($errors) as $key) {
            $message .= get_string('resumesavefailed', 'artefact.resume')."\n";
        }
        $form->json_reply(PIEFORM_ERR, $message);
    }
}
