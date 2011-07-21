<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @subpackage auth-saml
 * @author     Piers Harding <piers@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2011 Catalyst IT Ltd http://catalyst.net.nz
 *
 * This file incorporates work covered by the following copyright and
 * permission notice:
 *
 *    Moodle - Modular Object-Oriented Dynamic Learning Environment
 *             http://moodle.com
 *
 *    Copyright (C) 2001-3001 Martin Dougiamas        http://dougiamas.com
 *
 *    This program is free software; you can redistribute it and/or modify
 *    it under the terms of the GNU General Public License as published by
 *    the Free Software Foundation; either version 2 of the License, or
 *    (at your option) any later version.
 *
 *    This program is distributed in the hope that it will be useful,
 *    but WITHOUT ANY WARRANTY; without even the implied warranty of
 *    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *    GNU General Public License for more details:
 *
 *             http://www.gnu.org/copyleft/gpl.html
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
global $CFG, $USER, $SESSION;
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('docroot') .'auth/saml/lib.php');
require_once(get_config('libroot') .'institution.php');

// check that the plugin is active
if (get_field('auth_installed', 'active', 'name', 'saml') != 1) {
    redirect();
}

// get the config pointing to the SAML library - and load it
$samllib = get_config_plugin('auth', 'saml', 'simplesamlphplib');
if (!file_exists($samllib.'/lib/_autoload.php')) {
    throw new AuthInstanceException(get_string('errorbadssphplib','auth.saml'));
}
require_once($samllib.'/lib/_autoload.php');

// point at the configured config directory
$samlconfig = get_config_plugin('auth', 'saml', 'simplesamlphpconfig');

// get all the things that we will need from the SAML authentication
// and then shutdown the session control
SimpleSAML_Configuration::init($samlconfig);
$saml_session = SimpleSAML_Session::getInstance();

// do we have a logout request?
if (param_variable("logout", false)) {
    // logout the saml session
    $sp = $saml_session->getAuthority();
    if (! $sp) {
        $sp = 'default-sp';
    }
    $as = new SimpleSAML_Auth_Simple($sp);
    $as->logout($CFG->wwwroot);
}
$sp = param_alphanumext('as','default-sp');
if (! in_array($sp, SimpleSAML_Auth_Source::getSources())) {
    $sp = 'default-sp';
}
$as = new SimpleSAML_Auth_Simple($sp);

// Check the SimpleSAMLphp config is compatible
$saml_config = SimpleSAML_Configuration::getInstance();
$session_handler = $saml_config->getString('session.handler', false);
if (!$session_handler || $session_handler == 'phpsession') {
    throw new AuthInstanceException(get_string('errorbadssphp','auth.saml'));
}

// what is the session like?
$valid_saml_session = $saml_session->isValid($sp);

// figure out what the returnto URL should be
$wantsurl = param_variable("wantsurl", false);
if (!$wantsurl) {
    if (isset($_SESSION['wantsurl'])) {
        $wantsurl = $_SESSION['wantsurl'];
    }
    else if (! $saml_session->getIdP()) {
        $wantsurl = array_key_exists('HTTP_REFERER',$_SERVER) ? $_SERVER['HTTP_REFERER'] : $CFG->wwwroot;
    }
    else {
        $wantsurl = $CFG->wwwroot;
    }
}

// taken from Moodle clean_param - make sure the wantsurl is correctly formed
include_once('validateurlsyntax.php');
if (!validateUrlSyntax($wantsurl, 's?H?S?F?E?u-P-a?I?p?f?q?r?')) {
    $wantsurl = $CFG->wwwroot;
}

// trim off any reference to login and stash
$_SESSION['wantsurl'] = preg_replace('/\&login$/', '', $wantsurl);

// now - are we logged in?
$as->requireAuth();

// ensure that $_SESSION is cleared for simplesamlphp
if (isset($_SESSION['wantsurl'])) {
    unset($_SESSION['wantsurl']);
}

