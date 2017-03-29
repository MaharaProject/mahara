<?php
/**
 *
 * @package    mahara
 * @subpackage module-lti
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */
if (!defined('INTERNAL')) {
    die();
}
require_once(get_config('docroot') . 'webservice/lib.php');

/**
 * Functions needed to launch Mahara as an LTI provider
 */
class module_lti_launch extends external_api {

    /**
     * parameter definition for input of launch method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function launch_parameters() {
        return new external_function_parameters(
            array(
                // Required Params
                'launch_presentation_return_url' => new external_value(PARAM_URL, 'LTI launch_presentation_return_url', VALUE_REQUIRED),
                'lis_person_contact_email_primary' => new external_value(PARAM_EMAIL, 'LTI lis_person_contact_email_primary', VALUE_REQUIRED),
                'lis_person_name_family' => new external_value(PARAM_TEXT, 'LTI lis_person_name_family', VALUE_REQUIRED),
                'lis_person_name_given' => new external_value(PARAM_TEXT, 'LTI lis_person_name_given', VALUE_REQUIRED),

                // Optional Params
                'context_id' => new external_value(PARAM_TEXT, 'LTI context_id', VALUE_OPTIONAL),
                'context_label' => new external_value(PARAM_TEXT, 'LTI context_label', VALUE_OPTIONAL),
                'context_title' => new external_value(PARAM_TEXT, 'LTI context_title', VALUE_OPTIONAL),
                'context_type' => new external_value(PARAM_TEXT, 'LTI context_type', VALUE_OPTIONAL),
                'ext_lms' => new external_value(PARAM_TEXT, 'LTI ext_lms', VALUE_OPTIONAL),
                'ext_roles' => new external_value(PARAM_TEXT, 'LTI ext_roles', VALUE_OPTIONAL),
                'ext_user_username' => new external_value(PARAM_TEXT, 'LTI ext_user_username', VALUE_OPTIONAL),
                'launch_presentation_document_target' => new external_value(PARAM_TEXT, 'LTI launch_presentation_document_target', VALUE_OPTIONAL),
                'launch_presentation_height' => new external_value(PARAM_NUMBER, 'LTI launch_presentation_height', VALUE_OPTIONAL),
                'launch_presentation_locale' => new external_value(PARAM_TEXT, 'LTI launch_presentation_locale', VALUE_OPTIONAL),
                'launch_presentation_width' => new external_value(PARAM_NUMBER, 'LTI launch_presentation_width', VALUE_OPTIONAL),
                'lis_course_section_sourcedid' => new external_value(PARAM_TEXT, 'LTI lis_course_section_sourcedid', VALUE_OPTIONAL),
                'lis_outcome_service_url' => new external_value(PARAM_TEXT, 'LTI lis_outcome_service_url', VALUE_OPTIONAL),
                'lis_person_name_full' => new external_value(PARAM_TEXT, 'LTI lis_person_name_full', VALUE_OPTIONAL),
                'lis_person_sourcedid' => new external_value(PARAM_TEXT, 'LTI lis_person_sourcedid', VALUE_OPTIONAL),
                'lis_result_sourcedid' => new external_value(PARAM_TEXT, 'LTI lis_result_sourcedid', VALUE_OPTIONAL),
                'lti_message_type' => new external_value(PARAM_TEXT, 'LTI lti_message_type', VALUE_OPTIONAL),
                'lti_version' => new external_value(PARAM_TEXT, 'LTI lti_version', VALUE_OPTIONAL),
                'resource_link_description' => new external_value(PARAM_TEXT, 'LTI resource_link_description', VALUE_OPTIONAL),
                'resource_link_id' => new external_value(PARAM_TEXT, 'LTI resource_link_id', VALUE_OPTIONAL),
                'resource_link_title' => new external_value(PARAM_TEXT, 'LTI resource_link_title', VALUE_OPTIONAL),
                'roles' => new external_value(PARAM_TEXT, 'LTI roles', VALUE_OPTIONAL),
                'tool_consumer_info_product_family_code' => new external_value(PARAM_TEXT, 'LTI tool_consumer_info_product_family_code', VALUE_OPTIONAL),
                'tool_consumer_info_version' => new external_value(PARAM_TEXT, 'LTI tool_consumer_info_version', VALUE_OPTIONAL),
                'tool_consumer_instance_contact_email' => new external_value(PARAM_TEXT, 'LTI tool_consumer_instance_contact_email', VALUE_OPTIONAL),
                'tool_consumer_instance_description' => new external_value(PARAM_TEXT, 'LTI tool_consumer_instance_description', VALUE_OPTIONAL),
                'tool_consumer_instance_guid' => new external_value(PARAM_TEXT, 'LTI tool_consumer_instance_guid', VALUE_OPTIONAL),
                'tool_consumer_instance_name' => new external_value(PARAM_TEXT, 'LTI tool_consumer_instance_name', VALUE_OPTIONAL),
                'user_id' => new external_value(PARAM_TEXT, 'LTI user_id', VALUE_OPTIONAL),
                'user_image' => new external_value(PARAM_URL, 'LTI user_image', VALUE_OPTIONAL),

                // Canvas specific LTI params
                'custom_canvas_api_domain' => new external_value(PARAM_TEXT, 'LTI custom_canvas_api_domain', VALUE_OPTIONAL),
                'custom_canvas_api_domain' => new external_value(PARAM_TEXT, 'LTI custom_canvas_api_domain', VALUE_OPTIONAL),
                'custom_canvas_course_id' => new external_value(PARAM_TEXT, 'LTI custom_canvas_course_id', VALUE_OPTIONAL),
                'custom_canvas_enrollment_state' => new external_value(PARAM_TEXT, 'LTI custom_canvas_enrollment_state', VALUE_OPTIONAL),
                'custom_canvas_user_id' => new external_value(PARAM_TEXT, 'LTI custom_canvas_user_id', VALUE_OPTIONAL),
                'custom_canvas_user_login_id' => new external_value(PARAM_TEXT, 'LTI custom_canvas_user_login_id', VALUE_OPTIONAL),
                'custom_canvas_workflow_state' => new external_value(PARAM_TEXT, 'LTI custom_canvas_workflow_state', VALUE_OPTIONAL),
            )
        );
    }

   /**
    * parameter definition for output of autologin_redirect method
    */
    public static function launch_returns() {
        return null;
    }


