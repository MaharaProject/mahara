<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'engage/topics');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'group');
define('SECTION_PAGE', 'topics');
require(dirname(dirname(__FILE__)) . '/init.php');
safe_require('interaction', 'forum');
require_once('group.php');

define('TITLE', get_string('discussiontopics', 'interaction.forum'));

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
    'jsonscript' => 'json/topics.php',
    'datatable' => 'topiclist',
    'count' => $data['count'],
    'limit' => $limit,
    'offset' => $offset,
));

$smarty = smarty(array('paginator'));
setpageicon($smarty, 'icon-commenting-o');
$smarty->assign('topics', $data['data']);
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('INLINEJAVASCRIPT', 'jQuery(function() { p = ' . $pagination['javascript'] . '});');
$smarty->display('group/topics.tpl');
