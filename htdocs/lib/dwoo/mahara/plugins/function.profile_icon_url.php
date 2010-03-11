<?php

/**
 * Dwoo {profile_icon_url} function plugin
 *
 * Type:     function<br>
 * Date:     June 22, 2006<br>
 * Purpose:  Display a user's profile icon according to mahara rules
 * @author   Catalyst IT Ltd
 * @version  1.0
 */
function Dwoo_Plugin_profile_icon_url(Dwoo $dwoo, $user, $maxwidth, $maxheight) {
    return profile_icon_url($user, $maxwidth, $maxheight);
}

?>
