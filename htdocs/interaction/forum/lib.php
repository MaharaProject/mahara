<?php

class PluginInteractionForum extends PluginInteraction {

    public static function instance_config_form($group, $instance=null) {
        if (isset($instance)) {
            $weight = get_field_sql(
                'SELECT c.value AS weight
                FROM {interaction_forum_instance_config} c
                WHERE c.field=\'weight\'
                AND forum = ?',
                array($instance->get('id'))
            );
            $moderators = get_column('interaction_forum_moderator', '"user"', 'forum', $instance->get('id'));
        }

        return array(
            'fieldset' => array(
                'type' => 'fieldset',
                'collapsible' => true,
                'collapsed' => true,
                'legend' => get_string('settings'),
                'elements' => array(
                    'weight' => array(
                        'type' => 'text',
                        'title' => get_string('weight', 'interaction.forum'),
                        'description' => get_string('weightdescription', 'interaction.forum'),
                        'defaultvalue' => isset($weight) ? $weight : 0,
                        'rules' => array(
                            'required' => true,
                            'integer' => true
                        )
                    ),
                    'moderator' => array(
                        'type' => 'userlist',
                        'title' => get_string('moderators', 'interaction.forum'),
                        'description' => get_string('moderatorsdescription', 'interaction.forum'),
                        'defaultvalue' => isset($moderators) ? $moderators : null,
                        'group' => $group->id,
                        'filter' => false,
                        'lefttitle' => get_string('potentialmoderators', 'interaction.forum'),
                        'righttitle' => get_string('currentmoderators', 'interaction.forum')
                    )
                )
            )
        );
    }

    public static function instance_config_save($instance, $values){
        db_begin();
        delete_records(
            'interaction_forum_moderator',
            'forum', $instance->get('id')
        );
        foreach ($values['moderator'] as $user) {
            insert_record(
                'interaction_forum_moderator',
                (object)array(
                    'user' => $user,
                    'forum' => $instance->get('id')
                )
            );
        }

        delete_records(
            'interaction_forum_instance_config',
            'forum', $instance->get('id'),
            'field', 'weight'
        );
        insert_record(
            'interaction_forum_instance_config',
            (object)array(
                'forum' => $instance->get('id'),
                'field' => 'weight',
                'value' => $values['weight']
            )
        );
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
        );
    }

    public static function interaction_forum_new_post() {
        $currenttime = time();
        $posts = get_records_sql_array(
            'SELECT s.subscriber, s.type, p.id
            FROM (
                SELECT st."user" AS subscriber, st.topic AS topic, \'topic\' AS type
                FROM {interaction_forum_subscription_topic} st
                UNION SELECT sf."user" AS subscriber, t.id AS topic, \'forum\' AS type
                FROM {interaction_forum_subscription_forum} sf
                INNER JOIN {interaction_forum_topic} t ON t.forum = sf.forum
            ) s
            INNER JOIN {interaction_forum_topic} t ON (t.deleted != 1 AND t.id = s.topic)
            INNER JOIN {interaction_forum_post} p ON (p.sent != 1 AND p.ctime < ? AND p.deleted != 1 AND p.topic = t.id)
            INNER JOIN {interaction_instance} f ON (f.id = t.forum AND f.deleted != 1)
            INNER JOIN {group_member} gm ON (gm.member = s.subscriber AND gm.group = f.group)
            ORDER BY type, p.id',
            array(db_format_timestamp($currenttime - 30 * 60))
        );
        if ($posts) {
            $count = count($posts);
            for ($i = 0; $i < $count; $i++) {
                $posts[$i]->users = array($posts[$i]->subscriber);
                $temp = $i;
                while (isset($posts[$i+1])
                    && $posts[$i+1]->id == $posts[$temp]->id
                    && $posts[$i+1]->type == $posts[$temp]->type) {
                    $i++;
                    $posts[$temp]->users[] = $posts[$i]->subscriber;
                    unset($posts[$i]);
                }
            }
            foreach ($posts as $post) {
                activity_occurred(
                    'newpost',
                    array(
                        'type' => $post->type,
                        'postid' => $post->id,
                        'users' => $post->users
                    ),
                    'interaction',
                    'forum'
                );
            }
            set_field_select('interaction_forum_post', 'sent', 1,
                'ctime < ? AND deleted = 0 AND sent = 0', array(db_format_timestamp($currenttime - 30 * 60)));
        }
    }
}

