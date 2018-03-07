<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);

define('INSTITUTIONALADMIN', 1);
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('MENUITEM', 'manageinstitutions/institutiontags');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('libroot') . 'institution.php');

$institution = param_alphanum('institution', false);

// Get all the institutions that the current user has access to.
$institutionelement = get_institution_selector(true, false, false, false, false, true);
if (!$institutionelement || empty($institutionelement['options'])) {
    throw new AccessDeniedException(get_string('cantlistinstitutioncollections', 'collection'));
}

if (!$institution || !$USER->can_edit_institution($institution, true)) {
    $institution = empty($institutionelement['value']) ? $institutionelement['defaultvalue'] : $institutionelement['value'];
}
else if (!empty($institution)) {
    $institutionelement['defaultvalue'] = $institution;
}

define('TITLE', get_string('institutiontags'));

$institutionselector = pieform(array(
    'name' => 'usertypeselect',
    'class' => 'form-inline',
    'elements' => array(
        'institution' => $institutionelement,
    )
));

// The institution drop-down selector if applicable.
$wwwroot = get_config('wwwroot');
$js = <<< EOF
function reloadTags() {
    window.location.href = '{$wwwroot}admin/users/institutiontags.php?institution='+$('#usertypeselect_institution').val();
}
$(document).ready(function() {
    $('#usertypeselect_institution').on('change', reloadTags);
});
EOF;

$smarty = smarty(array('paginator'));
setpageicon($smarty, 'icon-university');
$smarty->assign('institutionselector', $institutionselector);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->display('admin/users/institutiontags.tpl');
