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
 * @subpackage core
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
define('MENUITEM', 'home');

require('init.php');

// check to see if we're installed...
if (!get_config('installed')) {
    redirect(get_config('wwwroot') . 'admin/index.php');
}

// Check for whether the user is logged in, before processing the page. After
// this, we can guarantee whether the user is logged in or not for this page.
if (!$SESSION->is_logged_in()) {
    require_once('form.php');
    $form = auth_get_login_form();
    $form['renderer'] = 'div';
    $login_form = form($form);
}

$smarty = smarty();
if (!$SESSION->is_logged_in()) {
    $smarty->assign('login_form', $login_form);
}
$smarty->display('index.tpl');

?>
