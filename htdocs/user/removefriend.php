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
define('MENUITEM', 'groups/findfriends');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');

$id = param_integer('id');
$returnto = param_alpha('returnto', 'myfriends');

if (!is_friend($id, $USER->get('id')) || !$user = get_record('usr', 'id', $id, 'deleted', 0)) {
    throw new AccessDeniedException(get_string('cantremovefriend', 'group'));
}

$user->introduction = get_field('artefact', 'title', 'artefacttype', 'introduction', 'owner', $id);

define('TITLE', get_string('removefromfriends', 'group', display_name($id)));

$form = pieform(array(
    'name' => 'removefriend',
    'autofocus' => false,
    'elements' => array(
        'reason' => array(
            'type' => 'textarea',
            'title' => get_string('reason'),
            'cols' => 40,
            'rows' => 5
        ),
        'submit' => array(
            'type' => 'submitcancel',
            'value' => array(get_string('removefriend', 'group'), get_string('cancel')),
            'goto' => get_config('wwwroot') . ($returnto == 'find' ? 'user/find.php' : ($returnto == 'view' ? 'user/view.php?id=' . $id : 'user/myfriends.php')),
        )
    )
));

$smarty = smarty();
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('form', $form);
$smarty->assign('user', $user);
$smarty->display('user/removefriend.tpl');

function removefriend_submit(Pieform $form, $values) {
    global $USER, $SESSION, $id;
    
    $loggedinid = $USER->get('id');
    $user = get_record('usr', 'id', $id);

    // friend db record
    $f = new StdClass;
    $f->ctime = db_format_timestamp(time());
    
    // notification info
    $n = new StdClass;
    $n->url = get_config('wwwroot') . 'user/view.php?id=' . $loggedinid;
    $n->users = array($user->id);
    $lang = get_user_language($user->id);
    $displayname = display_name($USER, $user);
    $n->urltext = $displayname;

    delete_records_select('usr_friend', '(usr1 = ? AND usr2 = ?) OR (usr2 = ? AND usr1 = ?)', 
                            array($id, $loggedinid, $id, $loggedinid));
    $n->subject = get_string_from_language($lang, 'removedfromfriendslistsubject', 'group');
    
    if (isset($values['reason']) && !empty($values['reason'])) {
        $n->message = get_string_from_language($lang, 'removedfromfriendslistmessage', 'group', $displayname) . $values['reason'];
    }
    else {
        $n->message = get_string_from_language($lang, 'removedfromfriendslistmessage', 'group', $displayname);
    }
    require_once('activity.php');
    activity_occurred('maharamessage', $n);

    handle_event('removefriend', array('user' => $loggedinid, 'friend' => $id));

    $SESSION->add_ok_msg(get_string('friendformremovesuccess', 'group', display_name($id)));
    switch (param_alpha('returnto', 'myfriends')) {
        case 'find':
            redirect('/user/find.php');
            break;
        case 'view':
            redirect('/user/view.php?id=' . $id);
            break;
        default:
            redirect('/user/myfriends.php');
            break;
    }

}
