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
 * @subpackage admin
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */
define('INTERNAL', 1);
define('INSTITUTIONALADMIN', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('institutions', 'admin'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'institutions');
require_once('pieforms/pieform.php');
define('MENUITEM', ($USER->get('admin') ? 'configusers' : 'manageinstitutions') . '/institutions');
$smarty = smarty();

$institution = param_variable('i', '');
$add         = param_boolean('add');
$edit        = param_boolean('edit');
$delete      = param_boolean('delete');

if (!$USER->get('admin')) {
    // Institutional admins with only 1 institution go straight to the edit page for that institution
    // They cannot add or delete institutions, or edit an institution they don't administer
    $add = false;
    $delete = false;
    if (!empty($institution) && !$USER->is_institutional_admin($institution)) {
        $institution = '';
        $edit = false;
    }
    if (empty($institution) && count($USER->get('admininstitutions')) == 1) {
        redirect(get_config('wwwroot') . 'admin/users/institutions.php?i='
                 . key($USER->get('institutions')));
    }
}

if ($institution || $add) {

    $authinstances = auth_get_auth_instances_for_institution($institution);
    if (false == $authinstances) {
        $authinstances = array();
    }

    if ($delete) {
        function delete_validate(Pieform $form, $values) {
            if (get_field('usr_institution', 'COUNT(*)', 'institution', $values['i'])) {
                // TODO: exception is of the wrong type
                throw new Exception('Attempt to delete an institution that has members');
            }
        }

        function delete_cancel_submit() {
            redirect('/admin/users/institutions.php');
        }

        function delete_submit(Pieform $form, $values) {
            global $SESSION;

            $authinstanceids = get_column('auth_instance', 'id', 'institution', $values['i']);

            db_begin();
            foreach ($authinstanceids as $id) {
                delete_records('auth_instance_config', 'instance', $id);
            }
            delete_records('auth_instance', 'institution', $values['i']);
            delete_records('host', 'institution', $values['i']);
            delete_records('institution_locked_profile_field', 'name', $values['i']);
            delete_records('usr_institution_request', 'institution', $values['i']);
            delete_records('institution', 'name', $values['i']);
            db_commit();

            $SESSION->add_ok_msg(get_string('institutiondeletedsuccessfully', 'admin'));
            redirect('/admin/users/institutions.php');
        }
        $form = array(
            'name' => 'delete',
            'elements' => array(
                'i' => array(
                    'type' => 'hidden',
                    'value' => $institution
                ),
                'delete' => array(
                    'type' => 'hidden',
                    'value' => 1
                ),
                'submit' => array(
                    'type' => 'submitcancel',
                    'value' => array(get_string('yes'), get_string('no'))
                )
            )
        );
        $smarty->assign('delete_form', pieform($form));
        $smarty->display('admin/users/institutions.tpl');
        exit;
    }

    $instancearray = array();
    $instancestring = '';
    $c = count($authinstances);
    $inuse = '';

    if (!$add) {
        $data = get_record('institution', 'name', $institution);
        $lockedprofilefields = (array) get_column('institution_locked_profile_field', 'profilefield', 'name', $institution);
        
        // TODO: Find a better way to work around Smarty's minimal looping logic
        if (!empty($authinstances)) {
            foreach($authinstances as $key => $val) {
                $authinstances[$key]->index = $key;
                $authinstances[$key]->total = $c;
                $instancearray[] = $val->id;
            }

            $instancestring = implode(',',$instancearray);
            $inuserecords = array();
            if ($records = get_records_sql_assoc('select authinstance, count(id) from {usr} where authinstance in ('.$instancestring.') group by authinstance', array())) {
                foreach ($records as $record) {
                    $inuserecords[] = $record->authinstance;
                }
            }
            $inuse = implode(',',$inuserecords);
        }
        $authtypes = auth_get_available_auth_types($institution);
    }
    else {
        $data = new StdClass;
        $data->displayname = '';
        $data->registerallowed = 1;
        $data->theme = 'default';
        $data->defaultmembershipperiod = null;
        $lockedprofilefields = array();
        $smarty->assign('add', true);

        $authtypes = auth_get_available_auth_types();
    }
    $themeoptions = get_themes();
    
    safe_require('artefact', 'internal');
    $elements = array(
        'name' => array(
            'type' => 'text',
            'title' => get_string('institutionname', 'admin'),
            'rules' => array(
                'required'  => true,
                'maxlength' => 255,
                'regex'     => '/^[a-zA-Z]+$/'
            ),
            'ignore' => !$add,
            'help'   => true,
        ),
        'add' => array(
            'type'   => 'hidden',
            'value'  => true,
            'ignore' => !$add
        ),
        'inuse' => array(
            'type'   => 'hidden',
            'value'  => $inuse,
            'id'     => 'inuse',
            'ignore' => $add
        ),
        'i' => array(
            'type'   => 'hidden',
            'value'  => $institution,
            'ignore' => $add
        ),
        'displayname' => array(
            'type' => 'text',
            'title' => get_string('institutiondisplayname', 'admin'),
            'defaultvalue' => $data->displayname,
            'rules' => array(
                'required'  => true,
                'maxlength' => 255
            ),
            'help'   => true,
        ),
        'authplugin' => array(
            'type'    => 'authlist',
            'title'   => get_string('authplugin', 'admin'),
            'options' => $authinstances,
            'authtypes' => $authtypes,
            'instancearray' => $instancearray,
            'instancestring' => $instancestring,
            'institution' => $institution,
            'help'   => true,
            'ignore' => count($authtypes) == 0
        ),
        'registerallowed' => array(
            'type'         => 'checkbox',
            'title'        => get_string('registrationallowed', 'admin'),
            'description'  => get_string('registrationalloweddescription', 'admin'),
            'defaultvalue' => $data->registerallowed,
            'help'   => true,
        ),
    );

    if (empty($data->name) || $data->name != 'mahara') {
        $elements['defaultmembershipperiod'] = array(
            'type'         => 'expiry',
            'title'        => get_string('defaultmembershipperiod', 'admin'),
            'description'  => get_string('defaultmembershipperioddescription', 'admin'),
            'defaultvalue' => $data->defaultmembershipperiod,
            'help'   => true,
        );
        $elements['theme'] = array(
            'type'         => 'select',
            'title'        => get_string('theme','admin'),
            'description'  => get_string('sitethemedescription','admin'),
            'defaultvalue' => $data->theme,
            'collapseifoneoption' => true,
            'options'      => $themeoptions,
            'help'         => true,
        );
        if ($USER->get('admin')) {
            $elements['maxuseraccounts'] = array(
                'type'         => 'text',
                'title'        => get_string('maxuseraccounts','admin'),
                'description'  => get_string('maxuseraccountsdescription','admin'),
                'defaultvalue' => empty($data->maxuseraccounts) ? '' : $data->maxuseraccounts,
                'rules'        => array('regex' => '/^\d*$/'),
            );
        }
    }

    $elements['lockedfields'] = array(
        'value' => '<tr><th colspan="2">Locked fields ' 
        . get_help_icon('core', 'admin', 'institution', 'lockedfields') 
        . '</th></tr>'
    );
    foreach (ArtefactTypeProfile::get_all_fields() as $field => $type) {
        $elements[$field] = array(
            'type' => 'checkbox',
            'title' => get_string($field, 'artefact.internal'),
            'defaultvalue' => in_array($field, $lockedprofilefields)
        );
    }
    $elements['submit'] = array(
        'type' => 'submitcancel',
        'value' => array(get_string('submit'), get_string('cancel'))
    );

    $smarty->assign('instancestring', $instancestring);

    $smarty->assign('institution_form', pieform(array(
        'name'     => 'institution',
        'renderer' => 'table',
        'plugintype' => 'core',
        'pluginname' => 'admin',
        'elements' => $elements
    )));

}
else {
    // Get a list of institutions
    if (!$USER->get('admin')) { // Filter the list for institutional admins
        $where = '
        WHERE i.name IN (' . join(',', array_map('db_quote', $USER->get('admininstitutions'))) . ')';
    }
    else {
        $where = '';
        $smarty->assign('siteadmin', true);
        $defaultinstmembers = count_records_sql('
            SELECT COUNT(u.id) FROM {usr} u LEFT OUTER JOIN {usr_institution} i ON u.id = i.usr
            WHERE u.deleted = 0 AND i.usr IS NULL
        ');
    }
    $institutions = get_records_sql_assoc('
        SELECT
            i.name, i.displayname, i.maxuseraccounts, 
            COUNT(ui.usr) AS members, SUM(ui.staff) AS staff, SUM(ui.admin) AS admins
        FROM {institution} i
        LEFT OUTER JOIN {usr_institution} ui ON (ui.institution = i.name)
        LEFT OUTER JOIN {usr} u ON (u.id = ui.usr AND u.deleted = 0) ' . $where . '
        GROUP BY
            i.name, i.displayname, i.maxuseraccounts
        ORDER BY i.name = \'mahara\', i.displayname', array());
    if (isset($defaultinstmembers)) {
        $institutions['mahara']->members = $defaultinstmembers;
    }
    $smarty->assign('institutions', $institutions);
}

function institution_submit(Pieform $form, $values) {
    global $SESSION, $institution, $add, $instancearray, $USER;

    db_begin();
    // Update the basic institution record...
    $newinstitution = new StdClass;
    if ($add) {
        $institution = $newinstitution->name = strtolower($values['name']);
    }

    $newinstitution->displayname                  = $values['displayname'];
    $newinstitution->authplugin                   = $values['authplugin'];
    $newinstitution->registerallowed              = ($values['registerallowed']) ? 1 : 0;
    $newinstitution->theme                        = empty($values['theme']) ? null : $values['theme'];
    if ($institution != 'mahara') {
        $newinstitution->defaultmembershipperiod  = ($values['defaultmembershipperiod']) ? intval($values['defaultmembershipperiod']) : null;
        if ($USER->get('admin')) {
            $newinstitution->maxuseraccounts      = ($values['maxuseraccounts']) ? intval($values['maxuseraccounts']) : null;
        }
    }

    $allinstances = array_merge($values['authplugin']['instancearray'], $values['authplugin']['deletearray']);

    if (array_diff($allinstances, $instancearray)) {
        // TODO wrong exception type
        throw new Exception('Attempt to delete or update another institution\'s auth instance');
    }

    if (array_diff($instancearray, $allinstances)) {
        // TODO wrong exception type
        throw new Exception('One of your instances is unaccounted for in this transaction');
    }

    foreach($values['authplugin']['instancearray'] as $priority => $instanceid) {
        if (in_array($instanceid, $values['authplugin']['deletearray'])) {
            // Should never happen:
            // TODO wrong exception type
            throw new Exception('Attempt to update AND delete an auth instance');
        }
        $record = new StdClass;
        $record->priority = $priority;
        $record->id = $instanceid;
        update_record('auth_instance', $record,  array('id' => $instanceid));
    }

    foreach($values['authplugin']['deletearray'] as $instanceid) {
        delete_records('auth_instance_config', 'instance', $instanceid);
        delete_records('auth_instance', 'id', $instanceid);
    }

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

    if ($add) {
        $message = 'institutionaddedsuccessfully';
        $nexturl = '/admin/users/institutions.php?i='.urlencode($institution);
    }
    else {
        $message = 'institutionupdatedsuccessfully';
        $nexturl = '/admin/users/institutions.php';
    }

    $SESSION->add_ok_msg(get_string($message, 'admin'));
    redirect($nexturl);
}

function institution_cancel_submit() {
    redirect('/admin/users/institutions.php');
}

$smarty->display('admin/users/institutions.tpl');

?>
