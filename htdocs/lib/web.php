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
 * This function creates a Smarty object and sets it up for use within our
 * podclass app, setting up some variables.
 *
 * The variables that it sets up are:
 *
 * - THEMEURL: The base url for static content
 * - WWWROOT: The base url for the Mahara system
 * - USER: The user object
 * - JAVASCRIPT: A list of javascript files to include in the header.  This
 *   list is passed into this function (see below).
 * - HEADERS: An array of any further headers to set.  Each header is just
 *   straight HTML (see below).
 * - PUBLIC: Set true if this page is a public page
 * - MAINNAV: Array defining the main navigation
 *
 * @param $javascript A list of javascript includes.  Each include should be just
 *                    the name of a file, and reside in {$THEMEURL}js/{filename}
 * @param $headers    A list of additional headers.  These are to be specified as
 *                    actual HTML.
 * @param $strings    A list of language strings required by the javascript code.
 * @return Smarty
 */

//smarty(array('js/tablerenderer.js', 'artefact/file/js/filebrowser.js'))
function &smarty($javascript = array(), $headers = array(), $pagestrings = array(), $extraconfig = array()) {
    global $USER, $SESSION;
    $SIDEBLOCKS = array();

    require_once(get_config('libroot') . 'smarty/Smarty.class.php');
    $wwwroot = get_config('wwwroot');

    $theme_list = array();
    
    if (function_exists('pieform_get_headdata')) {
        $headers = array_merge($headers, pieform_get_headdata());
    }

    // Insert the appropriate javascript tags 
    $javascript_array = array();
    $jsroot = $wwwroot . 'js/';

    // TinyMCE must be included first for some reason we're not sure about
    $checkarray = array(&$javascript, &$headers);
    foreach ($checkarray as &$check) {
        if (($key = array_search('tinymce', $check)) !== false) {
            $javascript_array[] = $jsroot . 'tinymce/tiny_mce.js';
            if (isset($extraconfig['tinymceinit'])) {
                $headers[] = $extraconfig['tinymceinit'];
            }
            else {
                $content_css = json_encode(theme_get_url('style/tinymce.css'));
                $language = substr(current_language(), 0, 2);
                $headers[] = <<<EOF
<script type="text/javascript">
tinyMCE.init({
    mode: "textareas",
    editor_selector: 'wysiwyg',
    button_tile_map: true,
    language: '{$language}',
    theme: "advanced",
    plugins: "table,emotions,iespell,inlinepopups,paste",
    theme_advanced_buttons1 : "bold,italic,underline,strikethrough,separator,forecolor,backcolor,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,hr,emotions,iespell,cleanup,separator,link,unlink,separator,code",
    theme_advanced_buttons2 : "bullist,numlist,separator,tablecontrols,separator,cut,copy,paste,pasteword",
    theme_advanced_buttons3 : "fontselect,separator,fontsizeselect,separator,formatselect",
    theme_advanced_toolbar_location : "top",
    theme_advanced_toolbar_align : "center",
    width: '512',
    content_css : {$content_css}
});
</script>

EOF;
            }
            unset($check[$key]);
            break;
        }
    }

    if (get_config('developermode')) {
        $javascript_array[] = $jsroot . 'MochiKit/MochiKit.js';
    }
    else {
        $javascript_array[] = $jsroot . 'MochiKit/Packed.js';
    }
    $javascript_array[] = $jsroot . 'keyboardNavigation.js';

    $strings = array();
    foreach ($pagestrings as $k => $v) {
        if (is_array($v)) {
            foreach ($v as $tag) {
                $strings[$tag] = get_raw_string($tag, $k);
            }
        }
        else {
            $strings[$k] = get_raw_string($k, $v);
        }
    }

    $jsstrings = jsstrings();
    $themepaths = themepaths();

    foreach ($javascript as $jsfile) {
        // For now, if there's no path in the js file, assume it's in
        // $jsroot and append '.js' to the name.  Later we may want to
        // ensure all smarty() calls include the full path to the js
        // file, with the proper extension.
        if (strpos($jsfile, '/') === false) {
            $javascript_array[] = $jsroot . $jsfile . '.js';
            if (isset($jsstrings[$jsfile])) {
                foreach ($jsstrings[$jsfile] as $section => $tags) {
                    foreach ($tags as $tag) {
                        $strings[$tag] = get_raw_string($tag, $section);
                    }
                }
            }
            if (isset($themepaths[$jsfile])) {
                foreach ($themepaths[$jsfile] as $themepath) {
                    $theme_list[$themepath] = theme_get_url($themepath);
                }
            }
        }
        else {
            // A .js file with a fully specified path
            $javascript_array[] = $wwwroot . $jsfile;
            // If $jsfile is from a plugin (i.e. plugintype/pluginname/js/foo.js)
            // Then get js strings from static function jsstrings in plugintype/pluginname/lib.php 
            $bits = explode('/', $jsfile);
            if (count($bits) == 4) {
                safe_require($bits[0], $bits[1]);
                $pluginclass = generate_class_name($bits[0], $bits[1]);
                $name = substr($bits[3], 0, strpos($bits[3], '.js'));
                if (is_callable(array($pluginclass, 'jsstrings'))) {
                    $tempstrings = call_static_method($pluginclass, 'jsstrings', $name);
                    foreach ($tempstrings as $section => $tags) {
                        foreach ($tags as $tag) {
                            $strings[$tag] = get_raw_string($tag, $section);
                        }
                    }
                }
                if (is_callable(array($pluginclass, 'jshelp'))) {
                    $tempstrings = call_static_method($pluginclass, 'jshelp', $name);
                    foreach ($tempstrings as $section => $tags) {
                        foreach ($tags as $tag) {
                            $strings[$tag . '.help'] = get_help_icon($bits[0], $bits[1], null, null,
                                                                     null, $tag);
                        }
                    }
                }
                if (is_callable(array($pluginclass, 'themepaths'))) {
                    $tmpthemepaths = call_static_method($pluginclass, 'themepaths', $name);
                    foreach ($tmpthemepaths as $themepath) {
                        $theme_list[$themepath] = theme_get_url($themepath);
                    }
                }
            }
        }
    }

    $javascript_array[] = $jsroot . 'mahara.js';
    if (get_config('developermode')) {
        $javascript_array[] = $jsroot . 'debug.js';
        if (isset($_SERVER['HTTP_USER_AGENT']) && false === stripos($_SERVER['HTTP_USER_AGENT'], 'gecko')) {
            $javascript_array[] = $jsroot . 'firebug/firebug.js';
        }
    }

    foreach ($jsstrings['mahara'] as $section => $tags) {
        foreach ($tags as $tag) {
            $strings[$tag] = get_raw_string($tag, $section);
        }
    }
    foreach ($themepaths['mahara'] as $themepath) {
        $theme_list[$themepath] = theme_get_url($themepath);
    }
    if (isset($extraconfig['themepaths']) && is_array($extraconfig['themepaths'])) {
        foreach ($extraconfig['themepaths'] as $themepath) {
            $theme_list[$themepath] = theme_get_url($themepath);
        }
    }

    $stringjs = '<script type="text/javascript">';
    $stringjs .= 'var strings = ' . json_encode($strings) . ';';
    $stringjs .= '</script>';
    $headers[] = $stringjs;

    $smarty =& new Smarty();
    
    $theme = theme_setup();

    $smarty->template_dir = $theme->template_dir;

    $smarty->compile_dir  = get_config('dataroot').'smarty/compile';
    $smarty->cache_dir    = get_config('dataroot').'smarty/cache';

    $smarty->assign('THEMEURL', get_config('themeurl'));

    // stylesheet set up - if we're in a plugin also get its stylesheet
    $stylesheets = array_reverse(theme_get_url('style/style.css', null, true));
    if (defined('SECTION_PLUGINTYPE') && defined('SECTION_PLUGINNAME') && SECTION_PLUGINTYPE != 'core') {
        if ($pluginsheets = theme_get_url('style/style.css', SECTION_PLUGINTYPE . '/' . SECTION_PLUGINNAME . '/', true)) {
            $stylesheets = array_merge($stylesheets, array_reverse($pluginsheets));
        }
    }
    if (get_config('developermode')) {
        $stylesheets[] = get_config('wwwroot') . 'theme/debug.css';
    }
    $smarty->assign('STYLESHEETLIST', $stylesheets);
    $smarty->assign('WWWROOT', $wwwroot);
    $smarty->assign('SESSKEY', $USER->get('sesskey'));
    $smarty->assign('THEMELIST', json_encode($theme_list));

    if (defined('TITLE')) {
        $smarty->assign('PAGETITLE', TITLE . ' - ' . get_config('sitename'));
    }
    else {
        $smarty->assign('PAGETITLE', get_config('sitename'));
    }


    $sitename = get_config('sitename');
    $smarty->assign('title', $sitename);
    $smarty->assign('heading', $sitename);

    if (defined('PUBLIC')) {
        $smarty->assign('PUBLIC', true);
    }
    if (defined('ADMIN')) {
        $smarty->assign('ADMIN', true);
    }

    $smarty->assign('LOGGEDIN', $USER->is_logged_in());
    if ($USER->is_logged_in()) {
        $smarty->assign('MAINNAV', main_nav());
        $smarty->assign('LOGGEDINSTR', get_loggedin_string());
    }

    $smarty->assign_by_ref('USER', $USER);
    $smarty->assign_by_ref('JAVASCRIPT', $javascript_array);
    $smarty->assign_by_ref('HEADERS', $headers);

    $smarty->assign('searchform', searchform());

    if ($help = has_page_help()) {
        $smarty->assign('PAGEHELPNAME', $help[0]);
        $smarty->assign('PAGEHELPICON', $help[1]);
    }

    // ---------- sideblock stuff ----------
    if (get_config('installed')) {
        $smarty->assign('SITEMENU', site_menu());
        $SIDEBLOCKS[] = array(
            'name'   => 'mainmenu',
            'weight' => 10,
            'data'   => site_menu(),
        );
    }

    if ($USER->is_logged_in() && defined('MENUITEM') && substr(MENUITEM, 0, 11) == 'myportfolio') {
        $SIDEBLOCKS[] = array(
            'name'   => 'selfsearch',
            'weight' => 0,
            'data'   => array(),
        );
    }
   
   if (!$USER->is_logged_in()) {
        $SIDEBLOCKS[] = array(
            'name'   => 'login',
            'weight' => -10,
            'id'     => 'loginbox',
            'data'   => array(
                'loginform' => auth_generate_login_form(),
            ),
        );
    }

    if (get_config('enablenetworking')) {
        require_once(get_config('docroot') .'api/xmlrpc/lib.php');
        if ($USER->is_logged_in() && $ssopeers = get_service_providers($USER->authinstance)) {
            $SIDEBLOCKS[] = array(
                'name'   => 'ssopeers',
                'weight' => 1,
                'data'   => $ssopeers,
            );
        }
    }

    if (isset($extraconfig['sideblocks']) && is_array($extraconfig['sideblocks'])) {
        foreach ($extraconfig['sideblocks'] as $sideblock) {
            $SIDEBLOCKS[] = $sideblock;
        }
    }

    usort($SIDEBLOCKS, create_function('$a,$b', 'if ($a["weight"] == $b["weight"]) return 0; return ($a["weight"] < $b["weight"]) ? -1 : 1;'));

    $smarty->assign('userauthinstance', $USER->lastauthinstance);
    $smarty->assign('SIDEBLOCKS', $SIDEBLOCKS);

    return $smarty;
}

