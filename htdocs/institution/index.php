<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2011 Catalyst IT Ltd and others; see:
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
 * @subpackage core
 * @author     Stacey Walker
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2011 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', '');
define('SECTION_PLUGINTYPE', 'core');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('institution.php');

if (!is_logged_in()) {
    throw new AccessDeniedException();
}

$inst = param_alpha('institution');
$institution = new Institution($inst);

$admins = $institution->admins();
$staff = $institution->staff();
build_stafflist_html($admins, 'institution', 'admin', $inst);
build_stafflist_html($staff, 'institution', 'staff', $inst);

define('TITLE', $institution->displayname);

$smarty = smarty();
$smarty->assign('admins', $admins);
$smarty->assign('staff', $staff);
$smarty->assign('PAGEHEADING', get_string('institutioncontacts', 'mahara', TITLE));
$smarty->display('institution/staffadmin.tpl');
