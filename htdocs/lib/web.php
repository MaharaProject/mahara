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
            } else {
                $content_css = json_encode(theme_get_url('style/tinymce.css'));
                $headers[] = <<<EOF
<script type="text/javascript">
tinyMCE.init({
    mode: "textareas",
    editor_selector: 'wysiwyg',
    button_tile_map: true,
    theme: "advanced",
    plugins: "table,emotions,iespell,inlinepopups,paste",
    theme_advanced_buttons1 : "bold,italic,underline,strikethrough,separator,forecolor,backcolor,separator,justifyleft,justifycenter,justifyright,justifyfull,separator,hr,emotions,iespell,cleanup,separator,link,unlink,separator,code",
    theme_advanced_buttons2 : "tablecontrols,separator,cut,copy,paste,pasteword",
    theme_advanced_buttons3 : "fontselect,separator,fontsizeselect,separator,formatselect",
    theme_advanced_toolbar_location : "top",
    theme_advanced_toolbar_align : "center",
    content_css : {$content_css}
});
</script>

EOF;
            }
            unset($check[$key]);
            break;
        }
    }

    $javascript_array[] = $jsroot . 'MochiKit/setup.js';

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
                if (is_callable(array($pluginclass, 'jsstrings'))) {
                    $name = substr($bits[3], 0, strpos($bits[3], '.js'));
                    $tempstrings = call_static_method($pluginclass, 'jsstrings', $name);
                    foreach ($tempstrings as $section => $tags) {
                        foreach ($tags as $tag) {
                            $strings[$tag] = get_raw_string($tag, $section);
                        }
                    }
                }
                if (is_callable(array($pluginclass, 'themepaths'))) {
                    $name = substr($bits[3], 0, strpos($bits[3], '.js'));
                    $tmpthemepaths = call_static_method($pluginclass, 'themepaths', $name);
                    foreach ($tmpthemepaths as $themepath) {
                        $theme_list[$themepath] = theme_get_url($themepath);
                    }
                }
            }
        }
    }

    $javascript_array[] = $jsroot . 'mahara.js';
    $javascript_array[] = $jsroot . 'debug.js';

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

    if (get_config('installed')) {
        $smarty->assign('SITEMENU', site_menu());
    }
    $smarty->assign('THEMEURL', get_config('themeurl'));
    $smarty->assign('STYLESHEETLIST', array_reverse(theme_get_url('style/style.css', null, true)));
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
                'unreadmessages',
                'unreadmessage',
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
    );
}

