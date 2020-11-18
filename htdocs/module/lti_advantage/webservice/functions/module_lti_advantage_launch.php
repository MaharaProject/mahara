<?php
/**
 *
 * @package    mahara
 * @subpackage module-lti
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

use Dwoo\Template\Str;
use \IMSGlobal\LTI;

if (!defined('INTERNAL')) {
    die();
}
require_once(get_config('docroot') . 'webservice/lib.php');
require_once(get_config('docroot') . 'module/lti_advantage/lib/lti-1-3-php-library/lti/lti.php');
require_once(get_config('docroot') . 'module/lti_advantage/database.php');
/**
 * Functions needed to launch Mahara as an LTI provider
 */
class module_lti_advantage_launch extends external_api {

    /**
     * parameter definition for input of launch method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */

     public static function launch_advantage_parameters() {
        return new external_function_parameters(
            array(
                // Required Params
                'user_id' => new external_value(PARAM_TEXT, 'LTI user_id', VALUE_REQUIRED),
                'ext_user_username' => new external_value(PARAM_EMAIL, 'LTI ext_user_username', VALUE_REQUIRED),
                'email' => new external_value(PARAM_TEXT, 'LTI email', VALUE_REQUIRED),
                'given_name' => new external_value(PARAM_TEXT, 'LTI given_name', VALUE_REQUIRED),
                'family_name' => new external_value(PARAM_TEXT, 'LTI family_name', VALUE_REQUIRED),
                'iss' => new external_value(PARAM_TEXT, 'LTI iss', VALUE_REQUIRED),
                'deployment_id' => new external_value(PARAM_TEXT, 'LTI deployment_id', VALUE_REQUIRED),
                'launch_id' => new external_value(PARAM_TEXT, 'LTI launch_id', VALUE_REQUIRED),

                // Optional Params
                'context_id' => new external_value(PARAM_TEXT, 'LTI context_id', VALUE_OPTIONAL),
                'context_label' => new external_value(PARAM_TEXT, 'LTI context_label', VALUE_OPTIONAL),
                'context_title' => new external_value(PARAM_TEXT, 'LTI context_title', VALUE_OPTIONAL),
                'context_type' => new external_value(PARAM_TEXT, 'LTI context_type', VALUE_OPTIONAL),
                'ext_roles' => new external_value(PARAM_TEXT, 'LTI ext_roles', VALUE_OPTIONAL),
                'lis_person_name_full' => new external_value(PARAM_TEXT, 'LTI lis_person_name_full', VALUE_OPTIONAL),
                'lis_person_sourcedid' => new external_value(PARAM_TEXT, 'LTI lis_person_sourcedid', VALUE_OPTIONAL),
                'lis_result_sourcedid' => new external_value(PARAM_TEXT, 'LTI lis_result_sourcedid', VALUE_OPTIONAL),
                'lti_message_type' => new external_value(PARAM_TEXT, 'LTI lti_message_type', VALUE_OPTIONAL),
                'lti_version' => new external_value(PARAM_TEXT, 'LTI lti_version', VALUE_OPTIONAL),
                'resource_link_description' => new external_value(PARAM_TEXT, 'LTI resource_link_description', VALUE_OPTIONAL),
                'resource_link_id' => new external_value(PARAM_TEXT, 'LTI resource_link_id', VALUE_OPTIONAL),
                'resource_link_title' => new external_value(PARAM_TEXT, 'LTI resource_link_title', VALUE_OPTIONAL),
                'roles' => new external_value(PARAM_TEXT, 'LTI roles', VALUE_OPTIONAL),
                'context_memberships_url' => new external_value(PARAM_TEXT, 'LTI context_memberships_url', VALUE_OPTIONAL),
                'service_versions' => new external_value(PARAM_TEXT, 'LTI service_versions', VALUE_OPTIONAL),

            )
        );
    }

   /**
    * parameter definition for output of autologin_redirect method
    */
    public static function launch_advantage_returns() {
        return null;
    }

