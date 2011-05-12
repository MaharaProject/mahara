<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
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
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'configextensions/pluginadmin');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('pluginadmin', 'admin'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'plugins');
require('upgrade.php');

// @TODO when artefact plugins get installed, move the not installed blocktypes
// that get installed into the list of installed blocktype plugins

$plugins = array();
$plugins['blocktype'] = array();

foreach (plugin_types()  as $plugin) {
    // this has to happen first because of broken artefact/blocktype ordering
    $plugins[$plugin] = array();
    $plugins[$plugin]['installed'] = array();
    $plugins[$plugin]['notinstalled'] = array();
}
foreach (array_keys($plugins) as $plugin) {
    if (table_exists(new XMLDBTable($plugin . '_installed'))) {
        if ($installed = plugins_installed($plugin, true)) {
            foreach ($installed as $i) {
                $key = $i->name;
                if ($plugin == 'blocktype') {
                    $key = blocktype_single_to_namespaced($i->name, $i->artefactplugin);
                }
                try {
                    safe_require($plugin, $key);
                }
                catch (SystemException $e) {
                    $message = get_string('missingplugin', 'admin', hsc("$plugin:$key")) . ':<br>' . $e->getMessage();
                    die_info($message);
                }
                $plugins[$plugin]['installed'][$key] = array(
                    'active' => $i->active,
                    'disableable' => call_static_method(generate_class_name($plugin, $key), 'can_be_disabled'),
                );
                if ($plugins[$plugin]['installed'][$key]['disableable']) {
                    $plugins[$plugin]['installed'][$key]['activateform'] = activate_plugin_form($plugin, $i);
                }
                if ($plugin == 'artefact') {
                    $plugins[$plugin]['installed'][$key]['types'] = array();
                    safe_require('artefact', $key);
                    if ($types = call_static_method(generate_class_name('artefact', $i->name), 'get_artefact_types')) {
                        foreach ($types as $t) {
                            $classname = generate_artefact_class_name($t);
                            if ($collapseto = call_static_method($classname, 'collapse_config')) {
                                $plugins[$plugin]['installed'][$key]['types'][$collapseto] = true;
                            }
                            else {
                                $plugins[$plugin]['installed'][$key]['types'][$t] = 
                                    call_static_method($classname, 'has_config');
                            }
                        }
                    }
                } 
                else {
                    $classname = generate_class_name($plugin, $i->name);
                    safe_require($plugin, $key);
                    if (call_static_method($classname, 'has_config')) {
                        $plugins[$plugin]['installed'][$key]['config'] = true;
                    }
                }
            }
        }
    
        $dirhandle = opendir(get_config('docroot') . $plugin);
        while (false !== ($dir = readdir($dirhandle))) {
            $installed = false; // reinitialise
            if (strpos($dir, '.') === 0) {
                continue;
            }
            if (!is_dir(get_config('docroot') . $plugin . '/' . $dir)) {
                continue;
            }
            if (array_key_exists($dir, $plugins[$plugin]['installed'])) {
                $installed = true;
            }
            // if we're already installed keep going
            // if we're an artefact plugin, we have to check for blocktypes.
            if ($plugin != 'artefact' && !empty($installed)) {
                continue;
            }
            if (empty($installed)) {
                $plugins[$plugin]['notinstalled'][$dir] = array();
                try {
                    validate_plugin($plugin, $dir);
                }
                catch (InstallationException $e) {
                    $plugins[$plugin]['notinstalled'][$dir]['notinstallable'] = $e->GetMessage();
                }
            }
            if ($plugin == 'artefact' && table_exists(new XMLDBTable('blocktype_installed'))) { // go check it for blocks as well
                $btlocation = get_config('docroot') . $plugin . '/' . $dir . '/blocktype';
                if (!is_dir($btlocation)) {
                    continue;
                }

                $btdirhandle = opendir($btlocation);
                while (false !== ($btdir = readdir($btdirhandle))) {
                    if (strpos($btdir, '.') === 0) {
                        continue;
                    }
                    if (!is_dir(get_config('docroot') . $plugin . '/' . $dir . '/blocktype/' . $btdir)) {
                        continue;
                    }
                    if (!array_key_exists($dir . '/' . $btdir, $plugins['blocktype']['installed'])) {
                        try {
                            if (!array_key_exists($dir, $plugins['artefact']['installed'])) {
                                throw new InstallationException(get_string('blocktypeprovidedbyartefactnotinstallable', 'error', $dir));
                            }
                            validate_plugin('blocktype', $dir . '/' . $btdir, 
                                get_config('docroot') . 'artefact/' . $dir . '/blocktype/' . $btdir);
                            $plugins['blocktype']['notinstalled'][$dir . '/' . $btdir] = array();
                        }
                        catch (InstallationException $_e) {
                            $plugins['blocktype']['notinstalled'][$dir . '/' . $btdir]['notinstallable'] = $_e->getMessage();
                        }
                    }
                }
            }
        }
    }
}

global $THEME;
$loadingicon = $THEME->get_url('images/loading.gif');
$successicon = $THEME->get_url('images/success.gif');
$failureicon = $THEME->get_url('images/failure.gif');

$loadingstring = json_encode(get_string('upgradeloading', 'admin'));
$successstring = json_encode(get_string('upgradesuccesstoversion', 'admin'));
$failurestring = json_encode(get_string('upgradefailure', 'admin'));

$javascript = <<<JAVASCRIPT

function installplugin(name) {
    $(name + '.message').innerHTML = '<img src="{$loadingicon}" alt=' + {$loadingstring} + '" />';

    sendjsonrequest('../upgrade.json.php', { 'name': name }, 'GET', function (data) {
        if (!data.error) {
            var message = {$successstring} + data.newversion;
            $(name + '.message').innerHTML = '<img src="{$successicon}" alt=":)" />  ' + message;
            $(name + '.install').innerHTML = '';
            // move the whole thing into the list of installed plugins 
            // new parent node
            var bits = name.split('\.');
            var newparent = $(bits[0] + '.installed');
            appendChildNodes(newparent, $(name));
        }
        else {
            var message = '';
            if (data.errormessage) {
                message = data.errormessage;
            } 
            else {
                message = {$failurestring};
            }
            $(name).innerHTML = '<img src="{$failureicon}" alt=":(" /> ' + message;
        }
    },
    function () {
        message = {$failurestring};
        $(name).innerHTML = message;
    },
    true);
}
JAVASCRIPT;


$smarty = smarty();
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->assign('plugins', $plugins);
$smarty->assign('installlink', 'installplugin');
$smarty->assign('PAGEHEADING', TITLE);
$smarty->display('admin/extensions/plugins.tpl');
