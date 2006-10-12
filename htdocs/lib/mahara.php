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
 * @copyright  (C) portions from Moodle, (C) Martin Dougiamas http://dougiamas.com
 */

defined('INTERNAL') || die();


/**
 * This function checks core and plugins
 * for which need to be upgraded/installed
 * @returns array of objects
 */
function check_upgrades($name = null) {
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

    $plugins = array();
    if (strpos($name,'artefact.') === 0) {
        $plugins[] = substr($name,9);
    } 
    else if (empty($name)) {
        // artefact plugins next..
        $dirhandle = opendir(get_config('docroot').'artefact/');
        while (false !== ($dir = readdir($dirhandle))) {
            if (strpos($dir,'.') === 0) {
                continue;
            }
            if (!empty($installing) && $dir != 'internal') {
                continue;
            }
            $plugins[] = $dir;
        }
    }

    foreach ($plugins as $dir) {
        require(get_config('docroot') . 'artefact/' . $dir . '/version.php');
        $pluginversion = 0;
        try {
            $pluginversion = get_config_plugin('arfetact', $dir, 'version');
        }
        catch (Exception $e) { }
        if (empty($pluginversion)) {
            $plugin = new StdClass;
            $plugin->install = true;
            $plugin->to = $config->version;
            $plugin->torelease = $config->release;
            $toupgrade['artefact.' . $dir] = $plugin;
        }
        else if ($config->version > $pluginversion) {
            $plugin = new StdClass;
            $plugin->upgrade = true;
            $plugin->from = $pluginversion;
            try {
                $plugin->fromrelease = get_config_plugin('artefact',$dir,'release');
            }
            catch (Exception $e) { }
            $plugin->to = $config->version;
            $plugin->torelease = $config->release;
            $toupgrade['artefact.' . $dir] = $plugin;
        } 
    }

    // if we've just asked for one, don't return an array...
    if (!empty($name) && count($toupgrade) == 1) {
        $upgrade = new StdClass;
        $upgrade->name = $name;
        foreach ((array)$toupgrade[$name] as $key => $value) {
            $upgrade->{$key} = $value;
        }    
        return $upgrade;
    }

    return $toupgrade;
}

function upgrade_core($upgrade) {

    $location = get_config('libroot') . '/db/';
    
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
    return $status;
}

function upgrade_plugin($upgrade) {
    
    $plugintype = '';
    $pluginname = '';

    if (strpos($upgrade->name,'artefact.') === 0) {
        $pluginname = substr($upgrade->name,9);
        $plugintype = 'artefact';
    }

    $location = get_config('dirroot') . $plugintype . '/' . $pluginname . '/db/';
    
    if (!empty($upgrade->install)) {
        // @todo check file exists first - reasonable for it not to have 
        // db tables at all. should still insert version number and cron etc
        // @todo add to installed_artefacts
        $status = install_from_xmldb_file($location . 'install.xml'); 
    }
    else {
        require_once($location . 'upgrade.php');
        // @todo check file exists first - reasonable for it not to have 
        // db tables at all. should still insert version number and cron etc
        $function = 'xmldb_' . $plugintype . '_' . $pluginname . '_upgrade';
        $status = $function($upgrade->from);
    }
    
    if (!$status) {
        throw new DatalibException("Failed to upgrade $upgrade->name");
    }
    
    $status = set_config_plugin($plugintype,$pluginname,'version',$upgrade->to);
    $status = $status && set_config_plugin($plugintype,$pluginname,'release',$upgrade->torelease);

    // @todo here is where plugins register events and set their crons up
    
    return $status;
}

/** 
 * work around silly php settings
 * and broken setup stuff about the install
 * and raise a warning/fail depending on severity
 */
