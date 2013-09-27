<?php
/**
 *
 * @package    mahara
 * @subpackage dwoo
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * Dwoo compiler for the Search plugin type. The only added functionality is the ability
 * to search for dwoo templates under a Search plugin by prepending the path with "search:"
 */
class Dwoo_Template_Mahara_Search extends Dwoo_Template_Mahara
{
    protected function resolveFileName(array $name, array $includePath) {
        global $THEME;

        $plugin_name = $name[0];
        $plugin_path = $name[1];

        $basedir = get_config('docroot') . 'search/' . $plugin_name . '/theme/';

        foreach ($THEME->inheritance as $theme) {
            $filename = $basedir . $theme . '/' . $plugin_path;
            if (is_readable($filename)) {
                array_unshift($includePath, $basedir . $theme . '/');
                return array($plugin_path, $includePath);
            }
        }

        throw new MaharaException('Search template could not be found : '.implode(':', $name));
    }
}