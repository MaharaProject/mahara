<?php
/**
 * The database object for LTI Advantage.
 *
 * @package    mahara
 * @subpackage LTI Advantage
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

require_once('lib/lti-1-3-php-library/lti/lti.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');

use IMSGlobal\LTI;

/**
 * A database object for LTI Advantage features.
 *
 * Supporting methods to fetch specific details for LTI Advantage.
 */
class LTI_Advantage_Database implements LTI\Database {

    /**
     * Return the LTI_Registration object for an issuer.
     *
     * @param string $iss
     *
     * @return object|bool An LTI_Registration object or false if not found.
     */
    public function find_registration_by_issuer($iss) {
        $registration = get_record('lti_advantage_registration', 'issuer', $iss);

        if (!$registration) {
            return false;
        }

        $key = get_record('lti_advantage_key', 'key_set_id', $registration->key_set_id);

        if (!$key) {
            return false;
        }

        return LTI\LTI_Registration::new()
            ->set_issuer($registration->issuer)
            ->set_client_id($registration->client_id)
            ->set_auth_login_url($registration->platform_login_auth_endpoint)
            ->set_auth_token_url($registration->platform_service_auth_endpoint)
            ->set_key_set_url($registration->platform_jwks_endpoint)
            ->set_auth_server($registration->platform_auth_provider)
            ->set_kid($key->id)
            ->set_tool_private_key($key->private_key);

    }

    /**
     * Fetch a new deployment object.
     *
     * @param string $iss The issuer.
     * @param string $deployment_id The deployment ID.
     *
     * @return object|bool The deployment object or false if none found
     */
    public function find_deployment($iss, $deployment_id) {
        // make sure we have the right id
        $sql = "
            SELECT d.deployment_id FROM
            {lti_advantage_deployment} d
            JOIN {lti_advantage_registration} r
            ON d.registration_id = r.id
            WHERE r.issuer = ? AND d.deployment_id = ?";

        $deployment = get_field_sql($sql, array($iss, $deployment_id));

        if (!$deployment) {
            return false;
        }

        return LTI\LTI_Deployment::new()
            ->set_deployment_id($deployment);
    }

    /**
     * Return keys for a given key set.
     *
     * @param string $key_set_id The key set ID.
     *
     * @return array|bool The private keys or false if none found.
     */
    public function get_keys_in_set($key_set_id) {
        $key = get_records_array('lti_advantage_key', 'key_set_id', $key_set_id);
        if (!$key) {
            return false;
        }

        $keys = array();
        foreach ($key as $k) {
            $keys[$k->id] = $k->private_key;
        }

        return $keys;
    }

    /**
     * Return an issuer URL for a given client id.
     *
     * @param string $client_id The client_id to check.
     *
     * @return string The Issuer URL or an empty string
     */
    public function find_issuer_by_client_id($client_id) {
        $registration = get_records_array('lti_advantage_registration', 'client_id', $client_id, '', 'issuer');
        if (!$registration) {
            return '';
        }
        $registration = current($registration);
        if (empty($registration->issuer)) {
            return '';
        }

        return $registration->issuer;
    }

    /**
     * Return the display name for an issuer URL.
     *
     * The display name is a shorter name used where longer references won't
     * look good like tables, etc.
     *
     * @param string $issuer The issuer to check.
     * @param bool $return_issuer If a display name for the issuer is not
     *     found, return the value of $issuer.
     *
     * @return string The Issuer name, value of $issuer, or an empty string.
     */
    public static function find_name_of_issuer($issuer, $return_issuer = true) {
        $registration = get_records_array(
            'lti_advantage_registration',
            'issuer',
            $issuer,
            '',
            'display_name'
        );
        if (!$registration) {
            // No records found.
            return ($return_issuer) ? $issuer : '';
        }

        $registration = current($registration);
        if (empty($registration->display_name)) {
            // No display name set.
            return ($return_issuer) ? $issuer : '';
        }

        return $registration->display_name;
    }

    public static function get_vendor_key($issuer) {
        $registration = get_records_array(
            'lti_advantage_registration',
            'issuer',
            $issuer,
            '',
            'platform_vendor_key, display_name'
        );
        if (!$registration) {
            // No record found.
            return false;
        }

        $registration = current($registration);
        if (empty($registration->display_name)) {
            // No vendor key set.
            return false;
        }

        return $registration->platform_vendor_key;

    }
}
