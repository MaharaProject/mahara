<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
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
    $phpversionrequired = '7.0.0';
    if (version_compare(phpversion(), $phpversionrequired) < 0) {
        throw new ConfigSanityException(get_string('phpversion', 'error', $phpversionrequired));
    }

    // Various required extensions
    if (!extension_loaded('json')) {
        throw new ConfigSanityException(get_string('jsonextensionnotloaded', 'error'));
    }
    switch (get_config('dbtype')) {
    case 'postgres':
    case 'postgres8': // for legacy purposes we also accept "postgres8"
        if (!extension_loaded('pgsql')) {
            throw new ConfigSanityException(get_string('pgsqldbextensionnotloaded', 'error'));
        }
        break;
    case 'mysql':
    case 'mysql5': // for legacy purposes we also accept "mysql5"
        if (!extension_loaded('mysqli') && !extension_loaded('mysql')) {
            throw new ConfigSanityException(get_string('mysqldbextensionnotloaded', 'error'));
        }
        break;
    default:
        throw new ConfigSanityException(get_string('unknowndbtype', 'error'));
    }
    if (!extension_loaded('xml')) {
        throw new ConfigSanityException(get_string('xmlextensionnotloaded', 'error', 'xml'));
    }
    if (!extension_loaded('libxml')) {
        throw new ConfigSanityException(get_string('xmlextensionnotloaded', 'error', 'libxml'));
    }
    if (!extension_loaded('gd')) {
        throw new ConfigSanityException(get_string('gdextensionnotloaded', 'error'));
    }
    if (!extension_loaded('session')) {
        throw new ConfigSanityException(get_string('sessionextensionnotloaded', 'error'));
    }

    if(!extension_loaded('curl')) {
        throw new ConfigSanityException(get_string('curllibrarynotinstalled', 'error'));
    }
    if (!extension_loaded('dom')) {
        throw new ConfigSanityException(get_string('domextensionnotloaded', 'error'));
    }
    if (!extension_loaded('mbstring')) {
        throw new ConfigSanityException(get_string('mbstringextensionnotloaded', 'error'));
    }
    // Check for freetype in the gd extension
    $gd_info = gd_info();
    if (!$gd_info['FreeType Support']) {
        throw new ConfigSanityException(get_string('gdfreetypenotloaded', 'error'));
    }

    // register globals workaround
    if (ini_get_bool('register_globals')) {
        $massivearray = array_keys(array_merge($_POST, $_GET, $_COOKIE, $_SERVER, $_REQUEST, $_FILES));
        foreach ($massivearray as $tounset) {
            unset($GLOBALS[$tounset]);
        }
    }

    // magic_quotes_gpc workaround
    if (!defined('CRON') && ini_get_bool('magic_quotes_gpc')) {
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
    }

    if (ini_get_bool('magic_quotes_sybase')) {
        // See above comment re. magic_quotes_runtime
        @ini_set('magic_quotes_sybase', 0);
    }

    if (ini_get_bool('safe_mode')) {
        // We don't run with safe mode
        throw new ConfigSanityException(get_string('safemodeon', 'error'));
    }

    if ('0' === ini_get('apc.stat') or 'off' === ini_get('apc.stat')) {
        // We don't run with apc.stat=0 (see https://bugs.launchpad.net/mahara/+bug/548333)
        throw new ConfigSanityException(get_string('apcstatoff', 'error'));
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
        $message = get_string('datarootnotwritable', 'error', get_config('dataroot'));
        if ($openbasedir = ini_get('open_basedir')) {
            $message .= "\n(" . get_string('openbasedirenabled', 'error') . ' '
                . get_string('openbasedirpaths', 'error', htmlspecialchars($openbasedir)) // hsc() is not defined yet
                . ')';
        }
        throw new ConfigSanityException($message);
    }
    $dwoo_dir = get_dwoo_dir();
    if (
        !check_dir_exists($dwoo_dir) ||
        !check_dir_exists(get_config('dataroot') . 'temp') ||
        !check_dir_exists(get_config('dataroot') . 'langpacks') ||
        !check_dir_exists(get_config('dataroot') . 'htmlpurifier') ||
        !check_dir_exists(get_config('dataroot') . 'log') ||
        !check_dir_exists(get_config('dataroot') . 'images')) {
        throw new ConfigSanityException(get_string('couldnotmakedatadirectories', 'error'));
    }
    // Since sessionpath can now exist outside of the the dataroot, check it separately.
    // NOTE: If we implement separate session handlers, we may want to remove or alter this check
    if (!check_dir_exists(get_config('sessionpath')) || !is_writable(get_config('sessionpath'))) {
        throw new ConfigSanityException(get_string('sessionpathnotwritable', 'error', get_config('sessionpath')));
    }

    raise_memory_limit('128M');
}

function get_dwoo_dir() {
    return !empty(get_config('customdwoocachedir')) ? get_config('customdwoocachedir') . '/dwoo/' : get_config('dataroot') . 'dwoo/';
}

/**
 * Check sanity of things that we only check at installation time - not on
 * every request, like ensure_sanity() does
 */
function ensure_install_sanity() {
    // Must must must be a UTF8 database!
    if (!db_is_utf8()) {
        throw new ConfigSanityException(get_string('dbnotutf8', 'error'));
    }
    if (is_postgres() && !postgres_create_language('plpgsql')) {
        throw new ConfigSanityException(get_string('plpgsqlnotavailable', 'error'));
    }
    if (is_mysql() && !mysql_has_trigger_privilege()) {
        throw new ConfigSanityException(get_string('mysqlnotriggerprivilege', 'error'));
    }
    if (!file_exists(get_config('docroot') . 'theme/raw/style/style.css')) {
        $e = new ConfigSanityException(get_string('cssnotpresent', 'error'));
        $e->set_log_off();
        throw $e;
    }
}

function ensure_upgrade_sanity() {
    // Check column collation is equal to the default
    if (is_mysql()) {
        require_once('ddl.php');
        if (table_exists(new XMLDBTable('event_type'))) {
            if (!column_collation_is_default('event_type', 'name')) {
                throw new ConfigSanityException(get_string('dbcollationmismatch', 'admin'));
            }
        }
        if (!mysql_has_trigger_privilege()) {
            throw new ConfigSanityException(get_string('mysqlnotriggerprivilege', 'error'));
        }
    }
    if (is_postgres() && !postgres_create_language('plpgsql')) {
        throw new ConfigSanityException(get_string('plpgsqlnotavailable', 'error'));
    }
    if (!file_exists(get_config('docroot') . 'theme/raw/style/style.css')) {
        $e = new ConfigSanityException(get_string('cssnotpresent', 'error'));
        $e->set_log_off();
        throw $e;
    }
    if (!record_exists('usr', 'id', 0)) {
        throw new ConfigSanityException(get_string('mahararootusermissing', 'error'));
    }
}

/**
 * Upgrade/Install the specified mahara components
 * (Only used by the CLI installer & upgrader)
 *
 * @param array $upgrades The list of components to install or upgrade
 * @return void
 */
function upgrade_mahara($upgrades) {
    if (isset($upgrades['firstcoredata']) && $upgrades['firstcoredata']) {
        $install = true;
    }
    else {
        $install = false;
    }
    uksort($upgrades, 'sort_upgrades');
    foreach ($upgrades as $name => $data) {
        if ($name == 'settings') {
            continue;
        }
        if ($install) {
            log_info(get_string('installingplugin', 'admin', $name));
        }
        else {
            log_info(get_string('upgradingplugin', 'admin', $name));
        }
        if ($name == 'firstcoredata' || $name == 'lastcoredata') {
            $funname = 'core_install_' . $name . '_defaults';
            $funname();
            continue;
        }
        else if ($install && $name == 'localpreinst') {
            $name(array('localdata' => true));
        }
        else if ($install && $name == 'localpostinst') {
            // Update local version
            $name(array('localdata' => true));

            $config = new stdClass();
            require(get_config('docroot') . 'local/version.php');
            set_config('localversion', $config->version);
            set_config('localrelease', $config->release);

            // Installation is finished
            set_config('installed', true);
            log_info('Installation complete.');
        }
        else {
            if ($name == 'core') {
                $funname = 'upgrade_core';
            }
            else if ($name == 'local') {
                $funname = 'upgrade_local';
            }
            else {
                $funname = 'upgrade_plugin';
            }
            $data->name = $name;
            $funname($data);
        }
    }
}

/**
 * Check to see if the internal plugins are installed. Die if they are not.
 */
function ensure_internal_plugins_exist() {
    // Internal things installed
    if (get_config('installed')) {
        foreach (plugin_types() as $type) {
            if (!record_exists($type . '_installed', 'name', 'internal')) {
                throw new ConfigSanityException(get_string($type . 'notinstalled'));
            }
        }
    }
}

/**
 * Check to see whether a language string is present in the
 * lang files. when checking the lang string we don't care about extra arguments so we use
 * the _return_string_unchanged rather than format_langstring function.
 *
 * @param string $identifier
 * @param string $section
 * @return boolean
 */
function string_exists($identifier, $section = 'mahara') {
    // Because we don't need to perform parameter replacement to test if string exists
    // we can use a simple pass-through for the $replacefunc argument.
    $string = get_string_location($identifier, $section, array(), '_return_string_unchanged');
    return $string !== '[[' . $identifier . '/' . $section . ']]';
}

function _return_string_unchanged($string, $args=array(), $lang='en.utf8') {
    return $string;
}

/**
 * Find out the number of expected arguments if $identifier for get_string() is
 * passed in by a variable. Eg in webservices error handling.
 */
function count_string_args($identifier, $section = 'mahara') {
    $string = get_string_location($identifier, $section, array(), '_return_string_unchanged');
    preg_match_all('/[^\%]\%[^\%]/', $string, $matches);
    return count($matches[0]);
}

function get_string($identifier, $section='mahara') {

    $variables = func_get_args();
    if (count($variables) > 2) { // we have some stuff we need to sprintf
        array_shift($variables);
        array_shift($variables); //shift off the first two.
    }
    else {
        $variables = array();
    }

    return get_string_location($identifier, $section, $variables);
}

function get_string_php_version($identifier, $section = 'mahara') {
    if (version_compare(PHP_VERSION, '7.0.0') >= 0
        && string_exists($identifier . '7php', $section)) {
        return get_string($identifier . '7php', $section);
    }
    else {
        return get_string($identifier, $section);
    }
}

function get_string_from_language($lang, $identifier, $section='mahara') {

    $variables = func_get_args();
    if (count($variables) > 3) { // we have some stuff we need to sprintf
        array_shift($variables);
        array_shift($variables);
        array_shift($variables); //shift off the first three.
    }
    else {
        $variables = array();
    }

    return get_string_location($identifier, $section, $variables, 'format_langstring', $lang);
}

function get_helpfile($plugintype, $pluginname, $form, $element, $page=null, $section=null) {
    if ($langfile = get_helpfile_location($plugintype, $pluginname, $form, $element, $page, $section)) {
        return file_get_contents($langfile);
    }
    return false;
}

function get_helpfile_location($plugintype, $pluginname, $form, $element, $page=null, $section=null) {

    $subdir = 'help/';

    if ($page) {
        $pagebits = explode('-', $page);
        $file = array_pop($pagebits) . '.html';
        if ($plugintype != 'core') {
            $subdir .= 'pages/' . join('/', $pagebits) . '/';
        }
        else {
            $subdir .= 'pages/' . $pluginname . '/' . join('/', $pagebits) . '/';
        }
    }
    else if ($section) {
        $subdir .= 'sections/';
        $file = $section . '.html';
    }
    else if (!empty($form) && !empty($element)) {
        $subdir .= 'forms/';
        $file = $form . '.' . $element . '.html';
    }
    else if (!empty($form) && empty($element)) {
        $subdir .= 'forms/';
        $file = $form . '.html';
    }
    else {
        return false;
    }

    // now we have to try and locate the help file
    $lang = current_language();
    if ($lang == 'en.utf8') {
        $trieden = true;
    }
    else {
        $trieden = false;
    }

    //try the local settings
    $langfile = get_config('docroot') . 'local/lang/' . $lang . '/' . $subdir;
    if ($plugintype != 'core') {
        $langfile .= $plugintype . '.' . $pluginname . '.' . $file;
    }
    else {
        $langfile .= $file;
    }
    if (is_readable($langfile)) {
        return $langfile;
    }

    if ($plugintype == 'blocktype') { // these are a bit of a special case
        $bits = explode('/', $pluginname);
        if (count($bits) == 2) {
           $location = 'artefact/' . $bits[0] . '/blocktype/' . $bits[1] . '/lang/';
        }
        else {
            try {
                if ($artefactplugin = blocktype_artefactplugin($pluginname)) {
                    $location = 'artefact/' . $artefactplugin . '/blocktype/' . $pluginname . '/lang/';
                }
                else {
                    $location = 'blocktype/' . $pluginname . '/lang/';
                }
            }
            catch (SQLException $e) {
                if (get_config('installed')) {
                    throw $e;
                }
            }
        }
    }
    else if ($plugintype != 'core') {
        $location = $plugintype . '/' . $pluginname . '/lang/';
    }
    else {
        $location = 'lang/';
    }

    // try the current language
    $langfile = get_language_root() . $location . $lang . '/' . $subdir . $file;
    if (is_readable($langfile)) {
        return $langfile;
    }

    // if it's not found, try the parent language if there is one...
    if (empty($data) && empty($trieden)) {
        $langfile = get_language_root($lang) . 'lang/' . $lang . '/langconfig.php';
        if ($parentlang = get_string_from_file('parentlanguage', $langfile)) {
            if ($parentlang == 'en.utf8') {
                $trieden = true;
            }
            $langfile = get_language_root($parentlang) . $location . $parentlang . '/' . $subdir . $file;
            if (is_readable($langfile)) {
                return $langfile;
            }
        }
    }

    // if it's STILL not found, and we haven't already tried english ...
    if (empty($data) && empty($trieden)) {
        $langfile = get_language_root('en.utf8') . $location . 'en.utf8/' . $subdir . $file;
        if (is_readable($langfile)) {
            return $langfile;
        }
    }

    // if it's a form element, try the wildcard form name
    if (!empty($form) && !empty($element) && $form !== 'ANY') {
        // if it's a block instance config form element, try the wildcard form name
        // and element without it's prefixes
        if (preg_match('/^instconf_/', $element)) {
            $element_explode = explode('_', $element);
            $element = end($element_explode);
        }
        return get_helpfile_location('core', '', 'ANY', $element, $page, $section);
    }

    return false;
}

// get a string without sprintfing it.
function get_raw_string($identifier, $section='mahara') {
    // For a raw string we don't want to format any arguments using
    // sprintf, so the replace function passed to get_string_location
    // should just return the first argument and ignore the second.
    return get_string_location($identifier, $section, array(), 'raw_langstring');
}


/**
 * This function gets a language string identified by $identifier from
 * an appropriate location, and formats the string and any arguments
 * in $variables using the function $replacefunc.
 *
 * @param string   $identifier
 * @param string   $section
 * @param array    $variables
 * @param function $replacefunc
 * @return string
 */
function get_string_location($identifier, $section, $variables, $replacefunc='format_langstring', $lang='') {

    $langconfigstrs = array('parentlanguage', 'thislanguage');

    if ($section == 'mahara' &&
        (in_array($identifier, $langconfigstrs)
         || strpos($identifier, 'strftime') === 0
         || strpos($identifier, 'strfday')  === 0)) {
        $section = 'langconfig';
    }

    if (empty($lang)) {
        $lang = current_language();
    }

    // Define the locations of language strings for this section
    $langstringroot = get_language_root($lang);
    $docroot = get_config('docroot');

    $langdirectory  = ''; // The directory in which the language file for this string should ideally reside, if the language has implemented it

    if (false === strpos($section, '.')) {
        $langdirectory = 'lang/';
    }
    else {
        $extras = plugin_types();
        $extras[] = 'theme'; // Allow themes to have lang files the same as plugins
        foreach ($extras as $tocheck) {
            if (strpos($section, $tocheck . '.') === 0) {
                $pluginname = substr($section ,strlen($tocheck) + 1);
                if ($tocheck == 'blocktype' &&
                    strpos($pluginname, '/') !== false) { // it belongs to an artefact plugin
                    $bits = explode('/', $pluginname);
                    $langdirectory = 'artefact/' . $bits[0] . '/blocktype/' . $bits[1] . '/lang/';
                    $section = 'blocktype.' . $bits[1];
                }
                else {
                    $langdirectory = $tocheck . '/' . $pluginname . '/lang/';
                }
            }
        }
    }

    // First check the theme/plugin locations
    $result = get_string_local($docroot . $langdirectory, $lang . '/' . $section . '.php', $identifier);
    if ($result !== false) {
        return $replacefunc($result, $variables, $lang);
    }

    // Then check the default location for the string in the current language
    $result = get_string_local($langstringroot . $langdirectory, $lang . '/' . $section . '.php', $identifier);
    if ($result !== false) {
        return $replacefunc($result, $variables, $lang);
    }

    // If the preferred language was English (utf8) we can abort now
    // saving some checks beacuse it's the only "root" lang
    if ($lang == 'en.utf8') {
        return '[[' . $identifier . '/' . $section . ']]';
    }

    // Is a parent language defined?  If so, try to find this string in a parent language file
    $langfile = $langstringroot . 'lang/' . $lang . '/langconfig.php';
    if (is_readable($langfile)) {
        if ($parentlang = get_string_from_file('parentlanguage', $langfile)) {

            // First check the theme/plugin locations
            $result = get_string_local($docroot . $langdirectory, $parentlang . '/' . $section . '.php', $identifier);
            if ($result !== false) {
                return $replacefunc($result, $variables, $parentlang);
            }

            // Then check the default location for the string in the current language
            $result = get_string_local(get_language_root($parentlang) . $langdirectory, $parentlang . '/' . $section . '.php', $identifier);
            if ($result !== false) {
                return $replacefunc($result, $variables, $parentlang);
            }
        }
    }

    /// Our only remaining option is to try English
    $result = get_string_local(get_config('docroot') . $langdirectory, 'en.utf8/' . $section . '.php', $identifier);
    if ($result !== false) {
        return $replacefunc($result, $variables);
    }

    return '[[' . $identifier . '/' . $section . ']]';  // Last resort
}


/**
 * Get string from a file, checking the appropriate local customisation directory first
 *
 */
function get_string_local($langpath, $langfile, $identifier) {
    foreach (array(get_config('docroot') . 'local/lang/', $langpath) as $dir) {
        $file = $dir . $langfile;
        if (is_readable($file)) {
            $result = get_string_from_file($identifier, $file);
            if ($result !== false) {
                return $result;
            }
        }
    }
    return false;
}


/**
 * Return a list of available languages
 *
 */
function get_languages() {
    static $langs = array();

    if (!$langs) {
        foreach (language_get_searchpaths() as $searchpath) {
            $langbase = $searchpath . 'lang/';
            if ($langdir = @opendir($langbase)) {
                while (false !== ($subdir = readdir($langdir))) {
                    if (preg_match('/\.utf8$/', $subdir) && is_dir($langbase . $subdir)) {
                        $langfile = $langbase . $subdir . '/langconfig.php';
                        if (is_readable($langfile)) {
                            if ($langname = get_string_from_file('thislanguage', $langfile)) {
                                $langs[$subdir] = $langname;
                            }
                        }
                    }
                }
                closedir($langdir);
                asort($langs);
            }
            else {
                log_warn('Unable to read language directory ' . $langbase);
            }
        }
    }

    return $langs;
}

/**
 * Returns whether the given language is installed/available for use
 */
function language_installed($lang) {
    foreach (language_get_searchpaths() as $searchpath) {
        if (is_readable($searchpath . 'lang/' . $lang . '/langconfig.php')) {
            return true;
        }
    }
    return false;
}

/**
 * Returns a list of directories in which to search for language packs.
 *
 * This is influenced by the configuration variable 'langpacksearchpaths'
 */
function language_get_searchpaths() {
    static $searchpaths = array();

    if (!$searchpaths) {
        // Construct the search path
        $docrootpath = array(get_config('docroot'));

        // Paths to language files in dataroot
        $datarootbase = get_config('dataroot') . 'langpacks/*';
        $datarootpaths = glob($datarootbase, GLOB_MARK | GLOB_ONLYDIR);
        if ($datarootpaths === false) {
            log_warn("Problem searching for langfiles at this path: " . $datarootbase);
            $datarootpaths = array();
        }

        // langpacksearchpaths configuration variable - for experts :)
        $lpsearchpaths = (array)get_config('langpacksearchpaths');
        $langpacksearchpaths = array();
        foreach ($lpsearchpaths as $path) {
            if (is_dir($path)) {
                $langpacksearchpaths[] = (substr($path, -1) == '/') ? $path : "$path/";
            }
            else {
                log_warn("Path in langpacksearchpaths is not a directory: $path");
            }
        }

        $searchpaths = array_merge($docrootpath, $datarootpaths, $langpacksearchpaths);
    }

    return $searchpaths;
}

/**
 * Get the directory in which the specified language pack resides.
 *
 * Defaults to getting the directory for the current_language() - i.e. the
 * language the user is using
 *
 * Returns null if the language can't be found
 *
 * @param string $language The language to look for
 */
function get_language_root($language=null) {
    static $language_root_cache = array();

    if (!isset($language_root_cache[$language])) {
        if ($language == null) {
            $language = current_language();
        }

        foreach (language_get_searchpaths() as $path) {
            if (is_dir("$path/lang/$language")) {
                return $language_root_cache[$language] = $path;
            }
        }

        // Oh noes, can't be found
        $language_root_cache[$language] = null;

    }

    return $language_root_cache[$language];
}

/**
 * Return a list of all available themes.
 * @return array subdir => name
 */
function get_all_themes() {
    static $themes = null;

    if (is_null($themes)) {
        $themes = array();
        $themelist = get_all_theme_objects();

        foreach ($themelist AS $subdir => $theme) {
            $themes[$subdir] = isset($theme->displayname) ? $theme->displayname : $subdir;
        }
    }

    return $themes;
}

/**
 * Return a list of themes available to this user
 * If the user is a member of any institutions, only themes available to
 * those institutions are returned; or
 * If a user is not a member of any institution, all themes not marked as
 * institution specific are returned.
 * @return array subdir => name
 */
function get_user_accessible_themes() {
    global $USER;

    $themes = array();
    if ($institutions = $USER->get('institutions')) {
        // Get themes for all of this users institutions
        foreach ($institutions AS $i) {
            $themes = array_merge($themes, get_institution_themes($i->institution));
        }
    }
    else {
        $themelist = get_all_theme_objects();
        foreach ($themelist AS $subdir => $theme) {
            if (!isset($theme->institutions) || !is_array($theme->institutions)) {
                $themes[$subdir] = isset($theme->displayname) ? $theme->displayname : $subdir;
            }
        }

    }
    $themes = array_merge(array('sitedefault' => get_string('nothemeselected1', 'view')), $themes);
    unset($themes['custom']);

    return $themes;
}

