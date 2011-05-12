<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2010 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
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
 * @subpackage antispam
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2010 Catalyst IT Ltd http://catalyst.net.nz
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
