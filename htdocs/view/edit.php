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
 * @author     Martyn Smith <martyn@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'myportfolio/views');

define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'edit');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('docroot') . 'lib/view.php');

$id = param_integer('id', 0); // if 0, we're creating a new view
$new = param_boolean('new');

if (empty($id)) {
    define('TITLE', get_string('createview', 'view'));
    $new = true;
}
else {
    define('TITLE', get_string('editview', 'view'));
    $view = new View($id);
    if ($view->get('owner') != $USER->get('id')) {
        throw new AccessDeniedException(get_string('canteditdontown', 'view'));
    }
}

$heading = TITLE; // for the smarty template

require_once('pieforms/pieform.php');

$formatstring = '%s (%s)';
$ownerformatoptions = array(
    FORMAT_NAME_FIRSTNAME => sprintf($formatstring, get_string('firstname'), $USER->get('firstname')),
    FORMAT_NAME_LASTNAME => sprintf($formatstring, get_string('lastname'), $USER->get('lastname')),
    FORMAT_NAME_FIRSTNAMELASTNAME => sprintf($formatstring, get_string('fullname'), full_name())
);

$preferredname = $USER->get('preferredname');
if ($preferredname !== '') {
    $ownerformatoptions[FORMAT_NAME_PREFERREDNAME] = sprintf($formatstring, get_string('preferredname'), $preferredname);
}
$studentid = (string)get_field('artefact', 'title', 'owner', $USER->get('id'), 'artefacttype', 'studentid');
if ($studentid !== '') {
    $ownerformatoptions[FORMAT_NAME_STUDENTID] = sprintf($formatstring, get_string('studentid'), $studentid);
}
$ownerformatoptions[FORMAT_NAME_DISPLAYNAME] = sprintf($formatstring, get_string('displayname'), display_name($USER));

// @todo need a rule here that prevents stopdate being smaller than startdate
$editview = pieform(array(
    'name'     => 'editview',
    'method'   => 'post',
    'autofocus' => 'title',
    'plugintype' => 'core',
    'pluginname' => 'view',
    'elements' => array(
        'id' => array(
            'type'  => 'hidden',
            'value' => $id,
        ),
        'new' => array(
            'type' => 'hidden',
            'value' => $new,
        ),
        'title' => array(
            'type'         => 'text',
            'title'        => get_string('title','view'),
            'defaultvalue' => isset($view) ? $view->get('title') : null,
            'rules'        => array( 'required' => true ),
            'help'         => true,
        ),
        'startdate'        => array(
            'type'         => 'calendar',
            'title'        => get_string('startdate','view'),
            'defaultvalue' => isset($view) ? $view->get('startdate') : null,
            'caloptions'   => array(
                'showsTime'      => true,
                'ifFormat'       => '%Y/%m/%d %H:%M'
            ),
            'help'         => true,
        ),
        'stopdate'  => array(
            'type'         => 'calendar',
            'title'        => get_string('stopdate','view'),
            'defaultvalue' => isset($view) ? $view->get('stopdate') : null,
            'caloptions'   => array(
                'showsTime'      => true,
                'ifFormat'       => '%Y/%m/%d %H:%M'
            ),
            'help'         => true,
        ),
        'description' => array(
            'type'         => 'wysiwyg',
            'title'        => get_string('description','view'),
            'rows'         => 10,
            'cols'         => 70,
            'defaultvalue' => isset($view) ? $view->get('description') : null,
            'help'         => true,
        ),
        'tags'        => array(
            'type'         => 'tags',
            'title'        => get_string('tags'),
            'description'  => get_string('tagsdesc'),
            'defaultvalue' => isset($view) ? $view->get('tags') : null,
            'help'        => true,
        ),
        'ownerformat' => array(
            'type'         => 'select',
            'title'        => get_string('ownerformat','view'),
            'description'  => get_string('ownerformatdescription','view'),
            'options'      => $ownerformatoptions,
            'defaultvalue' => isset($view) ? $view->get('ownerformat') : FORMAT_NAME_DISPLAYNAME,
            'rules'        => array('required' => true),
            'help'         => true,
        ),
        'submit'   => array(
            'type'  => 'submitcancel',
            'value' => array(empty($new) ? get_string('save') : get_string('next'), get_string('cancel')),
        ),
    ),
));

function editview_validate(Pieform $form, $values) {
    if ($values['startdate'] && $values['stopdate'] && $values['startdate'] > $values['stopdate']) {
        $form->set_error('startdate', get_string('startdatemustbebeforestopdate', 'view'));
    }
}

function editview_cancel_submit() {
    redirect('/view');
}

function editview_submit(Pieform $form, $values) {

    global $USER, $SESSION;

    $editing = !empty($values['id']);
    $view = new View($values['id'], $values);


    if (empty($editing)) {
        $view->set('numcolumns', 3); // default
        $view->set('owner', $USER->get('id'));
    }
    else {
        $view->set('dirty', true);
    }

    $view->commit();

    if ($values['new']) {
        $redirecturl = '/view/blocks.php?id=' . $view->get('id') . '&new=1';
    } 
    else {
        $redirecturl = '/view/index.php';
        $SESSION->add_ok_msg(get_string('viewsavedsuccessfully', 'view'));
    }

    redirect($redirecturl);

}

$smarty = smarty();
$smarty->assign('heading', $heading);
$smarty->assign('editview', $editview);
$smarty->display('view/edit.tpl');

?>
