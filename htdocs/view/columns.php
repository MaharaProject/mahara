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
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
require_once(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require_once('view.php');
require_once(get_config('libroot') . 'group.php');
define('TITLE', get_string('changemyviewlayout', 'view'));

$id = param_integer('id');
$new = param_boolean('new');
$category = param_alpha('c', '');
$view = new View($id);
$numcolumns = $view->get('numcolumns');
$group = $view->get('group');
$institution = $view->get('institution');
View::set_nav($group, $institution);

if (!$USER->can_edit_view($view)) {
    throw new AccessDeniedException();
}

if ($USER->get_account_preference('addremovecolumns')) {
    redirect('/view/layout.php?id=' . $id . '&c=' . $category . '&new=' . $new);
}

$columnsform = pieform(array(
    'name' => 'viewcolumns',
    'elements' => array(
        'numcolumns' => array(
            'type' => 'select',
            'title' => get_string('numberofcolumns', 'view'),
            'options' => array( 1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5'),
            'defaultvalue' => $numcolumns,
        ),
        'submit' => array(
            'type' => 'submitcancel',
            'value' => array(get_string('next'), get_string('cancel')),
            'goto' => get_config('wwwroot') . 'view/blocks.php?id=' . $view->get('id') . '&c=' . $category . '&new=' . $new
        )
    )
));

$smarty = smarty(array(), array(), array(), array('sidebars' => false));
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('form', $columnsform);
$smarty->assign('pagedescription', get_string('viewcolumnspagedescription', 'view'));
$smarty->display('form.tpl');

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

    if ($newcolumns > 1 && $newcolumns < 5) {
        redirect(get_config('wwwroot') . 'view/layout.php?id=' . $view->get('id') . '&c=' . $category . '&new=' . $new);
    }
    else {
        $SESSION->add_ok_msg(get_string('viewlayoutchanged', 'view'));
        redirect(get_config('wwwroot') . 'view/blocks.php?id=' . $view->get('id') . '&c=' . $category . '&new=' . $new);
    }
}

?>