function ensure_sanity() {

    // register globals workaround
    if (ini_get_bool('register_globals')) {
        log_environ(get_string('registerglobals', 'error'));
        $massivearray = array_keys(array_merge($_POST,$_GET,$_COOKIE,$_SERVER,$_REQUEST,$_FILES));
        foreach ($massivearray as $tounset) {
            unset($GLOBALS[$tounset]);
        }
    }

    // magic_quotes_gpc workaround
    if (ini_get_bool('magic_quotes_gpc')) {
        log_environ(get_string('magicquotesgpc', 'error'));
        function stripslashes_deep($value) {
            $value = is_array($value) ?
                array_map('stripslashes_deep', $value) :
                stripslashes($value);
            return $value;
        }
        $_POST = array_map('stripslashes_deep', $_POST);
        $_GET = array_map('stripslashes_deep', $_GET);
        $_COOKIE = array_map('stripslashes_deep', $_COOKIE);
        $_REQUEST = array_map('stripslashes_deep', $_REQUEST);

        $servervars = array('REQUEST_URI','QUERY_STRING','HTTP_REFERER','PATH_INFO','PHP_SELF','PATH_TRANSLATED');
        foreach ($servervars as $tocheck) {
            if (array_key_exists($tocheck,$_SERVER) && !empty($_SERVER[$tocheck])) {
                $_SERVER[$tocheck] = stripslashes($_SERVER[$tocheck]);
            }
        }
    }

    if (ini_get_bool('magic_quotes_runtime')) {
        // Turn of magic_quotes_runtime. Anyone with this on deserves a slap in the face
        set_magic_quotes_runtime(0);
        log_environ(get_string('magicquotesruntime', 'error'));
    }

    if (ini_get_bool('magic_quotes_sybase')) {
        // See above comment re. magic_quotes_runtime
        @ini_set('magic_quotes_sybase', 0);
        log_environ(get_string('magicquotessybase', 'error'));
    }

    if (ini_get_bool('safe_mode')) {
        // We don't run with safe mode
        throw new ConfigSanityException(get_string('safemodeon', 'error'));
    }

    // Other things that might be worth checking:
    //    memory limit
    //    file_uploads (off|on)
    //    upload_max_filesize
    //    allow_url_fopen (only if we use this)
    //

    // dataroot inside document root.
    if (strpos(get_config('dataroot'),get_config('docroot')) !== false) {
        throw new ConfigSanityException(get_string('datarootinsidedocroot','error'));
    }

    // dataroot not writable..
    if (!check_dir_exists(get_config('dataroot')) || !is_writable(get_config('dataroot'))) {
        throw new ConfigSanityException(get_string('datarootnotwritable', 'error', get_config('dataroot')));
    }

    // Json functions not available
    if (!function_exists('json_encode') || !function_exists('json_decode')) {
        throw new ConfigSanityException(get_string('jsonextensionnotloaded', 'error'));
    }
    
    check_dir_exists(get_config('dataroot').'smarty/compile');
    check_dir_exists(get_config('dataroot').'smarty/cache');

}

