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
    WHERE u.institution = ? AND u.pending IN (1, 2)
    ORDER BY u.pending ASC, u.expiry ASC', array($institution)
);
if (!$pending) {
        $pending = array();
}

function build_pending_html($data, $institution) {
    // Check for information saved in the usr_registrtion.extra table column
    // This extra data is serialized data from the custom fields in registration form
    // The custom fields can be existing core profile fields like 'studentid' or
    // can be local custom profile fields defined in the 'local/lib/artefact_internal.php' file
    $extracols = array();
    if (!empty($data)) {
        foreach ($data as $itemkey => $item) {
            if (!empty($item->extra)) {
                $item->extra = unserialize($item->extra);
                safe_require('artefact', 'internal');
                // If 'extra' data exists we loop through the de-serialized 'extra' data to:
                // 1) add the columnsto the pending registrations table for these particular profile fields
                // 2) adjust the content for the column to be human readable, via format_result()
                foreach ($item->extra as $k => $v) {
                    $classname = 'ArtefactType' . ucfirst($k);
                    if (class_exists($classname)) {
                        $extracols[$k] = 1;
                    }
                    if (is_callable(array($classname, 'format_result'))) {
                        $out = call_static_method($classname, 'format_result', $v);
                        $item->extra->$k = $out;
                    }
                }
            }
            $item->expiryformat = format_date(strtotime($item->expiry));
        }
    }
    $smarty = smarty_core();
    $smarty->assign('extracols', isset($extracols) ? $extracols : null);
    $smarty->assign('data', isset($data) ? $data : null);
    $smarty->assign('institution', $institution);
    $tablerows = $smarty->fetch('admin/users/pendinguserslist.tpl');
    return $tablerows;
}
$data = build_pending_html($pending, $institution);

$wwwroot = get_config('wwwroot');
$js = <<< EOF
jQuery(function($) {
  function reloadUsers() {
      window.location.href = '{$wwwroot}admin/users/pendingregistrations.php?institution='+$('#usertypeselect_institution').val();
  }

  $('#usertypeselect_institution').on('change', reloadUsers);
});
EOF;

$smarty = smarty();
setpageicon($smarty, 'icon-university');

$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('data', $data);
$smarty->assign('institutionselector', $institutionselector);
$smarty->display('admin/users/pendingregistrations.tpl');
