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
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
 * Class to use for installation exceptions
 */
class InstallationException extends SystemException {}

require_once('ddl.php');

/**
 * This function checks core and plugins for which need to be upgraded/installed
 *
 * Note: This function is sometimes executed during upgrades from
 * ancient databases.  Avoid rash assumptions about what's installed
 * or these upgrades may fail.
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
            if (is_mysql()) { // Show a more informative error message if using mysql with skip-innodb
                global $db;
                $result = $db->Execute("SHOW VARIABLES LIKE 'have_innodb'");
                if ($result->fields['Value'] != 'YES') {
                    throw new ConfigSanityException("Mahara requires InnoDB tables.  Please ensure InnoDB tables are enabled in your MySQL server.");
                }
            }
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

    if (!$installing && (empty($name) || $name == 'local')) {
        $localversion = get_config('localversion');
        $localrelease = get_config('localrelease');
        if (is_null($localversion)) {
            $localversion = 0;
            $localrelease = 0;
        }

        $config = new StdClass;
        require(get_config('docroot') . 'local/version.php');

        if ($config->version > $localversion) {
            $toupgrade['local'] = (object) array(
                'upgrade'     => true,
                'from'        => $localversion,
                'fromrelease' => $localrelease,
                'to'          => $config->version,
                'torelease'   => $config->release,
            );
        }

        if ($name == 'local') {
            $toupgrade['local']->disablelogin = $disablelogin;
            return $toupgrade['local'];
        }
    }

    $plugins = array();
    if (!empty($name)) {
        try {
            $bits = explode('.', $name);
            $pt = $bits[0];
            $pn = $bits[1];
            $pp = null;
            if ($pt == 'blocktype' && strpos($pn, '/') !== false) {
                $bits = explode('/', $pn);
                $pp = get_config('docroot') . 'artefact/' . $bits[0]  . '/blocktype/' . $bits[1];
            }
            validate_plugin($pt, $pn, $pp);
            $plugins[] = explode('.', $name);
        }
        catch (InstallationException $_e) {
            log_warn("Plugin $pt $pn is not installable: " . $_e->GetMessage());
        }
    }
    else {
        foreach ($pluginstocheck as $plugin) {
            $dirhandle = opendir(get_config('docroot') . $plugin);
            while (false !== ($dir = readdir($dirhandle))) {
                if (strpos($dir, '.') === 0 or 'CVS' == $dir) {
                    continue;
                }
                if (!is_dir(get_config('docroot') . $plugin . '/' . $dir)) {
                    continue;
                }
                try {
                    validate_plugin($plugin, $dir);
                    $plugins[] = array($plugin, $dir);
                }
                catch (InstallationException $_e) {
                    log_warn("Plugin $plugin $dir is not installable: " . $_e->GetMessage());
                }

                if ($plugin == 'artefact') { // go check it for blocks as well
                    $btlocation = get_config('docroot') . $plugin . '/' . $dir . '/blocktype';
                    if (!is_dir($btlocation)) {
                        continue;
                    }
                    $btdirhandle = opendir($btlocation);
                    while (false !== ($btdir = readdir($btdirhandle))) {
                        if (strpos($btdir, '.') === 0 or 'CVS' == $btdir) {
                            continue;
                        }
                        if (!is_dir(get_config('docroot') . $plugin . '/' . $dir . '/blocktype/' . $btdir)) {
                            continue;
                        }
                        $plugins[] = array('blocktype', $dir . '/' . $btdir);
                    }
                }
            }
        }
    }

    foreach ($plugins as $plugin) {
        $plugintype = $plugin[0];
        $pluginname = $plugin[1];
        $pluginpath = "$plugin[0]/$plugin[1]";
        $pluginkey  = "$plugin[0].$plugin[1]";

        if ($plugintype == 'blocktype' && strpos($pluginname, '/') !== false) {
            // sigh.. we're a bit special...
            $bits = explode('/', $pluginname);
            $pluginpath = 'artefact/' . $bits[0] . '/blocktype/' . $bits[1];
        }

        // Don't try to get the plugin info if we are installing - it will
        // definitely fail
        $pluginversion = 0;
        if (!$installing && table_exists(new XMLDBTable($plugintype . '_installed'))) {
            if ($plugintype == 'blocktype' && strpos($pluginname, '/')) {
                $bits = explode('/', $pluginname);
                $installed = get_record('blocktype_installed', 'name', $bits[1], 'artefactplugin', $bits[0]);
            }
            else {
                $installed = get_record($plugintype . '_installed', 'name', $pluginname);
            }
            if ($installed) {
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
    uksort($toupgrade, 'sort_upgrades');
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
        install_from_xmldb_file($location . 'install.xml');
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
 * Upgrades local customisations.
 *
 * @param object $upgrade   The version to upgrade to
 * @return bool             Whether the upgrade succeeded or not
 * @throws SQLException     If the upgrade failed due to a database error
 */