function themepaths() {
    return array(
        'mahara' => array(
            'images/icon_close.gif',
            'images/failure.gif',
            'images/loading.gif',
            'images/success.gif',
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
            $qstrings[$tag] = "'" . get_string($tag, $section) . "'";
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
function json_reply($error, $message) {
    json_headers();
    echo json_encode(array('error' => $error, 'message' => $message));
    exit;
}

/**
 * This function checks the user's sesskey against one passed in. If the check
 * fails, a json reply is sent to that effect.
 */
function json_check_sesskey() {
    global $USER;

    $sesskey = param_variable('sesskey', null);

    if ($sesskey === null || $USER->get('sesskey') != $sesskey) {
        json_reply('invalidsesskey', get_string('invalidsesskey'));
    }
}

function _param_retrieve($name) {
    // if it's not set and we have a default
    if (!isset($_REQUEST[$name]) && func_num_args() == 2) {
        $php_work_around = func_get_arg(1);
        return array($php_work_around, true);
    }

    if (!isset($_REQUEST[$name])) {
        throw new ParameterException("Missing parameter '$name' and no default supplied");
    }

    return array($_REQUEST[$name], false);
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
 * @return string The value of the parameter
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

    throw new ParameterException("Parameter '$name' = '$value' is not an integer");
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

    throw new ParameterException("Parameter '$name' = '$value' is not an alpha");
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

    throw new ParameterException("Parameter '$name' = '$value' is not an integer list");
}

/**
 * This function returns a GET or POST parameter as a boolean.
 *
 * @param string The GET or POST parameter you want returned
 *
 * @return string The value of the parameter
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

function get_help_icon($plugintype, $pluginname, $form, $element, $page='') {
    return ' <span class="help"><a href="" onclick="' . 
        hsc(
            'contextualHelp(' . json_encode($form) . ',' . 
            json_encode($element) . ',' . json_encode($plugintype) . ',' . 
            json_encode($pluginname) . ',' . json_encode($page) . ',this); return false;'
        ) . '">?</a></span>';
}

function make_link($url) {
    return '<a href="' . $url . '">' . $url . '</a>';
}


/**
 * Builds the admin navigation menu and returns it as a data structure
 *
 * @return $adminnav a data structure containing the admin navigation
 */
function admin_nav() {
    $wwwroot = get_config('wwwroot');

    $menu = array(
        array(
            'name'     => 'adminhome',
            'section'  => 'admin',
            'link'     => $wwwroot . 'admin/',
        ),
        array(
            'name'     => 'configsite',
            'section'  => 'admin',
            'link'     => $wwwroot . 'admin/site/options.php',
            'submenu'  => array(
                array(
                    'name' => 'siteoptions',
                    'section' => 'admin',
                    'link' => $wwwroot . 'admin/site/options.php'
                ),
                array(
                    'name'    => 'sitepages',
                    'section' => 'admin',
                    'link'    => $wwwroot . 'admin/site/pages.php',
                ),
                array(
                    'name'    => 'sitemenu',
                    'section' => 'admin',
                    'link'    => $wwwroot . 'admin/site/menu.php',
                ),
                array(
                    'name'    => 'adminfiles',
                    'section' => 'admin',
                    'link'    => $wwwroot . 'admin/site/files.php'
                )
            )
        ),
        array(
            'name'     => 'configusers',
            'section'  => 'admin',
            'link'     => $wwwroot . 'admin/users/suspended.php',
            'submenu' => array(
                array(
                    'name'    => 'suspendedusers',
                    'section' => 'admin',
                    'link'    => $wwwroot . 'admin/users/suspended.php'
                ),
                array(
                    'name'    => 'staffusers',
                    'section' => 'admin',
                    'link'    => $wwwroot . 'admin/users/staff.php'
                ),
                array(
                    'name'    => 'adminusers',
                    'section' => 'admin',
                    'link'    => $wwwroot . 'admin/users/admins.php'
                ),
                array(
                    'name'    => 'adminnotifications',
                    'section' => 'admin',
                    'link'    => $wwwroot . 'admin/users/notifications.php'
                ),
                array(
                    'name'    => 'institutions',
                    'section' => 'admin',
                    'link'    => $wwwroot . 'admin/users/institutions.php'
                ),
                array(
                    'name'    => 'uploadcsv',
                    'section' => 'admin',
                    'link'    => $wwwroot . 'admin/users/uploadcsv.php'
                ),
                array(
                    'name'    => 'usersearch',
                    'section' => 'admin',
                    'link'    => $wwwroot . 'admin/users/search.php'
                ),
            )
        ),
        array(
            'name'     => 'configextensions',
            'section'  => 'admin',
            'link'     => $wwwroot . 'admin/extensions/plugins.php',
            'submenu' => array(
                array(
                    'name' => 'pluginadmin',
                    'section' => 'admin',
                    'link' => $wwwroot . 'admin/extensions/plugins.php'
                ),
                array(
                    'name' => 'templatesadmin',
                    'section' => 'admin',
                    'link' => $wwwroot . 'admin/extensions/templates.php'
                )
            )
        ),
    );

    if (defined('MENUITEM')) {
        foreach ( $menu as &$item ) {
            if ($item['name'] == MENUITEM) {
                $item['selected'] = true;
                if (defined('SUBMENUITEM') && is_array($item['submenu'])) {
                    foreach ( $item['submenu'] as &$subitem ) {
                        if ($subitem['name'] == SUBMENUITEM) {
                            $subitem['selected'] = true;
                        }
                    }
                }
            }
        }
    }

    return $menu;
}

/**
 * Builds the main navigation menu and returns it as a data structure
 *
 * @return $mainnav a data structure containing the main navigation
 * @todo martyn this is probably quite expenvise, perhaps it needs teh caching
 */
function main_nav() {
    $wwwroot = get_config('wwwroot');

    if (defined('ADMIN')) {
        return admin_nav();
    }

    $menu = array(
        array(
            'name'     => 'home',
            'section'  => 'mahara',
            'link'     => $wwwroot,
        ),
    );

    if ($plugins = get_records_array('artefact_installed')) {
        foreach ($plugins as &$plugin) {
            safe_require('artefact', $plugin->name);
            $plugin_menu = call_static_method(generate_class_name('artefact',$plugin->name), 'menu_items');

            foreach ($plugin_menu as &$menu_item) {
                $menu_item['link'] = $wwwroot . 'artefact/' . $plugin->name . '/' . $menu_item['link'];
                $menu_item['section'] = 'artefact.' . $plugin->name;
                if (array_key_exists('submenu', $menu_item)) {
                    foreach ($menu_item['submenu'] as &$submenuitem) {
                        if (!array_key_exists('section', $submenuitem)) {
                            $submenuitem['section'] = 'artefact.' . $plugin->name;
                        }
                    }
                }
            }

            $menu = array_merge($menu, $plugin_menu);
        }
    }

    $menu[] = array(
        'name'    => 'mycontacts',
        'link'    => $wwwroot . 'contacts/',
        'section' => 'mahara',
        'submenu' => array(
            array(
                'name'    => 'myfriends',
                'link'    => $wwwroot . 'contacts/',
                'section' => 'mahara',
            ),
            array(
                'name'    => 'myaddressbook',
                'link'    => $wwwroot . 'contacts/addressbook/',
                'section' => 'mahara',
            ),
            array(
                'name'    => 'mycommunities',
                'link'    => $wwwroot . 'contacts/communities/',
                'section' => 'mahara',
            ),
            array(
                'name'    => 'myownedcommunities',
                'link'    => $wwwroot . 'contacts/communities/owned.php',
                'section' => 'mahara',
            ),
            array(
                'name'    => 'mygroups',
                'link'    => $wwwroot . 'contacts/groups/',
                'section' => 'mahara',
            ),
        ),
    );
    $menu[] = array(
        'name'    => 'myviews',
        'link'    => $wwwroot . 'view/',
        'section' => 'mahara',
    );
    $menu[] = array(
        'name'    => 'account',
        'link'    => $wwwroot . 'account/',
        'section' => 'mahara',
        'submenu' => array(
            array(
                'name'    => 'accountprefs',
                'link'    => $wwwroot . 'account/',
                'section' => 'mahara',
            ),
            array(
                'name'    => 'activity',
                'link'    => $wwwroot . 'account/activity/',
                'section' => 'mahara',
            ),
            array(
                'name'    => 'activityprefs',
                'link'    => $wwwroot . 'account/activity/preferences/',
                'section' => 'mahara',
            ),
            array(
                'name'    => 'watchlist',
                'link'    => $wwwroot . 'account/watchlist/',
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

function has_page_help() {
    // the path of the current script (used for page help)
    $scriptname = substr(get_full_script_path(), strlen(get_config('wwwroot')));
    if (strpos($scriptname, '.php') != (strlen($scriptname) - 4)) {
        $scriptname .= 'index.php';
    }
    $scriptname = substr($scriptname, 0, -4);
    
    $firstdir = $scriptname;
    if (false !== ($slashpos = strpos($scriptname, '/'))) {
        $firstdir = substr($scriptname, 0, $slashpos);
    }

    if (in_array($firstdir, plugin_types())) {
        $bits = explode('/', $scriptname);
        if (count($bits) > 2) {
            $plugintype = $bits[0];
            $pluginname = $bits[1];
            $pagehelp = get_config('docroot') . $plugintype . '/' . $pluginname . '/lang/en.utf8/help/pages/' . 
                substr($scriptname, strlen($plugintype . '/' . $pluginname . '/')) . '.html';
        }
    }
    if (empty($plugintype)) {
        $plugintype = 'core';
        $pluginname = 'pages';
        $pagehelp = get_config('docroot') . 'lang/en.utf8/help/pages/' . $scriptname . '.html';
    }

    if (is_readable($pagehelp)) {
        $scriptname = str_replace('/', '-', $scriptname);
        return array($scriptname, get_help_icon($plugintype, $pluginname, '', '', $scriptname));
    }
    return false;
}

?>
