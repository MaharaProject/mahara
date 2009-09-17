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
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
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
define('MENUITEM', 'manageinstitutions/institutions');
$smarty = smarty(array('lib/pieforms/static/core/pieforms.js'));

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
                 . key($USER->get('admininstitutions')));
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
        $data->expiry = null;
        if (!get_config('usersuniquebyusername')) {
            $data->registerallowed = 1;
        }
        $data->theme = 'sitedefault';
        $data->defaultmembershipperiod = null;
        $lockedprofilefields = array();
        $smarty->assign('add', true);

        $authtypes = auth_get_available_auth_types();
    }
    $themeoptions = get_themes();
    $themeoptions['sitedefault'] = '- ' . get_string('sitedefault', 'admin') . ' (' . $themeoptions[get_config('theme')] . ') -';
    uksort($themeoptions, 'theme_sort');

    $sitename = get_config('sitename');

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
    );
    if ($institution != 'mahara') {
       $elements['expiry'] = array(
            'type'         => 'date',
            'title'        => get_string('institutionexpiry', 'admin'),
            'description'  => get_string('institutionexpirydescription', 'admin', hsc($sitename)),
            'defaultvalue' => is_null($data->expiry) ? null : strtotime($data->expiry),
            'help'         => true,
            'minyear'      => date('Y') - 2,
            'maxyear'      => date('Y') + 10,
        );
    }
    if ($USER->get('admin')) {
        $elements['authplugin'] = array(
            'type'    => 'authlist',
            'title'   => get_string('authplugin', 'admin'),
            'options' => $authinstances,
            'authtypes' => $authtypes,
            'instancearray' => $instancearray,
            'instancestring' => $instancestring,
            'institution' => $institution,
            'help'   => true,
            'ignore' => count($authtypes) == 0 || $institution == ''
        );
    }

    if (!$add && empty($authinstances)) {
        if ($USER->get('admin')) {
            $SESSION->add_error_msg(get_string('adminnoauthpluginforinstitution', 'admin'));
        }
        else {
            $SESSION->add_error_msg(get_string('noauthpluginforinstitution', 'admin'));
        }
    }

    if (!get_config('usersuniquebyusername')) {
        $elements['registerallowed'] = array(
            'type'         => 'checkbox',
            'title'        => get_string('registrationallowed', 'admin'),
            'description'  => get_string('registrationalloweddescription2', 'admin'),
            'defaultvalue' => $data->registerallowed,
            'help'   => true,
        );
    }

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
            'defaultvalue' => $data->theme ? $data->theme : 'sitedefault',
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
                'rules'        => array(
                    'regex'     => '/^\d*$/',
                    'maxlength' => 8,
                ),
                'size'         => 5,
            );
        }
    }

    $elements['lockedfields'] = array(
        'type' => 'fieldset',
        'legend' => get_string('Lockedfields', 'admin'),
        'collapsible' => true,
        'collapsed' => true,
        'elements' => array(),
    );
    foreach (ArtefactTypeProfile::get_all_fields() as $field => $type) {
        $elements['lockedfields']['elements'][$field] = array(
            'type' => 'checkbox',
            'title' => get_string($field, 'artefact.internal'),
            'defaultvalue' => in_array($field, $lockedprofilefields)
        );
    }
    $elements['lockedfieldshelp'] = array(
        'value' => '<tr id="lockedfieldshelp"><th colspan="2">'
        . get_help_icon('core', 'admin', 'institution', 'lockedfields') 
        . '</th></tr>'
    );

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
        WHERE ii.name IN (' . join(',', array_map('db_quote', $USER->get('admininstitutions'))) . ')';
    }
    else {
        $where = '';
        $smarty->assign('siteadmin', true);
        $defaultinstmembers = count_records_sql('
            SELECT COUNT(u.id) FROM {usr} u LEFT OUTER JOIN {usr_institution} i ON u.id = i.usr
            WHERE u.deleted = 0 AND i.usr IS NULL AND u.id != 0
        ');
    }
    $institutions = get_records_sql_assoc('
        SELECT
            ii.name,
            ii.displayname,
            ii.maxuseraccounts,
            ii.suspended,
            COALESCE(a.members, 0) AS members,
            COALESCE(a.staff, 0) AS staff,
            COALESCE(a.admins, 0) AS admins
        FROM
            {institution} ii
            LEFT JOIN
                (SELECT
                    i.name, i.displayname, i.maxuseraccounts,
                    COUNT(ui.usr) AS members, SUM(ui.staff) AS staff, SUM(ui.admin) AS admins
                FROM
                    {institution} i
                    LEFT OUTER JOIN {usr_institution} ui ON (ui.institution = i.name)
                    LEFT OUTER JOIN {usr} u ON (u.id = ui.usr)
                WHERE
                    (u.deleted = 0 OR u.id IS NULL)
                GROUP BY
                    i.name, i.displayname, i.maxuseraccounts
                ) a ON (a.name = ii.name)' . $where . '
                ORDER BY
                    ii.name = \'mahara\', ii.displayname', array());
    if (isset($defaultinstmembers)) {
        $institutions['mahara']->members = $defaultinstmembers;
        $institutions['mahara']->staff   = '';
        $institutions['mahara']->admins  = '';
    }
    $smarty->assign('institutions', $institutions);
}

