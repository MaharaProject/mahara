<?php
/**
 *
 * @package    mahara
 * @subpackage auth-webservice
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * Support for external API
 *
 * @package    core
 * @subpackage webservice
 * @copyright  2009 Moodle Pty Ltd (http://moodle.com)
 * @copyright  Copyright (C) 2011 Catalyst IT Ltd (http://www.catalyst.net.nz)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author     Piers Harding
 */

defined('INTERNAL') || die();

/**
 * PARAM_ALPHA - contains only english ascii letters a-zA-Z.
 */
define('PARAM_ALPHA',    'alpha');

/**
 * PARAM_ALPHAEXT the same contents as PARAM_ALPHA plus the chars in quotes: "_-" allowed
 * NOTE: originally this allowed "/" too, please use PARAM_SAFEPATH if "/" needed
 */
define('PARAM_ALPHAEXT', 'alphaext');

/**
 * PARAM_ALPHANUM - expected numbers and letters only.
 */
define('PARAM_ALPHANUM', 'alphanum');

/**
 * PARAM_ALPHANUMEXT - expected numbers, letters only and _-.
 */
define('PARAM_ALPHANUMEXT', 'alphanumext');

/**
 * PARAM_BASE64 - Base 64 encoded format
 */
define('PARAM_BASE64',   'base64');

/**
 * PARAM_BOOL - converts input into 0 or 1, use for switches in forms and urls.
 */
define('PARAM_BOOL',     'bool');

/**
 * PARAM_CLEANHTML - cleans submitted HTML code. use only for text in HTML format. This cleaning may fix xhtml strictness too.
 */
define('PARAM_CLEANHTML', 'cleanhtml');

/**
 * PARAM_EMAIL - an email address following the RFC
 */
define('PARAM_EMAIL',   'email');

/**
 * PARAM_FILE - safe file name, all dangerous chars are stripped, protects against XSS, SQL injections and directory traversals
 */
define('PARAM_FILE',   'file');

/**
 * PARAM_FLOAT - a real/floating point number.
 */
define('PARAM_FLOAT',  'float');

/**
 * PARAM_HOST - expected fully qualified domain name (FQDN) or an IPv4 dotted quad (IP address)
 */
define('PARAM_HOST',     'host');

/**
 * PARAM_INT - integers only, use when expecting only numbers.
 */
define('PARAM_INT',      'int');

/**
 * PARAM_LOCALURL - expected properly formatted URL as well as one that refers to the local server itself. (NOT orthogonal to the others! Implies PARAM_URL!)
 */
define('PARAM_LOCALURL', 'localurl');

/**
 * PARAM_NOTAGS - all html tags are stripped from the text. Do not abuse this type.
 */
define('PARAM_NOTAGS',   'notags');

/**
 * PARAM_PATH - safe relative path name, all dangerous chars are stripped, protects against XSS, SQL injections and directory traversals
 * note: the leading slash is not removed, window drive letter is not allowed
 */
define('PARAM_PATH',     'path');

/**
 * PARAM_PEM - Privacy Enhanced Mail format
 */
define('PARAM_PEM',      'pem');

/**
 * PARAM_RAW specifies a parameter that is not cleaned/processed in any way
 */
define('PARAM_RAW', 'raw');

/**
 * PARAM_RAW_TRIMMED like PARAM_RAW but leading and trailing whitespace is stripped.
 */
define('PARAM_RAW_TRIMMED', 'raw_trimmed');

/**
 * PARAM_SAFEDIR - safe directory name, suitable for include() and require()
 */
define('PARAM_SAFEDIR',  'safedir');

/**
 * PARAM_SAFEPATH - several PARAM_SAFEDIR joined by "/", suitable for include() and require(), plugin paths, etc.
 */
define('PARAM_SAFEPATH',  'safepath');

/**
 * PARAM_SEQUENCE - expects a sequence of numbers like 8 to 1,5,6,4,6,8,9.  Numbers and comma only.
 */
