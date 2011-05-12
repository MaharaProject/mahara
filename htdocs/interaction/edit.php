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
 * @subpackage interaction
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'groups');


require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('docroot') . 'interaction/lib.php');

require_once('pieforms/pieform.php');
require_once('group.php');

$id = param_integer('id', 0);

if (!empty($id)) {
    $instance = interaction_instance_from_id($id);
    $plugin = $instance->get('plugin');
    $groupid = (int)$instance->get('group');
    define('TITLE', get_string('edittitle', 'interaction.' . $plugin));
}
else {
    $instance = null;
    $plugin = param_alphanum('plugin');
    $groupid = param_integer('group');
    define('TITLE', get_string('addtitle', 'interaction.' . $plugin));
}
define('GROUP', $groupid);
$group = group_current_group();

safe_require('interaction', $plugin);

$membership = group_user_access($groupid);
if ($membership != 'admin') {
    throw new AccessDeniedException(get_string('notallowedtoeditinteractions', 'group'));
}

$returnto = param_alpha('returnto', 'view');

$elements = array_merge(
    PluginInteraction::instance_config_base_form($plugin, $group, $instance),
    call_static_method(generate_class_name('interaction', $plugin), 'instance_config_form', $group, $instance),
    array(
        'submit' => array(
            'type'  => 'submitcancel',
            'value' => array(get_string('save'), get_string('cancel')),
            'goto'  => get_config('wwwroot') . 'interaction/' . $plugin .
                (isset($instance) && $returnto != 'index' ? '/view.php?id=' . $instance->get('id') : '/index.php?group=' . $groupid),
        )
    )
);

$js = call_static_method(generate_class_name('interaction', $plugin), 'instance_config_js', $group, $instance);

// save, validate and cancelhandlers are in interaction/lib.php
$form = pieform(array(
    'name'       => 'edit_interaction',
    'plugintype' => 'interaction',
    'pluginname' => $plugin,
    'elements'   => $elements,
    )
);

$smarty = smarty(array('tablerenderer'));
$smarty->assign('form', $form);
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('heading', $group->name);
$smarty->assign('subheading', TITLE);
$smarty->display('interaction/edit.tpl');