function institution_validate(Pieform $form, $values) {
    if (!empty($values['name']) && !$form->get_error('name') && record_exists('institution', 'name', $values['name'])) {
        $form->set_error('name', get_string('institutionnamealreadytaken', 'admin'));
    }
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
    $newinstitution->expiry                       = db_format_timestamp($values['expiry']);
    $newinstitution->authplugin                   = empty($values['authplugin']) ? null : $values['authplugin'];
    if (get_config('usersuniquebyusername')) {
        // Registering absolutely not allowed when this setting is on, it's a 
        // security risk. See the documentation for the usersuniquebyusername 
        // setting for more information
        $newinstitution->registerallowed = 0;
    }
    else {
        $newinstitution->registerallowed              = ($values['registerallowed']) ? 1 : 0;
    }
    $newinstitution->theme                        = (empty($values['theme']) || $values['theme'] == 'sitedefault') ? null : $values['theme'];
    if ($institution != 'mahara') {
        $newinstitution->defaultmembershipperiod  = ($values['defaultmembershipperiod']) ? intval($values['defaultmembershipperiod']) : null;
        if ($USER->get('admin')) {
            $newinstitution->maxuseraccounts      = ($values['maxuseraccounts']) ? intval($values['maxuseraccounts']) : null;
        }
    }

    if (!empty($values['authplugin'])) {
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
            delete_records('auth_remote_user', 'authinstance', $instanceid);
            delete_records('auth_instance_config', 'instance', $instanceid);
            delete_records('auth_instance', 'id', $instanceid);
        }
    }

    if ($add) {
        insert_record('institution', $newinstitution);
        // If registration has been turned on, then we automatically insert an 
        // internal authentication authinstance
        if ($newinstitution->registerallowed) {
            $authinstance = (object)array(
                'instancename' => 'internal',
                'priority'     => 0,
                'institution'  => $newinstitution->name,
                'authname'     => 'internal',
            );
            insert_record('auth_instance', $authinstance);
        }
    }
    else {
        $where = new StdClass;
        $where->name = $institution;
        $oldtheme = get_field('institution', 'theme', 'name', $institution);
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
        if ($newinstitution->registerallowed) {
            // If registration is not allowed, then an authinstance will not 
            // have been created, and thus cause the institution page to add 
            // its own error message on the next page load
            $SESSION->add_ok_msg(get_string('institutionaddedsuccessfully2', 'admin'));
        }
        $nexturl = '/admin/users/institutions.php?i='.urlencode($institution);
    }
    else {
        $message = get_string('institutionupdatedsuccessfully', 'admin');
        if (isset($values['theme']) && $oldtheme != $values['theme']
            && (!empty($oldtheme) || $values['theme'] != 'sitedefault')) {
            $USER->update_theme();
            $message .= '  ' . get_string('usersseenewthemeonlogin', 'admin');
        }
        $SESSION->add_ok_msg($message);
        $nexturl = '/admin/users/institutions.php';
    }

    redirect($nexturl);
}

