<?php

/**
 * Core {profile_icon_url} function plugin
 *
 * Type:     function<br>
 * Date:     June 22, 2006<br>
 * Purpose:  Display a user's profile icon according to mahara rules
 * @author   Catalyst IT Limited
 * @version  1.0
 */

use Dwoo\Core;

function PluginProfileIconUrl(Core $core, $user, $maxwidth, $maxheight) {
    return profile_icon_url($user, $maxwidth, $maxheight);
}
