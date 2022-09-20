<?php
/**
 *
 * @package    mahara
 * @subpackage module-lti
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

use Dwoo\Template\Str;
use \IMSGlobal\LTI;

if (!defined('INTERNAL')) {
    die();
}
require_once(get_config('docroot') . 'webservice/lib.php');
require_once(get_config('docroot') . 'webservice/mahara_url.php');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'collection.php');
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

    private static function process_resource_launch(array $params, $launch) {
        global $SESSION;
        if (!isset($params['resource_launch']) || empty($params['resource_launch']['PublicUrl'])) {
            // No page link
            redirect();
        }
        $url = $params['resource_launch']['PublicUrl'];
        $canview = array();

        $mahara_url = new mahara_url($url);
        $path = $mahara_url->get_path();
        switch($path) {
            case '/view/view.php':
                // The View ID is the id param on the URL.
                $view = new View($mahara_url->get_param('id'));
                if ($view) {
                    $collection = $view->get_collection();
                    if ($collection) {
                        // Our view is part of a collection.
                        if ($pid = $collection->has_progresscompletion()) {
                            $canview[] = $pid;
                        }
                        $views = $collection->get('views');
                        // Add all View IDs for views in the collection.
                        foreach ($views['views'] as $view) {
                            $canview[] = $view->id;
                        }
                    }
                    else {
                        // Our view is not part of a collection.
                        $canview[] = $view->get('id');
                    }
                }
                break;

            case '/collection/progresscompletion.php':
            case '/module/framework/matrix.php':
                // The id on the URL is for a collection. Fetch the views.
                $collectionid = (int) $mahara_url->get_param('id');
                $collection = new Collection($collectionid);
                if ($pid = $collection->has_progresscompletion()) {
                    $canview[] = $pid;
                }
                $views = $collection->get('views');
                foreach ($views['views'] as $view) {
                    $canview[] = $view->id;
                }
                break;

            default:
                // We have a Clean URL?  Fetch our content from the path.
                $bits = explode('/', $path);
                $view = new View(['ownerurlid' => $bits[2], 'urlid' => $bits[3]]);
                if ($view) {
                    $collection = $view->get_collection();
                    if ($collection) {
                        // Our view is part of a collection.
                        if ($pid = $collection->has_progresscompletion()) {
                            $canview[] = $pid;
                        }
                        $views = $collection->get('views');
                        // Add all View IDs for views in the collection.
                        foreach ($views['views'] as $view) {
                            $canview[] = $view->id;
                        }
                    }
                    else {
                        // Our view is not part of a collection.
                        $canview[] = $view->get('id');
                    }
                }
        }

        if (!empty($canview)) {
            $SESSION->set('lti.canviewview', $canview);
        }

        redirect($url);
    }

    /**
     * React to a Deep Link request.
     *
     * @param array $params
     * @param LTI\LTI_Message_Launch $launch
     * @param boolean $testing Allows for testing without a real $launch object.
     *
     * @return string
     *   The HTML of the Deep Link response.
     */
    private static function process_deep_link(array $params, $launch, $testing = false) {
        global $USER, $SESSION, $WEBSERVICE_INSTITUTION;

        // We need session cookies to work in an iframe.
        $SESSION->allow_cookie_in_iframe();

        $owner_views = View::views_by_owner(null, null, true);
        $data = $owner_views[1];

        // This allows for us to call this in a test environment without LTI.
        if ($testing) {
            $launch_id = 'this_is_a_test';
        }
        else {
            $launch_id = $launch->get_launch_id();
        }

        $section_links = array();
        $page_links = array(
            'title' => get_string('Pages', 'view'),
            'links' => array(),
        );
        $collection_links = array(
            'title' => get_string('Collections', 'collection'),
            'links' => array(),
        );
        foreach ($data->data as $portfolio) {
            if ($portfolio['submittedstatus']) {
                // This one has been submitted.  Skip to the next portfolio.
                continue;
            }
            $is_collection = !empty($portfolio['collid']);
            $is_portfolio = ($portfolio['type'] == 'portfolio');
            if ($is_collection || $is_portfolio) {
                $link = get_config('wwwroot') . 'module/lti_advantage/deep_link.php?launch_id=' . $launch_id;
                $link .= '&portfolio_id=' . $portfolio['id'];
                if ($is_collection) {
                    $link .= '&collection_id=' . $portfolio['collid'];
                    $collection_links['links'][] = array(
                        'href' => $link,
                        'text' => $portfolio['title'],
                    );
                }
                else {
                    $page_links['links'][] = array(
                        'href' => $link,
                        'text' => $portfolio['title'],
                    );
                }
            }
        }
        if (!empty($page_links['links'])) {
            $section_links[] = $page_links;
        }
        if (!empty($collection_links['links'])) {
            $section_links[] = $collection_links;
        }

        $stylesheets = [];
        $extraconfig = [];
        $stylesheets = get_stylesheets_for_current_page($stylesheets, $extraconfig);

        $smarty = smarty();
        $smarty->assign('STYLESHEETLIST', $stylesheets);
        $smarty->assign('title', get_string('deeplinkportfoliostitle', 'module.lti_advantage'));
        $smarty->assign('links', $section_links);
        $smarty->assign('nolinks', get_string('deeplinknoportfolios', 'module.lti_advantage'));
        $smarty->display('module:lti_advantage:deeplinkportfoliolist.tpl');
        die();
    }

    /**
     * Helper function to debug the deep link content.
     *
     * Called from /module/lti_advantage/test.php while logged in as a test
     * user.  The content is what would be sent to the deep linked quicklink
     * embed.
     */
    public static function test_deep_link() {
        $params = array();
        $launch = '';
        $testing = true;
        return self::process_deep_link($params, $launch, $testing, true);
    }

    /**
     * Provision a new User with the appropriate roles if needed.
     *
     * @param array $params
     *
     * @return void
     */
    private static function provision_names_and_roles(array $params) {
        global $USER, $SESSION, $WEBSERVICE_INSTITUTION;

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

        // only redirect if is a group admin
        if (isset($groupid) && PluginModuleLti_advantage::can_create_groups($params['roles'])) {
            redirect(get_config('wwwroot') . 'group/view.php?id=' . $groupid);
            exit;
        }
    }

    public static function launch_advantage($params) {
        global $USER, $SESSION, $WEBSERVICE_INSTITUTION, $WEBSERVICE_OAUTH_SERVERID, $CFG;

        $authinstanceid = get_field('auth_instance', 'id', 'authname', 'webservice', 'institution', $WEBSERVICE_INSTITUTION);
        if (!$authinstanceid) {
            $USER->logout();
            throw new AccessDeniedException(get_string('webserviceauthdisabled', 'module.lti'));
        }

        $user = PluginModuleLti_advantage::module_lti_advantage_ensure_user_exists($params, $WEBSERVICE_INSTITUTION, $authinstanceid, $WEBSERVICE_OAUTH_SERVERID);

        $session_was_staff = $SESSION->get('user/staff');
        $USER->reanimate($user->id, $authinstanceid);
        if ($session_was_staff) {
            // The user was set as a staff member for this session. Put that
            // back in place.
            $SESSION->set('user/staff', 1);
        }

        // If the consumer supports grading send the user to select a portfolio.
        if (!empty($params['lis_outcome_service_url'])) {
            $parts = parse_url($params['launch_presentation_return_url']);
            $cspurl = $parts['scheme'] . '://' . $parts['host'];
            $SESSION->set('csp-ancestor-exemption', $cspurl);

            // If the user is a tutor or admin set the 'lti.submittedhost' as
            // well. This is used to expose the Release View/Collection form.
            // This value is the one that is used when submitting a view or
            // collection. If that value ever changes, this one needs to match
            // that.
            if (array_key_exists('roles', $params)) {
                $role = strtolower($params['roles']);
                if (strpos($role, 'instructor') !== false || strpos($role, 'administrator') !== false) {
                    $SESSION->set('lti.submittedhost', $cspurl);
                }
            }
        }

        // Rebuild the launch object.
        $lti_db = new LTI_Advantage_Database();
        $lti_cache = new LTI\Cache();
        $lti_cache->set_cache_dir($CFG->dataroot . '/temp');
        $launch =  LTI\LTI_Message_Launch::from_cache($params['launch_id'], $lti_db, $lti_cache);
        // If it's a Name and roles provisioning extension
        if ($launch->has_nrps() && isset($params['roles']) && PluginModuleLti_advantage::can_create_groups($params['roles'])) {
            // Process the NRPS call.
            self::provision_names_and_roles($params);
        }
        else if ($launch->is_resource_launch() && key_exists('resource_launch', $params)) {
            self::process_resource_launch($params, $launch);
        }
        else if ($launch->is_deep_link_launch()) {
            // Currently this returns the list of Portfolios to select.
            self::process_deep_link($params, $launch);
        }

        redirect();
        exit;
    }
}
