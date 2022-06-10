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
     * @return string The Mahara institution name or an empty string.
     */
    public static function get_affiliation_map($external) {
        // If you need any affiliation mapping you can have the
        // LocalAuthSamlLib::get_affiliation_map() function return it.
        //
        // For example:
        $map = array('example.com' => 'myinstitution',
                     'example2.com' => 'mytestinstitution');
        if (isset($map[$external]) && record_exists('[TABLE]', '[FIELD]', $map[$external])) {
           return $map[$external];
        }
        else {
            return '';
        }
    }
}