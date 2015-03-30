<?php
/**
 *
 * @package    mahara
 * @subpackage dwoo
 * @author     Gilles-Philippe Leblanc <gilles-philippe.leblanc@umontreal.ca>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * Dwoo {theme_image_url} function plugin
 *
 * @param $filename The name of the image file to get a URL for without the images folder
 * @param $plugin If the file is in a plugin, specify this as plugintype/pluginname (e.g. artefact/file)
 */
function Dwoo_Plugin_theme_image_url(Dwoo $dwoo, $filename, $plugin = null) {
    global $THEME;
    return $THEME->get_image_url($filename, $plugin);
}