define('PARAM_SEQUENCE',  'sequence');

/**
 * PARAM_TEXT - general plain text compatible with multilang filter, no other html tags. Please note '<', or '>' are allowed here.
 */
define('PARAM_TEXT',  'text');

/**
 * PARAM_URL - expected properly formatted URL. Please note that domain part is required, http://localhost/ is not accepted but http://localhost.localdomain/ is ok.
 */
define('PARAM_URL',      'url');

/**
 * PARAM_STRINGID - used to check if the given string is valid string identifier for get_string()
 */
define('PARAM_STRINGID',    'stringid');

///// DEPRECATED PARAM TYPES OR ALIASES - DO NOT USE FOR NEW CODE  /////
/**
 * PARAM_CLEAN - obsoleted, please use a more specific type of parameter.
 * It was one of the first types, that is why it is abused so much ;-)
 * @deprecated since 2.0
 */
define('PARAM_CLEAN',    'clean');

/**
 * PARAM_INTEGER - deprecated alias for PARAM_INT
 */
define('PARAM_INTEGER',  'int');

/**
 * PARAM_NUMBER - deprecated alias of PARAM_FLOAT
 */
define('PARAM_NUMBER',  'float');

/// Web Services ///

/**
 * VALUE_REQUIRED - if the parameter is not supplied, there is an error
 */
define('VALUE_REQUIRED', 1);

/**
 * VALUE_OPTIONAL - if the parameter is not supplied, then the param has no value
 */
define('VALUE_OPTIONAL', 2);

/**
 * VALUE_DEFAULT - if the parameter is not supplied, then the default value is used
 */
define('VALUE_DEFAULT', 0);

/**
 * NULL_NOT_ALLOWED - the parameter can not be set to null in the database
 */
define('NULL_NOT_ALLOWED', false);

/**
 * NULL_ALLOWED - the parameter can be set to null in the database
 */
define('NULL_ALLOWED', true);

/// Functions

/**
 * Replaces non-standard HTML entities
 *
 * @param string $string
 * @return string
 */
function fix_non_standard_entities($string) {
    $text = preg_replace('/&#0*([0-9]+);?/', '&#$1;', $string);
    $text = preg_replace('/&#x0*([0-9a-fA-F]+);?/', '&#x$1;', $text);
    $text = preg_replace('[\x00-\x08\x0b-\x0c\x0e-\x1f]', '', $text);
    return $text;
}

/**
 * Given raw text (eg typed in by a user), this function cleans it up
 * and removes any nasty tags that could mess up pages.
 *
 * NOTE: the format parameter was deprecated because we can safely clean only HTML.
 *
 * @param string $text The text to be cleaned
 * @return string The cleaned up text
 */
function clean_text($text) {
    if (empty($text) or is_numeric($text)) {
       return (string)$text;
    }

    $text = clean_html($text);

    // Remove potential script events - some extra protection for undiscovered bugs in our code
    $text = preg_replace("~([^a-z])language([[:space:]]*)=~i", "$1Xlanguage=", $text);
    $text = preg_replace("~([^a-z])on([a-z]+)([[:space:]]*)=~i", "$1Xon$2=", $text);

    return $text;
}

/**
 * Strict validation of parameter values, the values are only converted
 * to requested PHP type. Internally it is using clean_param, the values
 * before and after cleaning must be equal - otherwise
 * an WebserviceInvalidParameterException is thrown.
 * Objects and classes are not accepted.
 *
 * @param mixed $param
 * @param int $type PARAM_ constant
 * @param bool $allownull are nulls valid value?
 * @param string $debuginfo optional debug information
 * @return mixed the $param value converted to PHP type or WebserviceInvalidParameterException
 */
