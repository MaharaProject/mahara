<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @author     Penny Leach <penny@catalyst.net.nz> 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', true);
define('MENUITEM', 'profile/myskills');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'resume');
define('SECTION_PAGE', 'skills');

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');
require_once(get_config('docroot') . 'artefact/lib.php');

$personal = null;
$academic = null;
$work = null;

try {
   $personal = artefact_instance_from_type('personalskill'); 
}
catch (Exception $e) {}
try {
    $academic = artefact_instance_from_type('academicskill');
}
catch (Exception $e) {}
try {
    $work = artefact_instance_from_type('workskill');
}
catch (Exception $e) {}
$sform = array(
    'name' => 'skillform',
    'jsform' => true,
    'plugintype' => 'artefact',
    'pluginname' => 'resume',
    'successcallback' => 'goalandskillform_submit',
    'elements' => array(
        'personalskill' => array(
            'type' => 'wysiwyg',
            'rows' => 10,
            'cols' => 70,
            'defaultvalue' => ((!empty($personal)) ? $personal->get('description') : null),
            'title' => get_string('personalskill', 'artefact.resume'),
        ),
        'academicskill' => array(
            'type' => 'wysiwyg',
            'rows' => 10,
            'cols' => 70,
            'defaultvalue' => ((!empty($academic)) ? $academic->get('description') : null),
            'title' => get_string('academicskill', 'artefact.resume'),
        ),
        'workskill' => array(
            'type' => 'wysiwyg',
            'rows' => 10,
            'cols' => 70,
            'defaultvalue' => ((!empty($work)) ? $work->get('description') : null),
            'title' => get_string('workskill', 'artefact.resume'),
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('save'),
        ),
    ),
);
$skillform = pieform($sform);
$smarty = smarty();
$smarty->assign('skillform', $skillform);
$smarty->display('artefact:resume:skills.tpl');
?>
