<?php
/**
 *
 * @package    mahara
 * @subpackage antispam
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

function available_spam_traps() {
    $results = array();
    $handle = opendir(get_config('docroot') . 'lib/antispam');
    while ($file = readdir($handle)) {
        preg_match("/(.+)SpamTrap\.php/", $file, $name);
        if ($name) {
            $results[strtolower($name[1])] = get_string($name[1], 'admin');
        }
    }

    // Addition of new traps will involve updating this ordering array.
    $order = array(1,3,2);
    array_multisort($order, $results);

    return $results;
}

function new_spam_trap($fields) {
    $spamclass = ucfirst(get_config('antispam')) . 'SpamTrap';
    require_once('antispam/' . $spamclass . '.php');
    return new $spamclass($fields);
}

function get_first_blacklisted_domain($text) {
    $spamtrap = new_spam_trap(array());
    if ($baddomain = $spamtrap->has_blacklisted_urls($text)) {
        return $baddomain;
    }
}

// windows has no checkdnsrr until PHP 5.3
if (!function_exists('checkdnsrr')) {
    function checkdnsrr($host, $type='MX') {
        if (empty($host)) {
            return false;
        }
        exec('nslookup -type=' . $type . ' ' . escapeshellcmd($host), $output);
        foreach ($output as $line) {
            if (preg_match('/^' . $host . '/', $line)) {
                return true;
            }
        }
        return false;
    }
}
