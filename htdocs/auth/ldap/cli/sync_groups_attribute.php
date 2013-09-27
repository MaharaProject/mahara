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
 * The purpose of this CLI script is to be run as a cron job to synchronize Mahara's groups
 * with users having some attribute set on a LDAP server
 *
 * This script requires at least a single parameter the name of the target institution
 * in which groups will be created/updated.
 * An instance of LDAP or CAS auth plugin MUST have been added to this institution
 * for this script to retrieve LDAP parameters
 * It is possible to run this script for several institutions
 *
 * For the synchronisation of group members , this script MUST be run after
 * the mahara_sync_users script
 *
 * This script is strongly inspired of synching Moodle's cohorts with LDAP groups
 * as described here : http://moodle.org/mod/forum/discuss.php?d=160751
 *
 * Sample cron entry:
 * # 5 minutes past 4am
 * 5 4 * * * $sudo -u www-data /usr/bin/php /var/www/mahara/local/ldap/cli/mahara_sync_groups_attribute.php -i='my institution'
 *
 * Notes:
 *   - run this script on command line without any paramters to get help on all options
 *   - it is required to use root or the the web server accounts when executing PHP CLI scripts
 *   - If you have a large number of groups/users, you may want to raise the memory limits
 *     by passing -d memory_limit=256M
 *   - For debugging & better logging, you are encouraged to use in the command line:
 *     -d log_errors=1 -d error_reporting=E_ALL -d display_errors=0 -d html_errors=0
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('INSTALLER', 1);
define('CLI', 1);

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
require(get_config('libroot') . 'cli.php');
require(get_config('docroot') . 'auth/ldap/lib.php');

// must be done before any output
$USER->reanimate(1, 1);

require(get_config('libroot') . 'institution.php');
require(get_config('libroot') . 'group.php');
require(get_config('libroot') . 'searchlib.php');
require_once(dirname(dirname(__FILE__))) . '/lib.php';

$cli = get_cli();

$options = array();

$options['institution'] = new stdClass();
$options['institution']->examplevalue = '\'my institution\'';
$options['institution']->shortoptions = array('i');
$options['institution']->description = get_string('institutionname', 'auth.ldap');
$options['institution']->required = true;

$options['attribute'] = new stdClass();
$options['attribute']->examplevalue = '\'eduPersonAffiliation\'';
$options['attribute']->shortoptions = array('a');
$options['attribute']->description = get_string('attributename', 'auth.ldap');
$options['attribute']->required = false;
$options['attribute']->defaultvalue = -1;

$options['exclude'] = new stdClass();
$options['exclude']->examplevalue = '\'repository*;cipc-*[;another reg. exp.]\'';
$options['exclude']->shortoptions = array('x');
$options['exclude']->description = get_string('excludelist', 'auth.ldap');
$options['exclude']->required = false;
$options['exclude']->defaultvalue = -1;

$options['include'] = new stdClass();
$options['include']->examplevalue = '\'repository*;cipc-*[;another reg. exp.]\'';
$options['include']->shortoptions = array('o');
$options['include']->description = get_string('includelist', 'auth.ldap');
$options['include']->required = false;
$options['include']->defaultvalue = -1;

$options['contexts'] = new stdClass();
$options['contexts']->examplevalue = '\'ou=students,ou=pc,dc=insa-lyon,dc=fr[;anothercontext]\'';
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

$options['grouptype'] = new stdClass();
$options['grouptype']->examplevalue = 'course|standard';
$options['grouptype']->shortoptions = array('t');
$options['grouptype']->description = get_string('grouptype', 'auth.ldap');
$options['grouptype']->required = false;
$options['grouptype']->defaultvalue = -1;

$options['nocreate'] = new stdClass();
$options['nocreate']->shortoptions = array('n');
$options['nocreate']->description = get_string('nocreatemissinggroups', 'auth.ldap');
$options['nocreate']->required = false;
$options['nocreate']->defaultvalue = -1;

$options['dryrun'] = new stdClass();
$options['dryrun']->description = get_string('dryrun', 'auth.ldap');
$options['dryrun']->required = false;


$settings = new stdClass();
$settings->options = $options;
$settings->info = get_string('cli_info_sync_groups_attribute', 'auth.ldap');

$cli->setup($settings);


try {
    //REQUIRED
    $institutionname = $cli->get_cli_param('institution');
    $attributename = $cli->get_cli_param('attribute');
    //OPTIONALS
    $excludelist = $cli->get_cli_param('exclude');
    $includelist = $cli->get_cli_param('include');
    // Overriding the value from config-defaults for this script run
    $CFG->auth_ldap_debug_sync_cron = $cli->get_cli_param('verbose');
    $onlycontexts = $cli->get_cli_param('contexts');
    $searchsub = $cli->get_cli_param('searchsub');
    $grouptype = $cli->get_cli_param('grouptype');
    // If they supplied a grouptype, only allow it to be 'course' or 'standard'
    if ($grouptype != -1) {
        if ($grouptype !== 'course') {
            $grouptype = 'standard';
        }
    }
    $nocreate = $cli->get_cli_param('nocreate');
    $dryrun = $cli->get_cli_param('dryrun');

    auth_ldap_sync_groups(
        $institutionname,
        false,
        ($excludelist == -1 ? null : $excludelist),
        ($includelist == -1 ? null : $includelist),
        ($onlycontexts == -1 ? null : $onlycontexts),
        ($searchsub == -1 ? null : $searchsub),
        ($grouptype == -1 ? null : $grouptype),
        ($nocreate == -1 ? null : !$nocreate),
        null,
        null,
        null,
        true,
        ($attributename == -1 ? null : $attributename),
        null,
        $dryrun
    );
}
// we catch missing parameter and unknown institution
catch (Exception $e) {
    $USER->logout(); // important
    cli::cli_exit($e->getMessage(), true);
}

$USER->logout(); // important
cli::cli_exit('---------- ended at ' . date('r', time()) . ' ----------', true);
