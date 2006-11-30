<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {image_path} function plugin
 *
 * Type:     function<br>
 * Name:     str<br>
 * Date:     June 22, 2006<br>
 * Purpose:  Fetch the image according to the theme hierarchy
 * @author   Penny <penny@catalyst.net.nz>
 * @version  1.0
 * @param array
 * @param Smarty
 * @return Internationalized string
 */
function smarty_function_image_path($params, &$smarty) {
    static $dictionary;
    
    if (!isset($theme['pluginlocation'])) {
        $theme['pluginlocation'] = '';
    }

    return theme_get_image_path($params['imagelocation'], $params['pluginlocation']);
}

?>