/** 
 * Returns the lists of strings used in the .js files
 * @return array                   
 */

function jsstrings() {
    return array(
       'mahara' => array(                        // js file
            'mahara' => array(                   // section
                'namedfieldempty',               // string name
                'processing',
                'requiredfieldempty',
                'unknownerror',
                'loading',
                'showtags',
                'unreadmessages',
                'unreadmessage',
                'couldnotgethelp',
            ),
        ),
        'tablerenderer' => array(
            'mahara' => array(
                'firstpage',
                'nextpage',
                'prevpage',
                'lastpage',
            )
        ),
        'collapsabletree' => array(
            'view' => array(
                'nochildren',
            ),
        ),
        'friends' => array(
            'mahara' => array(
                'confirmremovefriend',
                'seeallviews',
                'noviewstosee',
                'sendmessage',
                'whymakemeyourfriend',
                'approverequest',
                'denyrequest',
                'pending',
                'removefromfriendslist',
                'views',
                'trysearchingforfriends',
                'nobodyawaitsfriendapproval',
                'sendfriendrequest',
                'addtomyfriends',
                'friendshiprequested',
                'userdoesntwantfriends',
                'existingfriend',
                'nosearchresultsfound',
                'reason',
                'requestfriendship',
                'cancel',
            ),
        ),
    );
}

function themepaths() {
    return array(
        'mahara' => array(
            'images/icon_close.gif',
            'images/failure.gif',
            'images/loading.gif',
            'images/success.gif',
            'images/icon_help.gif',
        ),
    );
}

/** 
 * Takes an array of string identifiers and returns an array of the
 * corresponding strings, quoted for use in inline javascript here
 * docs.
 */

function quotestrings($strings) {
    $qstrings = array();
    foreach ($strings as $section => $tags) {
        foreach ($tags as $tag) {
            $qstrings[$tag] = json_encode(get_string($tag, $section));
        }
    }
    return $qstrings;
}

/** 
 * This function sets up and caches info about the current selected theme
 * contains inheritance path (used for locating images) and template dirs
 * and potentially more stuff later ( like mime header to send (html vs xhtml))
 * @return object
 */

function theme_setup() {
    
    static $theme;

    if (!empty($theme)) {
        return $theme;
    }
    
    $theme = new StdClass;
    $theme->theme = get_config('theme');
    $theme->path = get_config('docroot') . 'theme/' . $theme->theme . '/';
    $theme->template_dir = array($theme->path . 'templates/');
    $theme->inheritance = array($theme->theme);

    $parent = $theme->theme;

    while (true) {
        if (!$parent = theme_get_parent($parent)) {
            break;
        }
        if ($parent != 'default') {
            $theme->template_dir[] = get_config('docroot') . 'theme/' . $parent . '/templates/';
            $theme->inheritance[] = $parent;
        }
    }

    // always put the default theme at the top of the tree, unless we're already it
    if ($theme->theme != 'default') {
        $theme->template_dir[] = get_config('docroot')  . 'theme/default/templates/';
        $theme->inheritance[] = 'default';
    }

    return $theme;
}

