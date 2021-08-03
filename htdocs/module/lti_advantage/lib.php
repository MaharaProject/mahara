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


use \IMSGlobal\LTI;
defined('INTERNAL') || die();

require_once('lib/lti-1-3-php-library/lti/lti.php');
require_once('database.php');
require_once(dirname(dirname(dirname(__FILE__))) . '/lib/institution.php');
class PluginModuleLti_advantage extends PluginModule {
    public static $can_create_groups_role = array('Administrator','Instructor');
    public static $group_tutor_role = array('TeachingAssistant');

    /**
     * Is the plugin activated or not?
     *
     * @return boolean true, if the plugin is activated, otherwise false
     */
    public static function is_active() {
        $active = false;
        if (get_field('module_installed', 'active', 'name', 'lti_advantage')) {
            $active = true;
        }
        return $active;
    }

    /**
     * API-Function get the Plugin ShortName
     *
     * @return string ShortName of the plugin
     */
    public static function get_plugin_display_name() {
        return 'ltiadvantage';
    }

    public static function disable_webservice_fields() {
        return array('service' => 1, 'institution' => 1);
    }

    public static function extra_webservice_fields($dbconnection) {
        if (get_field('module_installed', 'active', 'name', 'lti_advantage')) {
            if (!isset($dbconnection->id)) {
                $platform = new StdClass();
            }
            else {
                $platform = get_record('lti_advantage_registration', 'connectionid', $dbconnection->id);
                if ($platform) {
                    $deployments = get_records_array('lti_advantage_deployment', 'registration_id', $platform->id);
                }
            }
            $extra_fields = array(
                'display_name' => array(
                    'defaultvalue' => isset($platform->display_name) ? $platform->display_name : null,
                    'type'         => 'text',
                    'size'         => 50,
                    'disabled'     => false,
                    'title'        => get_string('display_name', 'module.lti_advantage'),
                    'rules'        => array(
                        'required' => true
                    ),
                ),
                'issuer' => array(
                    'defaultvalue' => isset($platform->issuer) ? $platform->issuer : null,
                    'type'         => 'text',
                    'size'         => 50,
                    'disabled'     => false,
                    'title'        => get_string('issuer', 'module.lti_advantage'),
                    'rules'        => array(
                        'required' => true
                    ),
                ),
                'client_id' => array(
                    'defaultvalue' => isset($platform->client_id) ? $platform->client_id : null,
                    'type'         => 'text',
                    'size'         => 50,
                    'disabled'     => false,
                    'title'        => get_string('client_id', 'module.lti_advantage'),
                    'rules'        => array(
                        'required' => true
                    ),
                ),
                'platform_login_auth_endpoint' => array(
                    'defaultvalue' => isset($platform->platform_login_auth_endpoint) ? $platform->platform_login_auth_endpoint : null,
                    'type'         => 'text',
                    'size'         => 50,
                    'disabled'     => false,
                    'title'        => get_string('platform_login_auth_endpoint', 'module.lti_advantage'),
                    'rules'        => array(
                        'required' => true
                    ),
                ),
                'platform_service_auth_endpoint' => array(
                    'defaultvalue' => isset($platform->platform_service_auth_endpoint) ? $platform->platform_service_auth_endpoint : null,
                    'type'         => 'text',
                    'size'         => 50,
                    'disabled'     => false,
                    'title'        => get_string('platform_service_auth_endpoint', 'module.lti_advantage'),
                    'rules'        => array(
                        'required' => true
                    ),
                ),
                'platform_jwks_endpoint' => array(
                    'defaultvalue' => isset($platform->platform_jwks_endpoint) ? $platform->platform_jwks_endpoint : null,
                    'type'         => 'text',
                    'size'         => 50,
                    'disabled'     => false,
                    'title'        => get_string('platform_jwks_endpoint', 'module.lti_advantage'),
                    'rules'        => array(
                        'required' => true
                    ),
                ),
                'platform_auth_provider' => array(
                    'defaultvalue' => isset($platform->platform_auth_provider) ? $platform->platform_auth_provider : null,
                    'type'         => 'text',
                    'size'         => 50,
                    'disabled'     => false,
                    'title'        => get_string('platform_auth_provider', 'module.lti_advantage'),
                ),
                'deployments' => array(
                    'type'         => 'fieldset',
                    'legend'       => get_string('deployments', 'module.lti_advantage'),
                    'elements'     => array(
                        'description' => array(
                            'type' => 'html',
                            'value' => get_string('deploymentsdesc', 'module.lti_advantage'),
                        ),
                       'deployment1_id' => array(
                           'type'         => 'text',
                           'defaultvalue' => isset($deployments[0]) ? $deployments[0]->deployment_id : null,
                           'title'        =>get_string('deploymentsbasiclaunchtitle', 'module.lti_advantage'),
                       ),
                       'deployment2_id' => array(
                           'type'         => 'text',
                           'defaultvalue' => isset($deployments[1]) ? $deployments[1]->deployment_id : null,
                           'title'        =>get_string('deploymentsnrpstitle', 'module.lti_advantage'),
                       ),
                       'deployment3_id' => array(
                           'type'         => 'text',
                           'defaultvalue' => isset($deployments[2]) ? $deployments[2]->deployment_id : null,
                           'title'        =>get_string('deploymentsdeeplinkportfoliolisttitle', 'module.lti_advantage'),
                           'rules' => array(
                               'required' => true,
                           ),
                           'legend'       => 'test',
                       ),
                    ),
                ),
            );
            return $extra_fields;
        }
        return array();
    }