function get_string($identifier,$section) {

    $langconfigstrs = array('parentlanguage', 'strftimedate', 'strftimedateshort', 'strftimedatetime',
                            'strftimedaydate', 'strftimedaydatetime', 'strftimedayshort', 'strftimedaytime',
                            'strftimemonthyear', 'strftimerecent', 'strftimerecentfull', 'strftimetime',
                            'thislanguage');

    if (in_array($identifier, $langconfigstrs)) {
        $section = 'langconfig';  
    }

    $variables = func_get_args();
    if (count($variables) > 2) { // we have some stuff we need to sprintf
        array_shift($variables);
        array_shift($variables); //shift off the first two.
    } else {
        $variables = array();
    }
    
    $lang = current_language();

    if ($section == '') {
        $section = 'mahara';
    }

    // Define the locations of language strings for this section
    $docroot = get_config('docroot');
    $locations = array();
    
    if ($section == 'mahara' || $section != 'langconfig') {
        $locations[] = $docroot.'lang/';
    } else {
        $extras = array('artefacts','auth'); // more later..
        foreach ($extras as $tocheck) {
            if (strpos($section,$tocheck.'.') === 0) {
                $pluginname = substr($section,strlen($tocheck));
                $locations[] = $docroot.$tocheck.'/'.$pluginname.'/lang/';
            }
        }
    }

    // First check all the normal locations for the string in the current language
    foreach ($locations as $location) {
        //if local directory not found, or particular string does not exist in local direcotry
        $langfile = $location.$lang.'/'.$section.'.php';
        if (is_readable($langfile)) {
            if ($result = get_string_from_file($identifier, $langfile)) {
                return format_langstring($result,$variables);
            }
        }
    }

    // If the preferred language was English (utf8) we can abort now
    // saving some checks beacuse it's the only "root" lang
    if ($lang == 'en.utf8') {
        return '[['. $identifier .']]';
    }

    // Is a parent language defined?  If so, try to find this string in a parent language file

    foreach ($locations as $location) {
        $langfile = $location.$lang.'/langconfig.php';
        if (is_readable($langfile)) {
            if ($parentlang = get_string_from_file('parentlanguage', $langfile)) {
                $langfile = $location.$parentlang.'/'.$section.'.php';
                if (is_readable($langfile)) {
                    if ($result = get_string_from_file($identifier, $langfile)) {
                        return format_langstring($result,$variables);
                    }
                }
            }
        }
    }

    /// Our only remaining option is to try English
    foreach ($locations as $location) {
        //if local_en not found, or string not found in local_en
        $langfile = $location.'en.utf8/'.$module.'.php';
        if (is_readable($langfile)) {
            if ($result = get_string_from_file($identifier, $langfile)) {
                return format_langstring($result,$variables);
            }
        }
    }

    return '[['.$identifier.']]';  // Last resort
}



/**
 * This function is only used from {@link get_string()}.
 *
 * @internal Only used from get_string, not meant to be public API
 * @param string $identifier ?
 * @param string $langfile ?
 * @param string $destination ?
 * @return string|false ?
 * @staticvar array $strings Localized strings
 * @access private
 * @todo Finish documenting this function.
 */
function get_string_from_file($identifier, $langfile) {

    static $strings;    // Keep the strings cached in memory.

    if (empty($strings[$langfile])) {
        $string = array();
        include ($langfile);
        $strings[$langfile] = $string;
    } else {
        $string = &$strings[$langfile];
    }

    if (!isset ($string[$identifier])) {
        return false;
    }

    return $string[$identifier];
}

/**
 * This function makes the return value of ini_get consistent if you are
 * setting server directives through the .htaccess file in apache.
 * Current behavior for value set from php.ini On = 1, Off = [blank]
 * Current behavior for value set from .htaccess On = On, Off = Off
 * Contributed by jdell @ unr.edu
 *
 * @param string $ini_get_arg setting to look for
 * @return bool
 */
function ini_get_bool($ini_get_arg) {
    $temp = ini_get($ini_get_arg);

    if ($temp == '1' or strtolower($temp) == 'on') {
        return true;
    }
    return false;
}

/**
 * This function loads up the basic $CFG
 * from the database table
 * note that it doesn't load plugin config
 * as not every page needs them
 * @return boolean false if the assignment fails (generally if the databse is not installed)
 */
function load_config() {
    global $CFG;
    
    try {
        $dbconfig = get_records('config');
    } 
    catch (ADODB_Exception $e) {
        return false;
    }
    
    foreach ($dbconfig as $cfg) {
        if (isset($CFG->{$cfg->field}) && $CFG->{$cfg->field} != $CFG->value) {
            // @todo warn that we're overriding db config with $CFG
            continue;
        }
        $CFG->{$cfg->field} = $cfg->value;
    }

    return true;
}

/**
 * This function returns a value from $CFG
 * or null if it is not found
 * 
 * @param string $key config setting to look for 
 * @return mixed
 */
function get_config($key) {
    global $CFG;
    if (array_key_exists($key,$CFG)) {
        return $CFG->$key;
    }
    return null;
}


/**
 * This function sets a config variable
 * both in $CFG and in the database
 *
 * @param string $key config field to set
 * @param string $value config value
 */
