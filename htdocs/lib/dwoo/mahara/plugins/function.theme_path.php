<?php

/**
 * Dwoo {theme_path} function plugin
 *
 * Type:     function<br>
 * Name:     theme_path<br>
 * Date:     June 22, 2006<br>
 * Purpose:  Fetch the image according to the theme hierarchy
 * @author   Catalyst IT Ltd
 * @version  1.0
 * @return Internationalized string
 */
function Dwoo_Plugin_theme_path(Dwoo $dwoo, $location, $pluginlocation=null) {
    global $THEME;
    log_warn("The dwoo modifier theme_path is deprecated: please use theme_url");
    
    $plugintype = $pluginname = '';
    if ($pluginlocation) {
        list($plugintype, $pluginname) = explode('/', $pluginlocation);
    }

    return $THEME->get_url($location, false, $plugintype, $pluginname);
}

?>
