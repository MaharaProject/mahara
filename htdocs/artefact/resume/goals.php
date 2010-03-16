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
define('MENUITEM', 'profile/mygoals');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'resume');
define('SECTION_PAGE', 'goals');

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');
require_once(get_config('docroot') . 'artefact/lib.php');

try {
    $personal = artefact_instance_from_type('personalgoal');
    $personal = $personal->get('description');
}
catch (Exception $e) {
    $personal = get_string('defaultpersonalgoal', 'artefact.resume');
}
try {
    $academic = artefact_instance_from_type('academicgoal');
    $academic = $academic->get('description');
}
catch (Exception $e) {
    $academic = get_string('defaultacademicgoal', 'artefact.resume');
}
try {
    $career = artefact_instance_from_type('careergoal');
    $career = $career->get('description');
}
catch (Exception $e) {
    $career = get_string('defaultcareergoal', 'artefact.resume');
}
$gform = array(
    'name' => 'goalform',
    'jsform' => true,
    'plugintype' => 'artefact',
    'pluginname' => 'resume',
    'successcallback' => 'goalandskillform_submit',
    'elements' => array(
        'personalgoal' => array(
            'type' => 'wysiwyg',
            'rows' => 10,
            'cols' => 65,
            'defaultvalue' => $personal,
            'title' => get_string('personalgoal', 'artefact.resume'),
        ),
        'academicgoal' => array(
            'type' => 'wysiwyg',
            'rows' => 10,
            'cols' => 65,
            'defaultvalue' => $academic,
            'title' => get_string('academicgoal', 'artefact.resume'),
        ),
        'careergoal' => array(
            'type' => 'wysiwyg',
            'rows' => 10,
            'cols' => 65,
            'defaultvalue' => $career,
            'title' => get_string('careergoal', 'artefact.resume'),
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('save'),
        ),
    ),
);
$goalform = pieform($gform);
$smarty = smarty();
$smarty->assign('goalform', $goalform);
$smarty->assign('PAGEHEADING', hsc(get_string('mygoals', 'artefact.resume')));
$smarty->display('artefact:resume:goals.tpl');
?>
