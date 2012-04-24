<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2011 Catalyst IT Ltd and others; see:
 *                    http://wiki.mahara.org/Contributors
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
 * @author     Richard Mansfield
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 *
 */

define('PUBLIC', 1);
define('INTERNAL', 1);
define('MENUITEM', 'groups/members');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('group.php');
require_once('pieforms/pieform.php');

define('GROUP', param_integer('id'));

$group = group_current_group();
if (!is_logged_in() && !$group->public) {
    throw new AccessDeniedException();
}

$role = group_user_access($group->id);

if (!$role || !$group->suggestfriends) {
    throw new AccessDeniedException();
}

$subheading = get_string('suggesttofriends', 'group');
define('TITLE', $group->name . ' - ' . $subheading);

$form = pieform(array(
    'name' => 'addmembers',
    'elements' => array(
        'users' => array(
            'type' => 'userlist',
            'lefttitle' => get_string('potentialmembers', 'group'),
            'righttitle' => get_string('userstosendrecommendationsto', 'group'),
            'searchscript' => 'group/membersearchresults.json.php',
            'defaultvalue' => array(),
            'filter' => false,
            'searchparams' => array(
                'id' => GROUP,
                'limit' => 100,
                'html' => 0,
                'membershiptype' => 'notinvited',
                'friends' => 1,
            ),
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('submit'),
        )
    )
));

$smarty = smarty();
$smarty->assign('subheading', $subheading);
$smarty->assign('form', $form);
$smarty->display('group/form.tpl');
exit;

function addmembers_submit(Pieform $form, $values) {
    global $SESSION, $group, $USER;

    if (empty($values['users'])) {
        redirect(get_config('wwwroot') . 'group/suggest.php?id=' . GROUP);
    }

    require_once('activity.php');
    $groupurl = group_homepage_url($group, false);
    activity_occurred('maharamessage', array(
        'users'   => $values['users'],
        'subject' => '',
        'message' => '',
        'strings'       => (object) array(
            'subject' => (object) array(
                'key'     => 'suggestgroupnotificationsubject',
                'section' => 'group',
                'args'    => array(display_name($USER)),
            ),
            'message' => (object) array(
                'key'     => 'suggestgroupnotificationmessage',
                'section' => 'group',
                'args'    => array(display_name($USER), hsc($group->name), get_config('sitename')),
            ),
        ),
        'url'     => $groupurl,
        'urltext' => hsc($group->name),
    ));

    $SESSION->add_ok_msg(get_string('recommendationssent', 'group', count($values['users'])));
    redirect(get_config('wwwroot') . $groupurl);
}
