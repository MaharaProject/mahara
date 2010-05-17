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

safe_require('artefact','plans');

$artefact = param_integer('artefact');

$a = artefact_instance_from_id($artefact);
$todelete = get_record('artefact_plans_plan','artefact',$artefact);

if ($a->get('owner') != $USER->get('id')) {
    throw new AccessDeniedException(get_string('notartefactowner', 'error'));
}

$form = array(
    'name' => 'deleteplanform',
    'plugintype' => 'artefact',
    'pluginname' => 'plans',
    'successcallback' => 'delete',
    'elements' => array(
        'submit' => array(
            'type' => 'submitcancel',
            'value' => array(get_string('delete'), get_string('cancel')),
            'goto' => get_config('wwwroot') . '/artefact/plans/',
        ),
        'artefact' => array(
            'type' => 'hidden',
            'value' => $artefact,
        ),
    )
);
$deleteplanform = pieform($form);

$smarty = smarty(array('tablerenderer'));
$smarty->assign('todelete', $todelete);
$smarty->assign('deleteplanform', $deleteplanform);
$smarty->display('artefact:plans:deleteplan.tpl');

function delete(Pieform $form, $values) {

    if ($artefact = new ArtefactTypePlans($values['artefact'])) {
        $artefact->delete();
    }

    redirect('/artefact/plans/');
}

?>