/** 
 * helper function to walk up the inheritance tree and find a parent
 * @param $currtheme the name of the theme to find the parent for
 * @return parent name or false
 */
function theme_get_parent($currtheme) {

    // look for a config file 
    if (is_readable(get_config('docroot') . 'theme/ ' . $currtheme. '/config.php')) {
        require_once(get_config('docroot') . 'theme/ ' . $currtheme. '/config.php');
        if (!empty($theme->parent) && is_dir(get_config('docroot') . 'theme/ ' . $theme->parent)) {
            return $theme->parent;
        }
    }
    return false;
}

/** 
 * This function returns the full url to an image
 * Always use it to get image urls
 * @param $imagelocation path to image relative to theme/$theme/static/
 * @param $pluginlocation path to plugin relative to docroot
 */
function theme_get_url($location, $pluginlocation='', $all = false) {
    $theme = theme_setup();
    $list = array();

    foreach ($theme->inheritance as $themedir) {
        if (is_readable(get_config('docroot') . $pluginlocation . 'theme/' . $themedir . '/static/' . $location)) {
            if ($all) {
                $list[] = get_config('wwwroot') . $pluginlocation . 'theme/' . $themedir . '/static/' . $location;
            }
            else {
                return get_config('wwwroot') . $pluginlocation . 'theme/' . $themedir . '/static/' . $location;
            }
        }
    }
    if ($all) {
        return $list;
    }
    return;
}

/** 
 * This function returns the full path to an image
 * Always use it to get image paths
 * @param $imagelocation path to image relative to theme/$theme/static/
 * @param $pluginlocation path to plugin relative to docroot
 */
function theme_get_path($location, $pluginlocation='') {
    $theme = theme_setup();

    foreach ($theme->inheritance as $themedir) {
        if (is_readable(get_config('docroot') . $pluginlocation . 'theme/' . $themedir . '/static/' . $location)) {
            return get_config('docroot') . $pluginlocation . 'theme/' . $themedir . '/static/' . $location;
        }
    }
}

/**
 * This function sends headers suitable for all JSON returning scripts.
 *
 */
function json_headers() {
    // @todo martyn This should be changed, but for now it's useful for debugging
    // header('Content-type: text/x-json');
    header('Content-type: text/plain');
    header('Pragma: no-cache');
}

/**
 * This function sends a JSON message, and ends the script.
 *
 * Scripts receiving replies will recieve a JSON array with two fields:
 *
 *  - error: True or false depending on whether the request was successful
 *  - message: JSON data representing a message sent back from the script
 *
 * @param boolean $error   Whether the script ended in an error or not
 * @param string  $message A message to pass back to the user, can be an
 *                         array of JSON data
 */
function json_reply($error, $message, $returncode=0) {
    json_headers();
    echo json_encode(array('error' => $error, 'message' => $message, 'returnCode' => $returncode));
    perf_to_log();
    exit;
}

function _param_retrieve($name) {
    // prefer post
    if (isset($_POST[$name])) {
        $value = $_POST[$name];
    } 
    else if (isset($_GET[$name])) {
        $value = $_GET[$name];
    }
    else if (func_num_args() == 2) {
        $php_work_around = func_get_arg(1);
        return array($php_work_around, true);
    }
    else {
        throw new ParameterException("Missing parameter '$name' and no default supplied");
    }

    return array($value, false);
}

/**
 * This function returns a GET or POST parameter with optional default.  If the
 * default isn't specified and the parameter hasn't been sent, a
 * ParameterException exception is thrown
 *
 * @param string The GET or POST parameter you want returned
 * @param mixed [optional] the default value for this parameter
 *
 * @return string The value of the parameter
 *
 */
function param_variable($name) {
    $args = func_get_args();
    list ($value) = call_user_func_array('_param_retrieve', $args);
    return $value;
}

/**
 * This function returns a GET or POST parameter as an integer with optional
 * default.  If the default isn't specified and the parameter hasn't been sent,
 * a ParameterException exception is thrown. Likewise, if the parameter isn't a
 * valid integer, a ParameterException exception is thrown
 *
 * @param string The GET or POST parameter you want returned
 * @param mixed [optional] the default value for this parameter
 *
 * @return int The value of the parameter
 *
 */
function param_integer($name) {
    $args = func_get_args();

    list ($value, $defaultused) = call_user_func_array('_param_retrieve', $args);

    if ($defaultused) {
        return $value;
    }

    if (preg_match('/^\d+$/',$value)) {
        return (int)$value;
    }

    throw new ParameterException("The '$name' parameter is not an integer");
}

/**
 * This function returns a GET or POST parameter as an alpha string with optional
 * default.  If the default isn't specified and the parameter hasn't been sent,
 * a ParameterException exception is thrown. Likewise, if the parameter isn't a
 * valid alpha string, a ParameterException exception is thrown
 *
 * Valid characters are a-z and A-Z
 *
 * @param string The GET or POST parameter you want returned
 * @param mixed [optional] the default value for this parameter
 *
 * @return string The value of the parameter
 *
 */
function param_alpha($name) {
    $args = func_get_args();

    list ($value, $defaultused) = call_user_func_array('_param_retrieve', $args);

    if ($defaultused) {
        return $value;
    }

    if (preg_match('/^[a-zA-Z]+$/',$value)) {
        return $value;
    }

    throw new ParameterException("The '$name' parameter is not alphabetical only");
}

/**
 * This function returns a GET or POST parameter as an alphanumeric string with optional
 * default.  If the default isn't specified and the parameter hasn't been sent,
 * a ParameterException exception is thrown. Likewise, if the parameter isn't a
 * valid alpha string, a ParameterException exception is thrown
 *
 * Valid characters are a-z and A-Z and 0-9
 *
 * @param string The GET or POST parameter you want returned
 * @param mixed [optional] the default value for this parameter
 *
 * @return string The value of the parameter
 *
 */
function param_alphanum($name) {
    $args = func_get_args();

    list ($value, $defaultused) = call_user_func_array('_param_retrieve', $args);

    if ($defaultused) {
        return $value;
    }

    if (preg_match('/^[a-zA-Z0-9]+$/',$value)) {
        return $value;
    }

    throw new ParameterException("The '$name' parameter is not alphanumeric only");
}

/**
 * This function returns a GET or POST parameter as an alphanumeric string with optional
 * default.  If the default isn't specified and the parameter hasn't been sent,
 * a ParameterException exception is thrown. Likewise, if the parameter isn't a
 * valid alpha string, a ParameterException exception is thrown
 *
 * Valid characters are a-z and A-Z and 0-9 and _ and - and .
 *
 * @param string The GET or POST parameter you want returned
 * @param mixed [optional] the default value for this parameter
 *
 * @return string The value of the parameter
 *
 */
