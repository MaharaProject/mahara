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
define('MENUITEM', 'configextensions');
define('SUBMENUITEM', 'pluginadmin');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('pluginadmin', 'admin'));
require('upgrade.php');

$plugins = array();

foreach (plugin_types()  as $plugin) {
    $plugins[$plugin] = array();
    $plugins[$plugin]['installed'] = array();
    $plugins[$plugin]['notinstalled'] = array();
    if ($installed = get_records_array($plugin . '_installed')) {
        foreach ($installed as $i) {
            $plugins[$plugin]['installed'][$i->name] = array();
            if ($plugin == 'artefact') {
                $plugins[$plugin]['installed'][$i->name]['types'] = array();
                safe_require('artefact',$i->name);
                if ($types = call_static_method(generate_class_name('artefact', $i->name), 'get_artefact_types')) {
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
        $plugins[$plugin]['notinstalled'][$dir] = array();
        require_once('artefact.php');
        $funname = $plugin . '_check_plugin_sanity';
        if (function_exists($funname)) {
            try {
                $funname($dir);
            }
            catch (InstallationException $e) {
                $plugins[$plugin]['notinstalled'][$dir]['notinstallable'] = $e->GetMessage();
            }
        }
    }
}

$loadingicon = theme_get_url('loading.gif');
$successicon = theme_get_url('success.gif');
$failureicon = theme_get_url('failure.gif');

$loadingstring = get_string('upgradeloading', 'admin');
$successstring = get_string('upgradesuccesstoversion', 'admin');
$failurestring = get_string('upgradefailure', 'admin');

$javascript = <<<JAVASCRIPT

function installplugin(name) {
    var d = loadJSONDoc('../upgrade.json.php', { 'name': name });

    $(name + '.message').innerHTML = '<img src="{$loadingicon}" alt="{$loadingstring}" />';

    d.addCallbacks(function (data) {
        if (data.success) {
            var message = '{$successstring}' + data.newversion;
            $(name + '.message').innerHTML = '<img src="{$successicon}" alt=":)" />  ' + message;
            // move the whole thing into the list of installed plugins 
            // new parent node
            var bits = name.split('\.');
            var newparent = $(bits[0] + '.installed');
            appendChildNodes(newparent, $(name));
        }
        if (data.error) {
            var message = '';
            if (data.errormessage) {
                message = data.errormessage;
            } 
            else {
                message = '{$failurestring}';
            }
            $(name).innerHTML = '<img src="{$failureicon}" alt=":(" /> ' + message;
        }
    },
                   function () {
                       message = '{$failurestring}';
                       $(name).innerHTML = message;
                   });
}
JAVASCRIPT;


$smarty = smarty();
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->assign('plugins', $plugins);
$smarty->assign('installlink', 'installplugin');
$smarty->display('admin/extensions/plugins.tpl');

?>
