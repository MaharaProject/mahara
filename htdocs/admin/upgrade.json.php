<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage admin
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('INSTALLER', 1);
define('JSON', 1);

require(dirname(dirname(__FILE__)) . '/init.php');
require(get_config('libroot') . 'upgrade.php');

$install = param_boolean('install');
if (!$install) {
    $name    = param_variable('name');
    $upgrade = check_upgrades($name);
    
    if (empty($upgrade->disablelogin)) {
        auth_setup();
    }
}

if ($install) {
    $message = '';
    if (!get_config('installed')) {
        try {
            core_install_defaults();
        }
        catch (SQLException $e) {
            json_reply('local', $e->getMessage());
        }
    }
    json_reply(false, $message);
}

$data = array(
    'key'        => $name
);             

if (!empty($upgrade)) {
    $data['newversion'] = $upgrade->torelease . ' (' . $upgrade->to . ')' ;
    if ($name == 'core') {
        $funname = 'upgrade_core';
    } 
    else {
        $funname = 'upgrade_plugin';
    }
    try {
        $funname($upgrade);
        if (isset($upgrade->install)) {
            $data['install'] = $upgrade->install;
        }
        $data['error'] = false;
        json_reply(false, $data);
        exit;
    } 
    catch (Exception $e) {
        list($texttrace, $htmltrace) = log_build_backtrace($e->getTrace());
        $data['errormessage'] = $e->getMessage() . '<br>' . $htmltrace;
        $data['error'] = true;
        json_reply('local', $data);
        exit;
    }
}
else {
    json_reply(false, array('error' => false,
                            'message' => string('nothingtoupgrade','admin')));
    exit;
}
?>
