<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @subpackage blocktype-myfriends
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

define('MAXFRIENDDISPLAY', 16);

class PluginBlocktypeMyfriends extends SystemBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.myfriends');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.myfriends');
    }

    public static function single_only() {
        return true;
    }

    public static function get_categories() {
        return array('internal');
    }

    public static function get_viewtypes() {
        return array('profile');
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        global $USER;
        $userid = $instance->get_view()->get('owner');
        if (!$userid) {
            // 'My Friends' doesn't make sense for group/site views
            return '';
        }

        $smarty = smarty_core();
        $records = get_records_sql_array('SELECT usr1, usr2 FROM {usr_friend}
            JOIN {usr} u1 ON (u1.id = usr1 AND u1.deleted = 0)
            JOIN {usr} u2 ON (u2.id = usr2 AND u2.deleted = 0)
            WHERE usr1 = ? OR usr2 = ?
            ORDER BY ' . db_random() . '
            LIMIT ?',
            array($userid, $userid, MAXFRIENDDISPLAY)
        );
        // get the friends into a 4x4 array
        if ($records) {
            $friends = array();
            for ($i = 0; $i < 4; $i++) {
                if (isset($records[4 * $i])) {
                    $friends[$i] = array();
                    for($j = 4 * $i; $j < ($i + 1 ) * 4; $j++) {
                        if (isset($records[$j])) {
                            if ($records[$j]->usr1 == $userid) {
                                $friends[$i][] = $records[$j]->usr2;
                            }
                            else {
                                $friends[$i][] = $records[$j]->usr1;
                            }
                        }
                    }
                }
            }
        }
        else {
            $friends = false;
        }
        $smarty->assign('friends', $friends);
        $smarty->assign('searchingforfriends', array('<a href="' . get_config('wwwroot') . 'user/find.php">', '</a>'));

        // If the user has no friends, try and display something useful, such 
        // as a 'request friendship' button
        if (!$friends) {
            $loggedinid = $USER->get('id');
            $is_friend = is_friend($userid, $loggedinid);

            if ($is_friend) {
                $relationship = 'existingfriend';
            }
            else if (record_exists('usr_friend_request', 'requester', $loggedinid, 'owner', $userid)) {
                $relationship = 'requestedfriendship';
            }
            else {
                $relationship = 'none';
                $friendscontrol = get_account_preference($userid, 'friendscontrol');
                if ($friendscontrol == 'auto') {
                    require_once('pieforms/pieform.php');
                    $newfriendform = pieform(array(
                        'name' => 'myfriends_addfriend',
                        'successcallback' => 'addfriend_submit',
                        'autofocus' => false,
                        'renderer' => 'div',
                        'elements' => array(
                            'add' => array(
                                'type' => 'submit',
                                'value' => get_string('addtomyfriends', 'group')
                            ),
                            'id' => array(
                                'type' => 'hidden',
                                'value' => $userid
                            )
                        )
                    ));
                    $smarty->assign('newfriendform', $newfriendform);
                }
                $smarty->assign('friendscontrol', $friendscontrol);
            }
            $smarty->assign('relationship', $relationship);
        }

        $smarty->assign('lookingatownpage', $USER->get('id') == $userid);
        $smarty->assign('USERID', $userid);

        return $smarty->fetch('blocktype:myfriends:myfriends.tpl');
    }

    public static function has_instance_config() {
        return false;
    }

    public static function default_copy_type() {
        return 'shallow';
    }

    /**
     * Myfriends only makes sense for personal views
     */
    public static function allowed_in_view(View $view) {
        return $view->get('owner') != null;
    }

    public static function override_instance_title(BlockInstance $instance) {
        global $USER;
        $ownerid = $instance->get_view()->get('owner');

        if ($ownerid === null || $ownerid == $USER->get('id')) {
            $title = get_string('title', 'blocktype.myfriends');
        }
        else {
            $title = get_string('otherusertitle', 'blocktype.myfriends', display_name($ownerid, null, true));
        }

        $numberoffriends = count_records_sql('SELECT COUNT(usr1) FROM {usr_friend}
            JOIN {usr} u1 ON (u1.id = usr1 AND u1.deleted = 0)
            JOIN {usr} u2 ON (u2.id = usr2 AND u2.deleted = 0)
            WHERE usr1 = ? OR usr2 = ?',
            array($ownerid, $ownerid)
        );
        if ($numberoffriends > MAXFRIENDDISPLAY) {
            $title .= ' ' . get_string('numberoffriends', 'blocktype.myfriends', MAXFRIENDDISPLAY, $numberoffriends);
        }

        return $title;
    }

}

?>