function upgrade_local($upgrade) {
    db_begin();

    require_once(get_config('docroot') . 'local/upgrade.php');
    xmldb_local_upgrade($upgrade->from);

    set_config('localversion', $upgrade->to);
    set_config('localrelease', $upgrade->torelease);

    db_commit();
    return true;
}

/**
 * Upgrades the plugin to a new version
 *
 * Note: This function is sometimes executed during upgrades from
 * ancient databases.  Avoid rash assumptions about what's installed
 * or these upgrades may fail.
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

    if ($plugintype == 'blocktype' && strpos($pluginname, '/') !== false) {
        list($artefactplugin, $blocktypename) = explode('/', $pluginname);
    }

    $location = get_config('docroot') . $plugintype . '/' . $pluginname . '/db/';
    db_begin();

    if (!empty($upgrade->install)) {
        if (is_readable($location . 'install.xml')) {
            install_from_xmldb_file($location . 'install.xml');
        }
    }
    else {
        if (is_readable($location .  'upgrade.php')) {
            require_once($location . 'upgrade.php');
            $function = 'xmldb_' . $plugintype . '_' . $pluginname . '_upgrade';
            if (!$function($upgrade->from)) {
                throw new InstallationException("Failed to run " . $function . " (check logs for errors)");
            }
        }
    }

    $installed = new StdClass;
    $installed->name = $pluginname;
    $installed->version = $upgrade->to;
    $installed->release = $upgrade->torelease;
    if ($plugintype == 'blocktype') {
        if (!empty($blocktypename)) {
            $installed->name = $blocktypename;
        }
        if (!empty($artefactplugin)) { // blocks come from artefactplugins.
            $installed->artefactplugin = $artefactplugin;
        }
    }
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
    $pcname = generate_class_name($plugintype, $installed->name);

    if ($crons = call_static_method($pcname, 'get_cron')) {
        foreach ($crons as $cron) {
            $cron = (object)$cron;
            if (empty($cron->callfunction)) {
                throw new InstallationException("cron for $pcname didn't supply function name");
            }
            if (!is_callable(array($pcname, $cron->callfunction))) {
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
                throw new InstallationException("event $event->event for $pcname doesn't exist!");
            }
            if (empty($event->callfunction)) {
                throw new InstallationException("event $event->event for $pcname didn't supply function name");
            }
            if (!is_callable(array($pcname, $event->callfunction))) {
                throw new InstallationException("event $event->event with function $event->callfunction for $pcname supplied but wasn't callable");
            }
            $exists = false;
            $table = $plugintype . '_event_subscription';
            $block = blocktype_namespaced_to_single($pluginname);
            if (empty($upgrade->install)) {
                $exists = get_record($table, 'plugin' , $block, 'event', $event->event);
            }
            $event->plugin = $block;
            if (empty($exists)) {
                insert_record($table, $event);
            }
            else {
                update_record($table, $event, array('id' => $exists->id));
            }
        }
    }

    if ($activities = call_static_method($pcname, 'get_activity_types')) {
        foreach ($activities as $activity) {
            $classname = 'ActivityType' . ucfirst($plugintype) . ucfirst($pluginname) . ucfirst($activity->name);
            if (!class_exists($classname)) {
                throw new InstallationException(get_string('classmissing', 'error',  $classname, $pluginname, $plugintype));
            }
            $activity->plugintype = $plugintype;
            $activity->pluginname = $pluginname;
            $where = (object) array(
                'name'       => $activity->name,
                'plugintype' => $plugintype,
                'pluginname' => $pluginname,
            );
            // Work around the fact that insert_record cached the columns that
            // _were_ in the activity_type table before it was upgraded
            global $INSERTRECORD_NOCACHE;
            $INSERTRECORD_NOCACHE = true;
            ensure_record_exists('activity_type', $where, $activity);
            unset($INSERTRECORD_NOCACHE);
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
    }

    // install blocktype categories.
    if ($plugintype == 'blocktype' && get_config('installed')) {
        install_blocktype_categories_for_plugin($pluginname);
        install_blocktype_viewtypes_for_plugin($pluginname);
    }

    $prevversion = (empty($upgrade->install)) ? $upgrade->from : 0;
    call_static_method($pcname, 'postinst', $prevversion);

    db_commit();
    return true;
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
    set_config('installation_key', get_random_key());
    set_config('installation_time', $now);
    set_config('stats_installation_time', $now);

    // Pre-define SMTP settings
    set_config('smtphosts', '');
    set_config('smtpport', '');
    set_config('smtpuser', '');
    set_config('smtppass', '');
    set_config('smtpsecure', '');

    // PostgreSQL supports indexes over functions of columns, MySQL does not. 
    // We make use if this if we can
    if (is_postgres()) {
        // Improve the username index
        execute_sql('DROP INDEX {usr_use_uix}');
        execute_sql('CREATE UNIQUE INDEX {usr_use_uix} ON {usr}(LOWER(username))');

        // Only one profile view per user
        execute_sql("CREATE UNIQUE INDEX {view_own_type_uix} ON {view}(owner) WHERE type = 'profile'");
    }

    // Some more advanced constraints. XMLDB can't handle this in its xml file format
    execute_sql('ALTER TABLE {artefact} ADD CHECK (
        (owner IS NOT NULL AND "group" IS NULL     AND institution IS NULL) OR
        (owner IS NULL     AND "group" IS NOT NULL AND institution IS NULL) OR
        (owner IS NULL     AND "group" IS NULL     AND institution IS NOT NULL)
    )');
    execute_sql('ALTER TABLE {view} ADD CHECK (
        (owner IS NOT NULL AND "group" IS NULL     AND institution IS NULL) OR
        (owner IS NULL     AND "group" IS NOT NULL AND institution IS NULL) OR
        (owner IS NULL     AND "group" IS NULL     AND institution IS NOT NULL)
    )');
    execute_sql('ALTER TABLE {artefact} ADD CHECK (
        (author IS NOT NULL AND authorname IS NULL    ) OR
        (author IS NULL     AND authorname IS NOT NULL)
    )');
    execute_sql('ALTER TABLE {view_access} ADD CHECK (
        (accesstype IS NOT NULL AND "group" IS NULL     AND usr IS NULL     AND token IS NULL) OR
        (accesstype IS NULL     AND "group" IS NOT NULL AND usr IS NULL     AND token IS NULL) OR
        (accesstype IS NULL     AND "group" IS NULL     AND usr IS NOT NULL AND token IS NULL) OR
        (accesstype IS NULL     AND "group" IS NULL     AND usr IS NULL     AND token IS NOT NULL)
    )');

    set_antispam_defaults();
    reload_html_filters();
    return $status;
}

function core_install_lastcoredata_defaults() {
    global $USER;
    db_begin();
    $institution = new StdClass;
    $institution->name = 'mahara';
    $institution->displayname = 'No Institution';
    $institution->authplugin  = 'internal';
    $institution->theme  = 'default';
    $institution->priority = 0;
    insert_record('institution', $institution);

    $auth_instance = new StdClass;
    $auth_instance->instancename  = 'Internal';
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
    $user->firstname = 'System';
    $user->lastname = 'User';
    $user->email = 'root@example.org';
    $user->quota = get_config_plugin('artefact', 'file', 'defaultquota');
    $user->authinstance = $auth_instance->id;

    if (is_mysql()) { // gratuitous mysql workaround
        $newid = insert_record('usr', $user, 'id', true);
        set_field('usr', 'id', 0, 'id', $newid);
        execute_sql('ALTER TABLE {usr} AUTO_INCREMENT=1');
    }
    else {
        insert_record('usr', $user);
    }

    require_once('group.php');
    install_system_profile_view();
    install_system_dashboard_view();
    install_system_grouphomepage_view();

    // Insert the admin user
    $user = new StdClass;
    $user->username = 'admin';
    $user->salt = auth_get_random_salt();
    $user->password = sha1($user->salt . 'mahara');
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
    handle_event('createuser', $user);
    activity_add_admin_defaults(array($user->id));
    db_commit();

    // if we're installing, set up the block categories here and then poll the plugins.
    // if we're upgrading this happens somewhere else.  This is because of dependency issues around
    // the order of installation stuff.

    install_blocktype_extras();
}

function core_install_firstcoredata_defaults() {
    // Install the default institution
    db_begin();

    set_config('session_timeout', 86400);
    set_config('sitename', 'Mahara');
    set_config('defaultaccountinactivewarn', 604800);
    set_config('creategroups', 'all');
    set_config('createpublicgroups', 'all');
    set_config('allowpublicviews', 1);
    set_config('allowpublicprofiles', 1);
    set_config('showselfsearchsideblock', 0);
    set_config('showtagssideblock', 1);
    set_config('tagssideblockmaxtags', 20);
    set_config('usersallowedmultipleinstitutions', 1);
    set_config('viewmicroheaders', 0);
    set_config('userscanchooseviewthemes', 0);
    set_config('anonymouscomments', 1);
    set_config('homepageinfo', 1);
    set_config('showonlineuserssideblock', 1);
    set_config('footerlinks', serialize(array('privacystatement', 'about', 'contactus')));
    set_config('searchusernames', 1);

    // install the applications
    $app = new StdClass;
    $app->name = 'mahara';
    $app->displayname = 'Mahara';
    $app->xmlrpcserverurl = '/api/xmlrpc/server.php';
    $app->ssolandurl = '/auth/xmlrpc/land.php';
    insert_record('application', $app);

    $app->name = 'moodle';
    $app->displayname = 'Moodle';
    $app->xmlrpcserverurl = '/mnet/xmlrpc/server.php';
    $app->ssolandurl = '/auth/mnet/land.php';
    insert_record('application', $app);

    // insert the event types
    $eventtypes = array(
        'createuser',
        'updateuser',
        'suspenduser',
        'unsuspenduser',
        'deleteuser',
        'undeleteuser',
        'expireuser',
        'unexpireuser',
        'deactivateuser',
        'activateuser',
        'userjoinsgroup',
        'saveartefact',
        'deleteartefact',
        'deleteartefacts',
        'saveview',
        'deleteview',
        'blockinstancecommit',
        'addfriend',
        'removefriend',
        'addfriendrequest',
        'removefriendrequest',
        'creategroup',
    );

    foreach ($eventtypes as $et) {
        $e = new StdClass;
        $e->name = $et;
        insert_record('event_type', $e);
    }

    // install the core event subscriptions
    $subs = array(
        array(
            'event'        => 'createuser',
            'callfunction' => 'activity_set_defaults',
        ),
        array(
            'event'        => 'createuser',
            'callfunction' => 'add_user_to_autoadd_groups',
        ),
    );

    foreach ($subs as $sub) {
        insert_record('event_subscription', (object)$sub);
    }

    // install the activity types
    $activitytypes = array(
        array('maharamessage', 0, 0),
        array('usermessage', 0, 0),
        array('watchlist', 0, 1),
        array('viewaccess', 0, 1),
        array('contactus', 1, 1),
        array('objectionable', 1, 1),
        array('virusrepeat', 1, 1),
        array('virusrelease', 1, 1),
        array('institutionmessage', 0, 0),
        array('groupmessage', 0, 1),
    );

    foreach ($activitytypes as $at) {
        $a = new StdClass;
        $a->name = $at[0];
        $a->admin = $at[1];
        $a->delay = $at[2];
        insert_record('activity_type', $a);
    }

    // install the cronjobs...
    $cronjobs = array(
        'rebuild_artefact_parent_cache_dirty'       => array('*', '*', '*', '*', '*'),
        'rebuild_artefact_parent_cache_complete'    => array('0', '4', '*', '*', '*'),
        'auth_clean_partial_registrations'          => array('5', '0', '*', '*', '*'),
        'auth_handle_account_expiries'              => array('5', '10', '*', '*', '*'),
        'auth_handle_institution_expiries'          => array('5', '9', '*', '*', '*'),
        'activity_process_queue'                    => array('*/5', '*', '*', '*', '*'),
        'auth_remove_old_session_files'             => array('30', '20', '*', '*', '*'),
        'recalculate_quota'                         => array('15', '2', '*', '*', '*'),
        'import_process_queue'                      => array('*/5', '*', '*', '*', '*'),
        'cron_send_registration_data'               => array(rand(0, 59), rand(0, 23), '*', '*', rand(0, 6)),
        'export_cleanup_old_exports'                => array('0', '3,15', '*', '*', '*'),
        'import_cleanup_old_imports'                => array('0', '4,16', '*', '*', '*'),
        'cron_site_data_weekly'                     => array('55', '23', '*', '*', '6'),
        'cron_site_data_daily'                      => array('51', '23', '*', '*', '*'),
        'cron_check_for_updates'                    => array(rand(0, 59), rand(0, 23), '*', '*', '*'),
        'cron_clean_internal_activity_notifications'=> array(45, 22, '*', '*', '*'),
    );
    foreach ($cronjobs as $callfunction => $times) {
        $cron = new StdClass;
        $cron->callfunction = $callfunction;
        $cron->minute       = $times[0];
        $cron->hour         = $times[1];
        $cron->day          = $times[2];
        $cron->month        = $times[3];
        $cron->dayofweek    = $times[4];
        insert_record('cron', $cron);
    }

    // install the view column widths
    install_view_column_widths();

    $viewtypes = array('dashboard', 'portfolio', 'profile', 'grouphomepage');
    foreach ($viewtypes as $vt) {
        insert_record('view_type', (object)array(
            'type' => $vt,
        ));
    }
    db_commit();
}


