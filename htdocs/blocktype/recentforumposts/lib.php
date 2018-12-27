<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-recentforumposts
 * @author     Nigel McNie
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2009 Nigel McNie http://nigel.mcnie.name/
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeRecentForumPosts extends MaharaCoreBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.recentforumposts');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.recentforumposts');
    }

    public static function get_categories() {
        return array('general' => 23000);
    }

    private static function get_group(BlockInstance $instance, $versioning=false) {
        static $groups = array();

        if ($versioning) {
            $configdata = $instance->get('configdata');
            $groupid = $configdata['groupid'];
            return get_record_select('group', 'id = ? AND deleted = 0', array($groupid), '*, ' . db_format_tsfield('ctime'));
        }

        $block = $instance->get('id');

        if (!isset($groups[$block])) {

            // When this block is in a group view it should always display the
            // forum posts from that group

            $groupid = $instance->get_view()->get('group');
            $configdata = $instance->get('configdata');

            if (!$groupid && !empty($configdata['groupid'])) {
                $groupid = intval($configdata['groupid']);
            }

            if ($groupid) {
                $groups[$block] = get_record_select('group', 'id = ? AND deleted = 0', array($groupid), '*, ' . db_format_tsfield('ctime'));
            }
            else {
                $groups[$block] = false;
            }
        }

        return $groups[$block];
    }

    public static function render_instance(BlockInstance $instance, $editing=false, $versioning=false) {
        if ($group = self::get_group($instance, $versioning)) {

            require_once('group.php');
            $role = group_user_access($group->id);

            if ($role || $group->public) {
                $limit = 5;
                $configdata = $instance->get('configdata');
                if (!empty($configdata['limit'])) {
                    $limit = intval($configdata['limit']);
                }

                $foruminfo = get_records_sql_array('
                    SELECT
                        p.id, p.subject, p.body, p.poster, p.topic, pt.approved, t.forum, pt.subject AS topicname,
                        u.firstname, u.lastname, u.username, u.preferredname, u.email, u.profileicon, u.admin, u.staff, u.deleted, u.urlid
                    FROM
                        {interaction_forum_post} p
                        INNER JOIN {interaction_forum_topic} t ON (t.id = p.topic)
                        INNER JOIN {interaction_instance} i ON (i.id = t.forum)
                        INNER JOIN {interaction_forum_post} pt ON (pt.topic = p.topic AND pt.parent IS NULL)
                        INNER JOIN {usr} u ON p.poster = u.id
                    WHERE
                        i.group = ?
                        AND i.deleted = 0
                        AND t.deleted = 0
                        AND p.deleted = 0
                        AND pt.approved = 1
                    ORDER BY
                        p.ctime DESC',
                    array($group->id), 0, $limit
                );

                if ($foruminfo) {
                    $userfields = array(
                        'firstname', 'lastname', 'username', 'preferredname', 'email', 'profileicon',
                        'admin', 'staff', 'deleted', 'urlid',
                    );
                    foreach ($foruminfo as $f) {
                        $f->author = (object) array('id' => $f->poster);
                        foreach ($userfields as $uf) {
                            $f->author->$uf = $f->$uf;
                            unset($f->$uf);
                        }
                        $f->filecount = 0;
                        if ($f->attachments = get_records_sql_array("
                                 SELECT a.*, aff.size, aff.fileid, pa.post
                                 FROM {artefact} a
                                 JOIN {interaction_forum_post_attachment} pa ON pa.attachment = a.id
                                 LEFT JOIN {artefact_file_files} aff ON aff.artefact = a.id
                                 WHERE pa.post = ?", array($f->id))) {
                            $f->filecount = count($f->attachments);
                            safe_require('artefact', 'file');
                            foreach ($f->attachments as $file) {
                                $file->icon = call_static_method(generate_artefact_class_name($file->artefacttype), 'get_icon', array('id' => $file->id, 'post' => $f->id));
                            }
                        }
                    }
                }

                $smarty = smarty_core();
                $smarty->assign('group', $group);
                $smarty->assign('foruminfo', $foruminfo);
                if ($instance->get_view()->get('type') == 'grouphomepage') {
                    return $smarty->fetch('blocktype:recentforumposts:latestforumposts.tpl');
                }
                return $smarty->fetch('blocktype:recentforumposts:recentforumposts.tpl');
            }
        }

        return '';
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form(BlockInstance $instance) {
        global $USER;

        $elements   = array();
        $groupid    = $instance->get_view()->get('group');
        $configdata = $instance->get('configdata');

        if ($groupid || $instance->get_view()->get('institution')) {
            // This block will show recent forum posts from this group
            $elements['groupid'] = array(
                'type' => 'hidden',
                'value' => $groupid,
            );
        }
        else {
            // Allow the user to choose a group they're in to show posts for
            if (!empty($configdata['groupid'])) {
                $groupid = intval($configdata['groupid']);
                $group = get_record_select('group', 'id = ? AND deleted = 0', array($groupid), '*, ' . db_format_tsfield('ctime'));
            }

            $usergroups = get_records_sql_array(
                "SELECT g.id, g.name
                FROM {group} g
                JOIN {group_member} gm ON (gm.group = g.id)
                WHERE gm.member = ?
                AND g.deleted = 0
                ORDER BY g.name", array($USER->get('id')));

            if ($usergroups) {
                $choosablegroups = array();
                foreach ($usergroups as $group) {
                    $choosablegroups[$group->id] = $group->name;
                }
                $elements['groupid'] =  array(
                    'type'  => 'select',
                    'title' => get_string('group', 'blocktype.recentforumposts'),
                    'options' => $choosablegroups,
                    'collapseifoneoption' => false,
                    'defaultvalue' => $groupid,
                    'rules' => array(
                        'required' => true,
                    ),
                );
            }
        }

        if (isset($elements['groupid'])) {
            $elements['limit'] = array(
                'type' => 'text',
                'title' => get_string('poststoshow', 'blocktype.recentforumposts'),
                'description' => get_string('poststoshowdescription', 'blocktype.recentforumposts'),
                'defaultvalue' => (isset($configdata['limit'])) ? intval($configdata['limit']) : 5,
                'size' => 3,
                'minvalue' => 1,
                'maxvalue' => 100,
            );
        }
        else {
            $elements = array(
                'whoops' => array(
                    'type' => 'html',
                    'value' => '<p class="noartefacts">' . get_string('nogroupstochoosefrom', 'blocktype.recentforumposts') . '</p>',
                ),
            );
        }

        return $elements;
    }

    public static function default_copy_type() {
        return 'shallow';
    }

    public static function feed_url(BlockInstance $instance) {
        if ($group = self::get_group($instance)) {
            if ($group->public) {
                return get_config('wwwroot') . 'interaction/forum/atom.php?type=g&id=' . $group->id;
            }
        }
    }

    public static function get_instance_title(BlockInstance $instance) {
        if ($instance->get_view()->get('type') == 'grouphomepage') {
            return get_string('latestforumposts', 'interaction.forum');
        }
        return get_string('title', 'blocktype.recentforumposts');
    }

    public static function should_ajaxify() {
        return true;
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
