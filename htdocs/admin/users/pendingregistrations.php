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
 * @subpackage admin
 * @author     Stacey Walker
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2011 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('INSTITUTIONALADMIN', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('pendingregistrations', 'admin'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'pendingregistrations');
define('MENUITEM', 'manageinstitutions/pendingregistrations');
require_once('pieforms/pieform.php');
require_once('institution.php');

if (!is_logged_in()) {
    throw new AccessDeniedException();
}

$institutionelement = get_institution_selector();

if (empty($institutionelement)) {
    $smarty = smarty();
    $smarty->display('admin/users/noinstitutions.tpl');
    exit;
}

$institution = param_alphanum('institution', null);
if (!$institution || !$USER->can_edit_institution($institution)) {
    $institution = empty($institutionelement['value']) ? $institutionelement['defaultvalue'] : $institutionelement['value'];
}
else if (!empty($institution)) {
    $institutionelement['defaultvalue'] = $institution;
}
$institutionselector = pieform(array(
    'name' => 'usertypeselect',
    'elements' => array(
        'institution' => $institutionelement,
    )
));

$pending = get_records_sql_array('
    SELECT u.* FROM {usr_registration} u
    WHERE u.institution = ? AND u.pending = 1
    ORDER BY u.expiry ASC', array($institution)
);
if (!$pending) {
        $pending = array();
}

function build_pending_html($data, $institution) {
    $smarty = smarty_core();
    $smarty->assign('data', isset($data) ? $data : null);
    $smarty->assign('institution', $institution);
    $tablerows = $smarty->fetch('admin/users/pendinguserslist.tpl');
    return $tablerows;
}
$data = build_pending_html($pending, $institution);

$wwwroot = get_config('wwwroot');
$js = <<< EOF
function reloadUsers() {
    window.location.href = '{$wwwroot}admin/users/pendingregistrations.php?institution='+$('usertypeselect_institution').value;
}
addLoadEvent(function() {
    connect($('usertypeselect_institution'), 'onchange', reloadUsers);
});
EOF;

$smarty = smarty();
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('data', $data);
$smarty->assign('institutionselector', $institutionselector);
$smarty->display('admin/users/pendingregistrations.tpl');