    /**
     * Webservice info fields.
     *
     * @return array
     */
    public static function info_webservice_fields() {
        $info_fields = array();
        if (get_field('module_installed', 'active', 'name', 'lti_advantage')) {
            $info_fields = array(
                'domain' => array(
                    'title' => get_string('domain', 'module.lti_advantage'),
                    'type' => 'html',
                    'value' => get_config('wwwroot'),
                ),
                'redirecturl' => array(
                    'title' => get_string('redirecturl', 'module.lti_advantage'),
                    'type' => 'html',
                    'value' => get_config('wwwroot') . 'module/lti_advantage/home.php',
                ),
                'openidconnectlogin' => array(
                    'title' => get_string('openidconnectlogin', 'module.lti_advantage'),
                    'type' => 'html',
                    'value' => get_config('wwwroot') . 'module/lti_advantage/login.php',
                ),
                'keyset' => array(
                    'title' => get_string('keyset', 'module.lti_advantage'),
                    'type' => 'html',
                    'value' => get_config('wwwroot') . 'module/lti_advantage/jwks.php',
                )
            );
        }
        return $info_fields;
    }

    private static $default_config = array(
        'autocreateusers'   => false,
        'parentauth'        => null,
    );

    public static function has_config() {
        return true;
    }

    public static function has_oauth_service_config() {
        return true;
    }

    /**
     * @param int $prevversion
     * @return bool|void
     */
    public static function postinst($prevversion) {

    }
    /**
     * Check the status of each configuration element needed for the LTI API
     *
     * @param boolean $clearcache Whether to clear the cached results of the check
     * @return array Information about the status of each config step needed.
     */
    public static function check_service_status($clearcache = false) {
        static $statuslist = null;
        if (!$clearcache && $statuslist !== null) {
            return $statuslist;
        }

        require_once(get_config('docroot') . 'webservice/lib.php');

        // Check all the configs needed for the LTI API to work.
        $statuslist = array();
        $statuslist[] = array(
            'name' => get_string('webserviceproviderenabled', 'module.lti'),
            'status' => (bool) get_config('webservice_provider_enabled')
        );
        $statuslist[] = array(
            'name' => get_string('oauthprotocolenabled', 'module.lti'),
            'status' => webservice_protocol_is_enabled('oauth')
        );
        $statuslist[] = array(
            'name' => get_string('restprotocolenabled', 'module.lti'),
            'status' => webservice_protocol_is_enabled('rest')
        );

        $servicerec = get_record('external_services', 'shortname', 'maharaltiadvantage', 'component', 'module/lti_advantage', null, null, 'enabled, restrictedusers, tokenusers');
        $statuslist[] = array(
            'name' => get_string('ltiserviceexists', 'module.lti_advantage'),
            'status' => (bool) $servicerec
        );

        return $statuslist;
    }

    /**
     * Determine whether the LTI webservice, as a whole, is fully configured
     * @param boolean $clearcache Whether to clear cached results from a previous check
     * @return boolean
     */
    public static function is_service_ready($clearcache = false) {
        return array_reduce(
            static::check_service_status($clearcache),
            function($carry, $item) {
                return $carry && $item['status'];
            },
            true
        );
    }

