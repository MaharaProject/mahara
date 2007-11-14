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


?>
