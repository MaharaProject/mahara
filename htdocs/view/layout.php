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

if ($numcolumns > 1 && $numcolumns < 5) {
    $layouts = get_records_array('view_layout', 'columns', $numcolumns);
    $options = array();
    foreach ($layouts as $layout) {
        $options[$layout->id] = get_string($layout->widths, 'view');
    }
}
else {
    $SESSION->add_info_msg(get_string('noviewlayouts', 'view', $numcolumns));
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
