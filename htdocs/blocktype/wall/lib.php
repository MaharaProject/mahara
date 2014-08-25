<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-wall
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
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
        if (!$owner) {
            return '';
        }
        $userid = (!empty($USER) ? $USER->get('id') : 0);

        $returnstr = '';
        if (!$editing && $userid != 0) {
            $returnstr .= self::wallpost_form($instance);
            $returnstr .= self::wallpost_js();
        }

        $smarty = smarty_core();
        $smarty->assign('instanceid', $instance->get('id'));
        $smarty->assign('ownwall', (!empty($USER) && $USER->get('id') == $owner));
        if ($posts = self::fetch_posts($instance)) {
            $smarty->assign('wallposts', $posts);
        }
        else {
            $smarty->assign('wallmessage', get_string('noposts', 'blocktype.wall'));
        }

        return $returnstr . $smarty->fetch('blocktype:wall:inlineposts.tpl');
    }

    public static function has_instance_config() {
        return false;
    }

    public static function has_config() {
        return true;
    }

    public static function get_config_options() {
        $elements = array();
        $elements['sizelimitfieldset'] = array(
            'type' => 'fieldset',
            'legend' => get_string('postsizelimit', 'blocktype.wall'),
            'elements' => array(
                'postsizelimitdescription' => array(
                    'value' => get_string('postsizelimitdescription', 'blocktype.wall')
                ),
                'defaultpostsizelimit' => array(
                    'title'        => get_string('postsizelimitmaxcharacters', 'blocktype.wall'),
                    'type'         => 'text',
                    'defaultvalue' => get_config_plugin('blocktype', 'wall', 'defaultpostsizelimit'),
                    'rules' => array( 'maxlength' => 6 )
                )
            ),
        );
        return array(
            'elements' => $elements,
        );

    }

    public static function validate_config_options($form, $values) {
        if (!is_numeric($values['defaultpostsizelimit'])) {
            $form->set_error('defaultpostsizelimit', get_string('postsizelimitinvalid', 'blocktype.wall'));
        }
        else if ($values['defaultpostsizelimit'] < 0) {
            $form->set_error('defaultpostsizelimit', get_string('postsizelimittoosmall', 'blocktype.wall'));
        }
    }

    public static function save_config_options($form, $values) {
        set_config_plugin('blocktype', 'wall', 'defaultpostsizelimit', (int)$values['defaultpostsizelimit']);
    }

    public static function postinst($prevversion) {
        if ($prevversion == 0) {
            set_config_plugin('blocktype', 'wall', 'defaultpostsizelimit', 1500); // 1500 characters
        }
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
        require_once('pieforms/pieform.php');
        return pieform(array(
            'name'      => 'wallpost_'.$instance->get('id'),
            'renderer'  => 'maharatable',
            'autofocus' => false,
            'jsform'    => true,
            'template'  => 'wallpost.php',
            'templatedir' => pieform_template_dir('wallpost.php', 'blocktype/wall'),
            'validatecallback' => array('PluginBlocktypeWall', 'wallpost_validate'),
            'successcallback' => array('PluginBlocktypeWall', 'wallpost_submit'),
            'jssuccesscallback' => 'wallpost_success',
            'elements' => array(
                'text' => array(
                    'type' => 'textarea',
                    'title' => get_string('Post', 'blocktype.wall'),
                    'hiddenlabel' => true,
                    'description' => bbcode_format_post_message(),
                    'rows' => 3,
                    'cols' => 50,
                    'defaultvalue' => '',
                    'width' => '100%',
                    'rules' => array(
                        'required' => true,
                        'maxlength' => get_config_plugin('blocktype', 'wall', 'defaultpostsizelimit'),
                    ),
                ),
                'postsizelimit' => array(
                    'type' => 'html',
                    'value' => get_string('maxcharacters', 'blocktype.wall', get_config_plugin('blocktype', 'wall', 'defaultpostsizelimit'))
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

    public function wallpost_js() {
        $js = <<<EOF
function wallpost_success(form, data) {
    if ($('wall') && data.posts && data.block) {
        var wall = getFirstElementByTagAndClassName('div', 'wall', 'blockinstance_' + data.block);
        var temp = DIV();
        temp.innerHTML = data.posts;
        newposts = getElementsByTagAndClassName('li', 'wallpost', temp);
        replaceChildNodes(wall, newposts);
        if ($('wallpost_' + data.block + '_text')) {
            $('wallpost_' + data.block + '_text').value = '';
        }
        formSuccess(form, data);
    }
}
EOF;
        return "<script>$js</script>";
    }

    public static function wallpost_validate(Pieform $form, $values) {
        require_once(get_config('libroot') . 'antispam.php');
        $result = probation_validate_content($values['text']);
        if ($result !== true) {
            $form->set_error('text', get_string('newuserscantpostlinksorimages'));
        }
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

        $instance = new BlockInstance($values['instance']);
        $owner = $instance->get_view()->get('owner');
        $smarty = smarty_core();
        $smarty->assign('instanceid', $instance->get('id'));
        $smarty->assign('ownwall', (!empty($USER) && $USER->get('id') == $owner));
        if ($posts = self::fetch_posts($instance)) {
            $smarty->assign('wallposts', $posts);
        }
        $renderedposts = $smarty->fetch('blocktype:wall:inlineposts.tpl');

        $form->reply(PIEFORM_OK, array(
            'message'  => get_string('addpostsuccess', 'blocktype.wall'),
            'posts'    => $renderedposts,
            'block'    => $values['instance'],
            'goto'     => profile_url($owner),
        ));
    }

    public static function fetch_posts(BlockInstance $instance, $nolimit=false ) {
        global $USER;
        $owner = $instance->get_view()->get('owner');
        $userid = (!empty($USER) ? $USER->get('id') : 0);

        // We select u.id because display_name uses the 'id' field to get
        // information (we really should be passing objects with just user
        // information to it, for safety). We select it again as 'userid' to
        // avoid confusion in the templates
        $sql = '
            SELECT bwp.id AS postid, bwp.instance, bwp.from, bwp.replyto, bwp.private, bwp.postdate, bwp.text,' . db_format_tsfield('postdate') . ',
                u.id, u.id AS userid, u.username, u.firstname, u.lastname, u.preferredname, u.staff, u.admin, u.email, u.profileicon, u.urlid
                FROM {blocktype_wall_post} bwp
                JOIN {usr} u ON bwp.from = u.id
                WHERE bwp.instance = ? AND u.deleted = 0
        ' . (($owner != $userid)  ? '
                AND (bwp.private = 0 OR bwp.from = ' . db_quote($userid) . ') ' : '' ) . '
                ORDER BY bwp.postdate DESC
        ';
        $params = array($instance->get('id'));

        if ($records = get_records_sql_array($sql, $params, $nolimit ? '' : 0, $nolimit ? '' : 10)) {
            return array_map(
                create_function(
                    '$item',
                    '$item->displayname = display_name($item);
                    $item->profileurl = profile_url($item);
                    $item->deletable = PluginBlocktypeWall::can_delete_wallpost($item->from, ' . intval($owner) .');
                    return $item;'),
                $records
            );
        }
        return false;
    }

    public static function default_copy_type() {
        return 'shallow';
    }

    /**
     * Wall only makes sense on profile viewtypes
     */
    public static function allowed_in_view(View $view) {
        return $view->get('type') == 'profile';
    }

    public static function override_instance_title(BlockInstance $instance) {
        global $USER;
        $ownerid = $instance->get_view()->get('owner');
        if ($ownerid === null || $ownerid == $USER->get('id')) {
            return get_string('title', 'blocktype.wall');
        }
        return get_string('otherusertitle', 'blocktype.wall', display_name($ownerid, null, true));
    }

    public static function can_delete_wallpost($poster, $wallowner) {
        global $USER;
        return $USER->is_admin_for_user($wallowner) ||
            $poster == $USER->get('id') ||
            $wallowner == $USER->get('id');
    }

}
