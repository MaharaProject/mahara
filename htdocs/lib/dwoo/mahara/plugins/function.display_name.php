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
function Dwoo_Plugin_display_name(Dwoo $dwoo, $user) {
    if (!$user) {
        return '';
    }

    return display_name($user);
}

?>