function institution_cancel_submit() {
    redirect('/admin/users/institutions.php');
}

if ($institution && $institution != 'mahara') {
    function institution_suspend_submit(Pieform $form, $values) {
        global $SESSION, $USER;
        if (!$USER->get('admin')) {
            $SESSION->add_error_msg(get_string('errorwhilesuspending', 'admin'));
        }
        else {
            set_field('institution', 'suspended', 1, 'name', $values['i']);
            $SESSION->add_ok_msg(get_string('institutionsuspended', 'admin'));
        }
        redirect('/admin/users/institutions.php?i=' . $values['i']);
    }

    function institution_unsuspend_submit(Pieform $form, $values) {
        global $SESSION, $USER;
        if (!$USER->get('admin')) {
            $SESSION->add_error_msg(get_string('errorwhileunsuspending', 'admin'));
        }
        else {
            set_field('institution', 'suspended', 0, 'name', $values['i']);
            $SESSION->add_ok_msg(get_string('institutionunsuspended', 'admin'));
        }
        redirect('/admin/users/institutions.php?i=' . $values['i']);
    }

    $_institution = get_record('institution', 'name', $institution);
    // Suspension controls
    $suspended = $_institution->suspended;
    if (empty($suspended)) {
        $suspendformdef = array(
            'name'       => 'institution_suspend',
            'plugintype' => 'core',
            'pluginname' => 'admin',
            'elements'   => array(
                'i' => array(
                     'type'    => 'hidden',
                     'value'   => $institution,
                ),
                'submit' => array(
                    'type'        => 'submit',
                    'value'       => get_string('suspendinstitution','admin'),
                    'description' => get_string('suspendinstitutiondescription','admin'),
                ),
            )
        );

        $suspendform  = pieform($suspendformdef);
    }
    else {
        $suspendformdef = array(
            'name'       => 'institution_unsuspend',
            'plugintype' => 'core',
            'pluginname' => 'admin',
            'elements'   => array(
                'i' => array(
                     'type'    => 'hidden',
                     'value'   => $institution,
                ),
                'submit' => array(
                    'type'        => 'submit',
                    'value'       => get_string('unsuspendinstitution','admin'),
                    'description' => get_string('unsuspendinstitutiondescription','admin'),
                ),
            )
        );
        $suspendform  = pieform($suspendformdef);

        // Create a second forms for unsuspension to go in the suspend message.
        // This keeps the HTML IDs unique
        $suspendformdef['name'] = 'institution_unsuspend_top';
        $suspendformdef['renderer'] = 'oneline';
        $suspendformdef['successcallback'] = 'institution_unsuspend_submit';
        $suspendform_top = pieform($suspendformdef);
    }
    $smarty->assign('suspendform', $suspendform);
    if (isset($suspendform_top)) {
        $smarty->assign('suspendform_top', $suspendform_top);
    }
    if ($suspended) {
        $smarty->assign('suspended', get_string('suspendedinstitutionmessage', 'admin'));
    }
}

$smarty->assign('PAGEHEADING', hsc(get_string('admininstitutions', 'admin')));
$smarty->display('admin/users/institutions.tpl');

function theme_sort($a, $b) {
    if ($a == 'sitedefault') {
        return -1;
    }
    if ($b == 'sitedefault') {
        return 1;
    }
    return $a > $b;
}

?>