/**
 * xmldb will pass us the xml file and we can perform any substitution as necessary
 */
function local_xmldb_contents_sub(&$contents) {
    // the main install.xml file needs to sub in plugintype tables.
    $searchstring = '<!-- PLUGIN_TYPE_SUBSTITUTION -->';
    if (strstr($contents, $searchstring) === 0) {
        return;
    }
    // ok, we're in the main file and we need to install all the plugin tables
    // get the basic skeleton structure
    $plugintables = file_get_contents(get_config('docroot') . 'lib/db/plugintables.xml');
    $tosub = '';
    foreach (plugin_types() as $plugin) {
        // any that want their own stuff can put it in docroot/plugintype/lib/db/plugintables.xml
        //- like auth is a bit special
        $specialcase = get_config('docroot') . $plugin . '/plugintables.xml';
        if (is_readable($specialcase)) {
            $tosub .= file_get_contents($specialcase) . "\n";
        }
        else {
            $replaced = '';
            // look for tables to put at the start...
            $pretables = get_config('docroot') . $plugin . '/beforetables.xml';
            if (is_readable($pretables)) {
                $replaced = file_get_contents($pretables) . "\n";
            }

            // perform any additional once off substitutions
            require_once(get_config('docroot') . $plugin . '/lib.php');
            if (method_exists(generate_class_name($plugin), 'extra_xmldb_substitution')) {
                $replaced  .= call_static_method(generate_class_name($plugin), 'extra_xmldb_substitution', $plugintables);
            }
            else {
                $replaced .= $plugintables;
            }
            $tosub .= str_replace('__PLUGINTYPE__', $plugin, $replaced) . "\n";
            // look for any tables to put at the end..
            $extratables = get_config('docroot') . $plugin . '/extratables.xml';
            if (is_readable($extratables)) {
                $tosub .= file_get_contents($extratables) . "\n";
            }
        }
    }
    $contents = str_replace($searchstring, $tosub, $contents);
}


