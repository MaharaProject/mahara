<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-resume
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

define('INTERNAL', 1);
define('MENUITEM', 'content/resume');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'resume');
define('SECTION_PAGE', 'index');
define('RESUME_SUBPAGE', 'goalsandskills');

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('resume', 'artefact.resume'));
define('SUBSECTIONHEADING', get_string('goalsandskills',  'artefact.resume'));
safe_require('artefact', 'resume');

if (!PluginArtefactResume::is_active()) {
    throw new AccessDeniedException(get_string('plugindisableduser', 'mahara', get_string('resume','artefact.resume')));
}

$goals  = ArtefactTypeResumeGoalAndSkill::get_goals_and_skills('goals');
$skills = ArtefactTypeResumeGoalAndSkill::get_goals_and_skills('skills');

$js = '
jQuery(function($) {
    $("a.goaltitle").click(function(e) {
        e.preventDefault();
        $j("#" + this.id + "_desc").toggleClass("hidden");
    });
    $("a.skilltitle").click(function(e) {
        e.preventDefault();
        $("#" + this.id + "_desc").toggleClass("hidden");
    });
});';

$smarty = smarty(array('tablerenderer'));
$smarty->assign('goals', $goals);
$smarty->assign('skills', $skills);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('SUBPAGENAV', PluginArtefactResume::submenu_items());
$smarty->display('artefact:resume:goalsandskills.tpl');
