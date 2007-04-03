<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage artefact-resume
 * @author     Penny Leach <penny@catalyst.net.nz> 
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', true);
define('MENUITEM', 'myresume');
define('SUBMENUITEM', 'mygoals');

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');
require('artefact.php');

$personal = null;
$academic = null;
$career = null;

try {
   $personal = artefact_instance_from_type('personalgoal'); 
}
catch (Exception $e) {}
try {
    $academic = artefact_instance_from_type('academicgoal');
}
catch (Exception $e) {}
try {
    $career = artefact_instance_from_type('careergoal');
}
catch (Exception $e) {}
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
            'cols' => 70,
            'defaultvalue' => ((!empty($personal)) ? $personal->get('description') : null),
            'title' => get_string('personalgoal', 'artefact.resume'),
        ),
        'academicgoal' => array(
            'type' => 'wysiwyg',
            'rows' => 10,
            'cols' => 70,
            'defaultvalue' => ((!empty($academic)) ? $academic->get('description') : null),
            'title' => get_string('academicgoal', 'artefact.resume'),
        ),
        'careergoal' => array(
            'type' => 'wysiwyg',
            'rows' => 10,
            'cols' => 70,
            'defaultvalue' => ((!empty($career)) ? $career->get('description') : null),
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
$smarty->display('artefact:resume:goals.tpl');
?>
