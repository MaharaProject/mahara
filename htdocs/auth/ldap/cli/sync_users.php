<?php
/**
 *
 * @package    mahara
 * @subpackage auth-ldap
 * @author     Patrick Pollet <pp@patrickpollet.net>
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2011 INSA de Lyon France
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

/**
 * This is an OPTIONAL command-line script, to be used if you want more detailed control over the
 * ldap sync process than what you get from the standard ldap sync cron task. If you use this script,
 * you will probably also want to disable the standard ldap sync cron task by deleting its entry from
 * the auth_cron table in the database.
 *
 * The purpose of this CLI script is to be run as a cron job to synchronize Mahara' users
 * with users defined on a LDAP server
 *
 * This script requires at least a parameter the name of the target institution
 * in which users will be created/updated.
 * An instance of LDAP or CAS auth plugin MUST have been added to this institution
 * for this script to retrieve LDAP parameters
 * It is possible to run this script for several institutions
 *
 * For the synchronisation of group membership , this script MUST be run before
 * the mahara_sync_groups script
 *
 * This script is strongly inspired of synching Moodle's users with LDAP
 */


define('INTERNAL', 1);
define('ADMIN', 1);
define('INSTALLER', 1);
define('CLI', 1);

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
require(get_config('libroot') . 'cli.php');
require(get_config('docroot') . 'auth/ldap/lib.php');

require(get_config('libroot') . 'institution.php');
require(get_config('libroot') . 'group.php');
require_once(get_config('docroot') . 'auth/ldap/lib.php');
require_once(dirname(dirname(__FILE__))) . '/lib.php';
require_once(get_config('docroot') . 'artefact/lib.php');

$cli = get_cli();

$options = array();

$options['institution'] = new stdClass();
$options['institution']->examplevalue = '\'my institution\'';
$options['institution']->shortoptions = array('i');
$options['institution']->description = get_string('institutionname', 'auth.ldap');
$options['institution']->required = true;

$options['contexts'] = new stdClass();
$options['contexts']->examplevalue = '\'ou=pc,dc=insa-lyon,dc=fr[;anothercontext]\'';
$options['contexts']->shortoptions = array('c');
$options['contexts']->description = get_string('searchcontexts', 'auth.ldap');
$options['contexts']->required = false;
$options['contexts']->defaultvalue = -1;

$options['searchsub'] = new stdClass();
$options['searchsub']->examplevalue = '0';
$options['searchsub']->shortoptions = array('s');
$options['searchsub']->description = get_string('searchsubcontextscliparam', 'auth.ldap');
$options['searchsub']->required = false;
$options['searchsub']->defaultvalue = -1;

$options['extrafilterattribute'] = new stdClass();
$options['extrafilterattribute']->examplevalue = 'eduPersonAffiliation=member';
$options['extrafilterattribute']->shortoptions = array('f');
$options['extrafilterattribute']->description = get_string('extrafilterattribute', 'auth.ldap');
$options['extrafilterattribute']->required = false;
$options['extrafilterattribute']->defaultvalue = -1;

$options['doupdate'] = new stdClass();
$options['doupdate']->shortoptions = array('u');
$options['doupdate']->description = get_string('doupdate', 'auth.ldap');
$options['doupdate']->required = false;
$options['doupdate']->defaultvalue = -1;

$options['nocreate'] = new stdClass();
$options['nocreate']->shortoptions = array('n');
$options['nocreate']->description = get_string('nocreate', 'auth.ldap');
$options['nocreate']->required = false;
$options['nocreate']->defaultvalue = -1;

$options['dosuspend'] = new stdClass();
$options['dosuspend']->shortoptions = array('p');
$options['dosuspend']->description = get_string('dosuspend', 'auth.ldap');
$options['dosuspend']->required = false;
$options['dosuspend']->defaultvalue = -1;

$options['dodelete'] = new stdClass();
$options['dodelete']->shortoptions = array('d');
$options['dodelete']->description = get_string('dodelete', 'auth.ldap');
$options['dodelete']->required = false;
$options['dodelete']->defaultvalue = -1;

$options['dryrun'] = new stdClass();
$options['dryrun']->description = get_string('dryrun', 'auth.ldap');
$options['dryrun']->required = false;
$options['dryrun']->defaultvalue = false;

$settings = new stdClass();
$settings->options = $options;
$settings->info = get_string('cli_info_sync_users', 'auth.ldap');

$cli->setup($settings);

try {
    $institutionname = $cli->get_cli_param('institution');
    $extrafilterattribute = $cli->get_cli_param('extrafilterattribute');

    // Overriding the value from config-defaults for this script
    $CFG->auth_ldap_debug_sync_cron = $cli->get_cli_param('verbose');
    $onlycontexts = $cli->get_cli_param('contexts');
    $searchsub = $cli->get_cli_param('searchsub');

    $doupdate = $cli->get_cli_param('doupdate');
    $nocreate = $cli->get_cli_param('nocreate');
    $dosuspend = $cli->get_cli_param('dosuspend');
    $dodelete = $cli->get_cli_param('dodelete');
    $dryrun = $cli->get_cli_param('dryrun');

    if ($dosuspend == -1 && $dodelete == -1) {
        $tomissing = null;
    }
    else {
        $tomissing = '';
        if ($dosuspend && $dodelete) {
            throw new ParameterException (get_string('cannotdeleteandsuspend', 'auth.ldap'));
        }
        else if ($dosuspend) {
            $tomissing = 'suspend';
        }
        else if ($dodelete) {
            $tomissing = 'delete';
        }
    }

    auth_ldap_sync_users(
        $institutionname,
        ($onlycontexts == -1 ? null : $onlycontexts),
        ($searchsub == -1 ? null : $searchsub),
        ($extrafilterattribute == -1 ? null : $extrafilterattribute),
        ($doupdate == -1 ? null : $doupdate),
        ($nocreate == -1 ? null : !$nocreate),
        $tomissing
    );
}
// we catch missing parameter and unknown institution
catch (Exception $e) {
    $USER->logout(); // important
    cli::cli_exit($e->getMessage(), true);
}

$USER->logout(); // important
cli::cli_exit('---------- ended at ' . date('r', time()) . ' ----------', true);




