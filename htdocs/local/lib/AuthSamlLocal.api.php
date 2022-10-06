<?php
/**
 * This class API documents the available methods that can be called to provide
 * custom/local values.
 */
class AuthSamlLocal {

    /**
     * Maps an external institution name to a Mahara institution name.
     *
     * @param string $external The external institution name.
     * @param string $partial  Only partial match on the last part of the external institution name.
     * @return string The Mahara institution name or an empty string.
     */
    public static function get_affiliation_from_map($external, $partial=false) {
        // If you need any affiliation mapping you can have the
        // LocalAuthSamlLib::get_affiliation_from_map() function return it.
        //
        // For example:
        $map = array('example.com' => 'myinstitution',
                     'example2.com' => 'mytestinstitution');
        foreach ($map as $mapkey => $mapvalue) {
            if ($partial) {
                $check = '.*?' . $mapkey;
            }
            else {
                $check = $mapkey;
            }
            if (preg_match('/^' . $check . '$/', $external) && record_exists('[TABLE]', '[FIELD]', $map[$mapkey])) {
                return $map[$mapkey];
            }
        }
        return '';
    }
}