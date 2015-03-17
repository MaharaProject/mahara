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
    $phpversionrequired = '5.3.0';
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

    //Check for freetype in the gd extension
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

    if (
        !check_dir_exists(get_config('dataroot') . 'smarty/compile') ||
        !check_dir_exists(get_config('dataroot') . 'smarty/cache') ||
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
}

/**
 * Upgrade/Install the specified mahara components
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
            $config = new StdClass;
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
            $element = end(explode('_', $element));
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

    // First check all the normal locations for the string in the current language
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
            $result = get_string_local(get_language_root($parentlang) . 'lang/', $parentlang . '/' . $section . '.php', $identifier);
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
    $themes = array_merge(array('sitedefault' => get_string('nothemeselected', 'view')), $themes);
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
                            $themes[$subdir] = $theme;
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
 * or null if it is not found
 *
 * @param string $key config setting to look for
 * @return mixed
 */
function get_config($key) {
    global $CFG;
    if (isset($CFG->$key)) {
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

    db_ignore_sql_exceptions(true);
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
        $records = get_records_array($plugintype . '_config', 'plugin', $pluginname, 'field');
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

    $success = false;
    if (false !== get_field($table, 'value', 'plugin', $pluginname, 'field', $key)) {
        $success = set_field($table, 'value', $value, 'plugin', $pluginname, 'field', $key);
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
 * @param string $pluginname   E.g. internal
 * @param string $pluginid     Instance id
 * @param string $key          The config setting to look for
 */
function get_config_plugin_instance($plugintype, $pluginid, $key) {
    global $CFG;

    // Must be unlikely to exist as a config option for any plugin
    $instance = '_i_n_s_t' . $pluginid;

    // Suppress NOTICE with @ in case $key is not yet cached
    $configname = "plugin_{$plugintype}_{$instance}_{$key}";
    @$value = $CFG->{$configname};
    if (isset($value)) {
        return $value;
    }

    $records = get_records_array($plugintype . '_instance_config', 'instance', $pluginid, 'field', 'field, value');
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
 * @param string $pluginid     Instance id
 * @param string $key          The config setting to look for
 */
function set_config_plugin_instance($plugintype, $pluginname, $pluginid, $key, $value) {
    global $CFG;
    $table = $plugintype . '_instance_config';

    if (false !== get_field($table, 'value', 'instance', $pluginid, 'field', $key)) {
        if (set_field($table, 'value', $value, 'instance', $pluginid, 'field', $key)) {
            $status = true;
        }
    }
    else {
        $pconfig = new StdClass;
        $pconfig->instance = $pluginid;
        $pconfig->field  = $key;
        $pconfig->value  = $value;
        $status = insert_record($table, $pconfig);
    }
    if ($status) {
        // Must be unlikely to exist as a config option for any plugin
        $instance = '_i_n_s_t' . $pluginid;
        $configname = "plugin_{$plugintype}_{$instance}_{$key}";
        $CFG->{$configname} = $value;
        return true;
    }
    return false;
}

/**
 * Fetch an institution configuration (from either the "institution" or "institution_config" table)
 *
 * TODO: If needed, create a corresponding set_config_institution(). This would be most useful if there's
 * a situation where you need to manipulate individual institution configs. If you want to manipulate
 * them in batch, you can use the Institution class's __set() and commit() methods.
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

            // Cache it (in $CFG so if we ever write set_config_institution() we can make it update the cache)
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
 */
function safe_require($plugintype, $pluginname, $filename='lib.php', $function='require_once', $nonfatal=false) {
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

    if ($function == 'require') { return require($realpath); }
    if ($function == 'include') { return include($realpath); }
    if ($function == 'require_once') { return require_once($realpath); }
    if ($function == 'include_once') { return include_once($realpath); }

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
 */
function safe_require_plugin($plugintype, $pluginname, $filename='lib.php', $function='require_once', $nonfatal=false) {
    try {
        safe_require($plugintype, $pluginname, $filename, $function, $nonfatal);
        return true;
    }
    catch (SystemException $e) {
        if (get_field($plugintype . '_installed', 'active', 'name', $pluginname) == 1) {
            global $SESSION;

            set_field($plugintype . '_installed', 'active', 0, 'name', $pluginname);
            $SESSION->add_error_msg(get_string('missingplugindisabled', 'admin', hsc("$plugintype:$pluginname")));

            // Reset the plugin cache.
            plugins_installed('', TRUE, TRUE);

            // Alert site admins that the plugin is broken so was disabled
            $message = new stdClass();
            $message->users = get_column('usr', 'id', 'admin', 1);
            $message->subject = get_string('pluginbrokenanddisabledtitle', 'mahara', $pluginname);
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
 * @return  bool
 */
function is_plugin_active($pluginname) {
    foreach (plugin_types() as $type) {
        if (record_exists($type . '_installed', 'name', $pluginname, 'active', 1)) {
            return true;
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
        $pluginstocheck = array('artefact', 'auth', 'notification', 'search', 'blocktype', 'interaction', 'grouptype', 'import', 'export', 'module');
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
function handle_event($event, $data) {
    global $USER;
    static $event_types = array(), $coreevents_cache = array(), $eventsubs_cache = array();

    if (empty($event_types)) {
        $event_types = array_fill_keys(get_column('event_type', 'name'), true);
    }

    $e = $event_types[$event];

    if (is_null($e)) {
        throw new SystemException("Invalid event");
    }

    if ($data instanceof ArtefactType) {
        // leave $data alone, but convert for the event log
        $logdata = $data->to_stdclass();
    }
    else if ($data instanceof BlockInstance) {
        // leave $data alone, but convert for the event log
        $logdata = array(
            'id' => $data->get('id'),
            'blocktype' => $data->get('blocktype'),
        );
    }
    else if (is_object($data)) {
        $data = (array)$data;
    }
    else if (is_numeric($data)) {
        $data = array('id' => $data);
    }

    $parentuser = $USER->get('parentuser');
    $eventloglevel = get_config('eventloglevel');
    if ($eventloglevel === 'all'
            or ($parentuser and $eventloglevel === 'masq')) {
        $logentry = (object) array(
            'usr'      => $USER->get('id'),
            'realusr'  => $parentuser ? $parentuser->id : $USER->get('id'),
            'event'    => $event,
            'data'     => json_encode(isset($logdata) ? $logdata : $data),
            'time'     => db_format_timestamp(time()),
        );
        insert_record('event_log', $logentry);
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
                    call_static_method($classname, $sub->callfunction, $event, $data);
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
        return strftime($fixedkey, $date);
    }
    return strftime(get_string($formatkey), $date);
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
        'requiredmarker' => true,
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

function pieform_element_calendar_configure($element) {
    global $THEME;
    $element['jsroot'] = get_config('wwwroot') . 'js/jquery/jquery-ui/';
    $element['themefile'] = $THEME->get_url('style/datepicker.css');
    $element['imagefile'] = $THEME->get_image_url('btn_calendar');
    $language = substr(current_language(), 0, 2);
    $element['language'] = $language;
    return $element;
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
        $filepath = get_config('docroot') . 'theme/' . $themedir . '/' . $pluginlocation . '/pieforms/' . $file;
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

        // Suspended user
        if ($ownerobj->suspendedctime) {
            return false;
        }

        // Probationary user (no public pages or profiles)
        // (setting these here instead of doing a return-false, so that we can do checks for
        // logged-in users later)
        require_once(get_config('libroot') . 'antispam.php');
        $onprobation = is_probationary_user($ownerobj->id);
        $publicviews = $publicviews && !$onprobation;
        $publicprofiles = $publicprofiles && !$onprobation;

        // Member of an institution that prohibits public pages
        // (group views and logged in users are not affected by
        // the institution level config for public views)
        $owner = new User();
        $owner->find_by_id($ownerobj->id);
        $publicviews = $publicviews && $owner->institution_allows_public_views();
    }

    // Now that we've examined the page owner, check again for whether it can be viewed by a logged-out user
    if (!$user_id && !$publicviews && !$publicprofiles) {
        return false;
    }

    if ($user_id && $user->can_edit_view($view)) {
        return true;
    }

    // If the view's owner is suspended, deny access to the view
    if ($view->get('owner')) {
        if ((!$owner = $view->get_owner_object()) || $owner->suspendedctime) {
            return false;
        }
    }

    if ($SESSION->get('mnetuser')) {
        $mnettoken = get_cookie('mviewaccess:' . $view_id);
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
            else if ($publicprofiles && $view->get('type') == 'profile') {
                return true;
            }
        }
        else if ($a->token && ($allowedbyoverride || !$a->visible)) {
            $usertoken = get_cookie('viewaccess:'.$view_id);
            if ($a->token == $usertoken && $publicviews) {
                return true;
            }
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
 */
function get_view_from_token($token, $visible=true) {
    if (!$token) {
        return false;
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
        return false;
    }
    if (count($viewids) > 1) {
        // if any of the views are in collection(s), pick one of the ones
        // with the lowest displayorder.
        $order = get_records_sql_array('
            SELECT cv.view, collection
            FROM {collection_view} cv
            WHERE cv.view IN (' . join(',', $viewids) . ')
            ORDER BY displayorder, collection',
            array()
        );
        if ($order) {
            if ($token != get_cookie('caccess:'.$order[0]->collection)) {
                set_cookie('caccess:'.$order[0]->collection, $token, 0, true);
            }
            return $order[0]->view;
        }
    }
    $viewid = $viewids[0];
    if (!$visible && $token != get_cookie('mviewaccess:'.$viewid)) {
        set_cookie('mviewaccess:'.$viewid, $token, 0, true);
    }
    if ($visible && $token != get_cookie('viewaccess:'.$viewid)) {
        set_cookie('viewaccess:'.$viewid, $token, 0, true);
    }
    return $viewid;
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
    $wwwroot = get_config('wwwroot');
    $wwwroot = preg_replace('#^https?://#', '', $wwwroot);
    return substr($wwwroot, strpos($wwwroot, '/'));
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
    $logstring .= ' memory_total: '.$info['memory_total'].'B (' . display_size($info['memory_total']).') memory_growth: '.$info['memory_growth'].'B ('.display_size($info['memory_growth']).')';
    $logstring .= ' time: '.$info['realtime'].'s';
    $logstring .= ' includecount: '.$info['includecount'];
    $logstring .= ' dbqueries: '.$info['dbreads'] . ' reads, ' . $info['dbwrites'] . ' writes, ' . $info['dbcached'] . ' cached';
    $logstring .= ' ticks: ' . $info['ticks']  . ' user: ' . $info['utime'] . ' sys: ' . $info['stime'] .' cuser: ' . $info['cutime'] . ' csys: ' . $info['cstime'];
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

    while (list($key) = each($scan)) {
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
        $size = $size .' '. $b;
    }
    return $size;
}

/**
 * creates the profile sideblock
 */
function profile_sideblock() {
    global $USER, $SESSION;
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
                $data['mnetloggedinfrom'] = get_string('youhaveloggedinfrom1', 'auth.xmlrpc', $authobj->wwwroot, $peer->name);
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

    $data['grouplimitstr'] = $limitstr;
    $data['views'] = get_records_sql_array(
        'SELECT v.id, v.title, v.urlid, v.owner
        FROM {view} v
        INNER JOIN {view_tag} vt ON (vt.tag = ? AND vt.view = v.id)
        WHERE v.owner = ?
        ORDER BY v.title',
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
         'SELECT a.id, a.artefacttype, a.title
         FROM {artefact} a
         INNER JOIN {artefact_tag} at ON (a.id = at.artefact AND tag = ?)
         WHERE a.owner = ?
         ORDER BY a.title',
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
    return $data;
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

    // Determine what level of users to show
    // 0 = none, 1 = institution/s only, 2 = all users
    $showusers = 2;
    $institutions = $USER->institutions;
    if (!empty($institutions)) {
        $showusers = 0;
        foreach ($institutions as $i) {
            if ($i->showonlineusers == 2) {
                $showusers = 2;
                break;
            }
            if ($i->showonlineusers == 1) {
                $showusers = 1;
            }
        }
    }

    $maxonlineusers = get_config('onlineuserssideblockmaxusers');
    switch ($showusers) {
        case 0: // show none
            return array(
                'users' => array(),
                'count' => 0,
                'lastminutes' => floor(get_config('accessidletimeout') / 60),
            );
        case 1: // show institution only
            $sql = 'SELECT DISTINCT u.* FROM {usr} u JOIN {usr_institution} i ON u.id = i.usr
                WHERE i.institution IN ('.join(',', array_map('db_quote', array_keys($institutions))).')
                AND lastaccess > ? AND deleted = 0 ORDER BY lastaccess DESC';
            break;
        case 2: // show all
            $sql = 'SELECT * FROM {usr} WHERE lastaccess > ? AND deleted = 0 ORDER BY lastaccess DESC';
            break;
    }

    $onlineusers = get_records_sql_array($sql, array(db_format_timestamp(time() - get_config('accessidletimeout'))), 0, $maxonlineusers);
    if ($onlineusers) {
        foreach ($onlineusers as &$user) {
            $user->profileiconurl = profile_icon_url($user, 20, 20);

            // If the user is an MNET user, show where they've come from
            $authobj = AuthFactory::create($user->authinstance);
            if ($authobj->authname == 'xmlrpc') {
                $peer = get_peer($authobj->wwwroot);
                $user->loggedinfrom = $peer->name;
            }
        }
    }
    else {
        $onlineusers = array();
    }
    return array(
        'users' => $onlineusers,
        'count' => count($onlineusers),
        'lastminutes' => floor(get_config('accessidletimeout') / 60),
    );
}

function tag_weight($freq) {
    return pow($freq, 2);
    // return log10($freq);
}

function get_my_tags($limit=null, $cloud=true, $sort='freq') {
    global $USER;
    $id = $USER->get('id');
    if ($limit || $sort != 'alpha') {
        $sort = 'COUNT(t.tag) DESC';
    }
    else {
        $sort = 't.tag ASC';
    }
    $tagrecords = get_records_sql_array("
        SELECT
            t.tag, COUNT(t.tag) AS count
        FROM (
           (SELECT at.tag, a.id, 'artefact' AS type
            FROM {artefact_tag} at JOIN {artefact} a ON a.id = at.artefact
            WHERE a.owner = ?)
           UNION
           (SELECT vt.tag, v.id, 'view' AS type
            FROM {view_tag} vt JOIN {view} v ON v.id = vt.view
            WHERE v.owner = ?)
           UNION
           (SELECT ct.tag, c.id, 'collection' AS type
            FROM {collection_tag} ct JOIN {collection} c ON c.id = ct.collection
            WHERE c.owner = ?)
    ) t
        GROUP BY t.tag
        ORDER BY " . $sort . (is_null($limit) ? '' : " LIMIT $limit"),
        array($id, $id, $id)
    );
    if (!$tagrecords) {
        return false;
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
        usort($tagrecords, create_function('$a,$b', 'return strnatcasecmp($a->tag, $b->tag);'));
    }
    else {
        foreach ($tagrecords as &$t) {
            $t->tagurl = urlencode($t->tag);
        }
    }
    return $tagrecords;
}

function tags_sideblock() {
    global $USER;
    $maxtags = $USER->get_account_preference('tagssideblockmaxtags');
    $maxtags = is_null($maxtags) ? get_config('tagssideblockmaxtags') : $maxtags;
    if ($tagrecords = get_my_tags($maxtags)) {
        return array('tags' => $tagrecords);
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
            $institution = key(array_slice($institutions, 0, 1));
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
        return array(
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
        return array(
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
    return array(
        'data' => null,
        'percent' => 0,
        'preview' => $preview,
        'count' => 1,
        'institutions' => null,
        'institution' => 'mahara',
    );
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
    $request = array(
        CURLOPT_URL => 'https://launchpad.net/mahara/+download',
    );

    $result = mahara_http_request($request);

    if (!empty($result->errno)) {
        log_debug('Could not retrieve launchpad download page');
        return;
    }

    $page = new DOMDocument();
    libxml_use_internal_errors(true);
    $success = $page->loadHTML($result->data);
    libxml_use_internal_errors(false);
    if (!$success) {
        log_debug('Error parsing launchpad download page');
        return;
    }
    $xpath = new DOMXPath($page);
    $query = '//div[starts-with(@id,"release-information-")]';
    $elements = $xpath->query($query);
    $versions = array();
    foreach ($elements as $e) {
        if (preg_match('/^release-information-(\d+)-(\d+)-(\d+)$/', $e->getAttribute('id'), $match)) {
            $versions[] = "$match[1].$match[2].$match[3]";
        }
    }
    if (!empty($versions)) {
        usort($versions, 'strnatcmp');
        set_config('latest_version', end($versions));
    }
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
    require_once(get_config('libroot') . 'registration.php');
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

    graph_site_data_weekly();
}

function cron_site_data_daily() {
    require_once(get_config('libroot') . 'registration.php');
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
    require_once(get_config('libroot') . 'registration.php');
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
        graph_institution_data_weekly($current);
    }

}

function cron_institution_data_daily() {
    require_once(get_config('libroot') . 'registration.php');
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
            $where .= " AND id IN (" . join(',', array_fill(0, $current['users'], '?')) . ")";
            $loggedin = count_records_select('usr', $where, array_merge(array($time, $time), $current['members']));
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
            'time < CURRENT_DATE - INTERVAL ' .
                (is_postgres() ? "'" . $expiry . " seconds'" : $expiry . ' SECOND')
        );

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
            $item->typestr = get_string('view');
            $item->icon    = $THEME->get_image_url('page');
            $v = new View(0, (array)$item);
            $v->set('dirty', false);
            $item->url = $v->get_url();
        }
        else if ($item->type == 'collection') {
            $item->typestr = get_string('Collection', 'collection');
            $item->icon    = $THEME->get_image_url('collection');
            $c = new Collection(0, (array)$item);
            $item->url = $c->get_url();
        }
        else { // artefact
            safe_require('artefact', $artefacttypes[$item->artefacttype]->plugin);
            $links = call_static_method(generate_artefact_class_name($item->artefacttype), 'get_links', $item->id);
            $item->url     = $links['_default'];
            $item->icon    = call_static_method(generate_artefact_class_name($item->artefacttype), 'get_icon', array('id' => $item->id));
            if ($item->artefacttype == 'task') {
                $item->typestr = get_string('Task', 'artefact.plans');
            }
            else {
                $item->typestr = get_string($item->artefacttype, 'artefact.' . $artefacttypes[$item->artefacttype]->plugin);
            }
        }
    }

    $data->baseurl = get_config('wwwroot') . 'tags.php' . (is_null($data->tag) ? '' : '?tag=' . urlencode($data->tag));
    $data->sortcols = array('name', 'date');
    $data->filtercols = array(
        'all'        => get_string('tagfilter_all'),
        'file'       => get_string('tagfilter_file'),
        'image'      => get_string('tagfilter_image'),
        'text'       => get_string('tagfilter_text'),
        'view'       => get_string('tagfilter_view'),
        'collection' => get_string('tagfilter_collection'),
    );

    $smarty = smarty_core();
    $smarty->assign_by_ref('data', $data->data);
    $smarty->assign('owner', $data->owner->id);
    $data->tablerows = $smarty->fetch('portfoliosearchresults.tpl');
    $pagination = build_pagination(array(
        'id' => 'results_pagination',
        'class' => 'center',
        'url' => $data->baseurl . ($data->sort == 'name' ? '' : '&sort=' . $data->sort) . ($data->filter == 'all' ? '' : '&type=' . $data->filter),
        'jsonscript' => 'json/tagsearch.php',
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
    return ((!get_config('wysiwyg') && $USER->get_account_preference('wysiwyg')) ||
        get_config('wysiwyg') == 'enable') && $SESSION->get('handheld_device') == false;
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
    if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
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

function generate_csv($data, $csvfields) {
    $csv = join(',', $csvfields) . "\n";
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
                log_warn("Plugin $plugin $dir is not installable: " . $_e->GetMessage());
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
