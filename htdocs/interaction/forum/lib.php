<?php

class PluginInteractionForum extends PluginInteraction {

    public static function instance_config_form($instance=null) {
        return array();
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


?>
