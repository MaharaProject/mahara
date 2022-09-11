<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'settings/institutions');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'account');
define('SECTION_PAGE', 'migrateinstitution');
require(dirname(dirname(__FILE__)) . '/init.php');
safe_require('auth', 'saml');
define('TITLE', get_string('institutionmembership'));
define('SUBSECTIONHEADING', get_string('selfmigration', 'mahara'));
$postformresponse = false;
$clashinstitutionname = '';

if ($SESSION->get('migrateresponse')) {
    // we are returning from the SAMl check
    $response = $SESSION->get('migrateresponse');
    $authrecords = get_records_array('auth_instance_config', 'instance', $response['instance']->instance);
    $configs = array();
    if (!$authrecords) {
        throw new ParameterException();
    }
    foreach($authrecords as $record) {
        $configs[$record->field] = $record->value;
    }
    $samlattrs = $response['saml_attributes'];
    $idp_entityid = $configs['institutionidpentityid'];
    $idp_email = $samlattrs[$configs['emailfield']][0];
    $idp_username = $samlattrs[$configs['user_attribute']][0];
    // Check that details supplied by the IdP do not clash with another existing user
    // We check if there is an existing user with the username/email pair supplied by IdP
    // On both the usr table and the auth_remote_user table
    if ($clashes = get_records_sql_array("
        SELECT u.id FROM {usr} u
        WHERE u.username = ?
        AND (
            u.email = ? OR u.id IN (
                SELECT ai.owner FROM {artefact_internal_profile_email} ai
                WHERE ai.email = ? AND ai.owner = u.id
            )
        )
        AND u.id != ?
        UNION
        SELECT u.id FROM {usr} u
        JOIN {auth_remote_user} aru ON aru.localusr = u.id
        WHERE aru.remoteusername = ?
        AND (
            u.email = ? OR u.id IN (
                SELECT ai.owner FROM {artefact_internal_profile_email} ai
                WHERE ai.email = ? AND ai.owner = u.id
            )
        )
        AND u.id != ?", array($idp_username, $idp_email, $idp_email, $USER->get('id'), $idp_username, $idp_email, $idp_email, $USER->get('id')))) {
        // Alert them about the first clash user
        $clashuser = new User();
        $clashuser->find_by_id($clashes[0]->id);
        $clashinstitution = $clashuser->get_primary_institution();
        $clashinstitutionname = get_field('institution', 'displayname', 'name', $clashinstitution);
        $hasclash = true;
        $keyinfo = get_config('wwwroot') . 'institution/index.php?institution=' . $clashinstitution;
    }
    else {
        // Add the email address as a secondary email to the logged in user
        // set email as non-primary
        set_profile_field($USER->get('id'), 'email', $idp_email, true);
        // workaround for the usr table to be set back to the primary email
        $oldemail = get_field('artefact_internal_profile_email', 'email', 'owner', $USER->get('id'), 'principal', 1);
        execute_sql('UPDATE {usr} SET email = ? WHERE id = ?', array($oldemail, $USER->get('id')));
        // Save the token
        $key = get_random_key(16);
        $token = get_random_key(8);
        $fordb = new StdClass();
        $fordb->usr = $USER->get('id');
        $wheredb = clone $fordb;
        $fordb->old_authinstance = get_field('usr', 'authinstance', 'id', $USER->get('id'));
        $fordb->new_authinstance = $response['instance']->instance;
        $fordb->new_username = $idp_username;
        $fordb->ctime = db_format_timestamp(time());
        $fordb->key = $key;
        $fordb->token = $token;
        $fordb->email = $idp_email;
        $mid = ensure_record_exists('usr_institution_migrate', $wheredb, $fordb, 'id', true);
        $hasclash = false;
        $keyinfo = get_config('wwwroot') . 'account/migrateinstitutionconfirm.php?key=' . $key . '&token=' . $token;
    }
    // Send the email
    $fullname = display_name($USER, null, true);
    $oldinstitutionname = get_field_sql("SELECT displayname FROM {institution} i
                                     JOIN {auth_instance} ai ON ai.institution = i.name
                                     JOIN {usr} u ON u.authinstance = ai.id
                                     WHERE u.id = ?", array($USER->get('id')));
    $newinstitutionname = get_field('institution', 'displayname', 'name', $response['instance']->institution);
    if ($hasclash) {
        $emailtext = get_string('migrateemailtextexistinguser', 'mahara', $fullname, get_config('sitename'), $oldinstitutionname, $newinstitutionname, $clashinstitutionname, $keyinfo, get_config('sitename'));
        $emailhtml = get_string('migrateemailtextexistinguser_html', 'mahara', $fullname, get_config('sitename'), $oldinstitutionname, $newinstitutionname, $keyinfo, $clashinstitutionname, get_config('sitename'));
    }
    else {
        $emailtext = get_string('migrateemailtext', 'mahara', $fullname, get_config('sitename'), $oldinstitutionname, $newinstitutionname, $keyinfo, get_config('sitename'));
        $emailhtml = get_string('migrateemailtext_html', 'mahara', $fullname, get_config('sitename'), $oldinstitutionname, $newinstitutionname, $keyinfo, get_config('sitename'));
    }
    email_user(
        $USER,
        null,
        get_string('migrateemailsubject', 'mahara', get_config('sitename')),
        $emailtext,
        $emailhtml
    );
    // logout from the SAML check
    $SESSION->set('migrateresponse', null);
    $SESSION->set('postmigrateresponse', true);
    require_once(get_config('docroot') .'auth/saml/lib.php');
    PluginAuthSaml::init_simplesamlphp();
    $as = new SimpleSAML\Auth\Simple('default-sp');
    $as->logout(get_config('wwwroot') . 'account/migrateinstitution.php');
}
else if ($SESSION->get('postmigrateresponse')) {
    $postformresponse = get_string('postformresponse', 'mahara');
    $SESSION->set('postmigrateresponse', false);
}

$disco = PluginAuthSaml::get_disco_list();
// get all the institutions that have an active saml auth that this user does not belong to
$institutions = get_records_sql_array("
    SELECT ai.id, i.name, i.displayname, aic.value AS idpentityid
    FROM {institution} i
    JOIN {auth_instance} ai ON ai.institution = i.name
    JOIN {auth_instance_config} aic ON aic.instance = ai.id
    WHERE ai.authname = ? AND ai.active = ? AND aic.field = ?
    AND NOT EXISTS(
        SELECT ui.usr FROM {usr_institution} ui
        WHERE ui.institution = i.name AND ui.usr = ?)
    ORDER BY aic.value, i.displayname",
    array('saml', 1, 'institutionidpentityid', $USER->get('id')));

if (!empty($institutions)) {
    foreach ($institutions as $k => $v) {
        if (!isset($disco[$v->idpentityid])) {
            unset($institutions[$k]);
        }
        else {
            $name = $disco[$v->idpentityid] . ' - ' . $v->displayname;
            $institutions[$k]->idpname = $name;
        }
        $seen[$v->idpentityid] = true;
    }
}

// Migrate institution membership for the institutions containing saml auth
if (!empty($institutions)) {
    $options = array();
    foreach ($institutions as $i) {
        $options[$i->id] = array(
            'value' => $i->idpname,
        );
    }

    $options = array('' => get_string('selectmigrateto', 'auth.saml')) + $options;
    $migrateform = pieform(array(
        'name'        => 'migrateinstitution',
        'method'      => 'post',
        'plugintype'  => 'core',
        'pluginname'  => 'account',
        'elements'    => array(
            'authinstance' => array(
                'type' => 'select',
                'title' => get_string('institution', 'mahara'),
                'collapseifoneoption' => false,
                'options' => $options,
                'defaultvalue' => key($options),
                'rules'        => array( 'required' => true ),
             ),
            'submit' => array(
                'subclass' => array('btn-primary'),
                'type'  => 'submitcancel',
                'value' => array(get_string('sendrequest'), get_string('cancel')),
             ),
        )
    ));
}
else {
    $migrateform = null;
}

function migrateinstitution_cancel_submit(Pieform $form) {
    global $SESSION;
    $SESSION->set('migrateresponse', null);
    $SESSION->set('postmigrateresponse', null);
    redirect(get_config('wwwroot') . 'account/institutions.php');
}

function migrateinstitution_validate(Pieform $form, $values) {
    global $USER;
    if (empty($values['authinstance'])) {
        $form->set_error('authinstance', get_string('novalidauthinstanceprovided', 'auth.saml'));
    }
    else {
        $iname = get_field('auth_instance', 'institution', 'id', $values['authinstance']);
        require_once('institution.php');
        $institution = new Institution($iname);
        if ($institution->isFull()) {
            $institution->send_admin_institution_is_full_message($USER->get('id'));
            $form->set_error(null, get_string('institutionmaxusersexceededrequest', 'mahara', get_config('wwwroot') . 'institution/index.php?institution=' . $iname));
        }
    }
}

function migrateinstitution_submit(Pieform $form, $values) {
    global $USER, $SESSION;
    if (!empty($values['authinstance'])) {
        $idpinstitution = get_field('auth_instance_config', 'value', 'instance', $values['authinstance'], 'field', 'institutionvalue');
        $idpinstitutionattribute = get_field('auth_instance_config', 'value', 'instance', $values['authinstance'], 'field', 'institutionattribute');
        $idpentity = get_field('auth_instance_config', 'value', 'instance', $values['authinstance'], 'field', 'institutionidpentityid');
        if ($idpentity) {
            $SESSION->set('migrateidp', $idpinstitution);
            $SESSION->set('migrateidpkey', $idpinstitutionattribute);
            $SESSION->set('migrateresponse', null);
            redirect(get_config('wwwroot') . 'auth/saml/index.php?migratecheck=1&idpentityid=' . $idpentity);
        }
        $SESSION->add_error_msg(get_string('noentityidpfound', 'auth.saml'));
    }
    else {
        $SESSION->add_error_msg(get_string('novalidauthinstanceprovided', 'auth.saml'));
    }
    redirect(get_config('wwwroot') . 'account/migrateinstitution.php');
}

$smarty = smarty();
setpageicon($smarty, 'icon-university');
$smarty->assign('migrateform', $migrateform);
$smarty->assign('postformresponse', $postformresponse);
$smarty->assign('sitename', get_config('sitename'));
if ($SESSION->get('saml_logout')) {
    // Allow the template call the iframe breaker
    $SESSION->set('saml_logout', null);
    $smarty->assign('saml_logout', true);
    // Allow the post response show again
    $SESSION->set('postmigrateresponse', true);
}
$smarty->assign('SUBPAGENAV', account_institution_get_menu_tabs());
$smarty->display('account/migrateinstitution.tpl');
