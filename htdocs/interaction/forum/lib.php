<?php

class PluginInteractionForum extends PluginInteraction {

    public static function instance_config_form($instance=null) {
        return array();
    }
}

class InteractionForumInstance extends InteractionInstance {

    public static function get_plugin() {
        return 'forum';
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
