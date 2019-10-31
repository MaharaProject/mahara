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
                'lis_outcome_service_url' => new external_value(PARAM_URL, 'LTI lis_outcome_service_url', VALUE_OPTIONAL),
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
                'ext_ims_lis_basic_outcome_url' => new external_value(PARAM_TEXT, 'LTI ext_ims_lis_basic_outcome_url', VALUE_OPTIONAL),
                'ext_lti_assignment_id' => new external_value(PARAM_TEXT, 'LTI ext_lti_assignment_id', VALUE_OPTIONAL),
                'ext_outcome_data_values_accepted' => new external_value(PARAM_TEXT, 'LTI ext_outcome_data_values_accepted', VALUE_OPTIONAL),
                'ext_outcome_result_total_score_accepted' => new external_value(PARAM_TEXT, 'LTI ext_outcome_result_total_score_accepted', VALUE_OPTIONAL),
                'ext_outcome_submission_submitted_at_accepted' => new external_value(PARAM_TEXT, 'LTI ext_outcome_submission_submitted_at_accepted', VALUE_OPTIONAL),
                'ext_outcomes_tool_placement_url' => new external_value(PARAM_TEXT, 'LTI ext_outcomes_tool_placement_url', VALUE_OPTIONAL),
                'custom_canvas_assignment_id' => new external_value(PARAM_TEXT, 'LTI custom_canvas_assignment_id', VALUE_OPTIONAL),
                'custom_canvas_assignment_points_possible' => new external_value(PARAM_TEXT, 'LTI custom_canvas_assignment_points_possible', VALUE_OPTIONAL),
                'custom_canvas_assignment_title' => new external_value(PARAM_TEXT, 'LTI custom_canvas_assignment_title', VALUE_OPTIONAL),

                // Blackboard specific LTI params
                'ext_launch_id' => new external_value(PARAM_TEXT, 'Blackboard ext_launch_id', VALUE_OPTIONAL),
                'ext_launch_presentation_css_url' => new external_value(PARAM_URL, 'Blackboard ext_launch_presentation_css_url', VALUE_OPTIONAL),
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

        // User not found
        $remoteusername = false;
        // - try to match on ext username
        if (!$userid && isset($params['ext_user_username'])) {
            log_debug('User not found in auth_remote_user with user_id:' . $params['user_id']);
            $userid = get_field('auth_remote_user', 'localusr', 'authinstance', $authinstanceid, 'remoteusername', $params['ext_user_username']);
            $updateremote = true;
            $remoteusername = $params['ext_user_username'];
        }
        // User not found
        // - try to match on email
        if (!$userid && isset($params['lis_person_contact_email_primary'])) {
            log_debug('User not found in auth_remote_user with ext_user_username:' . $params['ext_user_username']);
            $userid = get_field_sql("SELECT DISTINCT owner
                                     FROM {artefact_internal_profile_email}
                                     WHERE LOWER(email) = ?
                                     AND verified = ?", array(strtolower($params['lis_person_contact_email_primary']), 1));
            $updateremote = true;
            if (empty($remoteusername)) {
                $remoteusername = $params['lis_person_contact_email_primary'];
            }
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
                if (empty($institutions)) {
                    // we check if they are in the 'mahara' institution
                    $institutions = array('mahara');
                }

                if (!in_array($WEBSERVICE_INSTITUTION, $institutions)) {
                    $USER->logout();
                    die_info(get_string('institutiondenied', 'module.lti', institution_display_name($WEBSERVICE_INSTITUTION)));
                }
            }
        }

        // Auto create user if auth allowed
        $canautocreate = get_field('oauth_server_config', 'value', 'oauthserverregistryid', $WEBSERVICE_OAUTH_SERVERID, 'field', 'autocreateusers');
        $parentauthid = get_field('oauth_server_config', 'value', 'oauthserverregistryid', $WEBSERVICE_OAUTH_SERVERID, 'field', 'parentauth');

        if (!$userid) {
            if ($canautocreate) {

                $user = new stdClass();
                $user->email = $params['lis_person_contact_email_primary'];
                $user->password = sha1(uniqid('', true));
                $user->firstname = $params['lis_person_name_given'];
                $user->lastname = $params['lis_person_name_family'];
                $user->authinstance = !empty($parentauthid) ? $parentauthid : $authinstanceid;
                $user->username = !empty($remoteusername) ? $remoteusername : $user->email;
                // Make sure that the username doesn't already exist
                if (get_field_sql("SELECT username
                                   FROM {usr}
                                   WHERE LOWER(username) = ?", array(strtolower($user->username)))) {
                    $USER->logout();
                    throw new WebserviceInvalidParameterException(get_string('usernameexists2', 'module.lti', $user->username));
                }

                $userid = create_user($user, array(), $WEBSERVICE_INSTITUTION, true, $params['user_id']);

                $updateremote = false;
                $updateuser = false;

                if ($parentauthid) {
                    $authremoteuser = new stdClass();
                    $authremoteuser->authinstance = $parentauthid;
                    $authremoteuser->remoteusername = $user->username;
                    $authremoteuser->localusr = $user->id;

                    insert_record('auth_remote_user', $authremoteuser);
                }
            }
            else {
                $USER->logout();
                throw new AccessDeniedException(get_string('autocreationnotenabled', 'module.lti'));
            }
        }

        $user = get_record('usr', 'id', $userid, 'deleted', 0);
        if ($updateuser) {
            if (strtolower($user->email) != strtolower($params['lis_person_contact_email_primary'])) {
                $user->email = $params['lis_person_contact_email_primary'];
            }
            $user->firstname = $params['lis_person_name_given'];
            $user->lastname = $params['lis_person_name_family'];
            $user->authinstance = !empty($parentauthid) ? $parentauthid : $authinstanceid;
            unset($user->password);

            $profilefields = new stdClass();
            $remoteuser = null;
            // We need to update the following fields for both the usr and artefact tables
            foreach (array('firstname', 'lastname', 'email') as $field) {
                if (isset($user->{$field})) {
                    $profilefields->{$field} = $user->{$field};
                }
            }
            update_user($user, $profilefields, $remoteuser);
        }

        log_debug('found userid: '.$user->id);

        if ($updateremote) {
            $authremoteuser = new stdClass();
            $authremoteuser->authinstance = $authinstanceid;
            $authremoteuser->remoteusername = $params['user_id'];
            $authremoteuser->localusr = $user->id;

            insert_record('auth_remote_user', $authremoteuser);
        }

        log_debug('reanimating: ' . var_export($user->username, true));
        $USER->reanimate($user->id, $authinstanceid);

        if (isset($params['launch_presentation_return_url'])) {
            $SESSION->set('logouturl', $params['launch_presentation_return_url']);
        }

        $SESSION->set('lti.lis_result_sourcedid', $params['lis_result_sourcedid']);
        $SESSION->set('lti.roles', $params['roles']);
        $SESSION->set('lti.presentation_target', $params['launch_presentation_document_target']);
        $SESSION->set('lti.launch_presentation_return_url', $params['launch_presentation_return_url']);

        // If the consumer supports grading send the user to select a portfolio
        if (!empty($params['lis_outcome_service_url'])) {
            $parts = parse_url($params['launch_presentation_return_url']);
            $cspurl = $parts['scheme'] . '://' . $parts['host'];

            $SESSION->set('csp-ancestor-exemption', $cspurl);

            db_begin();

            if ($assessment = get_record('lti_assessment', 'oauthserver', $WEBSERVICE_OAUTH_SERVERID, 'resourcelinkid', $params['resource_link_id'])) {
                if ($assessment->contexttitle != $params['context_title'] || $assessment->resourcelinktitle != $params['resource_link_title']) {
                    $assessment->contexttitle = $params['context_title'];
                    $assessment->resourcelinktitle = $params['resource_link_title'];
                    update_record('lti_assessment', $assessment);
                }

                $groups = $USER->get('grouproles');

                if (!isset($groups[$assessment->group])) {
                    group_add_user($assessment->group, $USER->get('id'), PluginModuleLti::can_grade() ? 'admin' : 'member');
                }

            }
            else {

                if (!$groupid = get_field('lti_assessment', 'group', 'oauthserver', $WEBSERVICE_OAUTH_SERVERID, 'contextid', $params['context_id'], 'resourcelinkid', $params['resource_link_id'])) {

                    $basename = get_string('groupname', 'module.lti', $params['context_title'], $params['resource_link_title']);
                    $name = $basename;

                    // generate a unique group name
                    $i = 0;
                    while (get_record('group', 'name', $name)) {
                        $name = $basename . ' - ' . $i++;
                    }

                    // Create assessment group
                    $group = array(
                        'name'           => $name,
                        'institution'    => $WEBSERVICE_INSTITUTION,
                        'grouptype'      => 'standard',
                        'submittableto'  => true,
                        'hidemembersfrommembers' => 1,
                        'hidemembers'    => 1,
                        'members'        => array($USER->get('id') => PluginModuleLti::can_grade() ? 'admin' : 'member'),
                        'allowarchives'  => true,
                        'hidden'         => true,
                        'submittableto'  => false,
                    );

                    $groupid = group_create($group);
                }

                // Create assessment record
                $assessment = new stdClass;
                $assessment->oauthserver = $WEBSERVICE_OAUTH_SERVERID;
                $assessment->resourcelinkid = $params['resource_link_id'];
                $assessment->contextid = $params['context_id'];
                $assessment->lisoutcomeserviceurl = $params['lis_outcome_service_url'];
                $assessment->contexttitle = $params['context_title'];
                $assessment->resourcelinktitle = $params['resource_link_title'];
                $assessment->group = $groupid;
                $assessment->lock = 0; // Have the config for lock set to false by default so portfolios get unlocked after grading

                $assessment->id = insert_record('lti_assessment', $assessment, 'id', true);
            }

            db_commit();

            $SESSION->set('lti.assessment', $assessment->id);

            redirect(get_config('wwwroot') . 'module/lti/submission.php');
            exit;
        }

        redirect(get_config('wwwroot'));

    }
}
