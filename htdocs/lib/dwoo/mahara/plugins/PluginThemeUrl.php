<?php

/**
 * Core {theme_url} function plugin
 *
 * @param $filename The name of the file to get a URL for
 * @param $plugin If the file is in a plugin, specify this as plugintype/pluginname (e.g. artefact/file)
 */
use Dwoo\Core;

function PluginThemeUrl(Core $core, $filename, $plugin = null) {
    global $THEME;

    return $THEME->get_url($filename, false, $plugin);
}

?>