    public static function launch($params) {
        global $USER, $SESSION, $WEBSERVICE_INSTITUTION, $WEBSERVICE_OAUTH_SERVERID;

        $keys = array_keys(self::launch_parameters()->keys);
        $params = array_combine($keys, func_get_args());

        // Get auth instance for institution that issued OAuth key
        $authinstanceid = get_field('auth_instance', 'id', 'instancename', 'webservice', 'institution', $WEBSERVICE_INSTITUTION);

        if (!$authinstanceid) {
            $USER->logout();
            throw new AccessDeniedException(get_string('webserviceauthdisabled', 'module.lti'));
        }

        // Check for userid in auth_remote_user
        $userid = get_field('auth_remote_user', 'localusr', 'authinstance', $authinstanceid, 'remoteusername', $params['user_id']);

        $updateremote = false;
        $updateuser = true;

        // User not found - try to match on email
        if (!$userid && isset($params['lis_person_contact_email_primary'])) {
            log_debug('User not found in auth_remote_user with user_id:'.$params['user_id']);
            $userid = get_field('artefact_internal_profile_email', 'owner', 'email', $params['lis_person_contact_email_primary'], 'verified', 1);

            $updateremote = true;
        }

        // Check user belongs to institution specified by OAuth key
        if ($userid) {

            $is_site_admin = false;

            foreach (get_site_admins() as $site_admin) {
                if ($site_admin->id == $userid) {
                    $is_site_admin = true;
                    break;
                }
            }

            if (!$is_site_admin) {
                // check user is member of configured OAuth institution
                $institutions = array_keys(load_user_institutions($userid));
                if (!in_array($WEBSERVICE_INSTITUTION, $institutions)) {
                    $USER->logout();
                    die_info(get_string('institutiondenied', 'module.lti', institution_display_name($WEBSERVICE_INSTITUTION)));
                }
            }
        }

        // Auto create user if auth allowed
        $canautocreate = get_field('oauth_server_config', 'value', 'oauthserverregistryid', $WEBSERVICE_OAUTH_SERVERID, 'field', 'autocreateusers');

        if (!$userid) {
            if ($canautocreate) {

                $user = new stdClass;
                $user->email = $params['lis_person_contact_email_primary'];
                $user->password = sha1(uniqid('', true));
                $user->firstname = $params['lis_person_name_given'];
                $user->lastname = $params['lis_person_name_family'];
                $user->authinstance = $authinstanceid;

                // Make sure that the username doesn't already exist
                if (get_record('usr', 'username', $user->email)) {
                    $USER->logout();
                    throw new WebserviceInvalidParameterException(get_string('usernameexists1', 'module.lti', $user->email));
                }

                $user->username = $user->email;

                $userid = create_user($user, array(), $WEBSERVICE_INSTITUTION, true, $params['user_id']);

                $updateremote = false;
                $updateuser = false;
            }
            else {
                $USER->logout();
                throw new AccessDeniedException(get_string('autocreationnotenabled', 'module.lti'));
            }
        }

        $user = get_record('usr', 'id', $userid, 'deleted', 0);

        if ($updateuser) {
            $user->email = $params['lis_person_contact_email_primary'];
            $user->firstname = $params['lis_person_name_given'];
            $user->lastname = $params['lis_person_name_family'];
            unset($user->password);
            update_user($user);
        }

        log_debug('found userid: '.$user->id);

        if ($updateremote) {
            $authremoteuser = new StdClass;
            $authremoteuser->authinstance = $authinstanceid;
            $authremoteuser->remoteusername = $params['user_id'];
            $authremoteuser->localusr = $user->id;

            insert_record('auth_remote_user', $authremoteuser);
        }

        log_debug('reanimating: '.var_export($user->username, true));
        $USER->reanimate($user->id, $authinstanceid);

        if (isset($params['launch_presentation_return_url'])) {
            $SESSION->set('logouturl', $params['launch_presentation_return_url']);
        }

        redirect(get_config('wwwroot'));

    }
}
