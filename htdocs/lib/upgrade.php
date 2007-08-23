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
 * @subpackage core
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
 * Class to use for installation exceptions
 */
class InstallationException extends SystemException {}


/**
 * This function checks core and plugins for which need to be upgraded/installed
 *
 * @param string $name The name of the plugin to check. If no name is specified,
 *                     all plugins are checked.
 * @return array of objects
 */
function check_upgrades($name=null) {

    $pluginstocheck = plugin_types();

    $toupgrade = array();
    $installing = false;
    $disablelogin = false;

    require('version.php');
    if (isset($config->disablelogin) && !empty($config->disablelogin)) {
        $disablelogin = true;
    }
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
            $corerelease = get_config('release');
            if (isset($config->minupgradefrom) && isset($config->minupgraderelease) 
                && $coreversion < $config->minupgradefrom) {
                throw new ConfigSanityException("Must upgrade to $config->minupgradefrom "
                                          . "($config->minupgraderelease) first "
                                          . " (you have $coreversion ($corerelease)");
            }
            $core = new StdClass;
            $core->upgrade = true;
            $core->from = $coreversion;
            $core->fromrelease = $corerelease;
            $core->to = $config->version;
            $core->torelease = $config->release;
            $toupgrade['core'] = $core;
        }
    }

    // If we were just checking if the core needed to be upgraded, we can stop here
    if ($name == 'core') {
        $toupgrade['core']->disablelogin = $disablelogin;
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
                if (!is_dir(get_config('docroot') . $plugin . '/' . $dir)) {
                    continue;
                }
                require_once('artefact.php');
                $funname = $plugin . '_check_plugin_sanity';
                if (function_exists($funname)) {
                    try {
                        $funname($dir);
                    }
                    catch (InstallationException $e) {
                        log_warn("Plugin $plugin $dir is not installable: " . $e->GetMessage());
                        continue;
                    }
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
            if ($installed = get_record($plugintype . '_installed', 'name', $pluginname)) {
                $pluginversion = $installed->version;
                $pluginrelease =  $installed->release;
            }
        }

        $config = new StdClass;
        require(get_config('docroot') . $pluginpath . '/version.php');
        if (isset($config->disablelogin) && !empty($config->disablelogin)) {
            $disablelogin = true;
        }

        if (empty($pluginversion)) {
            if (empty($installing) && $pluginkey != $name) {
                continue;
            }
            $plugininfo = new StdClass;
            $plugininfo->install = true;
            $plugininfo->to = $config->version;
            $plugininfo->torelease = $config->release;
            if (property_exists($config, 'requires_config')) {
                $plugininfo->requires_config = $config->requires_config;
            }
            if (property_exists($config, 'requires_parent')) {
                $plugininfo->requires_parent = $config->requires_parent;
            }
            $toupgrade[$pluginkey] = $plugininfo;
        }
        else if ($config->version > $pluginversion) {
            if (isset($config->minupgradefrom) && isset($config->minupgraderelease)
                && $pluginversion < $config->minupgradefrom) {
                throw new ConfigSanityException("Must upgrade to $config->minupgradefrom "
                                          . " ($config->minupgraderelease) first "
                                          . " (you have $pluginversion ($pluginrelease))");
            }
            $plugininfo = new StdClass;
            $plugininfo->upgrade = true;
            $plugininfo->from = $pluginversion;
            $plugininfo->fromrelease = $pluginrelease;
            $plugininfo->to = $config->version;
            $plugininfo->torelease = $config->release;
            if (property_exists($config, 'requires_config')) {
                $plugininfo->requires_config = $config->requires_config;
            }
            if (property_exists($config, 'requires_parent')) {
                $plugininfo->requires_parent = $config->requires_parent;
            }
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
        $upgrade->disablelogin = $disablelogin;
        return $upgrade;
    }
    $toupgrade['disablelogin'] = $disablelogin;
    if (count($toupgrade) == 1) {
        $toupgrade = array();
    }
    return $toupgrade;
}

/**
 * Upgrades the core system to given upgrade version.
 *
 * @param object $upgrade   The version to upgrade to
 * @return bool             Whether the upgrade succeeded or not
 * @throws SQLException     If the upgrade failed due to a database error
 */
