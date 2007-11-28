<?php

class PluginInteractionForum extends PluginInteraction {

    public static function instance_config_form($group, $instance=null) {
        if (isset($instance)) {
            $weight = get_record_sql(
                'SELECT c.value as weight
                FROM {interaction_forum_instance_config} c
                WHERE c.field=\'weight\'
                AND forum = ?',
                array($instance->get('id'))
            )->weight;
            $moderators = get_column('interaction_forum_moderator', '"user"', 'forum', $instance->get('id'));
        }

        return array(
            'weight' => array(
                'type' => 'text',
                'title' => get_string('weight', 'interaction.forum'),
                'defaultvalue' => isset($weight) ? $weight : 0,
                'rules' => array(
                    'required' => true,
                    'integer' => true
                )
            ),
            'moderator' => array(
                'type' => 'userlist',
                'title' => get_string('moderators', 'interaction.forum'),
                'defaultvalue' => isset($moderators) ? $moderators : null,
                'group' => $group->id,
                'filter' => false,
                'lefttitle' => get_string('potentialmoderators', 'interaction.forum'),
                'righttitle' => get_string('currentmoderators', 'interaction.forum')
            ),
            'submit' => array(
                'type' => 'submit',
                'value' => get_string('submit')
            )
        );
    }

    public static function instance_config_save($instance, $values){
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
    }

    public static function get_activity_types() {
        return array(
            (object)array(
                'name' => 'newpost',
                'admin' => 0,
                'delay' => 1,
            )
        );
    }
}

class InteractionForumInstance extends InteractionInstance {

    public static function get_plugin() {
        return 'forum';
    }

}

class ActivityTypeInteractionForumNewPost extends ActivityType {

    public function get_required_parameters() {
        return array();
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
        'SELECT "user"
        FROM {interaction_forum_moderator}
        WHERE "user" = ?
        AND forum = ?',
        array($userid, $forumid)
    );
}


?>
