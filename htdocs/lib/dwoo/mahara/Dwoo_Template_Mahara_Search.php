<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2013 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage dwoo
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2013 Catalyst IT Ltd http://catalyst.net.nz
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