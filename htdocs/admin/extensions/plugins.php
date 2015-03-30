<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
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
                if (!safe_require_plugin($plugin, $key)) {
                    continue;
                }
                $plugins[$plugin]['installed'][$key] = array(
                    'active' => $i->active,
                    'disableable' => call_static_method(generate_class_name($plugin, $key), 'can_be_disabled'),
                );
                if ($plugins[$plugin]['installed'][$key]['disableable'] || !$i->active) {
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
                    $classname = generate_class_name($plugin, $dir);
                    $classname::sanity_check();
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
$loadingicon = $THEME->get_image_url('loading');
$successicon = $THEME->get_image_url('success');
$failureicon = $THEME->get_image_url('failure');

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
            var oldparent = $(name).parentNode;
            insertSiblingNodesBefore(newparent, $(name));
            // If there are no more plugins left for this type to be installed
            if (oldparent.children.length == 0) {
                oldparent.parentNode.style.display = 'none';
            }
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
