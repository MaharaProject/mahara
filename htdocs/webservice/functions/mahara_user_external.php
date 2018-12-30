<?php
/**
 *
 * @package    mahara
 * @subpackage auth-webservice
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * External user API
 *
 * @package    auth
 * @subpackage webservice
 * @copyright  2009 Moodle Pty Ltd (http://moodle.com)
 * @copyright  Copyright (C) 2011 Catalyst IT Ltd (http://www.catalyst.net.nz)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
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

    static private $ALLOWEDKEYS = array(
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

    /**
     * parameter definition for input of delete_users method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function autologin_redirect_parameters() {
       return new external_function_parameters(
                        array(
                            'context_id'        => new external_value(PARAM_RAW, 'LTI context_id', VALUE_OPTIONAL),
                            'context_label'        => new external_value(PARAM_RAW, 'LTI context_label', VALUE_OPTIONAL),
                            'context_title'        => new external_value(PARAM_RAW, 'LTI context_title', VALUE_OPTIONAL),
                            'context_type'        => new external_value(PARAM_RAW, 'LTI context_type', VALUE_OPTIONAL),
                            'ext_lms'        => new external_value(PARAM_RAW, 'LTI ext_lms', VALUE_OPTIONAL),
                            'ext_user_username'        => new external_value(PARAM_RAW, 'LTI ext_user_username', VALUE_OPTIONAL),
                            'launch_presentation_locale'        => new external_value(PARAM_RAW, 'LTI launch_presentation_locale', VALUE_OPTIONAL),
                            'launch_presentation_return_url'        => new external_value(PARAM_RAW, 'LTI launch_presentation_return_url', VALUE_OPTIONAL),
                            'lis_person_contact_email_primary'        => new external_value(PARAM_RAW, 'LTI lis_person_contact_email_primary', VALUE_OPTIONAL),
                            'lis_person_name_family'        => new external_value(PARAM_RAW, 'LTI lis_person_name_family', VALUE_OPTIONAL),
                            'lis_person_name_full'        => new external_value(PARAM_RAW, 'LTI lis_person_name_full', VALUE_OPTIONAL),
                            'lis_person_name_given'        => new external_value(PARAM_RAW, 'LTI lis_person_name_given', VALUE_OPTIONAL),
                            'lis_person_sourcedid'        => new external_value(PARAM_RAW, 'LTI lis_person_sourcedid', VALUE_OPTIONAL),
                            'lti_message_type'        => new external_value(PARAM_RAW, 'LTI lti_message_type', VALUE_OPTIONAL),
                            'lti_version'        => new external_value(PARAM_RAW, 'LTI lti_version', VALUE_OPTIONAL),
                            'resource_link_description'        => new external_value(PARAM_RAW, 'LTI resource_link_description', VALUE_OPTIONAL),
                            'resource_link_id'        => new external_value(PARAM_RAW, 'LTI resource_link_id', VALUE_OPTIONAL),
                            'resource_link_title'        => new external_value(PARAM_RAW, 'LTI resource_link_title', VALUE_OPTIONAL),
                            'roles'        => new external_value(PARAM_RAW, 'LTI roles', VALUE_OPTIONAL),
                            'tool_consumer_info_product_family_code'        => new external_value(PARAM_RAW, 'LTI tool_consumer_info_product_family_code', VALUE_OPTIONAL),
                            'tool_consumer_info_version'        => new external_value(PARAM_RAW, 'LTI tool_consumer_info_version', VALUE_OPTIONAL),
                            'tool_consumer_instance_guid'        => new external_value(PARAM_RAW, 'LTI tool_consumer_instance_guid', VALUE_OPTIONAL),
                            'tool_consumer_instance_name'        => new external_value(PARAM_RAW, 'LTI tool_consumer_instance_name', VALUE_OPTIONAL),
                            'user_id'        => new external_value(PARAM_RAW, 'LTI user_id', VALUE_OPTIONAL),
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

        return new external_function_parameters(
            array(
                'users' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'username'        => new external_value(PARAM_RAW, 'Between 3 and 30 characters long. Letters, numbers and most standard symbols are allowed'),
                            'password'        => new external_value(PARAM_RAW, 'Must be at least 6 characters long. Must be different from the username'),
                            'firstname'       => new external_value(PARAM_NOTAGS, 'The first name(s) of the user'),
                            'lastname'        => new external_value(PARAM_NOTAGS, 'The family name of the user'),
                            'email'           => new external_value(PARAM_EMAIL, 'A valid and unique email address'),
                            'institution'     => new external_value(PARAM_SAFEDIR, 'Mahara institution', VALUE_DEFAULT, 'mahara', NULL_NOT_ALLOWED),
                            'auth'            => new external_value(PARAM_SAFEDIR, 'Auth plugins include manual, ldap, imap, etc', VALUE_DEFAULT, 'internal', NULL_NOT_ALLOWED),
                            'quota'           => new external_value(PARAM_INTEGER, 'Option storage quota', VALUE_OPTIONAL),
                            'forcepasswordchange' => new external_value(PARAM_BOOL, 'Forcing password change on first login', VALUE_DEFAULT, '0'),
                            'studentid'       => new external_value(PARAM_RAW, 'An arbitrary ID code number for the student', VALUE_DEFAULT, ''),
                            'remoteuser'      => new external_value(PARAM_RAW, 'Remote user Id', VALUE_DEFAULT, ''),
                            'preferredname'   => new external_value(PARAM_TEXT, 'User preferred name', VALUE_OPTIONAL),
                            'address'         => new external_value(PARAM_RAW, 'Street address of the user', VALUE_OPTIONAL),
                            'town'            => new external_value(PARAM_NOTAGS, 'Home town of the user', VALUE_OPTIONAL),
                            'city'            => new external_value(PARAM_NOTAGS, 'Home city of the user', VALUE_OPTIONAL),
                            'country'         => new external_value(PARAM_ALPHA, 'Home country code of the user, such as NZ', VALUE_OPTIONAL),
                            'homenumber'      => new external_value(PARAM_RAW, 'Home phone number', VALUE_OPTIONAL),
                            'businessnumber'  => new external_value(PARAM_RAW, 'Business phone number', VALUE_OPTIONAL),
                            'mobilenumber'    => new external_value(PARAM_RAW, 'Mobile phone number', VALUE_OPTIONAL),
                            'faxnumber'       => new external_value(PARAM_RAW, 'Fax number', VALUE_OPTIONAL),
                            'introduction'    => new external_value(PARAM_RAW, 'Introduction text', VALUE_OPTIONAL),
                            'officialwebsite' => new external_value(PARAM_RAW, 'Official user website', VALUE_OPTIONAL),
                            'personalwebsite' => new external_value(PARAM_RAW, 'Personal website', VALUE_OPTIONAL),
                            'blogaddress'     => new external_value(PARAM_RAW, 'Blog web address', VALUE_OPTIONAL),
                            'socialprofile'   => new external_value(PARAM_RAW, 'Social profile needs both the type and url entered', VALUE_OPTIONAL),
                            'occupation'      => new external_value(PARAM_TEXT, 'Occupation', VALUE_OPTIONAL),
                            'industry'        => new external_value(PARAM_TEXT, 'Industry', VALUE_OPTIONAL),
                        )
                    )
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
            foreach (self::$ALLOWEDKEYS as $field) {
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
                    'id'       => new external_value(PARAM_INT, 'user id'),
                    'username' => new external_value(PARAM_RAW, 'user name'),
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
                            'id'              => new external_value(PARAM_NUMBER, 'ID of the user to delete', VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                            'username'        => new external_value(PARAM_RAW, 'Username of the user to delete', VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
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

       return new external_function_parameters(
            array(
                'users' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id'              => new external_value(PARAM_NUMBER, 'ID of the user', VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                            'username'        => new external_value(PARAM_RAW, 'Username policy is defined in Mahara security config', VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                            'password'        => new external_value(PARAM_RAW, 'Plain text password consisting of any characters', VALUE_OPTIONAL),
                            'firstname'       => new external_value(PARAM_NOTAGS, 'The first name(s) of the user', VALUE_OPTIONAL),
                            'lastname'        => new external_value(PARAM_NOTAGS, 'The family name of the user', VALUE_OPTIONAL),
                            'email'           => new external_value(PARAM_EMAIL, 'A valid and unique email address', VALUE_OPTIONAL),
                            'institution'     => new external_value(PARAM_TEXT, 'Mahara institution', VALUE_OPTIONAL),
                            'auth'            => new external_value(PARAM_TEXT, 'Auth plugins include manual, ldap, imap, etc', VALUE_OPTIONAL),
                            'quota'           => new external_value(PARAM_INTEGER, 'Option storage quota', VALUE_OPTIONAL),
                            'forcepasswordchange' => new external_value(PARAM_BOOL, 'Forcing password change on first login', VALUE_OPTIONAL),
                            'studentid'       => new external_value(PARAM_RAW, 'An arbitrary ID code number for the student', VALUE_OPTIONAL),
                            'remoteuser'      => new external_value(PARAM_RAW, 'Remote user Id', VALUE_OPTIONAL),
                            'preferredname'   => new external_value(PARAM_TEXT, 'Userpreferred name', VALUE_OPTIONAL),
                            'address'         => new external_value(PARAM_RAW, 'Introduction text', VALUE_OPTIONAL),
                            'town'            => new external_value(PARAM_NOTAGS, 'Home town of the user', VALUE_OPTIONAL),
                            'city'            => new external_value(PARAM_NOTAGS, 'Home city of the user', VALUE_OPTIONAL),
                            'country'         => new external_value(PARAM_ALPHA, 'Home country code of the user, such as NZ', VALUE_OPTIONAL),
                            'homenumber'      => new external_value(PARAM_RAW, 'Home phone number', VALUE_OPTIONAL),
                            'businessnumber'  => new external_value(PARAM_RAW, 'business phone number', VALUE_OPTIONAL),
                            'mobilenumber'    => new external_value(PARAM_RAW, 'mobile phone number', VALUE_OPTIONAL),
                            'faxnumber'       => new external_value(PARAM_RAW, 'fax number', VALUE_OPTIONAL),
                            'introduction'    => new external_value(PARAM_RAW, 'Introduction text', VALUE_OPTIONAL),
                            'officialwebsite' => new external_value(PARAM_RAW, 'Official user website', VALUE_OPTIONAL),
                            'personalwebsite' => new external_value(PARAM_RAW, 'Personal website', VALUE_OPTIONAL),
                            'blogaddress'     => new external_value(PARAM_RAW, 'Blog web address', VALUE_OPTIONAL),
                            'socialprofile'   => new external_value(PARAM_RAW, 'Social profile', VALUE_OPTIONAL),
                            'occupation'      => new external_value(PARAM_TEXT, 'Occupation', VALUE_OPTIONAL),
                            'industry'        => new external_value(PARAM_TEXT, 'Industry', VALUE_OPTIONAL),
                            )
                    )
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
            foreach (self::$ALLOWEDKEYS as $field) {
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
                            'id'              => new external_value(PARAM_NUMBER, 'ID of the user', VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                            'username'        => new external_value(PARAM_RAW, 'Username of the user', VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                            'remoteuser'      => new external_value(PARAM_RAW, 'Remote username of the user', VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                            'email'           => new external_value(PARAM_RAW, 'Email address of the user', VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                            )
                        )
                    )
                )
            );
    }

    /**
     * Check that a user exists
     *
     * @param array $user array('id' => .., 'username' => ..)
     * @return array() of user
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
            $dbuser = get_record('usr', 'username', $user['username']);
            if (empty($dbuser)) {
                throw new WebserviceInvalidParameterException(get_string('invalidusername', 'auth.webservice', $user['username']));
            }
            $id = $dbuser->id;
        }
        else if (isset($user['email'])) {
            $dbuser = get_record('usr', 'email', $user['email'], null, null, null, null, '*', 0);
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
               $dbuser = $user_factory->find_by_instanceid_username($dbinstance->id, $user['remoteuser'], true);
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
            foreach (array('introduction', 'country', 'city', 'address',
                           'town', 'homenumber', 'businessnumber', 'mobilenumber', 'faxnumber',
                           'officialwebsite', 'personalwebsite', 'blogaddress', 'socialprofile',
                           'occupation', 'industry') as $attr) {
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
                foreach (self::$ALLOWEDKEYS as $field) {
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
        return new external_multiple_structure(
                new external_single_structure(
                        array(
                    'id'              => new external_value(PARAM_NUMBER, 'ID of the user'),
                    'username'        => new external_value(PARAM_RAW, 'Username policy is defined in Mahara security config'),
                    'firstname'       => new external_value(PARAM_NOTAGS, 'The first name(s) of the user'),
                    'lastname'        => new external_value(PARAM_NOTAGS, 'The family name of the user'),
                    'email'           => new external_value(PARAM_TEXT, 'An email address - allow email as root@localhost'),
                    'auth'            => new external_value(PARAM_SAFEDIR, 'Auth plugins include manual, ldap, imap, etc'),
                    'studentid'       => new external_value(PARAM_RAW, 'An arbitrary ID code number perhaps from the institution'),
                    'institution'     => new external_value(PARAM_SAFEDIR, 'Mahara institution'),
                    'preferredname'   => new external_value(PARAM_RAW, 'User preferred name'),
                    'introduction'    => new external_value(PARAM_RAW, 'User introduction'),
                    'country'         => new external_value(PARAM_ALPHA, 'Home country code of the user, such as AU or CZ'),
                    'city'            => new external_value(PARAM_NOTAGS, 'Home city of the user'),
                    'address'         => new external_value(PARAM_RAW, 'Introduction text'),
                    'town'            => new external_value(PARAM_NOTAGS, 'Home town of the user'),
                    'homenumber'      => new external_value(PARAM_RAW, 'Home phone number'),
                    'businessnumber'  => new external_value(PARAM_RAW, 'business phone number'),
                    'mobilenumber'    => new external_value(PARAM_RAW, 'mobile phone number'),
                    'faxnumber'       => new external_value(PARAM_RAW, 'fax number'),
                    'officialwebsite' => new external_value(PARAM_RAW, 'Official user website'),
                    'personalwebsite' => new external_value(PARAM_RAW, 'Personal website'),
                    'blogaddress'     => new external_value(PARAM_RAW, 'Blog web address'),
                    'socialprofile'   => new external_value(PARAM_RAW, 'Social profile'),
                    'occupation'      => new external_value(PARAM_TEXT, 'Occupation'),
                    'industry'        => new external_value(PARAM_TEXT, 'Industry'),
                    'auths'           => new external_multiple_structure(
                                            new external_single_structure(
                                                array(
                                                    'auth'       => new external_value(PARAM_SAFEDIR, 'Auth plugins include manual, ldap, imap, etc'),
                                                    'remoteuser' => new external_value(PARAM_RAW, 'remote username'),
                                                ), 'Connected Remote Users')
                                        ),
                        )
                )
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
                'offset'       => new external_value(PARAM_INTEGER, 'Offset of the results', VALUE_DEFAULT, '0'),
                'limit'        => new external_value(PARAM_INTEGER, 'Limit of the results', VALUE_DEFAULT, '9999999'),
                'sortdir'      => new external_value(PARAM_TEXT, 'Order user id. Either "asc" or "desc"', VALUE_DEFAULT, 'asc'),
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
                'limit' => new external_value(PARAM_INTEGER, 'Limit of the results', VALUE_DEFAULT, '10'),
                'offset' => new external_value(PARAM_INTEGER, 'Offset of the results', VALUE_DEFAULT, '10'),
                'totalcount' => new external_value(PARAM_INTEGER, 'Total count of the results', VALUE_OPTIONAL),
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
                 'title'   => 'Get Online Users by ' . $USER->username . ' at ' . webservice_rest_server::format_rfc3339_date(time()),
                 'name'    => 'mahara_user_external_get_online_users',
                 'updated' => webservice_rest_server::format_rfc3339_date(time()),
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
                             'updated'   => webservice_rest_server::format_rfc3339_date(strtotime($user->lastaccess)),
                             'published' => webservice_rest_server::format_rfc3339_date(time()),
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
        return new external_single_structure(
                 array(
                    'id'              => new external_value(PARAM_NUMBER, 'ID of the user'),
                    'username'        => new external_value(PARAM_RAW, 'Username policy is defined in Mahara security config'),
                    'firstname'       => new external_value(PARAM_NOTAGS, 'The first name(s) of the user'),
                    'lastname'        => new external_value(PARAM_NOTAGS, 'The family name of the user'),
                    'email'           => new external_value(PARAM_TEXT, 'An email address - allow email as root@localhost'),
                    'auth'            => new external_value(PARAM_SAFEDIR, 'Auth plugins include manual, ldap, imap, etc'),
                    'studentid'       => new external_value(PARAM_RAW, 'An arbitrary ID code number perhaps from the institution'),
                    'institution'     => new external_value(PARAM_SAFEDIR, 'Mahara institution'),
                    'preferredname'   => new external_value(PARAM_RAW, 'User preferred name'),
                    'introduction'    => new external_value(PARAM_RAW, 'User introduction'),
                    'country'         => new external_value(PARAM_ALPHA, 'Home country code of the user, such as AU or CZ'),
                    'city'            => new external_value(PARAM_NOTAGS, 'Home city of the user'),
                    'address'         => new external_value(PARAM_RAW, 'Introduction text'),
                    'town'            => new external_value(PARAM_NOTAGS, 'Home town of the user'),
                    'homenumber'      => new external_value(PARAM_RAW, 'Home phone number'),
                    'businessnumber'  => new external_value(PARAM_RAW, 'business phone number'),
                    'mobilenumber'    => new external_value(PARAM_RAW, 'mobile phone number'),
                    'faxnumber'       => new external_value(PARAM_RAW, 'fax number'),
                    'officialwebsite' => new external_value(PARAM_RAW, 'Official user website'),
                    'personalwebsite' => new external_value(PARAM_RAW, 'Personal website'),
                    'blogaddress'     => new external_value(PARAM_RAW, 'Blog web address'),
                    'socialprofile'   => new external_value(PARAM_RAW, 'Social profile'),
                    'occupation'      => new external_value(PARAM_TEXT, 'Occupation'),
                    'industry'        => new external_value(PARAM_TEXT, 'Industry'),
                    'auths'           => new external_multiple_structure(
                                            new external_single_structure(
                                                array(
                                                    'auth'       => new external_value(PARAM_SAFEDIR, 'Auth plugins include manual, ldap, imap, etc'),
                                                    'remoteuser' => new external_value(PARAM_RAW, 'remote username'),
                                                ), 'Connected Remote Users')
                                        ),
                        )
                );
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
        return new external_value(PARAM_TEXT, 'The INSTITUTION context of the authenticated user');
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
                    'institution'     => new external_value(PARAM_TEXT, 'The INSTITUTION context of the authenticated user'),
                    'institutionname' => new external_value(PARAM_TEXT, 'The INSTITUTION FULLNAME context of the authenticated user'),
                    'sitename'        => new external_value(PARAM_RAW, 'Site name', VALUE_OPTIONAL),
                    'siteurl'         => new external_value(PARAM_RAW, 'Site URL', VALUE_OPTIONAL),
                    'userid'          => new external_value(PARAM_NUMBER, 'ID of the authenticated user', VALUE_OPTIONAL),
                    'username'        => new external_value(PARAM_RAW, 'Username of the authenticated user', VALUE_OPTIONAL),
                    'firstname'       => new external_value(PARAM_TEXT, 'Firstname of the authenticated user', VALUE_OPTIONAL),
                    'lastname'        => new external_value(PARAM_TEXT, 'Last of the authenticated user', VALUE_OPTIONAL),
                    'fullname'        => new external_value(PARAM_TEXT, 'Fullname of the authenticated user', VALUE_OPTIONAL),
                    'functions'       => new external_multiple_structure(
                                                    new external_single_structure(
                                                        array(
                                                            'function' => new external_value(PARAM_RAW, 'functon name', VALUE_OPTIONAL),
                                                            'wsdoc'    => new external_value(PARAM_RAW, 'function documentation URI', VALUE_OPTIONAL),
                                                        ), 'Available functions')
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
                            'id'              => new external_value(PARAM_NUMBER, 'ID of the favourites owner', VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                            'username'        => new external_value(PARAM_RAW, 'Username of the favourites owner', VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                            'shortname'       => new external_value(PARAM_SAFEDIR, 'Favourites shorname', VALUE_DEFAULT, 'favourites', NULL_NOT_ALLOWED, null, NULL_ALLOWED, 'id'),
                            'institution'     => new external_value(PARAM_SAFEDIR, 'Mahara institution', VALUE_DEFAULT, 'mahara', NULL_NOT_ALLOWED),
                            'favourites'      => new external_multiple_structure(
                                                            new external_single_structure(
                                                                array(
                                                                    'id'       => new external_value(PARAM_NUMBER, 'favourite user Id', VALUE_OPTIONAL),
                                                                    'username' => new external_value(PARAM_RAW, 'favourite username', VALUE_OPTIONAL),
                                                                ), 'User favourites')
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
                            'shortname' => new external_value(PARAM_SAFEDIR, 'Favourites shorname', VALUE_DEFAULT, 'favourites', NULL_NOT_ALLOWED),
                            'userid'    => new external_value(PARAM_INT, 'user id', VALUE_OPTIONAL),
                            'username'  => new external_value(PARAM_RAW, 'Username of the favourites owner', VALUE_OPTIONAL),
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
                throw new WebserviceInvalidParameterException('get_favourites | ' . get_string('notauthforuseridinstitution', 'auth.webservice', $user['userid'], $auth_instance->institution));
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
                            'id'              => new external_value(PARAM_NUMBER, 'ID of the favourites owner'),
                            'username'        => new external_value(PARAM_RAW, 'Username of the favourites owner'),
                            'shortname'       => new external_value(PARAM_SAFEDIR, 'Favourites shorname'),
                            'institution'     => new external_value(PARAM_SAFEDIR, 'Mahara institution'),
                            'favourites'      => new external_multiple_structure(
                                                            new external_single_structure(
                                                                array(
                                                                    'id'       => new external_value(PARAM_NUMBER, 'favourite user Id', VALUE_OPTIONAL),
                                                                    'username' => new external_value(PARAM_RAW, 'favourite username', VALUE_OPTIONAL),
                                                                ), 'User favourites')
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
                'shortname' => new external_value(PARAM_SAFEDIR, 'Favourites shorname', VALUE_DEFAULT, 'favourites', NULL_NOT_ALLOWED),
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
}
