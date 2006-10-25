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
 * @subpackage core or plugintype/pluginname
 * @author     Your Name <you@example.org>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
 * Class to use for installation exceptions
 */
class InstallationException extends Exception {}


/**
 * This function checks core and plugins
 * for which need to be upgraded/installed
 * @returns array of objects
 */
function check_upgrades($name = null) {
 
    $pluginstocheck = plugin_types();

    $toupgrade = array();
    $installing = false;

    require('version.php');
    // check core first...
    if (empty($name) || $name == 'core') {
        try {
            $coreversion = get_config('version');
        } 
        catch (Exception $e) {
            $coreversion = 0;
        }
        if (empty($coreversion)) {
            $core = new StdClass;
            $core->install = true;
            $core->to = $config->version;
            $core->torelease = $config->release;
            $toupgrade['core'] = $core;
            $installing = true;
        } 
        else if ($config->version > $coreversion) {
            $core = new StdClass;
            $core->upgrade = true;
            $core->from = $coreversion;
            $core->fromrelease = get_config('release');
            $core->to = $config->version;
            $core->torelease = $config->release;
            $toupgrade['core'] = $core;
        }
    }

    // If we were just checking if the core needed to be upgraded, we can stop
    // here.
    if ($name == 'core') {
        return $toupgrade['core'];
    }

    $plugins = array();
    if (!empty($name)) {
        $plugins[] = explode('.', $name);
    }
    else {
        foreach ($pluginstocheck as $plugin) {
            $dirhandle = opendir(get_config('docroot') . $plugin);
            while (false !== ($dir = readdir($dirhandle))) {
                if (strpos($dir, '.') === 0) {
                    continue;
                }
                if (!empty($installing) && $dir != 'internal') {
                    continue;
                }
                if (!is_dir(get_config('docroot') . $plugin . '/' . $dir)) {
                    continue;
                }
                $plugins[] = array($plugin, $dir);
            }
        }
    }

    foreach ($plugins as $plugin) {
        $plugintype = $plugin[0];
        $pluginname = $plugin[1];
        $pluginpath = "$plugin[0]/$plugin[1]";
        $pluginkey  = "$plugin[0].$plugin[1]";

        
        // Don't try to get the plugin info if we are installing - it will
        // definitely fail
        $pluginversion = 0;
        if (!$installing) {
            if ($installed = get_record('installed_' . $plugintype, 'name', $pluginname)) {
                $pluginversion = $installed->version;
                $pluginrelease =  $installed->release;
            }
            
            require(get_config('docroot') . $pluginpath . '/version.php');
        }

        if (empty($pluginversion)) {
            $plugininfo = new StdClass;
            $plugininfo->install = true;
            $plugininfo->to = $config->version;
            $plugininfo->torelease = $config->release;
            $toupgrade[$pluginkey] = $plugininfo;
        }
        else if ($config->version > $pluginversion) {
            $plugininfo = new StdClass;
            $plugininfo->upgrade = true;
            $plugininfo->from = $pluginversion;
            $plugininfo->fromrelease = $pluginrelease;
            $plugininfo->to = $config->version;
            $plugininfo->torelease = $config->release;
            $toupgrade[$pluginkey] = $plugininfo;
        }
    }

    // if we've just asked for one, don't return an array...
    if (!empty($name) && count($toupgrade) == 1) {
        $upgrade = new StdClass;
        $upgrade->name = $name;
        foreach ((array)$toupgrade[$name] as $key => $value) {
            $upgrade->{$key} = $value;
        }
        log_dbg('thing to upgrade:');
        log_dbg($upgrade);
        return $upgrade;
    }
    log_dbg('stuff to upgrade:');
    log_dbg($toupgrade);
    return $toupgrade;
}

function upgrade_core($upgrade) {
    global $db;

    $location = get_config('libroot') . '/db/';
    $db->StartTrans();

    if (!empty($upgrade->install)) {
        $status = install_from_xmldb_file($location . 'install.xml'); 
    }
    else {
        require_once($location . 'upgrade.php');
        $status = xmldb_core_upgrade($upgrade->from);
    }
    if (!$status) {
        throw new DatalibException("Failed to upgrade core");
    }

    $status = set_config('version', $upgrade->to);
    $status = $status && set_config('release', $upgrade->torelease);
    
    if ($db->HasFailedTrans()) {
        $status = false;
    }
    $db->CompleteTrans();

    return $status;
}

