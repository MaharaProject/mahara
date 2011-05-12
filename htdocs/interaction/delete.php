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
$smarty->assign('message', get_string('deleteinteractionsure', 'group'));
$smarty->display('interaction/delete.tpl');
