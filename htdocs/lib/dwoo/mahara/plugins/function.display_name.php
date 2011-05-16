<?php

/**
 * Dwoo {display_name} function plugin
 *
 * Type:     function<br>
 * Date:     June 22, 2006<br>
 * Purpose:  Display a user's name according to mahara rules
 * @author   Catalyst IT Ltd
 * @version  1.0
 */
function Dwoo_Plugin_display_name(Dwoo $dwoo, $user, $userto=null, $nameonly=false, $realname=false) {
    if (!$user) {
        return '';
    }

    return hsc(display_name($user, $userto, $nameonly, $realname));
}

?>
