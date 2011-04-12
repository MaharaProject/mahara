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
define('RESUME_SUBPAGE', 'interests');

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('resume', 'artefact.resume'));
require_once('pieforms/pieform.php');
safe_require('artefact', 'resume');

$interest = null;
try {
    $interest = artefact_instance_from_type('interest');
}
catch (Exception $e) { }

$interestsform = pieform(array(
    'name'        => 'interests',
    'jsform'      => true,
    'plugintype'  => 'artefact',
    'pluginname'  => 'resume',
    'jsform'      => true,
    'method'      => 'post',
    'elements'    => array(
        'interestsfs' => array(
            'type' => 'fieldset',
            'legend' => get_string('interest', 'artefact.resume'),
            'elements' => array(
                'interest' => array(
                    'type' => 'wysiwyg',
                    'defaultvalue' => ((!empty($interest)) ? $interest->get('description') : null),
                    'cols'  => 100,
                    'rows'  => 30,
                    'rules' => array('maxlength' => 65536),
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

$smarty = smarty();
$smarty->assign('interestsform', $interestsform);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('SUBPAGENAV', PluginArtefactResume::submenu_items());
$smarty->display('artefact:resume:interests.tpl');

function interests_submit(Pieform $form, $values) {
    global $coverletter, $personalinformation, $interest, $USER;

    $userid = $USER->get('id');
    $errors = array();

    try {
        if (empty($interest) && !empty($values['interest'])) {
            $interest = new ArtefactTypeInterest(0, array( 
                'owner' => $userid, 
                'description' => $values['interest']
            ));
            $interest->commit();
        }
        else if (!empty($interest) && !empty($values['interest'])) {
            $interest->set('description', $values['interest']);
            $interest->commit();
        }
        else if (!empty($interest) && empty($values['interest'])) {
            $interest->delete();
        }
    }
    catch (Exception $e) {
        $errors['interest'] = true;
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
