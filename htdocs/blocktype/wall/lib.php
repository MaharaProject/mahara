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
 * @subpackage blocktype-wall
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeWall extends SystemBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.wall');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.wall');
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
        $owner = $instance->get_view()->get('owner');
        $userid = (!empty($USER) ? $USER->get('id') : 0);
        
        $smarty = smarty_core();
        $smarty->assign('instanceid', $instance->get('id'));
        $smarty->assign('ownwall', (!empty($USER) && $USER->get('id') == $owner));
        if ($posts = self::fetch_posts($instance)) { 
            $smarty->assign('wallposts', $posts);
        }
        else {
            $smarty->assign('wallmessage', get_string('noposts', 'blocktype.wall'));
        }

        $returnstr = '';
        if (!$editing && $userid != 0) {
            $returnstr .= self::wallpost_form($instance);
        }
        return $smarty->fetch('blocktype:wall:inlineposts.tpl') . $returnstr;
    }

    public static function has_instance_config() {
        return false;
    }

    public static function delete_instance(BlockInstance $instance) {
        return delete_records('blocktype_wall_post', 'instance', $instance->get('id'));
    }

    public function wallpost_form(BlockInstance $instance, $replyto='', $replyuser='') {
        if ($replyuser) {
            $walltoreplyto = self::get_wall_id_for_user($replyuser);
        }
        else {
            $walltoreplyto = $instance->get('id');
        }
        return pieform(array(
            'name'     => 'wallpost',
            'renderer' => 'maharatable',
            'action'   => get_config('wwwroot') . 'blocktype/wall/post.php',
            'template' => 'wallpost.php',
            'templatedir' => pieform_template_dir('wallpost.php', 'blocktype/wall'),
            'successcallback' => array('PluginBlocktypeWall', 'wallpost_submit'),
            'elements' => array(
                'text' => array(
                    'type' => 'textarea',
                    'rows' => 3,
                    'cols' => 50,
                    'defaultvalue' => '',
                    'width' => '100%',
                ),
                'private' => array(
                    'type' => 'checkbox',
                    'title' => get_string('makeyourpostprivate', 'blocktype.wall'),
                ),
                'instance' => array(
                    'type' => 'hidden',
                    'value' => $walltoreplyto,
                ),
                'replyto' => array(
                    'type' => 'hidden',
                    'value' => $replyto,
                ),
                'submit' => array(
                    'type' => 'submit',
                    'value' => get_string('Post', 'blocktype.wall'),
                ),
            ),
        ));
        // TODO if replying here, add select element for replyto other wall or own wall
        // depending on if the user we're replying to has a wall
    }

    public static function wallpost_submit(Pieform $form, $values) {
        global $USER;
        $record = (object)array(
            'instance' => $values['instance'],
            'from'     => $USER->get('id'),
            'replyto'  => ($values['replyto']) ? $values['replyto'] : null,
            'private'  => (int)(bool)$values['private'],
            'postdate' => db_format_timestamp(time()),
            'text'     => $values['text'],
        );
        insert_record('blocktype_wall_post', $record);
        $userid = get_field_sql('SELECT owner FROM {view} WHERE id = (SELECT "view" FROM {block_instance} WHERE id = ?)', array($values['instance']));
        redirect('/user/view.php?id=' . $userid);
    }

    public static function fetch_posts(BlockInstance $instance ) {
        global $USER;
        $owner = $instance->get_view()->get('owner');
        $userid = (!empty($USER) ? $USER->get('id') : 0);

        // We select u.id because display_name uses the 'id' field to get 
        // information (we really should be passing objects with just user 
        // information to it, for safety). We select it again as 'userid' to 
        // avoid confusion in the templates
        $sql = '
            SELECT bwp.instance, bwp.from, bwp.replyto, bwp.private, bwp.postdate, bwp.text,' . db_format_tsfield('postdate') . ',
                u.id, u.id AS userid, u.username, u.firstname, u.lastname, u.preferredname, u.staff, u.admin
                FROM {blocktype_wall_post} bwp 
                JOIN {usr} u ON bwp.from = u.id
                WHERE bwp.instance = ? AND u.deleted = 0
        ' . (($owner != $userid)  ? ' 
                AND bwp.private = 0 ' : '' ) . '
                ORDER BY bwp.postdate DESC
        ';
        $params = array($instance->get('id'));

        if ($records = get_records_sql_array($sql, $params, 0, 10)) {
            return array_map(
                create_function(
                    '$item', 
                    '$item->displayname = display_name($item); return $item;'), 
                $records
            );
        }
        return false;
    }

    public static function default_copy_type() {
        return 'shallow';
    }

}

?>
