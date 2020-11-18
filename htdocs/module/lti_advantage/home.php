<?php
/**
 *
 * @package    mahara
 * @subpackage module.lti_advantage
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */
use \IMSGlobal\LTI;
define('INTERNAL', 1);
define('PUBLIC', 1);

require_once('lib/lti-1-3-php-library/lti/lti.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('database.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/lib/institution.php');
require_once('webservice/functions/module_lti_advantage_launch.php');

$launch = LTI\LTI_Message_Launch::new(new LTI_Advantage_Database())
    ->validate();

$data = $launch->get_launch_data();

global $WEBSERVICE_INSTITUTION, $WEBSERVICE_OAUTH_SERVERID;
$params = array();

// get basic parameters
$userdata = $data['http://www.brightspace.com']; // contains user id
$params['user_id'] = $userdata['user_id'];
$params['ext_user_username'] = $userdata['username'];

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

$unit = $data['https://purl.imsglobal.org/spec/lti/claim/context'];
$params['context_id'] = $unit['id'];
$params['context_label'] = $unit['label'];
$params['context_title'] = $unit['title'];
$params['context_type'] = $unit['type'];

$roles = '';
if ($userroles = $data['https://purl.imsglobal.org/spec/lti/claim/roles']) {
    foreach($userroles as $role) {
        $length = strlen('http://purl.imsglobal.org/vocab/lis/v2/membership#');
        if (substr($role, 0, $length) == 'http://purl.imsglobal.org/vocab/lis/v2/membership#') {
            $roles .= substr($role, $length) . ',';
        }
    }
    $roles = substr($roles, 0, strlen($roles) - 1);
}
$params['roles'] = $roles;

if ($launch->has_nrps()) {
    $namesroleservice = $data['https://purl.imsglobal.org/spec/lti-nrps/claim/namesroleservice'];
    $params['context_memberships_url'] = $namesroleservice['context_memberships_url'];
    $params['service_versions'] = json_encode($namesroleservice['service_versions']);

}

module_lti_advantage_launch::launch_advantage($params);

redirect();

function is_course_offering($unit) {
    return (isset($unit['type']) && is_array($unit['type']) && in_array('http://purl.imsglobal.org/vocab/lis/v2/course#CourseOffering', $unit['type']));
}