function upgrade_core($upgrade) {
    global $db;

    $location = get_config('libroot') . 'db/';

    db_begin();

    if (!empty($upgrade->install)) {
        if (!install_from_xmldb_file($location . 'install.xml')) {
            throw new SQLException("Failed to upgrade core (check logs for xmldb errors)");
        }
    }
    else {
        require_once($location . 'upgrade.php');
        xmldb_core_upgrade($upgrade->from);
    }

    set_config('version', $upgrade->to);
    set_config('release', $upgrade->torelease);
    
    if (!empty($upgrade->install)) {
        core_postinst();
    }

    db_commit();
    return true;
}

/**
 * Upgrades the plugin to a new version
 *
 * @param object $upgrade   Information about the plugin to upgrade
 * @return bool             Whether the upgrade succeeded or not
 * @throws SQLException     If the upgrade failed due to a database error
 */
function upgrade_plugin($upgrade) {
    global $db;

    $plugintype = '';
    $pluginname = '';

    list($plugintype, $pluginname) = explode('.', $upgrade->name);

    $location = get_config('docroot') . $plugintype . '/' . $pluginname . '/db/';
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
        throw new SQLException("Failed to upgrade $upgrade->name (check logs for xmldb errors)");
    }

    $installed = new StdClass;
    $installed->name = $pluginname;
    $installed->version = $upgrade->to;
    $installed->release = $upgrade->torelease;
    if (property_exists($upgrade, 'requires_config')) {
        $installed->requires_config = $upgrade->requires_config;
    }
    if (property_exists($upgrade, 'requires_parent')) {
        $installed->requires_parent = $upgrade->requires_parent;
    }
    $installtable = $plugintype . '_installed';

    if (!empty($upgrade->install)) {
        insert_record($installtable,$installed);
    } 
    else {
        update_record($installtable, $installed, 'name');
    }

    // postinst stuff...
    safe_require($plugintype, $pluginname);
    $pcname = generate_class_name($plugintype, $pluginname);

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
            $table = $plugintype . '_cron';
            if (!empty($upgrade->install)) {
                $new = true;
            }
            else if (!record_exists($table, 'plugin', $pluginname, 'callfunction', $cron->callfunction)) {
                $new = true;
            }
            $cron->plugin = $pluginname;
            if (!empty($new)) {
                insert_record($table, $cron);
            }
            else {
                update_record($table, $cron, array('plugin', 'callfunction'));
            }
        }
    }
    
    if ($events = call_static_method($pcname, 'get_event_subscriptions')) {
        foreach ($events as $event) {
            $event = (object)$event;

            if (!record_exists('event_type', 'name', $event->event)) {
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
            $table = $plugintype . '_event_subscription';
            if (empty($upgrade->install)) {
                $exists = get_record($table, 'plugin' , $pluginname, 'event', $event->event);
            }
            $event->plugin = $pluginname;
            if (empty($exists)) {
                insert_record($table, $event);
            }
            else {
                update_record($table, $event, array('id' => $exists->id));
            }
        }
    }

     // install artefact types
    if ($plugintype == 'artefact') {
        if (!is_callable(array($pcname, 'get_artefact_types'))) {
            throw new InstallationException("Artefact plugin $pcname must implement get_artefact_types and doesn't");
        }
        $types = call_static_method($pcname, 'get_artefact_types');
        $ph = array();
        if (is_array($types)) {
            foreach ($types as $type) {
                $ph[] = '?';
                if (!record_exists('artefact_installed_type', 'plugin', $pluginname, 'name', $type)) {
                    $t = new StdClass;
                    $t->name = $type;
                    $t->plugin = $pluginname;
                    insert_record('artefact_installed_type',$t);
                }
            }
            $select = '(plugin = ? AND name NOT IN (' . implode(',', $ph) . '))';
            delete_records_select('artefact_installed_type', $select,
                                  array_merge(array($pluginname),$types));
        }

        //@todo install blocks here too.
    }
    
    $prevversion = (empty($upgrade->install)) ? $upgrade->from : 0;
    call_static_method($pcname, 'postinst', $prevversion);
    
    if ($db->HasFailedTrans()) {
        $status = false;
    }
    $db->CompleteTrans();
    
    return $status;

}