function upgrade_plugin($upgrade) {
    global $db;

    $plugintype = '';
    $pluginname = '';

    list($plugintype, $pluginname) = explode('.', $upgrade->name);

    $location = get_config('dirroot') . $plugintype . '/' . $pluginname . '/db/';
    $db->StartTrans();

    if (!empty($upgrade->install)) {
        if (is_readable($location . 'install.xml')) {
            $status = install_from_xmldb_file($location . 'install.xml');
        }
        else {
            $status = true;
        }
    }
    else {
        if (is_readable($location .  'upgrade.php')) {
            require_once($location . 'upgrade.php');
            $function = 'xmldb_' . $plugintype . '_' . $pluginname . '_upgrade';
            $status = $function($upgrade->from);
        }
        else {
            $status = true;
        }
    }
    if (!$status || $db->HasFailedTrans()) {
        $db->CompleteTrans();
        throw new DatalibException("Failed to upgrade $upgrade->name");
    }

    $installed = new StdClass;
    $installed->name = $pluginname;
    $installed->version = $upgrade->to;
    $installed->release = $upgrade->torelease;
    $installtable = 'installed_' . $plugintype;

    if (!empty($upgrade->install)) {
        insert_record($installtable,$installed);
    } 
    else {
        update_record($installtable, $installed, 'name');
    }

    // postinst stuff...
    safe_require($plugintype, $pluginname, 'lib.php');
    $pcname = 'Plugin' . ucfirst($plugintype) . ucfirst($pluginname);

    if ($crons = call_static_method($pcname, 'get_cron')) {
        foreach ($crons as $cron) {
            $cron = (object)$cron;
            if (empty($cron->callfunction)) {
                $db->RollbackTrans();
                throw new InstallationException("cron for $pcname didn't supply function name");
            }
            if (!is_callable(array($pcname, $cron->callfunction))) {
                $db->RollbackTrans();
                throw new InstallationException("cron $cron->callfunction for $pcname supplied but wasn't callable");
            }
            $new = false;
            if (!empty($upgrade->install)) {
                $new = true;
            }
            else if (!record_exists('cron_' . $plugintype, 'plugin', $pluginname, 'function', $cron->callfunction)) {
                $new = true;
            }
            $cron->plugin = $pluginname;
            if (!empty($new)) {
                insert_record('cron_' . $plugintype, $cron);
            }
            else {
                update_record('cron_' . $plugintype, $cron, array('plugin', 'name'));
            }
        }
    }
    
    if ($events = call_static_method($pcname, 'get_event_subscriptions')) {
        foreach ($events as $event) {
            $event = (object)$event;

            if (!record_exists('event', 'name', $event->event)) {
                $db->RollbackTrans();
                throw new InstallationException("event $event->event for $pcname doesn't exist!");
            }
            if (empty($event->callfunction)) {
                $db->RollbackTrans();
                throw new InstallationException("event $event->event for $pcname didn't supply function name");
            }
            if (!is_callable(array($pcname, $event->callfunction))) {
                $db->RollbackTrans();
                throw new InstallationException("event $event->event with function $event->callfunction for $pcname supplied but wasn't callable");
            }
            $exists = false;
            if (empty($upgrade->install)) {
                $exists = record_exists('event_subscription_' . $plugintype, 'plugin' , $pluginname, 'event', $event->event());
            }
            $event->plugin = $pluginname;
            if (empty($exists)) {
                insert_record('event_subscription_' . $plugintype, $event);
            }
            else {
                update_record('event_subscription_' . $plugintype, $event, array('id', $exists->id));
            }
        }
    }

    call_static_method($pcname, 'postinst');
    
    if ($db->HasFailedTrans()) {
        $status = false;
    }
    $db->CompleteTrans();
    
    return $status;
}


?>
