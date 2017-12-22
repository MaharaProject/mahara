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
define('TITLE', get_string('privacy', 'admin'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'institutionprivacy');
define('MENUITEM', 'manageinstitutions/privacy');
require_once('institution.php');

if (!is_logged_in()) {
    throw new AccessDeniedException();
}

$institutionelement = get_institution_selector(false);

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

$data = '<div class="no-results">' . get_string('noinstitutionprivacy', 'admin') . '</div>'; //privacy data to show

$wwwroot = get_config('wwwroot');
$js = <<< EOF
jQuery(function($) {
  function reloadUsers() {
      window.location.href = '{$wwwroot}admin/users/institutionprivacy.php?institution='+$('#usertypeselect_institution').val();
  }

  $('#usertypeselect_institution').on('change', reloadUsers);
});
EOF;

$smarty = smarty();
setpageicon($smarty, 'icon-umbrella');

$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('data', $data);
$smarty->assign('institutionselector', $institutionselector);
$smarty->display('admin/users/institutionprivacy.tpl');
