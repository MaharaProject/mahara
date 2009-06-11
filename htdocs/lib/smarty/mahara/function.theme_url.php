<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {theme_url} function plugin
 *
 * Parameters that can be used with this function:
 *  - filename: The name of the file to get a URL for
 *  - plugin: If the file is in a plugin, specify this as plugintype/pluginname (e.g. artefact/file)
 */
function smarty_function_theme_url($params, $smarty) {
    global $THEME;
    
    $plugintype = $pluginname = '';
    if (isset($params['plugin'])) {
        list($plugintype, $pluginname) = explode('/', $params['plugin']);
    }

    return $THEME->get_url($params['filename'], false, $plugintype, $pluginname);
}

?>