function set_config($key, $value) {
    if (get_record('config','field',$key)) {
        if (set_field('config','value',$value,'field',$key)) {
            $status = true;
        }
    } 
    else {
        $config = new StdClass;
        $config->field = $key;
        $config->value = $value;
        $status = insert_record('config',$config);
    }

    if (!empty($status)) {
        $CFG->{$key} = $value;
        return true;
    }

    return false;
}

/**
 * This function returns a value for $CFG for a plugin
 * or null if it is not found
 * note that it may go and look in the database
 *
 * @param string $plugintype eg artefact
 * @param string $pluginname eg blog
 * @param string $key the config setting to look for
 */
function get_config_plugin($plugintype, $pluginname, $key) {
    global $CFG;

    if (array_key_exists('plugin',$CFG)
        && array_key_exists($plugintype,$CFG->plugin)
        && array_key_exists($pluginname,$CFG->plugin->{$plugintype})
        && array_key_exists($key,$CFG->plugin->{$plugintype}->{$pluginname})) {
        return  $CFG->plugin->{$plugintype}->{$pluginname}->{$key};
    }
    
    // @todo: an optimisation might be to get all fields related to the plugin instead, as
    // it may be quite likely that if one config item is requested for a plugin another
    // might be.
    if (!$value = get_field('config_'.$plugintype,'value','plugin',$pluginname,'field',$key)) {
        $value = null;
    } 
    
    $CFG->plugin->{$plugintype}->{$pluginname}->{$key} = $value;
    return $value;
}

function set_config_plugin($plugintype, $pluginname, $key, $value) {
    $tablename = 'config_' . $plugintype;

    if (get_field($table,'value','plugin',$pluginname,'field',$key)) {
        if (set_field($table,'value',$key,'plugin',$pluginname, 'field',$value)) { 
            $status = true;
        }
    }
    else {
        $pconfig = new StdClass;
        $pconfig->plugin = $pluginname;
        $pconfig->field  = $key;
        $pconfig->value  = $value;
        $status = insert_record($table,$pconfig);
    }
    if ($status) {
        $CFG->plugin->{$plugintype}->{$pluginname}->{$key} = $value;
        return true;
    }
    return false;
}

/**
 * This function prints an array or object
 * wrapped inside <pre></pre>
 * 
 * @param $mixed value to print
 */
function print_object($mixed) {
    echo '<pre>';
    print_r($mixed);
    echo '</pre>';
}

/**
 * This function returns the current 
 * language to use, either for a given user
 * or sitewide, or the default
 * 
 * @return string
 */
function current_language() {
    global $USER, $CFG;
    if (!empty($USER->lang)) {
        return $USER->lang;
    }
    if (!empty($CFG->lang)) {
        return $CFG->lang;
    }
    return 'en.utf8';
}

/**
 * Helper function to sprintf language strings
 * with a variable number of arguments
 * 
 * @param string $string raw string to use
 * @param array $args arguments to sprintf
 */
function format_langstring($string,$args) {
    return call_user_func_array('sprintf',array_merge(array($string),$args));
}

/**
 * Helper function to figure out whether an array is an array or a hash
 * @param array $array array to check
 * @return bool true if the array is a hash
 */
function is_hash($array) {
    if (!is_array($array)) {
        return false;
    }
    $diff = array_diff_assoc($array,array_values($array));
    return !empty($diff);
}



/**
 * Function to check if a directory exists and optionally create it.
 *
 * @param string absolute directory path
 * @param boolean create directory if does not exist
 * @param boolean create directory recursively
 *
 * @return boolean true if directory exists or created
 */
function check_dir_exists($dir, $create=true, $recursive=true) {

    $status = true;

    if(!is_dir($dir)) {
        if (!$create) {
            $status = false;
        } else {
            umask(0000); 
            $status = @mkdir($dir, 0777, true);
            // @todo has the umask been clobbered at this point, and is this a bad thing?
        }
    }
    return $status;
}

?>