/**
 * validates a plugin for installation
 * @throws InstallationException
*/
function validate_plugin($plugintype, $pluginname, $pluginpath='') {

    if (empty($pluginpath)) {
        $pluginpath = get_config('docroot') . $plugintype . '/' . $pluginname;
    }
    if (!file_exists($pluginpath . '/version.php')) {
        throw new InstallationException(get_string('versionphpmissing', 'error', $plugintype, $pluginname));
    }
    safe_require($plugintype, $pluginname);
    $classname = generate_class_name($plugintype, $pluginname);
    if (!class_exists($classname)) {
        throw new InstallationException(get_string('classmissing', 'error', $classname, $plugintype, $pluginname));
    }
    require_once(get_config('docroot') . $plugintype . '/lib.php');
    $funname = $plugintype . '_check_plugin_sanity';
    if (function_exists($funname)) {
        $funname($pluginname);
    }
}

/*
* the order things are installed/upgraded in matters
*/

function sort_upgrades($k1, $k2) {
    if ($k1 == 'core') {
        return -1;
    }
    else if ($k2 == 'core') {
        return 1;
    }
    else if ($k1 == 'firstcoredata') {
        return -1;
    }
    else if ($k2 == 'firstcoredata') {
        return 1;
    }
    else if ($k1 == 'localpreinst') {
        return -1;
    }
    else if ($k2 == 'localpreinst') {
        return 1;
    }
    else if ($k1 == 'localpostinst') {
        return 1;
    }
    else if ($k2 == 'localpostinst') {
        return -1;
    }
    else if ($k1 == 'lastcoredata') {
        return 1;
    }
    else if ($k2 == 'lastcoredata') {
        return -1;
    }
    // else obey the order plugin types returns (strip off plugintype. from the start)
    $weight1 = array_search(substr($k1, 0, strpos($k1, '.')), plugin_types());
    $weight2 = array_search(substr($k2, 0, strpos($k2, '.')), plugin_types());
    return ($weight1 > $weight2);
}

