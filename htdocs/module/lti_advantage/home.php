<?php
/**
 *
 * @package    mahara
 * @subpackage module.lti_advantage
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */
use \IMSGlobal\LTI;
define('INTERNAL', 1);
define('PUBLIC', 1);

require_once('lib/lti-1-3-php-library/lti/lti.php');
require_once('database.php');

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/lib/institution.php');
require_once('webservice/functions/module_lti_advantage_launch.php');

$lti_db = new LTI_Advantage_Database();
$lti_cache = new LTI\Cache();
$lti_cache->set_cache_dir($CFG->dataroot . '/temp');

$launch = LTI\LTI_Message_Launch::new($lti_db, $lti_cache)
    ->validate();

$data = $launch->get_launch_data();

// Check if we need to allow the page to work in an iframe.
if (!empty($data['aud'])) {
    // The 'aud' is the 'client_id' from the previous call.
    $issuer = $lti_db->find_issuer_by_client_id($data['aud']);
    $parts = parse_url($issuer);
    if (!empty($parts['scheme']) && !empty($parts['host'])) {
        $cspurl = $parts['scheme'] . '://' . $parts['host'];
        $SESSION->set('csp-ancestor-exemption', $cspurl);
        update_csp_headers($cspurl);
    }
}

global $WEBSERVICE_INSTITUTION, $WEBSERVICE_OAUTH_SERVERID;
$params = array();

// Get basic parameters.
if (!array_key_exists('iss', $data)) {
    $msg = get_string('platformvendorissnotfound', 'module.lti_advantage');
    throw new WebserviceInvalidResponseException($msg);
}
$vendor_key = $lti_db->get_vendor_key($data['iss']);
$product_family_code = !empty($data['https://purl.imsglobal.org/spec/lti/claim/tool_platform']['product_family_code']) ? $data['https://purl.imsglobal.org/spec/lti/claim/tool_platform']['product_family_code'] : false;
try {
    if ($vendor_key === false) {
        // We could not find an lti_advantage_registration that matched 'iss' in $data.
        $msg = get_string('platformvendorkeynotfound', 'module.lti_advantage', $data['iss']);
        throw new WebserviceInvalidResponseException($msg);
    }
    if ($product_family_code === false) {
        // We could not find a product_family_code to tell us how to handle the $data.
        $msg = get_string('productfamilycodenotfound', 'module.lti_advantage');
        throw new WebserviceInvalidResponseException($msg);
    }

    if (file_exists('lib/ProductFamily' . ucfirst($product_family_code) . '.php')) {
        require_once('lib/ProductFamily' . ucfirst($product_family_code) . '.php');
    }
    else {
        $msg = get_string('productfamilycodeunknown', 'module.lti_advantage', $product_family_code);
        throw new WebserviceInvalidResponseException($msg);
    }

    $classname = 'LtiAdvantageProductFamily' . ucfirst($product_family_code);
    $productfamily = new $classname($data, $vendor_key);
    $params['user_id'] = $productfamily->get_userdata('userid');
    $params['ext_user_username'] = $productfamily->get_userdata('username');
    $params['given_name'] = $data['given_name'];
    $params['family_name'] = $data['family_name'];
    $params['email'] = $data['email'];
    $params['iss'] = $data['iss'];
    $params['deployment_id'] = $data['https://purl.imsglobal.org/spec/lti/claim/deployment_id'];
    $params['launch_id'] = $launch->get_launch_id();

    // get institution
    $sql = "SELECT sr.id, sr.institution FROM {lti_advantage_registration} r
        JOIN {oauth_server_registry} sr
        ON sr.id = r.connectionid
        WHERE r.issuer = ? ";
    $webservice = get_record_sql($sql, array($data['iss']));

    $WEBSERVICE_INSTITUTION = $webservice->institution;
    $WEBSERVICE_OAUTH_SERVERID = $webservice->id;
    if (empty($data['https://purl.imsglobal.org/spec/lti/claim/context'])) {
        $unit = array(
            'id' => '',
            'label' => '',
            'title' => '',
            'type' => '',
        );
    }
    else {
    $unit = $data['https://purl.imsglobal.org/spec/lti/claim/context'];
    }
    $params['context_id'] = $unit['id'];
    $params['context_label'] = $unit['label'];
    $params['context_title'] = $unit['title'];
    $params['context_type'] = $unit['type'];

    $roles = array();
    if ($userroles = $data['https://purl.imsglobal.org/spec/lti/claim/roles']) {
        foreach($userroles as $role) {
            $role_url = new mahara_url($role);
            if (strpos($role_url->get_path(), 'vocab/lis/v2/membership') !== false) {
                if ($role_url->get_anchor()) {
                    $roles[] = $role_url->get_anchor();
                }
            }
        }
        $roles = implode(',', $roles);
    }
    $params['roles'] = $roles;

    if (!empty($cspurl)) {
        $test_role = strtolower($params['roles']);
        if (strpos($test_role, 'instructor') !== false || strpos($test_role, 'teachingassistant') !== false || strpos($test_role, 'administrator') !== false) {
            $SESSION->set('lti.submittedhost', $cspurl);
            $SESSION->set('user/staff', 1);
        }
    }

    if ($launch->has_nrps()) {
        $namesroleservice = $data['https://purl.imsglobal.org/spec/lti-nrps/claim/namesroleservice'];
        $params['context_memberships_url'] = $namesroleservice['context_memberships_url'];
        $params['service_versions'] = json_encode($namesroleservice['service_versions']);

    }

    if ($launch->is_resource_launch() && key_exists('https://purl.imsglobal.org/spec/lti/claim/custom', $data)) {
        $params['resource_launch'] = $data['https://purl.imsglobal.org/spec/lti/claim/custom'];
    }

    if (!empty($data['sub'])) {
        // During the cron run that builds users/groups the users come over with a
        // user_id that does not match user_id in any other calls. The 'sub' key
        // contains this value in standard calls.
        $params['sub'] = $data['sub'];
    }

    module_lti_advantage_launch::launch_advantage($params);

    redirect();
}
catch (WebserviceInvalidResponseException $e) {
    log_warn($e->getMessage());
}

function is_course_offering($unit) {
    return (isset($unit['type']) && is_array($unit['type']) && in_array('http://purl.imsglobal.org/vocab/lis/v2/course#CourseOffering', $unit['type']));
}