/**
 * Return a list of themes available to the specified institution
 * @param string institution the name of the institution to load themes for
 * @return array subdir => name
 * @throws SystemException if unable to read the theme directory
 */
function get_institution_themes($institution) {
    static $institutionthemes = array();
    if (!isset($institutionthemes[$institution])) {
        $themes = get_all_theme_objects();
        $r = array();
        foreach ($themes AS $subdir => $theme) {
            if (empty($theme->institutions) || !is_array($theme->institutions) || in_array($institution, $theme->institutions)) {
                $r[$subdir] = isset($theme->displayname) ? $theme->displayname : $subdir;
            }
        }
        $institutionthemes[$institution] = $r;
    }

    return $institutionthemes[$institution];
}

/**
 * Return a list of all themes available on the system
 * @return array An array of theme objects
 * @throws SystemException if unable to read the theme directory
 */
function get_all_theme_objects() {
    static $themes = null;

    if (is_null($themes)) {
        $themes = array();
        $themebase = get_config('docroot') . 'theme/';
        if (!$themedir = opendir($themebase)) {
            throw new SystemException('Unable to read theme directory '.$themebase);
        }
        while (false !== ($subdir = readdir($themedir))) {
            if (substr($subdir, 0, 1) != "." && is_dir($themebase . $subdir)) {
                // is the theme directory name valid?
                if (!Theme::name_is_valid($subdir)) {
                    log_warn(get_string('themenameinvalid', 'error', $subdir));
                } else {
                    $config_path = $themebase . $subdir . '/themeconfig.php';
                    if (is_readable($config_path)) {
                        require($config_path);
                        if (empty($theme->disabled) || !$theme->disabled) {
                            // don't include the special subthemestarter theme in the options
                            if ($subdir != 'subthemestarter') {
                                $themes[$subdir] = $theme;
                            }
                        }
                    }
                }
            }
        }
        closedir($themedir);
        asort($themes);
    }

    return $themes;
}

/**
 * Checks if theme still exists and if not resets it to default option
 *
 * @param $theme         string  Name of theme
 * @param $institution   string  Name of Institution
 * @param $new           boolean If we are validating the theme for something newly created
 *
 * @return  bool       True if theme exists
 */
function validate_theme($theme, $institution = null, $new = false) {
    global $SESSION;

    // Null theme means the institution is using the site default.
    if ($theme == null && ($institution || $new)) {
        return true;
    }

    if ($institution) {
        $themeoptions = get_institution_themes($institution);
    }
    else {
        $themeoptions = get_all_themes();
    }
    if (!array_key_exists($theme, $themeoptions)) {
        if ($new) {
            return false;
        }
        else {
            if ($institution) {
                set_config_institution($institution, 'theme', null);
            }
            else {
                set_config('theme', 'default');
            }
            $SESSION->add_info_msg(get_string('thememissing', 'admin', $theme));
            return false;
        }
    }

    // validate parent theme
    if (($themeconfig = Theme::get_theme_config($theme)) && $themeconfig->parent !== false) {

          $parentthemename = $themeconfig->parent;
          $parentthemeconfig = Theme::get_theme_config($parentthemename);

          if (!$parentthemeconfig) {
            if ($institution) {
                set_config_institution($institution, 'theme', null);
            }
            else {
                set_config('theme', 'default');
            }
            $SESSION->add_info_msg(get_string('parentthememissing', 'admin', $themeconfig->displayname, $parentthemename));
            return false;

          }
      }

    return true;
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
   global $OVERRIDDEN;    // array containing the config fields overridden by $CFG

   // Get a full list of overridden fields
   foreach ($CFG as $field => $value) {
        $OVERRIDDEN[] = $field;
   }

   $dbconfig = get_records_array('config', '', '', '', 'field, value');
   foreach ($dbconfig as $cfg) {
       if (!isset($CFG->{$cfg->field})) {
           $CFG->{$cfg->field} = $cfg->value;
       }
   }

   return true;
}

/**
 * This function returns a value from $CFG
 * or default value if supplied or null if it is not found
 *
 * @param string $key      Config setting to look for
 * @param string $default  Default value to return if setting not found
 * @return mixed
 */
function get_config($key, $default = null) {
    global $CFG;
    if (isset($CFG->$key)) {
        return $CFG->$key;
    }
    return $default;
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
    $value = (string) $value;

    db_ignore_sql_exceptions(true);
    $dbvalue = get_field('config', 'value', 'field', $key);
    if (false !== $dbvalue) {
        if (
                // No need to update the DB if the value already matches
                ($dbvalue === $value)
                || set_field('config', 'value', $value, 'field', $key)
        ) {
            $status = true;
        }
    }
    else {
        $config = new stdClass();
        $config->field = $key;
        $config->value = $value;
        $status = insert_record('config', $config);
    }
    db_ignore_sql_exceptions(false);

    if (!empty($status)) {
        $CFG->{$key} = $value;
        return true;
    }

    return false;
}

/**
 * This function returns a value for $CFG for a plugin
 * or null if it is not found.
 *
 * It will give precedence to config values set in config.php like so:
 * $cfg->plugin_{$plugintype}_{$pluginname}_{$key} = 'whatever';
 *
 * If it doesn't find one of those, it will look for the config value in
 * the database.
 *
 * @param string $plugintype eg artefact
 * @param string $pluginname eg blog
 * @param string $key the config setting to look for
 * @return mixed The value of the key if found, or NULL if not found
 */
function get_config_plugin($plugintype, $pluginname, $key) {
    global $CFG;
    static $pluginsfetched = array();

    $typename = "{$plugintype}_{$pluginname}";
    $configname = "plugin_{$typename}_{$key}";
    if (isset($CFG->{$configname})) {
        return $CFG->{$configname};
    }
    else if (isset($CFG->plugin->{$plugintype}->{$pluginname}->{$key})) {
        log_warn(
            "Mahara 1.9-format plugin config detected in your config.php: \$cfg->plugin->{$plugintype}->{$pluginname}->{$key}."
            . " You should change this to the Mahara 1.10 format: \$cfg->plugin_{$plugintype}_{$pluginname}_{$key}."
        );
        return $CFG->plugin->{$plugintype}->{$pluginname}->{$key};
    }
    // If we have already fetched this plugin's data from the database, then return NULL.
    // (Note that some values may come from config.php before we hit the database.)
    else if (in_array($typename, $pluginsfetched)) {
        return null;
    }
    // We haven't fetched this plugin's data yet. So do it!
    else {

        // To minimize database calls, get all the records for this plugin from the database at once.
        try {
            $records = get_records_array($plugintype . '_config', 'plugin', $pluginname, 'field');
        }
        catch (SQLException $e) {
            // Db might not exist yet on install
            return null;
        }
        if (!empty($records)) {
            foreach ($records as $record) {
                $storeconfigname = "plugin_{$typename}_{$record->field}";
                if (!isset($CFG->{$storeconfigname})) {
                    $CFG->{$storeconfigname} = $record->value;
                }
            }
        }

        // Make a note that we've now hit the database over this one.
        $pluginsfetched[] = $typename;

        // Now, return it if we found it, otherwise null.
        // (This could be done by a recursive call to get_config_plugin(), but it's
        // less error-prone to do it this way and it doesn't cause that much duplication)
        if (isset($CFG->{$configname})) {
            return $CFG->{$configname};
        }
        else {
            return null;
        }
    }
}

/**
 * Set or update a plugin config value.
 *
 * @param string $plugintype The plugin type: 'artefact', 'blocktype', etc
 * @param string $pluginname The plugin name: 'file', 'creativecommons', etc
 * @param string $key The config name
 * @param string $value The config's new value
 * @return boolean Whether or not the config was updated successfully
 */
function set_config_plugin($plugintype, $pluginname, $key, $value) {
    global $CFG;
    $table = $plugintype . '_config';
    $value = (string) $value;

    $success = false;
    $dbvalue = get_field($table, 'value', 'plugin', $pluginname, 'field', $key);
    if (false !== $dbvalue) {
        if (
                // No need to update the DB if the value already matches
                ($dbvalue === $value)
                || set_field($table, 'value', $value, 'plugin', $pluginname, 'field', $key)
        ) {
            $success = true;
        }
    }
    else {
        $pconfig = new stdClass();
        $pconfig->plugin = $pluginname;
        $pconfig->field  = $key;
        $pconfig->value  = $value;
        $success = insert_record($table, $pconfig);
    }
    // Now update the cached version
    if ($success) {
        $configname = "plugin_{$plugintype}_{$pluginname}_{$key}";
        $CFG->{$configname} = $value;
        return true;
    }
    return false;
}

/**
 * This function returns a value for $CFG for a plugin instance
 * or null if it is not found. Initially this is interesting only
 * for multiauth. Note that it may go and look in the database
 *
 * @param string $plugintype   E.g. auth
 * @param string $instanceid     Instance id
 * @param string $key          The config setting to look for
 */
function get_config_plugin_instance($plugintype, $instanceid, $key) {
    global $CFG;

    // Must be unlikely to exist as a config option for any plugin
    $instance = '_i_n_s_t' . $instanceid;

    // Suppress NOTICE with @ in case $key is not yet cached
    $configname = "plugin_{$plugintype}_{$instance}_{$key}";
    @$value = $CFG->{$configname};
    if (isset($value)) {
        return $value;
    }

    $instancefield = $plugintype == 'interaction_forum' ? 'forum' : 'instance';
    $records = get_records_array($plugintype . '_instance_config', $instancefield, $instanceid, 'field', 'field, value');

    if (!empty($records)) {
        foreach($records as $record) {
            $storeconfigname = "plugin_{$plugintype}_{$instance}_{$record->field}";
            $CFG->{$storeconfigname} = $record->value;
            if ($record->field == $key) {
                $value = $record->value;
            }
        }
    }

    return $value;
}

/**
 * This function returns a value for $CFG for a plugin instance
 * or null if it is not found. Initially this is interesting only
 * for multiauth. Note that it may go and look in the database
 *
 * @param string $plugintype   E.g. auth
 * @param string $pluginname   E.g. internal
 * @param string $instanceid     Instance id
 * @param string $key          The config setting to look for
 */
function set_config_plugin_instance($plugintype, $pluginname, $instanceid, $key, $value) {
    global $CFG;
    $value = (string) $value;

    $table = $plugintype . '_instance_config';
    $dbvalue = get_field($table, 'value', 'instance', $instanceid, 'field', $key);
    if ($dbvalue !== false) {
        if (
                // No need to update the DB if the value already matches
                ($dbvalue === $value)
                || set_field($table, 'value', $value, 'instance', $instanceid, 'field', $key)
        ) {
            $status = true;
        }
    }
    else {
        $pconfig = new stdClass();
        $pconfig->instance = $instanceid;
        $pconfig->field  = $key;
        $pconfig->value  = $value;
        $status = insert_record($table, $pconfig);
    }
    if ($status) {
        // Must be unlikely to exist as a config option for any plugin
        $instance = '_i_n_s_t' . $instanceid;
        $configname = "plugin_{$plugintype}_{$instance}_{$key}";
        $CFG->{$configname} = $value;
        return true;
    }
    return false;
}

/**
 * Fetch an institution configuration (from either the "institution" or "institution_config" table)
 *
 * @param string $institutionname
 * @param string $key
 * @return mixed The value of the key or NULL if the key is not valid
 */
function get_config_institution($institutionname, $key) {
    global $CFG;
    require_once(get_config('docroot').'/lib/institution.php');

    // First, check the cache for an Institution object with this name
    if (isset($CFG->fetchedinst->{$institutionname})) {
        $inst = $CFG->fetchedinst->{$institutionname};
    }
    else {
        // No cache hit, so instatiate a new Institution object
        try {
            $inst = new Institution($institutionname);

            // Cache it in $CFG so we can make set_config_institution() update the cache
            if (!isset($CFG->fetchedinst)) {
                $CFG->fetchedinst = new stdClass();
            }
            $CFG->fetchedinst->{$institutionname} = $inst;
        }
        catch (ParamOutOfRangeException $e) {
            return null;
        }
    }

    // Use the magical __get() function of the Institution class
    return $inst->{$key};
}

/**
 * Set or update an institution config value.
 *
 * @param string $institutionname The institution name
 * @param string $key The config name
 * @param string $value The config's new value
 * @return boolean Whether or not the config was updated successfully
 */
function set_config_institution($institutionname, $key, $value) {
    global $CFG;

    if (isset($CFG->fetchedinst->{$institutionname})) {
        $inst = $CFG->fetchedinst->{$institutionname};
    }
    else {
        // No cache hit, so instatiate a new Institution object
        try {
            $inst = new Institution($institutionname);
        }
        catch (ParamOutOfRangeException $e) {
            return null;
        }
    }
    if (isset($inst)) {
        $inst->{$key} = $value;
        $inst->commit();
        return true;
    }
    return false;
}

/**
 * Fetch a config setting for the specified user's institutions (from either the "institution" or "institution_config" table)
 *
 * @param string $key
 * @param int $userid (Optional) If not supplied, fetch for the current user's institutions
 * @return array The results for the all the users' institutions, in the order
 *               supplied by load_user_institutions(). Array key is institution name.
 */
