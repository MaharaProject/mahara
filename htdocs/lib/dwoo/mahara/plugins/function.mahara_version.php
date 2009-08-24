<?php

/**
 * Dwoo {mahara_version} function plugin
 *
 * Type:     function<br>
 * Name:     mahara_version<br>
 * Date:     July 10, 2007<br>
 * Purpose:  Fetch the mahara version number if in the admin section
 * @author   Catalyst IT Ltd
 * @version  1.0
 * @return html to display in the footer.
 */
function Dwoo_Plugin_mahara_version(Dwoo $dwoo) {
    global $USER;
    if (!defined('ADMIN') || !$USER->get('admin')) {
        return '';
    }
    return '<div class="center">Mahara version ' . get_config('release') . ' (' . get_config('version') . ')</div>';
}

?>
