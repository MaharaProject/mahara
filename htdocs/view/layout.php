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
 * @subpackage core
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

// TODO fix title of this page
// TODO check security of this page
define('INTERNAL', 1);
define('MENUITEM', 'viewlayout');
require_once(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require_once('view.php');
define('TITLE', 'Views Layout [DANGER construction site]');

$id = param_integer('id');
$new = param_boolean('new');
$category = param_alpha('c', '');
$view = new View($id);
$numcolumns = $view->get('numcolumns');
$currentlayout = $view->get('layout');

if ($numcolumns > 1 && $numcolumns < 5) {
    $layouts = get_records_array('view_layout', 'columns', $numcolumns);
    $options = array();
    foreach ($layouts as $layout) {
        $options[$layout->id] = get_string($layout->widths, 'view');
    }
    $layoutform = new Pieform(array(
        'name' => 'viewlayout',
        'elements' => array(
            'layout'  => array(
                'type' => 'radio',
                'options' => $options,
                'defaultvalue' => $view->get('layout'),
            ),
            'submit' => array(
                'type' => 'submit',
                'value' => get_string('submit'),
            ),
        ),
    ));
}
else {
    $nolayoutsmessage = get_string('noviewlayouts', 'view', $numcolumns);
}

$columnsform = pieform(array(
    'name' => 'viewcolumns',
    'renderer' => 'oneline',
    'elements' => array(
        'numcolumns' => array(
            'type' => 'select',
            'options' => array( 1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'),
            'defaultvalue' => $numcolumns,
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('changeviewcolumns', 'view')
        )
    )
));

$smarty = smarty();
$smarty->assign('columnsform', $columnsform);
$smarty->assign('currentlayout', $currentlayout);
$smarty->assign('view', $id);
$smarty->assign('new', $new);
if (isset($layoutform)) {
    $smarty->assign('form_start_tag', $layoutform->get_form_tag());
    $smarty->assign('options', $options);
}
else {
    $smarty->assign('nolayouts', $nolayoutsmessage);
}
$smarty->display('view/layout.tpl');

function viewlayout_submit(Pieform $form, $values) {
    global $view, $SESSION, $category, $new;
    $view->set('layout', $values['layout']);
    $view->commit();
    $SESSION->add_ok_msg(get_string('viewlayoutchanged', 'view'));
    redirect(get_config('wwwroot') . 'view/blocks.php?id=' . $view->get('id') . '&new=' . $new);
}

function viewcolumns_submit(Pieform $form, $values) {
    global $view, $SESSION, $category, $new;

    $oldcolumns = $view->get('numcolumns');
    $newcolumns = $values['numcolumns'];

    if ($oldcolumns > $newcolumns) {
        for ($i = $oldcolumns; $i > $newcolumns; $i--) {
            $view->removecolumn(array('column' => $i));
        }
    }
    else if ($oldcolumns < $newcolumns) {
        for ($i = $oldcolumns; $i < $newcolumns; $i++) {
            $view->addcolumn(array('before' => $i + 1, 'returndata' => false));
        }
    }

    $SESSION->add_ok_msg(get_string('viewlayoutchanged', 'view'));
    if ($newcolumns > 1 && $newcolumns < 5) {
        redirect(get_config('wwwroot') . 'view/layout.php?id=' . $view->get('id') . '&c=' . $category . '&new=' . $new);
    }
    else {
        redirect(get_config('wwwroot') . 'view/blocks.php?id=' . $view->get('id') . '&new=' . $new);
    }
}

?>
