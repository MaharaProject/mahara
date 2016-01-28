<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Stacey Walker
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
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
    'class' => 'form-inline',
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
setpageicon($smarty, 'icon-university');

$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('data', $data);
$smarty->assign('institutionselector', $institutionselector);
$smarty->display('admin/users/pendingregistrations.tpl');
