<?php
/**
 * Copyright 2006,2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
 *
 * This file is part of maraha.
 *
 * maraha is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.

 * maraha is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.

 * You should have received a copy of the GNU General Public License
 * along with maraha; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */


/** 
 * work around silly php settings
 * and broken setup stuff about the install
 * and raise a warning/fail depending on severity
 */
function ensure_sanity() {

    // register globals workaround
    if (ini_get_bool('register_globals')) {
        trigger_error(get_string('registerglobals','error'),E_USER_NOTICE);
        $massivearray = array_keys(array_merge($_POST,$_GET,$_COOKIE,$_SERVER,$_REQUEST,$_FILES));
        foreach ($massivearray as $tounset) {
            unset($GLOBALS[$tounset]);
        }
    }

    // magic_quotes_gpc workaround
    if (ini_get_bool('magic_quotes_gpc')) {
        trigger_error(get_string('magicquotesgpc','error'),E_USER_NOTICE);
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

    // dataroot inside document root.
    if (strpos(get_config('dataroot'),get_config('docroot')) !== false) {
        trigger_error(get_string('datarootinsidedocroot','error'));
    }

    // more later?

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
    $libroot = get_config('libroot');
    $docroot = get_config('docroot');
    $locations = array();
    
    if ($section == 'mahara' || $section != 'langconfig') {
        $locations[] = $libroot.'lang/';
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
        if (file_exists($langfile)) {
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
        if (file_exists($langfile)) {
            if ($parentlang = get_string_from_file('parentlanguage', $langfile)) {
                $langfile = $location.$parentlang.'/'.$section.'.php';
                if (file_exists($langfile)) {
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
        if (file_exists($langfile)) {
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

/*
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

?>