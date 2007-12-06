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

$id = param_integer('id');

$instance = interaction_instance_from_id($id);
$group = get_record('group', 'id', $instance->get('group'));

if (!$group->owner == $USER->get('id')) {
    throw new AccessDeniedException(get_string('notallowedtodeleteinteraction', 'group'));
}

define('TITLE', get_string('deleteinteraction', 'group', get_string('name', 'interaction.' . $instance->get('plugin')), $instance->get('title')));
// submit handler in interaction/lib.php
$form = pieform(array(
    'name'     => 'delete_interaction',
    'elements' => array(
        'title' => array(
            'value' => get_string('deleteinteractionsure', 'group'),
        ),
        'id' => array(
            'type'  => 'hidden',
            'value' => $id,
        ),
        'submit' => array(
            'type'  => 'submitcancel',
            'value' => array(get_string('yes'), get_string('no')),
            'goto'  => get_config('wwwroot') . 'interaction/' .$instance->get('plugin') . '/view.php?id=' . $instance->get('id'),
        )
    )
));

$smarty = smarty();
$smarty->assign('form', $form);
$smarty->assign('heading', TITLE);
$smarty->assign('group', $group);
$smarty->display('interaction/delete.tpl');

?>
