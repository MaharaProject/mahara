<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage artefact-resume
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', true);
define('MENUITEM', 'content/resume');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'resume');
define('SECTION_PAGE', 'index');
define('RESUME_SUBPAGE', 'index');

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('resume', 'artefact.resume'));
require_once('pieforms/pieform.php');
safe_require('artefact', 'resume');

// load up all the artefacts this user already has....
$coverletter = null;
try {
    $coverletter = artefact_instance_from_type('coverletter');
}
catch (Exception $e) { }
$personalinformation = null;
try {
    $personalinformation = artefact_instance_from_type('personalinformation');
}
catch (Exception $e) { }

$coverletterform = pieform(array(
    'name'        => 'coverletter',
    'jsform'      => true,
    'plugintype'  => 'artefact',
    'pluginname'  => 'resume',
    'jsform'      => true,
    'method'      => 'post',
    'elements'    => array(
        'coverletterfs' => array(
            'type' => 'fieldset',
            'legend' => get_string('coverletter', 'artefact.resume'),
            'elements' => array(
                'coverletter' => array(
                    'type'  => 'wysiwyg',
                    'cols'  => 100,
                    'rows'  => 30,
                    'rules' => array('maxlength' => 65536),
                    'defaultvalue' => ((!empty($coverletter)) ? $coverletter->get('description') : null),
                ),
                'save' => array(
                    'type' => 'submit',
                    'value' => get_string('save'),
                ),
            ),
            'help' => true,
        )
    )
));

$personalinformationform = pieform(array(
    'name'        => 'personalinformation',
    'jsform'      => true,
    'plugintype'  => 'artefact',
    'pluginname'  => 'resume',
    'jsform'      => true,
    'method'      => 'post',
    'elements'    => array(
        'personalinfomation' => array(
            'type' => 'fieldset',
            'legend' => get_string('personalinformation', 'artefact.resume'),
            'elements' => array(
                'dateofbirth' => array(
                    'type'       => 'calendar',
                    'caloptions' => array(
                        'showsTime'      => false,
                        'ifFormat'       => '%Y/%m/%d'
                        ),
                    'defaultvalue' => ((!empty($personalinformation)) 
                                       ? $personalinformation->get_composite('dateofbirth')+3600 : null),
                    'title' => get_string('dateofbirth', 'artefact.resume'),
                    'description' => get_string('dateformatguide'),
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
                        'female' => get_string('female', 'artefact.resume'),
                        'male'   => get_string('male', 'artefact.resume'),
                    ),
                    'title' => get_string('gender', 'artefact.resume'),
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
                ),
            ),
        ),
    ),
));

$smarty = smarty();
$smarty->assign('coverletterform', $coverletterform);
$smarty->assign('personalinformationform',$personalinformationform);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('SUBPAGENAV', PluginArtefactResume::submenu_items());
$smarty->display('artefact:resume:index.tpl');

function coverletter_submit(Pieform $form, $values) {
    global $coverletter, $personalinformation, $interest, $USER;

    $userid = $USER->get('id');
    $errors = array();

    try {
        if (empty($coverletter) && !empty($values['coverletter'])) {
            $coverletter = new ArtefactTypeCoverletter(0, array( 
                'owner' => $userid, 
                'description' => $values['coverletter']
            ));
            $coverletter->commit();
        }
        else if (!empty($coverletter) && !empty($values['coverletter'])) {
            $coverletter->set('description', $values['coverletter']);
            $coverletter->commit();
        }
        else if (!empty($coverletter) && empty($values['coverletter'])) {
            $coverletter->delete();
        }
    }
    catch (Exception $e) {
        $errors['coverletter'] = true;
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
