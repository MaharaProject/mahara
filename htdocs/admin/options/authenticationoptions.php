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
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');

$smarty = smarty();

$method = clean_requestdata('m', PARAM_ALPHA);
$smarty->assign('method', $method);

if (!safe_require('auth', $method, 'lib.php', 'require_once', true)) {
    throw new Exception('The specified method does not exist');
}
$class = 'Auth' . ucfirst(strtolower($method));

require_once('form.php');
$form = call_static_method($class, 'get_configuration_form');
if ($form) {
    $smarty->assign('form', form($form));
}
else {
    die_info(get_string('authnoconfigurationoptions', 'admin'));
}

$smarty->display('admin/options/authenticationoptions.tpl');

?>
