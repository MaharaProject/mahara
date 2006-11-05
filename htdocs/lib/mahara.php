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
 * work around silly php settings
 * and broken setup stuff about the install
 * and raise a warning/fail depending on severity
 */
function ensure_sanity() {

    // PHP version
    if (version_compare(phpversion(), '5.1.0') < 0) {
        throw new ConfigSanityException(get_string('phpversion', 'error'));
    }

    // Various required extensions
    if (!extension_loaded('json')) {
        throw new ConfigSanityException(get_string('jsonextensionnotloaded', 'error'));
    }
    if (!extension_loaded('pgsql') && !extension_loaded('mysqli')) {
        throw new ConfigSanityException(get_string('dbextensionnotloaded', 'error'));
    }
    if (!extension_loaded('libxml')) {
        throw new ConfigSanityException(get_string('libxmlextensionnotloaded', 'error'));
    }
    if (!extension_loaded('gd')) {
        throw new ConfigSanityException(get_string('gdextensionnotloaded', 'error'));
    }
    if (!extension_loaded('session')) {
        throw new ConfigSanityException(get_string('sessionextensionnotloaded', 'error'));
    }

    // register globals workaround
    if (ini_get_bool('register_globals')) {
        log_environ(get_string('registerglobals', 'error'));
        $massivearray = array_keys(array_merge($_POST, $_GET, $_COOKIE, $_SERVER, $_REQUEST, $_FILES));
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
    if (strpos(get_config('dataroot'), get_config('docroot')) !== false) {
        throw new ConfigSanityException(get_string('datarootinsidedocroot', 'error'));
    }

    // dataroot not writable..
    if (!check_dir_exists(get_config('dataroot')) || !is_writable(get_config('dataroot'))) {
        throw new ConfigSanityException(get_string('datarootnotwritable', 'error', get_config('dataroot')));
    }

    // @todo the results of these should be checked
    check_dir_exists(get_config('dataroot').'smarty/compile');
    check_dir_exists(get_config('dataroot').'smarty/cache');

}

/**
 * Check to see if the internal plugins are installed. Die if they are not.
 */
function ensure_internal_plugins_exist() {
    // Internal things installed
    if (!get_config('installed')) {
        foreach (plugin_types() as $type) {
            if (!record_exists($type . '_installed', 'name', 'internal')) {
                throw new ConfigSanityException(get_string($type . 'notinstalled'));
            }
        }
    }
}

function get_string($identifier, $section='mahara') {

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
    }
    else {
        $variables = array();
    }
    
    $lang = current_language();

    // Define the locations of language strings for this section
    $docroot = get_config('docroot');
    $locations = array();
    
    if (false === strpos($section, '.')) {
        $locations[] = $docroot . 'lang/';
    }
    else {
        $extras = plugin_types(); // more later..
        foreach ($extras as $tocheck) {
            if (strpos($section,$tocheck . '.') === 0) {
                $pluginname = substr($section,strlen($tocheck) + 1);
                $locations[] = $docroot . $tocheck . '/' . $pluginname . '/lang/';
            }
        }
    }

    // First check all the normal locations for the string in the current language
    foreach ($locations as $location) {
        //if local directory not found, or particular string does not exist in local direcotry
        $langfile = $location . $lang . '/' . $section . '.php';
        if (is_readable($langfile)) {
            if ($result = get_string_from_file($identifier, $langfile)) {
                return format_langstring($result, $variables);
            }
        }
    }

    // If the preferred language was English (utf8) we can abort now
    // saving some checks beacuse it's the only "root" lang
    if ($lang == 'en.utf8') {
        return '[[' . $identifier . ']]';
    }

    // Is a parent language defined?  If so, try to find this string in a parent language file

    foreach ($locations as $location) {
        $langfile = $location . $lang . '/langconfig.php';
        if (is_readable($langfile)) {
            if ($parentlang = get_string_from_file('parentlanguage', $langfile)) {
                $langfile = $location . $parentlang . '/' . $section . '.php';
                if (is_readable($langfile)) {
                    if ($result = get_string_from_file($identifier, $langfile)) {
                        return format_langstring($result, $variables);
                    }
                }
            }
        }
    }

    /// Our only remaining option is to try English
    foreach ($locations as $location) {
        //if local_en not found, or string not found in local_en
        $langfile = $location . 'en.utf8/' . $section . '.php';
        if (is_readable($langfile)) {
            if ($result = get_string_from_file($identifier, $langfile)) {
                return format_langstring($result, $variables);
            }
        }
    }

    return '[[' . $identifier . ']]';  // Last resort
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
    catch (SQLException $e) {
        return false;
    }
    
    foreach ($dbconfig as $cfg) {
        if (isset($CFG->{$cfg->field}) && $CFG->{$cfg->field} != $cfg->value) {
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
    global $CFG;

    if (get_record('config', 'field', $key)) {
        if (set_field('config', 'value', $value, 'field', $key)) {
            $status = true;
        }
    } 
    else {
        $config = new StdClass;
        $config->field = $key;
        $config->value = $value;
        $status = insert_record('config', $config);
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
    if (!$value = get_field($plugintype . '_config', 'value', 'plugin', $pluginname, 'field', $key)) {
        $value = null;
    }

    $CFG->plugin->{$plugintype}->{$pluginname}->{$key} = $value;
    return $value;
}

function set_config_plugin($plugintype, $pluginname, $key, $value) {
    global $CFG;
    $table = $plugintype . '_config';

    if (false !== get_field($table, 'value', 'plugin', $pluginname, 'field', $key)) {
        //if (set_field($table, 'value', $key, 'plugin', $pluginname, 'field', $value)) {
        if (set_field($table, 'value', $value, 'plugin', $pluginname, 'field', $key)) {
            $status = true;
        }
    }
    else {
        $pconfig = new StdClass;
        $pconfig->plugin = $pluginname;
        $pconfig->field  = $key;
        $pconfig->value  = $value;
        $status = insert_record($table, $pconfig);
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
 *
 * Check whether to use the wysiwyg html editor or a plain textarea.
 * @todo check user setting from db and browser capability
 *
 */
function use_html_editor() {
    return true;
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

/**
 * Checks that a username is in valid form
 *
 * @todo need such a function for password too.
 */
function validate_username($username) {
    return preg_match('/^[a-zA-Z0-9_\.@]+$/', $username);
}

/**
 * Function to require a plugin file. This is to avoid doing 
 * require and include directly with variables.
 * This function is the one safe point to require plugin files.
 * so USE it :)
 * @param string $plugintype the type of plugin (eg artefact)
 * @param string $pluginname the name of the plugin (eg blog)
 * @param string $filename the name of the file to include within the plugin structure
 * @param string $function (optional, defaults to require) the require/include function to use
 * @param string $nonfatal (optional, defaults to false) just returns false if the file doesn't exist
 */
function safe_require($plugintype, $pluginname, $filename='lib.php', $function='require', $nonfatal=false) {

    $plugintype = clean_filename($plugintype);
    $pluginname = clean_filename($pluginname);

    if (!in_array($function,array('require','include','require_once','include_once'))) {
        if (!empty($nonfatal)) {
            return false;
        }
        throw new Exception ('invalid require type');
    }

    $fullpath = get_config('docroot') . $plugintype . '/' . $pluginname . '/' . $filename;
    if (!$realpath = realpath($fullpath)) {
        if (!empty($nonfatal)) {
            return false;
        }
        throw new Exception ("File $fullpath did not exist");
    }

    if (strpos($realpath, get_config('docroot') !== 0)) {
        if (!empty($nonfatal)) {
            return false;
        }
        throw new Exception ("File $fullpath was outside document root!");
    }

    if ($function == 'require') { return require($realpath); }
    if ($function == 'include') { return include($realpath); }
    if ($function == 'require_once') { return require_once($realpath); }
    if ($function == 'include_once') { return include_once($realpath); }
    
}


/**
 * Returns the list of site content pages
 * @return array of names
 */
function site_content_pages() {
    return array('about','home','loggedouthome','privacy','termsandconditions','uploadcopyright');
}

/**
 * This function returns the list of plugintypes we currently care about
 * @return array of names
 */
function plugin_types() {
    static $pluginstocheck;
    if (empty($pluginstocheck)) {
        $pluginstocheck = array('artefact', 'auth', 'notification');
    }
    return $pluginstocheck;
}

/**
 * Helper to call a static method when you do not know the name of the class
 * you want to call the method on. PHP5 does not support $class::method().
 */
function call_static_method($class, $method) {
    $args = func_get_args();
    array_shift($args);
    array_shift($args);
    return call_user_func_array(array($class, $method), $args);
}

function generate_class_name() {
    $args = func_get_args();
    return 'Plugin' . implode('', array_map('ucfirst', $args));
}

function redirect($location) {
    if (headers_sent()) {
        throw new Exception('Headers already sent when redirect() was called');
    }
    header('HTTP/1.1 303 See Other');
    header('Location:' . $location);
    exit;
}

function handle_event($event) {
    if (!$e = get_record('event_type','name',$event)) {
        throw new Exception("Invalid event");
    }
    $plugintypes = plugin_types();
    foreach ($plugintypes as $name) {
        if ($subs = get_records('event_subscription_' . $name, 'event', $event)) {
            foreach ($subs as $sub) {
                $classname = 'Plugin' . ucfirst($name) . ucfirst($sub->plugin);
                try {
                    call_static_method($classname, $sub->callfunction);
                }
                catch (Exception $e) {
                    log_warn("Event $event caused an exception from plugin $classname "
                             . "with function $sub->callfunction. Continuing with event handlers");
                }
            }
        }
    }
}

/**
 * Returns a string, HTML escaped
 *
 * @param string $text The text to escape
 * @return string      The text, HTML escaped
 */
function hsc ($text) {
    return htmlspecialchars($text, ENT_COMPAT, 'UTF-8');
}

/**
 * function to convert an array of objects to 
 * an array containing one field per place
 * 
 * @param array $array input array
 * @param mixed $field field to look for in each object
 */
function mixed_array_to_field_array($array, $field) {
    $repl_fun = create_function('$n, $field', '$n = (object)$n; return $n->{$field};');
    $fields = array_pad(array(), count($array), $field);
    return array_map($repl_fun, $array, $fields);
}


/** 
 * Adds stuff to the log
 * @todo write this functino
 *
 * @param string $plugintype plugin type or core
 * @param string $pluginname plugin name or core component (eg 'view')
 * @param string $action action string (like 'add')
 * @param int $user id of user making the action
 * @param int $id relevant id (ie, profile view would have id of profile owner)
 * 
 */
function add_to_log($plugintype, $pluginname, $action, $user, $id=0) {

}

/**
 * Used by XMLDB
 */
function debugging ($message, $level) {
    log_debug($message);
}
function xmldb_dbg($message) {
    log_warn($message);
}
define('DEBUG_DEVELOPER', 'whocares');

/** 
 * Base class for all plugintypes.
 */
class Plugin {
    
    public static function get_cron() {
        return array();
    }

    public static function get_event_subscriptions() {
        return array();
    }

    public static function postinst() {
        return true;
    }
}

/**
 * Builds the main navigation menu and returns it as a data structure
 *
 * @return $mainnav a data structure containing the main navigation
 * @todo martyn this is probably quite expenvise, perhaps it needs teh caching
 */
function main_nav() {
    $wwwroot = get_config('wwwroot');

    $menu = array(
        array(
            'name'     => 'home',
            'section'  => 'mahara',
            'link'     => $wwwroot,
        ),
    );

    if ($plugins = get_rows('artefact_installed')) {
        foreach ($plugins as &$plugin) {
            safe_require('artefact', $plugin['name'], 'lib.php', 'require_once');
            $plugin_menu = call_static_method(generate_class_name('artefact',$plugin['name']), 'menu_items');

            foreach ($plugin_menu as &$menu_item) {
                $menu_item['link'] = $wwwroot . 'artefact/' . $plugin['name'] . '/' . $menu_item['link'];
                $menu_item['section'] = 'artefact.' . $plugin['name'];
            }

            $menu = array_merge($menu, $plugin_menu);
        }
    }

    $menu[] = array(
        'name'    => 'mycontacts',
        'link'    => $wwwroot . 'contacts/',
        'section' => 'mahara',
        'submenu' => array(
            'myfriends' => array(
                'name'    => 'myfriends',
                'link'    => $wwwroot . 'contacts/',
                'section' => 'mahara',
            ),
            'myaddressbook' => array(
                'name'    => 'myaddressbook',
                'link'    => $wwwroot . 'contacts/addressbook/',
                'section' => 'mahara',
            ),
            'mycommunities' => array(
                'name'    => 'mycommunities',
                'link'    => $wwwroot . 'contacts/communities/',
                'section' => 'mahara',
            ),
            'myownedcommunities' => array(
                'name'    => 'myownedcommunities',
                'link'    => $wwwroot . 'contacts/communities/owned.php',
                'section' => 'mahara',
            ),
            'mygroups' => array(
                'name'    => 'mygroups',
                'link'    => $wwwroot . 'contacts/groups/',
                'section' => 'mahara',
            ),
        ),
    );


    if (defined('MENUITEM')) {
        foreach ( $menu as &$item ) {
            if ($item['name'] == MENUITEM) {
                $item['selected'] = true;
                if (defined('SUBMENUITEM') and is_array($item['submenu'])) {
                    foreach ( $item['submenu'] as &$subitem ) {
                        if ($subitem['name'] == SUBMENUITEM) {
                            $subitem['selected'] = true;
                        }
                    }
                }
            }
        }
    }
    else {
        $menu[0]['selected'] = true;
    }

    return $menu;
}

?>
