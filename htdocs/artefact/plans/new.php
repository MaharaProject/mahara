<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
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
 * @subpackage artefact-plans
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'content/plans');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'plans');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('artefact', 'plans');

$id = param_integer('id',0);
if ($id) {
    $plan = new ArtefactTypePlan($id);
    if (!$USER->can_edit_artefact($plan)) {
        throw new AccessDeniedException(get_string('accessdenied', 'error'));
    }
    define('TITLE', get_string('newtask','artefact.plans'));
    $form = ArtefactTypeTask::get_form($id);
}
else {
    define('TITLE', get_string('newplan','artefact.plans'));
    $form = ArtefactTypePlan::get_form();
}

$smarty =& smarty();
$smarty->assign_by_ref('form', $form);
$smarty->assign_by_ref('PAGEHEADING', hsc(TITLE));
$smarty->display('artefact:plans:new.tpl');
