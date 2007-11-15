<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage group-interaction
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'groups');


require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('docroot') . 'interaction/lib.php');

require_once('pieforms/pieform.php');

$id = param_integer('id', 0);

if (!empty($id)) {
    $instance = interaction_instance_from_id($id);
    $plugin = $instance->get('plugin');
    $groupid = $instance->get('group');
    define('TITLE', get_string('edittitle', 'interaction.' . $plugin));
}
else {
    $instance = null;
    $plugin = param_alphanum('plugin');
    $groupid = param_integer('group');
    define('TITLE', get_string('addtitle', 'interaction.' . $plugin));
}

safe_require('interaction', $plugin);
if (!$group = get_record('group', 'id', $groupid)) {
    throw new GroupNotFoundException('groupnotfound', 'group', $groupid);
}

if (!$group->owner == $USER->get('id')) {
    throw new AccessDeniedException(get_string('notallowedtoeditinteraction', 'group'));
}

$elements = array_merge(
    PluginInteraction::instance_config_base_form($plugin, $group, $instance),
    call_static_method(generate_class_name('interaction', $plugin), 'instance_config_form'),
    array(
        'submit' => array(
            'type'  => 'submitcancel',
            'value' => array(get_string('save'), get_string('cancel')),
            'goto'  => get_config('wwwroot') . 'group/interactions.php?id=' . $groupid,
        )
    )
);

// save, validate and cancelhandlers are in interaction/lib.php
$form = pieform(array(
    'name'     => 'edit_interaction',
    'elements' => $elements
    )
);

$smarty = smarty();
$smarty->assign('form', $form);
$smarty->assign('heading', TITLE);
$smarty->assign('group', $group);
$smarty->display('interaction/edit.tpl');




?>
