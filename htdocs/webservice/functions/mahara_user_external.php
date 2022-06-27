<?php
/**
 *
 * @package    mahara
 * @subpackage auth-webservice
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * External user API
 *
 * @package    auth
 * @subpackage webservice
 * @copyright  2009 Moodle Pty Ltd (http://moodle.com)
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL v3 or later
 * @author     Piers Harding
 */

require_once(get_config('docroot') . 'webservice/lib.php');
require_once(get_config('docroot') . 'webservice/rest/locallib.php');
require_once(get_config('docroot') . 'lib/user.php');
require_once(get_config('docroot') . 'lib/group.php');
require_once(get_config('docroot') . 'lib/institution.php');
require_once(get_config('docroot') . 'lib/searchlib.php');

global $WEBSERVICE_OAUTH_USER;

/**
* Class container for core Mahara user related API calls
*/
class mahara_user_external extends external_api {

    private static $ALLOWEDKEYS;
    private static $localoptions;

    private static function get_allowed_keys() {
        if (is_array(self::$ALLOWEDKEYS)) {
            return self::$ALLOWEDKEYS;
        }
        $types = array(
            'remoteuser',
            'introduction',
            'officialwebsite',
            'personalwebsite',
            'blogaddress',
            'address',
            'town',
            'city',
            'country',
            'homenumber',
            'businessnumber',
            'mobilenumber',
            'faxnumber',
            'socialprofile',
            'occupation',
            'industry',
        );
        $localtypes = self::get_local_options();
        if (is_array($localtypes)) {
            $types = array_merge($types, array_keys($localtypes));
        }
        self::$ALLOWEDKEYS = $types;
        return self::$ALLOWEDKEYS;
    }

    private static function get_local_options() {
        if (is_array(self::$localoptions)) {
            return self::$localoptions;
        }
        if (file_exists(get_config('docroot') . 'local/lib/artefact_internal.php')) {
            safe_require('artefact', 'internal');
            include_once(get_config('docroot') . 'local/lib/artefact_internal.php');
        }
        if (class_exists('PluginArtefactInternalLocal', false)) {
            $localtypes = PluginArtefactInternalLocal::get_webservice_options();
            self::$localoptions = $localtypes;
        }
        return self::$localoptions;
    }

    /**
     * parameter definition for input of delete_users method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function autologin_redirect_parameters() {
       return new external_function_parameters(
                        array(
                            'context_id'        => new external_value(PARAM_RAW, get_string('context_id', WEBSERVICE_LANG), VALUE_OPTIONAL),
                            'context_label'        => new external_value(PARAM_RAW,  get_string('context_label', WEBSERVICE_LANG), VALUE_OPTIONAL),
                            'context_title'        => new external_value(PARAM_RAW, get_string('context_title', WEBSERVICE_LANG), VALUE_OPTIONAL),
                            'context_type'        => new external_value(PARAM_RAW, get_string('context_type', WEBSERVICE_LANG), VALUE_OPTIONAL),
                            'ext_lms'        => new external_value(PARAM_RAW, get_string('ext_lms', WEBSERVICE_LANG), VALUE_OPTIONAL),
                            'ext_user_username'        => new external_value(PARAM_RAW, get_string('ext_user_username', WEBSERVICE_LANG), VALUE_OPTIONAL),
                            'launch_presentation_locale'        => new external_value(PARAM_RAW, get_string('launch_presentation_locale', WEBSERVICE_LANG), VALUE_OPTIONAL),
                            'launch_presentation_return_url'        => new external_value(PARAM_RAW, get_string('launch_presentation_return_url', WEBSERVICE_LANG), VALUE_OPTIONAL),
                            'lis_person_contact_email_primary'        => new external_value(PARAM_RAW, get_string('lis_person_contact_email_primary', WEBSERVICE_LANG), VALUE_OPTIONAL),
                            'lis_person_name_family'        => new external_value(PARAM_RAW, get_string('lis_person_name_family', WEBSERVICE_LANG), VALUE_OPTIONAL),
                            'lis_person_name_full'        => new external_value(PARAM_RAW, get_string('lis_person_name_full', WEBSERVICE_LANG), VALUE_OPTIONAL),
                            'lis_person_name_given'        => new external_value(PARAM_RAW, get_string('lis_person_name_given', WEBSERVICE_LANG), VALUE_OPTIONAL),
                            'lis_person_sourcedid'        => new external_value(PARAM_RAW, get_string('lis_person_sourcedid', WEBSERVICE_LANG), VALUE_OPTIONAL),
                            'lti_message_type'        => new external_value(PARAM_RAW, get_string('lti_message_type', WEBSERVICE_LANG), VALUE_OPTIONAL),
                            'lti_version'        => new external_value(PARAM_RAW, get_string('lti_version', WEBSERVICE_LANG), VALUE_OPTIONAL),
                            'resource_link_description'        => new external_value(PARAM_RAW, get_string('resource_link_description', WEBSERVICE_LANG), VALUE_OPTIONAL),
                            'resource_link_id'        => new external_value(PARAM_RAW, get_string('resource_link_id', WEBSERVICE_LANG), VALUE_OPTIONAL),
                            'resource_link_title'        => new external_value(PARAM_RAW, get_string('resource_link_title', WEBSERVICE_LANG), VALUE_OPTIONAL),
                            'roles'        => new external_value(PARAM_RAW, get_string('roles', WEBSERVICE_LANG), VALUE_OPTIONAL),
                            'tool_consumer_info_product_family_code' => new external_value(PARAM_RAW, get_string('tool_consumer_info_product_family_code', WEBSERVICE_LANG), VALUE_OPTIONAL),
                            'tool_consumer_info_version'        => new external_value(PARAM_RAW, get_string('tool_consumer_info_version', WEBSERVICE_LANG), VALUE_OPTIONAL),
                            'tool_consumer_instance_guid'        => new external_value(PARAM_RAW, get_string('tool_consumer_instance_guid', WEBSERVICE_LANG), VALUE_OPTIONAL),
                            'tool_consumer_instance_name'        => new external_value(PARAM_RAW, get_string('tool_consumer_instance_name', WEBSERVICE_LANG), VALUE_OPTIONAL),
                            'user_id'        => new external_value(PARAM_RAW, get_string('user_id', WEBSERVICE_LANG), VALUE_OPTIONAL),
                        )
            );
    }


    /**
     * Delete one or more users
     *
     * @param array $params
     */
    public static function autologin_redirect($params) {
        global $USER, $WEBSERVICE_INSTITUTION, $WEBSERVICE_OAUTH_USER;

        require_once(get_config('docroot') . 'artefact/lib.php');

        $keys = array_keys(self::autologin_redirect_parameters()->keys);
        $params = array_combine($keys, func_get_args());

        log_debug('in autologin_redirect: '.var_export($params, true));
        $user_field = (get_config('autologin_redirect_username_field') ? get_config('autologin_redirect_username_field') : 'username');

        log_debug('in autologin_redirect: user field: '.$user_field);
        if (in_array($user_field, array('username', 'email'))) {
            $user = get_record('usr', $user_field, $params['ext_user_username'], 'deleted', 0);
            log_debug('in autologin_redirect: user by field: '.var_export($user, true));
        }
        else if ($user_field == 'studentid') {
            // now find the user by institution studentid
            $user_id = get_field('usr_institution', 'usr', 'studentid', $params['ext_user_username'], 'institution', $WEBSERVICE_INSTITUTION);
            log_debug('in autologin_redirect: usr_institution id: '.var_export($user_id, true));
            if ($user_id) {
                $user = get_record('usr', 'id', $user_id, 'deleted', 0);
                log_debug('in autologin_redirect: user by usr_institution: '.var_export($user, true));
            }
        }
        else {
            // must be a remote user field
            $user = null;
            $auths = explode(',', $user_field);
            foreach ($auths as $auth) {
                list($institution, $authtype) = explode(':', $auth);
                // only institutions for the web service user token
                if ($WEBSERVICE_INSTITUTION == $institution) {
                    // now find the user by remote
                    $instance_id = get_field('auth_instance', 'id', 'instancename', $authtype, 'institution', $WEBSERVICE_INSTITUTION, 'active', 1);
                    log_debug('in autologin_redirect: auth_instance id: '.$instance_id);
                    if ($instance_id) {
                        $user_id = get_field('auth_remote_user', 'localusr', 'remoteusername', $params['ext_user_username'], 'authinstance', $instance_id);
                        log_debug('in autologin_redirect: auth_remote_user id: '.$user_id);
                        if ($user_id) {
                            $user = get_record('usr', 'id', $user_id, 'deleted', 0);
                            log_debug('in autologin_redirect: user by auth_remote_user: '.var_export($user, true));
                        }
                    }
                }
            }

        }
        if (empty($user) || empty($user->id) || $user->id < 1) {
            // logout
            log_debug('cant find user - logout');
            $USER->logout();
        }
        else {
            log_debug('reanimating: '.var_export($user->username, true));
            $USER->reanimate($user->id, $user->authinstance);
        }

        if (empty($params['resource_link_id'])) {
            log_debug('no resource_link_id - now jumping to: ' . get_config('wwwroot'));
            redirect(get_config('wwwroot'));
        }
        else {
            log_debug('now jumping to: ' . $params['resource_link_id']);
            redirect($params['resource_link_id']);
        }

        // should not get here
        die();
    }

