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
define('MENUITEM', 'groups/topics');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
safe_require('interaction', 'forum');
define('TITLE', get_string('Topics', 'interaction.forum'));

if (!$USER->is_logged_in()) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}

$limit  = param_integer('limit', 10);
$offset = param_integer('offset', 0);
$category = param_integer('category', 0);

$data = PluginInteractionForum::get_active_topics($limit, $offset, $category);

$pagination = build_pagination(array(
    'id' => 'topics_pagination',
    'url' => get_config('wwwroot') . 'group/topics.php' . ($category ? ('?category=' . (int) $category) : ''),
    'jsonscript' => '/json/topics.php',
    'datatable' => 'topiclist',
    'count' => $data['count'],
    'limit' => $limit,
    'offset' => $offset,
));

$smarty = smarty(array('paginator'));
$smarty->assign_by_ref('topics', $data['data']);
$smarty->assign_by_ref('pagination', $pagination['html']);
$smarty->assign('INLINEJAVASCRIPT', 'addLoadEvent(function() {' . $pagination['javascript'] . '});');
$smarty->assign('PAGEHEADING', TITLE);
$smarty->display('group/topics.tpl');
