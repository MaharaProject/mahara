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

$institution = param_variable('i', '');
$add         = param_boolean('add');
$edit        = param_boolean('edit');
$delete      = param_boolean('delete');

$query = param_variable('query', '');
$offset = param_integer('offset', 0);
$limit  = 20;

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
                throw new ConfigException('Attempt to delete an institution that has members');
            }
        }

        function delete_cancel_submit() {
            redirect('/admin/users/institutions.php');
        }

        function delete_submit(Pieform $form, $values) {
            global $SESSION;

            $authinstanceids = get_column('auth_instance', 'id', 'institution', $values['i']);
            $viewids = get_column('view', 'id', 'institution', $values['i']);
            $artefactids = get_column('artefact', 'id', 'institution', $values['i']);

            db_begin();
            if ($viewids) {
                require_once(get_config('libroot') . 'view.php');
                foreach ($viewids as $viewid) {
                    $view = new View($viewid);
                    $view->delete();
                }
            }
            if ($artefactids) {
                foreach ($artefactids as $artefactid) {
                    try {
                        $a = artefact_instance_from_id($artefactid);
                        $a->delete();
                    }
                    catch (ArtefactNotFoundException $e) {
                        // Awesome, it's already gone.
                    }
                }
            }
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
        $deleteform = pieform($form);
        $smarty = smarty();
        $smarty->assign('delete_form', $deleteform);
        $smarty->assign('institutionname', get_field('institution', 'displayname', 'name', $institution));
        $smarty->display('admin/users/institutions.tpl');
        exit;
    }

    $instancearray = array();
    $instancestring = '';
    $c = count($authinstances);
    $inuse = '';

    $sitelockedfields = (array) get_column('institution_locked_profile_field', 'profilefield', 'name', 'mahara');

    if (!$add) {
        $data = get_record('institution', 'name', $institution);
        $lockedprofilefields = (array) get_column('institution_locked_profile_field', 'profilefield', 'name', $institution);

        // TODO: Find a better way to work around Smarty's minimal looping logic
        if (!empty($authinstances)) {
            foreach($authinstances as $key => $val) {
                $authinstances[$key]->index = $key;
                $authinstances[$key]->total = $c;
                $instancearray[] = (int)$val->id;
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

        $authtypes = auth_get_available_auth_types();
    }
    $themeoptions = get_institution_themes($institution);
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
    if ($USER->get('admin') && $institution != 'mahara') {
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
            'title'        => get_string('theme'),
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
    if ($institution != 'mahara') {
        $elements['lockedfields']['elements']['description'] = array(
            'type' => 'html',
            'value' => get_string('disabledlockedfieldhelp', 'admin', get_field('institution', 'displayname', 'name', 'mahara')),
        );
    }
    foreach (ArtefactTypeProfile::get_all_fields() as $field => $type) {
        $elements['lockedfields']['elements'][$field] = array(
            'type' => 'checkbox',
            'title' => get_string($field, 'artefact.internal'),
            'defaultvalue' => in_array($field, $lockedprofilefields) || ($institution != 'mahara' && in_array($field, $sitelockedfields)),
            'disabled' => $institution != 'mahara' && in_array($field, $sitelockedfields)
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

    $institutionform = pieform(array(
        'name'     => 'institution',
        'renderer' => 'table',
        'plugintype' => 'core',
        'pluginname' => 'admin',
        'elements' => $elements
    ));

}
else {
    // Get a list of institutions
    require_once(get_config('libroot') . 'institution.php');
    if (!$USER->get('admin')) { // Filter the list for institutional admins
        $filter      = $USER->get('admininstitutions');
        $showdefault = false;
    }
    else {
        $filter      = false;
        $showdefault = true;
    }
    $data = build_institutions_html($filter, $showdefault, $query, $limit, $offset, $count);

    $smarty = smarty(array('lib/pieforms/static/core/pieforms.js', 'paginator'));
    $smarty->assign('results', $data);
    $smarty->assign('countinstitutions', $count);

    /*search institution form*/
    $searchform = pieform(array(
        'name' => 'search',
        'renderer' => 'oneline',
        'elements' => array(
            'query' => array(
                'type' => 'text',
                'defaultvalue' => $query
            ),
            'submit' => array(
                'type' => 'submit',
                'value' => get_string('search')
            )
        )
    ));
    $smarty->assign('searchform', $searchform);

    $js = <<< EOF
    addLoadEvent(function () {
    p = {$data['pagination_js']}
    connect('search_submit', 'onclick', function (event) {
        replaceChildNodes('messages');
        var params = {'query': $('search_query').value};
        p.sendQuery(params);
        event.stop();
        });
    });
EOF;

    $smarty->assign('INLINEJAVASCRIPT', $js);
    $smarty->assign('siteadmin', $USER->get('admin'));
    $smarty->assign('PAGEHEADING', get_string('admininstitutions', 'admin'));
    $smarty->display('admin/users/institutions.tpl');
    exit;
}

function institution_validate(Pieform $form, $values) {
    if (!empty($values['name']) && !$form->get_error('name') && record_exists('institution', 'name', $values['name'])) {
        $form->set_error('name', get_string('institutionnamealreadytaken', 'admin'));
    }
}

function institution_submit(Pieform $form, $values) {
    global $SESSION, $institution, $add, $instancearray, $USER, $authinstances;

    db_begin();
    // Update the basic institution record...
    $newinstitution = new StdClass;
    if ($add) {
        $institution = $newinstitution->name = strtolower($values['name']);
    }

    $newinstitution->displayname                  = $values['displayname'];
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
            $newinstitution->expiry               = db_format_timestamp($values['expiry']);
        }
    }

    if (!empty($values['authplugin'])) {
        $allinstances = array_merge($values['authplugin']['instancearray'], $values['authplugin']['deletearray']);

        if (array_diff($allinstances, $instancearray)) {
            throw new ConfigException('Attempt to delete or update another institution\'s auth instance');
        }

        if (array_diff($instancearray, $allinstances)) {
            throw new ConfigException('One of your instances is unaccounted for in this transaction');
        }

        foreach($values['authplugin']['instancearray'] as $priority => $instanceid) {
            if (in_array($instanceid, $values['authplugin']['deletearray'])) {
                // Should never happen:
                throw new SystemException('Attempt to update AND delete an auth instance');
            }
            $record = new StdClass;
            $record->priority = $priority;
            $record->id = $instanceid;
            update_record('auth_instance', $record,  array('id' => $instanceid));
        }

        foreach($values['authplugin']['deletearray'] as $instanceid) {
            // If this authinstance is the only xmlrpc authinstance that references a host, delete the host record.
            $hostwwwroot = null;
            foreach ($authinstances as $ai) {
                if ($ai->id == $instanceid && $ai->authname == 'xmlrpc') {
                    $hostwwwroot = get_field_sql("SELECT \"value\" FROM {auth_instance_config} WHERE \"instance\" = ? AND field = 'wwwroot'", array($instanceid));
                    if ($hostwwwroot && count_records_select('auth_instance_config', "field = 'wwwroot' AND \"value\" = ?", array($hostwwwroot)) == 1) {
                        // Unfortunately, it's possible that this host record could belong to a different institution,
                        // so specify the institution here.
                        delete_records('host', 'wwwroot', $hostwwwroot, 'institution', $institution);
                        // We really need to fix this, either by removing the institution from the host table, or refusing to allow the
                        // institution to be changed in the host record when another institution's authinstance is still pointing at it.
                    }
                    break;
                }
            }
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
    $_institution = get_record('institution', 'name', $institution);
    $suspended = $_institution->suspended;
    if ($USER->get('admin')) {
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

        // Suspension controls
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
    }
}

function search_submit(Pieform $form, $values) {
    redirect('/admin/users/institutions.php' . (!empty($values['query']) ? '?query=' . urlencode($values['query']) : ''));
}

$smarty = smarty();
$smarty->assign('institution_form', $institutionform);
$smarty->assign('instancestring', $instancestring);
$smarty->assign('add', $add);

if (isset($suspended)) {
    if ($suspended) {
        $smarty->assign('suspended', get_string('suspendedinstitutionmessage', 'admin'));
    }
    if (isset($suspendform)) {
        $smarty->assign('suspendform', $suspendform);
        if (isset($suspendform_top)) {
            $smarty->assign('suspendform_top', $suspendform_top);
        }
    }
}

$smarty->assign('PAGEHEADING', get_string('admininstitutions', 'admin'));
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
