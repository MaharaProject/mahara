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
 */
error_reporting(0);
define('INTERNAL', 1);
require('htdocs/lib/version.php');

$newconfig = get_mahara_version('HEAD');
$oldconfig = get_mahara_version('HEAD~');

function get_mahara_version($gitversion) {
    exec("git show {$gitversion}:htdocs/lib/version.php", $lines, $returnval);
    if ($returnval !== 0) {
        echo "ERROR (test/versioncheck.php): Couldn't locate version.php file in {$gitversion}.";
        exit(1);
    }

    array_shift($lines);
    eval(implode("\n", $lines));
    return $config;
}

if ($oldconfig->version != $newconfig->version) {
    echo "Bumping lib/version.php...\n";
    echo "Old version: {$oldconfig->version}\n";
    echo "New version: {$newconfig->version}\n";
}

if ($newconfig->version < $oldconfig->version) {
    echo "(test/versioncheck.php) ERROR: Version number in lib/version.php has decreased!\n";
    exit(2);
}

// Determine if we're on a stable branch or not.
if (substr($newconfig->release, -3) == 'dev') {
    $stablebranch = false;
}
else {
    $stablebranch = true;
}

if (strlen($newconfig->version) != 10) {
    echo "(test/versioncheck.php) ERROR: Version number in lib/version.php should be exactly 10 digits.\n";
    exit(4);
}

if ($stablebranch && substr($newconfig->version, 0, 8) != substr($oldconfig->version, 0, 8)) {
    echo "(test/versioncheck.php) ERROR: Version number in lib/version.php has gone up too much for a stable branch!\n";
    exit(3);
}