/** blocktype categories the system exports (including artefact categories)
*/
function get_blocktype_categories() {
    return array('general', 'internal', 'blog', 'resume', 'fileimagevideo', 'external');
}

function install_blocktype_categories_for_plugin($blocktype) {
    safe_require('blocktype', $blocktype);
    $blocktype = blocktype_namespaced_to_single($blocktype);
    $catsinstalled = get_column('blocktype_category', 'name');
    db_begin();
    delete_records('blocktype_installed_category', 'blocktype', $blocktype);
    if ($cats = call_static_method(generate_class_name('blocktype', $blocktype), 'get_categories')) {
        foreach ($cats as $cat) {
            if (in_array($cat, $catsinstalled)) {
                insert_record('blocktype_installed_category', (object)array(
                    'blocktype' => $blocktype,
                    'category' => $cat
                ));
            }
        }
    }
    db_commit();
}

function install_blocktype_viewtypes_for_plugin($blocktype) {
    safe_require('blocktype', $blocktype);
    $blocktype = blocktype_namespaced_to_single($blocktype);
    $vtinstalled = get_column('view_type', 'type');
    db_begin();
    delete_records('blocktype_installed_viewtype', 'blocktype', $blocktype);
    if ($viewtypes = call_static_method(generate_class_name('blocktype', $blocktype), 'get_viewtypes')) {
        foreach($viewtypes as $vt) {
            if (in_array($vt, $vtinstalled)) {
                insert_record('blocktype_installed_viewtype', (object)array(
                    'blocktype' => $blocktype,
                    'viewtype'  => $vt,
                ));
            }
        }
    }
    db_commit();
}

