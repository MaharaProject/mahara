<?php
/**
 *
 * @package    mahara
 * @subpackage interaction
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'engage/index');
define('MENUITEM_SUBPAGE', 'forums');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('docroot') . 'interaction/lib.php');
require_once('group.php');
define('SUBSECTIONHEADING', get_string('nameplural', 'interaction.forum'));
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
            'class' => 'btn-primary',
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
