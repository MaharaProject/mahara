<?php

/**
 * Dwoo {display_default_name} function plugin
 *
 * Type:     function<br>
 * Date:     2012-06-11<br>
 * Purpose:  Escape output of display_default_name for use in templates
 * @version  1.0
 */

use Dwoo\Core;

function PluginDisplayDefaultName(Core $dwoo, $user) {
    if (!$user) {
        return '';
    }

    return hsc(display_default_name($user));
}