function validate_param($param, $type, $allownull=NULL_NOT_ALLOWED, $debuginfo='') {
    if (is_null($param)) {
        if ($allownull == NULL_ALLOWED) {
            return null;
        }
        else {
            throw new WebserviceInvalidParameterException($debuginfo.' value: NULL');
        }
    }
    if (is_array($param) or is_object($param)) {
        throw new WebserviceInvalidParameterException($debuginfo.' value: IS ARRAY or IS OBJECT');
    }

    $cleaned = clean_param($param, $type);
    if ((string)$param !== (string)$cleaned) {
        // conversion to string is usually lossless
        throw new WebserviceInvalidParameterException($debuginfo.' value: '.$param);
    }

    return $cleaned;
}

/**
 * clean the variables and/or cast to specific types, based on
 * an options field.
 *
 * @param mixed $param the variable we are cleaning
 * @param int $type expected format of param after cleaning.
 * @return mixed
 */
function clean_param($param, $type) {
    // Let's loop
    if (is_array($param)) {
        $newparam = array();
        foreach ($param as $key => $value) {
            $newparam[$key] = clean_param($value, $type);
        }
        return $newparam;
    }

    switch ($type) {
        // no cleaning at all
        case PARAM_RAW:
            return $param;

        // no cleaning, but strip leading and trailing whitespace.
        case PARAM_RAW_TRIMMED:
            return trim($param);

        // General HTML cleaning, try to use more specific type if possible
        case PARAM_CLEAN:
            // this is deprecated!, please use more specific type instead
            if (is_numeric($param)) {
                return $param;
            }
            // Sweep for scripts, etc
            return clean_text($param);

        // clean html fragment
        case PARAM_CLEANHTML:
            // Sweep for scripts, etc
            $param = clean_text($param);
            return trim($param);

        case PARAM_INT:
            // Convert to integer
            return (int)$param;

        case PARAM_FLOAT:
        case PARAM_NUMBER:
            // Convert to float
            return (float)$param;

        // Remove everything not a-z
        case PARAM_ALPHA:
            return preg_replace('/[^a-zA-Z]/i', '', $param);

        // Remove everything not a-zA-Z_- (originally allowed "/" too)
        case PARAM_ALPHAEXT:
            return preg_replace('/[^a-zA-Z_-]/i', '', $param);

        // Remove everything not a-zA-Z0-9
        case PARAM_ALPHANUM:
            return preg_replace('/[^A-Za-z0-9]/i', '', $param);

        // Remove everything not a-zA-Z0-9_-
        case PARAM_ALPHANUMEXT:
            return preg_replace('/[^A-Za-z0-9_-]/i', '', $param);

        // Remove everything not 0-9,
        case PARAM_SEQUENCE:
            return preg_replace('/[^0-9,]/i', '', $param);

        // Convert to 1 or 0
        case PARAM_BOOL:
            $tempstr = strtolower($param);
            if ($tempstr === 'on' or $tempstr === 'yes' or $tempstr === 'true') {
                $param = 1;
            }
            else if ($tempstr === 'off' or $tempstr === 'no'  or $tempstr === 'false') {
                $param = 0;
            }
            else {
                $param = empty($param) ? 0 : 1;
            }
            return $param;

        // Strip all tags
        case PARAM_NOTAGS:
            return strip_tags($param);

        // leave only tags needed for multilang
        case PARAM_TEXT:
            // if the multilang syntax is not correct we strip all tags
            // because it would break xhtml strict which is required for accessibility standards
            // please note this cleaning does not strip unbalanced '>' for BC compatibility reasons
            do {
                if (strpos($param, '</lang>') !== false) {
                    // old and future mutilang syntax
                    $param = strip_tags($param, '<lang>');
                    if (!preg_match_all('/<.*>/suU', $param, $matches)) {
                        break;
                    }
                    $open = false;
                    foreach ($matches[0] as $match) {
                        if ($match === '</lang>') {
                            if ($open) {
                                $open = false;
                                continue;
                            }
                            else {
                                break 2;
                            }
                        }
                        if (!preg_match('/^<lang lang="[a-zA-Z0-9_-]+"\s*>$/u', $match)) {
                            break 2;
                        }
                        else {
                            $open = true;
                        }
                    }
                    if ($open) {
                        break;
                    }
                    return $param;

                }
                else if (strpos($param, '</span>') !== false) {
                    // current problematic multilang syntax
                    $param = strip_tags($param, '<span>');
                    if (!preg_match_all('/<.*>/suU', $param, $matches)) {
                        break;
                    }
                    $open = false;
                    foreach ($matches[0] as $match) {
                        if ($match === '</span>') {
                            if ($open) {
                                $open = false;
                                continue;
                            }
                            else {
                                break 2;
                            }
                        }
                        if (!preg_match('/^<span(\s+lang="[a-zA-Z0-9_-]+"|\s+class="multilang"){2}\s*>$/u', $match)) {
                            break 2;
                        }
                        else {
                            $open = true;
                        }
                    }
                    if ($open) {
                        break;
                    }
                    return $param;
                }
            } while (false);
            // easy, just strip all tags, if we ever want to fix orphaned '&' we have to do that in format_string()
            return strip_tags($param);

        // Remove everything not a-zA-Z0-9_-
        case PARAM_SAFEDIR:
            return preg_replace('/[^a-zA-Z0-9_-]/i', '', $param);

        // Remove everything not a-zA-Z0-9/_-
        case PARAM_SAFEPATH:
            return preg_replace('/[^a-zA-Z0-9\/_-]/i', '', $param);

        // Strip all suspicious characters from filename
        case PARAM_FILE:
            $param = preg_replace('~[[:cntrl:]]|[&<>"`\|\':\\\\/]~u', '', $param);
            $param = preg_replace('~\.\.+~', '', $param);
            if ($param === '.') {
                $param = '';
            }
            return $param;

        // Strip all suspicious characters from file path
        case PARAM_PATH:
            $param = str_replace('\\', '/', $param);
            $param = preg_replace('~[[:cntrl:]]|[&<>"`\|\':]~u', '', $param);
            $param = preg_replace('~\.\.+~', '', $param);
            $param = preg_replace('~//+~', '/', $param);
            return preg_replace('~/(\./)+~', '/', $param);

        // allow FQDN or IPv4 dotted quad
        case PARAM_HOST:
            // only allowed chars
            $param = preg_replace('/[^\.\d\w-]/','', $param );
            // match ipv4 dotted quad
            if (preg_match('/(\d{1,3})\.(\d{1,3})\.(\d{1,3})\.(\d{1,3})/',$param, $match)) {
                // confirm values are ok
                if ( $match[0] > 255
                     || $match[1] > 255
                     || $match[3] > 255
                     || $match[4] > 255 ) {
                    // hmmm, what kind of dotted quad is this?
                    $param = '';
                }
            }
            else if ( preg_match('/^[\w\d\.-]+$/', $param)
                       // dots, hyphens, numbers
                       && !preg_match('/^[\.-]/',  $param)
                       // no leading dots/hyphens
                       && !preg_match('/[\.-]$/',  $param)
                       // no trailing dots/hyphens
                       ) {
                // all is ok - $param is respected
            }
            else {
                // all is not ok...
                $param='';
            }
            return $param;

        // allow safe ftp, http, mailto urls
        case PARAM_URL:
            if (!empty($param) && filter_var($param, FILTER_VALIDATE_URL)) {
                // all is ok, param is respected
            }
            else {
                // not really ok
                $param ='';
            }
            return $param;

        // allow http absolute, root relative and relative URLs within wwwroot
        case PARAM_LOCALURL:
            $param = clean_param($param, PARAM_URL);
            if (!empty($param)) {
                if (preg_match(':^/:', $param)) {
                    // root-relative, ok!
                }
                else if (preg_match('/^' . preg_quote(get_config('wwwroot'), '/') . '/i',$param)) {
                    // absolute, and matches our wwwroot
                }
            }
            return $param;

        case PARAM_PEM:
            $param = trim($param);
            // PEM formatted strings may contain letters/numbers and the symbols
            // forward slash: /
            // plus sign:     +
            // equal sign:    =
            // , surrounded by BEGIN and END CERTIFICATE prefix and suffixes
            if (preg_match('/^-----BEGIN CERTIFICATE-----([\s\w\/\+=]+)-----END CERTIFICATE-----$/', trim($param), $matches)) {
                list($wholething, $body) = $matches;
                unset($wholething, $matches);
                $b64 = clean_param($body, PARAM_BASE64);
                if (!empty($b64)) {
                    return "-----BEGIN CERTIFICATE-----\n$b64\n-----END CERTIFICATE-----\n";
                }
                else {
                    return '';
                }
            }
            return '';

        case PARAM_BASE64:
            if (!empty($param)) {
                // PEM formatted strings may contain letters/numbers and the symbols
                // forward slash: /
                // plus sign:     +
                // equal sign:    =
                if (0 >= preg_match('/^([\s\w\/\+=]+)$/', trim($param))) {
                    return '';
                }
                $lines = preg_split('/[\s]+/', $param, -1, PREG_SPLIT_NO_EMPTY);
                // Each line of base64 encoded data must be 64 characters in
                // length, except for the last line which may be less than (or
                // equal to) 64 characters long.
                for ($i=0, $j=count($lines); $i < $j; $i++) {
                    if ($i + 1 == $j) {
                        if (64 < strlen($lines[$i])) {
                            return '';
                        }
                        continue;
                    }

                    if (64 != strlen($lines[$i])) {
                        return '';
                    }
                }
                return implode("\n",$lines);
            }
            else {
                return '';
            }

        case PARAM_EMAIL:
            if (filter_var($param, FILTER_VALIDATE_EMAIL)) {
                return $param;
            }
            else {
                return '';
            }

        case PARAM_STRINGID:
            if (preg_match('|^[a-zA-Z][a-zA-Z0-9\.:/_-]*$|', $param)) {
                return $param;
            }
            else {
                return '';
            }

        default:
            // throw error, switched parameters in clean or another serious problem
           throw new MaharaException("unknownparamtype: ".$type);
    }
}