function install_blocktype_extras() {
    db_begin();

    $categories = get_blocktype_categories();
    $installedcategories = get_column('blocktype_category', 'name');

    if ($toinstall = array_diff($categories, $installedcategories)) {
        foreach ($toinstall as $i) {
            insert_record('blocktype_category', (object)array('name' => $i));
        }
    }

    db_commit();


    // poll all the installed blocktype plugins and ask them what categories they export
    if ($blocktypes = plugins_installed('blocktype', true)) {
        foreach ($blocktypes as $bt) {
            install_blocktype_categories_for_plugin(blocktype_single_to_namespaced($bt->name, $bt->artefactplugin));
            install_blocktype_viewtypes_for_plugin(blocktype_single_to_namespaced($bt->name, $bt->artefactplugin));
        }
    }
}

/**
 * Installs all the allowed column widths for views. Used when installing core
 * defaults, and also when upgrading from 0.8 to 0.9
 */
function install_view_column_widths() {
    db_begin();
    require_once('view.php');

    $layout = new StdClass;
    foreach (View::$layouts as $column => $widths) {
        foreach ($widths as $width) {
            $layout->columns = $column;
            $layout->widths = $width;
            insert_record('view_layout', $layout);
        }
    }
    db_commit();
}

/**
 * Reload htmlpurifier filters from the XML configuration file.
 */
function reload_html_filters() {
    require_once('xmlize.php');

    $newlist = xmlize(file_get_contents(get_config('libroot') . 'htmlpurifiercustom/filters.xml'));
    $filters = $newlist['filters']['#']['filter'];
    foreach ($filters as &$f) {
        $f = (object) array(
            'site' => $f['#']['site'][0]['#'],
            'file' => $f['#']['filename'][0]['#']
        );
    }
    set_config('filters', serialize($filters));
    log_info('Enabled ' . count($filters) . ' HTML filters.');
}