function param_alphanumext($name) {
    $args = func_get_args();

    list ($value, $defaultused) = call_user_func_array('_param_retrieve', $args);

    if ($defaultused) {
        return $value;
    }

    if (preg_match('/^[a-zA-Z0-9_.-]+$/',$value)) {
        return $value;
    }

    throw new ParameterException("The '$name' parameter contains invalid characters");
}

/**
 * This function returns a GET or POST parameter as an array of integers with optional
 * default.  If the default isn't specified and the parameter hasn't been sent,
 * a ParameterException exception is thrown. Likewise, if the parameter isn't a
 * valid integer list , a ParameterException exception is thrown.
 *
 * An integer list is integers separated by commas (with optional whitespace),
 * or just whitespace which indicates an empty list
 *
 * @param string The GET or POST parameter you want returned
 * @param mixed [optional] the default value for this parameter
 *
 * @return array The value of the parameter
 *
 */
function param_integer_list($name) {
    $args = func_get_args();

    list ($value, $defaultused) = call_user_func_array('_param_retrieve', $args);

    if ($defaultused) {
        return $value;
    }

    if ($value == '') {
        return array();
    }

    if (preg_match('/^(\d+(,\d+)*)$/',$value)) {
        return array_map('intval', explode(',', $value));
    }

    throw new ParameterException("The '$name' parameter is not an integer list");
}

/**
 * This function returns a GET or POST parameter as a boolean.
 *
 * @param string The GET or POST parameter you want returned
 *
 * @return bool The value of the parameter
 *
 */
function param_boolean($name) {
    
    list ($value) = _param_retrieve($name, false);

    if (empty($value) || $value == 'off' || $value == 'no' || $value == 'false') {
        return false;
    }
    else {
        return true;
    }
}

/**
 * Gets a cookie, respecting the configured cookie prefix
 *
 * @param string $name The name of the cookie to get the value of
 * @return string      The value of the cookie, or null if the cookie does not
 *                     exist.
 */
function get_cookie($name) {
    $name = get_config('cookieprefix') . $name;
    return (isset($_COOKIE[$name])) ? $_COOKIE[$name] : null;
}

/**
 * Sets a cookie, respecting the configured cookie prefix
 *
 * @param string $name    The name of the cookie
 * @param string $value   The value for the cookie
 * @param int    $expires The unix timestamp of the time the cookie should expire
 * @todo path/domain/secure: should be set automatically by this function if possible (?)
 */
function set_cookie($name, $value='', $expires=0, $path='', $domain='', $secure=false) {
    $name = get_config('cookieprefix') . $name;
    setcookie($name, $value, $expires, $path, $domain, $secure);
}

/**
 * Returns an assoc array of countrys suitable for use with the "select" form
 * element
 *
 * @return array Associative array of countrycodes => countrynames
 */
