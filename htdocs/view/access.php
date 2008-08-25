<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'editaccess');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require_once('pieforms/pieform/elements/calendar.php');
require_once(get_config('docroot') . 'lib/view.php');
require_once(get_config('docroot') . 'lib/group.php');

$view = new View(param_integer('id'));
$group = $view->get('group');
$institution = $view->get('institution');
View::set_nav($group, $institution);
$new = param_boolean('new');

if ($new) {
    define('TITLE', get_string('createviewstepthree', 'view'));
}
else {
    define('TITLE', get_string('editaccessforview', 'view', $view->get('title')));
}

if (!$USER->can_edit_view($view)) {
    throw new AccessDeniedException();
}

$smarty = smarty(array('tablerenderer'), pieform_element_calendar_get_headdata(pieform_element_calendar_configure(array())), array('mahara' => array('From', 'To')));

$artefacts = $view->get_artefact_metadata();
if (empty($artefacts)) {
    $confirmmessage = get_string('reallyaddaccesstoemptyview', 'view');
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

// @todo need a rule here that prevents stopdate being smaller than startdate
$form = array(
    'name' => 'editaccess',
    'renderer' => 'div',
    'plugintype' => 'core',
    'pluginname' => 'view',
    'elements' => array(
        'id' => array(
            'type' => 'hidden',
            'value' => $view->get('id'),
        ),
        'new' => array(
            'type' => 'hidden',
            'value' => $new,
        ),
        'template' => array(
            'type'         => 'checkbox',
            'title'        => get_string('allowcopying', 'view'),
            'description'  => get_string('templatedescription', 'view'),
            'defaultvalue' => $view->get('template'),
        ),
        'accesslist' => array(
            'type'         => 'viewacl',
            'defaultvalue' => isset($view) ? $view->get_access() : null
        ),
        'overrides' => array(
            'type' => 'fieldset',
            'legend' => get_string('overridingstartstopdate', 'view'),
            'elements' => array(
                'description' => array(
                    'type' => 'html',
                    'value' => get_string('overridingstartstopdatesdescription', 'view'),
                ),
                'startdate'        => array(
                    'type'         => 'calendar',
                    'title'        => get_string('startdate','view'),
                    'defaultvalue' => isset($view) ? strtotime($view->get('startdate')) : null,
                    'caloptions'   => array(
                        'showsTime'      => true,
                        'ifFormat'       => '%Y/%m/%d %H:%M'
                    ),
                    'help'         => true,
                ),
                'stopdate'  => array(
                    'type'         => 'calendar',
                    'title'        => get_string('stopdate','view'),
                    'defaultvalue' => isset($view) ? strtotime($view->get('stopdate')) : null,
                    'caloptions'   => array(
                        'showsTime'      => true,
                        'ifFormat'       => '%Y/%m/%d %H:%M'
                    ),
                    'help'         => true,
                ),
            ),
        ),
        'submit'   => array(
            'type'  => !empty($new) ? 'cancelbackcreate' : 'submitcancel',
            'value' => !empty($new) 
                ? array(get_string('cancel'), get_string('back','view'), get_string('save'))
                : array(get_string('save'), get_string('cancel')),
            'confirm' => !empty($new) ? array(get_string('confirmcancelcreatingview', 'view'), null, null) : null,
        ),
    )
);

function editaccess_validate(Pieform $form, $values) {
    if ($values['startdate'] && $values['stopdate'] && $values['startdate'] > $values['stopdate']) {
        $form->set_error('startdate', get_string('startdatemustbebeforestopdate', 'view'));
    }
}

function editaccess_cancel_submit() {
    global $view, $new, $group, $institution;
    if ($new) {
        $view->delete();
    }
    if ($group) {
        redirect('/view/groupviews.php?group='.$group);
    }
    if ($institution) {
        redirect('/view/institutionviews.php?group='.$institution);
    }
    redirect('/view');
}


function editaccess_submit(Pieform $form, $values) {
    global $SESSION, $view, $new, $group, $institution;

    if (param_boolean('back')) {
        redirect('/view/blocks.php?id=' . $view->get('id') . '&new=' . $new);
    }

    $view->set_access($values['accesslist']);

    $view->set('startdate', $values['startdate']);
    $view->set('stopdate', $values['stopdate']);
    $view->set('template', (int) $values['template']);
    $view->commit();

    if ($values['new']) {
        $str = get_string('viewcreatedsuccessfully', 'view');
    }
    else {
        $str = get_string('viewaccesseditedsuccessfully', 'view');
    }
    $SESSION->add_ok_msg($str);
    if ($group) {
        redirect('/view/groupviews.php?group='.$group);
    }
    if ($institution) {
        redirect('/view/institutionviews.php?institution='.$institution);
    }
    redirect('/view/');

}


$smarty->assign('pagetitle', TITLE);
$smarty->assign('heading', TITLE);
$smarty->assign('form', pieform($form));
$smarty->display('view/access.tpl');

?>
