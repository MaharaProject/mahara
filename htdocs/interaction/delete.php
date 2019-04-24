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
define('MENUITEM', 'groups');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('docroot') . 'interaction/lib.php');
require_once('group.php');

$id = param_integer('id');

$instance = interaction_instance_from_id($id);
define('GROUP', $instance->get('group'));
$group = group_current_group();

$membership = group_user_access((int)$group->id);
if ($membership != 'admin') {
    throw new AccessDeniedException(get_string('notallowedtodeleteinteractions', 'group'));
}

define('TITLE', get_string('deleteinteraction', 'group', get_string('name', 'interaction.' . $instance->get('plugin')), $instance->get('title')));
// submit handler in interaction/lib.php

// Check to see if the forum is being used as a landing page url
$landingpagenote = '';
if (get_config('homepageredirect') && !empty(get_config('homepageredirecturl'))) {
    $landing = translate_landingpage_to_tags(array(get_config('homepageredirecturl')));
    foreach ($landing as $land) {
        if ($land->type == 'forum' && $land->typeid == $id) {
            $landingpagenote = get_string('islandingpage', 'admin');
        }
    }
}

$returnto = param_alpha('returnto', 'view');

$form = pieform(array(
    'name'     => 'delete_interaction',
    'renderer' => 'div',
    'elements' => array(
        'id' => array(
            'type'  => 'hidden',
            'value' => $id,
        ),
        'submit' => array(
            'type'  => 'submitcancel',
            'class' => 'btn-secondary',
            'value' => array(get_string('yes'), get_string('no')),
            'goto'  => get_config('wwwroot') . 'interaction/' .$instance->get('plugin') .
                ($returnto == 'index' ? '/index.php?group=' . $instance->get('group') : '/view.php?id=' . $instance->get('id')),
        )
    )
));

$smarty = smarty(array('tablerenderer'));
$smarty->assign('form', $form);
$smarty->assign('heading', $group->name);
$smarty->assign('subheading', TITLE);
$smarty->assign('landingpagenote', $landingpagenote);
$smarty->assign('message', get_string('deleteinteractionsure', 'group'));
$smarty->display('interaction/delete.tpl');