function getoptions_country() {
    return array(
        'af' => 'Afghanistan',
        'ax' => 'Åland Islands',
        'al' => 'Albania',
        'dz' => 'Algeria',
        'as' => 'American Samoa',
        'ad' => 'Andorra',
        'ao' => 'Angola',
        'ai' => 'Anguilla',
        'aq' => 'Antarctica',
        'ag' => 'Antigua and Barbuda',
        'ar' => 'Argentina',
        'am' => 'Armenia',
        'aw' => 'Aruba',
        'au' => 'Australia',
        'at' => 'Austria',
        'az' => 'Azerbaijan',
        'bs' => 'Bahamas',
        'bh' => 'Bahrain',
        'bd' => 'Bangladesh',
        'bb' => 'Barbados',
        'by' => 'Belarus',
        'be' => 'Belgium',
        'bz' => 'Belize',
        'bj' => 'Benin',
        'bm' => 'Bermuda',
        'bt' => 'Bhutan',
        'bo' => 'Bolivia',
        'ba' => 'Bosnia and Herzegovina',
        'bw' => 'Botswana',
        'bv' => 'Bouvet Island',
        'br' => 'Brazil',
        'io' => 'British Indian Ocean Territory',
        'bn' => 'Brunei Darussalam',
        'bg' => 'Bulgaria',
        'bf' => 'Burkina Faso',
        'bi' => 'Burundi',
        'kh' => 'Cambodia',
        'cm' => 'Cameroon',
        'ca' => 'Canada',
        'cv' => 'Cape Verde',
        'ky' => 'Cayman Islands',
        'cf' => 'Central African Republic',
        'td' => 'Chad',
        'cl' => 'Chile',
        'cn' => 'China',
        'cx' => 'Christmas Island',
        'cc' => 'Cocos (Keeling) Islands',
        'co' => 'Colombia',
        'km' => 'Comoros',
        'cg' => 'Congo',
        'cd' => 'Congo, The Democratic Republic of The',
        'ck' => 'Cook Islands',
        'cr' => 'Costa Rica',
        'ci' => 'Cote D\'ivoire',
        'hr' => 'Croatia',
        'cu' => 'Cuba',
        'cy' => 'Cyprus',
        'cz' => 'Czech Republic',
        'dk' => 'Denmark',
        'dj' => 'Djibouti',
        'dm' => 'Dominica',
        'do' => 'Dominican Republic',
        'ec' => 'Ecuador',
        'eg' => 'Egypt',
        'sv' => 'El Salvador',
        'gq' => 'Equatorial Guinea',
        'er' => 'Eritrea',
        'ee' => 'Estonia',
        'et' => 'Ethiopia',
        'fk' => 'Falkland Islands (Malvinas)',
        'fo' => 'Faroe Islands',
        'fj' => 'Fiji',
        'fi' => 'Finland',
        'fr' => 'France',
        'gf' => 'French Guiana',
        'pf' => 'French Polynesia',
        'tf' => 'French Southern Territories',
        'ga' => 'Gabon',
        'gm' => 'Gambia',
        'ge' => 'Georgia',
        'de' => 'Germany',
        'gh' => 'Ghana',
        'gi' => 'Gibraltar',
        'gr' => 'Greece',
        'gl' => 'Greenland',
        'gd' => 'Grenada',
        'gp' => 'Guadeloupe',
        'gu' => 'Guam',
        'gt' => 'Guatemala',
        'gg' => 'Guernsey',
        'gn' => 'Guinea',
        'gw' => 'Guinea-bissau',
        'gy' => 'Guyana',
        'ht' => 'Haiti',
        'hm' => 'Heard Island and Mcdonald Islands',
        'va' => 'Holy See (Vatican City State)',
        'hn' => 'Honduras',
        'hk' => 'Hong Kong',
        'hu' => 'Hungary',
        'is' => 'Iceland',
        'in' => 'India',
        'id' => 'Indonesia',
        'ir' => 'Iran, Islamic Republic of',
        'iq' => 'Iraq',
        'ie' => 'Ireland',
        'im' => 'Isle of Man',
        'il' => 'Israel',
        'it' => 'Italy',
        'jm' => 'Jamaica',
        'jp' => 'Japan',
        'je' => 'Jersey',
        'jo' => 'Jordan',
        'kz' => 'Kazakhstan',
        'ke' => 'Kenya',
        'ki' => 'Kiribati',
        'kp' => 'Korea, Democratic People\'s Republic of',
        'kr' => 'Korea, Republic of',
        'kw' => 'Kuwait',
        'kg' => 'Kyrgyzstan',
        'la' => 'Lao People\'s Democratic Republic',
        'lv' => 'Latvia',
        'lb' => 'Lebanon',
        'ls' => 'Lesotho',
        'lr' => 'Liberia',
        'ly' => 'Libyan Arab Jamahiriya',
        'li' => 'Liechtenstein',
        'lt' => 'Lithuania',
        'lu' => 'Luxembourg',
        'mo' => 'Macao',
        'mk' => 'Macedonia, The Former Yugoslav Republic of',
        'mg' => 'Madagascar',
        'mw' => 'Malawi',
        'my' => 'Malaysia',
        'mv' => 'Maldives',
        'ml' => 'Mali',
        'mt' => 'Malta',
        'mh' => 'Marshall Islands',
        'mq' => 'Martinique',
        'mr' => 'Mauritania',
        'mu' => 'Mauritius',
        'yt' => 'Mayotte',
        'mx' => 'Mexico',
        'fm' => 'Micronesia, Federated States of',
        'md' => 'Moldova, Republic of',
        'mc' => 'Monaco',
        'mn' => 'Mongolia',
        'ms' => 'Montserrat',
        'ma' => 'Morocco',
        'mz' => 'Mozambique',
        'mm' => 'Myanmar',
        'na' => 'Namibia',
        'nr' => 'Nauru',
        'np' => 'Nepal',
        'nl' => 'Netherlands',
        'an' => 'Netherlands Antilles',
        'nc' => 'New Caledonia',
        'nz' => 'New Zealand',
        'ni' => 'Nicaragua',
        'ne' => 'Niger',
        'ng' => 'Nigeria',
        'nu' => 'Niue',
        'nf' => 'Norfolk Island',
        'mp' => 'Northern Mariana Islands',
        'no' => 'Norway',
        'om' => 'Oman',
        'pk' => 'Pakistan',
        'pw' => 'Palau',
        'ps' => 'Palestinian Territory, Occupied',
        'pa' => 'Panama',
        'pg' => 'Papua New Guinea',
        'py' => 'Paraguay',
        'pe' => 'Peru',
        'ph' => 'Philippines',
        'pn' => 'Pitcairn',
        'pl' => 'Poland',
        'pt' => 'Portugal',
        'pr' => 'Puerto Rico',
        'qa' => 'Qatar',
        're' => 'Reunion',
        'ro' => 'Romania',
        'ru' => 'Russian Federation',
        'rw' => 'Rwanda',
        'sh' => 'Saint Helena',
        'kn' => 'Saint Kitts and Nevis',
        'lc' => 'Saint Lucia',
        'pm' => 'Saint Pierre and Miquelon',
        'vc' => 'Saint Vincent and The Grenadines',
        'ws' => 'Samoa',
        'sm' => 'San Marino',
        'st' => 'Sao Tome and Principe',
        'sa' => 'Saudi Arabia',
        'sn' => 'Senegal',
        'cs' => 'Serbia and Montenegro',
        'sc' => 'Seychelles',
        'sl' => 'Sierra Leone',
        'sg' => 'Singapore',
        'sk' => 'Slovakia',
        'si' => 'Slovenia',
        'sb' => 'Solomon Islands',
        'so' => 'Somalia',
        'za' => 'South Africa',
        'gs' => 'South Georgia and The South Sandwich Islands',
        'es' => 'Spain',
        'lk' => 'Sri Lanka',
        'sd' => 'Sudan',
        'sr' => 'Suriname',
        'sj' => 'Svalbard and Jan Mayen',
        'sz' => 'Swaziland',
        'se' => 'Sweden',
        'ch' => 'Switzerland',
        'sy' => 'Syrian Arab Republic',
        'tw' => 'Taiwan, Province of China',
        'tj' => 'Tajikistan',
        'tz' => 'Tanzania, United Republic of',
        'th' => 'Thailand',
        'tl' => 'Timor-leste',
        'tg' => 'Togo',
        'tk' => 'Tokelau',
        'to' => 'Tonga',
        'tt' => 'Trinidad and Tobago',
        'tn' => 'Tunisia',
        'tr' => 'Turkey',
        'tm' => 'Turkmenistan',
        'tc' => 'Turks and Caicos Islands',
        'tv' => 'Tuvalu',
        'ug' => 'Uganda',
        'ua' => 'Ukraine',
        'ae' => 'United Arab Emirates',
        'gb' => 'United Kingdom',
        'us' => 'United States',
        'um' => 'United States Minor Outlying Islands',
        'uy' => 'Uruguay',
        'uz' => 'Uzbekistan',
        'vu' => 'Vanuatu',
        've' => 'Venezuela',
        'vn' => 'Viet Nam',
        'vg' => 'Virgin Islands, British',
        'vi' => 'Virgin Islands, U.S.',
        'wf' => 'Wallis and Futuna',
        'eh' => 'Western Sahara',
        'ye' => 'Yemen',
        'zm' => 'Zambia',
        'zw' => 'Zimbabwe',
    );
}

/**
 * 
 */

function get_help_icon($plugintype, $pluginname, $form, $element, $page='', $section='') {
    // I see no reason why IE has to drag the quality of the interwebs down with it
    $imageext = (isset($_SERVER['HTTP_USER_AGENT']) && false !== stripos($_SERVER['HTTP_USER_AGENT'], 'msie 6.0')) ? 'gif' : 'png';
    return ' <span class="help"><a href="" onclick="'. 
        hsc(
            'contextualHelp(' . json_encode($form) . ',' . 
            json_encode($element) . ',' . json_encode($plugintype) . ',' . 
            json_encode($pluginname) . ',' . json_encode($page) . ',' . 
            json_encode($section)
            . ',this); return false;'
        ) . '"><img src="' . theme_get_url('images/icon_help.' . $imageext) . '" alt="?"></a></span>';
}

function pieform_get_help(Pieform $form, $element) {
    return get_help_icon($form->get_property('plugintype'),
        $form->get_property('pluginname'),
        $form->get_name(), $element['name']);
}

function make_link($url) {
    return '<a href="' . $url . '">' . $url . '</a>';
}


/**
 * Returns the entries in the standard admin menu
 *
 * @return $adminnav a data structure containing the admin navigation
 */
