<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-file
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('INSTITUTIONALADMIN', 1);
define('MENUITEM', 'manageinstitutions/institutionfiles');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'file');
define('SECTION_PAGE', 'institutionfiles');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'file');
require_once(get_config('libroot') . 'institution.php');

$institution = param_alphanum('institution', false);

define('TITLE', get_string('institutionfiles', 'admin'));

$s = institution_selector_for_page($institution,
                                   get_config('wwwroot') . 'artefact/file/institutionfiles.php');

$institution = $s['institution'];

$pagebase = get_config('wwwroot') . 'artefact/file/institutionfiles.php?institution=' . $institution;
$form = pieform(ArtefactTypeFileBase::files_form($pagebase, null, $institution));
$js = ArtefactTypeFileBase::files_js();

$smarty = smarty();
setpageicon($smarty, 'icon-university');

if ($institution === false) {
    $smarty->display('admin/users/noinstitutions.tpl');
    exit;
}

if (!$USER->can_edit_institution($institution)) {
    throw new AccessDeniedException();
}

$smarty->assign('institution', $institution);
$smarty->assign('institutionselector', $s['institutionselector']);
$smarty->assign('form', $form);
$smarty->assign('INLINEJAVASCRIPT', $s['institutionselectorjs'] . $js);
$smarty->display('artefact:file:files.tpl');
