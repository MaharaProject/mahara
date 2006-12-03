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
 * - WWWROOT: The base url for the podclass system
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
function &smarty($javascript = array(), $headers = array(), $strings = array()) {
    global $USER, $SESSION;

    require_once(get_config('libroot') . 'smarty/Smarty.class.php');

    // Insert the appropriate javascript tags 
    $jsroot = get_config('wwwroot') . 'js/';

    // TinyMCE must be included first for some reason we're not sure about
    if (($key = array_search('tinymce', $javascript)) !== FALSE) {
        $javascript_array[] = $jsroot . 'tinymce/tiny_mce.js';
        $initfile = $jsroot . 'mahara_tinymce_init.html';
        if (!$headers[] = @file_get_contents($initfile)) {
            throw new Exception ('tinyMCE not initialised.');
        }
        unset($javascript[$key]);
    }

    $javascript_array[] = $jsroot . 'MochiKit/MochiKit.js';

    $jsstrings = jsstrings();

    foreach ($javascript as $jsfile) {
        $javascript_array[] = $jsroot . $jsfile . '.js';
        if ($jsstrings[$jsfile]) {
            foreach ($jsstrings[$jsfile] as $string) {
                if (!in_array($string, $strings)) {
                    $strings[] = $string;
                }
            }
        }
    }

    $javascript_array[] = $jsroot . 'mahara.js';
    $javascript_array[] = $jsroot . 'debug.js';

    foreach ($jsstrings['mahara'] as $string) {
        if (!in_array($string, $strings)) {
            $strings[] = $string;
        }
    }

    // Add language strings for the javascript
    foreach ($strings as &$string) {
        $string = '"' . $string . '":"' . addslashes(get_raw_string($string)) . '"';
    }
    $stringjs = '<script language="javascript" type="text/javascript">';
    $stringjs .= 'var strings={' . implode(',', $strings) . '};';
    $stringjs .= '</script>';
    $headers[] = $stringjs;

    $smarty =& new Smarty();
    
    $theme = theme_setup();

    $smarty->template_dir = $theme->template_dir;

    $smarty->compile_dir  = get_config('dataroot').'smarty/compile';
    $smarty->cache_dir    = get_config('dataroot').'smarty/cache';

    $smarty->assign('THEMEURL', get_config('themeurl'));
    $smarty->assign('WWWROOT', get_config('wwwroot'));
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
    }

    $smarty->assign_by_ref('USER', $USER);
    $smarty->assign_by_ref('JAVASCRIPT', $javascript_array);
    $smarty->assign_by_ref('HEADERS', $headers);

    return $smarty;
}

function jsstrings() {
    return array(
        'mahara' => array(
            'namedfieldempty',
            'processingform',
            'requiredfieldempty',
            'unknownerror',
            'loading',
        ),
        'tablerenderer' => array(
            'firstpage',
            'nextpage',
            'prevpage',
            'lastpage',
        ),
        'fileuploader' => array(),
    );
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

    // always put the parent at the top of the tree, unless we're already it
    if ($theme->theme != 'default') {
        $theme->template_dir[] = get_config('docroot')  . 'theme/default/templates/';
        $theme->inheritance[] = $parent;
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
function theme_get_image_path($imagelocation, $pluginlocation='') {
    $theme = theme_setup();

    foreach ($theme->inheritance as $themedir) {
        if (is_readable(get_config('docroot') . $pluginlocation . 'theme/' . $themedir . '/static/' . $imagelocation)) {
            return get_config('wwwroot') . $pluginlocation . 'theme/' . $themedir . '/static/' . $imagelocation;
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
    return ' <span class="help"><a href="" onclick="contextualHelp(\'' . $form . "', '"
            . $element . "', '" . $plugintype . "', '"
            . $pluginname . "', '" . $page . "'); return false;\">"
            . '?</a></span>';
}

function make_link($url) {
    return '<a href="' . $url . '">' . $url . '</a>';
}

?>