function admin_nav() {
    $wwwroot = get_config('wwwroot');

    $menu = array(
        array(
            'path'   => 'admin',
            'url'    => 'admin/',
            'title'  => get_string('adminhome', 'admin'),
            'weight' => 10,
        ),
        array(
            'path'   => 'configsite',
            'url'    => 'admin/site/options.php',
            'title'  => get_string('configsite', 'admin'),
            'weight' => 20,
        ),
        array(
            'path'   => 'configsite/siteoptions',
            'url'    => 'admin/site/options.php',
            'title'  => get_string('siteoptions', 'admin'),
            'weight' => 10,
        ),
        array(
            'path'   => 'configsite/sitepages',
            'url'    => 'admin/site/pages.php',
            'title'  => get_string('sitepages', 'admin'),
            'weight' => 20
        ),
        array(
            'path'   => 'configsite/sitemenu',
            'url'    => 'admin/site/menu.php',
            'title'  => get_string('sitemenu', 'admin'),
            'weight' => 30,
        ),
        array(
            'path'   => 'configsite/adminfiles',
            'url'    => 'admin/site/files.php',
            'title'  => get_string('adminfiles', 'admin'),
            'weight' => 40,
        ),
        array(
            'path'   => 'configsite/networking',
            'url'    => 'admin/site/networking.php',
            'title'  => get_string('networking', 'admin'),
            'weight' => 50,
        ),
        array(
            'path'   => 'configusers',
            'url'    => 'admin/users/suspended.php',
            'title'  => get_string('configusers', 'admin'),
            'weight' => 30,
        ),
        array(
            'path'   => 'configusers/suspendedusers',
            'url'    => 'admin/users/suspended.php',
            'title'  => get_string('suspendedusers', 'admin'),
            'weight' => 10,
        ),
        array(
            'path'   => 'configusers/staffusers',
            'url'    => 'admin/users/staff.php',
            'title'  => get_string('staffusers', 'admin'),
            'weight' => 20,
        ),
        array(
            'path'   => 'configusers/adminusers',
            'url'    => 'admin/users/admins.php',
            'title'  => get_string('adminusers', 'admin'),
            'weight' => 30,
        ),
        array(
            'path'   => 'configusers/adminnotifications',
            'url'    => 'admin/users/notifications.php',
            'title'  => get_string('adminnotifications', 'admin'),
            'weight' => 40,
        ),
        array(
            'path'   => 'configusers/institutions',
            'url'    => 'admin/users/institutions.php',
            'title'  => get_string('institutions', 'admin'),
            'weight' => 50,
        ),
        array(
            'path'   => 'configusers/uploadcsv',
            'url'    => 'admin/users/uploadcsv.php',
            'title'  => get_string('uploadcsv', 'admin'),
            'weight' => 60,
        ),
        array(
            'path'   => 'configusers/usersearch',
            'url'    => 'admin/users/search.php',
            'title'  => get_string('usersearch', 'admin'),
            'weight' => 70,
        ),
        array(
            'path'   => 'configextensions',
            'url'    => 'admin/extensions/plugins.php',
            'title'  => get_string('configextensions', 'admin'),
            'weight' => 40,
        ),
        array(
            'path'   => 'configextensions/pluginadmin',
            'url'    => 'admin/extensions/plugins.php',
            'title'  => get_string('pluginadmin', 'admin'),
            'weight' => 10,
        ),
        array(
            'path'   => 'configextensions/templatesadmin',
            'url'    => 'admin/extensions/templates.php',
            'title'  => get_string('templatesadmin', 'admin'),
            'weight' => 20
        ),
    );

    return $menu;
}

/**
 * Builds a data structure representing the menu for Mahara.
 */
function main_nav() {
    if (defined('ADMIN')) {
        $menu = admin_nav();
    }
    else {
        // Build the menu structure for the site

        // The keys of each entry are as follows:
        //   path: Where the link sits in the menu. E.g. 'myporfolio/myplugin'
        //   url:  The URL to the page, relative to wwwroot. E.g. 'artefact/myplugin/'
        //   title: Translated text to use for the text of the link. E.g. get_string('myplugin', 'artefact.myplugin')
        //   weight: Where in the menu the item should be inserted. Larger number are to the right.
        $menu = array(
            array(
                'path' => '',
                'url' => '',
                'title' => get_string('home'),
                'weight' => 10,
            ),
            array(
                'path' => 'myportfolio',
                'url' => 'view/',
                'title' => get_string('myportfolio'),
                'weight' => 30,
            ),
            array(
                'path' => 'myportfolio/views',
                'url' => 'view/',
                'title' => get_string('views'),
                'weight' => 10
            ),
            array(
                'path' => 'groups',
                'url' => 'contacts/',
                'title' => get_string('groups'),
                'weight' => 40,
            ),
            array(
                'path' => 'groups/myfriends',
                'url' => 'contacts/',
                'title' => get_string('myfriends'),
                'weight' => 10,
            ),
            array(
                'path' => 'groups/groupsimin',
                'url' => 'contacts/groups/',
                'title' => get_string('groupsimin'),
                'weight' => 20,
            ),
            array(
                'path' => 'groups/groupsiown',
                'url' => 'contacts/groups/owned.php',
                'title' => get_string('groupsiown'),
                'weight' => 30,
            ),
            array(
                'path' => 'settings',
                'url' => 'account/',
                'title' => get_string('settings'),
                'weight' => 50
            ),
            array(
                'path' => 'settings/preferences',
                'url' => 'account/',
                'title' => get_string('preferences'),
                'weight' => 10,
            ),
            array(
                'path' => 'settings/recentactivity',
                'url' => 'account/activity/',
                'title' => get_string('recentactivity'),
                'weight' => 20,
            ),
            array(
                'path' => 'settings/activitypreferences',
                'url' => 'account/activity/preferences/',
                'title' => get_string('activityprefs'),
                'weight' => 30,
            ),
        );

        if ($plugins = get_records_array('artefact_installed', 'active', 1)) {
            foreach ($plugins as &$plugin) {
                safe_require('artefact', $plugin->name);
                $plugin_menu = call_static_method(generate_class_name('artefact',$plugin->name), 'menu_items');
                $menu = array_merge($menu, $plugin_menu);
            }
        }
    }

    $menu_structure = find_menu_children($menu, '');
    return $menu_structure;
}

/**
 * Given a menu structure and a path, returns a data structure representing all 
 * of the child menu items of the path, and removes those items from the menu 
 * structure
 *
 * Used by main_nav()
 */
function find_menu_children(&$menu, $path) {
    $result = array();
    if (!$menu) {
        return array();
    }

    foreach ($menu as $key => $item) {
        if (
            defined('MENUITEM') &&
            ((MENUITEM == '' && $item['path'] == '') ||
            ($item['path'] != '' && $item['path'] == substr(MENUITEM, 0, strlen($item['path']))))) {
            $item['selected'] = true;
        }
        if (
            ($path == '' && $item['path'] == '') ||
            ($item['path'] != '' && substr($item['path'], 0, strlen($path)) == $path && !preg_match('%/%', substr($item['path'], strlen($path) + 1)))) {
            $result[] = $item;
            unset($menu[$key]);
        }
    }

    if ($menu) {
        foreach ($result as &$item) {
            $item['submenu'] = find_menu_children($menu, $item['path']);
        }
    }

    uasort($result, 'menu_sort_items');

    return $result;
}

/**
 * Comparison function for sorting menu items
 */
function menu_sort_items(&$a, &$b) {
    !isset($a['weight']) && $a['weight'] = 0;
    !isset($b['weight']) && $b['weight'] = 0;
    return $a['weight'] > $b['weight'];
}

/**
 * Site-level sidebar menu (list of links)
 * There is no admin files table yet so just get the urls.
 * @return $menu a data structure containing the site menu
 */
