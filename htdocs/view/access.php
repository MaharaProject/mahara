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
define('MENUITEM', 'myportfolio/views');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'editaccess');

require(dirname(dirname(__FILE__)) . '/init.php');
define('TITLE', get_string('editaccess', 'view'));
require_once('pieforms/pieform.php');
require_once('pieforms/pieform/elements/calendar.php');
require_once(get_config('docroot') . 'lib/view.php');
$smarty = smarty(array('tablerenderer'), pieform_element_calendar_get_headdata(pieform_element_calendar_configure(array())));

$view = new View(param_integer('id'));
$new = param_boolean('new');

$artefacts = $view->get_artefact_metadata();
if (empty($artefacts)) {
    $confirmmessage = get_string('reallyaddaccesstoemptyview');
    $js = <<<EOF
addLoadEvent(function() {
    connect('editaccess_submit', 'onclick', function () {
        var accesslistrows = getElementsByTagAndClassName('tr', null, 'accesslistitems');
        if (accesslistrows.length > 0 && !confirm('{$confirmmessage}')) {
            replaceChildNodes('accesslistitems', []);
        }
    });
});
EOF;
    $smarty->assign('INLINEJAVASCRIPT', $js);
}

$form = array(
    'name' => 'editaccess',
    'elements' => array(
        'id' => array(
            'type' => 'hidden',
            'value' => $view->get('id'),
        ),
        'new' => array(
            'type' => 'hidden',
            'value' => $new,
        ),
        'accesslist' => array(
            'type'         => 'viewacl',
            'defaultvalue' => isset($view) ? $view->get_access() : null
        ),
        'submit'   => array(
            'type'  => !empty($new) ? 'cancelbackcreate' : 'submitcancel',
            'value' => !empty($new) 
                ? array(get_string('cancel'), get_string('back','view'), get_string('save'))
                : array(get_string('save'), get_string('cancel')),
        ),
    )
);

function editaccess_cancel_submit() {
    redirect('/view/');
}


function editaccess_submit(Pieform $form, $values) {
    global $SESSION, $view, $new; 

    if (param_boolean('back')) {
        redirect('/view/blocks.php?id=' . $view->get('id') . '&new=' . $new);
    }

    $view->set_access($values['accesslist']);
    if ($values['new']) {
        $str = get_string('viewcreatedsuccessfully', 'view');
    }
    else {
        $str = get_string('viewaccesseditedsuccessfully', 'view');
    }
    $SESSION->add_ok_msg($str);
    redirect('/view/');
}


$smarty->assign('titlestr', get_string('editaccess', 'view'));
$smarty->assign('form', pieform($form));
$smarty->display('view/access.tpl');

?>
