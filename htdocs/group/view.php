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
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
define('MENUITEM', 'groups/info');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('group.php');
require_once('searchlib.php');
require_once(get_config('docroot') . 'interaction/lib.php');
require_once(get_config('libroot') . 'view.php');
safe_require('artefact', 'file');

define('GROUP', param_integer('id'));
$group = group_current_group();
if (!is_logged_in() && !$group->public) {
    throw new AccessDeniedException();
}

define('TITLE', $group->name);

$group->role = group_user_access($group->id);

// logged in user can do stuff
if ($USER->is_logged_in()) {
    $afterjoin = param_variable('next', 'view');
    if ($group->role) {
        if ($group->role == 'admin') {
            $group->membershiptype = 'admin';
            $group->requests = count_records('group_member_request', 'group', $group->id);
        }
        else {
            $group->membershiptype = 'member';
        }
        $group->canleave = group_user_can_leave($group->id);
    }
    else if ($group->jointype == 'invite'
             and $invite = get_record('group_member_invite', 'group', $group->id, 'member', $USER->get('id'))) {
        $group->membershiptype = 'invite';
        $group->invite = group_get_accept_form('invite', $group->id, $afterjoin);
    }
    else if ($group->jointype == 'request'
             and $request = get_record('group_member_request', 'group', $group->id, 'member', $USER->get('id'))) {
        $group->membershiptype = 'request';
    }
    else if ($group->jointype == 'open') {
        $group->groupjoin = group_get_join_form('joingroup', $group->id, $afterjoin);
    }
}

$view = group_get_homepage_view($group->id);
$viewcontent = $view->build_columns();

$headers = array('<link rel="stylesheet" type="text/css" href="' . get_config('wwwroot') . 'theme/views.css">');
if ($group->public) {
    $feedlink = get_config('wwwroot') . 'interaction/forum/atom.php?type=g&id=' . $group->id;
    $headers[] = '<link rel="alternate" type="application/atom+xml" href="' . $feedlink . '" />';
}

$javascript = array('paginator', 'jquery');
$javascript = array_merge($javascript, $view->get_blocktype_javascript());

$smarty = smarty(
    $javascript,
    $headers,
    array(),
    array('stylesheets' => array('style/views.css'))
);
$smarty->assign('viewid', $view->get('id'));
$smarty->assign('viewcontent', $viewcontent);
$smarty->assign('group', $group);
$smarty->assign('returnto', 'view');
$smarty->assign('SUBPAGETOP', 'group/groupuserstatus.tpl');
$smarty->display('group/view.tpl');
