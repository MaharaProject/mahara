<?php
/**
 * The main module file.
 *
 * @package    mahara
 * @subpackage module-monitor
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

require_once('lib/lti-1-3-php-library/lti/lti.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');

use IMSGlobal\LTI;

class LTI_Advantage_Database implements LTI\Database {

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
}

