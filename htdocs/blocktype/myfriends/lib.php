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
 * @subpackage blocktype-myfriends
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
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
        return array('profile', 'dashboard');
    }

    public static function build_myfriends_html(&$friends, $userid, BlockInstance $instance) {
        $friendarray = array_chunk($friends['data'], 4); // get the friends into a 4x4 array
        $smarty = smarty_core();
        $smarty->assign_by_ref('friends', $friendarray);
        $friends['tablerows'] = $smarty->fetch('blocktype:myfriends:myfriendrows.tpl');

        if ($friends['limit'] === false) {
            return;
        }

        $baseurl = $instance->get_view()->get_url() . '&block=' . $instance->get('id');
        $baseurl .= '&user=' . (int) $userid;
        $pagination = build_pagination(array(
            'id' => 'userfriendstable_pagination',
            'class' => 'center nojs-hidden-block',
            'datatable' => 'userfriendstable',
            'url' => $baseurl,
            'jsonscript' => 'blocktype/myfriends/myfriends.json.php',
            'count' => $friends['count'],
            'limit' => $friends['limit'],
            'offset' => $friends['offset'],
            'numbersincludefirstlast' => false,
            'resultcounttextsingular' => get_string('friend', 'group'),
            'resultcounttextplural' => get_string('friends', 'group'),
        ));
        $friends['pagination'] = $pagination['html'];
        $friends['pagination_js'] = $pagination['javascript'];
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        global $USER, $exporter;

        $userid = $instance->get_view()->get('owner');
        if (!$userid) {
            // 'My Friends' doesn't make sense for group/site views
            return '';
        }

        $limit = isset($exporter) ? false : MAXFRIENDDISPLAY;

        $friends = get_friends($userid, $limit, 0);
        if ($friends['count']) {
            self::build_myfriends_html($friends, $userid, $instance);
        }
        else {
            $friends = false;
        }

        $smarty = smarty_core();
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
            return get_string('title', 'blocktype.myfriends');
        }

        return get_string('otherusertitle', 'blocktype.myfriends', display_name($ownerid, null, true));
    }

}
