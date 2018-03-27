<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('INSTITUTIONALADMIN', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('pendingdeletions', 'admin'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'pendingdeletions');
define('MENUITEM', 'manageinstitutions/pendingdeletions');
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
if ($institution == 'mahara') {
    $pending = get_records_sql_array('
        SELECT d.*, u.id AS userid, u.username
        FROM {usr_pendingdeletion} d
        JOIN {usr} u ON d.usr = u.id
        WHERE NOT EXISTS (SELECT * FROM {usr_institution} ui WHERE ui.usr = u.id)
        ORDER BY d.ctime ASC'
    );
}
else {
    $instobj = new Institution($institution);
    if ($instobj->requires_user_deletion_approval()) {
        $pending = get_records_sql_array('
            SELECT d.*, u.id AS userid, u.username
            FROM {usr_pendingdeletion} d
            JOIN {usr} u ON d.usr = u.id
            JOIN {usr_institution} ui ON ui.usr = u.id
            WHERE ui.institution = ?
            ORDER BY d.ctime ASC',
            array($institution)
        );
    }
}
if (!isset($pending) || !$pending) {
        $pending = array();
}

function build_pending_html($data, $institution) {
    foreach ($data as $d) {
        $d->displayname = display_name($d->userid, null, true);
        $d->displayurl = profile_url($d->userid);
    }
    $smarty = smarty_core();
    $smarty->assign('data', isset($data) ? $data : null);
    $smarty->assign('institution', $institution);
    $tablerows = $smarty->fetch('admin/users/pendingdeletionlist.tpl');
    return $tablerows;
}
$data = build_pending_html($pending, $institution);

$wwwroot = get_config('wwwroot');
$js = <<< EOF
jQuery(function($) {
  function reloadUsers() {
      window.location.href = '{$wwwroot}admin/users/pendingdeletions.php?institution='+$('#usertypeselect_institution').val();
  }

  $('#usertypeselect_institution').on('change', reloadUsers);
});
EOF;

$smarty = smarty();
setpageicon($smarty, 'icon-university');

$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('data', $data);
$smarty->assign('institutionselector', $institutionselector);
$smarty->display('admin/users/pendingdeletions.tpl');