function core_postinst() {
    $status = true;
    $pages = site_content_pages();
    $now = db_format_timestamp(time());
    foreach ($pages as $name) {
        $page = new stdClass();
        $page->name = $name;
        $page->ctime = $now;
        $page->mtime = $now;
        $page->content = get_string($page->name . 'defaultcontent', 'install');
        if (!insert_record('site_content',$page)) {
            $status = false;
        }
    }

    // Attempt to create session directories
    $sessionpath = get_config('dataroot') . 'sessions';
    if (check_dir_exists($sessionpath)) {
        // Create three levels of directories, named 0-9, a-f
        $characters = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9', 'a', 'b', 'c', 'd', 'e', 'f');
        foreach ($characters as $c1) {
            if (check_dir_exists("$sessionpath/$c1")) {
                foreach ($characters as $c2) {
                    if (check_dir_exists("$sessionpath/$c1/$c2")) {
                        foreach ($characters as $c3) {
                            if (!check_dir_exists("$sessionpath/$c1/$c2/$c3")) {
                                $status = false;
                                break(3);
                            }
                        }
                    }
                    else {
                        $status = false;
                        break(2);
                    }
                }
            }
            else {
                $status = false;
                break;
            }
        }
    }
    else {
        $status = false;
    }

    // Set default search plugin
    set_config('searchplugin', 'internal');

    set_config('lang', 'en.utf8');

    return $status;
}


function core_install_defaults() {
    // Install the default institution
    db_begin();
    $institution = new StdClass;
    $institution->name = 'mahara';
    $institution->displayname = 'No Institution';
    $institution->authplugin  = 'internal';
    insert_record('institution', $institution);

    $auth_instance = new StdClass;
    $auth_instance->instancename  = 'internal';
    $auth_instance->priority='1';
    $auth_instance->institution   = 'mahara';
    $auth_instance->authname      = 'internal';
    $auth_instance->id = insert_record('auth_instance', $auth_instance, 'id', true);

    // Insert the root user
    $user = new StdClass;
    $user->id = 0;
    $user->username = 'root';
    $user->password = '*';
    $user->salt = '*';
    $user->institution = 'mahara';
    $user->firstname = 'System';
    $user->lastname = 'User';
    $user->email = 'root@example.org';
    $user->quota = get_config_plugin('artefact', 'file', 'defaultquota');
    $newid = insert_record('usr', $user);

    if ($newid > 0 && get_config('dbtype') == 'mysql') { // gratuitous mysql workaround
        set_field('usr', 'id', 0, 'id', $newid);
    }

    // Insert the admin user
    $user = new StdClass;
    $user->username = 'admin';
    $user->password = 'mahara';
    $user->institution = 'mahara';
    $user->authinstance = $auth_instance->id;
    $user->passwordchange = 1;
    $user->admin = 1;
    $user->firstname = 'Admin';
    $user->lastname = 'User';
    $user->email = 'admin@example.org';
    $user->quota = get_config_plugin('artefact', 'file', 'defaultquota');
    $user->id = insert_record('usr', $user, 'id', true);
    set_profile_field($user->id, 'email', $user->email);
    set_profile_field($user->id, 'firstname', $user->firstname);
    set_profile_field($user->id, 'lastname', $user->lastname);
    
    set_config('installed', true);
    db_commit();
}

function local_xmldb_contents_sub(&$contents) {
    $searchstring = '<!-- PLUGIN_TYPE_SUBSTITUTION -->';
    if (strstr($contents, $searchstring) === 0) {
        return;
    }
    // ok, we're in the main file and we need to install all the plugin tables
    // get the basic skeleton structure
    $plugintables = file_get_contents(get_config('docroot') . 'lib/db/plugintables.xml');
    $tosub = '';
    foreach (plugin_types() as $plugin) {
        // any that want their own stuff can put it in docroot/plugintype/lib/db/plugintables.xml - like auth is a bit special
        $specialcase = get_config('docroot') . $plugin . '/plugintables.xml';
        if (is_readable($specialcase)) {
            $tosub .= file_get_contents($specialcase) . "\n";
        } 
        else {
            require_once(get_config('docroot') . $plugin . '/lib.php');
            if (method_exists(generate_class_name($plugin), 'extra_xmldb_substitution')) {
                $replaced  = call_static_method(generate_class_name($plugin), 'extra_xmldb_substitution', $plugintables);
            }
            else {
                $replaced = $plugintables;
            }
            $tosub .= str_replace('__PLUGINTYPE__', $plugin, $replaced) . "\n";
            $extratables = get_config('docroot') . $plugin . '/extratables.xml';
            if (is_readable($extratables)) {
                $tosub .= file_get_contents($extratables) . "\n";
            }
        }
    }
    $contents = str_replace($searchstring, $tosub, $contents);
}

?>
