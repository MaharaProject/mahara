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
 * @subpackage core
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
define('MENUITEM', 'viewlayout');
require_once(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require_once('view.php');
define('TITLE', 'Views Layout [DANGER construction site]');

$id = param_integer('id');
$new = param_boolean('new');
$category = param_alpha('c');
$view = new View($id);

if ($view->get('numcolumns') > 1) {
    $layouts = get_records_array('view_layout', 'columns', $view->get('numcolumns'));
    $options = array();
    foreach ($layouts as $layout) {
        $options[$layout->id] = get_string($layout->widths, 'view');
    }
}
else {
    redirect('/view/blocks.php?id=' . $id . '&c=' . $category . '&new=' . $new);
}

// NOTE: not building the form, that's left to the template
$form = new Pieform(array(
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

$smarty = smarty();
$smarty->assign('view', $id);
$smarty->assign('form_start_tag', $form->get_form_tag());
$smarty->assign('options', $options);
$smarty->display('view/layout.tpl');

function viewlayout_submit(Pieform $form, $values) {
    global $view, $SESSION, $category, $new;
    $view->set('layout', $values['layout']);
    $view->commit();
    $SESSION->add_ok_msg('View layout changed');
    redirect('/view/blocks.php?id=' . $view->get('id') . '&c=' . $category . '&new=' . $new);
}

?>
