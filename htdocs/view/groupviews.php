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
define('MENUITEM', 'groups/views');

define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'groupviews');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'group.php');
require_once('pieforms/pieform.php');

//@todo: group menu; group sideblock

$limit   = param_integer('limit', 5);
$offset  = param_integer('offset', 0);
define('GROUP', param_integer('group'));
$group = group_current_group();
if (!is_logged_in() && !$group->public) {
    throw new AccessDeniedException();
}

define('TITLE', $group->name . ' - ' . get_string('groupviews', 'view'));

$member = group_user_access($group->id);
$shared = param_boolean('shared', 0) && $member;
$can_edit = group_user_can_edit_views($group->id);

$createviewform = pieform(create_view_form($group->id));

$smarty = smarty();
$smarty->assign('heading', $group->name);

if ($can_edit) {
    $data = View::get_myviews_data($limit, $offset, $group->id);
}
else {
    $data = View::view_search(null, null, (object) array('group' => $group->id), null, $limit, $offset);
}

$userid = $USER->get('id');

$pagination = build_pagination(array(
    'url' => get_config('wwwroot') . 'view/groupviews.php?group='.$group->id,
    'count' => $data->count,
    'limit' => $limit,
    'offset' => $offset,
    'resultcounttextsingular' => get_string('view', 'view'),
    'resultcounttextplural' => get_string('views', 'view')
));

$smarty->assign('groupviews', 1);
$smarty->assign('member', $member);
$smarty->assign('views', $data->data);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('createviewform', $createviewform);

if ($can_edit) { // && !$shared) {
    $smarty->display('view/index.tpl');
} else {
    $smarty->display('view/sharedviews.tpl');
}

?>
