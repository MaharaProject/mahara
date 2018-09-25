<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-resume
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', true);
define('MENUITEM', 'create/resume');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'resume');
define('SECTION_PAGE', 'index');
define('MENUITEM_SUBPAGE', 'achievements');

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');

define('TITLE', get_string('resume', 'artefact.resume'));
define('SUBSECTIONHEADING', get_string('achievements',  'artefact.resume'));
safe_require('artefact', 'resume');

if (!PluginArtefactResume::is_active()) {
    throw new AccessDeniedException(get_string('plugindisableduser', 'mahara', get_string('resume','artefact.resume')));
}

$compositetypes = array(
    'certification',
    'book',
    'membership'
);
$inlinejs = ArtefactTypeResumeComposite::get_js($compositetypes);
$compositeforms = ArtefactTypeResumeComposite::get_forms($compositetypes);

$smarty = smarty(array('tablerenderer'));
setpageicon($smarty, 'icon-star');
$smarty->assign('compositeforms', $compositeforms);
$smarty->assign('INLINEJAVASCRIPT', $inlinejs);
$smarty->assign('SUBPAGENAV', PluginArtefactResume::submenu_items());
$smarty->display('artefact:resume:achievements.tpl');
