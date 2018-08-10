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

/**
 * This file borrows heavily from the Moodle file lib/weblib.php
 */
class mahara_url {
    /**
     * Scheme, ex.: http, https
     * @var string
     */
    protected $scheme = '';
    /**
     * Hostname.
     * @var string
     */
    protected $host = '';
    /**
     * Port number, empty means default 80 or 443 in case of http.
     * @var int
     */
    protected $port = '';
    /**
     * Username for http auth.
     * @var string
     */
    protected $user = '';
    /**
     * Password for http auth.
     * @var string
     */
    protected $pass = '';
    /**
     * Script path.
     * @var string
     */
    public $path = '';
    /**
     * Optional slash argument value.
     * @var string
     */
    protected $slashargument = '';
    /**
     * Anchor, may be also empty, null means none.
     * @var string
     */
    protected $anchor = null;
    /**
     * Url parameters as associative array.
     * @var array
     */
    protected $params = array();

    /**
     * Create new instance of mahara_url.
     *
     * @param mahara_url|string $url - mahara_url means make a copy of another
     *      mahara_url and change parameters, string means full url or shortened
     *      form
     * @param array $params these params override current params or add new
     * @param string $anchor The anchor to use as part of the URL if there is one.
     * @throws MaharaException
     */

    public function __construct($url, array $params = null, $anchor = null) {

        if ($url instanceof mahara_url) {
            $this->scheme = $url->scheme;
            $this->host = $url->host;
            $this->port = $url->port;
            $this->user = $url->user;
            $this->pass = $url->pass;
            $this->path = $url->path;
            $this->slashargument = $url->slashargument;
            $this->params = $url->params;
            $this->anchor = $url->anchor;
        }
        else {
            // Detect if anchor used.
            $apos = strpos($url, '#');
            if ($apos !== false) {
                $anchor = substr($url, $apos);
                $anchor = ltrim($anchor, '#');
                $this->set_anchor($anchor);
                $url = substr($url, 0, $apos);
            }

            // Normalise shortened form of our url ex.: '/view/view.php'.
            if (strpos($url, '/') === 0) {
                $url = get_config('wwwroot') . $url;
            }

            $parts = parse_url($url);
            if ($parts === false) {
                throw new MaharaException('invalidurl');
            }

            if (isset($parts['query'])) {
                // Note: the values may not be correctly decoded, url parameters should be always passed as array.
                parse_str(str_replace('&amp;', '&', $parts['query']), $this->params);
            }
            unset($parts['query']);
            foreach ($parts as $key => $value) {
                $this->$key = $value;
            }

            // Detect slashargument value from path - we do not support directory names ending with .php.
            $pos = strpos($this->path, '.php/');
            if ($pos !== false) {
                $this->slashargument = substr($this->path, $pos + 4);
                $this->path = substr($this->path, 0, $pos + 4);
            }
        }

        $this->params($params);
        if ($anchor !== null) {
            $this->anchor = (string)$anchor;
        }
    }

    /**
     * Add an array of params to the params for this url.
     *
     * The added params override existing ones if they have the same name.
     *
     * @param array $params Defaults to null. If null then returns all params.
     * @return array Array of Params for url.
     * @throws MaharaException
     */
    public function params($params = null) {
        $params = (array)$params;

        foreach ($params as $key => $value) {
            if (is_int($key)) {
                throw new MaharaException('Url parameters can not have numeric keys!');
            }
            if (!is_string($value)) {
                if (is_array($value)) {
                    throw new MaharaException('Url parameters values can not be arrays!');
                }
                if (is_object($value) and !method_exists($value, '__toString')) {
                    throw new MaharaException('Url parameters values can not be objects, unless __toString() is defined!');
                }
            }
            $this->params[$key] = (string)$value;
        }
        return $this->params;
    }

    /**
     * Remove all params if no arguments passed.
     * Remove selected params if arguments are passed.
     *
     * Can be called as either remove_params('param1', 'param2')
     * or remove_params(array('param1', 'param2')).
     *
     * @param string[]|string $params,... either an array of param names, or 1..n string params to remove as args.
     * @return array url parameters
     */
    public function remove_params($params = null) {
        if (!is_array($params)) {
            $params = func_get_args();
        }
        foreach ($params as $param) {
            unset($this->params[$param]);
        }
        return $this->params;
    }

