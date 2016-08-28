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
require_once('activity.php');

class PluginBlocktypeWall extends MaharaCoreBlocktype {

    public static function should_ajaxify() {
        return false;
    }

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
        return array('internal' => 28000);
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
                'defaultpostsizelimit' => array(
                    'title'        => get_string('postsizelimitmaxcharacters', 'blocktype.wall'),
                    'type'         => 'text',
                    'defaultvalue' => get_config_plugin('blocktype', 'wall', 'defaultpostsizelimit'),
                    'rules' => array( 'maxlength' => 6 ),
                    'description' => get_string('postsizelimitdescription', 'blocktype.wall')
                )
            ),
        );
        return array(
            'elements' => $elements,
        );

    }

    public static function validate_config_options(Pieform $form, $values) {
        if (!is_numeric($values['defaultpostsizelimit'])) {
            $form->set_error('defaultpostsizelimit', get_string('postsizelimitinvalid', 'blocktype.wall'));
        }
        else if ($values['defaultpostsizelimit'] < 0) {
            $form->set_error('defaultpostsizelimit', get_string('postsizelimittoosmall', 'blocktype.wall'));
        }
    }

    public static function save_config_options(Pieform $form, $values) {
        set_config_plugin('blocktype', 'wall', 'defaultpostsizelimit', (int)$values['defaultpostsizelimit']);
    }

    public static function postinst($prevversion) {
        if ($prevversion == 0) {
            set_config_plugin('blocktype', 'wall', 'defaultpostsizelimit', 1500); // 1500 characters
        }

        if ($prevversion < 2016011400) {
            $status = ensure_record_exists(
                    'activity_type',
                    (object)array('name' => 'wallpost'),
                    (object)array(
                        'name' => 'wallpost',
                        'admin' => 0,
                        'delay' => 0,
                        'allowonemethod' => 1,
                        'defaultmethod' => 'email',
                        'plugintype' => 'blocktype',
                        'pluginname' => 'wall'
                    )
            );
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
        return pieform(array(
            'name'      => 'wallpost_'.$instance->get('id'),
            'renderer'  => 'dev',
            'autofocus' => false,
            'jsform'    => true,
            'template'  => 'wallpost.php',
            'templatedir' => pieform_template_dir('wallpost.php', 'blocktype/wall'),
            'validatecallback' => array('PluginBlocktypeWall', 'wallpost_validate'),
            'successcallback' => array('PluginBlocktypeWall', 'wallpost_submit'),
            'jssuccesscallback' => 'wallpost_success',
            'elements' => array(
                'text' => array(
                    'type' => 'wysiwyg',
                    'title' => get_string('Post', 'blocktype.wall'),
                    'hiddenlabel' => true,
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
                    'type' => 'switchbox',
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
                    'class' => 'btn-primary',
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
        var textareaid = 'wallpost_' + data.block + '_text';
        temp.innerHTML = data.posts;
        newposts = getElementsByTagAndClassName('div', 'wallpost', temp);
        replaceChildNodes(wall, newposts);
        if ($(textareaid)) {
            $(textareaid).value = '';
            // Clear TinyMCE
            if (typeof(tinyMCE) != 'undefined' && typeof(tinyMCE.get(textareaid)) != 'undefined') {
                tinyMCE.activeEditor.setContent('');
            }
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
            $form->set_error('text', get_string('newuserscantpostlinksorimages1'));
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

        $newid = insert_record('blocktype_wall_post', $record, 'id', true);

        require_once('embeddedimage.php');
        $newtext = EmbeddedImage::prepare_embedded_images($values['text'], 'wallpost', $newid);
        // If there is an embedded image, update the src so users can have visibility
        if ($values['text'] != $newtext) {
              $updatedwallpost = new stdClass();
              $updatedwallpost->id = $newid;
              $updatedwallpost->text = $newtext;
              update_record('blocktype_wall_post', $updatedwallpost, 'id');
        }

        activity_occurred('wallpost', $record, 'blocktype', 'wall');

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

    /**
     * Shouldn't be linked to any artefacts via the view_artefacts table.
     *
     * @param BlockInstance $instance
     * @return multitype:
     */
    public static function get_artefacts(BlockInstance $instance) {
        return array();
    }
}

class ActivityTypeBlocktypeWallWallpost extends ActivityTypePlugin {
    protected $from;
    protected $text;
    protected $instance;

    public function get_plugintype() {
        return 'blocktype';
    }

    public function get_pluginname() {
        return 'wall';
    }

    public function get_required_parameters() {
        return array('from', 'text', 'instance');
    }

    public function __construct($data, $cron = false) {
        global $CFG;

        parent::__construct($data, $cron);
        $wallinstance = new BlockInstance($data->instance);
        $owner = $wallinstance->get_view()->get('owner');
        // Posted on their own wall. Don't send a notification.
        if ($owner == $data->from) {
            $this->users = array();
            return;
        }

        $this->users = array(get_user($owner));
        $this->fromuser = $data->from;

        $this->strings = (object) array(
                'subject' => (object) array(
                    'key' => 'newwallpostnotificationsubject',
                    'section' => 'blocktype.wall'
                ),
                'message' => (object) array(
                    'key' => 'newwallpostnotificationmessage',
                    'section' => 'blocktype.wall',
                    'args' => array(html2text($data->text))
                ),
                'urltext' => (object) array(
                    'key' => 'wholewall',
                    'section' => 'blocktype.wall'
                )
        );
        $this->url = 'blocktype/wall/wall.php?id=' . $data->instance;
    }
}
