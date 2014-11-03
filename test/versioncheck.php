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
foreach (plugin_types() as $type) {
    // Find installed instances
    $dirhandle = opendir("htdocs/{$type}");
    while (false != ($plugin = readdir($dirhandle))) {
        if ($plugin[0] == '.' || $plugin == 'CSV') {
            continue;
        }
        $dir = "htdocs/{$type}/{$plugin}";
        if (!is_dir($dir)) {
            continue;
        }
        validate_version("$dir/version.php", "$dir/db/upgrade.php");
    }
    if ($type == 'artefact') {

    }
}


/**
 * Find out the version number in a particular version.php file at a particular git revision
 * @param string $gitversion
 * @param string $pathtofile
 */
function get_mahara_version($gitrevision, $pathtofile) {
    global $error;
    exec("git show {$gitrevision}:{$pathtofile}", $lines, $returnval);
    if ($returnval !== 0) {
        echo "ERROR (test/versioncheck.php): Couldn't locate version.php file in {$gitversion}.";
        $error = true;
    }

    array_shift($lines);
    eval(implode("\n", $lines));
    return $config;
}


function validate_version($versionfile, $upgradefile) {
    $newconfig = get_mahara_version('HEAD', $versionfile);
    $oldconfig = get_mahara_version('HEAD~', $versionfile);

    if ($oldconfig->version != $newconfig->version) {
        echo "Bumping {$versionfile}...\n";
        echo "Old version: {$oldconfig->version}\n";
        echo "New version: {$newconfig->version}\n";
    }

    if ($newconfig->version < $oldconfig->version) {
        echo "(test/versioncheck.php) ERROR: Version number in {$versionfile} has decreased!\n";
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
        echo "(test/versioncheck.php) ERROR: Version number in {$versionfile} should be exactly 10 digits.\n";
        $error = true;
    }
    else if ($stablebranch && substr($newconfig->version, 0, 8) > substr($oldconfig->version, 0, 8)) {
        echo "(test/versioncheck.php) ERROR: Version number in {$versionfile} has gone up too much for a stable branch!\n";
        $error = true;
    }

    // If they added new code to lib/db/upgrade.php, make sure the last block in it matches the new version number
    if ($newconfig->version != $oldconfig->version) {
        $p = popen("git show -- {$upgradefile}", 'r');
        $upgradeversion = false;
        while (!feof($p)) {
            $buffer = fgets($p);
            if (1 == preg_match('#\$oldversion.*\b(\d{10})\b#', $buffer, $matches)) {
                $upgradeversion = $matches[1];
                echo "New {$upgradefile}: {$upgradeversion}\n";
            }
        }
        pclose($p);
        if ($upgradeversion !== false && $upgradeversion != $newconfig->version) {
            echo "(test/versioncheck.php) ERROR: Version in {$versionfile} should match version of last new section in {$upgradefile}\n";
            $error = true;
        }
    }
}