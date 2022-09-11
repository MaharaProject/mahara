<?php
/**
 *
 * @package    mahara
 * @subpackage test
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
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
define('CLI', 1);

// The git revision to look at. Change this if you want to test it on past commits.
$GITREV = 'HEAD';

// Whether we've encountered an error or not.
$ERROR = false;

$newconfig = get_mahara_version($GITREV, 'htdocs/lib/version.php');
if (substr($newconfig->release, -3) == 'dev') {
    $STABLEBRANCH = false;
}
else {
    $STABLEBRANCH = true;
}


// Check the core database version
validate_version('htdocs/lib/version.php', 'htdocs/lib/db/upgrade.php');


// Check versions of plugins. Pull the list of changed files, and then check each of them
// to see if it's a version.php or an upgrade.php file. Make a list of those, then
// validate each one found.
require_once('htdocs/lib/mahara.php');
$p = popen("git diff-tree --no-commit-id --name-only -r {$GITREV}", 'r');
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
        $localconfig = get_mahara_version($GITREV, $buffer);
        if ($localconfig->version !== 0) {
            echo "ERROR: You should not update the version number in htdocs/local/version.php.\n";
            $ERROR = true;
        }
        continue;
    }

    if (preg_match('#htdocs/local/upgrade.php#', $buffer, $matches)) {
        $localupdated = true;
        continue;
    }
}
pclose($p);

// Now that we've got our list of updated plugins in this commit, validate each one
foreach (array_keys($updates) as $dir) {
    validate_version("$dir/version.php", "$dir/db/upgrade.php");
}


/**
 * Retrieves the $CFG->version number from a particular file in a particular git revision.
 *
 * @param string $gitrevision The git revision
 * @param string $pathtofile The relative path to the file (starting with htdocs/)
 */
function get_mahara_version($gitrevision, $pathtofile) {
    global $ERROR;

    exec("git show {$gitrevision}:{$pathtofile} 2> /dev/null", $lines, $returnval);
    if ($returnval !== 0) {
        $config = new stdClass();
        $config->version = null;
        return $config;
    }

    array_shift($lines);
    eval(implode("\n", $lines));
    return $config;
}


/**
 * Finds out the version numbers of sections added to a db/upgrade.php file in a
 * particular git revision.
 *
 * @param string $gitrevision The git revision
 * @param string $upgradefile The path to the upgrade.php file
 */
function find_upgrade_versions($gitrevision, $upgradefile) {
    $p = popen("git diff --unified=0 {$gitrevision}~ {$gitrevision} -- {$upgradefile}", 'r');
    $upgradeversions = array();
    while (!feof($p)) {
        $buffer = fgets($p);
        if (1 == preg_match('#^\+.*\$oldversion.*\b(\d{10})\b#', $buffer, $matches)) {
            echo "New {$upgradefile}: {$matches[1]}\n";
            $upgradeversions[] = $matches[1];
        }
    }
    pclose($p);
    return $upgradeversions;
}


/**
 * Check a particular version.php file and db/upgrade.php file and make sure
 * that they haven't been updated incorrectly in the HEAD revision.
 *
 * @param string $versionfile The path to the version file.
 * @param string $upgradefile The path to the upgrade file.
 */
function validate_version($versionfile, $upgradefile) {
    global $ERROR, $GITREV, $STABLEBRANCH;

    $newconfig = get_mahara_version($GITREV, $versionfile);
    $oldconfig = get_mahara_version("{$GITREV}~", $versionfile);

    if ($oldconfig->version !== $newconfig->version) {
        if ($oldconfig->version === null) {
            echo "New plugin {$versionfile}...\n";
        }
        else {
            echo "Bumping {$versionfile}...\n";
            echo "Old version: {$oldconfig->version}\n";
        }
        echo "New version: {$newconfig->version}\n";
    }
    # Check if version file has been deleted
    $isdeleted = `git diff HEAD~1 -- {$versionfile} | grep 'deleted file mode'`;
    if ($isdeleted) {
        echo "WARNING: {$versionfile} is deleted - this may be due to it being renamed\n";
    }
    if ($newconfig->version < $oldconfig->version && !$isdeleted) {
        echo "ERROR: Version number in {$versionfile} has decreased!\n";
        $ERROR = true;
    }

    if (strlen($newconfig->version) != 10 && !$isdeleted) {
        echo "ERROR: Version number in {$versionfile} should be exactly 10 digits.\n";
        $ERROR = true;
    }
    else if (
            $STABLEBRANCH
            && (
                    $oldconfig->version == null
                    || substr($newconfig->version, 0, 8) > substr($oldconfig->version, 0, 8)
            )
    ) {
        echo "ERROR: Version number in {$versionfile} has gone up too much for a stable branch!\n";
        $ERROR = true;
    }

    // If they added new code to lib/db/upgrade.php, make sure the last block in it matches the new version number
    $upgradeversions = find_upgrade_versions($GITREV, $upgradefile);
    if ($upgradeversions) {
        $lastv = $oldconfig->version;
        foreach($upgradeversions as $v) {
            if ($v <= $lastv) {
                echo "ERROR: {$upgradefile} section number {$v} not incremented correctly.\n";
                $ERROR = true;
            }
            $lastv = $v;
        }

        if (end($upgradeversions) != $newconfig->version) {
            echo "ERROR: Version in {$versionfile} should match version of last new section in {$upgradefile}\n";
            $ERROR = true;
        }
    }
}

if ($ERROR) {
    die(1);
}