function site_menu() {
    global $USER;
    $menu = array();
    if ($menuitems = get_records_array('site_menu','public',(int) !$USER->is_logged_in(),'displayorder')) {
        foreach ($menuitems as $i) {
            if ($i->url) {
                $menu[] = array('name' => $i->title,
                                'link' => $i->url);
            }
            else if ($i->file) {
                $menu[] = array('name' => $i->title,
                                'link' => get_config('wwwroot') . 'artefact/file/download.php?file=' . $i->file);
            }
        }
    }
    return $menu;
}

/**
 * Returns the list of site content pages
 * @return array of names
 */
function site_content_pages() {
    return array('about', 'home', 'loggedouthome', 'privacy', 'termsandconditions', 'uploadcopyright');
}

function get_site_page_content($pagename) {
    if ($pagedata = @get_record('site_content', 'name', $pagename)) {
        return $pagedata->content;
    }
    return get_string('sitecontentnotfound', 'mahara', get_string($pagename));
}



/** 
 * Redirects the browser to a new location. The path to redirect to can take
 * two forms:
 *  
 * - http[something]: will redirect the user to that exact URL
 * - /[something]: will redirect to WWWROOT/[something]
 *       
 * Any other form is illegal and will cause an error.
 *      
 * @param string $location The location to redirect the user to. Defaults to
 *                         the application home page.
 */     
