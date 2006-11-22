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
 * @subpackage admin
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'institutions');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
$smarty = smarty();

$institution = param_variable('i', '');
$add         = param_boolean('add');

if ($institution || $add) {

    if (!$add) {
        $data = get_record('institution', 'name', $institution);
        $lockedprofilefields = (array) get_rows('institution_locked_profile_field', 'name', $institution);
        $lockedprofilefields = array_map(create_function('$a', 'return $a[\'profilefield\'];'), $lockedprofilefields);
    }
    else {
        $data = new StdClass;
        $data->displayname = '';
        $data->registerallowed = 1;
        $data->defaultaccountlifetime = null;
        $data->defaultaccountinactiveexpire = null;
        $data->defaultaccountinactivewarn = 604800; // 1 week
        $lockedprofilefields = array();
        $smarty->assign('add', true);
    }
    
    safe_require('artefact', 'internal');
    $elements = array();

    if ($add) {
        $elements['name'] = array(
            'type' => 'text',
            'title' => get_string('institutionname'),
            'rules' => array(
                'required'  => true,
                'maxlength' => 255,
                'regex'     => '/^[a-z]+$/'
            )
        );
        $elements['add'] = array(
            'type'  => 'hidden',
            'value' => true
        );
    }
    else {
        $elements['i'] = array(
            'type'  => 'hidden',
            'value' => $institution
        );
    }

    $elements['displayname'] = array(
        'type' => 'text',
        'title' => get_string('institutiondisplayname'),
        'defaultvalue' => $data->displayname,
        'rules' => array(
            'required'  => true,
            'maxlength' => 255
        )
    );
    $elements['authplugin'] = array(
        'type'    => 'select',
        'title'   => get_string('authplugin'),
        'options' => get_records_menu('auth_installed', '', '', 'name', 'name, name')
    );
    $elements['registerallowed'] = array(
        'type'         => 'checkbox',
        'title'        => get_string('registrationallowed'),
        'description'  => get_string('registrationalloweddescription'),
        'checked' => $data->registerallowed
    );
    $elements['defaultaccountlifetime'] = array(
        'type'         => 'expiry',
        'title'        => get_string('defaultaccountlifetime'),
        'description'  => get_string('defaultaccountlifetimedescription'),
        'defaultvalue' => $data->defaultaccountlifetime
    );
    $elements['defaultaccountinactiveexpire'] = array(
        'type'         => 'expiry',
        'title'        => get_string('defaultaccountinactiveexpire'),
        'description'  => get_string('defaultaccountinactiveexpiredescription'),
        'defaultvalue' => $data->defaultaccountinactiveexpire
    );
    $elements['defaultaccountinactivewarn'] = array(
        'type' => 'expiry',
        'title' => get_string('defaultaccountinactivewarn'),
        'description' => get_string('defaultaccountinactivewarndescription'),
        'defaultvalue' => $data->defaultaccountinactivewarn
    );

    $elements['lockedfields'] = array(
        'value' => '<tr><th colspan="2">Locked fields</th></tr>'
    ); 
    foreach (ArtefactTypeProfile::get_all_fields() as $field => $type) {
        $elements[$field] = array(
            'type' => 'checkbox',
            'title' => get_string($field),
            'checked' => in_array($field, $lockedprofilefields)
        );
    }
    $elements['submit'] = array(
        'type' => 'submitcancel',
        'value' => array(get_string('submit'), get_string('cancel'))
    );

    $smarty->assign('institution_form', pieform(array(
        'name'     => 'institution',
        'elements' => $elements
    )));

}
else {
    // Get a list of institutions
    $institutions = get_records_sql('SELECT i.name, i.displayname, i.authplugin, i.registerallowed, COUNT(u.*)
        FROM institution i
        LEFT OUTER JOIN usr u ON (u.institution = i.name)
        GROUP BY 1, 2, 3, 4
        ORDER BY i.name', array());
    $smarty->assign('institutions', $institutions);
}

function institution_submit($values) {
    global $SESSION, $institution, $add;

    log_debug($values);
    db_begin();
    // Update the basic institution record...
    $newinstitution = new StdClass;
    if ($add) {
        $institution = $newinstitution->name = $values['name'];
    }

    $newinstitution->displayname                  = $values['displayname'];
    $newinstitution->authplugin                   = $values['authplugin'];
    $newinstitution->registerallowed              = ($values['registerallowed']) ? 1 : 0;
    $newinstitution->defaultaccountlifetime       = ($values['defaultaccountlifetime']) ? intval($values['defaultaccountlifetime']) : null;
    $newinstitution->defaultaccountinactiveexpire = ($values['defaultaccountinactiveexpire']) ? intval($values['defaultaccountinactiveexpire']) : null;
    $newinstitution->defaultaccountinactivewarn   = ($values['defaultaccountinactivewarn']) ? intval($values['defaultaccountinactivewarn']) : null;
    log_debug($newinstitution);

    if ($add) {
        insert_record('institution', $newinstitution);
    }
    else { 
        $where = new StdClass;
        $where->name = $institution;
        update_record('institution', $newinstitution, $where);
    }

    delete_records('institution_locked_profile_field', 'name', $institution);
    foreach (ArtefactTypeProfile::get_all_fields() as $field => $type) {
        if ($values[$field]) {
            $profilefield = new StdClass;
            $profilefield->name         = $institution;
            $profilefield->profilefield = $field;
            insert_record('institution_locked_profile_field', $profilefield);
        }
    }
    db_commit();

    $SESSION->add_ok_msg(get_string('institutionupdatedsuccessfully'));
    redirect(get_config('wwwroot') . 'admin/institutions.php');
}

function institution_cancel_submit() {
    redirect(get_config('wwwroot') . 'admin/institutions.php');
}

$smarty->display('admin/institutions.tpl');

?>