$saml_attributes = $as->getAttributes();
session_write_close();

// now - let's continue with the session handling that would normally be done
// by Maharas init.php
// the main thin is that it sets the session cookie name back to what it should be
// session_name(get_config('cookieprefix') . 'mahara');
// and starts the session again

// ***********************************************************************
// copied from original init.php
// ***********************************************************************
// Only do authentication once we know the page theme, so that the login form
// can have the correct theming.
require_once(dirname(dirname(dirname(__FILE__))) . '/auth/lib.php');
$SESSION = Session::singleton();
$USER    = new LiveUser();
$THEME   = new Theme($USER);
// ***********************************************************************
// END of copied stuff from original init.php
// ***********************************************************************
// restart the session for Mahara
@session_start();

if (!$SESSION->get('wantsurl')) {
    $SESSION->set('wantsurl', preg_replace('/\&login$/', '', $wantsurl));
}

// now start the hunt for the associated authinstance for the organisation attached to the saml_attributes
global $instance;
$instance = auth_saml_find_authinstance($saml_attributes);

// if we don't have an auth instance then this is a serious failure
if (!$instance) {
    throw new UserNotFoundException(get_string('errorbadinstitution','auth.saml'));
}

// stash the existing logged in user - if we have one
$current_user = $USER;
$is_loggedin = $USER->is_logged_in();

// check the instance and do a test login
$can_login = false;
try {
    $auth = new AuthSaml($instance->id);
    $can_login = $auth->request_user_authorise($saml_attributes);
}
catch (AccessDeniedException $e) {
    throw new UserNotFoundException(get_string('errnosamluser','auth.saml'));
}
catch (XmlrpcClientException $e) {
    throw new AccessDeniedException($e->getMessage());
}
catch (AuthInstanceException $e) {
    throw new AccessDeniedException(get_string('errormissinguserattributes', 'auth.saml'));
}

// if we can login with SAML - then let them go
if ($can_login) {
    // they are logged in, so they dont need to be here
    if ($SESSION->get('wantsurl')) {
        $wantsurl = $SESSION->get('wantsurl');
        $SESSION->set('wantsurl', null);
    }
    session_write_close();
    redirect($wantsurl);
}

// are we configured to allow testing of local login and linking?
$loginlink = get_field('auth_instance_config', 'value', 'field', 'loginlink', 'instance', $instance->id);
if (empty($loginlink)) {
    throw new UserNotFoundException(get_string('errnosamluser','auth.saml'));
}

// used in the submit callback for auth_saml_loginlink_screen()
global $remoteuser;
$user_attribute = get_field('auth_instance_config', 'value', 'field', 'user_attribute', 'instance', $instance->id);
$remoteuser = $saml_attributes[$user_attribute][0];

// is the local account already logged in or can the SAML auth succeed - if not try to get
// them to log in local/manual
if (!$is_loggedin) {
    // cannot match user account - so offer them the login-link/register page
    // if we can't login locally, and cant login via SAML then we should offer to register - but this should probably appear on the local login page anyway
    auth_saml_login_screen($remoteuser);
}
else {
    // if we can login locally, but can't login with SAML then we offer to link the accounts SAML -> local one
    auth_saml_loginlink_screen($remoteuser, $current_user->username);
}

exit(0);


/**
 * callback for linking local account with remote SAML account
 *
 * @param Pieform $form
 * @param array $values
 */
function auth_saml_loginlink_submit(Pieform $form, $values) {
    global $USER, $instance, $remoteuser;

    // create the new account linking
    db_begin();
    delete_records('auth_remote_user', 'authinstance', $instance->id, 'localusr', $USER->id);
    insert_record('auth_remote_user', (object) array(
        'authinstance'   => $instance->id,
        'remoteusername' => $remoteuser,
        'localusr'       => $USER->id,
    ));
    db_commit();
    session_write_close();
    redirect('/auth/saml/');
}


