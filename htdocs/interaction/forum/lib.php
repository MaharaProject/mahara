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
 * @subpackage interaction-forum
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

require_once('activity.php');

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

            $moderators = get_column_sql(
                'SELECT fm.user FROM {interaction_forum_moderator} fm
                JOIN {usr} u ON (fm.user = u.id AND u.deleted = 0)
                WHERE fm.forum = ?',
                array($instance->get('id'))
            );
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
            ORDER BY c.value',
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
                'collapsible' => true,
                'collapsed' => true,
                'legend' => get_string('settings'),
                'elements' => array(
                    'autosubscribe' => array(
                        'type'         => 'checkbox',
                        'title'        => get_string('autosubscribeusers', 'interaction.forum'),
                        'description'  => get_string('autosubscribeusersdescription', 'interaction.forum'),
                        'defaultvalue' => isset($autosubscribe) ? $autosubscribe : false,
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
                        'filter' => false,
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
                        'type'         => 'checkbox',
                        'title'        => get_string('closetopics', 'interaction.forum'),
                        'description'  => get_string('closetopicsdescription', 'interaction.forum'),
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

    public static function get_activity_types() {
        return array(
            (object)array(
                'name' => 'newpost',
                'admin' => 0,
                'delay' => 1
            )
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
                'weight' => 60,
            ),
        );
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
        $postdelay = get_config_plugin('interaction', 'forum', 'postdelay');
        if (!is_numeric($postdelay)) {
            $postdelay = 30;
        }

        return array(
            'elements' => array(
                'postdelay' => array(
                    'title'        => get_string('postdelay', 'interaction.forum'),
                    'description'  => get_string('postdelaydescription', 'interaction.forum'),
                    'type'         => 'text',
                    'rules'        => array('integer' => true, 'minvalue' => 0, 'maxvalue' => 10000000),
                    'defaultvalue' => (int) $postdelay,
                ),
            ),
            'renderer' => 'table'
        );
    }

    public static function save_config_options($values) {
        set_config_plugin('interaction', 'forum', 'postdelay', $values['postdelay']);
    }

    public static function get_active_topics($limit, $offset, $category) {
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

        $from = '
            FROM
                {interaction_forum_topic} t
                JOIN {interaction_instance} f ON t.forum = f.id
                JOIN {group} g ON f.group = g.id
                JOIN {group_member} gm ON (gm.group = g.id AND gm.member = ?)
                JOIN {interaction_forum_post} first ON (first.topic = t.id AND first.parent IS NULL)
                JOIN (' . $lastposts . '
                ) last ON last.topic = t.id';

        $values = array($USER->get('id'));

        $where = '
            WHERE g.deleted = 0 AND f.deleted = 0 AND t.deleted = 0';

        if (!empty($category)) {
            $where .= ' AND g.category = ?';
            $values[] = (int) $category;
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
                t.id, t.forum AS forumid, f.title AS forumname, g.id AS groupid, g.name AS groupname,
                first.subject AS topicname, first.poster AS firstpostby,
                last.id AS postid, last.poster, last.subject, last.body, last.ctime,
                COUNT(posts.id) AS postcount';

        $from .= '
                LEFT JOIN {interaction_forum_post} posts ON posts.topic = t.id';

        $sort = '
            GROUP BY
                t.id, t.forum, f.title, g.id, g.name,
                first.subject, first.poster,
                last.id, last.poster, last.subject, last.body, last.ctime
            ORDER BY last.ctime DESC';

        $result['data'] = get_records_sql_array($select . $from . $where . $sort, $values, $offset, $limit);

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
}

class InteractionForumInstance extends InteractionInstance {

    public static function get_plugin() {
        return 'forum';
    }

    public function interaction_remove_user($userid) {
        delete_records('interaction_forum_moderator', 'forum', $this->id, 'user', $userid);
        delete_records('interaction_forum_subscription_forum', 'forum', $this->id, 'user', $userid);
        delete_records_select('interaction_forum_subscription_topic',
            'user = ? AND topic IN (SELECT id FROM {interaction_forum_topic} WHERE forum = ?)',
            array($userid, $this->id)
        );
    }

}

class ActivityTypeInteractionForumNewPost extends ActivityTypePlugin {

    protected $postid;
    protected $temp;

    public function __construct($data) {
        parent::__construct($data);
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

        $subscribers = get_records_sql_assoc('
            SELECT "user" AS subscriber, \'topic\' AS type, "key" FROM {interaction_forum_subscription_topic} WHERE topic = ?
            UNION
            SELECT "user" AS subscriber, \'forum\' AS type, "key" FROM {interaction_forum_subscription_forum} WHERE forum = ?
            ORDER BY type',
            array($post->topicid, $post->forumid)
        );

        $this->users = $subscribers ? activity_get_users($this->get_id(), array_keys($subscribers)) : array();
        $this->fromuser = $post->poster;

        // When emailing forum posts, create Message-Id headers for threaded display by email clients
        $urlinfo = parse_url(get_config('wwwroot'));
        $hostname = $urlinfo['host'];
        $cleanforumname = str_replace('"', "'", strip_tags($post->forumtitle));
        $this->customheaders = array(
            'List-Id: "' . $cleanforumname . '" <forum' . $post->forumid . '@' . $hostname . '>',
            'List-Help: ' . get_config('wwwroot') . 'interaction/forum/view.php?id=' . $post->forumid,
            'Message-ID: <forumpost' . $this->postid . '@' . $hostname . '>',
        );
        if ($post->parent) {
            $this->customheaders[] = 'In-Reply-To: <forumpost' . $post->parent . '@' . $hostname . '>';
            $this->customheaders[] = 'References: <forumpost' . $post->parent . '@' . $hostname . '>';
        }

        $post->posttime = strftime(get_string('strftimedaydatetime'), $post->ctime);
        $this->message = strip_tags(str_shorten_html($post->body, 200, true)); // For internal notifications.

        $post->textbody = trim(html2text($post->body));
        $post->htmlbody = clean_html($post->body);
        $this->url = get_config('wwwroot') . 'interaction/forum/topic.php?id=' . $post->topicid . '#post' . $this->postid;

        $this->add_urltext(array(
            'key'     => 'Topic',
            'section' => 'interaction.forum'
        ));

        $this->strings->subject = (object) array(
            'key'     => $post->parent ? 'replyforumpostnotificationsubject' : 'newforumpostnotificationsubject',
            'section' => 'interaction.forum',
            'args'    => array($post->groupname, $post->forumtitle, $post->parent ? $post->topicsubject : $post->subject),
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
            $post->subject ? $post->subject : get_string_from_language($user->lang, 're', 'interaction.forum', $post->topicsubject),
            display_name($post->poster, $user),
            $post->posttime,
            $post->textbody,
            $this->url,
            $user->subscribetype,
            $unsubscribelink
        );
    }

    public function get_htmlmessage($user) {
        $post = $this->temp->post;
        $unsubscribeid = $post->{$user->subscribetype . 'id'};
        $unsubscribelink = get_config('wwwroot') . 'interaction/forum/unsubscribe.php?' . $user->subscribetype . '=' . $unsubscribeid . '&key=' . $user->unsubscribekey;
        return get_string_from_language($user->lang, 'forumposthtmltemplate', 'interaction.forum',
            $post->subject ? hsc($post->subject) : get_string_from_language($user->lang, 're', 'interaction.forum', hsc($post->topicsubject)),
            hsc(display_name($post->poster, $user)),
            $post->posttime,
            $post->htmlbody,
            $this->url,
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
    if (empty($userid)) {
        global $USER;
        $userid = $USER->get('id');
    }
    else if (!is_int($userid)) {
        throw new InvalidArgumentException("non integer user id given to user_can_access_forum: $userid");
    }
    if (!is_int($forumid)) {
        throw new InvalidArgumentException("non integer forum id given to user_can_access_forum: $forumid");
    }

    $membership = 0;

    $groupid = get_field('interaction_instance', '"group"', 'id', $forumid);
    $groupmembership = group_user_access((int)$groupid, (int)$userid);

    if (!$groupmembership) {
        return $membership;
    }
    $membership = $membership | INTERACTION_FORUM_MEMBER;
    if ($groupmembership == 'admin') {
        $membership = $membership | INTERACTION_FORUM_ADMIN | INTERACTION_FORUM_MOD;
    }
    if (record_exists('interaction_forum_moderator', 'forum', $forumid, 'user', $userid)) {
        $membership = $membership | INTERACTION_FORUM_MOD;
    }
    return $membership;
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
    return strftime($absolute, $time1);

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
