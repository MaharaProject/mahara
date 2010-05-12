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
 * @subpackage artefact-plans
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', true);
define('MENUITEM', 'profile/plans');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'plans');
define('SECTION_PAGE', 'index');

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('plans', 'artefact.plans'));

$taskform = pieform(array(
    'name'       => 'taskform',
    'plugintype' => 'artefact',
    'pluginname' => 'plans',
    'method'     => 'post',
    'elements'    => array(
        'addtask' => array(
            'type' => 'fieldset',
            'legend' => get_string('task', 'artefact.plans'),
            'elements' => array(
                'completiondate' => array(
                    'type'       => 'calendar',
                    'caloptions' => array(
                        'showsTime'      => false,
                        'ifFormat'       => '%Y/%m/%d'
                        ),
                    'defaultvalue' => null,
                    'title' => get_string('completiondate', 'artefact.plans'),
                    'description' => get_string('dateformatguide'),
                    'rules' => array(
                        'required' => true,
                    ),
                ),
                'title' => array(
                    'type' => 'text',
                    'defaultvalue' => null,
                    'title' => get_string('title', 'artefact.plans'),
                    'size' => 30,
                    'rules' => array(
                        'required' => true,
                    ),
                ),
                'description' => array(
                    'type'  => 'textarea',
                    'rows' => 10,
                    'cols' => 50,
                    'resizable' => false,
                    'defaultvalue' => null,
                    'title' => get_string('description', 'artefact.plans'),
                ),
                'completed' => array(
                    'type' => 'checkbox',
                    'defaultvalue' => 0,
                    'title' => get_string('completed', 'artefact.plans'),
                ),
                'save' => array(
                    'type' => 'submit',
                    'value' => get_string('savetask','artefact.plans'),
                ),
            ),
        ),
    ),
));

$smarty = smarty();
$smarty->assign('taskform',$taskform);
$smarty->assign('PAGEHEADING', hsc(TITLE));
$smarty->display('artefact:plans:index.tpl');

function taskform_submit(Pieform $form, $values) {
    global $USER, $SESSION;
    safe_require('artefact', 'plans');

    // Entry in artefact table
    $data = (object) array(
        'owner'    => $USER->id,
        'title'    => $values['title'] ? $values['title'] : '',
        'note'     => $values['title'],
    );
    $data->description      = $values['description'] ? $values['description'] : '';
    $data->title            = $values['title'] ? $values['title'] : '';
    $data->completiondate   = $values['completiondate'] ? $values['completiondate'] : '';
    $data->completed        = $values['completed'] ? $values['completed'] : 0;

    $artefact = new ArtefactTypePlans(0, $data);
    
    if ($artefact->commit()) {
        $SESSION->add_ok_msg(get_string('tasksavedsuccessfully', 'artefact.plans'));
    }

    redirect('/artefact/plans/');
}
?>