function get_configs_user_institutions($key, $userid = null) {
    global $USER, $CFG;
    if ($userid === null) {
        $userid = $USER->id;
    }

    // Check for the user and key in the cache (The cache is stored in $CFG so it can be cleared/updated
    // if we ever write a set_config_institution() method)
    $userobj = "user{$userid}";
    if (isset($CFG->userinstconf->{$userobj}->{$key})) {
        return $CFG->userinstconf->{$userobj}->{$key};
    }

    // We didn't hit the cache, so retrieve the config from their
    // institution.

    // First, get a list of their institution names
    if (!$userid) {
        // The logged-out user has no institutions.
        $institutions = false;
    }
    else if ($userid == $USER->id) {
        // Institutions for current logged-in user
        $institutions = $USER->get('institutions');
    }
    else {
        $institutions = load_user_institutions($userid);
    }

    // If the user belongs to no institution, check the Mahara institution
    if (!$institutions) {
        // For compatibility with $USER->get('institutions') and
        // load_user_institutions(), we only really care about the
        // array keys
        $institutions = array('mahara' => 'mahara');
    }
    $results = array();
    foreach ($institutions as $instname => $inst) {
        $results[$instname] = get_config_institution($instname, $key);
    }

    // Cache the result
    if (!isset($CFG->userinstconf)) {
        $CFG->userinstconf = new stdClass();
    }
    if (!isset($CFG->userinstconf->{$userobj})) {
        $CFG->userinstconf->{$userobj} = new stdClass();
    }
    $CFG->userinstconf->{$userobj}->{$key} = $results;

    return $results;
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
 * Reads the locales string from a language pack and attempts to set the current locale
 * based on the contents of that string.
 *
 * @param string $lang
 */
function set_locale_for_language($lang) {
    if (empty($lang)) {
        return;
    }

    if ($args = explode(',', get_string_location('locales', 'langconfig', array(), 'raw_langstring', $lang))) {
        array_unshift($args, LC_ALL);
        call_user_func_array('setlocale', $args);
    }
}

/**
 * This function returns the current language to use, either for a given user
 * or sitewide, or the default.
 *
 * This method is invoked in every call to get_string(), so make it performant!
 *
 * @return string
 */
function current_language() {
    global $USER, $CFG, $SESSION;

    static $userlang, $lastlang, $instlang;

    $loggedin = $USER instanceof User && $USER->is_logged_in();

    // Retrieve & cache the user lang pref (if the user is logged in)
    if (!isset($userlang) && $loggedin) {
        $userlang = $USER->get_account_preference('lang');
        if ($userlang !== null && $userlang != 'default') {
            if (!language_installed($userlang)) {
                $USER->set_account_preference('lang', 'default');
                $userlang = 'default';
            }
        }
    }

    // Retrieve & cache the institution language (if the user is logged in)
    if (!isset($instlang) && $loggedin) {
        $instlang = get_user_institution_language();
    }

    // Retrieve the $SESSION lang (from the user selecting a language while logged-out)
    // Note that if the user selected a language while logged out, and then logs in,
    // we will have set their user account pref to match that lang, over in
    // LiveUser->authenticate().
    if (!$loggedin && is_a($SESSION, 'Session')) {
        $sesslang = $SESSION->get('lang');
    }
    else {
        $sesslang = null;
    }

    // Logged-in user's language preference
    if (!empty($userlang) && $userlang != 'default') {
        $lang = $userlang;
    }
    // Logged-out user's language menu selection
    else if (!empty($sesslang) && $sesslang != 'default') {
        $lang = $sesslang;
    }
    // Logged-in user's institution language setting
    else if (!empty($instlang) && $instlang != 'default') {
        $lang = $instlang;
    }

    // If there's no language from the user pref or the logged-out lang menu...
    if (empty($lang)) {
        $lang = !empty($CFG->lang) ? $CFG->lang : 'en.utf8';
    }

    if ($lang == $lastlang) {
        return $lang;
    }

    set_locale_for_language($lang);

    return $lastlang = $lang;
}


/**
 * Find out a user's institution language. If they belong to one institution that has specified
 * a language, then this will be that institution's language. If they belong to multiple
 * institutions that have specified languages, it will be the arbitrarily "first" institution.
 * If they belong to no institution that has specified a language, it will return null.
 *
 * @param int $userid Which user to check (defaults to $USER)
 * @param string $sourceinst If provided, the source institution for the language will be
 *     returned here by reference
 * @return string A language, or 'default'
 */
function get_user_institution_language($userid = null, &$sourceinst = null) {
    global $USER;
    if ($userid == null) {
        $userid = $USER->id;
    }
    $instlangs = get_configs_user_institutions('lang', $userid);
    // Every user belongs to at least one institution
    foreach ($instlangs as $name => $lang) {
        $sourceinst = $name;
        $instlang = $lang;
        // If the user belongs to multiple institutions, arbitrarily use the language
        // from the first one that has specified a language.
        if (!empty($instlang) && $instlang != 'default' && language_installed($instlang)) {
            break;
        }
    }
    if (!$instlang) {
        $instlang = 'default';
    }
    return $instlang;
}


/**
 * Helper function to sprintf language strings
 * with a variable number of arguments
 *
 * @param mixed $string raw string to use, or an array of strings, one for each plural form
 * @param array $args arguments to sprintf
 * @param string $lang The language
 */
function format_langstring($string, $args, $lang='en.utf8') {
    if (is_array($string) && isset($args[0]) && is_numeric($args[0])) {
        // If there are multiple strings here, there must be one for each plural
        // form in the language.  The first argument is passed into the plural
        // function, which returns an index into the array of strings.
        $pluralfunction = get_string_location('pluralfunction', 'langconfig', array(), 'raw_langstring', $lang);
        $index = function_exists($pluralfunction) ? $pluralfunction($args[0]) : 0;
        $string = isset($string[$index]) ? $string[$index] : current($string);
    }

    return call_user_func_array('sprintf',array_merge(array($string),$args));
}

function raw_langstring($string) {
    return $string;
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
    $dir = trim($dir);

    if(!is_dir($dir)) {
        if (!$create) {
            $status = false;
        } else {
            $mask = umask(0000);
            $status = @mkdir($dir, get_config('directorypermissions'), true);
            umask($mask);
        }
    }
    return $status;
}


/**
 * Function to require a plugin file. This is to avoid doing
 * require and include directly with variables.
 *
 * This function is the one safe point to require plugin files.
 * so USE it :)
 *
 * blocktypes are special cases.  eg:
 *   system blocks: safe_require('blocktype', 'wall');
 *   artefact blocks: safe_require('blocktype', 'file/html');
 *
 * import/export plugins are special cases.  eg:
 *   main library: safe_require('export', 'leap');
 *   artefact plugin implementation: safe_require('export', 'leap/file');
 *
 * @param string $plugintype the type of plugin (eg artefact)
 * @param string $pluginname the name of the plugin (eg blog)
 * @param string $filename the name of the file to include within the plugin structure
 * @param string $function (optional, defaults to require) the require/include function to use
 * @param string $nonfatal (optional, defaults to false) just returns false if the file doesn't exist
 * @param array $returnvars (optional, defaults to null) Variables (defined in the file) to return.
 * Useful for files like version.php that simply define variables. If null, instead returns the
 * value of the include/require operation.
 */
function safe_require($plugintype, $pluginname, $filename='lib.php', $function='require_once', $nonfatal=false, $returnvars = null) {
    $plugintypes = plugin_types();
    if (!in_array($plugintype, $plugintypes)) {
        throw new SystemException("\"$plugintype\" is not a valid plugin type");
    }
    require_once(get_config('docroot') . $plugintype . '/lib.php');

    if (!in_array($function,array('require', 'include', 'require_once', 'include_once'))) {
        if (!empty($nonfatal)) {
            return false;
        }
        throw new SystemException ('Invalid require type');
    }

    if ($plugintype == 'blocktype') { // these are a bit of a special case
        $bits = explode('/', $pluginname);
        if (count($bits) == 2) {
           $fullpath = get_config('docroot') . 'artefact/' . $bits[0] . '/blocktype/' . $bits[1] . '/' . $filename;
        }
        else {
            try {
                if ($artefactplugin = blocktype_artefactplugin($pluginname)) {
                    $fullpath = get_config('docroot') . 'artefact/' . $artefactplugin . '/blocktype/' . $pluginname . '/'. $filename;
                }
            }
            catch (SQLException $e) {
                if (get_config('installed')) {
                    throw $e;
                }
            }
        }
    }
    // these can have parts living inside artefact directories as well.
    else if ($plugintype == 'export' || $plugintype == 'import') {
        $bits = explode('/', $pluginname);
        if (count($bits) == 2) {
            $fullpath = get_config('docroot') . 'artefact/' . $bits[1] . '/' . $plugintype . '/' . $bits[0] . '/' . $filename;
        }
    }
    if (empty($fullpath)) {
        $fullpath = get_config('docroot') . $plugintype . '/' . $pluginname . '/' . $filename;
    }

    if (!file_exists($fullpath)) {
        if (!empty($nonfatal)) {
            return false;
        }
        throw new SystemException ("File $fullpath did not exist");
    }

    $realpath = realpath($fullpath);

    if (strpos($realpath, get_config('docroot') !== 0)) {
        if (!empty($nonfatal)) {
            return false;
        }
        throw new SystemException ("File $fullpath was outside document root!");
    }

    if ($function == 'require') { $isloaded = require($realpath); }
    if ($function == 'include') { $isloaded = include($realpath); }
    if ($function == 'require_once') { $isloaded = require_once($realpath); }
    if ($function == 'include_once') { $isloaded = include_once($realpath); }

    if ($isloaded && $returnvars && is_array($returnvars)) {
        return compact($returnvars);
    }
    else {
        return $isloaded;
    }
}

/**
 * This function is a wrapper around safe_require which will attempt to
 * handle missing plugins more gracefully.
 *
 * If a missing plugin is detected, then that plugin will be disabled, and
 * an e-mail will be sent to site administrators to inform them of the
 * issue.
 *
 * See @safe_require for further information on that function.
 *
 * @param string $plugintype the type of plugin (eg artefact)
 * @param string $pluginname the name of the plugin (eg blog)
 * @param string $filename the name of the file to include within the plugin structure
 * @param string $function (optional, defaults to require) the require/include function to use
 * @param string $nonfatal (optional, defaults to false) just returns false if the file doesn't exist
 * @param array $returnvars (optional, defaults to null) Variables (defined in the file) to return.
 * Useful for files like version.php that simply define variables. If null, instead returns the
 * value of the include/require operation.
 */
function safe_require_plugin($plugintype, $pluginname, $filename='lib.php', $function='require_once', $nonfatal=false, $returnvars = null) {
    try {
        return safe_require($plugintype, $pluginname, $filename, $function, $nonfatal, $returnvars);
    }
    catch (SystemException $e) {
        if (get_field($plugintype . '_installed', 'active', 'name', $pluginname) == 1) {
            global $SESSION;

            set_field($plugintype . '_installed', 'active', 0, 'name', $pluginname);
            $SESSION->add_error_msg(get_string('missingplugindisabled1', 'admin', hsc("$plugintype:$pluginname")));

            // Reset the plugin cache.
            plugins_installed('', TRUE, TRUE);

            // Alert site admins that the plugin is broken so was disabled
            $message = new stdClass();
            $message->users = get_column('usr', 'id', 'admin', 1);
            $message->subject = get_string('pluginbrokenanddisabledtitle1', 'mahara', $pluginname);
            $message->message = get_string('pluginbrokenanddisabled', 'mahara', $pluginname, $e->getMessage());

            require_once('activity.php');
            activity_occurred('maharamessage', $message);
        }
        return false;
    }
}

/**
 * Check to see if a particular plugin is installed and is active by plugin name
 *
 * @param   string $pluginname Name of plugin
 * @param   string $type       Name of plugin type
 * @return  bool
 */
function is_plugin_active($pluginname, $type = null) {
    if ($type) {
        if (record_exists($type . '_installed', 'name', $pluginname, 'active', 1)) {
            return true;
        }
    }
    else {
        log_warn("Calling 'is_plugin_active()' without specifying plugin 'type'. This function may return incorrect results. Please update your 'is_plugin_active()' calls.");
        foreach (plugin_types() as $type) {
            if (record_exists($type . '_installed', 'name', $pluginname, 'active', 1)) {
                return true;
            }
        }
    }
    return false;
}

/**
 * This function returns the list of plugintypes we currently care about.
 *
 * NOTE: use plugin_types_installed if you just want the installed ones.
 *
 * @return array of names
 */
function plugin_types() {
    static $pluginstocheck;
    if (empty($pluginstocheck)) {
        // ORDER MATTERS! artefact has to be before blocktype
        // And so does module because module.framework has blocks as foreign keys in DB
        $pluginstocheck = array('artefact', 'auth', 'notification', 'search', 'module', 'blocktype', 'interaction', 'grouptype', 'import', 'export');
    }
    return $pluginstocheck;
}

/**
 * Returns plugin types that are actually installed
 */
function plugin_types_installed() {
    static $plugins = array();
    if (empty($plugins)) {
        require_once('ddl.php');
        foreach (plugin_types() as $plugin) {
            if (table_exists(new XMLDBTable("{$plugin}_installed"))) {
                $plugins[] = $plugin;
            }
        }
    }
    return $plugins;
}

/**
 * This returns the names of plugins installed
 * for the given plugin type.
 *
 * @param string $plugintype type of plugin
 * @param bool $all - return all (true) or only active (false) plugins
 * @param bool $reset - whether to reset the cache (when disabling a plugin due to unavailability)
 * @return array of objects with fields (version (int), release (str), active (bool), name (str))
 */
function plugins_installed($plugintype, $all=false, $reset=false) {
    static $records = array();

    if ($reset) {
        $records = array();
        return false;
    }

    if (defined('INSTALLER') || defined('TESTSRUNNING') || !isset($records[$plugintype][true])) {

        $sort = $plugintype == 'blocktype' ? 'artefactplugin,name' : 'name';

        if ($rs = get_records_assoc($plugintype . '_installed', '', '', $sort)) {
            $records[$plugintype][true] = $rs;
        }
        else {
            $records[$plugintype][true] = array();
        }
        $records[$plugintype][false] = array();

        foreach ($records[$plugintype][true] as $r) {
            if ($r->active) {
                $records[$plugintype][false][$r->name] = $r;
            }
        }
    }
    if (isset($records[$plugintype])) {
        return $records[$plugintype][$all];
    }
    return false;
}

/**
 * This returns the names of plugins installed
 * for all plugin types.
 *
 * @param bool $all - return all (true) or only active (false) plugins
 * @return array of objects with fields (version (int), release (str), active (bool), name (str))
 */

function plugin_all_installed($all=false) {
    $plugintypes = plugin_types_installed();
    $result = array();
    foreach ($plugintypes as $plugintype) {
        $plugins = plugins_installed($plugintype, $all);
        foreach ($plugins as $plugin) {
            $plugin->plugintype = $plugintype;
            $result[] = $plugin;
        }
    }
    return $result;
}

/**
 * Helper to call a static method when you do not know the name of the class
 * you want to call the method on. (PHP 5.0-5.2 did not support $class::method())
 *
 * @deprecated In PHP 5.3+, you can do $class::$method, $class::method(), or class::$method
 * See: http://php.net/ChangeLog-5.php#5.3.0
 * "Added support for dynamic access of static members using $foo::myFunc()."
 */
function call_static_method($class, $method) {
    $args = func_get_args();
    array_shift($args);
    array_shift($args);
    return call_user_func_array(array($class, $method), $args);
}

function generate_class_name() {
    $args = func_get_args();
    if (count($args) == 2 && $args[0] == 'blocktype') {
        return 'PluginBlocktype' . ucfirst(blocktype_namespaced_to_single($args[1]));
    }
    return 'Plugin' . implode('', array_map('ucfirst', $args));
}

function generate_artefact_class_name($type) {
    return 'ArtefactType' . ucfirst($type);
}

function generate_interaction_instance_class_name($type) {
    return 'Interaction' . ucfirst($type) . 'Instance';
}

function generate_generator_class_name() {
    $args = func_get_args();
    return 'DataGenerator' . implode('', array_map('ucfirst', $args));
}

function blocktype_namespaced_to_single($blocktype) {
    if (strpos($blocktype, '/') === false) { // system blocktype
        return $blocktype;
    }
    return substr($blocktype, strpos($blocktype, '/') + 1 );
}

function blocktype_single_to_namespaced($blocktype, $artefact='') {
    if (empty($artefact)) {
        return $blocktype;
    }
    return $artefact . '/' . $blocktype;
}

/**
 * Given a blocktype name, convert it to the namespaced version.
 *
 * This will be $artefacttype/$blocktype, or just plain $blocktype for system
 * blocktypes.
 *
 * This is useful for language strings
 */
function blocktype_name_to_namespaced($blocktype) {
    static $resultcache = array();

    if (!isset($resultcache[$blocktype])) {
        $artefactplugin = blocktype_artefactplugin($blocktype);
        if ($artefactplugin) {
            $resultcache[$blocktype] = "$artefactplugin/$blocktype";
        }
        else {
            $resultcache[$blocktype] = $blocktype;
        }
    }

    return $resultcache[$blocktype];
}

/* Get the name of the artefact plugin that provides a given blocktype */
function blocktype_artefactplugin($blocktype) {
    $installed = plugins_installed('blocktype', true);
    if (isset($installed[$blocktype])) {
        return $installed[$blocktype]->artefactplugin;
    }
    return false;
}


/**
 * Fires an event which can be handled by different parts of the system
 */
function handle_event($event, $data, $ignorefields = array()) {
    global $USER;
    static $event_types = array(), $coreevents_cache = array(), $eventsubs_cache = array();

    if (empty($event_types)) {
        $event_types = array_fill_keys(get_column('event_type', 'name'), true);
    }

    if (defined('INSTALLER') && !isset($event_types[$event])) {
        // Do not handle events not currently installed during upgrade
        return;
    }

    $e = $event_types[$event];

    if (is_null($e)) {
        throw new SystemException("Invalid event");
    }

    // leave $data alone, but convert for the event log
    if (is_object($data)) {
        $logdata = clone $data;
    }
    else {
        if (is_numeric($data)) {
            $logdata = $data = array('id' => $data);
        }
        else {
            $logdata = $data;
        }
        $data = (array)$data;
    }

    // Set viewaccess rules for elasticsearch
    if ($event == 'updateviewaccess' && get_config('searchplugin') == 'elasticsearch' && is_array($data)) {
        if (isset($data['rules']) && isset($data['rules']->view)) {
            safe_require('search', 'elasticsearch');
            $viewid = $data['rules']->view;
            $table = 'view';
            // Add the queue item
            $sql = "INSERT INTO {search_elasticsearch_queue} (itemid, type)
                    SELECT ?, ? WHERE NOT EXISTS (
                        SELECT 1 FROM {search_elasticsearch_queue} WHERE itemid = ? AND type = ?
                    )";
            execute_sql($sql, array($viewid, $table, $viewid, $table));

            // We need to update the user and artefacts for the view
            $sql = "INSERT INTO {search_elasticsearch_queue} (itemid, type)
                    SELECT u.id, ? AS type FROM {usr} u
                    INNER JOIN {view} v ON v.owner = u.id
                    WHERE v.type = ?
                    AND v.id = ?
                    AND NOT EXISTS (
                        SELECT q.id FROM {search_elasticsearch_queue} q
                        WHERE q.type = ? AND q.itemid = u.id
                    )";
            execute_sql($sql, array('usr', 'profile', $viewid, 'usr'));

            $artefacttypes_str = ElasticsearchIndexing::artefacttypes_filter_string();
            $sql = "INSERT INTO {search_elasticsearch_queue} (itemid, type)
                    SELECT va.artefact, ? AS type
                    FROM {view_artefact} va
                    INNER JOIN {artefact} a ON va.artefact = a.id
                    WHERE va.view = ?
                    AND va.artefact NOT IN (SELECT itemid FROM {search_elasticsearch_queue} WHERE type = ?)
                    AND a.artefacttype IN " . $artefacttypes_str;
            execute_sql($sql, array('artefact', $viewid, 'artefact'));
        }
    }

    $refid = $reftype = $parentrefid = $parentreftype = null;
    // Need to set dirty to false for all the classes with destructors
    if ($logdata instanceof View) {
        $logdata->set('dirty', false);
        // Move the id / view off to dedicated columns for easier searching
        $logdata = $logdata->to_stdclass();
        $refid = $logdata->id;
        unset($logdata->id);
        $reftype = 'view';
    }
    else if ($logdata instanceof ArtefactType) {
        $logdata->set('dirty', false);
        // Move the id / atefacttype off to dedicated columns for easier searching
        $logdata = $logdata->to_stdclass();
        $refid = $logdata->id;
        unset($logdata->id);
        $reftype = $logdata->artefacttype;
        unset($logdata->artefacttype);
        $parentrefid = $refid;
        $parentreftype = 'artefact';
        if ($reftype == 'comment') {
            if (isset($logdata->onview)) {
                $parentrefid = $logdata->onview;
                $parentreftype = 'view';
            }
            if (isset($logdata->onartefact)) {
                $parentrefid = $logdata->onartefact;
            }
        }
    }
    else if ($logdata instanceof BlockInstance) {
        $logdata->set('dirty', false);
        // Remove data from configdata that we don't need to log
        $configdata = $logdata->get('configdata');
        if (isset($ignorefields['ignoreconfigdata'])) {
            $ignore = $ignorefields['ignoreconfigdata'];
            if (!empty($ignore) && is_array($ignore)) {
                foreach ($ignore as $item) {
                    unset($configdata[$item]);
                }
            }
            unset($ignorefields['ignoreconfigdata']);
        }
        $logdata = $logdata->to_stdclass();
        $logdata->configdata = $configdata;
        // Move the id / blocktype and parent id / view off to dedicated columns for easier searching
        $refid = $logdata->id;
        unset($logdata->id);
        $reftype = $logdata->blocktype;
        unset($logdata->blocktype);
        $parentrefid = $logdata->view;
        unset($logdata->view);
        $parentreftype = 'view';
    }
    else if (is_object($logdata)) {
        // Try to set id / type from stdclass object to dedicated column if 'eventfor' indicated
        // eg. event = creategroup would have eventfor = group
        $logdata = (object)get_object_vars($logdata);
        if (isset($logdata->id) && isset($logdata->eventfor)) {
            $refid = $logdata->id;
            $reftype = $logdata->eventfor;
            unset($logdata->eventfor);
            if (isset($logdata->parentid) && isset($logdata->parenttype)) {
                $parentrefid = $logdata->parentid;
                $parentreftype = $logdata->parenttype;
                unset($logdata->parentid);
                unset($logdata->parenttype);
            }
        }
        $data = (array)$data;
    }
    else {
        $refid = !empty($logdata['id']) ? $logdata['id'] : null;
        $reftype = !empty($logdata['eventfor']) ? $logdata['eventfor'] : null;
        unset($logdata['eventfor']);
        if (isset($logdata['parentid']) && isset($logdata['parenttype'])) {
            $parentrefid = $logdata['parentid'];
            $parentreftype = $logdata['parenttype'];
            unset($logdata['parentid']);
            unset($logdata['parenttype']);
        }
    }

    // Then remove any unwanted items
    if (is_object($logdata)) {
        foreach ($logdata as $key => $field) {
            if (in_array($key, $ignorefields) || empty($field)) {
                unset($logdata->{$key});
            }
        }
        $logdata = (array)$logdata;
    }

    $parentuser = $USER->get('parentuser');
    $eventloglevel = get_config('eventloglevel');
    if ($eventloglevel === 'all'
            or ($parentuser and $eventloglevel === 'masq')) {
        $logentry = (object) array(
            'usr'      => $USER->get('id'),
            'realusr'  => $parentuser ? $parentuser->id : $USER->get('id'),
            'event'    => $event,
            'data'     => json_encode($logdata),
            'ctime'    => db_format_timestamp(time()),
            'resourceid' => $refid,
            'resourcetype' => $reftype,
            'parentresourceid' => $parentrefid,
            'parentresourcetype' => $parentreftype,
        );
        // Include the old time column as well to cater for
        // older versions of Mahara getting upgraded.
        // Their event_log table will have not gone
        // through the table alters during this
        // part of the upgrade.
        // The date it changed was 2017090800.
        $logentry->time = $logentry->ctime;

        // find out who 'owns' the event
        list ($ownerid, $ownertype) = event_find_owner_type($logentry);
        $logentry->ownerid = $ownerid;
        $logentry->ownertype = $ownertype;
        insert_record('event_log', $logentry);
        // If we are adding a comment to a page that is shared to a group
        // we need to add a 'sharedcommenttogroup' event
        if (is_event_comment_shared_with_group($reftype, $logdata)) {
            $wheresql = '';
            if (!empty($logdata['onartefact'])) {
                $commenttype = 'artefact';
                $commenttypeid = $logdata['onartefact'];
                $wheresql = " IN (SELECT view FROM {view_artefact} WHERE " . $commenttype . " = ?) ";
            }
            else if (!empty($logdata['onview'])) {
                $commenttype = 'view';
                $commenttypeid = $logdata['onview'];
                $wheresql = " = ? ";
            }
            if ($wheresql != '' && $groupids = get_records_sql_array("SELECT \"group\" FROM {view_access}
                                                WHERE view " . $wheresql . "
                                                AND \"group\" IS NOT NULL", array($commenttypeid))) {
                foreach ($groupids as $groupid) {
                    $logentry->event = 'sharedcommenttogroup';
                    $logentry->data = null;
                    $logentry->ownerid = $groupid->group;
                    $logentry->ownertype = 'group';
                    insert_record('event_log', $logentry);
                }
            }
        }
        // @TODO If we are sharing a page to a group that contains existing comments do we count these are sharedcomments?
    }

    if (empty($coreevents_cache)) {
        $rs = get_recordset('event_subscription');
        foreach($rs as $record) {
            $coreevents_cache[$record['event']][] = $record['callfunction'];
        }
        $rs->close();
    }

    $coreevents = isset($coreevents_cache[$event]) ? $coreevents_cache[$event] : array();

    if (!empty($coreevents)) {
        require_once('activity.php'); // core events can generate activity.
        foreach ($coreevents as $ce) {
            if (function_exists($ce)) {
                call_user_func($ce, $data);
            }
            else {
                log_warn("Event $event caused a problem with a core subscription "
                . " $ce, which wasn't callable.  Continuing with event handlers");
            }
        }
    }

    $plugintypes = plugin_types_installed();
    foreach ($plugintypes as $name) {
        $cache_key = "{$event}__{$name}";
        if (!isset($eventsubs_cache[$cache_key])) {
            $eventsubs_cache[$cache_key] = get_records_array($name . '_event_subscription', 'event', $event);
        }
        if ($eventsubs_cache[$cache_key]) {
            $pluginsinstalled = plugins_installed($name);
            foreach ($eventsubs_cache[$cache_key] as $sub) {
                if (!isset($pluginsinstalled[$sub->plugin])) {
                    continue;
                }
                safe_require($name, $sub->plugin);
                $classname = 'Plugin' . ucfirst($name) . ucfirst($sub->plugin);
                try {
                    if (method_exists($classname, $sub->callfunction)) {
                        call_static_method($classname, $sub->callfunction, $event, $data);
                    }
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
 * Find out if the event relates to a comment
 * and if the page the comment is on is shared
 * with a group.
 *
 * @param string $reftype event reference type
 * @param array $logdata the data that makes up the event
 *
 * @return boolean
 */
function is_event_comment_shared_with_group($reftype, $logdata) {
    $result = false;
    if ($reftype == 'comment' && empty($logdata['group'])) {
        if (!empty($logdata['onartefact']) || !empty($logdata['onview'])) {
            $result = true;
        }
    }
    return $result;
}

/**
 * Find out who / what owns the event
 *
 * @param obj $event  event_log database record
 *
 * @return array  An array of ($id, $type)
 */
function event_find_owner_type($event) {
    $ownerid = null;
    $ownertype = null;
    $record = null;
    $validtypes = array('view', 'collection', 'artefact');
    if (!empty($event->parentresourcetype) && in_array($event->parentresourcetype, $validtypes)) {
        $record = get_record($event->parentresourcetype, 'id', $event->parentresourceid);
    }
    else if (!empty($event->resourcetype) && in_array($event->resourcetype, $validtypes)) {
        $record = get_record($event->resourcetype, 'id', $event->resourceid);
    }

    if ($record) {
        if (!empty($record->institution)) {
            $ownerid = get_field('institution', 'id', 'name', $record->institution);
            $ownertype = 'institution';
        }
        if (!empty($record->group)) {
            $ownerid = $record->group;
            $ownertype = 'group';
        }
        if (!empty($record->owner)) {
            $ownerid = $record->owner;
            $ownertype = 'user';
        }
    }
    else if (!empty($event->resourcetype) && in_array($event->resourcetype, array('group', 'user', 'institution'))) {
        $ownerid = $event->resourceid;
        $ownertype = $event->resourcetype;
    }
    return array($ownerid, $ownertype);
}

/**
 * Used by XMLDB
 */
function debugging($message, $level) {
    log_debug($message);
}
function xmldb_dbg($message) {
    log_warn($message);
}
define('DEBUG_DEVELOPER', 'whocares');


/**
 * Helper interface to hold the Plugin class's abstract static functions
 */
interface IPlugin {
    /**
     * The name of this plugintype. Used in directory names, table names, etc.
     * @return string
     */
    public static function get_plugintype_name();
}

/**
 * defines for web services types and authentication
 *
 *
 */
define('WEBSERVICE_TYPE_SOAP', 'soap');
define('WEBSERVICE_TYPE_XMLRPC', 'xmlrpc');
define('WEBSERVICE_TYPE_REST', 'rest');
define('WEBSERVICE_TYPE_OAUTH1', 'oauth1');

define('WEBSERVICE_AUTH_USERPASS', 'user');
define('WEBSERVICE_AUTH_TOKEN', 'token');
define('WEBSERVICE_AUTH_CERT', 'cert');
define('WEBSERVICE_AUTH_WSSE', 'wsse');

/**
 * Generate an HTTP context object
 *
 * @param string $url
 * @return object - stream context object
 */

function webservice_create_context($url) {
    $hostname = parse_url($url, PHP_URL_HOST);
    $context = array('http' => array ('method' => 'POST',
                                'request_fulluri' => true,),
            );
    if (get_config('disablesslchecks')) {
        $context['ssl'] = array('verify_host' => false,
                           'verify_peer' => false,
                           'verify_peer_name' => false,
                           'SNI_server_name' => $hostname,
                           'SNI_enabled'     => true,);
    }
    $context = stream_context_create($context);
    return $context;
}

/**
 * Base class for all plugintypes.
 */
abstract class Plugin implements IPlugin {

    /**
     * This function returns an array of crons it wants to have run.
     *
     * The return value should be an array of objects. Each object should have these fields:
     *
     * - callfunction (mandatory): The name of the cron function. This must be a public static function
     * of the particular plugin subclass. It will be called with no parameters, and should return no
     * value.
     * - minute (defaults to *)
     * - hour (defaults to *)
     * - day (defaults to *)
     * - month (defaults to *)
     * - dayofweek (defaults to *)
     *
     * @return array
     */
    public static function get_cron() {
        return array();
    }

    /**
     * This function returns an array of events to subscribe to by unique name.
     * If an event the plugin is trying to subscribe to is unknown by the
     * core, an exception will be thrown.
     *
     * The return value should be array of objects. Each object should have these fields:
     *
     *  - event: The name of the event to subscribe. Must be present in the "event_type" table.
     *  - plugin: The name of the plugin that holds the callfunction (probably this one!)
     *  - callfunction: The function to call when the event occurs.
     *
     * @return array
     */
    public static function get_event_subscriptions() {
        return array();
    }

    /**
     * This function returns an array of client connection by unique name.
     *
     * The return value should be array of objects. Each object should have these fields:
     *
     *  - connection: The name of the client connection handle.
     *  - name: The descriptive name of the client connection for display purposes
     *  - version: A version required for the connection
     *  - notes: descriptive text that developer might want to show user
     *  - type: Protocol type required eg: WEBSERVICE_CLIENT_TYPE_SOAP
     *  - isfatal: Should an error be fatal
     *
     * @return array
     */
    public static function define_webservice_connections() {
        return array();
    }

    /**
     * This function returns an array of client connection records.
     *
     * @param array $institutions the institutions for the context of selecting
     *        the client connections
     *
     * @return array
     */
    public static function calculate_webservice_connections($institutions) {

        $me = get_called_class();
        $connection_defs = call_user_func($me . '::define_webservice_connections');
        $connections = array();
        foreach ($connection_defs as $def) {
            if (isset($def['connection'])) {
                $cname = $def['connection'];
                if ($results = get_records_sql_assoc(
                    'SELECT cci.*
                     FROM {client_connections_institution} AS cci
                     WHERE cci.class = ? AND
                           cci.connection = ? AND
                           cci.institution IN ('.join(',', array_map('db_quote', $institutions)).') AND enable = 1', array($me, $cname))
                ) {
                    foreach ($results as $c) {
                        $c->version = $def['version'];
                        $c->connectorname = $def['name'];
                        $connections[]= $c;
                    }
                }
            }
        }
        return $connections;
    }

    /**
     * This function returns an array of client connections.
     *
     * @param object $user the user for the context of selecting the client connections
     *
     * @return array
     */
    public static function get_webservice_connections($user=null) {
        global $USER;

        // is the web service connection switch enabled?
        if (!get_config('webservice_requester_enabled')) {
            log_debug('get_webservice_connections: disabled');
            return array();
        }
        // do we have any defined connections enabled?
        if (!get_records_array('client_connections_institution', 'enable', 1, '', 'id', 0, 1)) {
            log_debug('get_webservice_connections: no active connections');
            return array();
        }

        require_once(get_config('docroot') . 'webservice/lib.php');

        $userinstitutions = array();
        $institutions = ($user == null ? $USER->get('institutions') : load_user_institutions($user->id));
        if (!empty($institutions)) {
            foreach ($institutions as $institution) {
                $userinstitutions[] = $institution->institution;
            }
        }
        else {
            $userinstitutions[] = 'mahara';
        }
        $cdefs = self::calculate_webservice_connections($userinstitutions);

        $connections = array();
        foreach ($cdefs as $c) {
            $client = null;
            $auth = array();
            $authtype = null;
            if (!empty($c->token)) {
                $authtype = 'token';
                if ($c->useheader) {
                    $auth['header'] = (empty($c->header) ? 'Authorization' : $c->header . ": " . $c->token);
                }
                else {
                    if (strpos($c->token, '=')) {
                        list($k, $v) = explode('=', $c->token);
                        $auth[$k] = $v;
                    }
                    else {
                        $auth['wstoken'] = $c->token;
                    }
                }
            }
            else if (!empty($c->username) && !empty($c->password) ) {
                $authtype = 'user';
                if (strpos($c->username, '=')) {
                    list($k, $v) = explode('=', $c->token);
                    $auth[$k] = $v;
                }
                else {
                    $auth['wsusername'] = $c->username;
                }
                if (strpos($c->password, '=')) {
                    list($k, $v) = explode('=', $c->password);
                    $auth[$k] = $v;
                }
                else {
                    $auth['wspassword'] = $c->password;
                }
            }

            // other static parameters - one per line
            if (!empty($c->parameters)) {
                $params = explode("\n", $c->parameters);
                foreach ($params as $p) {
                    if (strpos($p, '=')) {
                        list($k, $v) = explode('=', $p);
                        $auth[$k] = $v;
                    }
                }
            }

            switch ($c->type) {
                case WEBSERVICE_TYPE_SOAP:
                    require_once(get_config('docroot') . "webservice/soap/lib.php");
                    libxml_disable_entity_loader(true);
                    if ($c->authtype == WEBSERVICE_AUTH_WSSE) {
                        //force SOAP synchronous mode
                        $client = new webservice_soap_client($c->url,
                                          $auth,
                                          array("features" => SOAP_WAIT_ONE_WAY_CALLS,
                                                'stream_context' => webservice_create_context($c->url),));
                        //when function return null
                        $wsseSoapClient = new webservice_soap_client_wsse(array($client, '_doRequest'), $client->wsdlfile, $client->getOptions());
                        $wsseSoapClient->__setUsernameToken($c->username, $c->password);
                        $client->setSoapClient($wsseSoapClient);
                    }
                    else {
                        //force SOAP synchronous mode
                        $client = new webservice_soap_client($c->url,
                                        $auth,
                                        array("features" => SOAP_WAIT_ONE_WAY_CALLS,
                                              'stream_context' => webservice_create_context($c->url),));
                    }
                    $client->setWsdlCache(false);
                    break;

                case WEBSERVICE_TYPE_XMLRPC:
                    require_once(get_config('docroot') . "webservice/xmlrpc/lib.php");
                    $client = new webservice_xmlrpc_client($c->url, $auth);
                    if ($c->authtype == WEBSERVICE_AUTH_CERT) {
                        $client->setCertificate($c->certificate);
                    }
                    break;

                case WEBSERVICE_TYPE_REST:
                    require_once(get_config('docroot') . "webservice/rest/lib.php");
                    if ($c->authtype == WEBSERVICE_TYPE_OAUTH1) {
                        $client = new webservice_rest_client($c->url, $auth, 'oauth', $c->json);
                        $client->set_2legged($c->consumer, $c->secret);
                    }
                    else {
                        $client = new webservice_rest_client($c->url, $auth, $c->authtype, $c->json);
                    }
                    break;

                default:
                    log_error("Unknown WEBSERVICE_TYPE: ".$c->type);
                    break;
            }
            if ($client) {
                $client->set_connection($c);
                $connections[]= $client;
            }
        }
        return $connections;
    }

    /**
     * This function will be run after every upgrade to the plugin.
     *
     * @param int $fromversion version upgrading from (or 0 if installing)
     * @return boolean to indicate whether upgrade was successful or not
     */
    public static function postinst($fromversion) {
        return true;
    }

    /**
     * Whether this plugin should show a config form on the Administration->Extensions screen.
     *
     * If you return true here, you will also need to define the following methods:
     * - get_config_options()
     * - [optional] validate_config_options($form, $values)
     * - save_config_options($form, $values)
     *
     * @return boolean
     */
    public static function has_config() {
        return false;
    }

    /**
     * If has_config() is true, this function should return a pieform array, which must at least
     * contain an "elements" list. This list does NOT need to contain a submit button, and it should not
     * contain any elements called "plugintype", "pluginname", "type", or "save".
     *
     * The form definition does NOT need to contain a successcallbac, validatecallback, or jsform setting.
     * If these are present, they'll be ignored.
     *
     * @return false|array
     */
    public static function get_config_options() {
        throw new SystemException("get_config_options not defined");
    }

    /**
     * If has_config() is true, this function will be used as the Pieform validation callback function.
     *
     * @param Pieform $form
     * @param array $values
     */
    public static function validate_config_options(Pieform $form, $values) {
    }

    /**
     * If has_config() is true, this function will be used as the Pieform success callback function
     * for the plugin's config form.
     *
     * @param Pieform $form
     * @param array $values
     */
    public static function save_config_options(Pieform $form, $values) {
        throw new SystemException("save_config_options not defined");
    }


    /**
     * This function returns a list of activities this plugin brings. (i.e. things that can
     * send notifications to users)
     *
     * It should return an array of objects. Each object should have these fields:
     * - name: The (internal) name of the activity type
     * - defaultmethod: The default notification to be used for this activity
     * - admin
     * - delay
     * - allowonemethod
     * - onlyapplyifwatched
     *
     * These fields correspond directly with the columns in the "activity_type" table.
     *
     * You must also implement in the plugin's lib.php file an ActivityTypePlugin subclass whose name
     * matches the pattern ActivityType{$Plugintype}{$Pluginname}{$ActivityName}. For instance,
     * ActivityTypeInteractionForumNewpost.
     *
     * @return array
     */
    public static function get_activity_types() {
        return array();
    }

    /**
     * Indicates whether this plugin can be disabled.
     *
     * All internal type plugins, and ones in which Mahara won't work should override this.
     * Probably at least one plugin per plugin-type should override this.
     */
    public static function can_be_disabled() {
        return true;
    }

    /**
     * Can be overridden by plugins to assert when they are able to be used.
     * For example, a plugin might check that a certain PHP extension is loaded
     */
    public static function is_usable() {
        return true;
    }

    /**
     * Check whether this plugin is okay to be installed.
     *
     * To prevent installation, throw an InstallationException
     *
     * @throws InstallationException
     */
    public static function sanity_check() {
    }

    /**
     * The relative path for this plugin's stuff in theme directories.
     *
     * @param string $pluginname The middle part in a dwoo reference, i.e. in "export:html/file:index.tpl", it's the "html/file".
     * @return string
     */
    public static function get_theme_path($pluginname) {
        return static::get_plugintype_name() . '/' . $pluginname;
    }


    /**
     * Get institution preference page settings for current artefact.
     * @param Institution $institution
     * @return array of form elements
     */
    public static function get_institutionprefs_elements(Institution $institution = null) {
        return array();
    }

    /**
     * Validate institution settings values.
     * @param Pieform $form
     * @param array $values
     */
    public static function institutionprefs_validate(Pieform $form, $values) {
        return;
    }

    /**
     * Submit institution settings values.
     * @param Pieform $form
     * @param array $values
     * @param Institution $institution
     */
    public static function institutionprefs_submit(Pieform $form, $values, Institution $institution) {
        return;
    }

    /**
     * Get user preference page settings for current artefact.
     * @param stdClass $prefs Saved preferences
     * @return array of form elements
     */
    public static function get_accountprefs_elements(stdClass $prefs) {
        return array();
    }

    /**
     * Validate account settings values.
     * @param Pieform $form
     * @param array $values
     */
    public static function accountprefs_validate(Pieform $form, $values) {
        return;
    }

    /**
     * Submit account settings values.
     * @param Pieform $form
     * @param array $values
     */
    public static function accountprefs_submit(Pieform $form, $values) {
        return;
    }

    /**
     * Is plugin deprecated - going to be obsolete / removed
     * @return bool
     */
    public static function is_deprecated() {
        return false;
    }

    /**
     * Fetch plugin's display name rather than plugin name that is based on dir name.
     * @return $tring or null
     */
    public static function get_plugin_display_name() {
        return null;
    }
}

/**
 * formats a unix timestamp to a nice date format.
 *
 * @param int $date unix timestamp to format
 * @param string $formatkey language key to fetch the format from
 * @param string $notspecifiedkey (optional) language key to fetch 'not specified string' from
 * @param string $notspecifiedsection (optional) language section to fetch 'not specified string' from
 * (see langconfig.php or the top of {@link get_string_location}
 * for supported keys
 */
function format_date($date, $formatkey='strftimedatetime', $notspecifiedkey='strftimenotspecified', $notspecifiedsection='mahara') {
    if (empty($date)) {
        return get_string($notspecifiedkey, $notspecifiedsection);
    }
    if (strtoupper(substr(PHP_OS, 0, 3)) == 'WIN') {
        $fixedkey = str_replace('%e', '%#d', get_string($formatkey));
        $fixedkey = str_replace('%l', '%#I', $fixedkey);
        return strftime($fixedkey, $date);
    }
    return strftime(get_string($formatkey), $date);
}

/**
 * Formats the difference of two unix timestamps as time lapsed.
 *
 * @param int $timestamp1 Older unix timestamp to compare
 * @param int $timestamp2 Newer unix timestamp or current time if not supplied
 *
 * @return formatted time difference or false
 */
function format_timelapse($timestamp1, $timestamp2 = NULL) {
    if (!is_numeric($timestamp2)) {
        $timestamp2 = time();
    }

    $datetime1 = date_create_from_format('U', $timestamp2);
    $datetime2 = date_create_from_format('U', $timestamp1); // a timestamp to test against first timestamp
    $interval = date_diff($datetime1, $datetime2);

    if ($interval->invert == 0 && $interval->s != 0) {
        // We are in the future so exit
        return false;
    }
    else if ($interval->invert == 0) {
        // We are at exact current time - this can happen when adding something
        // so we will make it 1 sec in the past for display purposes
        $interval->s = 1;
    }

    if ($interval->days < 1) {
        if ($interval->h != 0) {
            if ($interval->h < 2) {
                return get_string('timelapsestringhour', 'mahara', $interval->i, $interval->h, $interval->i);
            }
            else {
                return get_string('timelapsestringhours', 'mahara', $interval->i, $interval->h, $interval->i);
            }
        }
        else if ($interval->i != 0) {
            return get_string('timelapsestringminute', 'mahara', $interval->i, $interval->i);
        }
        else {
            return get_string('timelapsestringseconds', 'mahara', $interval->s, $interval->s);
        }
    }
    return false;
}

/**
 * Returns a random string suitable for registration/change password requests
 *
 * @param int $length The length of the key to return
 * @param array $pool The pool to draw from (optional, will use A-Za-z0-9 as a default)
 * @return string
 */
function get_random_key($length=16, $pool=null) {
    if ($length < 1) {
        throw new IllegalArgumentException('Length must be a positive number');
    }
    if (empty($pool)) {
        $pool = array_merge(
            range('A', 'Z'),
            range('a', 'z'),
            range(0, 9)
        );
    }
    shuffle($pool);
    $result = '';
    for ($i = 0; $i < $length; $i++) {
        $result .= $pool[$i];
    }
    return $result;
}


//
// Pieform related functions
//

/**
 * Configures a default form
 */
function pieform_configure() {
    global $USER, $THEME;
    $renderer = $THEME->formrenderer;
    return array(
        'method'    => 'post',
        'action'    => '',
        'language'  => current_language(),
        'autofocus' => true,
        'renderer'  => $renderer,
        'requiredmarker' => '*',
        'oneofmarker' => '#',
        'elementclasses' => true,
        'descriptionintwocells' => true,
        'jsdirectory'    => get_config('wwwroot') . 'lib/pieforms/static/core/',
        'replycallback'  => 'pieform_reply',
        'jserrorcallback'       => 'formError',
        'globaljserrorcallback' => 'formGlobalError',
        'jssuccesscallback'     => 'formSuccess',
        'presubmitcallback'     => 'formStartProcessing',
        'postsubmitcallback'    => 'formStopProcessing',
        'jserrormessage' => get_string('errorprocessingform'),
        'errormessage' => get_string('errorprocessingform'),
        'configdirs' => get_config('libroot') . 'form/',
        'helpcallback' => 'pieform_get_help',
        'elements'   => array(
            'sesskey' => array(
                'type'    => 'hidden',
                'sesskey' => true,
                'value'   => $USER->get('sesskey')
            )
        )
    );
}

function form_validate($sesskey) {
    global $USER;
    if (is_null($sesskey)) {
        throw new UserException('No session key');
    }
    if ($USER && $USER->is_logged_in() && $USER->get('sesskey') != $sesskey) {
        throw new UserException('Invalid session key');
    }

    // Check to make sure the user has not been suspended or deleted, so that they cannot
    // perform any action
    if ($USER) {
        $record = get_record_sql('SELECT suspendedctime, suspendedreason, deleted
            FROM {usr}
            WHERE id = ?', array($USER->get('id')));
        if ($record) {
            if ($record->suspendedctime) {
                throw new UserException(get_string('accountsuspended', 'mahara', $record->suspendedctime, $record->suspendedreason));
            }
            if ($record->deleted) {
                $USER->logout();
                throw new AccessDeniedException(get_string('accountdeleted', 'mahara'));
            }
        }
    }
}

function pieform_validate(Pieform $form, $values) {
    if (!isset($values['sesskey'])) {
        throw new UserException('No session key');
    }
    form_validate($values['sesskey']);
}

function pieform_reply($code, $data) {
    global $SESSION;
    if (isset($data['message'])) {
        if ($code == PIEFORM_ERR) {
            $SESSION->add_error_msg($data['message']);
        }
        else {
            $SESSION->add_ok_msg($data['message']);
        }
    }
    if (isset($data['goto'])) {
        redirect($data['goto']);
    }
    // NOT explicitly exiting here. Pieforms will throw an exception which will
    // force the user to fix their form
}

function pieform_element_textarea_configure($element) {
    if (!array_key_exists('resizable', $element)) {
        $element['resizable'] = true;
    }
    return $element;
}

/**
 * Should be used to provide the 'templatedir' directive to pieforms using a
 * template for layout.
 *
 * @param string $file The file to be used as a pieform template, e.g.
 *                     "admin/site/files.php". This is the value you used as
 *                     the 'template' option for your pieform
 * @param string $pluginlocation Which plugin to search for the template, e.g.
 *                               artefact/file
 */
function pieform_template_dir($file, $pluginlocation='') {
    global $THEME;

    $filepath = get_config('docroot') . 'local/theme/pieforms/' . $file;
    if (is_readable($filepath)) {
        return dirname($filepath);
    }

    foreach ($THEME->inheritance as $themedir) {
        // Check under the theme directory first
        $filepath = get_config('docroot') . 'theme/' . $themedir . '/plugintype/' . $pluginlocation . '/pieforms/' . $file;
        if (is_readable($filepath)) {
            return dirname($filepath);
        }
        // Then check under the plugin directory
        $filepath = get_config('docroot') . $pluginlocation . '/theme/' . $themedir . '/pieforms/' . $file;
        if (is_readable($filepath)) {
            return dirname($filepath);
        }
    }
    throw new SystemException('No pieform template available: ' . $file);
}

/**
 * Given a view id, and a user id (defaults to currently logged in user if not
 * specified) will return wether this user is allowed to look at this view.
 *
 * @param mixed $view           viewid or View to check
 * @param integer $user_id      User trying to look at the view (defaults to
 * currently logged in user, or null if user isn't logged in)
 *
 * @returns boolean Wether the specified user can look at the specified view.
 */
function can_view_view($view, $user_id=null) {
    global $USER, $SESSION;

    if (defined('BULKEXPORT')) {
        return true;
    }

    $now = time();
    $dbnow = db_format_timestamp($now);

    if ($user_id === null) {
        $user = $USER;
        $user_id = $USER->get('id');
    }
    else {
        $user = new User();
        if ($user_id) {
            try {
                $user->find_by_id($user_id);
            }
            catch (AuthUnknownUserException $e) {}
        }
    }

    $publicviews = get_config('allowpublicviews');
    $publicprofiles = get_config('allowpublicprofiles');

    // If the user is logged out and the publicviews & publicprofiles sitewide configs are false,
    // we can deny access without having to hit the database at all
    if (!$user_id && !$publicviews && !$publicprofiles) {
        return false;
    }

    require_once(get_config('libroot') . 'view.php');
    if ($view instanceof View) {
        $view_id = $view->get('id');
    }
    else {
        $view = new View($view_id = $view);
    }

    // If the page belongs to an individual, check for individual-specific overrides
    if ($view->get('owner')) {

        $ownerobj = $view->get_owner_object();

        // If the view's owner is suspended, deny access to the view
        if (empty($ownerobj) || $ownerobj->get('suspendedctime')) {
            return false;
        }

        // Probationary user (no public pages or profiles)
        // (setting these here instead of doing a return-false, so that we can do checks for
        // logged-in users later)
        require_once(get_config('libroot') . 'antispam.php');
        $onprobation = is_probationary_user($ownerobj);
        $publicviews = $publicviews && !$onprobation;
        $publicprofiles = $publicprofiles && !$onprobation;

        // Member of an institution that prohibits public pages
        // (group views and logged in users are not affected by
        // the institution level config for public views)
        $publicviews = $publicviews && $ownerobj->institution_allows_public_views();
    }

    // Now that we've examined the page owner, check again for whether it can be viewed by a logged-out user
    if (!$user_id && !$publicviews && !$publicprofiles) {
        return false;
    }

    if ($user_id && $user->can_edit_view($view)) {
        return true;
    }

    if ($SESSION->get('mnetuser')) {
        // TODO: The mviewaccess cookie is used by the old token-based Mahara assignment submission
        // access system, which is now deprecated. Remove eventually.
        $mnettoken = get_cookie('mviewaccess:' . $view_id);

        // On the other hand, the $SESSION 'mnetviews' field is used by the NEW system, so don't
        // delete this!
        if (is_array($SESSION->get('mnetviews')) && in_array($view_id, $SESSION->get('mnetviews'))) {
            return true;
        }
    }

    // If the page has been marked "objectionable" admins should be able to view
    // it for review purposes.
    if ($view->is_objectionable()) {
        if ($owner = $view->get('owner')) {
            if ($user->is_admin_for_user($owner)) {
                return true;
            }
        }
        else if ($view->get('group') && $user->get('admin')) {
            return true;
        }

        $params = array('view', $view->get('id'));
        $suspended = record_exists_select('objectionable', 'objecttype = ? AND objectid = ? AND suspended = 1', $params);
        if ($suspended) {
            return false;
        }
    }


    // Overriding start/stop dates are set by the owner to deny access
    // to users who would otherwise be allowed to see the view.  However,
    // for some kinds of access (e.g. objectionable content, submitted
    // views), we have to override the override and let the logged in
    // user see it anyway.  So we can't return false now, we have to wait
    // till we find out what kind of view_access record is being used.
    $overridestart = $view->get('startdate');
    $overridestop = $view->get('stopdate');
    $allowedbyoverride = (empty($overridestart) || $overridestart < $dbnow) && (empty($overridestop) || $overridestop > $dbnow);

    $access = View::user_access_records($view_id, $user_id);
    if (empty($access)) {
        return false;
    }

    foreach ($access as &$a) {
        if ($a->accesstype == 'public' && $allowedbyoverride) {
            if ($publicviews) {
                return true;
            }
            else if ($view->get('type') == 'grouphomepage') {
                // If the group is public then the homepage should be available
                return true;
            }
            else if ($publicprofiles && $view->get('type') == 'profile') {
                return true;
            }
        }
        else if ($a->token && ($allowedbyoverride || !$a->visible)) {
            $usertoken = get_cookie('viewaccess:'.$view_id);
            if ($a->token == $usertoken && $publicviews) {
                return true;
            }
            // TODO: This section is used by the old token-based Mahara assignment submission
            // access system, which is now deprecated. Remove eventually.
            if (!empty($mnettoken) && $a->token == $mnettoken) {
                $mnetviewlist = $SESSION->get('mnetviewaccess');
                if (empty($mnetviewlist)) {
                    $mnetviewlist = array();
                }
                $mnetviewlist[$view_id] = true;
                $SESSION->set('mnetviewaccess', $mnetviewlist);
                return true;
            }
            // Don't bother to pull the collection out unless the user actually
            // has some collection access cookies.
            if ($ctokens = get_cookies('caccess:')) {
                $cid = $view->collection_id();
                if ($cid && isset($ctokens[$cid]) && $a->token == $ctokens[$cid]) {
                    return true;
                }
            }
        }
        else if ($user_id) {
            if ($a->accesstype == 'friends') {
                $owner = $view->get('owner');
                if (!get_field_sql('
                    SELECT COUNT(*) FROM {usr_friend} f WHERE (usr1=? AND usr2=?) OR (usr1=? AND usr2=?)',
                    array($owner, $user_id, $user_id, $owner)
                )) {
                    continue;
                }
            }
            else if ($a->institution) {
                // Check if user belongs to the allowed institution
                if (!in_array($a->institution, array_keys($user->get('institutions')))) {
                    continue;
                }
            }
            if (!$allowedbyoverride && $a->visible) {
                continue;
            }
            // The view must have loggedin access, user access for the user
            // or group/role access for one of the user's groups
            return true;
        }
    }

    return false;
}


/**
 * Return the view associated with a given token, and set the
 * appropriate access cookie.
 *
 * @param int $token the token string to check on
 * @param bool $visible Switch between setting an mnet or secreturl cookie
 *
 * @return object Containing viewid,        // the id of first view
                             collectionid,  // the id of collection (if exists)
                             gotomatrix     // go to the collection matrix page on first arrival
 */
function get_view_from_token($token, $visible=true) {
    // Set up object to return
    $result = new stdClass();
    $result->viewid = null;
    $result->collectionid = null;
    $result->gotomatrix = false;

    if (!$token) {
        return $result;
    }
    $viewids = get_column_sql('
        SELECT "view"
        FROM {view_access}
        WHERE token = ? AND visible = ?
            AND (startdate IS NULL OR startdate < current_timestamp)
            AND (stopdate IS NULL OR stopdate > current_timestamp)
        ORDER BY "view"
        ', array($token, (int)$visible)
    );
    if (empty($viewids)) {
        return $result;
    }

    if (count($viewids) > 1) {
        // if any of the views are in collection(s), either pick the view
        // with the lowest displayorder or if there is a matrix page go to that.
        $order = get_records_sql_array('
            SELECT cv.view, cv.collection, c.framework
            FROM {collection_view} cv
            JOIN {collection} c ON c.id = cv.collection
            WHERE cv.view IN (' . join(',', $viewids) . ')
            ORDER BY displayorder, collection',
            array()
        );
        if ($order) {
            if ($token != get_cookie('caccess:'.$order[0]->collection)) {
                if (!empty($order[0]->framework)) {
                    $result->gotomatrix = true;
                }
                set_cookie('caccess:'.$order[0]->collection, $token, 0, true);
            }
            $result->viewid = $order[0]->view;
            $result->collectionid = $order[0]->collection;
            return $result;
        }
    }
    $viewid = $viewids[0];
    if (!$visible && $token != get_cookie('mviewaccess:'.$viewid)) {
        set_cookie('mviewaccess:'.$viewid, $token, 0, true);
    }
    if ($visible && $token != get_cookie('viewaccess:'.$viewid)) {
        set_cookie('viewaccess:'.$viewid, $token, 0, true);
    }
    $result->viewid = $viewid;
    return $result;
}

/**
 * get the views that a user can see belonging
 * to the given users
 *
 * @param array $users users to fetch views owned by
 * @param int $userlooking (optional, defaults to logged in user)
 * @param int $limit grab this many views. (setting this null means get all)
 *
 * @return array Associative array keyed by userid, of arrays of view ids
 */
function get_views($users, $userlooking=null, $limit=5, $type=null) {
    $userlooking = optional_userid($userlooking);
    if (is_int($users)) {
        $users = array($users);
    }

    $list = array();

    if(count($users) == 0) {
        return $list;
    }

    $users = array_flip($users);

    $dbnow  = db_format_timestamp(time());

    if ($friends = get_records_sql_array(
        'SELECT
            CASE WHEN usr1=? THEN usr2 ELSE usr1 END AS id
        FROM
            {usr_friend} f
        WHERE
            ( usr1=? AND usr2 IN (' . join(',',array_map('db_quote', array_keys($users))) . ') )
            OR
            ( usr2=? AND usr1 IN (' . join(',',array_map('db_quote', array_keys($users))) . ') )
        ',
        array($userlooking,$userlooking,$userlooking)
    )) {
        foreach ( $friends as $user_id ) {
            $users[$user_id->id] = 'friend';
        }
    }

    if (is_null($type)) {
        $typesql = "AND v.type != 'profile'";
    }
    else {
        $typesql = 'AND v.type = ' . db_quote($type);
    }

    $data = array();
    $done = false;

    // public, logged in, or friends' views
    if ($results = get_records_sql_assoc(
        'SELECT
            v.*,
            ' . db_format_tsfield('atime') . ',
            ' . db_format_tsfield('mtime') . ',
            ' . db_format_tsfield('v.ctime', 'ctime') . '
        FROM
            {view} v
            INNER JOIN {view_access} a ON
                v.id=a.view
                AND (
                    accesstype IN (\'public\',\'loggedin\')
            ' . (
                    count(preg_grep('/^friend$/', $users)) > 0
                    ?  'OR (
                            accesstype = \'friends\'
                            AND v.owner IN (' . join(',',array_map('db_quote', array_keys(preg_grep('/^friend$/', $users)))) . ')
                        )'
                    : ''
                )
            . '
                )
        WHERE
            v.owner IN (' . join(',',array_map('db_quote', array_keys($users))) . ')
            AND ( v.startdate IS NULL OR v.startdate < ? )
            AND ( v.stopdate IS NULL OR v.stopdate > ? )
        ' . $typesql,
        array( $dbnow, $dbnow )
        )
    ) {
        foreach ($results as $row) {
            $list[$row->owner][$row->id] = $row->id;
        }
        $data = $results;

        // bail if we've filled all users to the limit
        $done = _get_views_trim_list($list, $users, $limit, $data);
    }

    // check individual user access
    if (!$done && $results = get_records_sql_assoc(
        'SELECT
            v.*,
            ' . db_format_tsfield('atime') . ',
            ' . db_format_tsfield('mtime') . ',
            ' . db_format_tsfield('v.ctime', 'ctime') . '
        FROM
            {view} v
            INNER JOIN {view_access} a ON v.id=a.view AND a.usr=?
        WHERE
            v.owner IN (' . join(',',array_map('db_quote', array_keys($users))) . ')
            AND ( v.startdate IS NULL OR v.startdate < ? )
            AND ( v.stopdate IS NULL OR v.stopdate > ? )
        ' . $typesql,
        array($userlooking, $dbnow, $dbnow)
        )
    ) {
        foreach ($results as &$row) {
            $list[$row->owner][$row->id] = $row->id;
        }
        $data = array_merge($data, $results);

        // bail if we've filled all users to the limit
        $done = $done && _get_views_trim_list($list, $users, $limit, $data);
    }

    // check group access
    if (!$done && $results = get_records_sql_assoc(
        'SELECT
            v.*,
            ' . db_format_tsfield('v.atime','atime') . ',
            ' . db_format_tsfield('v.mtime','mtime') . ',
            ' . db_format_tsfield('v.ctime','ctime') . '
        FROM
            {view} v
            INNER JOIN {view_access} a ON v.id=a.view
            INNER JOIN {group_member} m ON m.group=a.group AND m.member=?
            INNER JOIN {group} g ON (g.id = a.group AND g.deleted = ?)
        WHERE
            v.owner IN (' . join(',',array_map('db_quote', array_keys($users))) . ')
            AND ( v.startdate IS NULL OR v.startdate < ? )
            AND ( v.stopdate IS NULL OR v.stopdate > ? )
        ' . $typesql,
        array($userlooking, 0, $dbnow, $dbnow)
        )
    ) {
        foreach ($results as &$row) {
            $list[$row->owner][$row->id] = $row->id;
        }
        $data = array_merge($data, $results);

        // bail if we've filled all users to the limit
        $done = $done && _get_views_trim_list($list, $users, $limit, $data);
    }

    require_once('view.php');
    View::get_extra_view_info($data, false, false);

    $list = array();

    foreach ($data as $d) {
        $list[$d['owner']][$d['id']] = (object)$d;
    }

    return $list;
}

function _get_views_trim_list(&$list, &$users, $limit, &$results) {
    if ($limit === null) {
        return;
    }
    foreach ($list as $user_id => &$views) {
        if($limit and count($views) > $limit) {
            foreach (array_slice($views, $limit) as $v) {
                unset($results[$v]);
            }
            $views = array_slice($views, 0, $limit);
        }
        if($limit and count($views) == $limit) {
            unset($users[$user_id]);
        }
    }
    if (count($users) == 0) {
        return true;
    }
    return false;
}

/**
 * Given a view id will return wether this view is suspended or not.
 *
 * @param mixed $view           viewid or View to check
 * @param bool  $artefacts      Whether to check if there are suspended artefacts on the view.
 *                              If there are any then the view is treated as suspended.
 *
 * @returns boolean Wether the specified view is suspended or not.
 */
function is_view_suspended($view, $artefacts=true) {
    require_once(get_config('libroot') . 'view.php');
    if ($view instanceof View) {
        $viewid = $view->get('id');
    }
    else {
        $viewid = $view;
    }

    if ($artefacts) {
        return get_field_sql("
            SELECT SUM(suspended) FROM (
                SELECT id, suspended FROM {objectionable}
                WHERE objecttype = 'view' AND objectid = ?
                AND resolvedtime IS NULL
                UNION
                SELECT o.id, suspended FROM {objectionable} o
                JOIN {view_artefact} va ON va.artefact = o.objectid
                WHERE objecttype = 'artefact' AND va.view = ?
                AND resolvedtime IS NULL
            ) AS foo
        ", array($viewid, $viewid));
    }
    else {
        return get_field_sql("
            SELECT suspended FROM {objectionable}
            WHERE objecttype = 'view' AND objectid = ? AND resolvedtime IS NULL
        ", array($viewid));
    }
}
/**
 * Checks if artefact was in a previous version of the view
 *
 * @param int|object $artefact ID of an artefact or object itself.
 *                   Will load object if ID is supplied.
 * @param int $view ID of a page that contains artefact.
 *
 * @return boolean True if artefact is in previous version of view, False otherwise.
 */
function artefact_in_view_version($artefact, $view) {
    if (!is_object($artefact)) {
        require_once(get_config('docroot') . 'artefact/lib.php');
        $artefact = artefact_instance_from_id($artefact);
    }
    // First check if in current view
    if (artefact_in_view($artefact, $view)) {
        return true;
    }
    // If not in current view then lets check the older versions
    $db_version = get_db_version();
    if (is_postgres() && version_compare($db_version, '9.2.0', '>=')) {
        // We can check direct on the json data
        return get_records_sql_array("SELECT id FROM {view_versioning} v, JSON_ARRAY_ELEMENTS(CAST(v.blockdata AS JSON)->'blocks') obj
                                   WHERE (? = ANY(TRANSLATE(obj->'configdata'->>'artefactids','[]','{}')::int[]) OR
                                          obj->'configdata'->>'artefactid' = ?)
                                   AND view = ?", array($artefact->get('id'), $artefact->get('id'), $view));
    }
    else if (is_mysql() && mysql_get_type() == 'mysql' && version_compare($db_version, '8.0.0', '>=')) {
        // Note: we can't translate the array string to an array yet so we need to do a regexp match instead
        $mysqlregex = '\\\[' . $artefact->get('id') . ',|\\\s' . $artefact->get('id') . ',|\\\s' . $artefact->get('id') . '\\\]';
        return get_records_Sql_array("SELECT id FROM {view_versioning} v WHERE view = ?
                                      AND (REGEXP_LIKE(JSON_EXTRACT( CAST(v.blockdata AS JSON), '$.blocks[*].configdata.artefactid'), '" . $mysqlregex . "')
                                        OR REGEXP_LIKE(JSON_EXTRACT( CAST(v.blockdata AS JSON), '$.blocks[*].configdata.artefactids'), '" . $mysqlregex . "')
                                      )", array($view));
    }

    // If we can't check direct on the json data We'll need to limit the results to those that possibly
    // contain the blockid and work back from most recent to make things faster
    if ($versions = get_records_sql_array("SELECT id, view, blockdata
                                       FROM {view_versioning}
                                       WHERE view = ? AND blockdata LIKE '%' || ? || '%'
                                       ORDER BY ctime DESC", array($view, $artefact->get('id')))) {
        foreach ($versions as $version) {
            $blockdata = json_decode($version->blockdata);
            if (isset($blockdata->blocks) && is_array($blockdata->blocks)) {
                foreach ($blockdata->blocks as $block) {
                    if (isset($block->configdata) && isset($block->configdata->artefactid) && $block->configdata->artefactid == $artefact->get('id')) {
                        return true;
                    }
                    if (isset($block->configdata) && isset($block->configdata->artefactids) && in_array($artefact->get('id'), $block->configdata->artefactids)) {
                        return true;
                    }
                }
            }
        }
    }
    return false;
}

/**
 * Checks if artefact or at least one of its ancestors is in view
 *
 * @param int|object $artefact ID of an artefact or object itself.
 *                   Will load object if ID is supplied.
 * @param int $view ID of a page that contains artefact.
 *
 * @return boolean True if artefact is in view, False otherwise.
 */
function artefact_in_view($artefact, $view) {
    if (!is_object($artefact)) {
        require_once(get_config('docroot') . 'artefact/lib.php');
        $artefact = artefact_instance_from_id($artefact);
    }

    $ancestors = $artefact->get_item_ancestors();

    $params = array($view, $artefact->get('id'), $artefact->get('id'));
    $extrasql = '';
    if ($ancestors) {
        $extrasql = "SELECT a.parent
                FROM {view_artefact} top JOIN {artefact} a
                    ON a.parent = top.artefact
                WHERE top.view = ? AND top.artefact IN (" . implode(',', $ancestors) . ")
                UNION";
        $params[] = $view;
    }

    $sql = "SELECT a.id
            FROM {view_artefact} a WHERE \"view\" = ? AND artefact = ?
            UNION
            SELECT aa.artefact
            FROM {artefact} a INNER JOIN {artefact_attachment} aa
                ON a.id = aa.artefact
            WHERE aa.attachment = ?
            UNION
            $extrasql
            SELECT s.id
            FROM {view} v INNER JOIN {skin} s
                ON v.skin = s.id
            WHERE v.id = ? AND ? in (s.bodybgimg, s.viewbgimg)
    ";
    $params = array_merge($params, array($view, $artefact->get('id')));

    return record_exists_sql($sql, $params);
}

function get_dir_contents($directory) {
    $contents = array();
    $dirhandle = opendir($directory);
    while (false !== ($dir = readdir($dirhandle))) {
        if (strpos($dir, '.') === 0) {
            continue;
        }
        $contents[] = $dir;
    }
    return $contents;
}

/**
 * Returns the subdirectory where mahara is installed, normally / but could
 * be something different on a shared host. Useful for setting cookie paths.
 *
 * @return string
 */
function get_mahara_install_subdirectory() {
    $path = parse_url(get_config('wwwroot'), PHP_URL_PATH);
    if (!strlen($path)) {
        return '/';
    }
    if (substr($path, -1) !== '/') {
        $path = $path . '/';
    }
    return $path;
}

/**
 *** get_performance_info() pairs up with init_performance_info()
 *** loaded in init.php. Returns an array with 'html' and 'txt'
 *** values ready for use, and each of the individual stats provided
 *** separately as well.
 ***
 **/
function get_performance_info() {

    if (!get_config('perftofoot') && !get_config('perftolog')) {
        return array();
    }

    global $PERF;

    $info = array();

    $info['realtime'] = microtime_diff($PERF->starttime, microtime());


    if (function_exists('memory_get_usage')) {
        $info['memory_total'] = memory_get_usage();
        $info['memory_growth'] = memory_get_usage() - $PERF->startmemory;
    }

    $inc = get_included_files();
    $info['includecount'] = count($inc);

    $info['dbreads'] = $PERF->dbreads;
    $info['dbwrites'] = $PERF->dbwrites;
    $info['dbcached'] = $PERF->dbcached;

    if (function_exists('posix_times')) {
        $ptimes = posix_times();
        if (is_array($ptimes)) {
            foreach ($ptimes as $key => $val) {
                $info[$key] = $ptimes[$key] -  $PERF->startposixtimes[$key];
            }
        }
    }

    // Grab the load average for the last minute
    // /proc will only work under some linux configurations
    // while uptime is there under MacOSX/Darwin and other unices
    if (is_readable('/proc/loadavg') && $loadavg = @file('/proc/loadavg')) {
        list($server_load) = explode(' ', $loadavg[0]);
        unset($loadavg);
    } else if ( function_exists('is_executable') && is_executable('/usr/bin/uptime') && $loadavg = `/usr/bin/uptime` ) {
        if (preg_match('/load averages?: (\d+[\.,:]\d+)/', $loadavg, $matches)) {
            $server_load = $matches[1];
        } else {
            log_debug('PERF: Could not parse uptime output!');
        }
    }
    if (!empty($server_load)) {
        $info['serverload'] = $server_load;
    }
    else {
        $info['serverload'] = 'unknown';
    }

    return $info;
}

function perf_to_log($info=null) {
    if (!get_config('perftolog')) {
        return true;
    }

    if (empty($info)) {
        $info = get_performance_info();
    }

    $logstring = 'PERF: ' .  strip_querystring(get_script_path()). ': ';
    if (isset($info['memory_total']) && isset($info['memory_growth'])) {
        $logstring .= ' memory_total: '.$info['memory_total'].'B (' . display_size($info['memory_total']).') memory_growth: '.$info['memory_growth'].'B ('.display_size($info['memory_growth']).')';
    }
    $logstring .= ' time: '.$info['realtime'].'s';
    $logstring .= ' includecount: '.$info['includecount'];
    $logstring .= ' dbqueries: '.$info['dbreads'] . ' reads, ' . $info['dbwrites'] . ' writes, ' . $info['dbcached'] . ' cached';
    if (isset($info['ticks']) && isset($info['utime']) && isset($info['stime']) && isset($info['cutime']) && isset($info['cstime'])) {
        $logstring .= ' ticks: ' . $info['ticks']  . ' user: ' . $info['utime'] . ' sys: ' . $info['stime'] .' cuser: ' . $info['cutime'] . ' csys: ' . $info['cstime'];
    }
    $logstring .= ' serverload: ' . $info['serverload'];
    log_debug($logstring);
}

/**
 * microtime_diff
 *
 * @param string $a ?
 * @param string $b ?
 * @return string
 * @todo Finish documenting this function
 */
function microtime_diff($a, $b) {
    list($a_dec, $a_sec) = explode(' ', $a);
    list($b_dec, $b_sec) = explode(' ', $b);
    return $b_sec - $a_sec + $b_dec - $a_dec;
}

/**
 * Function to raise the memory limit to a new value.
 * Will respect the memory limit if it is higher, thus allowing
 * settings in php.ini, apache conf or command line switches
 * to override it
 *
 * The memory limit should be expressed with a string (eg:'64M')
 *
 * @param string $newlimit the new memory limit
 * @return bool Whether we were able to raise the limit or not
 */
function raise_memory_limit ($newlimit) {
    if (empty($newlimit)) {
        return false;
    }

    $cur = @ini_get('memory_limit');
    if (empty($cur)) {
        // If php is compiled without --enable-memory-limits
        // apparently memory_limit is set to ''
        $cur=0;
    }
    else {
        if ($cur == -1){
            return true; // unlimited mem!
        }
        $cur = get_real_size($cur);
    }

    $new = get_real_size($newlimit);
    if ($new > $cur) {
        ini_set('memory_limit', $newlimit);
        return true;
    }
    return false;
}

/**
 * Function to raise the max execution time to a new value.
 * Will respect the time limit if it is higher, thus allowing
 * settings in php.ini, apache conf or command line switches
 * to override it
 *
 * @param int $newlimit the new max execution time limit (in seconds)
 * @return bool Whether we were able to raise the limit or not
 */
function raise_time_limit($newlimit) {
    if (empty($newlimit)) {
        return false;
    }
    $newlimit = intval($newlimit);
    $cur = @ini_get('max_execution_time');
    if (empty($cur)) {
        $cur = 0;
    }
    // Currently set as umlimited so don't change
    if ($cur == '0') {
        return false;
    }
    if ($newlimit > $cur) {
        // this won't work in safe mode - but we shouldn't be in safe mode
        // as that has been checked for already
        ini_set('max_execution_time', $newlimit);
        return true;
    }
    return false;
}

/**
 * Converts numbers like 10M into bytes.
 *
 * @param string $size The size to be converted
 * @return integer
 * @throws SystemException if the string does not have a valid suffix.
 *                         See the function definition for allowed suffixes.
 */
function get_real_size($size=0) {
    if (!$size) {
        return 0;
    }
    // If there is no suffix then assume bytes
    else if (is_numeric($size)) return (int)$size;

    $scan = array(
        'GB' => 1073741824,
        'Gb' => 1073741824,
        'G'  => 1073741824,
        'g'  => 1073741824,
        'MB' => 1048576,
        'Mb' => 1048576,
        'M'  => 1048576,
        'm'  => 1048576,
        'KB' => 1024,
        'Kb' => 1024,
        'K'  => 1024,
        'k'  => 1024,
    );

    $keys = array_keys($scan);
    foreach ($keys as $key) {
        if (strlen($size) > strlen($key) && substr($size, -strlen($key)) == $key) {
            $size = substr($size, 0, -strlen($key)) * $scan[$key];
            return $size;
        }
    }

    throw new SystemException('get_real_size called without valid size');
}

/**
 * Determines maximum upload size based on quota and PHP settings.
 *
 * @param  bool $is_user whether upload size should be evaluated for user (quota is considered)
 * @return integer
 */
function get_max_upload_size($is_user) {
    global $USER;
    if (!$postmaxsize = get_real_size(ini_get('post_max_size'))) {
        $maxuploadsize = get_real_size(ini_get('upload_max_filesize'));
    }
    else {
        $maxuploadsize = max(1024, min($postmaxsize - 4096, get_real_size(ini_get('upload_max_filesize'))));
    }
    if (get_config('maxuploadsize')) {
        $maxuploadsize = min($maxuploadsize, get_config('maxuploadsize'));
    }
    if ($is_user) {
        $userquotaremaining = $USER->get('quota') - $USER->get('quotaused');
        $maxuploadsize = min($maxuploadsize, $userquotaremaining);
    }
    return $maxuploadsize;
}

/**
 * Converts bytes into display form
 *
 * @param string $size  ?
 * @return string
 * @staticvar string $gb Localized string for size in gigabytes
 * @staticvar string $mb Localized string for size in megabytes
 * @staticvar string $kb Localized string for size in kilobytes
 * @staticvar string $b Localized string for size in bytes
 * @todo Finish documenting this function. Verify return type.
 */
function display_size($size) {

    static $gb, $mb, $kb, $b;

    if (empty($gb)) {
        $gb = get_string('sizegb');
        $mb = get_string('sizemb');
        $kb = get_string('sizekb');
        $b  = get_string('sizeb');
    }

    if ($size >= 1073741824) {
        $size = round($size / 1073741824 * 10) / 10 . $gb;
    } else if ($size >= 1048576) {
        $size = round($size / 1048576 * 10) / 10 . $mb;
    } else if ($size >= 1024) {
        $size = round($size / 1024 * 10) / 10 . $kb;
    } else {
        $size = round($size) .' '. $b;
    }
    return $size;
}

/**
 * creates the profile sideblock
 */
function profile_sideblock() {
    global $USER, $SESSION;

    if (!$USER->is_logged_in() || in_admin_section()) {
        return null;
    }

    safe_require('notification', 'internal');
    require_once('group.php');
    require_once('institution.php');
    $data = array(
        'id'          => $USER->get('id'),
        'myname'      => display_name($USER, null, true),
        'username'    => $USER->get('username'),
        'url'         => profile_url($USER),
        'profileiconurl' => get_config('wwwroot') . 'artefact/file/profileicons.php',
    );

    $authinstance = $SESSION->get('mnetuser') ? $SESSION->get('authinstance') : $USER->get('authinstance');
    if ($authinstance) {
        $authobj = AuthFactory::create($authinstance);
        if ($authobj->authname == 'xmlrpc') {
            $peer = get_peer($authobj->wwwroot);
            if ($SESSION->get('mnetuser')) {
                $data['mnetloggedinfrom'] = '<span class="icon left icon-external-link moodle-login"></span>' . get_string('youhaveloggedinfrom1', 'auth.xmlrpc', $authobj->wwwroot, $peer->name);
            }
            else {
                $data['peer'] = array('name' => $peer->name, 'wwwroot' => $peer->wwwroot);
            }
        }
    }
    $invitedgroups = get_records_sql_array('SELECT g.*, gmi.ctime, gmi.reason
             FROM {group} g
             JOIN {group_member_invite} gmi ON gmi.group = g.id
             WHERE gmi.member = ? AND g.deleted = ?', array($USER->get('id'), 0));
    $data['invitedgroups'] = $invitedgroups ? count($invitedgroups) : 0;
    $data['invitedgroupsmessage'] = $data['invitedgroups'] == 1 ? get_string('invitedgroup') : get_string('invitedgroups');
    $data['pendingfriends'] = count_records('usr_friend_request', 'owner', $USER->get('id'));
    $data['pendingfriendsmessage'] = $data['pendingfriends'] == 1 ? get_string('pendingfriend') : get_string('pendingfriends');
    // Check if we want to limit the displayed groups by the account setting
    $limitto = null;
    $limit = $USER->get_account_preference('groupsideblockmaxgroups');
    if (isset($limit) && is_numeric($limit)) {
        $limitto = intval($limit);
    }
    $sort = null;
    if ($sortorder = $USER->get_account_preference('groupsideblocksortby')) {
        $sort = $sortorder;
    }
    if ($limitto === null) {
        $data['groups'] = group_get_user_groups($USER->get('id'), null, $sort);
        $total = count($data['groups']);
    }
    else if ($limitto === 0) {
        $data['groups'] = null;
    }
    else {
        list($data['groups'], $total) = group_get_user_groups($USER->get('id'), null, $sort, $limitto);
    }
    $limitstr = '';
    if (!empty($limitto) && $limitto < $total) {
        switch ($sort) {
            case 'earliest':
                $limitstr = get_string('numberofmygroupsshowingearliest', 'blocktype.mygroups', $limitto, $total);
                break;
            case 'latest':
                $limitstr = get_string('numberofmygroupsshowinglatest', 'blocktype.mygroups', $limitto, $total);
                break;
            default:
                $limitstr = get_string('numberofmygroupsshowing', 'blocktype.mygroups', $limitto, $total);
                break;
        }
    }

    $typecast = is_postgres() ? '::varchar' : '';
    $data['grouplimitstr'] = $limitstr;
    $data['views'] = get_records_sql_array(
        "SELECT v.id, v.title, v.urlid, v.owner
         FROM {view} v
         INNER JOIN {tag} vt ON (vt.tag = ? AND vt.resourcetype = 'view' AND vt.resourceid = v.id" . $typecast . ")
         WHERE v.owner = ?
         ORDER BY v.title",
        array(get_string('profile'), $USER->get('id'))
    );
    if ($data['views']) {
        require_once('view.php');
        foreach($data['views'] as $v) {
            $view = new View(0, (array)$v);
            $view->set('dirty', false);
            $v->fullurl = $view->get_url();
        }
    }
    $data['artefacts'] = get_records_sql_array(
        "SELECT a.id, a.artefacttype, a.title
         FROM {artefact} a
         INNER JOIN {tag} at ON (at.tag = ? AND at.resourcetype = 'artefact' AND at.resourceid = a.id" . $typecast . ")
         WHERE a.owner = ?
         ORDER BY a.title",
        array(get_string('profile'), $USER->get('id'))
    );
    if (!empty($data['artefacts'])) {
        // check if we have any blogposts and fetch their blog id if we do
        foreach ($data['artefacts'] as $key => $value) {
            if ($value->artefacttype == 'blogpost') {
                $value->blogid = get_field('artefact', 'parent', 'id', $value->id);
            }
        }
    }

    $sideblock = array(
        'name'   => 'profile',
        'weight' => -20,
        'id'     => 'sb-profile',
        'data'   => $data,
        'class' => 'user-card',
        'template' => 'sideblocks/profile.tpl',
        'visible' => $USER->is_logged_in() && !in_admin_section(),
    );
    return $sideblock;
}

/**
 * Gets data about users who have been online in the last while.
 *
 * The time is configured by setting the 'accessidletimeout' configuration
 * option.
 *
 * Limits the number of users to display based on the 'onlineuserssideblockmaxusers'
 * site configuration option and the Institution specific 'showonlineusers' setting.
 * If the user belongs to no institution (other than the standard 'mahara' one) then
 * the decision will be to show ALL users by default.
 *
 */
function onlineusers_sideblock() {
    global $USER;

    if (!$USER->is_logged_in() || in_admin_section() || !get_config('showonlineuserssideblock')) {
        return null;
    }
    $maxonlineusers = get_config('onlineuserssideblockmaxusers');
    $results = get_onlineusers($maxonlineusers, 0, 'lastaccess DESC');

    if ($results['showusers'] == 0 || empty($results['count'])) {
        return null;
    }
    $onlineusers = $results['onlineusers'];

    $sideblock = array(
        'name'   => 'onlineusers',
        'id'     => 'sb-onlineusers',
        'weight' => -10,
        'data'   =>  array('users' => $onlineusers,
                           'count' => count($onlineusers),
                           'lastminutes' => floor(get_config('accessidletimeout') / 60),
                     ),
        'template' => 'sideblocks/onlineusers.tpl',
        'visible' => $USER->is_logged_in() && !in_admin_section(),
    );
    return $sideblock;
}

function tag_weight($freq) {
    return pow($freq, 2);
    // return log10($freq);
}

function get_my_tags($limit=null, $cloud=true, $sort='freq', $excludeinstitutiontags=false) {
    global $USER;
    if (get_config('version') < 2018061801) {
        // we are in upgrade before the table exists
        return false;
    }
    $id = $USER->get('id');
    if ($limit || $sort != 'alpha') {
        $sort = 'COUNT(1) DESC';  // In this instance '1' is not a number but the column reference to 'tag' column
    }
    else {
        $sort = '1 ASC';
    }
    $excludeinstitutiontagssql = $excludeinstitutiontags ? " AND t.tag NOT LIKE 'tagid_%'" : '';
    $typecast = is_postgres() ? '::varchar' : '';
    $tagrecords = get_records_sql_array("
        SELECT
            (CASE
                WHEN t.tag LIKE 'tagid_%' THEN CONCAT(i.displayname, ': ', t2.tag)
                ELSE t.tag
            END) AS tag, COUNT(t.tag) AS count
        FROM {tag} t
        LEFT JOIN {tag} t2 ON t2.id" . $typecast . " = SUBSTRING(t.tag, 7)
        LEFT JOIN {institution} i ON i.name = t2.ownerid
        WHERE t.resourcetype IN ('artefact', 'view', 'collection', 'blocktype')
        AND t.ownertype = 'user'
        AND t.ownerid = ?" . $excludeinstitutiontagssql . "
        GROUP BY 1
        ORDER BY " . $sort . (is_null($limit) ? '' : " LIMIT $limit"),
        array($id)
    );
    if (!$tagrecords) {
        return array();
    }
    if ($cloud) {
        $minfreq = $tagrecords[count($tagrecords) - 1]->count;
        $maxfreq = $tagrecords[0]->count;

        if ($minfreq != $maxfreq) {
            $minweight = tag_weight($minfreq);
            $maxweight = tag_weight($maxfreq);
            $minsize = 0.8;
            $maxsize = 2.5;

            foreach ($tagrecords as &$t) {
                $weight = (tag_weight($t->count) - $minweight) / ($maxweight - $minweight);
                $t->size = sprintf("%0.1f", $minsize + ($maxsize - $minsize) * $weight);
            }
        }
        usort($tagrecords, function($a, $b) { return strnatcasecmp($a->tag, $b->tag); });
    }
    foreach ($tagrecords as &$t) {
        $t->tagurl = urlencode($t->tag);
    }
    return $tagrecords;
}

function tags_sideblock() {
    global $USER;

    if (get_config('showtagssideblock')) {
        $maxtags = $USER->get_account_preference('tagssideblockmaxtags');
        $maxtags = is_null($maxtags) ? get_config('tagssideblockmaxtags') : $maxtags;

        $tags = null;
        if ($tagrecords = get_my_tags($maxtags)) {
            $tags = array('tags' => $tagrecords);
        }
        $sideblock = array(
            'name'   => 'tags',
            'id'     => 'sb-tags',
            'weight' => 0,
            'data'   => $tags,
            'template' => 'sideblocks/tags.tpl',
            'visible' => $USER->is_logged_in() &&
                        (defined('MENUITEM') &&
                         in_array(MENUITEM, array('profile',
                                                  'create/files',
                                                  'share/sharedbyme',
                                                  'create/views')
                                )
                        ),
        );
        return $sideblock;
    }
    return null;
}

/**
 * Get the string to display for a given progress bar item (in the sideblock).
 * eg: Upload 2 files
 * @param string $pluginname
 * @param string $artefacttype
 * @param int $target How many items need to be created
 * @param int $completed How many items have been created
 */
function progressbar_artefact_task_label($pluginname, $artefacttype, $target, $completed) {
    return call_user_func(generate_class_name('artefact', $pluginname) . '::progressbar_task_label', $artefacttype, $target, $completed);
}

/**
 * Get the link to link on a given progress bar item (in the sideblock)
 * @param string $pluginname
 * @param string $artefacttype
 */
function progressbar_artefact_link($pluginname, $artefacttype) {
    return call_user_func(generate_class_name('artefact', $pluginname) . '::progressbar_link', $artefacttype);
}

function progressbar_sideblock($preview=false) {
    global $USER;

    if (!$USER->is_logged_in()) {
        return null;
    }

    if (in_admin_section() && (!defined('SECTION_PAGE') || SECTION_PAGE != 'progressbar')) {
        return null;
    }

    if (!get_config('showprogressbar')) {
        return null;
    }

    // TODO: Remove this URL param from here, and when previewing pass institution
    // by function param instead
    $institution = param_alphanum('i', null);
    if (is_array($USER->institutions) && count($USER->institutions) > 0) {
        // Get all institutions where user is member
        $institutions = array();
        foreach ($USER->institutions as $inst) {
            if (empty($inst->suspended)) {
                $institutions = array_merge($institutions, array($inst->institution => $inst->displayname));
            }
        }
        // Set user's first institution in case that institution isn't
        // set yet or user is not member of currently set institution.
        if (!$institution || !array_key_exists($institution, $institutions)) {
            $institution = get_institution_by_current_theme();
        }
    }
    else {
        $institutions = array();
        $institution = 'mahara';
    }

    // Set appropriate preview according to institution, if the institutio is selected
    // If the institution isn't selected then show preview for first institution, which
    // is also selected as a default value in institution selection box
    if ($preview) {
        $default = get_column('institution', 'name');
        // TODO: Remove this URL param from here, and when previewing pass institution
        // by function param instead
        $institution = param_alphanum('institution', $default[0]);
    }
    // We need to check to see if any of the institutions have profile completeness to allow
    // the select box to work correctly for users with more than one institution
    $multiinstitutionprogress = false;
    $counting = null;
    if (!empty($institutions)) {
        foreach ($institutions as $key => $value) {
            if ($result = get_records_select_assoc('institution_config', 'institution=? and field like \'progressbaritem_%\'', array($key), 'field', 'field, value')) {
                $multiinstitutionprogress = true;
                if ($key == $institution) {
                    $counting = $result;
                    break;
                }
            }
        }
    }
    else {
        $counting = get_records_select_assoc('institution_config', 'institution=? and field like \'progressbaritem_%\'', array($institution), 'field', 'field, value');
    }

    // Get artefacts that count towards profile completeness
    if ($counting) {
        // Without locked ones (site locked and institution locked)
        $sitelocked = (array) get_column('institution_locked_profile_field', 'profilefield', 'name', 'mahara');
        $instlocked = (array) get_column('institution_locked_profile_field', 'profilefield', 'name', $institution);
        $locked = $sitelocked + $instlocked;
        foreach ($locked as $l) {
            unset($counting["progressbaritem_internal_{$l}"]);
        }

        $totalcounting = 0;
        foreach ($counting as $c) {
            $totalcounting = $totalcounting + $c->value;
        }

        // Get all artefacts for progressbar and create data structure
        $data = array();

        // For the artefact_get_progressbar_items function, we want them indexed by plugin
        // and then subindexed by artefact. For most other purposes, having them indexed
        // by config name is sufficient
        $onlytheseplugins = array();
        foreach($counting as $key => $obj) {
            // This one has no value. So remove it from the list.
            if (!($obj->value)) {
                unset($counting[$key]);
                continue;
            }
            $parts = explode('_', $obj->field);
            $plugin = $parts[1];
            $item = $parts[2];
            if (empty($onlytheseplugins[$plugin])) {
                $onlytheseplugins[$plugin] = array();
            }
            $onlytheseplugins[$plugin][$item] = $item;
        }
        require_once(get_config('docroot') . 'artefact/lib.php');
        $progressbaritems = artefact_get_progressbar_items($onlytheseplugins);

        // Get the data link about every item
        foreach ($progressbaritems as $pluginname => $itemlist) {
            foreach ($itemlist as $artefactname => $item) {
                $itemname = "progressbaritem_{$pluginname}_{$artefactname}";
                $c = $counting[$itemname];
                $target = $c->value;
                $completed = 0;
                $data[$itemname] = array(
                    'artefact'  => $artefactname,
                    'link'      => progressbar_artefact_link($pluginname,  $artefactname),
                    'counting'  => $target,
                    'completed' => $completed,
                    'display'   => ((bool) $c->value),
                    'label'     => progressbar_artefact_task_label($pluginname, $artefactname, $target, $completed),
                );
            }
        }

        if ($preview) {
            $percent = 0;
        }
        else {
            // Since this is not a preview, gather data about the users' actual progress,
            // and update the records we placed in $data.

            // Get a list of all the basic artefact types in this progress bar.
            $nonmeta = array();
            foreach($progressbaritems as $plugin=>$pluginitems) {
                foreach($pluginitems as $itemname=>$item) {
                    if (!$item->ismeta) {
                        $nonmeta[] = $itemname;
                    }
                }
            }

            if ($nonmeta) {
                // To reduce the number of queries, we gather data about all the user's artefacts
                // at once. (Metaartefacts are handled separately, below)
                $insql = "'" . implode("','", $nonmeta) . "'";
                $sql = "SELECT artefacttype, (select plugin from {artefact_installed_type} ait where ait.name=a.artefacttype) as plugin, COUNT(*) AS completed
                        FROM {artefact} a
                        WHERE owner = ?
                        AND artefacttype in ({$insql})
                        GROUP BY artefacttype";
                $normalartefacts = get_records_sql_array($sql, array($USER->get('id')));
                if (!$normalartefacts) {
                    $normalartefacts = array();
                }
            }
            else {
                // No basic artefacts in this one, so we just use an empty array for this.
                $normalartefacts = array();
            }
            $totalcompleted = 0;

            $metaartefacts = array();
            foreach ($progressbaritems as $plugin => $pluginitems) {
                if (is_array($records = artefact_get_progressbar_metaartefacts($plugin, $pluginitems))) {
                    foreach ($records as $record) {
                        $record->plugin = $plugin;
                        array_push($metaartefacts, $record);
                    }
                }
            }

            foreach (array_merge($normalartefacts, $metaartefacts) as $record) {
                $itemname = "progressbaritem_{$record->plugin}_{$record->artefacttype}";

                // It's not an item we're tracking, so skip it.
                if (!array_key_exists($itemname, $counting)) {
                    continue;
                }
                $target = $counting[$itemname]->value;
                $remaining = max(0, $target - $record->completed);

                // Override the data for this item
                $data[$itemname]['completed'] = $record->completed;
                $data[$itemname]['display'] = ($remaining > 0);
                $data[$itemname]['label'] = $label = get_string('progress_' . $record->artefacttype, 'artefact.' . $record->plugin, $remaining);

                if ($target > 0) {
                    $totalcompleted = $totalcompleted + min($target, $record->completed);
                }
            }

            $percent = round(($totalcompleted/$totalcounting)*100);
            if ($percent > 100) {
                $percent = 100;
            }
        }
        $blockdata = array(
            'data' => $data,
            'percent' => $percent,
            'preview' => $preview,
            'count' => ($preview ? 1 : count($institutions)),
            // This is important if user is member
            // of more than one institution ...
            'institutions' => $institutions,
            'institution' => $institution,
            'totalcompleted' => !empty($totalcompleted) ? $totalcompleted : 0,
            'totalcounting' => $totalcounting,
        );
    }
    else if ($multiinstitutionprogress) {
        $blockdata = array(
            'data' => null,
            'percent' => 0,
            'preview' => $preview,
            'count' => ($preview ? 1 : count($institutions)),
            // This is important if user is member
            // of more than one institution ...
            'institutions' => $institutions,
            'institution' => $institution,
            'totalcompleted' => 0,
            'totalcounting' => 0,
        );
    }
    else {
        $blockdata = array(
            'data' => null,
            'percent' => 0,
            'preview' => $preview,
            'count' => 1,
            'institutions' => null,
            'institution' => 'mahara',
        );
    }
    $blockname = $preview ? 'progressbar_preview' : 'progressbar';
    $sideblock = array(
        'name'   => $blockname,
        'id'     => 'sb-progressbar',
        'class'  => 'progressbar',
        'weight' => -8,
        'data'   => $blockdata,
        'template' => 'sideblocks/progressbar.tpl',
    );

    if ($preview) {
        // we are calling this via a page's extraconfig so will only exist on that page
        // so we can set visibility to true
        $sideblock['visible'] = true;
        return $sideblock;
    }
    else if ($USER->get_account_preference('showprogressbar')) {
        $sideblock['visible'] = !in_admin_section();
        return $sideblock;
    }
    else {
        return null;
    }
}

function quota_sideblock($group = false) {
    global $USER;

    $visible = false;
    if ($USER->is_logged_in() &&
        (defined('MENUITEM') && in_array(MENUITEM, array('create/files',
                                                         'profileicons'))
         ||
         defined('MENUITEM') && in_array(MENUITEM, array('engage/index',
                                                         'create/resume')) &&
         defined('MENUITEM_SUBPAGE') && in_array(MENUITEM_SUBPAGE, array('files',
                                                                         'goalsandskills'))  // for places that have arrowbar menu
         ||
         defined('SECTION_PAGE') && in_array(SECTION_PAGE, array('post',
                                                                'editnote'))
        )
    ) {
        $visible = true;
    }
    $template = $group ? 'sideblocks/groupquota.tpl' : 'sideblocks/quota.tpl';
    $sideblock = array(
        'name'   => 'quota',
        'weight' => -10,
        'data'   => array(), // worked out by the template
        'template' => $template,
        'visible' => $visible,
        'override' => $group,
    );
    return $sideblock;
}

/**
 * Cronjob to recalculate how much quota each user is using and update it as
 * appropriate.
 *
 * This gives a backstop for the possibility that there is a bug elsewhere that
 * has caused the quota count to get out of sync
 */
function recalculate_quota() {
    $plugins = plugins_installed('artefact', true);

    $userquotas = array();
    $groupquotas = array();

    foreach ($plugins as $plugin) {
        safe_require('artefact', $plugin->name);
        $classname = generate_class_name('artefact', $plugin->name);
        if (is_callable($classname . '::recalculate_quota')) {
            $pluginuserquotas = call_static_method($classname, 'recalculate_quota');
            foreach ($pluginuserquotas as $userid => $usage) {
                if (!isset($userquotas[$userid])) {
                    $userquotas[$userid] = $usage;
                }
                else {
                    $userquotas[$userid] += $usage;
                }
            }
        }
        if (is_callable($classname . '::recalculate_group_quota')) {
            $plugingroupquotas = call_static_method($classname, 'recalculate_group_quota');
            foreach ($plugingroupquotas as $groupid => $usage) {
                if (!isset($groupquotas[$groupid])) {
                    $groupquotas[$groupid] = $usage;
                }
                else {
                    $groupquotas[$groupid] += $usage;
                }
            }
        }
    }

    foreach ($userquotas as $user => $quota) {
        $data = (object) array(
            'quotaused' => $quota
        );
        $where = (object) array(
            'id' => $user
        );
        update_record('usr', $data, $where);
    }

    foreach ($groupquotas as $group => $quota) {
        $data = (object) array(
            'quotaused' => $quota
        );
        $where = (object) array(
            'id' => $group
        );
        update_record('group', $data, $where);
    }
}

/**
 * A cronjob to clean general internal activity notifications
 */
function cron_clean_internal_activity_notifications() {
    safe_require('notification', 'internal');
    PluginNotificationInternal::clean_notifications(array('viewaccess', 'watchlist', 'institutionmessage'));
}

/**
 * Cronjob to check Launchpad for the latest Mahara version
 */
function cron_check_for_updates() {

    $url = 'https://mahara.org/local/versions.php';
    $request = array(
        CURLOPT_URL => $url,
    );

    $result = mahara_http_request($request);

    if (!empty($result->errno) || $result->info['http_code'] != '200') {
        log_debug('Could not access Mahara.org for versioning information.');
        return;
    }
    $data = json_decode($result->data);
    if ($data->returnCode == 1) {
        log_debug('Could not retrieve Mahara versions information file from Mahara.org');
        return;
    }
    $versions = $data->message->versions;

    // Lets record the needed info locally as the cron only fetches the info once a day
    $latestmajorversion = max(array_keys((array)$versions));
    $latestversion = $latestmajorversion . '.' . $versions->$latestmajorversion->latest;
    set_config('latest_version', $latestversion);
    if (preg_match('/^(\d+)\.(\d+)\.(\d+).*?$/', get_config('release'), $match)) {
        $currentmajorversion = $match[1] . '.' . $match[2];
        $latestseriesversion = $currentmajorversion . '.' . $versions->$currentmajorversion->latest;
        set_config('latest_branch_version', $latestseriesversion);
    }
    else {
        set_config('latest_branch_version', '');
    }
    $supported = array();
    foreach ($versions as $k => $v) {
        $insupport = filter_var($v->supported, FILTER_VALIDATE_BOOLEAN);
        if ($insupport) {
            $supported[] = $k;
        }
    }
    set_config('supported_versions', implode(',', $supported));
}

/**
 * Cronjob to send an update of site statistics to mahara.org
 */
function cron_send_registration_data() {
    require_once(get_config('libroot') . 'registration.php');
    registration_store_data();
    if (!get_config('registration_sendweeklyupdates')) {
        return;
    }

    $result = registration_send_data();
    $data = json_decode($result->data);

    if ($data->status != 1) {
        log_info($result);
    }
    else {
        set_config('registration_lastsent', time());
    }
}

/**
 * Cronjob to store the institution statistics
 */
function cron_institution_registration_data() {
    require_once(get_config('libroot') . 'registration.php');
    institution_registration_store_data();
}

/**
 * Cronjob to save weekly site data locally
 */
function cron_site_data_weekly() {
    require_once(get_config('libroot') . 'statistics.php');
    $current = site_data_current();
    $time = db_format_timestamp(time());

    insert_record('site_data', (object) array(
        'ctime' => $time,
        'type'  => 'user-count',
        'value' => $current['users'],
    ));
    insert_record('site_data', (object) array(
        'ctime' => $time,
        'type'  => 'group-count',
        'value' => $current['groups'],
    ));
    insert_record('site_data', (object) array(
        'ctime' => $time,
        'type'  => 'view-count',
        'value' => $current['views'],
    ));
}

function cron_site_data_daily() {
    require_once(get_config('libroot') . 'statistics.php');
    $current = site_data_current();
    $time = db_format_timestamp(time());

    // Total users
    insert_record('site_data', (object) array(
        'ctime' => $time,
        'type'  => 'user-count-daily',
        'value' => $current['users'],
    ));

    // Logged-in users
    $interval = is_postgres() ? "'1 day'" : '1 day';
    $where = "lastaccess >= DATE(?) AND lastaccess < DATE(?)+ INTERVAL $interval";
    insert_record('site_data', (object) array(
        'ctime' => $time,
        'type'  => 'loggedin-users-daily',
        'value' => count_records_select('usr', $where, array($time, $time)),
    ));

    // Process log file containing view visits
    $viewlog = get_config('dataroot') . 'log/views.log';
    if (@rename($viewlog, $viewlog . '.temp') and $fh = @fopen($viewlog . '.temp', 'r')) {

        // Read the new stuff out of the file
        $latest = get_config('viewloglatest');

        $visits = array();
        while (!feof($fh)) {
            $line = fgets($fh, 1024);
            if (preg_match('/^\[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (\d+)$/', $line, $m) && $m[1] > $latest) {
                $visits[] = (object) array('ctime' => $m[1], 'view' => $m[2]);
            }
        }
        fclose($fh);

        // Get per-view counts for the view table.
        $visitcounts = array();
        foreach ($visits as &$v) {
            if (!isset($visitcounts[$v->view])) {
                $visitcounts[$v->view] = 0;
            }
            $visitcounts[$v->view]++;
        }

        // Add visit records to view_visit
        foreach ($visits as &$v) {
            if (record_exists('view', 'id', $v->view)) {
                insert_record('view_visit', $v);
            }
        }

        // Delete view_visit records > 1 week old
        delete_records_select(
            'view_visit',
            'ctime < CURRENT_DATE - INTERVAL ' . (is_postgres() ? "'1 week'" : '1 WEEK')
        );

        // Update view counts
        foreach ($visitcounts as $viewid => $newvisits) {
            execute_sql("UPDATE {view} SET visits = visits + ? WHERE id = ?", array($newvisits, $viewid));
        }

        set_config('viewloglatest', $time);

        unlink($viewlog . '.temp');
    }

    require_once('function.dirsize.php');
    if ($diskusage = dirsize(get_config('dataroot'))) {
        // Currently there is no need to track disk usage
        // over time, so delete old records first.
        delete_records('site_data', 'type', 'disk-usage');
        insert_record('site_data', (object) array(
            'ctime' => $time,
            'type'  => 'disk-usage',
            'value' => $diskusage,
        ));
    }
    graph_site_data_daily();

}

function cron_institution_data_weekly() {
    require_once(get_config('libroot') . 'statistics.php');
    foreach (get_column('institution', 'name') as $institution) {
        $current = institution_data_current($institution);
        $time = db_format_timestamp(time());

        insert_record('institution_data', (object) array(
            'ctime'       => $time,
            'institution' => $institution,
            'type'        => 'user-count',
            'value'       => $current['users'],
        ));
        insert_record('institution_data', (object) array(
            'ctime'       => $time,
            'institution' => $institution,
            'type'        => 'view-count',
            'value'       => $current['views'],
        ));

        $current['name'] = $institution;
    }
}

function cron_institution_data_daily() {
    require_once(get_config('libroot') . 'statistics.php');
    foreach (get_column('institution', 'name') as $institution) {
        $current = institution_data_current($institution);
        $time = db_format_timestamp(time());

        // Total users
        insert_record('institution_data', (object) array(
            'ctime'       => $time,
            'institution' => $institution,
            'type'        => 'user-count-daily',
            'value'       => $current['users'],
        ));

        // Logged-in users
        $interval = is_postgres() ? "'1 day'" : '1 day';
        if ($current['users'] != 0) {
            $where = "lastaccess >= DATE(?) AND lastaccess < DATE(?)+ INTERVAL $interval";
            $where .= " AND id IN (" . $current['memberssql'] . ")";
            $loggedin = count_records_select('usr', $where, array_merge(array($time, $time), $current['memberssqlparams']));
        }
        else {
            $loggedin = 0;
        }
        insert_record('institution_data', (object) array(
            'ctime'       => $time,
            'institution' => $institution,
            'type'        => 'loggedin-users-daily',
            'value'       => $loggedin,
        ));

        $current['name'] = $institution;
        graph_institution_data_daily($current);
    }

}

/**
 * A cronjob to generate a sitemap
 */
function cron_sitemap_daily() {
    if (!get_config('generatesitemap')) {
        return;
    }

    require_once(get_config('libroot') . 'searchlib.php');
    require_once(get_config('libroot') . 'group.php');
    require_once(get_config('libroot') . 'view.php');
    require_once(get_config('libroot') . 'sitemap.php');

    safe_require('interaction', 'forum');

    $sitemap = new Sitemap();
    $sitemap->generate();
}

/**
 * Cronjob to expire the event_log table.
 */
function cron_event_log_expire() {
    if ($expiry = get_config('eventlogexpiry')) {
        delete_records_select(
            'event_log',
            'ctime < CURRENT_DATE - INTERVAL ' .
                (is_postgres() ? "'" . $expiry . " seconds'" : $expiry . ' SECOND')
        );

    }
}

function cron_email_reset_rebounce() {
    $bounces_handle = get_config('bounces_handle');
    $bounces_resetdays = get_config('bounces_resetdays');
    if ($bounces_handle && isset($bounces_resetdays) && ($bounces_resetdays > 0)) {
          // if this is the first time running the cron, initialise the interaction_config value
          $lastbouncereset = get_field('notification_config', 'value', 'plugin', 'email', 'field', 'lastbouncereset');
          if ($lastbouncereset) {
              $newtime = strtotime('+' . $bounces_resetdays . ' days', $lastbouncereset);
              if ($newtime < time()) {
                  // get all artefact_internal_profile_email records and set mailsbounced to 0
                  $sql = "UPDATE {artefact_internal_profile_email}
                          SET mailssent = 0, mailsbounced = 0";
                  execute_sql($sql);
                  $pluginrecord = new StdClass;
                  $pluginrecord->plugin = 'email';
                  $pluginrecord->field  = 'lastbouncereset';
                  $pluginrecord->value  = time();
                  $whereobj = (object)array(
                      'plugin' => 'email',
                      'field'  => 'lastbouncereset',
                  );
                  update_record('notification_config',  $pluginrecord, $whereobj);
              }
          }
          else {
              $pluginrecord = new StdClass;
              $pluginrecord->plugin = 'email';
              $pluginrecord->field  = 'lastbouncereset';
              $pluginrecord->value  = time();
              insert_record('notification_config', $pluginrecord);
          }
    }
}

function build_portfolio_search_html(&$data) {
    global $THEME;
    $artefacttypes = get_records_assoc('artefact_installed_type');
    require_once('view.php');
    require_once('collection.php');
    foreach ($data->data as &$item) {
        $item->ctime = format_date($item->ctime);
        if ($item->type == 'view') {
            $item->typestr = $item->type;
            $item->typelabel = get_string('view');
            $v = new View(0, (array)$item);
            $v->set('dirty', false);
            $item->url = $v->get_url();
        }
        else if ($item->type == 'collection') {
            $item->typestr = $item->type;
            $item->typelabel = get_string('Collection', 'collection');
            // Because our 'views' array clashes with a collection objects 'views' array we need to move it out of the way
            // before calling the get_url() function
            $viewarray = $item->views;
            $dummy = new stdClass();
            $dummy->id = $item->viewid;
            $item->views = array('views' => array(0 => $dummy));
            $c = new Collection(0, (array)$item);
            $item->url = $c->get_url();
            $item->views = $viewarray;
        }
        else if ($item->type == 'blocktype') {
            safe_require('blocktype', $item->artefacttype);
            $bi = new BlockInstance($item->id);
            $bi->set('dirty', false);
            $item->title = $bi->get_title();
            $item->url = $bi->get_view()->get_url();
            // Get the correct css icon
            $namespaced = blocktype_single_to_namespaced($item->artefacttype, $bi->get('artefactplugin'));
            $classname = generate_class_name('blocktype', $namespaced);
            $item->typestr = call_static_method($classname, 'get_css_icon', $item->artefacttype);
            if (in_array($item->artefacttype, array('entireresume', 'resumefield'))) {
                $item->typelabel = get_string('title', 'blocktype.resume/' . $item->artefacttype);
            }
            else {
                $item->typelabel = get_string('title', 'blocktype.' . $item->artefacttype);
            }
        }
        else { // artefact
            safe_require('artefact', $artefacttypes[$item->artefacttype]->plugin);
            $links = call_static_method(generate_artefact_class_name($item->artefacttype), 'get_links', $item->id);
            $item->url     = $links['_default'];
            $item->typestr = isset($item->specialtype) ? $item->specialtype : $item->artefacttype;
            if ($item->artefacttype == 'task') {
                $item->typelabel = get_string('Task', 'artefact.plans');
            }
            else {
                $item->typelabel = get_string($item->artefacttype, 'artefact.' . $artefacttypes[$item->artefacttype]->plugin);
            }
        }
    }

    if (isset($data->isrelated) && $data->isrelated) {
        $data->baseurl = get_config('wwwroot') . 'relatedtags.php' . (is_null($data->tag) ? '' : '?tag=' . urlencode($data->tag) . '&view=' . $data->viewid);
        $data->basejsonurl = 'json/relatedtagsearch.php';
    }
    else {
        $data->baseurl = get_config('wwwroot') . 'tags.php' . (is_null($data->tag) ? '' : '?tag=' . urlencode($data->tag));
        $data->basejsonurl = 'json/tagsearch.php';
    }

    if (isset($data->sort) && $data->sort != 'name') {
        $data->baseurl .= (strpos($data->baseurl, '?') ? '&' : '?') . 'sort=' . $data->sort;
    }
    if (isset($data->filter) && $data->filter != 'all') {
        $data->baseurl .= (strpos($data->baseurl, '?') ? '&' : '?') . 'type=' . $data->filter;
    }

    $data->sortcols = array('name', 'date');
    $data->filtercols = array(
        'file'       => get_string('tagfilter_file'),
        'image'      => get_string('tagfilter_image'),
        'text'       => get_string('tagfilter_text'),
        'view'       => get_string('tagfilter_view'),
        'collection' => get_string('tagfilter_collection'),
        'blog'       => get_string('tagfilter_blog'),
        'blogpost'   => get_string('tagfilter_blogpost'),
        'plan'       => get_string('tagfilter_plan'),
        'task'       => get_string('tagfilter_task'),
        'media'      => get_string('tagfilter_external'),
        'resume'     => get_string('tagfilter_resume'),
    );
    asort($data->filtercols, SORT_NATURAL);
    $data->filtercols = array('all' => get_string('tagfilter_all')) + $data->filtercols;

    $smarty = smarty_core();
    $smarty->assign('data', $data->data);
    $smarty->assign('owner', $data->owner->id);
    $data->tablerows = $smarty->fetch('portfoliosearchresults.tpl');
    $pagination = build_pagination(array(
        'id' => 'results_pagination',
        'class' => 'center',
        'url' => $data->baseurl,
        'jsonscript' => $data->basejsonurl,
        'datatable' => 'results',
        'count' => $data->count,
        'limit' => $data->limit,
        'offset' => $data->offset,
        'jumplinks' => 6,
        'numbersincludeprevnext' => 2,
        'numbersincludefirstlast' => false,
        'resultcounttextsingular' => get_string('result'),
        'resultcounttextplural' => get_string('results'),
    ));
    $data->pagination = $pagination['html'];
    $data->pagination_js = $pagination['javascript'];
}

function mahara_touch_record($table, $id) {
    execute_sql("UPDATE " . db_table_name($table) . " SET atime = ? WHERE id = ?", array(db_format_timestamp(time()), $id));
}

function mahara_log($logname, $string) {
    error_log('[' . date("Y-m-d h:i:s") . "] $string\n", 3, get_config('dataroot') . 'log/' . $logname . '.log');
}

/**
 * Check whether HTML editor can be used.
 *
 * @return bool
 */

function is_html_editor_enabled () {
    global $USER, $SESSION;
    return (
            (!get_config('wysiwyg') && $USER->get_account_preference('wysiwyg')) ||
            (get_config('wysiwyg') == 'enable' && $USER->is_logged_in())
           );
}

/**
 * Determine if site is running with https
 *
 * @return bool
 */
function is_https() {
    return stripos(get_config('wwwroot'), 'https://') !== false;
}

function sanitize_email($value) {
    if (!PHPMailer\PHPMailer\PHPMailer::validateAddress($value)) {
        return '';
    }
    return $value;
}

function sanitize_firstname($value) {
    if (!preg_match('/\S/', $value)) {
        return '';
    }
    return $value;
}

function sanitize_lastname($value) {
    if (!preg_match('/\S/', $value)) {
        return '';
    }
    return $value;
}

function sanitize_studentid($value) {
    if (!preg_match('/\S/', $value)) {
        return '';
    }
    return $value;
}

function sanitize_preferredname($value) {
    if (!preg_match('/\S/', $value)) {
        return '';
    }
    return $value;
}

function generate_csv($data, $csvfields, $csvheaders = array()) {
    $csvfieldsheaders = $csvfields;
    if (!empty($csvheaders)) {
        // Allow for more human readable headers for csv columns
        // You can define this for any number of columns.
        // @TODO: allow lang string translations
        foreach ($csvfieldsheaders as $k => $v) {
            if (array_key_exists($v, $csvheaders)) {
                $csvfieldsheaders[$k] = $csvheaders[$v];
            }
        }
    }
    $csv = join(',', $csvfieldsheaders) . "\n";

    foreach ($data as $row) {
        if (is_object($row)) {
            $row = (array) $row;
        }
        $u = array();
        foreach ($csvfields as $f) {
            $u[] = str_replace('"', '""', (isset($row[$f]) ? $row[$f] : 0));
        }
        $csv .= '"' . join('","', $u) . '"' . "\n";
    }
    return $csv;
}

/**
 *
 */
function check_if_institution_tag($tag) {
    global $USER;
    $institutions = $USER->get('institutions');
    if ($USER->get('admin') && $institutiontags = get_records_sql_array("
        SELECT id FROM {tag}
        WHERE tag = ?
        AND resourcetype = ?
        AND ownertype = ?",
        array($tag, 'institution', 'institution'))) {
        $tag = 'tagid_' . $institutiontags[0]->id;
    }
    if ($institutions && $institutiontags = get_records_sql_array("
        SELECT id FROM {tag}
        WHERE tag = ?
        AND resourcetype = ?
        AND ownertype = ?
        AND ownerid IN ('" . join("','", array_keys($institutions)) . "')
        UNION
        SELECT t.id FROM {tag} t
        JOIN {institution} i ON i.name = t.ownerid
        WHERE resourcetype = ?
        AND ownertype = ?
        AND ownerid IN ('" . join("','", array_keys($institutions)) . "')
        AND CONCAT(i.displayname, ': ', t.tag) = ?",
        array($tag, 'institution', 'institution', 'institution', 'institution', $tag))) {
        $tag = 'tagid_' . $institutiontags[0]->id;  // if same tag in multiple institutions just pick first
    }
    return $tag;
}

/**
 * Check to make sure table is case sensitive (currently only for MySql)
 * If it is not then reduce supplied array to a case insensitive version
 * Preserving the first occurance of any duplicates.
 * E.g. 'Test,test,cat,TEST,CAT,Cat' will return 'Test,cat'
 *
 * @param array     Array of case senstive strings
 * @param string    Name of table
 *
 * @return array    Array of strings
 */
function check_case_sensitive($a, $table) {
    // Need to avoid tags that could clash with institution tag format
    // So we remove or strip anything beginning with tagid/tagid_
    foreach ($a as $k => $v) {
        if (preg_match("/^tagid(\_*)(.*)/i", $v, $matches)) {
            if (empty($matches[2])) {
                unset($a[$k]);
            }
            else {
                $a[$k] = $matches[2];
            }
        }
    }

    if (is_mysql()) {
        $db = get_config('dbname');
        $table = get_config('dbprefix') . $table;
        $result = get_records_sql_array("SHOW TABLE STATUS IN `$db` WHERE Name = ?", array($table));
        if (is_array($result) && count($result) === 1) {
            if (preg_match('/_ci/', $result[0]->Collation)) {
                $b = array_unique(array_map('strtolower', $a));
                $a = array_intersect_key($a, array_flip(array_keys($b)));
            }
        }
        else {
            throw new SQLException($table . " is not found or can not be accessed, check log for errors.");
        }
    }
    return $a;
}

/**
 * Check one array of associative arrays against another to see if
 * there are any differences and return a merged array based on the order
 * of the $first array with the differences of $second appended on
 *
 * @param array $first contains associative arrays
 * @param array $second contains associative arrays
 *
 * @return array all the different associative arrays
 */
function combine_arrays($first, $second) {
    foreach ($first as $k => $v) {
        foreach ($second as $sk => $sv) {
            $diff = array_diff($v, $sv);
            if (empty($diff)) {
                // arrays are the same
                unset($second[$sk]);
            }
        }
    }
    $merged = array_merge($first, $second);
    return $merged;
}

/**
 * Returns the number of available CPU cores
 *
 *  Should work for Linux, Windows, Mac & BSD
 *
 * @return integer
 *
 * Copyright © 2011 Erin Millard
 * https://gist.github.com/ezzatron/1321581
 */
function num_cpus() {
    $numCpus = 1;

    if (is_file('/proc/cpuinfo')) {
        $cpuinfo = file_get_contents('/proc/cpuinfo');
        preg_match_all('/^processor/m', $cpuinfo, $matches);

        $numCpus = count($matches[0]);
    }
// For Windows server users you can uncomment the following to try and access server load (experimental - use at own risk)
//    else if ('WIN' == strtoupper(substr(PHP_OS, 0, 3))) {
//        $process = @popen('wmic cpu get NumberOfCores', 'rb');
//
//        if (false !== $process) {
//            fgets($process);
//            $numCpus = intval(fgets($process));
//            pclose($process);
//        }
//    }
    else {
        $process = @popen('sysctl -a', 'rb');

        if (false !== $process) {
            $output = stream_get_contents($process);

            preg_match('/hw.ncpu: (\d+)/', $output, $matches);
            if ($matches) {
                $numCpus = intval($matches[1][0]);
            }
            pclose($process);
        }
    }

    return $numCpus;
}

/**
 * Perform checks to see if there is enough server capacity to run a task.
 *
 * @param  $threshold   Pass in a threshold to test against - optional
 *     The threshold value must be in [0..1]
 *         0: the server is completely idle
 *         1: is fully loaded
 * @return bool
 *     If the server is Windows, return false (bypass this feature)
 */
function server_busy($threshold = false) {
    // Get current server load information - code from:
    // http://www.php.net//manual/en/function.sys-getloadavg.php#107243
// For Windows server users you can uncomment the following to try and access server load (experimental - use at own risk)
     if (stristr(PHP_OS, 'win')) {
//        $wmi = new COM("Winmgmts://");
//        $server = $wmi->execquery("SELECT LoadPercentage FROM Win32_Processor");
//        $cpu_num = 0;
//        $load_total = 0;
//        foreach ($server as $cpu) {
//            $cpu_num++;
//            $load_total += $cpu->loadpercentage;
//        }
//        $load = round(($load_total / $cpu_num), 2);
         return false;
     }
     else {
        $sys_load = sys_getloadavg();
        $load = $sys_load[0] / num_cpus();
     }

    $threshold = ($threshold) ? $threshold : '0.5'; // TODO: find out a good base number
    if ($load > $threshold) {
        return true;
    }
    return false;
}

/**
 * Find out a user's institution sort order for comments on artefacts within view page.
 * If they belong to one institution that has specified a sort order, then this will
 * be that institution's comment sort.
 * If they belong to multiple institutions then the arbitrarily "first" institution's
 * sort order will be used.
 *
 * @param int $userid Which user to check (defaults to $USER)
 * @return string Sort order - either 'earliest', 'latest'.
 */
function get_user_institution_comment_sort_order($userid = null) {

    $instsorts = get_configs_user_institutions('commentsortorder', $userid);
    $sortorder = null;
    // Every user belongs to at least one institution
    foreach ($instsorts as $sort) {
        // If the user belongs to multiple institutions, arbitrarily use the sort
        // from the first one that has specified a sort.
        if (!empty($sort)) {
            $sortorder = $sort;
            break;
        }
    }
    return $sortorder;
}


/**
 * Find out if a user's pages should allow threaded replies.
 * @param string $userid
 * @return boolean
 */
function get_user_institution_comment_threads($userid = null) {
    $instthreads = get_configs_user_institutions('commentthreaded', $userid);
    // If they belong to even one institution that allows threaded comments, let them use them.
    foreach ($instthreads as $inst => $threads) {
        if (!empty($threads)) {
            return true;
        }
    }
    return false;
}

/**
 * Returns the user id of users in multiple institutions
 *
 * @return array user ids (or false)
 */
function users_in_multiple_institutions() {
    $sql = "SELECT usr
            FROM {usr_institution}
            GROUP BY usr
            HAVING COUNT(usr) > 1";
    return get_records_sql_array($sql);
}

/**
 * Returns all directories of installed plugins except for local
 * from the current codebase.
 *
 * This is relatively slow and not fully cached, use with care!
 *
 * @return array ('plugintkey' => path, ...)
 * For example, array (
 *     'artefact.blog' => $CFG->docroot . 'artefact/blog',
 *     'blocktype.blog' => $CFG->docroot . 'artefact/blog/blocktype/blog',
 *     ...
 * )
 */
function get_installed_plugins_paths() {
    $versions = array();
    // All installed plugins
    $plugins = array();
    foreach (plugin_types_installed() as $plugin) {
        $dirhandle = opendir(get_config('docroot') . $plugin);
        while (false !== ($dir = readdir($dirhandle))) {
            if (strpos($dir, '.') === 0 || 'CVS' == $dir) {
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
                log_warn(get_string('pluginnotinstallable', 'mahara', $plugin, $dir) . $_e->GetMessage());
            }

            if ($plugin === 'artefact') { // go check it for blocks as well
                $btlocation = get_config('docroot') . $plugin . '/' . $dir . '/blocktype';
                if (!is_dir($btlocation)) {
                    continue;
                }
                $btdirhandle = opendir($btlocation);
                while (false !== ($btdir = readdir($btdirhandle))) {
                    if (strpos($btdir, '.') === 0 || 'CVS' == $btdir) {
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
    $pluginpaths = array();
    foreach ($plugins as $plugin) {
        $plugintype = $plugin[0];
        $pluginname = $plugin[1];
        $pluginpath = "$plugin[0]/$plugin[1]";
        $pluginkey  = "$plugin[0].$plugin[1]";

        if ($plugintype == 'blocktype' && strpos($pluginname, '/') !== false) {
            $bits = explode('/', $pluginname);
            $pluginpath = 'artefact/' . $bits[0] . '/blocktype/' . $bits[1];
        }

        $pluginpaths[$pluginkey] = get_config('docroot') . $pluginpath;
    }

    return $pluginpaths;
}

/**
 * Returns hash of all versions including core and all installed plugins except for local
 * from the current codebase.
 *
 * This is relatively slow and not fully cached, use with care!
 *
 * @return string sha1 hash
 */
function get_all_versions_hash() {
    $versions = array();
    // Get core version
    require(get_config('libroot') . 'version.php');
    $versions['core'] = $config->version;
    // All installed plugins
    $pluginpaths = get_installed_plugins_paths();
    foreach ($pluginpaths as $pluginkey => $pluginpath) {
        require($pluginpath . '/version.php');
        $versions[$pluginkey] = $config->version;
    }

    return sha1(serialize($versions));
}

/*
 * Update the information on our progress so the browser can access it via
 * json/progress.php.
 *
 * @param token    (Alphanumeric) An identifier unique to the page. This
 *                 allows multiple progress meters on different tabs of a
 *                 browser at the same time.
 * @param numerator (Int) The top number in the fraction of work done so far.
 * @param denominator (Int) The bottom number in the fraction of work done so
 *                 far. If 0, no percentage will be displayed.
 * @param message  A message to be displayed in the progress bar prior to the
 *                 percentage.
 */
function set_progress_info($token, $numerator = 0, $denominator = 1, $message = '') {
    global $SESSION;

    $SESSION->set_progress($token, array(
                'finished' => FALSE,
                'numerator' => $numerator,
                'denominator' => $denominator,
                'message' => $message
                ));
}

/**
 * Update the information on our progress so the browser can access it via
 * json/progress.php.
 *
 * @param token    (Alphanumeric) An identifier unique to the page. This
 *                 allows multiple progress meters on different tabs of a
 *                 browser at the same time.
 * @param data     Any data to be passed back to the browser (instructions
 *                 for meter_update() in js/mahara.js.
 */
function set_progress_done($token, $data = array()) {
    global $SESSION;

    $data['finished'] = TRUE;

    $SESSION->set_progress($token, $data);
}

/**
 * Recursively implode an multidemenional array with optional key inclusion
 *
 * Output will be a string like either: value, value, value
 * or if keys included: key: value, key: value, key: value
 * Based on: https://gist.github.com/jimmygle/2564610
 *
 * @param   array   $array         multi-dimensional array to recursively implode
 * @param   bool    $include_keys  include keys before their values
 * @param   string  $separator     value that demarcates value elements
 * @param   string  $keyseparator  value that demarcates key/value elements
 * @param   bool    $trim_all      trim ALL whitespace from string
 *
 * @return  string  imploded array
 */
function recursive_implode(array $array, $include_keys = false, $separator = ',', $keyseparator = ': ', $trim_all = true) {
    $glued_string = '';
    // Recursively iterates array and adds key/value to glued string
    array_walk_recursive($array, function($value, $key) use ($separator, $keyseparator, $include_keys, &$glued_string) {
        $include_keys and $glued_string .= $key.$keyseparator;
        $glued_string .= $value.$separator;
    });
    // Removes last $separator from string
    strlen($separator) > 0 and $glued_string = substr($glued_string, 0, -strlen($separator));
    // Trim all whitespace
    $trim_all and $glued_string = preg_replace("/(\s)/ixsm", '', $glued_string);
    return (string) $glued_string;
}

/**
 * Set libxml internal errors and entity loader
 * state before accessing an external xml document
 *
 * @param boolean  $state The state to set the options to
 */
function libxml_before($state = true) {
    $xmlstate = $xmlerrors = null;
    if (function_exists('libxml_disable_entity_loader')) {
        $xmlerrors = libxml_use_internal_errors($state);
        $xmlstate = libxml_disable_entity_loader($state);

        // Record these settings so we can go back them at the end, as a workaround
        // to PHP bug https://bugs.php.net/bug.php?id=64938
        if (!defined('MAHARA_LIBXML_ENTITY_LOADER_BEFORE')) {
            define('MAHARA_LIBXML_USE_INTERNAL_ERRORS_BEFORE', $xmlerrors);
            define('MAHARA_LIBXML_ENTITY_LOADER_BEFORE', $xmlstate);
            register_shutdown_function('libxml_after');
        }
    }

    return array($xmlstate, $xmlerrors);
}

/**
 * Set libxml internal errors and entity loader
 * state after accessing an external xml document
 */
function libxml_after() {
    if (function_exists('libxml_disable_entity_loader')) {

        if (defined('MAHARA_LIBXML_ENTITY_LOADER_BEFORE')) {
            $xmlerrors = MAHARA_LIBXML_USE_INTERNAL_ERRORS_BEFORE;
            $xmlstate = MAHARA_LIBXML_ENTITY_LOADER_BEFORE;
        }
        else {
            $xmlerrors = true;
            $xmlstate = true;
        }

        libxml_use_internal_errors($xmlerrors);
        libxml_disable_entity_loader($xmlstate);
    }
}

/**
 * Wrapper to call Pieform class for new pieform instance.
 *
 * See lib/pieforms/pieform.php for more information
 */
function pieform_instance($data) {
    require_once(get_config('libroot') . 'pieforms/pieform.php');
    return new Pieform($data);
}

/**
 * Wrapper to call Pieform class for processed form.
 *
 * See lib/pieforms/pieform.php for more information
 */
function pieform($data) {
    require_once(get_config('libroot') . 'pieforms/pieform.php');
    return Pieform::process($data);
}

/**
 * Wrapper for setting up Pieform headdata.
 * When there is no pieforms on the page but pieforms are called via ajax
 * HACK: The ideal would be to refactor Pieforms so that Javascript dependencies can be loaded
 *       dynamically when we load up a form via AJAX
 *
 * @param array $elements A Piefrom element array if one needs to set element specific headdata js files
 */
function pieform_setup_headdata($elements = null) {
    $elements = is_null($elements) ? array('dummy' => array('type' => 'hidden', 'value' => 0)) : $elements;

    if (empty($GLOBALS['_PIEFORM_REGISTRY'])) {
        $fakeform = pieform_instance(array('name' => 'fakeform', 'elements' => $elements));
    }
}

/**
 * Check if the given input is a serialized string
 * @param varied $sstr
 */
function is_serialized_string($sstr) {
    if (is_string($sstr)) {
        return (preg_match('/^s:\d+:".*";$/s', $sstr) === 1);
    }
    return false;
}

/**
 * Check if the given input is a valid serialized stdClass object of a skin attribute
 * Each object's property can only be a string, integer or null
 * @param string $sobj
 */
function is_valid_serialized_skin_attribute($sobj) {
    if (is_string($sobj) && preg_match('/^O:8:"stdClass":\d+:{.*}$/s', $sobj)) {
        // Make sure each property is a string, integer or null.
        $pos = strpos($sobj, '{');
        $sattrs = substr($sobj, $pos + 1, -1);
        $cur = 0;
        while ($cur < strlen($sattrs)) {
            switch ($sattrs[$cur]) {
                case 's':
                    $cur+=2;
                    $strsize = "";
                    while ($sattrs[$cur] >= '0' && $sattrs[$cur] <= '9') {
                        $strsize .= $sattrs[$cur];
                        $cur++;
                    }
                    if ($sattrs[$cur] == ':') {
                        $cur += (int) $strsize + 4;
                    }
                    break;
                case 'i':
                    $cur+=2;
                    $strsize = "";
                    while ($sattrs[$cur] >= '0' && $sattrs[$cur] <= '9') {
                        $cur++;
                    }
                    $cur ++ ;
                    break;
                case 'N':
                    $cur+=2;
                    break;
                default:
                    // Wrong serialized format
                    return false;
            }
        }
        return true;
    }
    return false;
}

/*
 * Clear all Mahara caches.
 * @param   bool   $clearsessiondirs  Optional to clear sessions. Useful during upgrade when session structure changes
 *
 * @return bool True if success, false otherwise.
 */
function clear_all_caches($clearsessiondirs = false) {
    require_once(get_config('libroot') . 'file.php');

    try {
        clear_menu_cache();
        update_safe_iframe_regex();
        bump_cache_version();
        clear_resized_images_cache();

        $dwoo_dir = get_dwoo_dir();
        if (check_dir_exists($dwoo_dir) && !rmdirr($dwoo_dir)) {
            throw new SystemException('Can not remove dwoo directory ' . $dwoo_dir);
        }

        if ($clearsessiondirs) {
            $session_dir = get_config('dataroot') . 'sessions';
            rmdirr($session_dir);
            Session::create_directory_levels($session_dir);
        }

        clearstatcache();

        handle_event('clearcaches', array());

        $result = true;
    }
    catch (Exception $e) {
        log_info("Error while cleaning caches: " . $e->GetMessage());
        $result = false;
    }

    return $result;
}

/**
 * Clear the generated resized images
 * @param bool $profileonly Optional clear only the profile image resized files
 *
 * @return bool True if success, false otherwise
 */
function clear_resized_images_cache($profileonly=false) {
    require_once(get_config('libroot') . 'file.php');

    $filedir = get_config('dataroot') . 'artefact/file/';
    $profiledir = $filedir . 'profileicons/resized';
    if (check_dir_exists($profiledir) && !rmdirr($profiledir)) {
        log_debug('Can not remove profile image resized directory ' . $profiledir);
        return false;
    }
    if (!$profileonly) {
        $imagedir = $filedir . 'resized';
        if (check_dir_exists($imagedir) && !rmdirr($imagedir)) {
            log_debug('Can not remove image resized directory ' . $imagedir);
            return false;
        }
    }
}

/*
 * Replaces all accented characters with un-accented counterparts.
 *
 * See: http://stackoverflow.com/questions/3371697/replacing-accented-characters-php
 */
function no_accents($str) {
    $accents = array(
        'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A',
        'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
        'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N',
        'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
        'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'ss', 'à'=>'a',
        'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
        'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i',
        'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
        'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b',
        'ÿ'=>'y', 'ă'=>'a', 'Ă'=>'A', 'ș'=>'s', 'Ș'=>'S', 'ț'=>'t', 'Ț'=>'T',
        'Ğ'=>'G', 'İ'=>'I', 'Ş'=>'S', 'ğ'=>'g', 'ı'=>'i', 'ş'=>'s', 'ü'=>'u',
        'Č'=>'C', 'č'=>'c', 'Ć'=>'c', 'ć'=>'c', 'Đ'=>'dz', 'đ'=>'dz'
    );
    return strtr($str, $accents);
}

/**
 * Sort menu by the weight value
 * Centralise the custom value_compare_function for the usort of various menus
 */
function sort_menu_by_weight($a, $b) {
    // Only items with a "weight" component need to get sorted. Ones without weight can go first.
    if (!array_key_exists('weight', $a)) {
        return -1;
    }
    if (!array_key_exists('weight', $b)) {
        return 1;
    }
    $aweight = $a['weight'];
    $bweight = $b['weight'];
    if ($aweight == $bweight) {
        return 0;
    }
    return ($aweight < $bweight) ? -1 : 1;
}

/**
 * Disable elasticsearch triggers for site - useful for upgrades if we don't need to reindex the changes
 * This should be paired with create_elasticsearch_triggers(); - an example:
 *  drop_elasticsearch_triggers();
 *  execute_sql("UPDATE {view} ... ");
 *  create_elasticsearch_triggers();
 */
function drop_elasticsearch_triggers() {
    if (get_config('searchplugin') == 'elasticsearch') {
        log_debug('Dropping elasticsearch triggers');
        require_once(get_config('docroot') . 'search/elasticsearch/lib.php');
        $enabledtypes = explode(',', get_config_plugin('search', 'elasticsearch', 'types'));
        foreach ($enabledtypes as $type) {
            ElasticsearchIndexing::drop_triggers($type);
        }
        ElasticsearchIndexing::drop_trigger_functions();
    }
}

/**
 * Paired with  drop_elasticsearch_triggers(); - see it's info for useage
 */
function create_elasticsearch_triggers() {
    if (get_config('searchplugin') == 'elasticsearch') {
        log_debug('Adding elasticsearch triggers back in');
        require_once(get_config('docroot') . 'search/elasticsearch/lib.php');
        ElasticsearchIndexing::create_trigger_functions();
        $enabledtypes = explode(',', get_config_plugin('search', 'elasticsearch', 'types'));
        foreach ($enabledtypes as $type) {
            ElasticsearchIndexing::create_triggers($type);
        }
    }
}

/**
 * Return a list of available institution(s) to associate to a group.
 *
 * This list is based on user's institution memberships.
 * If the user is admin then all institutions are returned.
 *
 */
function get_institutions_to_associate() {
    global $USER;

    $institutions = array();
    if (is_array($USER->institutions) && count($USER->institutions) > 0 && !$USER->get('admin')) {
        // Get all institutions where user is member
        // This does not apply for site admins
        foreach ($USER->institutions as $inst) {
            if (empty($inst->suspended)) {
                $institutions = array_merge($institutions, array($inst->institution => $inst->displayname));
            }
        }
    }
    else if ($USER->get('admin')) {
        // Get all institutions since user is admin
        $records = get_records_array('institution');
        foreach ($records as $inst) {
            if (empty($inst->suspended)) {
                $institutions = array_merge($institutions, array($inst->name => $inst->displayname));
            }
        }
    }
    else {
        $institutions = array(
            'mahara' => get_field('institution', 'displayname', 'name', 'mahara')
        );
    }

    return $institutions;
}

/**
 * Get the password policy for this site
 *
 * @param  bool $parts  When true we return an array of the policy parts and when false return the policy string
 */
function get_password_policy($parts = false) {
    $policy = !empty(get_config('passwordpolicy')) ? get_config('passwordpolicy') : '8_ulns';
    if ($parts) {
        return explode('_', $policy);
    }
    return $policy;
}

/**
 * Get the password policy description based on password policy values
 *
 * @param  bool $type  When 'error' we return an error description rather than form field description
 *                     When 'user' we use message to user rather than generic message
 */
function get_password_policy_description($type = 'generic') {
    list($numbervalue, $formatvalue) = get_password_policy(true);
    if ($type == 'error') {
        $formatdesc = strtolower(get_string('element.passwordpolicy.' . $formatvalue, 'pieforms'));
        $description = get_string('passwordinvalidform1', 'auth.internal', $numbervalue, $formatdesc);
    }
    else if ($type == 'user') {
        $description = get_string('yournewpassword1', 'mahara', $numbervalue, get_string('passworddescription.' . $formatvalue, 'mahara'));
    }
    else {
        $description = get_string('passworddescriptionbase', 'mahara', $numbervalue) . ' ' . get_string('passworddescription.' . $formatvalue, 'mahara');
    }
    return $description;
}

/**
 *
 * Check if this site is using isolated institutions
 */
function is_isolated() {
    global $CFG;
    // If isolated institutions are turned on in $config.php we need to make sure
    // that the correct site settings exist in case they don't edit / save the Admin -> Config form
    // Note: we ned to save 'isolatedinstitutionset' in db as it needs to be different to the one set in $cfg
    if (isset($CFG->isolatedinstitutions) && $CFG->isolatedinstitutions && !get_field('config', 'value', 'field', 'isolatedinstitutionset')) {
        // Setting $cfg->isolatedinstitutions to true
        set_config('loggedinprofileviewaccess', false);
        set_config('creategroups', 'staff');
        set_config('createpublicgroups', 'siteadmins');
        set_config('usersallowedmultipleinstitutions', false);
        set_config('requireregistrationconfirm', true);
        set_config('isolatedinstitutionset', true); // set this in Db so we only do this check/update once
        // Set the institution 'showonlineusers' to institution only if currently all
        execute_sql('UPDATE {institution} SET showonlineusers = ? WHERE showonlineusers = ?', array(1, 2));
    }
    else if ((isset($CFG->isolatedinstitutions) && !$CFG->isolatedinstitutions) && get_field('config', 'value', 'field', 'isolatedinstitutionset')) {
        // Setting $cfg->isolatedinstitutions to false
        set_config('owngroupsonly', false);
        set_config('isolatedinstitutionset', false); // set this in Db so we only do this check/update once
    }
    else if (!isset($CFG->isolatedinstitutions) && get_field('config', 'value', 'field', 'isolatedinstitutionset')) {
        // Removing $cfg->isolatedinstitutions line
        set_config('owngroupsonly', false);
        set_config('isolatedinstitutionset', false); // set this in Db so we only do this check/update once
    }
    return (bool)get_config('isolatedinstitutions');
}

function get_homepage_redirect_results($request, $limit, $offset, $type = null, $id = null) {
    $admins = get_site_admins();
    $adminids = array();
    foreach ($admins as $admin) {
        $adminids[] = $admin->id;
    }
    $results = array('count' => 0,
                     'data' => array());
    if ($type) {
        $results['count'] = 1;
        $results['data'][] = get_record($type, 'id', $id);
    }
    else {
        $countsql = "SELECT COUNT(*) FROM (";
        $resultsql = "SELECT * FROM (";
        $fromsql = "SELECT v.id, v.title, v.owner, v.group, v.institution, 'view' AS urltype
             FROM {view} v
             JOIN {view_access} va ON va.view = v.id
             LEFT JOIN {group} g ON g.id = v.group
             LEFT JOIN {institution} i ON i.name = v.institution
             WHERE va.accesstype IN ('public', 'loggedin')
             AND v.type != 'profile'
             AND (
                 (v.owner IS NULL AND v.template != 2)
                  OR
                 (v.owner IN (" . join(',', array_map('db_quote', $adminids)) . "))
             )
             AND (v.title " . db_ilike() . " ? OR g.name " . db_ilike() . " ? OR i.name " . db_ilike() . " ?)
             UNION
             SELECT ii.id, ii.title, NULL AS owner, g.id AS \"group\", g.institution, 'forum' AS urltype
             FROM {interaction_instance} ii
             JOIN {group} g ON g.id = ii.group
             WHERE g.public = 1
             AND ii.deleted = 0
             AND (ii.title " . db_ilike() . " ?)
             ) AS foo LIMIT ? OFFSET ? ";
        $where = array('%' . $request . '%',
                       '%' . $request . '%',
                       '%' . $request . '%',
                       '%' . $request . '%',
                       $limit, $offset);
        if ($count = count_records_sql($countsql . $fromsql, $where)) {
            $results['count'] = $count;
            $results['data'] = get_records_sql_array($resultsql . $fromsql, $where);
            foreach ($results['data'] as $key => $value) {
                if ($value->urltype == 'view') {
                    $value->url = '/view/view.php?id=' . $value->id;
                }
                if ($value->urltype == 'forum') {
                    $value->url = '/interaction/forum/view.php?id=' . $value->id;
                }
            }
        }
    }

    return $results;
}

function translate_landingpage_to_tags(array $ids) {
    $ids = array_diff($ids, array(''));
    $results = array();
    if (!empty($ids)) {
        foreach ($ids as $id) {
            if (preg_match('/forum\/view\.php\?id=(\d+)/', $id, $matches)) {
                $data = get_homepage_redirect_results(null, null, null, 'interaction_instance', $matches[1]);
                $type = 'forum';
                $typeid = $matches[1];
                $result = $data['data'][0];
                $text = $result->title . ' (' . get_field('group', 'name', 'id', $result->group) . ')';
            }
            else if (preg_match('/view\.php\?id=(\d+)/', $id, $matches)) {
                $data = get_homepage_redirect_results(null, null, null, 'view', $matches[1]);
                $type = 'view';
                $typeid = $matches[1];
                $result = $data['data'][0];
                $text = $result->title;
                if ($result->institution) {
                    if ($result->institution == 'mahara') {
                        $text .= ' (' . get_string('Site') . ')';
                    }
                    else {
                        $text .= ' (' . get_field('institution', 'displayname', 'name', $result->institution) . ')';
                    }
                }
                else if ($result->group) {
                    $text .= ' (' . get_field('group', 'name', 'id', $result->group) . ')';
                }
                else if ($result->owner) {
                    $text .= ' (' . display_name($result->owner, null, true) . ')';
                }
            }
            else {
                $text = $typeid = $id;
                $type = 'unknown';
            }
            $results[] = (object) array('id' => $id, 'text' => $text, 'type' => $type, 'typeid' => $typeid);
        }
    }
    return $results;
}

function notify_landing_removed($landingpage, $deleted=false) {
    require_once('activity.php');

    $admins = array();
    foreach (get_site_admins() as $site_admin) {
        $admins[] = $site_admin->id;
    }

    if ($landingpage->type == 'forum') {
        $forumgroup = get_field('interaction_instance', 'group', 'id', $landingpage->typeid);
        $admins = array_merge($admins, group_get_admin_ids($forumgroup));
    }
    $admins = array_unique($admins);
    $message = $deleted ? 'landingpagegonemessagedeleted' : 'landingpagegonemessage';
    $messageargs = $deleted ? array($landingpage->text) : array();
    if ($deleted) {
        $url = get_config('wwwroot') . 'admin/site/options.php';
    }
    else {
        $url = preg_replace('/^\//', '', $landingpage->id);
    }
    activity_occurred('maharamessage', array(
        'users'   => $admins,
        'subject' => '',
        'message' => '',
        'strings'       => (object) array(
            'subject' => (object) array(
                'key'     => 'landingpagegonesubject',
                'section' => 'admin',
                'args'    => array(),
            ),
            'message' => (object) array(
                'key'     => $message,
                'section' => 'admin',
                'args'    => $messageargs,
            ),
        ),
        'url'     => $url,
    ));
}