class InteractionForumInstance extends InteractionInstance {

    public static function get_plugin() {
        return 'forum';
    }

}


class ActivityTypeInteractionForumNewPost extends ActivityTypePlugin {

    protected $postid;
    protected $type; // forum or topic

    public function __construct($data) {
        parent::__construct($data);
        $this->users = get_records_sql_array(
            'SELECT id, username, preferredname, firstname, lastname, admin, staff
            FROM {usr} u
            WHERE id IN (' . implode(',', $this->users) . ')',
            array()
        );
        $post = get_record_sql(
            'SELECT p.subject, p.poster, t.id AS topicid, p2.subject AS topicsubject, f.title AS forumtitle
            FROM {interaction_forum_post} p
            INNER JOIN {interaction_forum_topic} t
            ON t.id = p.topic
            AND t.deleted != 1
            INNER JOIN {interaction_forum_post} p2
            ON p2.parent IS NULL
            AND p2.topic = t.id
            INNER JOIN {interaction_instance} f
            ON t.forum = f.id
            AND f.deleted != 1
            WHERE p.id = ?
            AND p.deleted != 1',
            array($this->postid)
        );
        $this->url = get_config('wwwroot') . 'interaction/forum/topic.php?id=' . $post->topicid;
        $this->subject = get_string('newforumpostin', 'interaction.forum', $post->forumtitle);
        foreach ($this->users as &$user) {
            $user->message = get_string('postedin', 'interaction.forum', display_name($post->poster, $user), $post->topicsubject);
        }
    }

    public function get_plugintype(){
        return 'interaction';
    }

    public function get_pluginname(){
        return 'forum';
    }

    public function get_required_parameters() {
        return array('postid', 'type');
    }
}

/**
 * Is a user a moderator of a given forum
 *
 * @param int $forumid id of forum
 * @param int $userid optional id of user, defaults to logged in user
 *
 * @returns boolean
 */
function is_forum_moderator($forumid, $userid=null) {
    if (empty($userid)) {
        global $USER;
        $userid = $USER->get('id');
    }
    else if (!is_int($userid)) {
        throw new InvalidArgumentException("non integer user id given to is_forum_moderator: $userid");
    }

    if (!is_int($forumid)) {
        throw new InvalidArgumentException("non integer forum id given to is_forum_moderator: $forumid");
    }
    return record_exists_sql(
        'SELECT fm.user
        FROM {interaction_forum_moderator} fm
        INNER JOIN {interaction_instance} f ON f.id = fm.forum
        INNER JOIN {group_member} gm ON (gm.group = f.group AND gm.member = fm.user)
        WHERE fm.user = ?
        AND fm.forum = ?',
        array($userid, $forumid)
    );
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
function user_can_edit_post($poster, $posttime, $userid=null) {
	if (empty($userid)) {
        global $USER;
        $userid = $USER->get('id');
    }
    return $poster == $userid && $posttime > (time() - 30 * 60);
}

/**
 * For a pieform with forum, redirect and type elements.
 * forum is the forum id
 * redirect is where to redirect to
 * type is unsubscribe or subscribe depending on the intended action
 */
function subscribe_forum_submit(Pieform $form, $values) {
    global $USER, $SESSION;
    if ($values['type'] == 'subscribe') {
        db_begin();
        insert_record(
            'interaction_forum_subscription_forum',
            (object)array(
                'forum' => $values['forum'],
                'user' => $USER->get('id')
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
    redirect($values['redirect']);
}

?>
