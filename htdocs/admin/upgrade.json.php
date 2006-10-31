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
define('INSTALLER', 1);
require(dirname(dirname(__FILE__)) . '/init.php');
require(get_config('libroot') . 'upgrade.php');

$name    = clean_requestdata('name', PARAM_ALPHAEXT, REQUEST_EITHER);
$install = clean_requestdata('install', PARAM_BOOL, REQUEST_EITHER);

if ($install) {
    // @todo should probably report errors. Also see upgrade.php to make the js detect any errors
    if (!get_config('installed')) {
        set_config('installed', true);
    }
    exit;
}

$upgrade = check_upgrades($name);
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
        $data['success'] = 1;
    } 
    catch (Exception $e) {
        $data['errormessage'] = $e->getMessage();
        $data['success']      = 0;
    }
}
else {
    $data['success'] = 1;
    $data['errormessage'] = get_string('nothingtoupgrade','admin');
}
echo json_encode($data);    
?>