    public static function get_config_options() {

        $statuslist = static::check_service_status(true);
        $ready = static::is_service_ready();

        $smarty = smarty_core();
        $smarty->assign('statuslist', $statuslist);
        if ($ready) {
            $smarty->assign('notice', get_string('noticeenabled', 'module.lti'));
        }
        else {
            $smarty->assign('notice', get_string('noticenotenabled', 'module.lti'));
        }
        $statushtml = $smarty->fetch('module:lti:statustable.tpl');
        unset($smarty);

        $elements = array();
        $elements['statustable'] = array(
            'type' => 'html',
            'value' => $statushtml
        );

        if (!$ready) {
            $elements['activate'] = array(
                'type' => 'switchbox',
                'title' => get_string('autoconfiguretitle', 'module.lti'),
                'description' => get_string('autoconfiguredesc', 'module.lti'),
                'switchtext' => 'yesno',
            );
        }

        $form = array('elements' => $elements);

        if (!$ready) {
            // HACK: Reload the page after form submission, so that the status
            // table gets updated.
            $form['jssuccesscallback'] = 'module_lti_reload_page';
        }

        return $form;
    }

    public static function save_config_options(Pieform $form, $values) {

        if (!empty($values['activate'])) {
            set_config('webservice_provider_enabled', true);
            set_config('webservice_provider_oauth_enabled', true);
            set_config('webservice_provider_rest_enabled', true);

            require_once(get_config('docroot') . 'webservice/lib.php');
            external_reload_component('module/lti_advantage', false);
            set_field('external_services', 'enabled', 1, 'shortname', 'lti_advantage', 'component', 'module/lti_advantage');
        }
        return true;
    }