/**
 * Singleton to handle the external settings.
 *
 * We use singleton to encapsulate the "logic"
 *
 * @package    core_webservice
 * @copyright  2012 Jerome Mouneyrac
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.3
 */
class external_settings {

    /** @var object the singleton instance */
    public static $instance = null;

    /** @var boolean Should the external function return raw text or formatted */
    private $raw = false;

    /** @var boolean Should the external function filter the text */
    private $filter = false;

    /** @var boolean Should the external function rewrite plugin file url */
    private $fileurl = true;

    /** @var string In which file should the urls be rewritten */
    private $file = 'webservice/pluginfile.php';

    /**
     * Constructor - protected - can not be instanciated
     */
    protected function __construct() {
    }

    /**
     * Clone - private - can not be cloned
     */
    private final function __clone() {
    }

    /**
     * Return only one instance
     *
     * @return object
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new external_settings;
        }

        return self::$instance;
    }

    /**
     * Set raw
     *
     * @param boolean $raw
     */
    public function set_raw($raw) {
        $this->raw = $raw;
    }

    /**
     * Get raw
     *
     * @return boolean
     */
    public function get_raw() {
        return $this->raw;
    }

    /**
     * Set filter
     *
     * @param boolean $filter
     */
    public function set_filter($filter) {
        $this->filter = $filter;
    }

    /**
     * Get filter
     *
     * @return boolean
     */
    public function get_filter() {
        return $this->filter;
    }

    /**
     * Set fileurl
     *
     * @param boolean $fileurl
     */
    public function set_fileurl($fileurl) {
        $this->fileurl = $fileurl;
    }

    /**
     * Get fileurl
     *
     * @return boolean
     */
    public function get_fileurl() {
        return $this->fileurl;
    }

    /**
     * Set file
     *
     * @param string $file
     */
    public function set_file($file) {
        $this->file = $file;
    }

    /**
     * Get file
     *
     * @return string
     */
    public function get_file() {
        return $this->file;
    }
}

