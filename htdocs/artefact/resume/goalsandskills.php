<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2013 Catalyst IT Ltd and others; see:
 *                    http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage artefact-resume
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

define('INTERNAL', 1);
define('MENUITEM', 'content/resume');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'resume');
define('SECTION_PAGE', 'index');
define('RESUME_SUBPAGE', 'goalsandskills');

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('resume', 'artefact.resume'));
safe_require('artefact', 'resume');

$goals  = ArtefactTypeResumeGoalAndSkill::get_goals_and_skills('goals');
$skills = ArtefactTypeResumeGoalAndSkill::get_goals_and_skills('skills');

$js = '
$j(function() {
    $j("a.goaltitle").click(function(e) {
        e.preventDefault();
        $j("#" + this.id + "_desc").toggleClass("hidden");
    });
    $j("a.skilltitle").click(function(e) {
        e.preventDefault();
        $j("#" + this.id + "_desc").toggleClass("hidden");
    });
});';

$smarty = smarty(array('tablerenderer'));
$smarty->assign_by_ref('goals', $goals);
$smarty->assign_by_ref('skills', $skills);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('SUBPAGENAV', PluginArtefactResume::submenu_items());
$smarty->display('artefact:resume:goalsandskills.tpl');
