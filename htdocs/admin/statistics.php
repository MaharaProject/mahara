<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
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
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'adminhome');

require(dirname(dirname(__FILE__)).'/init.php');
require(get_config('libroot') . 'registration.php');

define('TITLE', get_string('sitestatistics', 'admin'));

$type = param_alpha('type', 'users');
$subpages = array('users', 'groups', 'views');
$offset = param_integer('offset', 0);
$limit  = param_integer('limit', 10);

if (!in_array($type, $subpages)) {
    $type = 'users';
}

$sitedata = site_statistics(true);

switch ($type) {
case 'groups':
    $data = group_statistics($limit, $offset);
    break;
case 'views':
    $data = view_statistics($limit, $offset);
    break;
case 'users':
default:
    $data = user_statistics($limit, $offset, $sitedata);
}

$js = <<< EOF
addLoadEvent(function () {
    {$data['table']['pagination_js']}
});
EOF;

$smarty = smarty(array('paginator'));
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('INLINEJAVASCRIPT', $js);

$smarty->assign('sitedata', $sitedata);
$smarty->assign('type', $type);
$smarty->assign('subpages', $subpages);

$smarty->assign('subpagedata', $data);

$smarty->display('admin/statistics.tpl');