    public static function launch_advantage($params) {
        global $USER, $SESSION, $WEBSERVICE_INSTITUTION, $WEBSERVICE_OAUTH_SERVERID;

        $authinstanceid = get_field('auth_instance', 'id', 'instancename', 'webservice', 'institution', $WEBSERVICE_INSTITUTION);
        if (!$authinstanceid) {
            $USER->logout();
            throw new AccessDeniedException(get_string('webserviceauthdisabled', 'module.lti'));
        }

        $user = PluginModuleLti_advantage::module_lti_advantage_ensure_user_exists($params, $WEBSERVICE_INSTITUTION, $authinstanceid, $WEBSERVICE_OAUTH_SERVERID);

        log_debug('reanimating: ' . var_export($user->username, true));
        $USER->reanimate($user->id, $authinstanceid);

        // If it's a Name and roles provisioning extension
        $launch =  LTI\LTI_Message_Launch::from_cache($params['launch_id'], new LTI_Advantage_Database());
        if ($launch->has_nrps() && isset($params['roles']) && PluginModuleLti_advantage::can_create_groups($params['roles'])) {

            db_begin();
            // check the group exists
            if ($group_membership = get_record('lti_advantage_group_membership', 'external_unit_id', $params['context_id'])) {
                if (!$group = get_record('group', 'id', $group_membership->group_id, 'deleted', 0)) {
                    delete_records('lti_advantage_group_membership', 'group_id', $group_membership->group_id);
                    $group_membership = null;
                }
            }

            if ($group_membership) {
                $group = get_record('group', 'id', $group_membership->group_id);
                if ($group->name != get_string('groupname', 'module.lti_advantage', $params['context_title'])) {
                    // if name is taken, don't do anything
                    if (!get_record('group', 'name', get_string('groupname', 'module.lti_advantage', $params['context_title']))) {
                        $group->name = get_string('groupname', 'module.lti_advantage', $params['context_title']);
                        update_record('group', $group);
                    }
                }

                $groups = $USER->get('grouproles');

                if (!isset($groups[$group_membership->group_id])) {
                    group_add_user($group_membership->group_id, $USER->get('id'), PluginModuleLti_advantage::can_create_groups($params['roles']) ? 'admin' : 'member');
                }
                $groupid = $group_membership->group_id;
            }
            else {
                $basename = get_string('groupname', 'module.lti_advantage', $params['context_title']);
                $name = $basename;

                // generate a unique group name
                $i = 0;
                while (get_record('group', 'name', $name)) {
                    $name = $basename . ' - ' . $i++;
                }
                /***************************************************/
                $group_data = (object) array(
                    'id'             => null,
                    'name'           => $name,
                    'description'    => null,
                    'institution'    => $WEBSERVICE_INSTITUTION,
                    'grouptype'      => 'course',
                    'open'           => 1,
                    'controlled'     => 0,
                    'request'        => 0,
                    'category'       => 0,
                    'public'         => 0,
                    'usersautoadded' => 0,
                    'viewnotify'     => GROUP_ROLES_ALL,
                    'submittableto'  => 0,
                    'allowarchives'  => 0,
                    'editroles'      => 'all',
                    'hidden'         => 0,
                    'groupparticipationreports' => 0,
                    'invitefriends'  => 0,
                    'suggestfriends' => 0,
                    'urlid'          => null,
                    'editwindowstart' => null,
                    'editwindowend'  => null,
                    'sendnow'        => 0,
                    'feedbacknotify' => GROUP_ROLES_ALL,
                    'hidemembers'    => GROUP_HIDE_NONE,
                    'hidemembersfrommembers'  => GROUP_HIDE_NONE,
                    'members'        => array($USER->get('id') => PluginModuleLti_advantage::can_create_groups($params['roles']) ? 'admin' : 'member'),
                );

                $group_prefix = 'group_';
                if ($group_defaults = get_records_sql_array("SELECT * FROM {institution_config}
                                                             WHERE institution = ? AND field LIKE ? || '%'", array('mahara', $group_prefix))) {
                    foreach ($group_defaults as $k => $v) {
                        $item = preg_replace('/^' . $group_prefix . '/', '', $v->field);
                        if (array_key_exists($item, (array)$group_data)) {
                            if ($item == 'editwindowstart' || $item == 'editwindowend') {
                                $v->value = strtotime($v->value);
                            }
                            $group_data->$item = $v->value;
                        }
                    }
                }

                $groupid = group_create((array)$group_data, true);

                $group_membership = new stdClass();
                $group_membership->context_memberships_url = $params['context_memberships_url'];
                $group_membership->service_versions = $params['service_versions'];
                $group_membership->registration_id = get_field('lti_advantage_registration', 'id', 'issuer', $params['iss']); // there should be only one
                $group_membership->state = null;
                $group_membership->group_id = $groupid; // the one we just created :)
                $group_membership->external_unit_id = $params['context_id'];

                $group_membership->id = insert_record('lti_advantage_group_membership', $group_membership);

                $sql = "UPDATE {module_cron}
                        SET nextrun = null
                        WHERE callfunction = ?
                        AND plugin = ?";
                execute_sql($sql, array('lti_advantage_get_members_cron', 'lti_advantage'));

                $SESSION->add_ok_msg(get_string('usercreationupdate', 'module.lti_advantage'));

            }

            db_commit();

            //$SESSION->set('lti.group_membership', $group_membership->id);

            // only redirect if is a group admin
            if (isset($groupid) && PluginModuleLti_advantage::can_create_groups($params['roles'])) {
                redirect(get_config('wwwroot') . 'group/view.php?id=' . $groupid);
                exit;
            }
        }

        redirect();
        exit;
    }
}
