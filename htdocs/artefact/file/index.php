<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-file
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'create/files');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'file');
define('SECTION_PAGE', 'index');
define('FOLDER_SIZE', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('Files', 'artefact.file'));
safe_require('artefact', 'file');

$form = pieform(ArtefactTypeFileBase::files_form(get_config('wwwroot') . 'artefact/file/index.php'));
$js = ArtefactTypeFileBase::files_js();

$smarty = smarty();
setpageicon($smarty, 'icon-regular icon-file-image');
$smarty->assign('form', $form);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->display('artefact:file:files.tpl');