/**
 * Find the connected authinstance for the organisation attached to this SAML account
 *
 * @param array $saml_attributes
 *
 * @return object authinstance record
 */
function auth_saml_find_authinstance($saml_attributes) {
// find the one (it should be only one) that has the right field, and the right field value for institution
    $instance = false;
    $institutions = array();

    // find all the possible institutions/auth instances of type saml
    $instances = recordset_to_array(get_recordset_sql("SELECT * FROM {auth_instance_config} aic, {auth_instance} ai WHERE ai.id = aic.instance AND ai.authname = 'saml' AND aic.field = 'institutionattribute'"));
    foreach ($instances as $row) {
        $institutions[]= $row->instance.':'.$row->institution.':'.$row->value;
        if (isset($saml_attributes[$row->value])) {
            // does this institution use a regex match against the institution check value?
            if ($configvalue = get_record('auth_instance_config', 'instance', $row->instance, 'field', 'institutionregex')) {
                $is_regex = (boolean) $configvalue->value;
            }
            else {
                $is_regex = false;
            }
            if ($configvalue = get_record('auth_instance_config', 'instance', $row->instance, 'field', 'institutionvalue')) {
                $institution_value = $configvalue->value;
            }
            else {
                $institution_value = $row->institution;
            }

            if ($is_regex) {
                foreach ($saml_attributes[$row->value] as $attr) {
                    if (preg_match('/'.trim($institution_value).'/', $attr)) {
                        $instance = $row;
                        break;
                    }
                }
            }
            else {
                foreach ($saml_attributes[$row->value] as $attr) {
                    if ($attr == $institution_value) {
                        $instance = $row;
                        break;
                    }
                }
            }
        }
    }
    return $instance;
}


/**
 * present the login-link screen where users are asked if they want to link
 * the current loggedin local account to the remote saml one
 *
 * @param string $remoteuser
 * @param string $currentuser
 */
function auth_saml_loginlink_screen($remoteuser, $currentuser) {
    require_once('pieforms/pieform.php');
    $form = array(
        'name'           => 'loginlink',
        'renderer'       => 'div',
        'successcallback'  => 'auth_saml_loginlink_submit',
        'method'         => 'post',
        'plugintype'     => 'auth',
        'pluginname'     => 'saml',
        'elements'       => array(
                    'linklogins' => array(
                        'value' => '<div><b>' . get_string('linkaccounts', 'auth.saml', $remoteuser, $currentuser) . '</b></div><br/>'
                    ),
                    'submit' => array(
                        'type'  => 'submitcancel',
                        'value' => array(get_string('link','auth.saml'), get_string('cancel')),
                        'goto'  => get_config('wwwroot'),
                    ),
                    'link_submitted' => array(
                        'type'  => 'hidden',
                        'value' => 1
                    ),
                ),
        'dieaftersubmit' => false,
        'iscancellable'  => true
    );
    $form = new Pieform($form);
    $smarty = smarty(array(), array(), array(), array('pagehelp' => false, 'sidebars' => false));
    $smarty->assign('form', $form->build());
    $smarty->assign('PAGEHEADING', get_string('link', 'auth.saml'));
    $smarty->display('form.tpl');
    exit;
}


/**
 * present the login screen for login-linking
 *
 * @param string $remoteuser
 */
function auth_saml_login_screen($remoteuser) {
    require_once('pieforms/pieform.php');
    $smarty = smarty(array(), array(), array(), array('pagehelp' => false, 'sidebars' => false));
    $smarty->assign('pagedescriptionhtml', get_string('logintolinkdesc', 'auth.saml', $remoteuser, get_config('sitename')));
    $smarty->assign('form', '<div id="loginform_container"><noscript><p>{str tag="javascriptnotenabled"}</p></noscript>'.auth_generate_login_form());
    $smarty->assign('PAGEHEADING', get_string('logintolink', 'auth.saml', get_config('sitename')));
    $smarty->assign('LOGINPAGE', true);
    $smarty->display('form.tpl');
    exit;
}
