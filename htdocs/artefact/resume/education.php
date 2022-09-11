<?php
/**
 * Add an education record
 *
 * @package    mahara
 * @subpackage artefact-resume
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', true);
define('MENUITEM', 'create/resume');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'resume');
define('SECTION_PAGE', 'index');
define('MENUITEM_SUBPAGE', 'education');

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('resume', 'artefact.resume'));
define('SUBSECTIONHEADING', get_string('education', 'artefact.resume'));
safe_require('artefact', 'resume');
safe_require('artefact', 'file');

if (!PluginArtefactResume::is_active()) {
    throw new AccessDeniedException(get_string('plugindisableduser', 'mahara', get_string('resume','artefact.resume')));
}

$compositetypes = array('educationhistory');
$inlinejs = ArtefactTypeResumeComposite::get_js($compositetypes);
$compositeforms = ArtefactTypeResumeComposite::get_forms($compositetypes);

$smarty = smarty(array('tablerenderer'));
setpageicon($smarty, 'icon-regular icon-address-book');
$smarty->assign('compositeforms', $compositeforms);
$smarty->assign('INLINEJAVASCRIPT', $inlinejs);
$smarty->assign('SUBPAGENAV', PluginArtefactResume::submenu_items());
$smarty->display('artefact:resume:education.tpl');