/**
 * Use meaningful defaults for the antispam settings.
 */
function set_antispam_defaults() {
    set_config('formsecret', get_random_key());
    require_once(get_config('docroot') . 'lib/antispam.php');
    if(checkdnsrr('test.uribl.com.black.uribl.com', 'A')) {
        set_config('antispam', 'advanced');
    }
    else {
        set_config('antispam', 'simple');
    }
    set_config('spamhaus', 0);
    set_config('surbl', 0);
}

function activate_plugin_form($plugintype, $plugin) {
    return pieform(array(
        'name'            => 'activate_' . $plugintype . '_' . $plugin->name,
        'renderer'        => 'oneline',
        'elementclasses'  => false,
        'successcallback' => 'activate_plugin_submit',
        'class'           => 'oneline inline',
        'jsform'          => false,
        'action'          => get_config('wwwroot') . 'admin/extensions/pluginconfig.php',
        'elements' => array(
            'plugintype' => array('type' => 'hidden', 'value' => $plugintype),
            'pluginname' => array('type' => 'hidden', 'value' => $plugin->name),
            'disable'    => array('type' => 'hidden', 'value' => $plugin->active),
            'enable'     => array('type' => 'hidden', 'value' => 1-$plugin->active),
            'submit'     => array(
                'type'  => 'submit',
                'class' => 'linkbtn',
                'value' => $plugin->active ? get_string('hide') : get_string('show')
            ),
        ),
    ));
}

function activate_plugin_submit(Pieform $form, $values) {
    global $SESSION;
    if ($values['plugintype'] == 'blocktype') {
        if (strpos($values['pluginname'], '/') !== false) {
            list($artefact, $values['pluginname']) = split('/', $values['pluginname']);
            // Don't enable blocktypes unless the artefact plugin that provides them is also enabled
            if ($values['enable'] && !get_field('artefact_installed', 'active', 'name', $artefact)) {
                $SESSION->add_error_msg(get_string('pluginnotenabled', 'mahara', $artefact));
                redirect('/admin/extensions/plugins.php');
            }
        }
    }
    else if ($values['plugintype'] == 'artefact' && $values['disable']) {
        // Disable all the artefact's blocktypes too
        set_field('blocktype_installed', 'active', 0, 'artefactplugin', $values['pluginname']);
    }
    set_field($values['plugintype'] . '_installed', 'active', $values['enable'], 'name', $values['pluginname']);
    $SESSION->add_ok_msg(get_string('plugin' . (($values['enable']) ? 'enabled' : 'disabled')));
    redirect('/admin/extensions/plugins.php');
}

// site warnings for the admin to consider
function site_warnings() {

    $warnings = array();

    // Warn about nasty php settings that Mahara can still sort of deal with.
    if (ini_get_bool('register_globals')) {
        $warnings[] = get_string('registerglobals', 'error');
    }
    if (!defined('CRON') && ini_get_bool('magic_quotes_gpc')) {
        $warnings[] = get_string('magicquotesgpc', 'error');
    }
    if (ini_get_bool('magic_quotes_runtime')) {
        $warnings[] = get_string('magicquotesruntime', 'error');
    }
    if (ini_get_bool('magic_quotes_sybase')) {
        $warnings[] = get_string('magicquotessybase', 'error');
    }

    // Check if the host returns a usable value for the timezone identifier %z
    $tz_count = preg_match("/[\+\-][0-9]{4}/", strftime("%z"));
    if ($tz_count == 0 || $tz_count == FALSE) {
        $warnings[] = get_string('timezoneidentifierunusable', 'error');
    }

    // Check file upload settings.
    $postmax       = ini_get('post_max_size');
    $uploadmax     = ini_get('upload_max_filesize');
    $realpostmax   = get_real_size($postmax);
    $realuploadmax = get_real_size($uploadmax);
    if ($realpostmax && $realpostmax < $realuploadmax) {
        $warnings[] = get_string('postmaxlessthanuploadmax', 'error', $postmax, $uploadmax, $postmax);
    }
    else if ($realpostmax && $realpostmax < 9000000) {
        $warnings[] = get_string('smallpostmaxsize', 'error', $postmax, $postmax);
    }

    return $warnings;
}
