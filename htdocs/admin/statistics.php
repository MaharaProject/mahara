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
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');

require(dirname(dirname(__FILE__)).'/init.php');
require(get_config('libroot') . 'registration.php');

define('TITLE', get_string('sitestatistics', 'admin'));

$sitedata = site_statistics(true);
if (!empty($sitedata['weekly'])) {
    $jsfiles = array('js/PlotKit/excanvas.js', 'js/PlotKit/PlotKit.js');
    $sitedata['dataarray'] = json_encode(array($sitedata['weekly']['view-count'], $sitedata['weekly']['user-count'], $sitedata['weekly']['group-count']));
}

$smarty = smarty($jsfiles);
$smarty->assign('PAGEHEADING', hsc(TITLE));

if (isset($sitedata)) {
    $smarty->assign('sitedata', $sitedata);
}

$smarty->display('admin/statistics.tpl');
?>
