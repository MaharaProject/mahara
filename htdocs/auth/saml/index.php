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
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
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
define('SAML_RETRIES', 5);

global $CFG, $USER, $SESSION;

// do our own partial initialisation so that we can get at the config
// this version of init.php has the user session initiation stuff ripped out
// this is because SimpleSAMLPHP does all kinds of things with the PHP session
// handling including changing the cookie names etc.
require(dirname(__FILE__) . '/init.php');

// get the config pointing to the SAML library - and load it
$samllib = get_config_plugin('auth', 'saml', 'simplesamlphplib');
if (null === $samllib) {
    exit(0);
}
require_once($samllib.'/lib/_autoload.php');

// point at the configured config directory
$samlconfig = get_config_plugin('auth', 'saml', 'simplesamlphpconfig');

// get all the things that we will need from the SAML authentication
// and then shutdown the session control
SimpleSAML_Configuration::init($samlconfig);
$saml_session = SimpleSAML_Session::getInstance();

// do we have a logout request?
if(param_variable("logout", false)) {
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
$saml_config = SimpleSAML_Configuration::getInstance();
$valid_saml_session = $saml_session->isValid($sp);

// do we have a returnto URL ?
$wantsurl = param_variable("wantsurl", false);
if($wantsurl) {
    $_SESSION['wantsurl'] = $wantsurl;
}
else if (isset($_SESSION['wantsurl'])) {
    $wantsurl = $_SESSION['wantsurl'];
}
else if (! $saml_session->getIdP()){
    $_SESSION['wantsurl'] = array_key_exists('HTTP_REFERER',$_SERVER) ? $_SERVER['HTTP_REFERER'] : $CFG->wwwroot;
    $wantsurl = $_SESSION['wantsurl'];
}
else {
    $wantsurl = $CFG->wwwroot;
}

// taken from Moodle clean_param
include_once('validateurlsyntax.php');
if (!validateUrlSyntax($wantsurl, 's?H?S?F?E?u-P-a?I?p?f?q?r?')) {
    $wantsurl = $CFG->wwwroot;
}

// now - are we logged in?
$as->requireAuth();

// ensure that $_SESSION is cleared
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
// The installer does its own auth_setup checking, because some upgrades may
// break logging in and so need to allow no logins.
if (!defined('INSTALLER')) {
    auth_setup();
}

if (get_config('siteclosed')) {
    if ($USER->admin) {
        if (get_config('disablelogin')) {
            $USER->logout();
        }
        else if (!defined('INSTALLER')) {
            redirect('/admin/upgrade.php');
        }
    }
    if (!$USER->admin) {
        if ($USER->is_logged_in()) {
            $USER->logout();
        }
        if (!defined('HOME') && !defined('INSTALLER')) {
            redirect();
        }
    }
}

// check to see if we're installed...
if (!get_config('installed')) {
    ensure_install_sanity();

    $scriptfilename = str_replace('\\', '/', $_SERVER['SCRIPT_FILENAME']);
    if (false === strpos($scriptfilename, 'admin/index.php')
    && false === strpos($scriptfilename, 'admin/upgrade.php')
    && false === strpos($scriptfilename, 'admin/upgrade.json.php')) {
        redirect('/admin/');
    }
}

if (defined('JSON') && !defined('NOSESSKEY')) {
    $sesskey = param_variable('sesskey', null);
    global $USER;
    if ($sesskey === null || $USER->get('sesskey') != $sesskey) {
        $USER->logout();
        json_reply('global', get_string('invalidsesskey'), 1);
    }
}
// ***********************************************************************
// END of copied stuff from original init.php
// ***********************************************************************


// restart the session for Mahara
@session_start();

require_once(get_config('docroot') .'auth/saml/lib.php');
require_once(get_config('libroot') .'institution.php');

// if the user is not logged in, then lets start it going 
if(!$USER->is_logged_in()) {
    simplesaml_init($saml_config, $valid_saml_session, $saml_attributes, $as);
}
// they are logged in, so they dont need to be here 
//    header('Location: '.$CFG->wwwroot);
redirect($wantsurl);
    

/**
 * check the validity of the users current SAML 2.0 session
 * if its bad, force log them out of Mahara, and redirect them to the IdP
 * if it's good, find an applicable saml auth instance, and try logging them in with it
 * passing in the attributes found from the IdP 
 *
 * @param object $saml_config saml configuration object
 * @param boolean $valid_saml_session is there a valid saml2 session
 * @param array $saml_attributes saml attributes passed in by the IdP
 * @param object $as new saml user object
 * @return nothing
 */
function simplesaml_init($saml_config, $valid_saml_session, $saml_attributes, $as) {
    global $CFG, $USER, $SESSION;
    
//    $idp = get_config_plugin('auth', 'saml', 'idpidentity');
    $retry = $SESSION->get('retry'); 
    if ($retry > SAML_RETRIES) {
        throw new AccessTotallyDeniedException(get_string('errorretryexceeded','auth.saml', $retry));
    }
    else if (!$valid_saml_session) { # 
        if ($USER->is_logged_in()) {
            $USER->logout();
        }
        $SESSION->set('messages', array());
        $SESSION->set('retry', $retry + 1);
        // not valid session. Ship user off to the Identity Provider
        $as->requireAuth();
    } else {
        // find all the possible institutions/auth instances
        $instances = recordset_to_array(get_recordset_sql("SELECT * FROM {auth_instance_config} aic, {auth_instance} ai WHERE ai.id = aic.instance AND ai.authname = 'saml' AND aic.field = 'institutionattribute'"));
        
        // find the one (it should be only one) that has the right field, and the right field value for institution
        $instance = false;
        $institutions = array();
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
        if (!$instance) {
            log_warn("auth/saml: could not find an authinstance from: " . join(",  ", $institutions));
            log_warn("auth/saml: could not find the saml institutionattribute for user: ".var_export($saml_attributes, true));
            throw new UserNotFoundException(get_string('errorbadinstitution','auth.saml'));
        }
        try {
            $auth = new AuthSaml($instance->id);
            if ($auth->request_user_authorise($saml_attributes)) {
                session_write_close();
            }
            else {
                 throw new UserNotFoundException(get_string('errnosamluser','auth.saml'));
            }
        } catch (AccessDeniedException $e) {
            throw new UserNotFoundException(get_string('errnosamluser','auth.saml'));
        }
    }
}
