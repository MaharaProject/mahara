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

function get_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    }
    return $_SERVER['REMOTE_ADDR'];
}

function hash_fieldnames($names, $time) {
    $ip = get_ip();
    $secret = get_config('formsecret');
    $hashed = array();
    foreach ($names as $name) {
        // prefix the hash with an underscore to ensure it is always a valid pieforms element name
        $hashed[$name] = '_' . sha1($name . $time . $ip . $secret);
    }
    return $hashed;
}

function available_spam_traps() {
    $results = array();
    $handle = opendir(get_config('docroot') . 'lib/antispam');
    while ($file = readdir($handle)) {
        preg_match("/(.+)SpamTrap\.php/", $file, $name);
        if ($name) {
            $results[strtolower($name[1])] = $name[1];
        }
    }
    return $results;
}

function new_spam_trap($fields) {
    $spamclass = ucfirst(get_config('antispam')) . 'SpamTrap';
    require_once('antispam/' . $spamclass . '.php');
    return new $spamclass($fields);
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

