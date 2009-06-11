<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {theme_path} function plugin
 *
 * Type:     function<br>
 * Name:     str<br>
 * Date:     June 22, 2006<br>
 * Purpose:  Fetch the image according to the theme hierarchy
 * @author   Catalyst IT Ltd
 * @version  1.0
 * @param array
 * @param Smarty
 * @return Internationalized string
 */
function smarty_function_theme_path($params, &$smarty) {
    global $THEME;
    log_warn("The smarty modifier theme_path is deprecated: please use theme_url");
    
    $plugintype = $pluginname = '';
    if (isset($params['pluginlocation'])) {
        list($plugintype, $pluginname) = explode('/', $params['pluginlocation']);
    }

    return $THEME->get_url($params['location'], false, $plugintype, $pluginname);
}

?>
