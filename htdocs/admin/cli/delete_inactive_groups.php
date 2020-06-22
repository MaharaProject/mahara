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
$options['beforedate'] = (object) array(
        'shortoptions' => array('b'),
        'description' => get_string('cli_deleteinactivegroups_beforedate', 'admin'),
        'required' => false,
        'defaultvalue' => false,
);
$options['limit'] = (object) array(
        'shortoptions' => array('l'),
        'description' => get_string('cli_deleteinactivegroups_limit', 'admin'),
        'required' => false,
        'defaultvalue' => 100,
);

// Unable to fully delete group at this stage because each group has an interaction_instance row on creation
// but does not get deleted on group deletion, but rather is marked as deleted. If this changes in the future
// then cleanup will work as expected
define('CLI_DELETEINACTIVE_CLEANGROUPS_DEFAULT', -1);
$options['cleangroups'] = (object) array(
        'shortoptions' => array('c'),
        'description' => get_string('cli_deleteinactivegroups_cleangroups', 'admin'),
        'required' => false,
        'defaultvalue' => CLI_DELETEINACTIVE_CLEANGROUPS_DEFAULT,
);

define('CLI_DELETEINACTIVE_EMPTYGROUPS_DEFAULT', -1);
$options['emptygroups'] = (object) array(
        'shortoptions' => array('e'),
        'description' => get_string('cli_deleteinactivegroups_emptygroups', 'admin'),
        'required' => false,
        'defaultvalue' => CLI_DELETEINACTIVE_EMPTYGROUPS_DEFAULT,
);
define('CLI_DELETEINACTIVE_ONLYADMINS_DEFAULT', -1);
$options['onlyadmins'] = (object) array(
        'shortoptions' => array('n'),
        'description' => get_string('cli_deleteinactivegroups_onlyadmins', 'admin'),
        'required' => false,
        'defaultvalue' => CLI_DELETEINACTIVE_ONLYADMINS_DEFAULT,
);

$settings = (object) array(
        'info' => get_string('cli_deleteinactivegroups_info', 'admin'),
        'options' => $options,
);
$cli->setup($settings);

$dryrun = $cli->get_cli_param_boolean('dryrun');
$institution = $cli->get_cli_param('institution');
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

// Determine whether or not to clean up all group data incl info from forum posts.
if ($cli->get_cli_param('cleangroups') === CLI_DELETEINACTIVE_CLEANGROUPS_DEFAULT) {
    // The default behavior
    $cleangroups = false;
}
else {
    // If they specified the cleangroups param, we respect that
    $cleangroups = $cli->get_cli_param_boolean('cleangroups');
}

// Determine whether or not to deal with groups with no members.
if ($cli->get_cli_param('emptygroups') === CLI_DELETEINACTIVE_EMPTYGROUPS_DEFAULT) {
    // The default behavior
    $emptygroups = true;
}
else {
    // If they specified the emptygroups param, we respect that
    $emptygroups = $cli->get_cli_param_boolean('emptygroups');
}

// Determine whether or not to only delete groups that have never logged in.
if ($cli->get_cli_param('onlyadmins') === CLI_DELETEINACTIVE_ONLYADMINS_DEFAULT) {
    // The default behavior
    $onlyadmins = false;
}
else {
    // If they specified the neverloggedin param, we respect that
    $onlyadmins = $cli->get_cli_param_boolean('onlyadmins');
}

$danger = '';
if (empty($institution) && empty($emptygroups) && empty($beforedate) && empty($onlyadmins)) {
    // Need to specify at least one thing
    $dryrun = true;
    $danger = get_string('cli_deleteinactivegroups_danger', 'admin');
}

// Find all the groups we need to deal with based on the params provided
$selectsql = "SELECT g.id, g.name, 0 AS totaladmins, 0 AS totalmembers, g.mtime, NULL AS member FROM {group} g";
$joinsql = "";
$unionsql = "";
$wheresql = " WHERE g.id != 0";  // dummy where to make things easier
$unionwheresql = "";
$values = array();

if (!$cleangroups) {
    $wheresql .= " AND g.deleted = 0";
}

if ($institution) {
    $wheresql .= " AND g.institution = ?";
    $values[] = $institution;
}

if ($emptygroups) {
    $wheresql .= " AND g.id NOT IN (SELECT g2.group FROM {group_member} g2)";
}

if ($beforedate) {
    $wheresql .= " AND g.mtime < DATE(?)";
    $values[] = $beforedate;
}

if ($emptygroups && $onlyadmins) {
    // We also want to delete groups where only site admins are members
    $selectsql = "SELECT * FROM (" . $selectsql;
    $unionsql .= " UNION
                   SELECT ag.id, ag.name, COUNT(*) AS totalmembers,
                    (SELECT COUNT(*)
                     FROM {group_member} gm2
                     WHERE gm2.group = gm.group
                     AND gm2.role = 'admin') AS totaladmins,
                   ag.mtime,
                   (SELECT gm3.member
                    FROM {group_member} gm3
                    WHERE gm3.group = gm.group
                    LIMIT 1) AS member
                   FROM {group} ag
                   JOIN {group_member} gm ON gm.group = ag.id";
    $unionwheresql = " WHERE ag.id != 0";

    if (!$cleangroups) {
        $unionwheresql .= " AND ag.deleted = 0";
    }

    if ($institution) {
        $unionwheresql .= " AND ag.institution = ?";
        $values[] = $institution;
    }

    if ($beforedate) {
        $unionwheresql .= " AND ag.mtime < DATE(?)";
        $values[] = $beforedate;
    }

    $unionwheresql .= " GROUP BY ag.id, gm.group) AS f
                        WHERE (f.totaladmins <= 1 AND f.totaladmins = f.totalmembers)";
}

if ($dryrun) {
    $cli->cli_print(get_string('cli_deleteinactivegroups_onlydryrun', 'admin', $institution, $beforedate, $cleangroups, $onlyadmins, $danger));
}

if ($records = get_records_sql_array($selectsql . $joinsql . $wheresql . $unionsql . $unionwheresql, $values)) {
    $total = count($records);
    $cli->cli_print(get_string('cli_deleteinactivegroups_groupcount', 'admin', $total));
    // We will need to batch things so the database/site doesn't stress out
    $count = 0;
    $limit = $cli->get_cli_param('limit');
    if (!$dryrun) {
        $cli->cli_print("--- " . date('Y-m-d H:i:s', time()) . " ---");
        foreach ($records as $record) {

            group_delete($record->id, null, null, false);

            if ($cleangroups) {
                try {
                    $DB_IGNORE_SQL_EXCEPTIONS = true;
                    delete_records('group', 'id', $record->id);
                    $DB_IGNORE_SQL_EXCEPTIONS = false;
                }
                catch (SQLException $e) {
                    $cli->cli_print(get_string('cli_deleteinactivegroups_groupunabletoclean', 'admin', $record->name, $record->id));
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
            $file_content = generate_csv($records, array('id','name','totaladmins','totalmembers', 'mtime', 'member'));
            $filename = get_random_key() . '.csv';
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
    $cli->cli_print(get_string('cli_deleteinactivegroups_nogroupstodelete', 'admin'));
}
$cli->cli_exit(get_string('done'));