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
define('MENUITEM', 'groups');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('docroot') . 'interaction/lib.php');
require_once(get_config('libroot') . 'group.php');

define('GROUP', param_integer('id'));
$group = group_current_group();

if (group_user_access($group->id, $USER->get('id')) != 'admin') {
    throw new AccessDeniedException(get_string('notallowedtoeditinteraction', 'group'));
}

define('TITLE', get_string('groupinteractions', 'group'));

$interactiontypes = array_flip(
    array_map(
        function($a) { return $a->name; },
        plugins_installed('interaction')
    )
);

if (!$interactions = get_records_select_array('interaction_instance',
    '"group" = ? AND deleted = ?', array($group->id, 0),
    'plugin, ctime', 'id, plugin, title')) {
    $interactions = array();
}
$names = array();
foreach (array_keys($interactiontypes) as $plugin) {
    $names[$plugin] = array(
        'single' => get_string('name', 'interaction.' . $plugin),
        'plural' => get_string('nameplural', 'interaction.' . $plugin)
    );
}

foreach ($interactions as $i) {
    if (!is_array($interactiontypes[$i->plugin])) {
        $interactiontypes[$i->plugin] = array();
    }
    $interactiontypes[$i->plugin][] = $i;
}
$smarty = smarty();
$smarty->assign('group', $group);
$smarty->assign('data', $interactiontypes);
$smarty->assign('pluginnames', $names);
$smarty->assign('subheading', TITLE);
$smarty->display('group/interactions.tpl');
