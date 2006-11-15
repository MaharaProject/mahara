<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage admin
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');

$plugins = array();

foreach (plugin_types()  as $plugin) {
    $plugins[$plugin] = array();
    $plugins[$plugin]['installed'] = array();
    $plugins[$plugin]['notinstalled'] = array();
    if ($installed = get_records($plugin . '_installed')) {
        foreach ($installed as $i) {
            $plugins[$plugin]['installed'][$i->name] = array();
            if ($plugin == 'artefact') {
                safe_require('artefact','internal');
                $types = call_static_method(generate_class_name('artefact', $i->name), 'get_artefact_types');
                $plugins[$plugin]['installed'][$i->name]['types'] = array();
                foreach ($types as $t) {
                    $classname = generate_artefact_class_name($t);
                    if ($collapseto = call_static_method($classname, 'collapse_config')) {
                        $plugins[$plugin]['installed'][$i->name]['types'][$collapseto] = true;
                    }
                    else {
                        $plugins[$plugin]['installed'][$i->name]['types'][$t] = 
                            call_static_method($classname, 'has_config');
                    }
                }
            } 
            else {
                $classname = generate_class_name($plugin, $i->name);
                safe_require($plugin, $i->name);
                if (call_static_method($classname, 'has_config')) {
                    $plugins[$plugin]['installed'][$i->name]['config'] = true;
                }
            }
        }
    }
    
    $dirhandle = opendir(get_config('docroot') . $plugin);
    while (false !== ($dir = readdir($dirhandle))) {
        if (strpos($dir, '.') === 0) {
            continue;
        }
        if (!is_dir(get_config('docroot') . $plugin . '/' . $dir)) {
            continue;
        }
        if (array_key_exists($dir, $plugins[$plugin]['installed'])) {
            continue;
        }
        $plugins[$plugin]['notinstalled'][] = $dir;
    }
}

$smarty = smarty();
$smarty->assign('plugins', $plugins);
$smarty->display('admin/plugins/index.tpl');

?>
