<?php

/**
 * Dwoo {theme_url} function plugin
 *
 * @param $filename The name of the file to get a URL for
 * @param $plugin If the file is in a plugin, specify this as plugintype/pluginname (e.g. artefact/file)
 */
function Dwoo_Plugin_theme_url(Dwoo $dwoo, $filename, $plugin = null) {
    global $THEME;

    $plugintype = $pluginname = '';
    if ($plugin) {
        list($plugintype, $pluginname) = explode('/', $plugin);
    }

    return $THEME->get_url($filename, false, $plugintype, $pluginname);
}

?>
