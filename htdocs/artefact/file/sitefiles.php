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
define('ADMIN', 1);
define('MENUITEM', 'configsite/sitefiles');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'file');
define('SECTION_PAGE', 'sitefiles');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'file');
define('TITLE', get_string('sitefiles', 'admin'));

$form = pieform(ArtefactTypeFileBase::files_form(get_config('wwwroot') . 'artefact/file/sitefiles.php', null, 'mahara'));
$js = ArtefactTypeFileBase::files_js();

$smarty = smarty();
setpageicon($smarty, 'icon-file-image-o');

$smarty->assign('descriptionstrargs', array('<a href="' . get_config('wwwroot') . 'admin/site/menu.php">', '</a>'));
$smarty->assign('institution', 'mahara');
$smarty->assign('form', $form);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->display('artefact:file:files.tpl');
