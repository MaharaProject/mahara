<?php
/**
 *
 * @package    mahara
 * @subpackage interaction-forum
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

require_once('activity.php');

// Contstants for objectionable content reporting events.
define('REPORT_OBJECTIONABLE', 1);
define('MAKE_NOT_OBJECTIONABLE', 2);
define('DELETE_OBJECTIONABLE_POST', 3);
define('DELETE_OBJECTIONABLE_TOPIC', 4);

class PluginInteractionForum extends PluginInteraction {

    public static function instance_config_form($group, $instance=null) {
        if (isset($instance)) {
            $instanceconfig = get_records_assoc('interaction_forum_instance_config', 'forum', $instance->get('id'), '', 'field,value');
            $autosubscribe = isset($instanceconfig['autosubscribe']) ? $instanceconfig['autosubscribe']->value : false;
            $weight = isset($instanceconfig['weight']) ? $instanceconfig['weight']->value : null;
            $createtopicusers = isset($instanceconfig['createtopicusers']) ? $instanceconfig['createtopicusers']->value : null;
            $closetopics = !empty($instanceconfig['closetopics']);
            $indentmode = isset($instanceconfig['indentmode']) ? $instanceconfig['indentmode']->value : null;
            $maxindent = isset($instanceconfig['maxindent']) ? $instanceconfig['maxindent']->value : null;

            $moderators = get_forum_moderators($instance->get('id'));
        }

        if ($instance === null) {
            $exclude = '';
        }
        else {
            $exclude = 'AND i.id != ' . db_quote($instance->get('id'));
        }

        $existing = get_records_sql_array('
            SELECT i.id, i.title, c.value AS weight
            FROM {interaction_instance} i
            INNER JOIN {interaction_forum_instance_config} c ON (i.id = c.forum AND c.field = \'weight\')
            WHERE i.group = ?
            AND i.deleted != 1
            ' . $exclude . '
            ORDER BY CHAR_LENGTH(c.value), c.value',
            array($group->id));
        if ($existing) {
            foreach ($existing as &$item) {
                $item = (array)$item;
            }
        }
        else {
            $existing = array();
        }

        return array(
            'indentmode' => array(
                'type'         => 'select',
                'title'        => get_string('indentmode', 'interaction.forum'),
                'options'      => array('full_indent'  => get_string('indentfullindent', 'interaction.forum'),
                                        'max_indent'   => get_string('indentmaxindent', 'interaction.forum'),
                                        'no_indent'    => get_string('indentflatindent', 'interaction.forum') ),
                'description'  => get_string('indentmodedescription', 'interaction.forum'),
                'defaultvalue' => isset($indentmode) ? $indentmode : 'full_indent',
                'help' => true,
                'rules' => array(
                    'required' => true,
                ),
            ),
            'maxindent' => array(
                'type'         => 'text',
                'title'        => get_string('maxindent', 'interaction.forum'),
                'size'         => 2,
                'defaultvalue' => isset($maxindent) ? $maxindent : 10,
                'class'        => (isset($indentmode) && $indentmode == 'max_indent') ? '' : 'hidden',
                'rules' => array(
                    'integer' => true,
                    'minvalue' => 1,
                    'maxvalue' => 100,
                ),
            ),
            'fieldset' => array(
                'type' => 'fieldset',
                'class' => 'last',
                'collapsible' => true,
                'collapsed' => true,
                'legend' => get_string('forumsettings', 'interaction.forum'),
                'elements' => array(
                    'autosubscribe' => array(
                        'type'         => 'switchbox',
                        'title'        => get_string('autosubscribeusers', 'interaction.forum'),
                        'description'  => get_string('autosubscribeusersdescription', 'interaction.forum'),
                        'defaultvalue' => isset($autosubscribe) ? $autosubscribe : true,
                        'help'         => true,
                    ),
                    'weight' => array(
                        'type' => 'weight',
                        'title' => get_string('Order', 'interaction.forum'),
                        'description' => get_string('orderdescription', 'interaction.forum'),
                        'defaultvalue' => isset($weight) ? $weight : count($existing),
                        'rules' => array(
                            'required' => true,
                        ),
                        'existing' => $existing,
                        'ignore'   => (count($existing) == 0)
                    ),
                    'moderator' => array(
                        'type' => 'userlist',
                        'title' => get_string('Moderators', 'interaction.forum'),
                        'description' => get_string('moderatorsdescription', 'interaction.forum'),
                        'defaultvalue' => isset($moderators) ? $moderators : null,
                        'group' => $group->id,
                        'includeadmins' => false,
                        'lefttitle' => get_string('potentialmoderators', 'interaction.forum'),
                        'righttitle' => get_string('currentmoderators', 'interaction.forum')
                    ),
                    'createtopicusers' => array(
                        'type'         => 'select',
                        'title'        => get_string('whocancreatetopics', 'interaction.forum'),
                        'options'      => array('members'    => get_string('allgroupmembers', 'group'),
                                                'moderators' => get_string('moderatorsandgroupadminsonly', 'interaction.forum')),
                        'description'  => get_string('createtopicusersdescription', 'interaction.forum'),
                        'defaultvalue' => (isset($createtopicusers) && $createtopicusers == 'moderators') ? 'moderators' : 'members',
                        'rules' => array(
                            'required' => true,
                        ),
                    ),
                    'closetopics' => array(
                        'type'         => 'switchbox',
                        'title'        => get_string('closetopics', 'interaction.forum'),
                        'description'  => get_string('closetopicsdescription1', 'interaction.forum'),
                        'defaultvalue' => !empty($closetopics),
                    ),
                )
            )
        );
    }

    public static function instance_config_js() {
        return <<<EOF
function update_maxindent() {
    var s = $('edit_interaction_indentmode');
    var m = $('edit_interaction_maxindent_container');
    var t = $('edit_interaction_maxindent');
    if (!m) {
        return;
    }
    if (s.options[s.selectedIndex].value == 'max_indent') {
        removeElementClass(m, 'hidden');
        removeElementClass(t, 'hidden');
    }
    else {
        addElementClass(m, 'hidden');
        addElementClass(t, 'hidden');
    }
}
addLoadEvent(function() {
    connect('edit_interaction_indentmode', 'onchange', update_maxindent);
});
EOF;
    }

    public static function instance_config_save($instance, $values){
        db_begin();

        // Autosubscribe
        delete_records_sql(
            "DELETE FROM {interaction_forum_instance_config}
            WHERE field = 'autosubscribe' AND forum = ?",
            array($instance->get('id'))
        );
        insert_record('interaction_forum_instance_config', (object)array(
            'forum' => $instance->get('id'),
            'field' => 'autosubscribe',
            'value' => (bool)$values['autosubscribe'],
        ));

        if ($values['justcreated'] && $values['autosubscribe']) {
            // Subscribe all existing users in the group to the forums
            if ($userids = get_column('group_member', 'member', 'group', $instance->get('group'))) {
                foreach ($userids as $userid) {
                    insert_record(
                        'interaction_forum_subscription_forum',
                        (object)array(
                            'forum' => $instance->get('id'),
                            'user'  => $userid,
                            'key'   => PluginInteractionForum::generate_unsubscribe_key(),
                        )
                    );
                }
            }
        }

        // Moderators
        delete_records(
            'interaction_forum_moderator',
            'forum', $instance->get('id')
        );
        if (isset($values['moderator']) && is_array($values['moderator'])) {
            foreach ($values['moderator'] as $user) {
                insert_record(
                    'interaction_forum_moderator',
                    (object)array(
                        'user' => $user,
                        'forum' => $instance->get('id')
                    )
                );
            }
        }

        // Re-order the forums according to their new ordering
        delete_records_sql(
            'DELETE FROM {interaction_forum_instance_config}
            WHERE field = \'weight\' AND forum IN (
                SELECT id FROM {interaction_instance} WHERE "group" = ?
            )',
            array($instance->get('group'))
        );

        if (isset($values['weight'])) {
            foreach ($values['weight'] as $weight => $id) {
                if ($id === null) {
                    // This is where the current forum is to be placed
                    $id = $instance->get('id');
                }

                insert_record(
                    'interaction_forum_instance_config',
                    (object)array(
                        'forum' => $id,
                        'field' => 'weight',
                        'value' => $weight,
                    )
                );
            }
        }
        else {
            // Element was ignored - because this is the first forum in a group
            insert_record(
                'interaction_forum_instance_config',
                (object)array(
                    'forum' => $instance->get('id'),
                    'field' => 'weight',
                    'value' => 0,
                )
            );
        }

        // Create topic users
        delete_records_sql(
            "DELETE FROM {interaction_forum_instance_config}
            WHERE field = 'createtopicusers' AND forum = ?",
            array($instance->get('id'))
        );
        insert_record('interaction_forum_instance_config', (object)array(
            'forum' => $instance->get('id'),
            'field' => 'createtopicusers',
            'value' => $values['createtopicusers'] == 'moderators' ? 'moderators' : 'members',
        ));

        // Close topics
        delete_records_sql(
            "DELETE FROM {interaction_forum_instance_config}
            WHERE field = 'closetopics' AND forum = ?",
            array($instance->get('id'))
        );
        if (!empty($values['closetopics'])) {
            insert_record('interaction_forum_instance_config', (object)array(
                'forum' => $instance->get('id'),
                'field' => 'closetopics',
                'value' => 1,
            ));
        }

        //Indent mode
        delete_records_sql(
            "DELETE FROM {interaction_forum_instance_config}
            WHERE field = 'indentmode' AND forum = ?",
            array($instance->get('id'))
        );
        if (!isset($values['indentmode'])) {
            $values['indentmode'] = 'full_indent';
        }
        insert_record('interaction_forum_instance_config', (object)array(
            'forum' => $instance->get('id'),
            'field' => 'indentmode',
            'value' => $values['indentmode'],
        ));

        //Max indent
        delete_records_sql(
            "DELETE FROM {interaction_forum_instance_config}
            WHERE field = 'maxindent' AND forum = ?",
            array($instance->get('id'))
        );
        if (!isset($values['maxindent'])) {
            $values['maxindent'] = 10;
        }
        insert_record('interaction_forum_instance_config', (object)array(
            'forum' => $instance->get('id'),
            'field' => 'maxindent',
            'value' => $values['maxindent'],
        ));


        db_commit();
    }

    public static function postinst($prevversion) {
        // On a new installation, set post delay to 30 minutes
        if ($prevversion == 0) {
            set_config_plugin('interaction', 'forum', 'postdelay', 30);
        }
    }

    public static function get_activity_types() {
        return array(
            (object)array(
                'name' => 'newpost',
                'admin' => 0,
                'delay' => 1,
                'allownonemethod' => 1,
                'defaultmethod' => 'email',
            ),
            (object)array(
                'name' => 'reportpost',
                'admin' => 1,
                'delay' => 0,
                'allownonemethod' => 1,
                'defaultmethod' => 'email',
            ),
        );
    }

    public static function get_cron() {
        return array(
            (object)array(
                'callfunction' => 'interaction_forum_new_post',
                'minute'       => '*/30',
            ),
            (object)array(
                'callfunction' => 'clean_forum_notifications',
                'minute'       => '30',
                'hour'         => '22',
            ),
        );
    }

    public static function clean_forum_notifications() {
        safe_require('notification', 'internal');
        PluginNotificationInternal::clean_notifications(array('newpost'));
    }

    /**
     * Subscribes the forum plugin to events
     *
     * @return array
     */
    public static function get_event_subscriptions() {
        return array(
            (object)array(
                'plugin'       => 'forum',
                'event'        => 'userjoinsgroup',
                'callfunction' => 'user_joined_group',
            ),
            (object)array(
                'plugin'       => 'forum',
                'event'        => 'creategroup',
                'callfunction' => 'create_default_forum',
            ),
        );
    }

    public static function menu_items() {
        return array(
            'groups/topics' => array(
                'path' => 'groups/topics',
                'url' => 'group/topics.php',
                'title' => get_string('Topics', 'interaction.forum'),
                'weight' => 70,
            ),
        );
    }

    public static function group_menu_items($group) {
        global $USER;
        $role = group_user_access($group->id);
        $hasobjectionable = false;
        if (!$role && $USER->get('admin')) {
            // No role, but site admin - see if there is objectionable content,
            // so that menu item should be displayed.
            foreach (self::get_instance_ids($group->id) as $instanceid) {
                $instance = new InteractionForumInstance($instanceid);
                if ($instance->has_objectionable()) {
                    $hasobjectionable = true;
                    break;
                }
            }
        }
        $menu = array();
        if ($group->public || $role || ($hasobjectionable && $USER->get('admin'))) {
            $menu['forums'] = array(// @todo: make forums an artefact plugin
                'path' => 'groups/forums',
                'url' => 'interaction/forum/index.php?group=' . $group->id,
                'title' => get_string('nameplural', 'interaction.forum'),
                'weight' => 40,
            );
        }
        return $menu;
    }

    /**
     * When a user joins a group, subscribe them automatically to all forums
     * that should be subscribable
     *
     * @param array $eventdata
     */
    public static function user_joined_group($event, $gm) {
        if ($forumids = get_column_sql("
            SELECT ii.id
            FROM {group} g
            LEFT JOIN {interaction_instance} ii ON g.id = ii.group
            LEFT JOIN {interaction_forum_instance_config} ific ON ific.forum = ii.id
            WHERE \"group\" = ? AND ific.field = 'autosubscribe' and ific.value = '1'",
            array($gm['group']))) {
            db_begin();
            foreach ($forumids as $forumid) {
                insert_record(
                    'interaction_forum_subscription_forum',
                    (object)array(
                        'forum' => $forumid,
                        'user'  => $gm['member'],
                        'key'   => PluginInteractionForum::generate_unsubscribe_key(),
                    )
                );
            }
            db_commit();
        }
    }

    /**
     * When a group is created, create one forum automatically.
     *
     * @param array $eventdata
     */
    public static function create_default_forum($event, $eventdata) {
        global $USER;
        $creator = 0;
        if (isset($eventdata['members'][$USER->get('id')])) {
            $creator = $USER->get('id');
        }
        else {
            foreach($eventdata['members'] as $userid => $role) {
                if ($role == 'admin') {
                    $creator = $userid;
                    break;
                }
            }
        }
        db_begin();
        $forum = new InteractionForumInstance(0, (object) array(
            'group'       => $eventdata['id'],
            'creator'     => $creator,
            'title'       => get_string('defaultforumtitle', 'interaction.forum'),
            'description' => get_string('defaultforumdescription', 'interaction.forum', $eventdata['name']),
        ));
        $forum->commit();
        self::instance_config_save($forum, array(
            'createtopicusers' => 'members',
            'autosubscribe'    => 1,
            'justcreated'      => 1,
        ));
        db_commit();
    }

    /**
     * Optional method. Takes a list of forums and sorts them according to
     * their weights for the sideblock
     *
     * @param array $forums An array of hashes of forum data
     * @return array        The array, sorted appropriately
     */
    public static function sideblock_sort($forums) {
        if (!$forums) {
            return $forums;
        }

        $weights = get_records_assoc('interaction_forum_instance_config', 'field', 'weight', 'forum', 'forum, value');
        foreach ($forums as &$forum) {
            // Note: forums expect every forum to have a 'weight' record in the
            // forum instance config table, so we don't need to check that
            // there is a weight for the forum here - there should be,
            // otherwise someone has futz'd with the database or there's a bug
            // elsewhere that allowed this to happen
            $forum->weight = $weights[$forum->id]->value;
        }
        usort($forums, create_function('$a, $b', 'return $a->weight > $b->weight;'));
        return $forums;
    }


    /**
     * Process new forum posts.
     *
     * @param array $postnow An array of post ids to be sent immediately.  If null, send all posts older than postdelay.
     */
    public static function interaction_forum_new_post($postnow=null) {
        if (is_array($postnow) && !empty($postnow)) {
            $values = array();
            $postswhere = 'id IN (' . join(',', array_map('intval', $postnow)) . ')';
            $delay = false;
        }
        else {
            $currenttime = time();
            $minpostdelay = $currenttime - get_config_plugin('interaction', 'forum', 'postdelay') * 60;
            $values = array(db_format_timestamp($minpostdelay));
            $postswhere = 'ctime < ?';
            $delay = null;
        }
        $posts = get_column_sql('SELECT id FROM {interaction_forum_post} WHERE sent = 0 AND deleted = 0 AND ' . $postswhere, $values);
        if ($posts) {
            set_field_select('interaction_forum_post', 'sent', 1, 'deleted = 0 AND sent = 0 AND ' . $postswhere, $values);
            foreach ($posts as $postid) {
                activity_occurred('newpost', array('postid' => $postid), 'interaction', 'forum', $delay);
            }
        }
    }

    public static function can_be_disabled() {
        return false; //TODO until it either becomes an artefact plugin or stops being hardcoded everywhere
    }

    /**
     * Generates a random key to use for unsubscription requests.
     *
     * See the interaction_forum_subscription_* tables and related operations
     * on them for more information.
     *
     * @return string A random key
     */
    public static function generate_unsubscribe_key() {
        return dechex(mt_rand());
    }


    public static function has_config() {
        return true;
    }

    public static function get_config_options() {
        $postdelay = (int) get_config_plugin('interaction', 'forum', 'postdelay');

        return array(
            'elements' => array(
                'postdelay' => array(
                    'title'        => get_string('postdelay', 'interaction.forum'),
                    'description'  => get_string('postdelaydescription', 'interaction.forum'),
                    'type'         => 'text',
                    'class'      => '',
                    'rules'        => array('integer' => true, 'minvalue' => 0, 'maxvalue' => 10000000),
                    'defaultvalue' => $postdelay,
                ),
            ),
        );
    }

    public static function save_config_options(Pieform $form, $values) {
        set_config_plugin('interaction', 'forum', 'postdelay', $values['postdelay']);
    }

    public static function get_active_topics($limit, $offset, $category, $forumids = array()) {
        global $USER;

        if (is_postgres()) {
            $lastposts = '
                    SELECT DISTINCT ON (topic) topic, id, poster, subject, body, ctime
                    FROM {interaction_forum_post} p
                    WHERE p.deleted = 0
                    ORDER BY topic, ctime DESC';
        }
        else if (is_mysql()) {
            $lastposts = '
                    SELECT topic, id, poster, subject, body, ctime
                    FROM (
                        SELECT topic, id, poster, subject, body, ctime
                        FROM {interaction_forum_post}
                        WHERE deleted = 0
                        ORDER BY ctime DESC
                    ) temp1
                    GROUP BY topic';
        }

        $values = array();
        $from = '
            FROM
                {interaction_forum_topic} t
                JOIN {interaction_instance} f ON t.forum = f.id
                JOIN {group} g ON f.group = g.id';

        // user is not anonymous
        if ($USER->get('id') > 0) {
            $from .= '
                JOIN {group_member} gm ON (gm.group = g.id AND gm.member = ?)
            ';

            $values[] = $USER->get('id');
        }

        $from .= '
                JOIN {interaction_forum_post} first ON (first.topic = t.id AND first.parent IS NULL)
                JOIN (' . $lastposts . '
                ) last ON last.topic = t.id';

        $where = '
            WHERE g.deleted = 0 AND f.deleted = 0 AND t.deleted = 0';

        if (!empty($category)) {
            $where .= ' AND g.category = ?';
            $values[] = (int) $category;
        }

        if (!empty($forumids)) {
            $where .= ' AND f.id IN (' . join(',', array_fill(0, count($forumids), '?')) . ')';
            $values = array_merge($values, $forumids);
        }

        $result = array(
            'count'  => count_records_sql('SELECT COUNT(*) ' . $from . $where, $values),
            'limit'  => $limit,
            'offset' => $offset,
            'data'   => array(),
        );

        if (!$result['count']) {
            return $result;
        }

        $select = '
            SELECT
                t.id, t.forum AS forumid, f.title AS forumname, g.id AS groupid, g.name AS groupname, g.urlid,
                first.subject AS topicname, first.poster AS firstpostby,
                last.id AS postid, last.poster, last.subject, last.body, last.ctime, edits.ctime as mtime,
                COUNT(posts.id) AS postcount';

        $from .= '
                LEFT JOIN {interaction_forum_post} posts ON posts.topic = t.id
                LEFT JOIN {interaction_forum_edit} edits ON edits.post = last.id';

        $sort = '
            GROUP BY
                t.id, t.forum, f.title, g.id, g.name, g.urlid,
                first.subject, first.poster,
                last.id, last.poster, last.subject, last.body, last.ctime, edits.ctime
            ORDER BY last.ctime DESC';

        $result['data'] = get_records_sql_array($select . $from . $where . $sort, $values, $offset, $limit);

        foreach($result['data'] as &$r) {
            $r->groupurl = group_homepage_url((object) array('id' => $r->groupid, 'urlid' => $r->urlid));
        }

        return $result;
    }

    // Rewrite download links in the post body to add a post id parameter.
    // Used in download.php to determine permission to view the file.
    static $replacement_postid;

    public static function replace_download_link($matches) {
        parse_str(html_entity_decode($matches[1]), $params);
        if (empty($params['file'])) {
            return $matches[0];
        }
        $url = get_config('wwwroot') . 'artefact/file/download.php?file=' . (int) $params['file'];
        unset($params['post']);
        unset($params['file']);
        if (!empty($params)) {
            $url .= '&' . http_build_query($params);
        }
        return $url . '&post=' . (int) self::$replacement_postid;
    }

    public static function prepare_post_body($body, $postid) {
        self::$replacement_postid = $postid;
        return preg_replace_callback(
            '#(?<=[\'"])' . get_config('wwwroot') . 'artefact/file/download\.php\?(file=\d+(?:(?:&amp;|&)(?:[a-z]+=[x0-9]+)+)*)#',
            array('self', 'replace_download_link'),
            $body
        );
    }

    /**
     * Given a post id & the id of an image artefact, check that the logged-in user
     * has permission to see the image in the context of the post.
     */
    public static function can_see_attached_file($file, $postid) {
        global $USER;
        require_once('group.php');

        if (!$file instanceof ArtefactTypeImage) {
            return false;
        }

        $post = get_record_sql('
            SELECT
                p.body, p.poster, g.id AS groupid, g.public
            FROM {interaction_forum_post} p
            INNER JOIN {interaction_forum_topic} t ON (t.id = p.topic AND t.deleted = 0)
            INNER JOIN {interaction_forum_post} fp ON (fp.parent IS NULL AND fp.topic = t.id)
            INNER JOIN {interaction_instance} f ON (t.forum = f.id AND f.deleted = 0)
            INNER JOIN {group} g ON (f.group = g.id AND g.deleted = 0)
            WHERE p.id = ? AND p.deleted = 0',
            array($postid)
        );

        if (!$post) {
            return false;
        }

        if (!$post->public && !group_user_access($post->groupid, $USER->get('id'))) {
            return false;
        }

        // Check that the author of the post is allowed to publish the file
        $poster = new User();
        $poster->find_by_id($post->poster);
        if (!$poster->can_publish_artefact($file)) {
            return false;
        }

        // Load the post as an html fragment & make sure it has the image in it
        $page = new DOMDocument();
        libxml_use_internal_errors(true);
        $success = $page->loadHTML($post->body);
        libxml_use_internal_errors(false);
        if (!$success) {
            return false;
        }
        $xpath = new DOMXPath($page);
        $srcstart = get_config('wwwroot') . 'artefact/file/download.php?file=' . $file->get('id') . '&';
        $query = '//img[starts-with(@src,"' . $srcstart . '")]';
        $elements = $xpath->query($query);
        if ($elements->length < 1) {
            return false;
        }

        return true;
    }

    /**
     * Return number of forums associated to a group
     *
     * @param  $groupid: the group ID number
     * @return the number of forums
     *     OR null if invalid $groupid
     */
    public static function count_group_forums($groupid) {
        if ($groupid && $groupid > 0) {
            return count_records_select('interaction_instance', '"group" = ? AND deleted != 1', array($groupid), 'COUNT(id)');
        }
        return null;
    }

    /**
     * Return number of topics associated to a group
     *
     * @param  $groupid: the group ID number
     * @return the number of topics
     *     OR null if invalid $groupid
     */
    public static function count_group_topics($groupid) {
        if ($groupid && $groupid > 0) {
            return count_records_sql('SELECT COUNT(t.id)
                    FROM {interaction_instance} f
                    JOIN {interaction_forum_topic} t ON t.forum = f.id AND t.deleted != 1
                    WHERE f.group = ?
                        AND f.deleted != 1', array($groupid));
        }
        return null;
    }

    /**
     * Return number of posts associated to a group
     *
     * @param  $groupid: the group ID number
     * @return the number of posts
     *     OR null if invalid $groupid
     */
    public static function count_group_posts($groupid) {
        if ($groupid && $groupid > 0) {
            return count_records_sql('SELECT COUNT(p.id)
                    FROM {interaction_instance} f
                    JOIN {interaction_forum_topic} t ON t.forum = f.id AND t.deleted != 1
                    JOIN {interaction_forum_post} p ON p.topic = t.id AND p.deleted != 1
                    WHERE f.group = ?
                    AND f.deleted != 1', array($groupid));
        }
        return null;
    }

    /**
     * Return IDs of plugin instances
     *
     * @param  int $groupid optional group ID number
     * @return array list of the instance IDs
     */
    public static function get_instance_ids($groupid = null) {
        if (isset($groupid) && $groupid > 0) {
            return get_column('interaction_instance', 'id', 'plugin', 'forum', 'group', $groupid, 'deleted', 0);
        }
        return get_column('interaction_instance', 'id', 'plugin', 'forum', 'deleted', 0);
    }
}

class InteractionForumInstance extends InteractionInstance {

    public static function get_plugin() {
        return 'forum';
    }

    public function delete() {
        if (empty($this->id)) {
            $this->dirty = false;
            return;
        }

        db_begin();
        // Delete embedded images in the forum description
        require_once('embeddedimage.php');
        EmbeddedImage::delete_embedded_images('forum', $this->id);
        // Delete the interaction instance
        parent::delete();
        db_commit();
    }

    public function commit() {
        if (empty($this->dirty)) {
            return;
        }

        db_begin();
        parent::commit();
        // Update embedded images in the forum description
        require_once('embeddedimage.php');
        $newdescription = EmbeddedImage::prepare_embedded_images($this->description, 'forum', $this->id, $this->group);
        set_field('interaction_instance', 'description', $newdescription, 'id', $this->id);
        db_commit();
    }

    public function interaction_remove_user($userid) {
        delete_records('interaction_forum_moderator', 'forum', $this->id, 'user', $userid);
        delete_records('interaction_forum_subscription_forum', 'forum', $this->id, 'user', $userid);
        delete_records_select('interaction_forum_subscription_topic',
            'user = ? AND topic IN (SELECT id FROM {interaction_forum_topic} WHERE forum = ?)',
            array($userid, $this->id)
        );
    }

   /**
    * Check if forum instance contains reported content.
    *
    * @returns bool $reported whether forum contains reported content.
    */
   public function has_objectionable() {
       $reported = count_records_sql(
           "SELECT count(fp.id) FROM {interaction_forum_topic} ft
            JOIN {interaction_forum_post} fp ON (ft.id = fp.topic)
            JOIN {objectionable} o ON (o.objecttype = 'forum' AND o.objectid = fp.id)
            WHERE fp.deleted = 0 AND o.resolvedby IS NULL AND o.resolvedtime IS NULL AND ft.forum = ?", array($this->id)
       );
       return (bool) $reported;
   }
}

class ActivityTypeInteractionForumNewPost extends ActivityTypePlugin {

    protected $postid;
    protected $temp;

    public function __construct($data, $cron=false) {
        parent::__construct($data, $cron);
        $this->overridemessagecontents = true;

        $post = get_record_sql('
            SELECT
                p.subject, p.body, p.poster, p.parent, ' . db_format_tsfield('p.ctime', 'ctime') . ',
                t.id AS topicid, fp.subject AS topicsubject, f.title AS forumtitle, g.name AS groupname, f.id AS forumid
            FROM {interaction_forum_post} p
            INNER JOIN {interaction_forum_topic} t ON (t.id = p.topic AND t.deleted = 0)
            INNER JOIN {interaction_forum_post} fp ON (fp.parent IS NULL AND fp.topic = t.id)
            INNER JOIN {interaction_instance} f ON (t.forum = f.id AND f.deleted = 0)
            INNER JOIN {group} g ON (f.group = g.id AND g.deleted = 0)
            WHERE p.id = ? AND p.deleted = 0',
            array($this->postid)
        );

        // The post may have been deleted during the activity delay
        if (!$post) {
            $this->users = array();
            return;
        }

        // A user may be subscribed via the forum or the specific topic. If they're subscribed to both, we want
        // to focus on the topic subscription because it's more specific.
        $sql = '
            SELECT
                subq2.subscriber,
                (CASE WHEN subq2.topickey IS NOT NULL THEN subq2.topickey ELSE subq2.forumkey END) AS "key",
                (CASE WHEN subq2.topickey IS NOT NULL THEN \'topic\' ELSE \'forum\' END) AS "type"
            FROM (
                SELECT subq1.subscriber, max(topickey) AS topickey, max(forumkey) AS forumkey
                FROM (
                    SELECT "user" AS subscriber, "key" AS topickey, NULL AS forumkey FROM {interaction_forum_subscription_topic} WHERE topic = ?
                    UNION ALL
                    SELECT "user" AS subscriber, NULL AS topickey, "key" AS forumkey FROM {interaction_forum_subscription_forum} WHERE forum = ?
                ) subq1
                GROUP BY subq1.subscriber
            ) subq2
            INNER JOIN {usr} u ON subq2.subscriber = u.id
            WHERE u.deleted = 0
        ';
        $params = array($post->topicid, $post->forumid);
        if ($cron) {
            $sql .= ' AND subq2.subscriber > ? ';
            $params[] = (int) $data->last_processed_userid;
            $limitfrom = 0;
            $limitnum = self::USERCHUNK_SIZE;
        }
        else {
            $limitfrom = '';
            $limitnum = '';
        }
        $sql .= ' ORDER BY subq2.subscriber';

        $subscribers = get_records_sql_assoc($sql, $params, $limitfrom, $limitnum);

        $this->users = $subscribers ? activity_get_users($this->get_id(), array_keys($subscribers)) : array();
        $this->fromuser = $post->poster;

        // When emailing forum posts, create Message-Id headers for threaded display by email clients
        $urlinfo = parse_url(get_config('wwwroot'));
        $hostname = $urlinfo['host'];
        $cleanforumname = clean_email_headers($post->forumtitle);
        $cleangroupname = clean_email_headers($post->groupname);
        $cleanforumname = str_replace('"', "'", strip_tags($cleanforumname));
        $cleangroupname = str_replace('"', "'", strip_tags($cleangroupname));
        $this->customheaders = array(
            'List-Id: "' . $cleanforumname . '" <forum' . $post->forumid . '@' . $hostname . '>',
            'List-Help: ' . get_config('wwwroot') . 'interaction/forum/view.php?id=' . $post->forumid,
            'Message-ID: <forumpost' . $this->postid . '@' . $hostname . '>',
            'X-Mahara-Group: ' . $cleangroupname,
            'X-Mahara-Forum: ' . $cleanforumname
        );
        if ($post->parent) {
            $this->customheaders[] = 'In-Reply-To: <forumpost' . $post->parent . '@' . $hostname . '>';
            $this->customheaders[] = 'References: <forumpost' . $post->parent . '@' . $hostname . '>';
        }

        $post->posttime = strftime(get_string('strftimedaydatetime'), $post->ctime);
        // Some messages are all html and when they're 'cleaned' with
        // strip_tags(str_shorten_html($post->body, 200, true)) for display,
        // they are left empty. Use html2text instead.
        $this->message = str_shorten_text(trim(html2text($post->body)), 200, true); // For internal notifications.

        $post->textbody = trim(html2text($post->body));
        $post->htmlbody = clean_html($post->body);
        $this->url = 'interaction/forum/topic.php?id=' . $post->topicid . '&post=' . $this->postid;

        $this->add_urltext(array(
            'key'     => 'Topic',
            'section' => 'interaction.forum'
        ));

        $this->strings->subject = (object) array(
            'key'     => 'newforumpostnotificationsubjectline',
            'section' => 'interaction.forum',
            'args'    => array($post->subject ? $post->subject : get_string('Re:', 'interaction.forum') . ($post->parent ? get_ancestorpostsubject($post->parent, true) : $post->topicsubject)),
        );

        foreach ($this->users as &$user) {
            $user->subscribetype = $subscribers[$user->id]->type;
            $user->unsubscribekey = $subscribers[$user->id]->key;
        }

        $this->temp = (object) array('post' => $post);
    }

    public function get_emailmessage($user) {
        $post = $this->temp->post;
        $unsubscribeid = $post->{$user->subscribetype . 'id'};
        $unsubscribelink = get_config('wwwroot') . 'interaction/forum/unsubscribe.php?' . $user->subscribetype . '=' . $unsubscribeid . '&key=' . $user->unsubscribekey;
        return get_string_from_language($user->lang, 'forumposttemplate', 'interaction.forum',
            $post->forumtitle,
            $post->groupname,
            $post->textbody,
            get_config('wwwroot') . $this->url,
            $user->subscribetype,
            $unsubscribelink
        );
    }

    public function get_htmlmessage($user) {
        $post = $this->temp->post;
        $unsubscribeid = $post->{$user->subscribetype . 'id'};
        $unsubscribelink = get_config('wwwroot') . 'interaction/forum/unsubscribe.php?' . $user->subscribetype . '=' . $unsubscribeid . '&key=' . $user->unsubscribekey;
        return get_string_from_language($user->lang, 'forumposthtmltemplate', 'interaction.forum',
            hsc($post->forumtitle),
            hsc($post->groupname),
            $post->htmlbody,
            get_config('wwwroot') . $this->url,
            $unsubscribelink,
            $user->subscribetype
        );
    }

    public function get_plugintype(){
        return 'interaction';
    }

    public function get_pluginname(){
        return 'forum';
    }

    public function get_required_parameters() {
        return array('postid');
    }
}

class ActivityTypeInteractionForumReportPost extends ActivityTypePlugin {

    protected $postid;
    protected $message;
    protected $reporter;
    protected $ctime;
    protected $event;
    protected $temp;

    public function __construct($data, $cron = false) {
        parent::__construct($data, $cron);

        $post = get_record_sql('
            SELECT
                p.subject, p.body, p.poster, p.parent, ' . db_format_tsfield('p.ctime', 'ctime') . ',
                t.id AS topicid, fp.subject AS topicsubject, f.title AS forumtitle, g.id AS groupid, g.name AS groupname, f.id AS forumid
            FROM {interaction_forum_post} p
            INNER JOIN {interaction_forum_topic} t ON (t.id = p.topic AND t.deleted = 0)
            INNER JOIN {interaction_forum_post} fp ON (fp.parent IS NULL AND fp.topic = t.id)
            INNER JOIN {interaction_instance} f ON (t.forum = f.id AND f.deleted = 0)
            INNER JOIN {group} g ON (f.group = g.id AND g.deleted = 0)
            WHERE p.id = ? AND p.deleted = 0',
            array($this->postid)
        );

        // The post may have been deleted during the activity delay
        if (!$post) {
            $this->users = array();
            return;
        }

        // Set notification to site admins.
        $siteadmins = activity_get_users($this->get_id(), null, null, true);
        // Get forum moderators and admins.
        $forumadminsandmoderators = activity_get_users(
            $this->get_id(),
            array_merge(get_forum_moderators($post->forumid),
            group_get_admin_ids($post->groupid)));
        // Populate users to notify list and get rid of duplicates.
        foreach (array_merge($siteadmins, $forumadminsandmoderators) as $user) {
            if (!isset($this->users[$user->id])) {
                $this->users[$user->id] = $user;
            }
        }

        // Record who reported it.
        $this->fromuser = $this->reporter;

        $post->posttime = strftime(get_string('strftimedaydatetime'), $post->ctime);
        $post->textbody = trim(html2text($post->body));
        $post->htmlbody = clean_html($post->body);
        $this->url = 'interaction/forum/topic.php?id=' . $post->topicid . '&post=' . $this->postid . '&objection=1';

        $this->add_urltext(array(
            'key'     => 'Topic',
            'section' => 'interaction.forum'
        ));

        if ($this->event === REPORT_OBJECTIONABLE) {
            $this->overridemessagecontents = true;
            $this->strings->subject = (object) array(
                'key'     => 'objectionablecontentpost',
                'section' => 'interaction.forum',
                'args'    => array($post->topicsubject, display_default_name($this->reporter)),
            );
        }
        else if ($this->event === MAKE_NOT_OBJECTIONABLE) {
            $this->strings = (object) array(
                'subject' => (object) array(
                    'key' => 'postnotobjectionablesubject',
                    'section' => 'interaction.forum',
                    'args' => array($post->topicsubject, display_default_name($this->reporter)),
                ),
                'message' => (object) array(
                    'key' => 'postnotobjectionablebody',
                    'section' => 'interaction.forum',
                    'args' => array(display_default_name($this->reporter), display_default_name($post->poster)),
                ),
            );
        }
        else if ($this->event === DELETE_OBJECTIONABLE_POST) {
            $this->url = '';
            $this->strings = (object) array(
                'subject' => (object) array(
                    'key' => 'objectionablepostdeletedsubject',
                    'section' => 'interaction.forum',
                    'args' => array($post->topicsubject, display_default_name($this->reporter)),
                ),
                'message' => (object) array(
                    'key' => 'objectionablepostdeletedbody',
                    'section' => 'interaction.forum',
                    'args' => array(display_default_name($this->reporter), display_default_name($post->poster), $post->textbody),
                ),
            );
        }
        else if ($this->event === DELETE_OBJECTIONABLE_TOPIC) {
            $this->url = '';
            $this->strings = (object) array(
                'subject' => (object) array(
                    'key' => 'objectionabletopicdeletedsubject',
                    'section' => 'interaction.forum',
                    'args' => array($post->topicsubject, display_default_name($this->reporter)),
                ),
                'message' => (object) array(
                    'key' => 'objectionabletopicdeletedbody',
                    'section' => 'interaction.forum',
                    'args' => array(display_default_name($this->reporter), display_default_name($post->poster), $post->textbody),
                ),
            );

        }
        else {
            throw new SystemException();
        }

        $this->temp = (object) array('post' => $post);
    }

    public function get_emailmessage($user) {
        $post = $this->temp->post;
        $reporterurl = profile_url($this->reporter);
        $ctime = strftime(get_string_from_language($user->lang, 'strftimedaydatetime'), $this->ctime);
        return get_string_from_language(
            $user->lang, 'objectionablecontentposttext', 'interaction.forum',
            $post->topicsubject, display_default_name($this->reporter), $ctime,
            $this->message, $post->posttime, $post->textbody, get_config('wwwroot') . $this->url, $reporterurl
        );
    }

    public function get_htmlmessage($user) {
        $post = $this->temp->post;
        $reportername = hsc(display_default_name($this->reporter));
        $reporterurl = profile_url($this->reporter);
        $ctime = strftime(get_string_from_language($user->lang, 'strftimedaydatetime'), $this->ctime);
        return get_string_from_language(
            $user->lang, 'objectionablecontentposthtml', 'interaction.forum',
            hsc($post->topicsubject), $reportername, $ctime,
            $this->message, $post->posttime, $post->htmlbody, get_config('wwwroot') . $this->url, hsc($post->topicsubject),
            $reporterurl, $reportername
        );
    }

    public function get_plugintype(){
        return 'interaction';
    }

    public function get_pluginname(){
        return 'forum';
    }

    public function get_required_parameters() {
        return array('postid', 'message', 'reporter', 'ctime', 'event');
    }
}

// constants for forum membership types
define('INTERACTION_FORUM_ADMIN', 1);
define('INTERACTION_FORUM_MOD', 2);
define('INTERACTION_FORUM_MEMBER', 4);

/**
 * Can a user access a given forum?
 *
 * @param int $forumid id of forum
 * @param int $userid optional id of user, defaults to logged in user
 *
 * @returns constant access level or false
 */
function user_can_access_forum($forumid, $userid=null) {
    global $USER;
    $forumuser = $USER;
    if (!empty($userid)) {
        $forumuser = new User;
        $forumuser->find_by_id($userid);
    }
    if (!is_int($forumid)) {
        throw new InvalidArgumentException("non integer forum id given to user_can_access_forum: $forumid");
    }

    $membership = 0;

    // Allow site admins accessing the forum directly if it has objectionable content.
    $instance = new InteractionForumInstance($forumid);
    if ($instance->has_objectionable() && $forumuser->get('admin')) {
        return $membership | INTERACTION_FORUM_ADMIN | INTERACTION_FORUM_MOD;
    }

    $groupid = get_field('interaction_instance', '"group"', 'id', $forumid);
    $groupmembership = group_user_access((int)$groupid, $forumuser->get('id'));

    if (!$groupmembership) {
        return $membership;
    }
    $membership = $membership | INTERACTION_FORUM_MEMBER;
    if ($groupmembership == 'admin') {
        $membership = $membership | INTERACTION_FORUM_ADMIN | INTERACTION_FORUM_MOD;
    }
    if (record_exists('interaction_forum_moderator', 'forum', $forumid, 'user', $forumuser->get('id'))) {
        $membership = $membership | INTERACTION_FORUM_MOD;
    }
    return $membership;
}

/**
 * Get list of moderators for a given forum.
 *
 * @param int $forumid id of forum
 *
 * @returns array $moderators list of forum moderators.
 */
function get_forum_moderators($forumid) {
    $moderators = get_column_sql(
        'SELECT fm.user FROM {interaction_forum_moderator} fm
         JOIN {usr} u ON (fm.user = u.id AND u.deleted = 0)
         WHERE fm.forum = ?', array($forumid)
    );
    return (array) $moderators;
}

/**
 * Is a user allowed to edit a post
 *
 * @param boolean $moderator
 * @param int $poster the the id of the user who created the post
 * @param int $posttime the time the post was made
 * @param int $userid optional id of user, defaults to logged in user
 *
 * @returns boolean
 */
function user_can_edit_post($poster, $posttime, $userid=null, $verifydelay=true) {
    if (empty($userid)) {
        global $USER;
        $userid = $USER->get('id');
    }
    $permitted = true;
    if ($verifydelay) {
        $permitted = $posttime > (time() - get_config_plugin('interaction', 'forum', 'postdelay') * 60);
    }
    return $poster == $userid && $permitted;
}

/**
 * Generates a relative date containing yesterday/today when appropriate
 *
 * @param string $relative the format (for strftime) for a relative date (with %v where yesterday/today should be)
 * @param string $absolute the format (for strftime) for an absolute date
 * @param int $time1 the time to display
 * @param int $time2 optional the time $time1 is relative to, defaults to current time
 */
function relative_date($relative, $absolute, $time1, $time2=null) {
    if ($time2==null) {
        $time2 = time();
    }

    $date = getdate($time1);

    $yesterday = getdate(strtotime('-1 day', $time2));
    $tomorrow = getdate(strtotime('+1 day', $time2));
    $today = getdate($time2);

    if ($date['year'] == $yesterday['year'] && $date['yday'] == $yesterday['yday']) {
        return str_replace('%v', get_string('yesterday', 'interaction.forum'), strftime($relative, $time1));
    }
    else if ($date['year'] == $today['year'] && $date['yday'] == $today['yday']) {
        return str_replace('%v', get_string('today', 'interaction.forum'), strftime($relative, $time1));
    }
    return strftime(get_string('strftimedatetime'), $time1);

}

function subscribe_forum_validate(Pieform $form, $values) {
    if (!is_logged_in()) {
        throw new AccessDeniedException();
    }
}

function subscribe_forum_submit(Pieform $form, $values) {
    global $USER, $SESSION;
    if ($values['type'] == 'subscribe') {
        db_begin();
        insert_record(
            'interaction_forum_subscription_forum',
            (object)array(
                'forum' => $values['forum'],
                'user'  => $USER->get('id'),
                'key'   => PluginInteractionForum::generate_unsubscribe_key(),
            )
        );
        delete_records_sql(
            'DELETE FROM {interaction_forum_subscription_topic}
            WHERE topic IN (
                SELECT id
                FROM {interaction_forum_topic}
                WHERE forum = ?
                AND "user" = ?
            )',
            array($values['forum'], $USER->get('id'))
        );
        db_commit();
        $SESSION->add_ok_msg(get_string('forumsuccessfulsubscribe', 'interaction.forum'));
    }
    else {
        delete_records(
            'interaction_forum_subscription_forum',
            'forum', $values['forum'],
            'user', $USER->get('id')
        );
        $SESSION->add_ok_msg(get_string('forumsuccessfulunsubscribe', 'interaction.forum'));
    }
    if ($values['redirect'] == 'index') {
        redirect('/interaction/forum/index.php?group=' . $values['group']);
    }
    else {
        redirect('/interaction/forum/view.php?id=' . $values['forum'] . '&offset=' . $values['offset']);
    }
}

/*
 * Return the subject for the topic
 *
 * @param int $postid the ID of the post
 *
 * @return string the subject
 */

function get_ancestorpostsubject($postid, $isparent = false) {
    if ($isparent) {
        $record = get_record_sql(
           'SELECT p.subject
            FROM {interaction_forum_post} p
            WHERE p.id = ?', array($postid));
        if ($record && !empty($record->subject)) {
            return $record->subject;
        }
    }
    while ($ppost = get_record_sql(
           'SELECT p1.id, p1.subject
            FROM {interaction_forum_post} p1
            INNER JOIN {interaction_forum_post} p2 ON (p1.id = p2.parent)
            WHERE p2.id = ?', array($postid))) {
        if (!empty ($ppost->subject)) {
            return $ppost->subject;
        }
        $postid = $ppost->id;
    }
    return null;
}
