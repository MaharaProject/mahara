<?php
/**
 *
 * @package    mahara
 * @subpackage test
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * This is a shell script that will verify that you haven't incorrectly changed the version
 * number in lib/version.php.
 *
 * It is meant to be used as part of the Makefile's "minaccept" task.
 *
 * It assumes that PHP library versions take the format YYYYMMDDXX, where YYYYMMDD is the date in 8-digit format,
 * and XX is a sequential integer starting with 0.
 *
 * In a stable branch, the YYYYMMDD is frozen and only the XX should increment.
 *
 * See https://wiki.mahara.org/index.php/Developer_Area/Version_Numbering_Policy#Version_bumps_and_database_upgrades
 */
error_reporting(0);
define('INTERNAL', 1);
require('htdocs/lib/version.php');

$error = false;

validate_version('htdocs/lib/version.php', 'htdocs/lib/db/upgrade.php');

// Check versions of plugins
require_once('htdocs/lib/mahara.php');
$p = popen('git diff-tree --no-commit-id --name-only -r HEAD', 'r');
$updates = array();
$localupdated = false;
while (!feof($p)) {
    $buffer = trim(fgets($p));
    if (
            preg_match('#htdocs/([a-z]+)/([a-z]+)/version.php#', $buffer, $matches)
            && in_array($matches[1], plugin_types())
    ) {
        $updates["htdocs/{$matches[1]}/{$matches[2]}"] = true;
        continue;
    }

    if (
            preg_match('#htdocs/([a-z]+)/([a-z]+)/db/upgrade.php#', $buffer, $matches)
            && in_array($matches[1], plugin_types())
    ) {
        $updates["htdocs/{$matches[1]}/{$matches[2]}"] = true;
        continue;
    }

    if (preg_match('#htdocs/artefact/([a-z]+)/blocktype/([a-z]+)/version.php#', $buffer, $matches)) {
        $updates["htdocs/artefact/{$matches[1]}/blocktype/{$matches[2]}"] = true;
        continue;
    }

    if (preg_match('#htdocs/artefact/([a-z]+)/blocktype/([a-z]+)/db/upgrade.php#', $buffer, $matches)) {
        $updates["htdocs/artefact/{$matches[1]}/blocktype/{$matches[2]}"] = true;
        continue;
    }

    if (preg_match('#htdocs/local/version.php#', $buffer, $matches)) {
        $localconfig = get_mahara_version('HEAD', $buffer);
        if ($localconfig->version !== 0) {
            echo "ERROR: You should not update the version number in htdocs/local/version.php.\n";
            $error = true;
        }
        continue;
    }

    if (preg_match('#htdocs/local/upgrade.php#', $buffer, $matches)) {
        $localupdated = true;
        continue;
    }
}
pclose($p);

var_dump($updates);

// Find any version.php or upgrade.php files that have changed
foreach (array_keys($updates) as $dir) {
    validate_version("$dir/version.php", "$dir/db/upgrade.php");
}


/**
 * Find out the version number in a particular version.php file at a particular git revision
 * @param string $gitversion
 * @param string $pathtofile
 */
function get_mahara_version($gitrevision, $pathtofile, $missingokay = true) {
    global $error;

    exec("git show {$gitrevision}:{$pathtofile}", $lines, $returnval);
    if ($returnval !== 0) {
        $config = new stdClass();
        $config->version = 0;
        return $config;
    }

    array_shift($lines);
    eval(implode("\n", $lines));
    return $config;
}


function find_upgrade_versions($gitrevision, $upgradefile) {
    // If they added new code to lib/db/upgrade.php, make sure the last block in it matches the new version number
    $p = popen("git show {$gitrevision} -- {$upgradefile}", 'r');
    $upgradeversions = array();
    while (!feof($p)) {
        $buffer = fgets($p);
        if (1 == preg_match('#\$oldversion.*\b(\d{10})\b#', $buffer, $matches)) {
            echo "New {$upgradefile}: {$matches[1]}\n";
            $upgradeversions[] = $matches[1];
        }
    }
    pclose($p);
    return $upgradeversions;
}


function validate_version($versionfile, $upgradefile) {
    global $error;

    $newconfig = get_mahara_version('HEAD', $versionfile);
    $oldconfig = get_mahara_version('HEAD~', $versionfile);

    if ($oldconfig->version != $newconfig->version) {
        echo "Bumping {$versionfile}...\n";
        echo "Old version: {$oldconfig->version}\n";
        echo "New version: {$newconfig->version}\n";
    }

    if ($newconfig->version < $oldconfig->version) {
        echo "ERROR: Version number in {$versionfile} has decreased!\n";
        $error = true;
    }

    // Determine if we're on a stable branch or not.
    if (substr($newconfig->release, -3) == 'dev') {
        $stablebranch = false;
    }
    else {
        $stablebranch = true;
    }

    if (strlen($newconfig->version) != 10) {
        echo "ERROR: Version number in {$versionfile} should be exactly 10 digits.\n";
        $error = true;
    }
    else if ($stablebranch && $oldconfig->version !== 0 && substr($newconfig->version, 0, 8) > substr($oldconfig->version, 0, 8)) {
        echo "ERROR: Version number in {$versionfile} has gone up too much for a stable branch!\n";
        $error = true;
    }

    // If they added new code to lib/db/upgrade.php, make sure the last block in it matches the new version number
    if ($newconfig->version != $oldconfig->version) {
        $upgradeversions = find_upgrade_versions('HEAD', $upgradefile);

        $lastv = $oldconfig->version;
        foreach($upgradeversions as $v) {
            if ($v <= $lastv) {
                echo "ERROR: {$upgradefile} section number {$v} not incremented correctly.\n";
                $error = true;
            }
            $lastv = $v;
        }

        if ($upgradeversions && end($upgradeversions) != $newconfig->version) {
            echo "ERROR: Version in {$versionfile} should match version of last new section in {$upgradefile}\n";
            $error = true;
        }
    }
}

if ($error) {
    die(1);
}