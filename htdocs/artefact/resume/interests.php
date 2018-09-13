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
define('MENUITEM_SUBPAGE', 'interests');

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('resume', 'artefact.resume'));
define('SUBSECTIONHEADING', get_string('interests',  'artefact.resume'));
safe_require('artefact', 'resume');

if (!PluginArtefactResume::is_active()) {
    throw new AccessDeniedException(get_string('plugindisableduser', 'mahara', get_string('resume','artefact.resume')));
}

$defaults = array(
    'interest' => array(
        'default' => '',
        'fshelp' => true,
    ),
);
$form = pieform(simple_resumefield_form($defaults, 'artefact/resume/interests.php', array(
    'editortitle' => get_string('interests', 'artefact.resume')
)));


$smarty = smarty(array('artefact/resume/js/simpleresumefield.js'));
setpageicon($smarty, 'icon-star');
$smarty->assign('interestsform', $form);
$smarty->assign('SUBPAGENAV', PluginArtefactResume::submenu_items());
$smarty->display('artefact:resume:interests.tpl');