   /**
    * parameter definition for output of autologin_redirect method
    */
    public static function autologin_redirect_returns() {
        return null;
    }

    /**
     * parameter definition for input of create_users method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function create_users_parameters() {

        $options = array(
            'username'        => new external_value(PARAM_RAW, get_string('usernamevalid1', WEBSERVICE_LANG)),
            'password'        => new external_value(PARAM_RAW,get_string('passwordvalid', WEBSERVICE_LANG) ),
            'firstname'       => new external_value(PARAM_NOTAGS, get_string('firstname', WEBSERVICE_LANG)),
            'lastname'        => new external_value(PARAM_NOTAGS,get_string('lastname', WEBSERVICE_LANG) ),
            'email'           => new external_value(PARAM_EMAIL, get_string('emailvalid', WEBSERVICE_LANG)),
            'institution'     => new external_value(PARAM_SAFEDIR, get_string('institution', WEBSERVICE_LANG), VALUE_DEFAULT, get_string('mahara', WEBSERVICE_LANG), NULL_NOT_ALLOWED),
            'auth'            => new external_value(PARAM_SAFEDIR,get_string('authplugins', WEBSERVICE_LANG) , VALUE_DEFAULT, get_string('internal', WEBSERVICE_LANG), NULL_NOT_ALLOWED),
            'quota'           => new external_value(PARAM_INTEGER, get_string('storagequota', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'forcepasswordchange' => new external_value(PARAM_BOOL,get_string('forcepasswordchange', WEBSERVICE_LANG) , VALUE_DEFAULT, '0'),
            'studentid'       => new external_value(PARAM_RAW, get_string('studentid', WEBSERVICE_LANG), VALUE_DEFAULT, ''),
            'remoteuser'      => new external_value(PARAM_RAW, get_string('remoteuserid', WEBSERVICE_LANG), VALUE_DEFAULT, ''),
            'preferredname'   => new external_value(PARAM_TEXT, get_string('preferredname', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'address'         => new external_value(PARAM_RAW, get_string('streetaddress', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'town'            => new external_value(PARAM_NOTAGS, get_string('town', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'city'            => new external_value(PARAM_NOTAGS, get_string('city', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'country'         => new external_value(PARAM_ALPHA, get_string('country', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'homenumber'      => new external_value(PARAM_RAW,get_string('homenumber', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'businessnumber'  => new external_value(PARAM_RAW,get_string('businessnumber', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'mobilenumber'    => new external_value(PARAM_RAW, get_string('mobilenumber', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'faxnumber'       => new external_value(PARAM_RAW, get_string('faxnumber', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'introduction'    => new external_value(PARAM_RAW, get_string('introduction', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'officialwebsite' => new external_value(PARAM_RAW, get_string('officialwebsite', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'personalwebsite' => new external_value(PARAM_RAW, get_string('personalwebsite', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'blogaddress'     => new external_value(PARAM_RAW,get_string('blogaddress', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'socialprofile'   => new external_value(PARAM_RAW, get_string('socialprofilevalid', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'occupation'      => new external_value(PARAM_TEXT,get_string('occupation', WEBSERVICE_LANG) , VALUE_OPTIONAL),
            'industry'        => new external_value(PARAM_TEXT, get_string('industry', WEBSERVICE_LANG), VALUE_OPTIONAL),
        );
        $localoptions = self::get_local_options();
        if (is_array($localoptions)) {
            $options = array_merge($options, $localoptions);
        }
        return new external_function_parameters(
            array(
                'users' => new external_multiple_structure(
                    new external_single_structure($options)
                )
            )
        );
    }

    /**
     * Create one or more users in the authorised institution
     *
     * @param array $users  An array of users to create.
     * @return array An array of arrays describing users
     */
    public static function create_users($users) {
        global $USER, $WEBSERVICE_INSTITUTION, $WEBSERVICE_OAUTH_USER;

        // we need to turn the social profile information into a single string to pass validate_paramaters()
        foreach ($users as $key => $user) {
            if (isset($user['socialprofile']) && is_array($user['socialprofile'])) {
                $users[$key]['socialprofile'] = (!empty($user['socialprofile']['profiletype']) ? $user['socialprofile']['profiletype'] : '') . '|' . (!empty($user['socialprofile']['profileurl']) ? $user['socialprofile']['profileurl'] : '');
            }
        }
        // Do basic automatic PARAM checks on incoming data, using params description
        // If any problems are found then exceptions are thrown with helpful error messages

        $params = self::validate_parameters(self::create_users_parameters(), array('users'=>$users));
        db_begin();
        $userids = array();
        foreach ($params['users'] as $user) {
            // Make sure that the username doesn't already exist
            if (get_record('usr', 'username', $user['username'])) {
                throw new WebserviceInvalidParameterException(get_string('usernameexists2', 'auth.webservice', $user['username']));
            }

            // check the institution is allowed
            // basic check authorisation to edit for the current institution
            if (!$USER->can_edit_institution($user['institution'])) {
                throw new WebserviceInvalidParameterException('create_users | ' . get_string('accessdeniedforinst', 'auth.webservice', $user['institution']));
            }

            // Make sure auth is valid
            if (!$authinstance = get_record('auth_instance', 'institution', $user['institution'], 'authname', $user['auth'])) {
                throw new WebserviceInvalidParameterException(get_string('invalidauthtype', 'auth.webservice', $user['institution'] . '/' . $user['auth']));
            }

            // Make sure socialprofiletype and socialprofileurl are set
            $socialprofileparts = array();
            if (!empty($user['socialprofile']) && $user['socialprofile'] != '|') {
                $parts = $socialprofileparts = explode('|', $user['socialprofile']);
                if ((!empty($parts[0]) && empty($parts[1])) || (empty($parts[0]) && !empty($parts[1]))) {
                    throw new WebserviceInvalidParameterException(get_string('invalidsocialprofile', 'auth.webservice', $parts[0] . ' | ' . $parts[1]));
                }
                $user['socialprofile'] = array('socialprofile_profiletype' =>  $parts[0], 'socialprofile_profileurl' => $parts[1]);
            }
            else {
                unset($user['socialprofile']);
            }

            $institution = new Institution($authinstance->institution);

            $maxusers = $institution->maxuseraccounts;
            if (!empty($maxusers)) {
                $members = count_records_sql('
                    SELECT COUNT(*) FROM {usr} u INNER JOIN {usr_institution} i ON u.id = i.usr
                    WHERE i.institution = ? AND u.deleted = ?', array($institution->name, 0));
                if ($members + 1 > $maxusers) {
                    throw new WebserviceInvalidParameterException(get_string('instexceedmax', 'auth.webservice', $institution->name));
                }
            }

            // build up the user object to create
            $new_user = new stdClass();
            $new_user->authinstance = $authinstance->id;
            $new_user->username     = $user['username'];
            $new_user->firstname    = $user['firstname'];
            $new_user->lastname     = $user['lastname'];
            $new_user->password     = $user['password'];
            $new_user->email        = $user['email'];
            if (isset($user['quota'])) {
                $new_user->quota        = $user['quota'];
            }
            if (isset($user['forcepasswordchange'])) {
                $new_user->passwordchange = (int)$user['forcepasswordchange'];
            }

            // handle profile fields
            $profilefields = new stdClass();
            $remoteuser = null;
            foreach (self::get_allowed_keys() as $field) {
                if (isset($user[$field])) {
                    if ($field == 'remoteuser') {
                        $remoteuser = $user[$field];
                        continue;
                    }
                    $profilefields->{$field} = $user[$field];
                }
            }
            // The student id and preferredname get saved as an artefact and to usr table
            if (isset($user['studentid'])) {
                $new_user->studentid = $user['studentid'];
                $profilefields->studentid = $user['studentid'];
            }
            if (isset($user['preferredname'])) {
                $new_user->preferredname = $user['preferredname'];
                $profilefields->preferredname = $user['preferredname'];
            }

            $new_user->id = create_user($new_user, $profilefields, $institution, $authinstance, $remoteuser);
            $addedusers[] = $new_user;
            $userids[] = array('id'=> $new_user->id, 'username'=>$user['username']);
        }
        db_commit();

        return $userids;
    }

   /**
    * parameter definition for output of create_users method
    *
    * Returns description of method result value
    * @return external_description
    */
    public static function create_users_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id'       => new external_value(PARAM_INT, get_string('userid', WEBSERVICE_LANG)),
                    'username' => new external_value(PARAM_RAW, get_string('username', WEBSERVICE_LANG)),
                )
            )
        );
    }

    /**
     * parameter definition for input of delete_users method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function delete_users_parameters() {
       return new external_function_parameters(
            array(
                'users' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id'              => new external_value(PARAM_NUMBER, get_string('deleteuserid', WEBSERVICE_LANG), VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                            'username'        => new external_value(PARAM_RAW, get_string('deleteusername', WEBSERVICE_LANG), VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                            )
                        )
                    )
                )
            );
    }

    /**
     * Delete one or more users
     *
     * @param array $users
     */
    public static function delete_users($users) {
        global $USER, $WEBSERVICE_INSTITUTION, $WEBSERVICE_OAUTH_USER;

        require_once(get_config('docroot') . 'artefact/lib.php');

        $params = self::validate_parameters(self::delete_users_parameters(), array('users'=>$users));

        $users = array();
        foreach ($params['users'] as $user) {
            $users[]= self::checkuser($user);
        }

        db_begin();
        foreach ($users as $user) {
            // Make sure auth is valid
            if (!$authinstance = get_record('auth_instance', 'id', $user->authinstance)) {
                throw new WebserviceInvalidParameterException(get_string('invalidauthtype', 'auth.webservice', $user->authinstance));
            }
            // check the institution is allowed
            // basic check authorisation to edit for the current institution
            if (!$USER->can_edit_institution($authinstance->institution)) {
                throw new WebserviceInvalidParameterException('delete_users | ' . get_string('accessdeniedforinstuser', 'auth.webservice', $authinstance->institution, $user->id));
            }

            if ($USER->get('id') == $user->id) {
                throw new WebserviceInvalidParameterException('delete_users | ' . get_string('unabletodeleteself1', 'admin'));
            }

            // only allow deletion of users that have not signed in
            if (!empty($user->lastlogin) && !$user->suspendedcusr) {
                throw new WebserviceInvalidParameterException('delete_users | ' . get_string('cannotdeleteaccount', 'auth.webservice', $user->id));
            }

            // must not allow deleting of admins or self!!!
            if ($user->admin) {
                throw new WebserviceInvalidParameterException('delete_users | ' . get_string('unabletodeleteadmin', 'auth.webservice', $user->id));
            }

            delete_user($user->id);
        }
        db_commit();

        return null;
    }

   /**
    * parameter definition for output of delete_users method
    */
    public static function delete_users_returns() {
        return null;
    }

    /**
     * parameter definition for input of update_users method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function update_users_parameters() {

        $options = array(
            'id'              => new external_value(PARAM_NUMBER, get_string('userid', WEBSERVICE_LANG), VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
            'username'        => new external_value(PARAM_RAW, get_string('username', WEBSERVICE_LANG), VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
            'password'        => new external_value(PARAM_RAW, get_string('plaintxtpassword', WEBSERVICE_LANG) , VALUE_OPTIONAL),
            'firstname'       => new external_value(PARAM_NOTAGS, get_string('firstname', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'lastname'        => new external_value(PARAM_NOTAGS, get_string('lastname', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'email'           => new external_value(PARAM_EMAIL, get_string('emailvalid', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'institution'     => new external_value(PARAM_TEXT, get_string('institutiont', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'auth'            => new external_value(PARAM_TEXT, get_string('authplugins', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'quota'           => new external_value(PARAM_INTEGER, get_string('storagequota', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'forcepasswordchange' => new external_value(PARAM_BOOL, get_string('forcepasswordchange', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'studentid'       => new external_value(PARAM_RAW, get_string('studentid', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'remoteuser'      => new external_value(PARAM_RAW, get_string('remoteuserid', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'preferredname'   => new external_value(PARAM_TEXT, get_string('preferredname', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'address'         => new external_value(PARAM_RAW, get_string('address', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'town'            => new external_value(PARAM_NOTAGS, get_string('town', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'city'            => new external_value(PARAM_NOTAGS, get_string('city', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'country'         => new external_value(PARAM_ALPHA, get_string('country', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'homenumber'      => new external_value(PARAM_RAW, get_string('homenumber', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'businessnumber'  => new external_value(PARAM_RAW, get_string('businessnumber', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'mobilenumber'    => new external_value(PARAM_RAW, get_string('mobilenumber', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'faxnumber'       => new external_value(PARAM_RAW, get_string('faxnumber', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'introduction'    => new external_value(PARAM_RAW, get_string('introduction', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'officialwebsite' => new external_value(PARAM_RAW, get_string('officialwebsite', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'personalwebsite' => new external_value(PARAM_RAW, get_string('personalwebsite', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'blogaddress'     => new external_value(PARAM_RAW, get_string('blogaddress', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'socialprofile'   => new external_value(PARAM_RAW, get_string('socialprofile', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'occupation'      => new external_value(PARAM_TEXT, get_string('occupation', WEBSERVICE_LANG), VALUE_OPTIONAL),
            'industry'        => new external_value(PARAM_TEXT, get_string('industry', WEBSERVICE_LANG), VALUE_OPTIONAL),
        );
        $localoptions = self::get_local_options();
        if (is_array($localoptions)) {
            $options = array_merge($options, $localoptions);
        }
        return new external_function_parameters(
            array(
                'users' => new external_multiple_structure(
                    new external_single_structure($options)
                )
            )
        );
    }

    /**
     * update one or more users
     *
     * @param array $users
     */
    public static function update_users($users) {
        global $USER, $WEBSERVICE_INSTITUTION, $WEBSERVICE_OAUTH_USER;

        // we need to turn the social profile information into a single string to pass validate_paramaters()
        foreach ($users as $key => $user) {
            if (isset($user['socialprofile']) && is_array($user['socialprofile'])) {
                $users[$key]['socialprofile'] = (!empty($user['socialprofile']['profiletype']) ? $user['socialprofile']['profiletype'] : '') . '|' . (!empty($user['socialprofile']['profileurl']) ? $user['socialprofile']['profileurl'] : '');
            }
        }

        $params = self::validate_parameters(self::update_users_parameters(), array('users' => $users));

        db_begin();
        foreach ($params['users'] as $user) {
            if (!empty($user['id'])) {
                $dbuser = get_record('usr', 'id', $user['id'], 'deleted', 0);
            }
            else if (!empty($user['username'])) {
                $dbuser = get_record('usr', 'username', $user['username'], 'deleted', 0);
            }
            else {
                throw new WebserviceInvalidParameterException('update_users | ' . get_string('nousernameorid', 'auth.webservice'));
            }
            if (empty($dbuser)) {
                throw new WebserviceInvalidParameterException('update_users | ' . get_string('invaliduser', 'auth.webservice', $user['id'] . '/' . $user['username']));
            }

            // Make sure auth is valid
            if (!$authinstance = get_record('auth_instance', 'id', $dbuser->authinstance)) {
                throw new WebserviceInvalidParameterException(get_string('invalidauthtype', 'auth.webservice', $dbuser->authinstance));
            }
            // check for changed authinstance
            if (isset($user['auth']) && isset($user['institution'])) {
                $ai = get_record('auth_instance', 'institution', $user['institution'], 'authname', $user['auth']);
                if (empty($ai)) {
                    throw new WebserviceInvalidParameterException('update_users | ' . get_string('invalidauthtypeuser', 'auth.webservice', $user['auth'], $dbuser->id));
                }
                $authinstance = $ai;
            }
            else if (isset($user['auth'])) {
                throw new WebserviceInvalidParameterException('update_users | ' . get_string('mustsetauth', 'auth.webservice', $dbuser->id));
            }

            // check the institution is allowed
            // basic check authorisation to edit for the current institution
            if (!$USER->can_edit_institution($authinstance->institution)) {
                throw new WebserviceInvalidParameterException('update_users | ' . get_string('accessdeniedforinstuser', 'auth.webservice', $authinstance->institution, $dbuser->id));
            }

            $updated_user = $dbuser;
            $updated_user->authinstance = $authinstance->id;
            $updated_user->password = (!empty($user['password']) ? $user['password'] : '');
            foreach (array('username', 'firstname', 'lastname', 'email', 'quota', 'studentid', 'preferredname') as $field) {
                if (isset($user[$field])) {
                    $updated_user->{$field} = $user[$field];
                }
            }
            if (isset($user['forcepasswordchange'])) {
                $updated_user->passwordchange = (int)$user['forcepasswordchange'];
            }

            // Make sure socialprofiletype and socialprofileurl are set
            $socialprofileparts = array();
            if (!empty($user['socialprofile']) && $user['socialprofile'] != '|') {
                $parts = $socialprofileparts = explode('|', $user['socialprofile']);
                if ((!empty($parts[0]) && empty($parts[1])) || (empty($parts[0]) && !empty($parts[1]))) {
                    throw new WebserviceInvalidParameterException(get_string('invalidsocialprofile', 'auth.webservice', $parts[0] . ' | ' . $parts[1]));
                }
                $user['socialprofile'] = array('socialprofile_profiletype' =>  $parts[0], 'socialprofile_profileurl' => $parts[1]);
            }
            else {
                unset($user['socialprofile']);
            }

            $profilefields = new stdClass();
            $remoteuser = null;
            foreach (self::get_allowed_keys() as $field) {
                if (isset($user[$field])) {
                    if ($field == 'remoteuser') {
                        $remoteuser = $user[$field];
                        continue;
                    }
                    $profilefields->{$field} = $user[$field];
                }
            }
            // We need to update the following fields for both the usr and artefact tables
            foreach (array('firstname', 'lastname', 'email', 'studentid', 'preferredname') as $field) {
                if (isset($user[$field])) {
                    $profilefields->{$field} = $user[$field];
                }
            }

            update_user($updated_user, $profilefields, $remoteuser);
        }
        db_commit();

        return null;
    }

   /**
    * parameter definition for output of update_users method
    */
    public static function update_users_returns() {
        return null;
    }

    /**
     * parameter definition for input of get_users_by_id method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_users_by_id_parameters() {
       return new external_function_parameters(
            array(
                'users' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id'              => new external_value(PARAM_NUMBER, get_string('userid', WEBSERVICE_LANG), VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                            'username'        => new external_value(PARAM_RAW, get_string('username', WEBSERVICE_LANG), VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                            'remoteuser'      => new external_value(PARAM_RAW, get_string('remoteuser', WEBSERVICE_LANG), VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                            'email'           => new external_value(PARAM_RAW, get_string('emailaddress', WEBSERVICE_LANG), VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                            )
                        )
                    )
                )
            );
    }

    /**
     * Check that a user exists
     *
     * Check a user exists by looking up the user by id, userid, username,
     * email, or remoteuser and return the user object.
     *
     * @param array $user A user array to check
     * @return object The user
     * @throws WebserviceInvalidParameterException
     */
    private static function checkuser($user) {
        global $WEBSERVICE_INSTITUTION;

        if (isset($user['id'])) {
            $id = $user['id'];
        }
        else if (isset($user['userid'])) {
            $id = $user['userid'];
        }
        else if (isset($user['username'])) {
            $username = strtolower($user['username']);
            $sql = 'SELECT * FROM {usr} WHERE LOWER(username) = ?';
            $dbuser = get_record_sql($sql, array($username));
            if (empty($dbuser)) {
                throw new WebserviceInvalidParameterException(get_string('invalidusername', 'auth.webservice', $user['username']));
            }
            $id = $dbuser->id;
        }
        else if (isset($user['email'])) {
            $email = strtolower($user['email']);
            $sql = 'SELECT * FROM {usr} WHERE LOWER(email) = ?';
            $dbuser = get_record_sql($sql, array($email), 0);
            if (empty($dbuser)) {
                throw new WebserviceInvalidParameterException(get_string('invalidusername', 'auth.webservice', $user['email']));
            }
            $id = $dbuser->id;
        }
        else if (isset($user['remoteuser'])) {
            $dbinstances = get_records_array('auth_instance', 'institution', $WEBSERVICE_INSTITUTION);
            $dbuser = false;
            foreach ($dbinstances as $dbinstance) {
               $user_factory = new User;
               $remote_user = strtolower($user['remoteuser']);
               $dbuser = $user_factory->find_by_instanceid_username($dbinstance->id, $remote_user, true);
               if ($dbuser) {
                   break;
               }
            }
            if (empty($dbuser)) {
                throw new WebserviceInvalidParameterException(get_string('invalidremoteusername', 'auth.webservice', $user['username']));
            }
            $id = $dbuser->id;
        }
        else {
            throw new WebserviceInvalidParameterException(get_string('musthaveid', 'auth.webservice'));
        }
        // now get the user
        if ($user = get_user($id)) {
            if ($user->deleted) {
                throw new WebserviceInvalidParameterException(get_string('invaliduserid', 'auth.webservice', $id));
            }
            // get the remoteuser
            $user->remoteuser = get_field('auth_remote_user', 'remoteusername', 'authinstance', $user->authinstance, 'localusr', $user->id);
            $options = self::get_allowed_keys();
            foreach ($options as $attr) {
                if ($art = get_record('artefact', 'artefacttype', $attr, 'owner', $user->id)) {
                    $user->{$attr} = $art->title;
                }
            }
            return $user;
        }
        else {
            throw new WebserviceInvalidParameterException(get_string('invaliduserid', 'auth.webservice', $id));
        }
    }

    /**
     * Get user information for one or more users
     *
     * @param array $users  array of users
     * @return array An array of arrays describing users
     */
    public static function get_users_by_id($users) {

        $params = self::validate_parameters(self::get_users_by_id_parameters(),
            array('users' => $users));

        if (empty($params['users'])) {
            list($count, $params) = self::_get_bulk_users($params);
        }
        $results = self::_get_users_by_id($params);
        return $results;
    }

    private static function _get_bulk_users($params) {
        global $WEBSERVICE_INSTITUTION, $WEBSERVICE_OAUTH_USER, $USER;
        $sortdirection = (isset($params['sortdir']) && strtolower($params['sortdir']) == 'desc') ? 'desc' : 'asc';
        $offset = isset($params['offset']) ? $params['offset'] : null;
        $limit = isset($params['limit']) ? $params['limit'] : null;
        $params['users'] = array();
        $fromsql = " FROM {usr} u
                     INNER JOIN {auth_instance} ai ON u.authinstance = ai.id
                     LEFT JOIN {usr_institution} ui ON u.id = ui.usr AND ui.institution = ?
                     WHERE u.deleted = ? AND u.id != 0
                     AND (ai.institution = ? OR ui.institution = ?)";
        $countusers = count_records_sql("SELECT COUNT(*) " . $fromsql, array($WEBSERVICE_INSTITUTION, 0, $WEBSERVICE_INSTITUTION, $WEBSERVICE_INSTITUTION));
        $dbusers = get_records_sql_array("SELECT u.id AS id " . $fromsql . " ORDER BY u.id " . $sortdirection, array($WEBSERVICE_INSTITUTION, 0, $WEBSERVICE_INSTITUTION, $WEBSERVICE_INSTITUTION), $offset, $limit);
        if ($dbusers) {
            foreach ($dbusers as $dbuser) {
                // eliminate bad uid
                if ($dbuser->id == 0) {
                    continue;
                }
                $params['users'][] = array('id' => $dbuser->id);
            }
        }
        return array($countusers, $params);
    }

    private static function _get_users_by_id($params) {
        global $WEBSERVICE_INSTITUTION, $WEBSERVICE_OAUTH_USER, $USER;

        $users = array();
        foreach ($params['users'] as $user) {
            $users[]= self::checkuser($user);
        }

        $result = array();
        foreach ($users as $user) {
            if (empty($user->deleted)) {
                // check the institution
                if (!mahara_external_in_institution($user, $WEBSERVICE_INSTITUTION)) {
                    throw new WebserviceInvalidParameterException(get_string('notauthforuseridinstitution', 'auth.webservice', $user->id, $WEBSERVICE_INSTITUTION));
                }

                $auth_instance = get_record('auth_instance', 'id', $user->authinstance);

                $userarray = array();
                // we want to return an array not an object
                // now we transfer all profile_field_xxx into the customfields
                // external_multiple_structure required by description
                $userarray['id'] = $user->id;
                $userarray['username'] = $user->username;
                $userarray['firstname'] = $user->firstname;
                $userarray['lastname'] = $user->lastname;
                $userarray['email'] = $user->email;
                $userarray['auth'] = $auth_instance->authname;
                $userarray['studentid'] = $user->studentid;
                $userarray['preferredname'] = $user->preferredname;
                foreach (self::get_allowed_keys() as $field) {
                    $userarray[$field] = ((isset($user->{$field}) && $user->{$field}) ? $user->{$field} : '');
                }
                $userarray['institution'] = $auth_instance->institution;
                $userarray['auths'] = array();
                $auths = get_records_sql_array('SELECT aru.remoteusername AS remoteusername, ai.authname AS authname FROM {auth_remote_user} aru
                                                  INNER JOIN {auth_instance} ai ON aru.authinstance = ai.id
                                                  WHERE ai.institution = ? AND aru.localusr = ?', array($WEBSERVICE_INSTITUTION, $user->id));
                if ($auths) {
                    foreach ($auths as $auth) {
                        $userarray['auths'][]= array('auth' => $auth->authname, 'remoteuser' => $auth->remoteusername);
                    }
                }
                $result[] = $userarray;
            }
        }

        return $result;
    }

    /**
     * parameter definition for output of get_users_by_id method
     *
     * Returns description of method result value
     * @return external_description
     */
    public static function get_users_by_id_returns() {

        $options = array(
            'id'              => new external_value(PARAM_NUMBER, get_string('userid', WEBSERVICE_LANG)),
            'username'        => new external_value(PARAM_RAW, get_string('username', WEBSERVICE_LANG)),
            'firstname'       => new external_value(PARAM_NOTAGS, get_string('firstname', WEBSERVICE_LANG)),
            'lastname'        => new external_value(PARAM_NOTAGS, get_string('lastname', WEBSERVICE_LANG)),
            'email'           => new external_value(PARAM_TEXT, get_string('emailaddress', WEBSERVICE_LANG)),
            'auth'            => new external_value(PARAM_SAFEDIR, get_string('authplugins', WEBSERVICE_LANG)),
            'studentid'       => new external_value(PARAM_RAW, get_string('studentidinst', WEBSERVICE_LANG)),
            'institution'     => new external_value(PARAM_SAFEDIR, get_string('institution', WEBSERVICE_LANG)),
            'preferredname'   => new external_value(PARAM_RAW, get_string('preferredname', WEBSERVICE_LANG)),
            'introduction'    => new external_value(PARAM_RAW, get_string('introduction', WEBSERVICE_LANG)),
            'country'         => new external_value(PARAM_ALPHA, get_string('country', WEBSERVICE_LANG)),
            'city'            => new external_value(PARAM_NOTAGS, get_string('city', WEBSERVICE_LANG)),
            'address'         => new external_value(PARAM_RAW, get_string('address', WEBSERVICE_LANG)),
            'town'            => new external_value(PARAM_NOTAGS, get_string('town', WEBSERVICE_LANG)),
            'homenumber'      => new external_value(PARAM_RAW, get_string('homenumber', WEBSERVICE_LANG)),
            'businessnumber'  => new external_value(PARAM_RAW, get_string('businessnumber', WEBSERVICE_LANG)),
            'mobilenumber'    => new external_value(PARAM_RAW, get_string('mobilenumber', WEBSERVICE_LANG)),
            'faxnumber'       => new external_value(PARAM_RAW, get_string('faxnumber', WEBSERVICE_LANG)),
            'officialwebsite' => new external_value(PARAM_RAW, get_string('officialwebsite', WEBSERVICE_LANG)),
            'personalwebsite' => new external_value(PARAM_RAW, get_string('personalwebsite', WEBSERVICE_LANG)),
            'blogaddress'     => new external_value(PARAM_RAW, get_string('blogaddress', WEBSERVICE_LANG)),
            'socialprofile'   => new external_value(PARAM_RAW, get_string('socialprofile', WEBSERVICE_LANG)),
            'occupation'      => new external_value(PARAM_TEXT, get_string('occupation', WEBSERVICE_LANG)),
            'industry'        => new external_value(PARAM_TEXT, get_string('industry', WEBSERVICE_LANG)),
            'auths'           => new external_multiple_structure(
                                    new external_single_structure(
                                        array(
                                            'auth'       => new external_value(PARAM_SAFEDIR, get_string('authplugins', WEBSERVICE_LANG)),
                                            'remoteuser' => new external_value(PARAM_RAW, get_string('remoteusername', WEBSERVICE_LANG)),
                                        ), get_string('remoteusersconnected', WEBSERVICE_LANG))
                                 ),
        );
        $localoptions = self::get_local_options();
        if (is_array($localoptions)) {
            $options = array_merge($options, $localoptions);
        }
        return new external_multiple_structure(
            new external_single_structure($options)
        );
    }

    /**
     * parameter definition for input of get_users method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_users_parameters() {
        return new external_function_parameters(
            array(
                'offset'       => new external_value(PARAM_INTEGER, get_string('userresultsoffset', WEBSERVICE_LANG), VALUE_DEFAULT, '0'),
                'limit'        => new external_value(PARAM_INTEGER, get_string('userresultslimit', WEBSERVICE_LANG), VALUE_DEFAULT, '9999999'),
                'sortdir'      => new external_value(PARAM_TEXT, get_string('useridsort', WEBSERVICE_LANG), VALUE_DEFAULT, 'asc'),
            )
        );
    }

    /**
     * Get user information for all users
     *
     * @return array An array of arrays describing users
     */
    public static function get_users($offset, $limit, $sortdir) {
        $params = array('offset' => $offset, 'limit' => $limit, 'sortdir' => $sortdir);
        $params = self::validate_parameters(self::get_users_parameters(), $params);
        list($countusers, $params) = self::_get_bulk_users($params);

        $bulkusers = self::_get_users_by_id($params);

        return array('limit' => $limit, 'offset' => $offset, 'totalcount' => $countusers, 'users' => $bulkusers);
    }

    /**
     * parameter definition for output of get_users method
     *
     * Returns description of method result value
     * @return external_description
     */
    public static function get_users_returns() {
        $users = self::get_users_by_id_returns();
        return new external_single_structure(
            array(
                'limit' => new external_value(PARAM_INTEGER, get_string('userresultslimit', WEBSERVICE_LANG), VALUE_DEFAULT, '10'),
                'offset' => new external_value(PARAM_INTEGER, get_string('userresultsoffset', WEBSERVICE_LANG), VALUE_DEFAULT, '10'),
                'totalcount' => new external_value(PARAM_INTEGER, get_string('userstotalcount', WEBSERVICE_LANG), VALUE_OPTIONAL),
                'users' => $users,
            )
        );
    }

    /**
     * parameter definition for input of get_online_users method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_online_users_parameters() {
        return new external_function_parameters(array());
    }

    /**
     * Get user information for online users
     *
     * @return array An array of arrays describing an atom feed
     */
    public static function get_online_users() {
        global $USER;

        // last 10 active users
        $users = get_onlineusers();

        // now format ready for atom
        $results = array(
                 'id'      => sha1($_SERVER['HTTP_HOST'] . 'get_online_users' . time()),
                 'link'    => get_config('wwwroot'),
                 'email'   => $USER->get('email'),
                 'uri'     => get_config('wwwroot'),
                 'title'   => 'Get Online Users by ' . $USER->username . ' at ' . WebserviceRestServer::format_rfc3339_date(time()),
                 'name'    => 'mahara_user_external_get_online_users',
                 'updated' => WebserviceRestServer::format_rfc3339_date(time()),
                 'entries' => array(),
        );

        foreach ($users['data'] as $user) {
            $user = get_record('usr', 'id', $user);
            if (empty($user)) {
                continue;
            }
            $results['entries'][] = array(
                             'id'        => get_config('wwwroot') . 'user/view.php?id=' . $user->id,
                             'link'      => get_config('wwwroot') . 'user/view.php?id=' . $user->id,
                             'email'     => $user->email,
                             'name'      => display_name($user),
                             'updated'   => WebserviceRestServer::format_rfc3339_date(strtotime($user->lastaccess)),
                             'published' => WebserviceRestServer::format_rfc3339_date(time()),
                             'title'     => 'last_access',
            );
        }
        return $results;
    }

    /**
     * parameter definition for output of get_online_users method
     *
     * Returns description of method result value
     * @return external_description
     */
    public static function get_online_users_returns() {
        return mahara_external_atom_returns();
    }

    /**
     * parameter definition for input of get_my_users method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_my_user_parameters() {
        return new external_function_parameters(array());
    }

    /**
     * Get my user information - the currently connected user
     *
     * @return array An array of arrays describing users
     */
    public static function get_my_user() {
        global $USER;
        $users_by_id = self::get_users_by_id(array(array('id' => $USER->get('id'))));
        return array_shift($users_by_id);
    }

    /**
     * parameter definition for output of get_my_users method
     *
     * Returns description of method result value
     * @return external_description
     */
    public static function get_my_user_returns() {
        $options = array(
            'id'              => new external_value(PARAM_NUMBER, get_string('userid', WEBSERVICE_LANG)),
            'username'        => new external_value(PARAM_RAW, get_string('username', WEBSERVICE_LANG)),
            'firstname'       => new external_value(PARAM_NOTAGS, get_string('firstname', WEBSERVICE_LANG)),
            'lastname'        => new external_value(PARAM_NOTAGS, get_string('lastname', WEBSERVICE_LANG)),
            'email'           => new external_value(PARAM_TEXT, get_string('emailaddress', WEBSERVICE_LANG)),
            'auth'            => new external_value(PARAM_SAFEDIR, get_string('authplugins', WEBSERVICE_LANG)),
            'studentid'       => new external_value(PARAM_RAW, get_string('studentid', WEBSERVICE_LANG)),
            'institution'     => new external_value(PARAM_SAFEDIR, get_string('institution', WEBSERVICE_LANG)),
            'preferredname'   => new external_value(PARAM_RAW, get_string('preferredname', WEBSERVICE_LANG)),
            'introduction'    => new external_value(PARAM_RAW, get_string('introduction', WEBSERVICE_LANG)),
            'country'         => new external_value(PARAM_ALPHA, get_string('country', WEBSERVICE_LANG)),
            'city'            => new external_value(PARAM_NOTAGS, get_string('city', WEBSERVICE_LANG)),
            'address'         => new external_value(PARAM_RAW, get_string('address', WEBSERVICE_LANG)),
            'town'            => new external_value(PARAM_NOTAGS, get_string('town', WEBSERVICE_LANG)),
            'homenumber'      => new external_value(PARAM_RAW, get_string('homenumber', WEBSERVICE_LANG)),
            'businessnumber'  => new external_value(PARAM_RAW, get_string('businessnumber', WEBSERVICE_LANG)),
            'mobilenumber'    => new external_value(PARAM_RAW, get_string('mobilenumber', WEBSERVICE_LANG)),
            'faxnumber'       => new external_value(PARAM_RAW, get_string('faxnumber', WEBSERVICE_LANG)),
            'officialwebsite' => new external_value(PARAM_RAW, get_string('officialwebsite', WEBSERVICE_LANG)),
            'personalwebsite' => new external_value(PARAM_RAW, get_string('personalwebsite', WEBSERVICE_LANG)),
            'blogaddress'     => new external_value(PARAM_RAW, get_string('blogaddress', WEBSERVICE_LANG)),
            'socialprofile'   => new external_value(PARAM_RAW, get_string('socialprofile', WEBSERVICE_LANG)),
            'occupation'      => new external_value(PARAM_TEXT, get_string('occupation', WEBSERVICE_LANG)),
            'industry'        => new external_value(PARAM_TEXT, get_string('industry', WEBSERVICE_LANG)),
            'auths'           => new external_multiple_structure(
                                     new external_single_structure(
                                         array(
                                            'auth'       => new external_value(PARAM_SAFEDIR, get_string('authplugins', WEBSERVICE_LANG)),
                                            'remoteuser' => new external_value(PARAM_RAW, get_string('remoteusername', WEBSERVICE_LANG)),
                                         ), get_string('remoteusersconnected', WEBSERVICE_LANG))
                                 ),
        );
        $localoptions = self::get_local_options();
        if (is_array($localoptions)) {
            $options = array_merge($options, $localoptions);
        }
        return new external_single_structure($options);
    }

    /**
     * parameter definition for input of get_context method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_context_parameters() {
        return new external_function_parameters(array());
    }

    /**
     * Get the current institution context - this is like a test call
     *
     * @return string the connected institution
     */
    public static function get_context() {
        global $WEBSERVICE_INSTITUTION;
        return $WEBSERVICE_INSTITUTION;
    }

    /**
     * parameter definition for output of get_context method
     *
     * Returns description of method result value
     * @return external_description
     */
    public static function get_context_returns() {
        return new external_value(PARAM_TEXT, get_string('userinstitutioncontext', WEBSERVICE_LANG));
    }

    /**
     * parameter definition for input of get_extended_context method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_extended_context_parameters() {
        return new external_function_parameters(array());
    }

    /**
     * Get my user information
     *
     * @return array of connection context information including:
     *   currently connected institution
     *   the current user - like get_my_user
     *   list of functions that this user/connection can execute
     */
    public static function get_extended_context() {
        global $USER, $WEBSERVICE_INSTITUTION, $WS_FUNCTIONS;
        $functions = array();
        foreach ((empty($WS_FUNCTIONS) ? array() : $WS_FUNCTIONS) as $name => $function) {
            $functions[]= array('function' => $name, 'wsdoc' => get_config('wwwroot') . 'webservice/wsdoc.php?id=' . $function['id']);
        }
        return array('institution' => $WEBSERVICE_INSTITUTION,
                     'institutionname' => get_field('institution', 'displayname', 'name', $WEBSERVICE_INSTITUTION),
                     'sitename' => get_config('sitename'),
                     'siteurl' => get_config('wwwroot'),
                     'userid' => $USER->get('id'),
                     'username' => $USER->get('username'),
                     'firstname' => $USER->get('firstname'),
                     'lastname' => $USER->get('lastname'),
                     'fullname' => display_name($USER, null, true),
                     'functions' => $functions,
                );
    }

    /**
     * parameter definition for output of get_extended_context method
     *
     * Returns description of method result value
     * @return external_description
     */
    public static function get_extended_context_returns() {
        return new external_single_structure(
                array(
                    'institution'     => new external_value(PARAM_TEXT, get_string('institutioncontextauthuser', WEBSERVICE_LANG)),
                    'institutionname' => new external_value(PARAM_TEXT, get_string('institutionnameauthuser', WEBSERVICE_LANG)),
                    'sitename'        => new external_value(PARAM_RAW, get_string('sitename', WEBSERVICE_LANG), VALUE_OPTIONAL),
                    'siteurl'         => new external_value(PARAM_RAW, get_string('siteurl', WEBSERVICE_LANG), VALUE_OPTIONAL),
                    'userid'          => new external_value(PARAM_NUMBER, get_string('authuserid', WEBSERVICE_LANG), VALUE_OPTIONAL),
                    'username'        => new external_value(PARAM_RAW, get_string('authuserusername', WEBSERVICE_LANG), VALUE_OPTIONAL),
                    'firstname'       => new external_value(PARAM_TEXT, get_string('authuserfirstname', WEBSERVICE_LANG), VALUE_OPTIONAL),
                    'lastname'        => new external_value(PARAM_TEXT, get_string('authuserlastname', WEBSERVICE_LANG), VALUE_OPTIONAL),
                    'fullname'        => new external_value(PARAM_TEXT, get_string('authuserfullname', WEBSERVICE_LANG), VALUE_OPTIONAL),
                    'functions'       => new external_multiple_structure(
                                                    new external_single_structure(
                                                        array(
                                                            'function' => new external_value(PARAM_RAW, get_string('functionname', WEBSERVICE_LANG), VALUE_OPTIONAL),
                                                            'wsdoc'    => new external_value(PARAM_RAW, get_string('functiondocuri', WEBSERVICE_LANG), VALUE_OPTIONAL),
                                                        ), get_string('availfunctions', WEBSERVICE_LANG))
                                                ),
                    )
            );

    }

    /**
     * parameter definition for input of update_favourites method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function update_favourites_parameters() {

       return new external_function_parameters(
            array(
                'users' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id'              => new external_value(PARAM_NUMBER, get_string('favsownerid', WEBSERVICE_LANG), VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                            'username'        => new external_value(PARAM_RAW, get_string('favsownerusername', WEBSERVICE_LANG), VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                            'shortname'       => new external_value(PARAM_SAFEDIR, get_string('favshortname', WEBSERVICE_LANG), VALUE_DEFAULT, 'favourites', NULL_NOT_ALLOWED),
                            'institution'     => new external_value(PARAM_SAFEDIR, get_string('institution', WEBSERVICE_LANG), VALUE_DEFAULT, 'mahara', NULL_NOT_ALLOWED), //TODO: change 'mahara'?
                            'favourites'      => new external_multiple_structure(
                                                            new external_single_structure(
                                                                array(
                                                                    'id'       => new external_value(PARAM_NUMBER, get_string('favuserid', WEBSERVICE_LANG), VALUE_OPTIONAL),
                                                                    'username' => new external_value(PARAM_RAW, get_string('favusername', WEBSERVICE_LANG), VALUE_OPTIONAL),
                                                                ), get_string('userfavs', WEBSERVICE_LANG))
                                                        ),
                            )
                    )
                )
            )
        );
    }

    /**
     * update one or more user favourites
     *
     * @param array $users
     */
    public static function update_favourites($users) {
        global $USER, $WEBSERVICE_INSTITUTION, $WEBSERVICE_OAUTH_USER;

        $params = self::validate_parameters(self::update_favourites_parameters(), array('users'=>$users));

        db_begin();
        foreach ($params['users'] as $user) {
            $dbuser = self::checkuser($user);

            $ownerid = $dbuser->id;

            // Make sure auth is valid
            if (!$authinstance = get_record('auth_instance', 'id', $dbuser->authinstance)) {
                throw new WebserviceInvalidParameterException('update_favourites | ' . get_string('invalidauthtype', 'auth.webservice', $dbuser->authinstance));
            }
            // check the institution is allowed
            // basic check authorisation to edit for the current institution
            if (!$USER->can_edit_institution($authinstance->institution)) {
                throw new WebserviceInvalidParameterException('update_favourites | ' . get_string('accessdeniedforinstuser', 'auth.webservice', $authinstance->institution, $dbuser->id));
            }

            // are we allowed to delete for this institution
            if ($WEBSERVICE_INSTITUTION != $user['institution'] || !$USER->can_edit_institution($user['institution'])) {
                throw new WebserviceInvalidParameterException('update_favourites | ' . get_string('accessdeniedforinst', 'auth.webservice', $user['institution']));
            }

            // check that the favourites exist and we are allowed to administer them
            $favourites = array();
            foreach ($user['favourites'] as $favourite) {
                $dbuser = self::checkuser($favourite);
                // Make sure auth is valid
                if (!$authinstance = get_record('auth_instance', 'id', $dbuser->authinstance)) {
                    throw new WebserviceInvalidParameterException('update_favourites | ' . get_string('invalidauthtype', 'auth.webservice', $dbuser->authinstance));
                }

                // check the institution is allowed
                // basic check authorisation to edit for the current institution of the user
                if (!$USER->can_edit_institution($authinstance->institution)) {
                    throw new WebserviceInvalidParameterException('update_favourites | ' . get_string('accessdeniedforinstuser', 'auth.webservice', $authinstance->institution, $dbuser->username));
                }
                $favourites[]= $dbuser->id;
            }

            // now do the update
            update_favorites($ownerid, $user['shortname'], $user['institution'], $favourites);
        }
        db_commit();

        return null;
    }

   /**
    * parameter definition for output of update_favourites method
    */
    public static function update_favourites_returns() {
        return null;
    }

    /**
     * parameter definition for input of get_favourites method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_favourites_parameters() {
        return new external_function_parameters(
            array(
                'users'=> new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'shortname' => new external_value(PARAM_SAFEDIR, get_string('favshortname', WEBSERVICE_LANG), VALUE_DEFAULT, 'favourites', NULL_NOT_ALLOWED),
                            'userid'    => new external_value(PARAM_INT, get_string('userid', WEBSERVICE_LANG), VALUE_OPTIONAL),
                            'username'  => new external_value(PARAM_RAW, get_string('favsownerusername', WEBSERVICE_LANG), VALUE_OPTIONAL),
                        )
                    )
                )
            )
        );
    }

    /**
     * Get user favourites for one or more users
     *
     * @param array $userids  array of user ids
     * @return array An array of arrays describing users favourites
     */
    public static function get_favourites($users) {
        global $WEBSERVICE_INSTITUTION, $WEBSERVICE_OAUTH_USER;

        $params = self::validate_parameters(self::get_favourites_parameters(), array('users' => $users));

        // build the final results
        $result = array();
        foreach ($params['users'] as $user) {
            $dbuser = self::checkuser($user);
            // check the institution
            if (!mahara_external_in_institution($dbuser, $WEBSERVICE_INSTITUTION)) {
                throw new WebserviceInvalidParameterException('get_favourites | ' . get_string('notauthforuseridinstitution', 'auth.webservice', $user['userid'], $WEBSERVICE_INSTITUTION));
            }

            // get the favourite for the shortname for this user
            $favs = array();
            $favourites = get_user_favorites($dbuser->id, 100);
            $dbfavourite = get_record('favorite', 'shortname', $user['shortname'], 'institution', $WEBSERVICE_INSTITUTION, 'owner', $dbuser->id);
            if (empty($dbfavourite)) {
                // create an empty one
                $dbfavourite = (object) array('shortname' => $user['shortname'], 'institution' => $WEBSERVICE_INSTITUTION);
            }
            if (!empty($favourites)) {
                foreach ($favourites as $fav) {
                    $dbfavuser = get_record('usr', 'id', $fav->id, 'deleted', 0);
                    $favs[]= array('id' => $fav->id, 'username' => $dbfavuser->username);
                }
            }

            $result[] = array(
                            'id'            => $dbuser->id,
                            'username'      => $dbuser->username,
                            'shortname'     => $dbfavourite->shortname,
                            'institution'   => $dbfavourite->institution,
                            'favourites'    => $favs,
                            );
        }

        return $result;
    }

    /**
     * parameter definition for output of get_favourites method
     *
     * Returns description of method result value
     * @return external_description
     */
    public static function get_favourites_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                        array(
                            'id'              => new external_value(PARAM_NUMBER, get_string('favsownerid', WEBSERVICE_LANG)),
                            'username'        => new external_value(PARAM_RAW, get_string('favsownerusername', WEBSERVICE_LANG)),
                            'shortname'       => new external_value(PARAM_SAFEDIR, get_string('favshortname', WEBSERVICE_LANG)),
                            'institution'     => new external_value(PARAM_SAFEDIR, get_string('institution', WEBSERVICE_LANG)),
                            'favourites'      => new external_multiple_structure(
                                                            new external_single_structure(
                                                                array(
                                                                    'id'       => new external_value(PARAM_NUMBER, get_string('favuserid', WEBSERVICE_LANG), VALUE_OPTIONAL),
                                                                    'username' => new external_value(PARAM_RAW, get_string('favusername', WEBSERVICE_LANG), VALUE_OPTIONAL),
                                                                ), get_string('userfavs', WEBSERVICE_LANG))
                                                        ),
                                                )
                )
        );
    }

    /**
     * parameter definition for input of get_all_favourites method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_all_favourites_parameters() {
        return new external_function_parameters(
            array(
                'shortname' => new external_value(PARAM_SAFEDIR, get_string('favshortname', WEBSERVICE_LANG), VALUE_DEFAULT, get_string('favourites', WEBSERVICE_LANG), NULL_NOT_ALLOWED),
                )
        );
    }

    /**
     * Get all user favourites
     *
     * @param string $shortname  shortname of the favourites
     * @return array An array describing favourites
     */
    public static function get_all_favourites($shortname) {
        global $WEBSERVICE_INSTITUTION, $WEBSERVICE_OAUTH_USER;

        $params = self::validate_parameters(self::get_all_favourites_parameters(), array('shortname' => $shortname));

        $dbfavourites = get_records_sql_array('SELECT * from {favorite} WHERE shortname = ? AND institution = ?',array($shortname, $WEBSERVICE_INSTITUTION));
        if (empty($dbfavourites)) {
            throw new WebserviceInvalidParameterException('get_favourites | ' . get_string('invalidfavourite', 'auth.webservice', $shortname . '/' . $WEBSERVICE_INSTITUTION));
        }

        $result = array();
        foreach ($dbfavourites as $dbfavourite) {
            $dbuser = get_record('usr', 'id', $dbfavourite->owner, 'deleted', 0);
            if (empty($dbuser)) {
                continue;
            }
            $favourites = get_user_favorites($dbuser->id, 100);
            $favs = array();
            if (!empty($favourites)) {
                foreach ($favourites as $fav) {
                    $dbfavuser = get_record('usr', 'id', $fav->id, 'deleted', 0);
                    $favs[]= array('id' => $fav->id, 'username' => $dbfavuser->username);
                }
            }

            $result[] = array(
                            'id'            => $dbuser->id,
                            'username'      => $dbuser->username,
                            'shortname'     => $dbfavourite->shortname,
                            'institution'   => $dbfavourite->institution,
                            'favourites'    => $favs,
                            );
        }

        return $result;
    }

    /**
     *  parameter definition for output of get_all_favourites method
     *
     * Returns description of method result value
     * @return external_description
     */
    public static function get_all_favourites_returns() {
        return self::get_favourites_returns();
    }

    /**
     * The parameter definition for input of upload_file method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function upload_file_parameters() {
        return new external_function_parameters(
      array(
                'externalsource' => new external_value(PARAM_TEXT, get_string('externalfilesource', WEBSERVICE_LANG)),
                'userid'         => new external_value(PARAM_INT, get_string('userid', WEBSERVICE_LANG), VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                'username'       => new external_value(PARAM_RAW, get_string('username', WEBSERVICE_LANG), VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                'filetoupload'   => new external_value(PARAM_FILE, get_string('filetoupload', WEBSERVICE_LANG)),
                'foldername'     => new external_value(PARAM_RAW, get_string('foldername', WEBSERVICE_LANG)),
                'title'          => new external_value(PARAM_TEXT, get_string('filetitle', WEBSERVICE_LANG), VALUE_OPTIONAL),
                'description'    => new external_value(PARAM_TEXT, get_string('filedescription', WEBSERVICE_LANG), VALUE_OPTIONAL),
                'tags'           => new external_multiple_structure(
                    new external_value(PARAM_RAW, "Text of tag"),
                    "List of tags to apply to the file",
                    VALUE_DEFAULT,
                    array()
                )
            )
        );
    }

    /**
     * Upload a file to files area
     *
     * @param  mixed $externalsource
     * @param  mixed $userid
     * @param  mixed $username
     * @param  mixed $filetoupload
     * @param  mixed $foldername
     * @param  mixed $title
     * @param  mixed $description
     * @param  mixed $tags
     * @return array An array describing the results of uploading the file

     */
    public static function upload_file($externalsource, $userid, $username, $filetoupload, $foldername, $title=null, $description = '', $tags = array() ) {
        global $USER, $WEBSERVICE_AUTH_METHOD;

        $params = array(
                    'externalsource' => $externalsource,
                    'userid'         => $userid,
                    'username'       => $username,
                    'filetoupload'   => $filetoupload,
                    'foldername'     => $foldername,
                    'title'          => $title,
                    'description'    => $description,
                    'tags'           => $tags
                );
        $params = self::validate_parameters(self::upload_file_parameters(), $params);

        // Get authinstance connected to this webservice instance
        $auth_instance = AuthFactory::create($WEBSERVICE_AUTH_METHOD);
        $is_remote_auth = ($auth_instance->needs_remote_username() || $auth_instance->authname === 'webservice');
        $filetoupload = $title ?: $filetoupload;
        $recipient_id = 0;
        $result_artefact_id = null;
        $upload_to_self = false;

        // Match the given ID or username with an account
        $u = new User;

        if (isset($userid)) {
            $u->find_by_id($userid);
        }
        else if ($is_remote_auth) {
            // Check the remote user table first and then fall back to the usr table if the username passed in is their username and not their remoteusername.
            $u->find_by_instanceid_username($WEBSERVICE_AUTH_METHOD, $username, $is_remote_auth);
        }
        else {
            $u->find_by_username($username);
        }

        $recipient_id = $u->get('id');
        $upload_to_self = $USER->get('id') === $recipient_id;

        // Check the capabilities of the auth method, i.e. > 0 targets institutions, where as 0 = No access to any institution, -1 = access to all institutitons
        $check_institution = true;

        if (!$upload_to_self) {
            switch ($WEBSERVICE_AUTH_METHOD) {
                case -1: # Access to all institutions
                    # No need to check institution, just upload
                    $check_institution = false;
                    break;
                case 0: # No access to any institution
                    throw new WebserviceAccessException(get_string('invalidpermission', 'auth.webservice', isset($userid) ? $userid : $username ));
                default: # Limited to an institution or 'mahara'. i.e. no institution
                    break;
            }

            if ($check_institution) {
                $recipient_institutions = array_keys($u->get('institutions'));
                $token_has_access_to_institution = in_array($auth_instance->institution, $recipient_institutions);
                $has_permission = $token_has_access_to_institution || $USER->is_admin_for_user($recipient_id);

                if (!$has_permission) {
                    throw new WebserviceAccessException(get_string('invalidpermission', 'auth.webservice', $userid ? $userid : $username ));
                }
            }
        }

        // Upload
        if ($result_artefact_id = parent::handle_file_upload('filetoupload', null, $foldername, $title, $description, $tags, $recipient_id)) {
            $parent_folder_id = ArtefactTypeFolder::get_folder_id_artefact_contents($result_artefact_id);
            $message = new stdClass();
            $message->users = array($recipient_id);
            $message->subject = get_string('fileuploadmessagesubject', WEBSERVICE_LANG);
            $message->message = get_string('fileuploadmessagebody', WEBSERVICE_LANG, $filetoupload, $externalsource, $foldername);
            $message->url = 'artefact/file/index.php' . ($parent_folder_id ? '?folder=' . $parent_folder_id : '');
            activity_occurred('maharamessage', $message);
        }

        return array(
            'fileid' => $result_artefact_id,
            'status' => $result_artefact_id ? get_string('fileuploadsuccess', WEBSERVICE_LANG) : get_string('fileuploadfail', WEBSERVICE_LANG)
        );
    }

    /**
     * The parameter definition for output of upload_file method
     *
     * @return external_description
     */
    public static function upload_file_returns() {
        return new external_single_structure(
        array(
                'fileid' => new external_value(PARAM_TEXT, get_string('fileid', WEBSERVICE_LANG)),
                'status'       => new external_value(PARAM_TEXT, get_string('fileuploadstatus', WEBSERVICE_LANG)),
            )
        );
    }
}