    /**
     * Remove all url parameters.
     *
     * @todo remove the unused param.
     * @param array $params Unused param
     * @return void
     */
    public function remove_all_params($params = null) {
        $this->params = array();
        $this->slashargument = '';
    }

    /**
     * Add a param to the params for this url.
     *
     * The added param overrides existing one if they have the same name.
     *
     * @param string $paramname name
     * @param string $newvalue Param value. If new value specified current value is overriden or parameter is added
     * @return mixed string parameter value, null if parameter does not exist
     */
    public function param($paramname, $newvalue = '') {
        if (func_num_args() > 1) {
            // Set new value.
            $this->params(array($paramname => $newvalue));
        }
        if (isset($this->params[$paramname])) {
            return $this->params[$paramname];
        }
        else {
            return null;
        }
    }

    /**
     * Merges parameters and validates them
     *
     * @param array $overrideparams
     * @return array merged parameters
     * @throws exception
     */
    protected function merge_overrideparams(array $overrideparams = null) {
        $overrideparams = (array)$overrideparams;
        $params = $this->params;
        foreach ($overrideparams as $key => $value) {
            if (is_int($key)) {
                throw new MaharaException('Overridden parameters can not have numeric keys!');
            }
            if (is_array($value)) {
                throw new MaharaException('Overridden parameters values can not be arrays!');
            }
            if (is_object($value) and !method_exists($value, '__toString')) {
                throw new MaharaException('Overridden parameters values can not be objects, unless __toString() is defined!');
            }
            $params[$key] = (string)$value;
        }
        return $params;
    }

    /**
     * Get the params as as a query string.
     *
     * This method should not be used outside of this method.
     *
     * @param bool $escaped Use &amp; as params separator instead of plain &
     * @param array $overrideparams params to add to the output params, these
     *      override existing ones with the same name.
     * @return string query string that can be added to a url.
     */
    public function get_query_string($escaped = true, array $overrideparams = null) {
        $arr = array();
        if ($overrideparams !== null) {
            $params = $this->merge_overrideparams($overrideparams);
        }
        else {
            $params = $this->params;
        }
        foreach ($params as $key => $val) {
            if (is_array($val)) {
                foreach ($val as $index => $value) {
                    $arr[] = rawurlencode($key . '[' . $index . ']') . "=" . rawurlencode($value);
                }
            }
            else {
                if (isset($val) && $val !== '') {
                    $arr[] = rawurlencode($key) . "=" . rawurlencode($val);
                }
                else {
                    $arr[] = rawurlencode($key);
                }
            }
        }
        if ($escaped) {
            return implode('&amp;', $arr);
        }
        else {
            return implode('&', $arr);
        }
    }

    /**
     * Shortcut for printing of encoded URL.
     *
     * @return string
     */
    public function __toString() {
        return $this->out(true);
    }

    /**
     * Output url.
     *
     * If you use the returned URL in HTML code, you want the escaped ampersands. If you use
     * the returned URL in HTTP headers, you want $escaped=false.
     *
     * @param bool $escaped Use &amp; as params separator instead of plain &
     * @param array $overrideparams params to add to the output url, these override existing ones with the same name.
     * @return string Resulting URL
     */
    public function out($escaped = true, array $overrideparams = null) {

        if (!is_bool($escaped)) {
            debugging('Escape parameter must be of type boolean, ' . gettype($escaped) . ' given instead.');
        }

        $url = $this;

        return $url->raw_out($escaped, $overrideparams);
    }

    /**
     * Output url without any rewrites
     *
     * This is identical in signature and use to out() but doesn't call the rewrite handler.
     *
     * @param bool $escaped Use &amp; as params separator instead of plain &
     * @param array $overrideparams params to add to the output url, these override existing ones with the same name.
     * @return string Resulting URL
     */
    public function raw_out($escaped = true, array $overrideparams = null) {
        if (!is_bool($escaped)) {
            debugging('Escape parameter must be of type boolean, ' . gettype($escaped) . ' given instead.');
        }

        $uri = $this->out_omit_querystring() . $this->slashargument;

        $querystring = $this->get_query_string($escaped, $overrideparams);
        if ($querystring !== '') {
            $uri .= '?' . $querystring;
        }
        if (!is_null($this->anchor)) {
            $uri .= '#' . $this->anchor;
        }

        return $uri;
    }

