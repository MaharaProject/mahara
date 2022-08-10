<?php
/**
 *
 * @package    mahara
 * @subpackage dwoo
 * @author     Gilles-Philippe Leblanc <gilles-philippe.leblanc@umontreal.ca>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * Core {theme_image_url} function plugin
 *
 * @param $filename The name of the image file to get a URL for without the images folder
 * @param $plugin If the file is in a plugin, specify this as plugintype/pluginname (e.g. artefact/file)
 */
use Dwoo\Core;

function PluginThemeImageUrl(Core $core, $filename, $plugin = null) {
    global $THEME;
    return $THEME->get_image_url($filename, $plugin);
}
