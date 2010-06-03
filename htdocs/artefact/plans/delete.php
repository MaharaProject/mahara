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

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');

define('TITLE', get_string('deleteplan','artefact.plans'));

safe_require('artefact','plans');

if ($delete = param_integer('plan')) {
    if ($plan = new ArtefactTypePlan($delete)) {
        $plan->check_permission();
    }
}

$todelete = (object) array(
    'completiondate' => strftime(get_string('strftimedate'), $plan->get('completiondate')),
    'completed'      => $plan->get('completed'),
    'title'          => $plan->get('title'),
    'description'    => $plan->get('description')
);

$form = array(
    'name' => 'deleteplanform',
    'plugintype' => 'artefact',
    'pluginname' => 'plans',
    'successcallback' => 'delete',
    'elements' => array(
        'submit' => array(
            'type' => 'submitcancel',
            'value' => array(get_string('deleteplan','artefact.plans'), get_string('cancel')),
            'goto' => get_config('wwwroot') . '/artefact/plans/',
        ),
        'plan' => array(
            'type' => 'hidden',
            'value' => $delete,
        ),
    )
);
$deleteplanform = pieform($form);

$smarty = smarty();
$smarty->assign('todelete', $todelete);
$smarty->assign('deleteplanform', $deleteplanform);
$smarty->assign('PAGEHEADING', hsc(get_string('deletingplan','artefact.plans',$plan->get('title'))));
$smarty->display('artefact:plans:delete.tpl');

// calls this function first so that we can get the artefact and call delete on it
function delete(Pieform $form, $values) {
    global $SESSION;

    if ($artefact = artefact_instance_from_id($values['plan'])) {
        $artefact->delete();
        if (!record_exists('artefact_plan','plan',$values['plan']) and !record_exists('artefact','id',$values['plan'])) {
            $SESSION->add_ok_msg(get_string('plandeletedsuccessfully', 'artefact.plans'));
        }
        else {
            $SESSION->add_error_msg(get_string('plannotdeletedsuccessfully', 'artefact.plans'));
        }
    }

    redirect('/artefact/plans/');
}

?>