    /**
     * Returns url without parameters, everything before '?'.
     *
     * @param bool $includeanchor if {@link self::anchor} is defined, should it be returned?
     * @return string
     */
    public function out_omit_querystring($includeanchor = false) {

        $uri = $this->scheme ? $this->scheme . ':' . ((strtolower($this->scheme) == 'mailto') ? '' : '//') : '';
        $uri .= $this->user ? $this->user . ($this->pass ? ':' . $this->pass : '') . '@' : '';
        $uri .= $this->host ? $this->host : '';
        $uri .= $this->port ? ':' . $this->port : '';
        $uri .= $this->path ? $this->path : '';
        if ($includeanchor and !is_null($this->anchor)) {
            $uri .= '#' . $this->anchor;
        }

        return $uri;
    }

    /**
     * Sets the anchor for the URI (the bit after the hash)
     *
     * @param string $anchor null means remove previous
     */
    public function set_anchor($anchor) {
        if (is_null($anchor)) {
            // Remove.
            $this->anchor = null;
        }
        else if ($anchor === '') {
            // Special case, used as empty link.
            $this->anchor = '';
        }
        else if (preg_match('|[a-zA-Z\_\:][a-zA-Z0-9\_\-\.\:]*|', $anchor)) {
            // Match the anchor against the NMTOKEN spec.
            $this->anchor = $anchor;
        }
        else {
            // Bad luck, no valid anchor found.
            $this->anchor = null;
        }
    }

    /**
     * Sets the scheme for the URI (the bit before ://)
     *
     * @param string $scheme
     */
    public function set_scheme($scheme) {
        // See http://www.ietf.org/rfc/rfc3986.txt part 3.1.
        if (preg_match('/^[a-zA-Z][a-zA-Z0-9+.-]*$/', $scheme)) {
            $this->scheme = $scheme;
        }
        else {
            throw new MaharaException('Bad URL scheme.');
        }
    }

    /**
     * Sets the url slashargument value.
     *
     * @param string $path usually file path
     * @param string $parameter name of page parameter if slasharguments not supported
     * @param bool $supported usually null, then it depends on $cfg->slasharguments, use true or false for other servers
     * @return void
     */
    public function set_slashargument($path, $parameter = 'file', $supported = null) {
        global $cfg;
        if (is_null($supported)) {
            $supported = !empty($cfg->slasharguments);
        }
        if ($supported) {
            $parts = explode('/', $path);
            $parts = array_map('rawurlencode', $parts);
            $path  = implode('/', $parts);
            $this->slashargument = $path;
            unset($this->params[$parameter]);
        }
        else {
            $this->slashargument = '';
            $this->params[$parameter] = $path;
        }
    }

    /**
     * Returns the 'path' portion of a URL. For example, if the URL is
     * http://www.example.org:447/my/file/is/here.txt?really=1 then this will
     * return '/my/file/is/here.txt'.
     *
     * By default the path includes slash-arguments (for example,
     * '/myfile.php/extra/arguments') so it is what you would expect from a
     * URL path. If you don't want this behaviour, you can opt to exclude the
     * slash arguments. (Be careful: if the $CFG variable slasharguments is
     * disabled, these URLs will have a different format and you may need to
     * look at the 'file' parameter too.)
     *
     * @param bool $includeslashargument If true, includes slash arguments
     * @return string Path of URL
     */
    public function get_path($includeslashargument = true) {
        return $this->path . ($includeslashargument ? $this->slashargument : '');
    }

    /**
     * Returns a given parameter value from the URL.
     *
     * @param string $name Name of parameter
     * @return string Value of parameter or null if not set
     */
    public function get_param($name) {
        if (array_key_exists($name, $this->params)) {
            return $this->params[$name];
        }
        else {
            return null;
        }
    }

    /**
     * Returns the 'scheme' portion of a URL. For example, if the URL is
     * http://www.example.org:447/my/file/is/here.txt?really=1 then this will
     * return 'http' (without the colon).
     *
     * @return string Scheme of the URL.
     */
    public function get_scheme() {
        return $this->scheme;
    }

    /**
     * Returns the 'host' portion of a URL. For example, if the URL is
     * http://www.example.org:447/my/file/is/here.txt?really=1 then this will
     * return 'www.example.org'.
     *
     * @return string Host of the URL.
     */
    public function get_host() {
        return $this->host;
    }

    /**
     * Returns the 'port' portion of a URL. For example, if the URL is
     * http://www.example.org:447/my/file/is/here.txt?really=1 then this will
     * return '447'.
     *
     * @return string Port of the URL.
     */
    public function get_port() {
        return $this->port;
    }
}
