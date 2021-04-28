<?php
namespace IMSGlobal\LTI;

class Cookie {
    public function get_cookie($name) {
        if (isset($_COOKIE[$name])) {
            return $_COOKIE[$name];
        }
        // Look for backup cookie if same site is not supported by the user's browser.
        if (isset($_COOKIE["LEGACY_" . $name])) {
            return $_COOKIE["LEGACY_" . $name];
        }
        return false;
    }

    public function set_cookie($name, $value, $exp = 3600, $options = []) {
        $cookie_options = [
            'expires' => time() + $exp
        ];

        // SameSite none and secure will be required for tools to work inside iframes
        $same_site_options = [
            'samesite' => 'None',
            'secure' => true
        ];

        // check if array $options as 3rd param in setcookie is supported
        if (version_compare(phpversion(), '7.3.0') > 0) {
            setcookie($name, $value, array_merge($cookie_options, $same_site_options, $options));

            // Set a second fallback cookie in the event that "SameSite" is not supported
            setcookie("LEGACY_" . $name, $value, array_merge($cookie_options, $options));
        }
        else {
            // The setcookie() function prior to PHP 7.3 only supports a
            // limited number of parameters. These are, fortunately, not
            // very smart. To this end we'll add extra options to one of
            // the parameters the function currently accepts. The 'path'
            // parameter can carry the extra options for us.
            $path = "/";
            $extra_options = array_merge($cookie_options, $same_site_options, $options);
            // Parameter 3 in setcookie().
            unset($extra_options['expires']);
            // Parameter 6 in setcookie().
            unset($extra_options['secure']);
            foreach ($extra_options as $key => $val) {
                $path .= "; {$key}={$val}";
            }
            setcookie($name, $value, $cookie_options['expires'], $path, "", true);

            // Set a second fallback cookie in the event that "SameSite" is not supported.
            // I am unsure how this is used. If SameSite is not supported the
            // iframe embedding does not work.
            setcookie("LEGACY_" . $name, $value, $cookie_options['expires'], $path, "", true);
        }
        return $this;
    }
}
?>