function redirect($location='/') {
    if (headers_sent()) {
        throw new Exception('Headers already sent when redirect() was called');
    }

    if (substr($location, 0, 4) != 'http') {
        if (substr($location, 0, 1) != '/') {
            throw new SystemException('redirect() should be called with either'
                . ' /[something] for local redirects or http[something] for'
                . ' absolute redirects');
        }

        $location = get_config('wwwroot') . substr($location, 1);
    }

    header('HTTP/1.1 303 See Other');
    header('Location:' . $location);
    exit;
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

function searchform() {
    require_once('pieforms/pieform.php');
    return pieform(array(
        'name'                => 'searchform',
        'action'              => get_config('wwwroot') . 'search.php',
        'renderer'            => 'oneline',
        'autofocus'           => false,
        'validate'            => false,
        'elements'            => array(
            'query' => array(
                'type'           => 'text',
                'defaultvalue'   => 'Search',
                'class'          => 'emptyonfocus'
            ),
            'submit' => array(
                'type' => 'image',
                'src'  => theme_get_url('images/btn_search_off.gif')
            )
        )
    ));
}

function get_loggedin_string() {
    global $USER;

    $str = get_string('youareloggedinas', 'mahara', display_name($USER));

    safe_require('notification', 'internal');
    $count = call_static_method(generate_class_name('notification', 'internal'), 'unread_count', $USER->get('id'));
    if ($count == 1) {
        $key = 'unreadmessage';
    }
    else {
        $key = 'unreadmessages';
    }

    if ($count > 0) {
        // these spans are here so that on the ajax page that marks messages as read, the contents can be updated.
        $str .=
            ' (<a href="' . get_config('wwwroot') . 'account/activity/">'  . 
            '<span id="headerunreadmessagecount">' . $count . '</span> ' . 
            '<span id="headerunreadmessages">' . get_string($key) . '</span></a>)';
    }

    return $str;
}


/**
 * Returns the name of the current script, WITH the querystring portion.
 * this function is necessary because PHP_SELF and REQUEST_URI and SCRIPT_NAME
 * return different things depending on a lot of things like your OS, Web
 * server, and the way PHP is compiled (ie. as a CGI, module, ISAPI, etc.)
 * <b>NOTE:</b> This function returns false if the global variables needed are not set.
 *
 * @return string
 */
function get_script_path() {

    if (!empty($_SERVER['REQUEST_URI'])) {
        return $_SERVER['REQUEST_URI'];

    } else if (!empty($_SERVER['PHP_SELF'])) {
        if (!empty($_SERVER['QUERY_STRING'])) {
            return $_SERVER['PHP_SELF'] .'?'. $_SERVER['QUERY_STRING'];
        }
        return $_SERVER['PHP_SELF'];

    } else if (!empty($_SERVER['SCRIPT_NAME'])) {
        if (!empty($_SERVER['QUERY_STRING'])) {
            return $_SERVER['SCRIPT_NAME'] .'?'. $_SERVER['QUERY_STRING'];
        }
        return $_SERVER['SCRIPT_NAME'];

    } else if (!empty($_SERVER['URL'])) {     // May help IIS (not well tested)
        if (!empty($_SERVER['QUERY_STRING'])) {
            return $_SERVER['URL'] .'?'. $_SERVER['QUERY_STRING'];
        }
        return $_SERVER['URL'];

    } else {
        log_warn('Warning: Could not find any of these web server variables: $REQUEST_URI, $PHP_SELF, $SCRIPT_NAME or $URL');
        return false;
    }
}

/**
 * Like {@link me()} but returns a full URL
 * @see me()
 * @return string
 */
function get_full_script_path() {

    global $CFG;

    if (!empty($CFG->wwwroot)) {
        $url = parse_url($CFG->wwwroot);
    }

    if (!empty($url['host'])) {
        $hostname = $url['host'];
    } else if (!empty($_SERVER['SERVER_NAME'])) {
        $hostname = $_SERVER['SERVER_NAME'];
    } else if (!empty($_ENV['SERVER_NAME'])) {
        $hostname = $_ENV['SERVER_NAME'];
    } else if (!empty($_SERVER['HTTP_HOST'])) {
        $hostname = $_SERVER['HTTP_HOST'];
    } else if (!empty($_ENV['HTTP_HOST'])) {
        $hostname = $_ENV['HTTP_HOST'];
    } else {
        log_warn('Warning: could not find the name of this server!');
        return false;
    }

    if (!empty($url['port'])) {
        $hostname .= ':'.$url['port'];
    } else if (!empty($_SERVER['SERVER_PORT'])) {
        if ($_SERVER['SERVER_PORT'] != 80 && $_SERVER['SERVER_PORT'] != 443) {
            $hostname .= ':'.$_SERVER['SERVER_PORT'];
        }
    }

    if (isset($_SERVER['HTTPS'])) {
        $protocol = ($_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
    } else if (isset($_SERVER['SERVER_PORT'])) { # Apache2 does not export $_SERVER['HTTPS']
        $protocol = ($_SERVER['SERVER_PORT'] == '443') ? 'https://' : 'http://';
    } else {
        $protocol = 'http://';
    }

    $url_prefix = $protocol.$hostname;
    return $url_prefix . get_script_path();
}

/**
 * Remove query string from url
 *
 * Takes in a URL and returns it without the querystring portion
 *
 * @param string $url the url which may have a query string attached
 * @return string
 */
 function strip_querystring($url) {

    if ($commapos = strpos($url, '?')) {
        return substr($url, 0, $commapos);
    } else {
        return $url;
    }
}

function has_page_help() {
    $pt = defined('SECTION_PLUGINTYPE') ? SECTION_PLUGINTYPE : null;
    $pn = defined('SECTION_PLUGINNAME') ? SECTION_PLUGINNAME : null;
    $sp = defined('SECTION_PAGE')       ? SECTION_PAGE       : null;

    if (empty($pt) || ($pt != 'core' && empty($pn))) {
        // we can't have a plugin type but no plugin name
        return false;
    }

    if (in_array($pt, plugin_types())) {
        $pagehelp = get_config('docroot') . $pt . '/' . $pn . '/lang/en.utf8/help/pages/' . $sp . '.html';
    }
    else {
        $pagehelp = get_config('docroot') . 'lang/en.utf8/help/pages/' . $pn . '/' . $sp . '.html';
    }

    if (is_readable($pagehelp)) {
        return array($sp, get_help_icon($pt, $pn, '', '', $sp));
    }
    return false;
}

//
// Cleaning/formatting functions
//
function format_whitespace($text) {
    $text = str_replace("\r\n", "\n", $text);
    $text = str_replace("\r", "\n", $text);
    $text = hsc($text);
    $text = str_replace('  ', ' &nbsp;', $text);
    $text = str_replace('  ', '&nbsp; ', $text);
    $text = nl2br($text);
    return $text;
}

/**
 * Returns only the first short snippet of a user's introduction
 */
function format_introduction($introduction) {
    $introduction = strip_tags($introduction);
    // Note: the lengths are different to prevent chopping off just one or two characters in order to add an ellipsis
    if (strlen($introduction) < 110) {
        return $introduction;
    }
    return substr($introduction, 0, 100) . '...';
}

/**
 * Given raw text (eg typed in by a user), this function cleans it up
 * and removes any nasty tags that could mess up pages.
 *
 * @param string $text The text to be cleaned
 * @return string The cleaned up text
 */
function clean_text($text) {

    $ALLOWED_TAGS =
'<p><br><b><i><u><font><table><tbody><span><div><tr><td><th><ol><ul><dl><li><dt><dd><h1><h2><h3><h4><h5><h6><hr><img><a><strong><emphasis><em><sup><sub><address><cite><blockquote><pre><strike><param><acronym><nolink><lang><tex><algebra><math><mi><mn><mo><mtext><mspace><ms><mrow><mfrac><msqrt><mroot><mstyle><merror><mpadded><mphantom><mfenced><msub><msup><msubsup><munder><mover><munderover><mmultiscripts><mtable><mtr><mtd><maligngroup><malignmark><maction><cn><ci><apply><reln><fn><interval><inverse><sep><condition><declare><lambda><compose><ident><quotient><exp><factorial><divide><max><min><minus><plus><power><rem><times><root><gcd><and><or><xor><not><implies><forall><exists><abs><conjugate><eq><neq><gt><lt><geq><leq><ln><log><int><diff><partialdiff><lowlimit><uplimit><bvar><degree><set><list><union><intersect><in><notin><subset><prsubset><notsubset><notprsubset><setdiff><sum><product><limit><tendsto><mean><sdev><variance><median><mode><moment><vector><matrix><matrixrow><determinant><transpose><selector><annotation><semantics><annotation-xml><tt><code>';

    // Fix non standard entity notations
    $text = preg_replace('/(&#[0-9]+)(;?)/', "\\1;", $text);
    $text = preg_replace('/(&#x[0-9a-fA-F]+)(;?)/', "\\1;", $text);

    // Remove tags that are not allowed
    $text = strip_tags($text, $ALLOWED_TAGS);

    // Clean up embedded scripts and , using kses
    $text = clean_attributes($text);

    // Remove script events
    $text = eregi_replace("([^a-z])language([[:space:]]*)=", "\\1Xlanguage=", $text);
    $text = eregi_replace("([^a-z])on([a-z]+)([[:space:]]*)=", "\\1Xon\\2=", $text);

    return $text;
}

/**
 * This function takes a string and examines it for HTML tags.
 * If tags are detected it passes the string to a helper function {@link cleanAttributes2()}
 *  which checks for attributes and filters them for malicious content
 *         17/08/2004              ::          Eamon DOT Costello AT dcu DOT ie
 *
 * @param string $str The string to be examined for html tags                                                                      
 * @return string
 */
function clean_attributes($str){
    $result = preg_replace_callback(
        '%(<[^>]*(>|$)|>)%m', #search for html tags
        "clean_attributes_2",
        $str
    );
    return $result;
}

/**
 * This function takes a string with an html tag and strips out any unallowed
 * protocols e.g. javascript:
 * It calls ancillary functions in kses which are prefixed by kses
 *        17/08/2004              ::          Eamon DOT Costello AT dcu DOT ie
 *
 * @param array $htmlArray An array from {@link cleanAttributes()}, containing in its 1st
 *              element the html to be cleared
 * @return string
 */
function clean_attributes_2($htmlArray) {
    require_once('kses.php');
    $ALLOWED_PROTOCOLS = array('http', 'https', 'ftp', 'news', 'mailto', 'rtsp', 'teamspeak', 'gopher', 'mms',
                               'color', 'callto', 'cursor', 'text-align', 'font-size', 'font-weight', 'font-style',
                               'border', 'margin', 'padding', 'background');   // CSS as well to get through kses


    $htmlTag = $htmlArray[1];
    if (substr($htmlTag, 0, 1) != '<') {
        return '&gt;';  //a single character ">" detected
    }
    if (!preg_match('%^<\s*(/\s*)?([a-zA-Z0-9]+)([^>]*)>?$%', $htmlTag, $matches)) {
        return ''; // It's seriously malformed
    }                                                                                                                                        
    $slash = trim($matches[1]); //trailing xhtml slash
    $elem = $matches[2];    //the element name
    $attrlist = $matches[3]; // the list of attributes as a string

    $attrArray = kses_hair($attrlist, $ALLOWED_PROTOCOLS);

    $attStr = '';
    foreach ($attrArray as $arreach) {
        $arreach['name'] = strtolower($arreach['name']);
        if ($arreach['name'] == 'style') {
            $value = $arreach['value'];
            while (true) {
                $prevvalue = $value;
                $value = kses_no_null($value);
                $value = preg_replace("/\/\*.*\*\//Us", '', $value);
                $value = kses_decode_entities($value);
                $value = preg_replace('/(&#[0-9]+)(;?)/', "\\1;", $value);
                $value = preg_replace('/(&#x[0-9a-fA-F]+)(;?)/', "\\1;", $value);
                if ($value === $prevvalue) {
                    $arreach['value'] = $value;
                    break;
                }
            }
            $arreach['value'] = preg_replace("/j\s*a\s*v\s*a\s*s\s*c\s*r\s*i\s*p\s*t/i", "Xjavascript", $arreach['value']);
            $arreach['value'] = preg_replace("/e\s*x\s*p\s*r\s*e\s*s\s*s\s*i\s*o\s*n/i", "Xexpression", $arreach['value']);
        }
        $attStr .=  ' '.$arreach['name'].'="'.$arreach['value'].'" ';
    }

    // Remove last space from attribute list
    $attStr = rtrim($attStr);

    $xhtml_slash = '';
    if (preg_match('%/\s*$%', $attrlist)) {
        $xhtml_slash = ' /';
    }
    return '<'. $slash . $elem . $attStr . $xhtml_slash .'>';
}


?>
