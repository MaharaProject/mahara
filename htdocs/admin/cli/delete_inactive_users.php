<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('CLI', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('docroot') . 'auth/lib.php');
require(get_config('libroot') . 'cli.php');

$cli = get_cli();

$options = array();
$options['dryrun'] = (object) array(
        'shortoptions' => array('d'),
        'description' => get_string('cli_param_dryrun', 'admin'),
        'required' => false,
        'defaultvalue' => true,
);
$options['institution'] = (object) array(
        'shortoptions' => array('i'),
        'description' => get_string('institutionshortname', 'admin'),
        'required' => false,
        'defaultvalue' => false,
        'examplevalue' => 'mahara',
);
$options['group'] = (object) array(
        'shortoptions' => array('g'),
        'description' => get_string('groupid', 'admin'),
        'required' => false,
        'defaultvalue' => false,
        'examplevalue' => '123',
);
$options['beforedate'] = (object) array(
        'shortoptions' => array('b'),
        'description' => get_string('cli_deleteinactiveusers_beforedate', 'admin'),
        'required' => false,
        'defaultvalue' => false,
);
$options['limit'] = (object) array(
        'shortoptions' => array('l'),
        'description' => get_string('cli_deleteinactiveusers_limit', 'admin'),
        'required' => false,
        'defaultvalue' => 100,
);
define('CLI_DELETEINACTIVE_CLEANUSERS_DEFAULT', -1);
$options['cleanusers'] = (object) array(
        'shortoptions' => array('c'),
        'description' => get_string('cli_deleteinactiveusers_cleanusers', 'admin'),
        'required' => false,
        'defaultvalue' => CLI_DELETEINACTIVE_CLEANUSERS_DEFAULT,
);
define('CLI_DELETEINACTIVE_NEVERLOGGEDIN_DEFAULT', -1);
$options['neverloggedin'] = (object) array(
        'shortoptions' => array('n'),
        'description' => get_string('cli_deleteinactiveusers_neverloggedin', 'admin'),
        'required' => false,
        'defaultvalue' => CLI_DELETEINACTIVE_NEVERLOGGEDIN_DEFAULT,
);

$settings = (object) array(
        'info' => get_string('cli_deleteinactiveusers_info', 'admin'),
        'options' => $options,
);
$cli->setup($settings);

$dryrun = $cli->get_cli_param_boolean('dryrun');
$institution = $cli->get_cli_param('institution');
$group = $cli->get_cli_param('group');
// Retrieve & validate the before date
$beforedate = $cli->get_cli_param('beforedate');
if ($beforedate) {
    if (strtotime($beforedate)) {
        $beforedate = date('Y-m-d H:i:s', strtotime($beforedate));
    }
    else {
        $cli->cli_exit(get_string('cli_param_baddate', 'admin', $beforedate), true);
    }
}

// Determine whether or not to clean up all user data incl info from forum posts / usr table.
if ($cli->get_cli_param('cleanusers') === CLI_DELETEINACTIVE_CLEANUSERS_DEFAULT) {
    // The default behavior
    $cleanusers = false;
}
else {
    // If they specified the cleanusers param, we respect that
    $cleanusers = $cli->get_cli_param_boolean('cleanusers');
}
// Determine whether or not to only delete users that have never logged in.
if ($cli->get_cli_param('neverloggedin') === CLI_DELETEINACTIVE_NEVERLOGGEDIN_DEFAULT) {
    // The default behavior
    $neverloggedin = true;
}
else {
    // If they specified the neverloggedin param, we respect that
    $neverloggedin = $cli->get_cli_param_boolean('neverloggedin');
}


// Find all the users we need to deal with based on the params provided
$selectsql = "SELECT u.id, u.username, u.lastlogin FROM {usr} u";
$joinsql = "";
$wheresql = " WHERE u.id != 0";
$values = array();

if ($institution) {
    $joinsql .= " JOIN {usr_institution} ui ON ui.usr = u.id";
    $wheresql .= " AND ui.institution = ?";
    $values[] = $institution;
}
if ($group) {
    $joinsql .= " JOIN {group_member} gm ON gm.member = u.id";
    $wheresql .= " AND gm.group = ?";
    $values[] = $group;
}
if ($beforedate && $neverloggedin) {
    $wheresql .= " AND (u.lastlogin < DATE(?) OR u.lastlogin IS NULL)";
    $values[] = $beforedate;
}
else if ($beforedate && !$neverloggedin) {
    $wheresql .= " AND u.lastlogin < DATE(?)";
    $values[] = $beforedate;
}
else if ($neverloggedin) {
    $wheresql .= " AND u.lastlogin IS NULL";
}

if (!$beforedate && !$neverloggedin) {
    // If we do not set a before date and / or never logged in flag then we are only using this script to
    // try and clean up the deleted users from the 'usr' table
    $wheresql .= " AND u.deleted = 1";
}
else if (!$cleanusers) {
    $wheresql .= " AND u.deleted = 0";
}

if ($dryrun) {
    $cli->cli_print(get_string('cli_deleteinactiveusers_onlydryrun1', 'admin', $institution, $group, $beforedate, $cleanusers, $neverloggedin));
}

if ($records = get_records_sql_array($selectsql . $joinsql . $wheresql, $values)) {
    $total = count($records);
    $cli->cli_print(get_string('cli_deleteinactiveusers_usercount', 'admin', $total));
    // We will need to batch things so the database/site doesn't stress out
    $count = 0;
    $limit = $cli->get_cli_param('limit');
    if (!$dryrun) {
        $cli->cli_print("--- " . date('Y-m-d H:i:s', time()) . " ---");
        foreach ($records as $record) {
            try {
                $DB_IGNORE_SQL_EXCEPTIONS = true;
                delete_user($record->id);
                $DB_IGNORE_SQL_EXCEPTIONS = false;
            }
            catch (SQLException $e) {
                $cli->cli_print(get_string('cli_deleteinactiveusers_userunabletodelete', 'admin', $record->username, $record->id));
            }
            if ($cleanusers) {
                try {
                    $DB_IGNORE_SQL_EXCEPTIONS = true;
                    delete_records('usr', 'id', $record->id);
                    $DB_IGNORE_SQL_EXCEPTIONS = false;
                }
                catch (SQLException $e) {
                    $cli->cli_print(get_string('cli_deleteinactiveusers_userunabletoclean', 'admin', $record->username, $record->id));
                }
            }
            $count++;
            if (($count % $limit) == 0 || $count == $total) {
                $cli->cli_print("$count/$total");
                set_time_limit(120);
            }
        }
        $cli->cli_print("--- " . date('Y-m-d H:i:s', time()) . " ---");
    }
    else {
        $verbose = $cli->get_cli_setting_value('verbose');
        if ($verbose) {
            $file_content = generate_csv($records, array('id','username','lastlogin'));
            $filename = 'delete-inactive-users.csv';
            $dir = get_config('dataroot') . 'temp/';
            if (check_dir_exists($dir) && file_put_contents($dir . $filename, $file_content)) {
                $cli->cli_print("Saved CSV file to " . $dir . $filename);
            }
            else {
                $cli->cli_print($file_content);
            }
        }
    }
}
else {
    $cli->cli_print(get_string('cli_deleteinactiveusers_nouserstodelete', 'admin'));
}
$cli->cli_exit(get_string('done'));