    public static function get_oauth_service_config_options($serverid) {
        $rawdbconfig = get_records_sql_array('SELECT c.field, c.value, r.institution
                                              FROM {oauth_server_registry} r
                                              LEFT JOIN {oauth_server_config} c ON c.oauthserverregistryid = r.id
                                              WHERE r.id = ?', array($serverid));
        $dbconfig = new stdClass();
        if ($rawdbconfig) {
            foreach ($rawdbconfig as $raw) {
                $dbconfig->institution = $raw->institution;
                if (!empty($raw->field)) {
                    $dbconfig->{$raw->field} = $raw->value;
                }
            }
        }

        $elements = array(
            'institution' => array(
                'type'  => 'html',
                'title' => get_string('institution'),
                'value' => institution_display_name($dbconfig->institution),
            ),
            'autocreateusers' => array(
                'type'  => 'switchbox',
                'title' => get_string('autocreateusers', 'module.lti'),
                'defaultvalue' => isset($dbconfig->autocreateusers) ? $dbconfig->autocreateusers : self::$default_config['autocreateusers'],
            ),
        );

        // Get the active auth instances for this institution that are not webservices
        if ($instances = get_records_sql_array("SELECT ai.* FROM {oauth_server_registry} osr
                                                JOIN {auth_instance} ai ON ai.institution = osr.institution
                                                WHERE osr.id = ? AND ai.active = 1 AND ai.authname != 'webservice'", array($serverid))) {
            $options = array('' => get_string('None', 'admin'));
            foreach ($instances as $instance) {
                $options[$instance->id] = get_string('title', 'auth.' . $instance->authname);
            }
            $elements['parentauth'] = array(
                'type' => 'select',
                'title' => get_string('parentauthforlti', 'module.lti'),
                'defaultvalue' => isset($dbconfig->parentauth) ? $dbconfig->parentauth : self::$default_config['parentauth'],
                'options' => $options,
                'help' => true,
            );
        }

        return $elements;

    }

    public static function save_oauth_service_config_options($serverid, $values) {
        $options = array('autocreateusers', 'parentauth');
        foreach ($options as $option) {
            $fordb = isset($values[$option]) ? $values[$option] : null;
            update_oauth_server_config($serverid, $option, $fordb);
        }
        return true;
    }

    // Disable form fields that are not needed by this plugin
    // @return array of fields not needed with key the field name
    public static function hide_webservice_fields() {
        $fields = array (
            'consumer_key_html',
            'consumer_secret',
            'application_uri',
            'callback_uri',
        );
        return $fields;
    }

    public static function create_new_app($values, $dbuser) {
            return array(
                'application_title' => $values['application'],
                'requester_name'    => $dbuser->firstname . ' ' . $dbuser->lastname,
                'requester_email'   => $dbuser->email,
                'institution'       => $values['institution'],
                'externalserviceid' => $values['service'],
            );
    }

    public static function get_app_values($values, $dbserver) {
        return array(
            'application_title' => $values['application_title'],
            'application_uri'   => '',
            'requester_name'    => $dbserver->requester_name,
            'requester_email'   => $dbserver->requester_email,
            'callback_uri'      => '',
            'institution'       => $values['institution'],
            'externalserviceid' => $values['service'],
            'consumer_key'      => $dbserver->consumer_key,
            'consumer_secret'   => $dbserver->consumer_secret,
            'id'                => $values['id'],
        );
    }

    public static function webservice_oauth_server_validate(Pieform $form, $values) {
        if (get_field('module_installed', 'active', 'name', 'lti_advantage')) {
            if (empty($values['display_name'])) {
                $form->set_error('display_name', get_string('display_namecannotbeempty', 'module.lti_advantage'));
            }
            // check the client_connections_institution is not related to a different issuer
            if (isset($values['id']) && $values['id']) {
                $registration = get_record('lti_advantage_registration', 'issuer', $values['issuer']);
                if ($registration && $registration->connectionid != $values['id']) {
                    $form->set_error('issuer', get_string('issueralreadyinuse', 'module.lti_advantage'));
                }
            }
            if (empty($values['deployment1_id']) && empty($values['deployment2_id'])) {
                $form->set_error('deployment1_id', get_string('deploymentidcannotbeempty', 'module.lti_advantage'));
                $form->set_error('deployment2_id', get_string('deploymentidcannotbeempty', 'module.lti_advantage'));
            }
            if (!empty($values['deployment1_id']) && !empty($values['deployment1_id']) &&
                $values['deployment1_id'] == $values['deployment2_id']) {
                $form->set_error('deployment1_id', get_string('deploymentidcannotbesame', 'module.lti_advantage'));
                $form->set_error('deployment2_id', get_string('deploymentidcannotbesame', 'module.lti_advantage'));
            }
        }
        else {
            $form->set_error(null, 'Module not active');
        }
        return $form;
    }

    /**
     * Create ssl certs.
     *
     * @return string The Private key.
     */
    private static function create_certificates() {
        // Custom options for openssl_pkey_new().
        $options = array(
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        );
        // Fetch the Private and Public keys.
        list($privatekey, $publickey) = PluginAuth::create_certificates(3650, null, $options);
        return $privatekey;
    }

    /**
     * Create the LTI Advantage keyset records.
     *
     * @return string Key set ID
     */
    private static function create_private_key() {
        $key_id = uniqid('', true);
        $key_set_id = uniqid('', true);
        $private_key = self::create_certificates();
        $alg = 'RSA256';

        $key_set = new stdClass();
        $key_set->id = $key_set_id;
        insert_record('lti_advantage_key_set', $key_set);

        $key = new stdClass();
        $key->id = $key_id;
        $key->key_set_id = $key_set_id;
        $key->private_key = $private_key;
        $key->alg = $alg;
        insert_record('lti_advantage_key', $key);

        return $key_set_id;
    }

    public static function webservice_oauth_server_submit(Pieform $form, $values) {
        $registration = get_record('lti_advantage_registration', 'connectionid', $values['id']);
        if (!$registration) {
            if (!$key_set_id = get_field('lti_advantage_key_set','id')) {
                $key_set_id = self::create_private_key();
            }
            $registration = new stdClass();
            $reg_id = uniqid('', true);
            $registration->id = $reg_id;
            $registration->display_name = $values['display_name'];
            $registration->issuer = $values['issuer'];
            $registration->client_id = $values['client_id'];
            $registration->platform_login_auth_endpoint = $values['platform_login_auth_endpoint'];
            $registration->platform_service_auth_endpoint = $values['platform_service_auth_endpoint'];
            $registration->platform_jwks_endpoint = $values['platform_jwks_endpoint'];
            if (isset($values['platform_auth_provider']) && $values['platform_auth_provider']) {
                $registration->platform_auth_provider = $values['platform_auth_provider'];
            }
            $registration->key_set_id = $key_set_id;
            $registration->connectionid = $values['id'];

            insert_record('lti_advantage_registration', $registration);

            $deployment = new stdClass();
            $deployment->registration_id = $reg_id;
            $deployment->customer_id = 'Brighspace';

            if (isset($values['deployment1_id']) && !empty($values['deployment1_id'])) {
                $deployment->deployment_id = $values['deployment1_id'];
                insert_record('lti_advantage_deployment', $deployment);
            }
            if (isset($values['deployment2_id']) && !empty($values['deployment2_id'])) {
                $deployment->deployment_id = $values['deployment2_id'];
                insert_record('lti_advantage_deployment', $deployment);
            }
            if (isset($values['deployment3_id']) && !empty($values['deployment3_id'])) {
                $deployment->deployment_id = $values['deployment3_id'];
                insert_record('lti_advantage_deployment', $deployment);
            }
        }
        else {
            $key = get_record('lti_advantage_key', 'key_set_id', $registration->key_set_id);
            if (!$key) {
                $key_set_id = self::create_private_key();
                $registration->key_set_id = $key_set_id;
            }
            $registration->issuer = $values['issuer'];
            $registration->display_name = $values['display_name'];
            $registration->client_id = $values['client_id'];
            $registration->platform_login_auth_endpoint = $values['platform_login_auth_endpoint'];
            $registration->platform_service_auth_endpoint = $values['platform_service_auth_endpoint'];
            $registration->platform_jwks_endpoint = $values['platform_jwks_endpoint'];
            if (isset($values['platform_auth_provider']) && $values['platform_auth_provider']) {
                $registration->platform_auth_provider = $values['platform_auth_provider'];
            }
            update_record('lti_advantage_registration', $registration);

            delete_records('lti_advantage_deployment', 'registration_id', $registration->id);
            $deployment = new stdClass();
            $deployment->registration_id = $registration->id;
            $deployment->customer_id = 'Brighspace';

            if (isset($values['deployment1_id']) && !empty($values['deployment1_id'])) {
                $deployment->deployment_id = $values['deployment1_id'];
                insert_record('lti_advantage_deployment', $deployment);
            }
            if (isset($values['deployment2_id']) && !empty($values['deployment2_id'])) {
                $deployment->deployment_id = $values['deployment2_id'];
                insert_record('lti_advantage_deployment', $deployment);
            }
            if (isset($values['deployment3_id']) && !empty($values['deployment3_id'])) {
                $deployment->deployment_id = $values['deployment3_id'];
                insert_record('lti_advantage_deployment', $deployment);
            }
        }
    }

    // delete the app
    public static function webservices_server_submit($form, $values) {
        $delete_key = false;
        if ($values['action'] == 'delete' && $registration = get_record('lti_advantage_registration', 'connectionid', $values['token'])) {

            // check if key is being used in any other registration
            $sql = "SELECT count(id) FROM {lti_advantage_registration} r
                WHERE r.key_set_id = ?
                AND r.id <> ?";
            if (count_records_sql($sql, array($registration->key_set_id, $registration->id)) == 0) {
                $delete_key = true;
            }

            $success = delete_records('lti_advantage_deployment', 'registration_id', $registration->id);
            $success = $success && delete_records('lti_advantage_registration', 'id', $registration->id);
            if ($delete_key) {
                $success = $success && delete_records('lti_advantage_key', 'key_set_id', $registration->key_set_id);
                $success = $success && delete_records('lti_advantage_key_set', 'id', $registration->key_set_id);
            }
            return $success;
        }
        return true;
    }

    public static function can_create_groups($rolescsv) {
        $roles = explode(',', $rolescsv);

        if (count(array_intersect($roles, self::$can_create_groups_role)) > 0) {
            return true;
        }
        return false;
    }

    public static function get_role($roles) {
        if ($roles) {
            foreach($roles as &$role) {
                $pos = strpos($role, "#");
                $role = substr($role, $pos+1);
            }
            foreach (self::$group_tutor_role as $tutorrole) {
                if (in_array($tutorrole, $roles)) {
                    return 'tutor';
                }
            }
            foreach (self::$can_create_groups_role as $adminrole) {
                if (in_array($adminrole, $roles)) {
                    return 'admin';
                }
            }
        }
        return 'member';
    }

    /**
     * Search for a user and create one if none found.
     *
     * @param array $params User data from the LTI request.
     * @param mixed $institution
     * @param mixed $authinstanceid
     * @param mixed $webserviceid
     *
     * @return object|false The found or created user object or false if there's not email on a new user.
     */
    public static function module_lti_advantage_ensure_user_exists($params, $institution, $authinstanceid, $webserviceid) {
        global $USER;
        // Check for user_id in auth_remote_user.
        $userid = get_field('auth_remote_user', 'localusr', 'authinstance', $authinstanceid, 'remoteusername', $params['user_id']);

        $updateremote = false;
        $updateuser = true;

        $remoteusername = false;

        // User not found - try to match on sub.
        if (!$userid && isset($params['sub'])) {
            // If a user was created on cron the user_id is of the form:
            // <GUID>_<USERID>
            // In other calls this value is stored in the "sub" parameter.
            $userid = get_field('auth_remote_user', 'localusr', 'authinstance', $authinstanceid, 'remoteusername', $params['sub']);
            $updateremote = true;
            $remoteusername = $params['sub'];
        }

        // User not found - try to match on ext username.
        if (!$userid && isset($params['ext_user_username'])) {
            $userid = get_field('auth_remote_user', 'localusr', 'authinstance', $authinstanceid, 'remoteusername', $params['ext_user_username']);
            $updateremote = true;
            $remoteusername = $params['ext_user_username'];
        }

        // User not found - try to match on email.
        if (!$userid && isset($params['email'])) {
            $userid = get_field_sql("SELECT DISTINCT owner
                                    FROM {artefact_internal_profile_email}
                                    WHERE LOWER(email) = ?
                                    AND verified = ?", array(strtolower($params['email']), 1));
            $updateremote = true;
            if (empty($remoteusername)) {
                $remoteusername = $params['email'];
            }
        }

        // Check user belongs to institution specified by OAuth key.
        if ($userid) {

            $is_site_admin = false;

            foreach (get_site_admins() as $site_admin) {
                if ($site_admin->id == $userid) {
                    $is_site_admin = true;
                    break;
                }
            }

            if (!$is_site_admin) {
                // Check user is member of configured OAuth institution.
                $institutions = array_keys(load_user_institutions($userid));
                if (empty($institutions)) {
                    // We check if they are in the 'mahara' institution.
                    $institutions = array('mahara');
                }

                if (!in_array($institution, $institutions)) {
                   // $USER->logout();
                }
            }
        }

        // Auto create user if auth allowed.
        $canautocreate = get_field('oauth_server_config', 'value', 'oauthserverregistryid', $webserviceid, 'field', 'autocreateusers');
        $parentauthid = get_field('oauth_server_config', 'value', 'oauthserverregistryid', $webserviceid, 'field', 'parentauth');

        if (!$userid) {
            if ($canautocreate) {

                // We need an e-mail address. Bail here if we don't have one.
                if (empty($params['email'])) {
                    log_debug('LTI Advantage: Unable to create ' . $params['family_name'] . ', ' . $params['given_name'] . ' (' . $params['user_id'] . ') due to missing email.');
                    return false;
                }

                // Create a user.
                $user = new stdClass();
                $user->email = $params['email'];
                $user->password = sha1(uniqid('', true));
                $user->firstname = $params['given_name'];
                $user->lastname = $params['family_name'];
                $user->authinstance = !empty($parentauthid) ? $parentauthid : $authinstanceid;
                $user->username = !empty($remoteusername) ? $remoteusername : $user->email;
                // Make sure that the username doesn't already exist.
                if (get_field_sql("SELECT username
                                FROM {usr}
                                WHERE LOWER(username) = ?", array(strtolower($user->username)))) {
                    $USER->logout();
                    throw new WebserviceInvalidParameterException(get_string('usernameexists2', 'module.lti', $user->username));
                }

                $userid = create_user($user, array(), $institution, true, $params['user_id']);

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
            if (strtolower($user->email) != strtolower($params['email'])) {
                $user->email = $params['email'];
            }
            $user->firstname = $params['given_name'];
            $user->lastname = $params['family_name'];
            $user->authinstance = !empty($parentauthid) ? $parentauthid : $authinstanceid;
            unset($user->password);

            $profilefields = new stdClass();
            $remoteuser = null;
            // We need to update the following fields for both the usr and
            // artefact tables.
            foreach (array('firstname', 'lastname', 'email') as $field) {
                if (isset($user->{$field})) {
                    $profilefields->{$field} = $user->{$field};
                }
            }
            update_user($user, $profilefields, $remoteuser);
        }

        if ($updateremote) {
            $authremoteuser = new stdClass();
            $authremoteuser->authinstance = $authinstanceid;
            $authremoteuser->remoteusername = $params['user_id'];
            $authremoteuser->localusr = $user->id;

            insert_record('auth_remote_user', $authremoteuser);
        }
        return $user;
    }

    public static function get_cron() {
        return array(
            (object)array(
                'callfunction' => 'lti_advantage_get_members_cron',
                'minute' => '0',
                'hour' => '4',
            ),
        );
    }


    /**
     * This functionruns everytime the cron calls it
     * and after an LTI roles and name provisioning is called
     * that will set the nextrun to null so it's forced to run in the next minute
     */
    public static function lti_advantage_get_members_cron() {
        if (self::is_active()) {
            log_debug('lti_advantage_get_members_cron');

            // get all group membership records with no state (cron call comes from LTI launch)
            $sql = "SELECT * FROM {lti_advantage_group_membership} WHERE state is null";
            if (!$memberships = get_records_sql_assoc($sql)) {
                // update doesn't come from LTI launch, then run all membership upgrades
                $memberships = get_records_assoc('lti_advantage_group_membership');
            }
            if ($memberships) {
                foreach ($memberships as $membership) {
                    // started working on this membership update
                    set_field('lti_advantage_group_membership', 'state', 'running', 'id', $membership->id);
                    try {
                        if (!$group = get_record('group', 'id', $membership->group_id, 'deleted', 0)) {
                            // group was deleted so delete cron record to that group
                            delete_records('lti_advantage_group_membership', 'id', $membership->id);
                            continue;
                        }
                        if ($registrationdb = get_record('lti_advantage_registration', 'id', $membership->registration_id)) {
                            $issuer = $registrationdb->issuer;
                            $registration = (new LTI_Advantage_Database())->find_registration_by_issuer($issuer);

                            $namesroleservice = array (
                                'context_memberships_url' => $membership->context_memberships_url,
                                'service_versions' => json_decode($membership->service_versions),
                            );

                            $service = new LTI\LTI_Names_Roles_Provisioning_Service(
                                    new LTI\LTI_Service_Connector($registration),
                                    $namesroleservice);

                            $group_members = get_column('group_member', 'member', 'group', $group->id);

                            if ($launch_users = $service->get_members()) {

                                $registry = get_record('oauth_server_registry', 'id', $registrationdb->connectionid);
                                $institution = $registry->institution;
                                $serviceid = $registry->id;
                                $authinstanceid = get_field('auth_instance', 'id', 'instancename', 'webservice', 'institution', $institution);

                                foreach ($launch_users as $launch_user) {
                                    $userobj = PluginModuleLti_advantage::module_lti_advantage_ensure_user_exists($launch_user, $institution, $authinstanceid, $serviceid);
                                    if ($userobj === false) {
                                        // This user likely have no email address.
                                        continue;
                                    }
                                    $role = PluginModuleLti_advantage::get_role($launch_user['roles']);

                                    $user = new User();
                                    $user->find_by_id($userobj->id);
                                    $groups = $user->get('grouproles');
                                    if (!isset($groups[$group->id])) {
                                        group_add_user($group->id, $userobj->id, $role);
                                    }

                                    unset($group_members[array_search($userobj->id, $group_members)]);
                                }
                                // if there are members on the group that are not on the membership list on the platform side
                                // remove them from the group
                                if ($group_members) {
                                    foreach ($group_members as $userid) {
                                        group_remove_user($group->id, $userid, true);
                                    }
                                }
                            }
                        }
                        set_field('lti_advantage_group_membership', 'state', 'success', 'id', $membership->id);
                    }
                    catch (Exception $e) {
                        set_field('lti_advantage_group_membership', 'state', 'failed', 'id', $membership->id);
                    }
                }
            }
        }
    }

}
