<?php

/**
 * Dwoo {profile_url} function plugin
 *
 * Type:     function<br>
 * Name:     profile_url<br>
 * Date:     June 22, 2006<br>
 * Purpose:  Fetch internationalized strings
 * @author   Catalyst IT Ltd
 * @version  1.0
 * @return Internationalized string
 */

use Dwoo\Core;

function PluginProfileUrl(Core $dwoo, $user, $full=true, $useid=false) {

    return profile_url($user, $full, $useid);
}