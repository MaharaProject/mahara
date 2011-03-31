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
define('MENUITEM', 'groups');

define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'groupviews');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'group.php');
require_once('pieforms/pieform.php');

define('GROUP', param_integer('group'));
$group = group_current_group();
if (!is_logged_in() && !$group->public) {
    throw new AccessDeniedException();
}

define('TITLE', $group->name . ' - ' . get_string('groupviews', 'view'));

$can_edit = group_user_can_edit_views($group->id);

// If the user can edit group views, show a page similar to the my views
// page, otherwise just show a list of the views owned by this group that
// are visible to the user.

if (!$can_edit) {

    $limit   = param_integer('limit', 5);
    $offset  = param_integer('offset', 0);

    $data = View::view_search(null, null, (object) array('group' => $group->id), null, $limit, $offset);
    // Add a copy view form for all templates in the list
    foreach ($data->data as &$v) {
        if ($v['template']) {
            $v['copyform'] = pieform(create_view_form(null, null, $v['id']));
        }
    }

    $pagination = build_pagination(array(
        'url' => get_config('wwwroot') . 'view/groupviews.php?group='.$group->id,
        'count' => $data->count,
        'limit' => $limit,
        'offset' => $offset,
    ));

    $smarty = smarty();
    $smarty->assign('views', $data->data);
    $smarty->assign('pagination', $pagination['html']);
    $smarty->display('view/groupviews.tpl');
    exit;

}

list($searchform, $data, $pagination) = View::views_by_owner($group->id);

$createviewform = pieform(create_view_form($group->id));

$smarty = smarty();
$smarty->assign('views', $data->data);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('searchform', $searchform);
$smarty->assign('createviewform', $createviewform);
$smarty->display('view/index.tpl');
