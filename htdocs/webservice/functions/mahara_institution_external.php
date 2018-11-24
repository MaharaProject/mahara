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
 * Class container for core Mahara institution related API calls
 */
class mahara_institution_external extends external_api {

    /**
     * Check that a user exists
     *
     * @param array $user array('id' => .., 'username' => ..)
     * @return array() of user
     */
    private static function checkuser($user) {
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
        else {
            throw new WebserviceInvalidParameterException(get_string('musthaveid', 'auth.webservice'));
        }
        // now get the user
        if ($user = get_user($id)) {
            return $user;
        }
        else {
            throw new WebserviceInvalidParameterException(get_string('invaliduserid', 'auth.webservice', $id));
        }
    }

    /**
     * parameter definition for input of add_members method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function add_members_parameters() {
        return new external_function_parameters(
                    array(
                            'institution'     => new external_value(PARAM_TEXT, 'Mahara institution'),
                            'users'           => new external_multiple_structure(
                                                    new external_single_structure(
                                                        array(
                                                                'id'              => new external_value(PARAM_NUMBER, 'ID of the favourites owner', VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                                                                'username'        => new external_value(PARAM_RAW, 'Username of the favourites owner', VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                                                                )
                                                            )
                                                        )
                                                    )
                                                );
    }

    /**
     * Add one or more members to an institution
     *
     * @param string $institution
     * @param array $users
     */
    public static function add_members($institution, $users) {
        global $USER, $WEBSERVICE_INSTITUTION;
        self::check_oauth();

        $params = array('institution' => $institution, 'users' => $users);
        $params = self::validate_parameters(self::add_members_parameters(), $params);

        if (!$USER->get('admin') && !$USER->is_institutional_admin()) {
            throw new AccessDeniedException("Institution::add_members | " . get_string('accessdenied', 'auth.webservice'));
        }
        // check the institution is allowed
        if (!$USER->can_edit_institution($params['institution'])) {
            throw new WebserviceInvalidParameterException('add_members | ' . get_string('accessdeniedforinst', 'auth.webservice', $params['institution']));
        }
        db_begin();
        $userids = array();
        foreach ($params['users'] as $user) {
            $dbuser = self::checkuser($user);
            // Make sure auth is valid
            if (!$authinstance = get_record('auth_instance', 'id', $dbuser->authinstance, 'active', 1)) {
                throw new WebserviceInvalidParameterException(get_string('invalidauthtype', 'auth.webservice', $dbuser->authinstance));
            }
            // check the institution is allowed
            // basic check authorisation to edit for the current institution
            if (!$USER->can_edit_institution($authinstance->institution)) {
                throw new WebserviceInvalidParameterException('add_members | ' . get_string('accessdeniedforinstuser', 'auth.webservice', $authinstance->institution, $dbuser->id));
            }
            $userids[]= $dbuser->id;
        }
        $institution = new Institution($params['institution']);
        $maxusers = $institution->maxuseraccounts;
        if (!empty($maxusers)) {
            $members = $institution->countMembers();
            if ($members + count($userids) > $maxusers) {
                throw new AccessDeniedException("Institution::add_members | " . get_string('institutionuserserrortoomanyinvites', 'admin'));
            }
        }
        $institution->add_members($userids);
        db_commit();

        return null;
    }

    /**
     * parameter definition for output of add_members method
     */
    public static function add_members_returns() {
        return null;
    }

    /**
     * parameter definition for input of invite_members method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function invite_members_parameters() {
        return new external_function_parameters(
                array(
                        'institution'     => new external_value(PARAM_TEXT, 'Mahara institution'),
                        'users'           => new external_multiple_structure(
                                                new external_single_structure(
                                                    array(
                                                            'id'              => new external_value(PARAM_NUMBER, 'ID of the favourites owner', VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                                                            'username'        => new external_value(PARAM_RAW, 'Username of the favourites owner', VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                                                        )
                                                    )
                                                )
                                            )
                                        );
    }

    /**
     * Invite one or more users to an institution
     *
     * @param string $institution
     * @param array $users
     */
    public static function invite_members($institution, $users) {
        global $USER, $WEBSERVICE_INSTITUTION;
        self::check_oauth();

        $params = array('institution' => $institution, 'users' => $users);
        $params = self::validate_parameters(self::invite_members_parameters(), $params);

        if (!$USER->get('admin') && !$USER->is_institutional_admin()) {
            throw new AccessDeniedException("Institution::invite_members | " . get_string('accessdenied', 'auth.webservice'));
        }
        // check the institution is allowed
        if (!$USER->can_edit_institution($params['institution'])) {
            throw new WebserviceInvalidParameterException('invite_members | ' . get_string('accessdeniedforinst', 'auth.webservice', $params['institution']));
        }
        db_begin();
        $userids = array();
        foreach ($params['users'] as $user) {
            $dbuser = self::checkuser($user);

            // Make sure auth is valid
            if (!$authinstance = get_record('auth_instance', 'id', $dbuser->authinstance, 'active', 1)) {
                throw new WebserviceInvalidParameterException('invite_members | ' . get_string('invalidauthtype', 'auth.webservice', $dbuser->authinstance));
            }
            // check the institution is allowed
            // basic check authorisation to edit for the current institution
            if (!$USER->can_edit_institution($authinstance->institution)) {
                throw new WebserviceInvalidParameterException('invite_members | ' . get_string('accessdeniedforinstuser', 'auth.webservice', $authinstance->institution, $dbuser->id));
            }
            $userids[]= $dbuser->id;
        }
        $institution = new Institution($params['institution']);
        $maxusers = $institution->maxuseraccounts;
        if (!empty($maxusers)) {
            if ($members + $institution->countInvites() + count($userids) > $maxusers) {
                throw new AccessDeniedException("Institution::invite_members | " . get_string('institutionuserserrortoomanyinvites', 'admin'));
            }
        }

        $institution->invite_users($userids);
        db_commit();

        return null;
    }

    /**
     * parameter definition for output of invite_members method
     */
    public static function invite_members_returns() {
        return null;
    }

    /**
     * parameter definition for input of remove_members method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function remove_members_parameters() {
        return new external_function_parameters(
                    array(
                            'institution'     => new external_value(PARAM_TEXT, 'Mahara institution'),
                            'users'           => new external_multiple_structure(
                                                    new external_single_structure(
                                                        array(
                                                                'id'              => new external_value(PARAM_NUMBER, 'ID of the favourites owner', VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                                                                'username'        => new external_value(PARAM_RAW, 'Username of the favourites owner', VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                                                            )
                                                        )
                                                    )
                                                )
                                            );
    }

    /**
     * remove one or more users from an institution
     *
     * @param string $institution
     * @param array $users
     */
    public static function remove_members($institution, $users) {
        global $USER, $WEBSERVICE_INSTITUTION;
        self::check_oauth();

        $params = array('institution' => $institution, 'users' => $users);
        $params = self::validate_parameters(self::remove_members_parameters(), $params);

        if (!$USER->get('admin') && !$USER->is_institutional_admin()) {
            throw new AccessDeniedException("Institution::remove_members | " . get_string('accessdenied', 'auth.webservice'));
        }
        // check the institution is allowed
        if (!$USER->can_edit_institution($params['institution'])) {
            throw new WebserviceInvalidParameterException('remove_members | ' . get_string('accessdeniedforinst', 'auth.webservice', $params['institution']));
        }
        db_begin();
        $userids = array();
        foreach ($params['users'] as $user) {
            $dbuser = self::checkuser($user);

            // Make sure auth is valid
            if (!$authinstance = get_record('auth_instance', 'id', $dbuser->authinstance, 'active', 1)) {
                throw new WebserviceInvalidParameterException('remove_members | ' . get_string('invalidauthtype', 'auth.webservice', $dbuser->authinstance));
            }

            // check the institution is allowed
            // basic check authorisation to edit for the current institution
            if (!$USER->can_edit_institution($authinstance->institution)) {
                throw new WebserviceInvalidParameterException('remove_members | ' . get_string('accessdeniedforinstuser', 'auth.webservice', $authinstance->institution, $dbuser->id));
            }
            $userids[]= $dbuser->id;
        }
        $institution = new Institution($params['institution']);
        $institution->removeMembers($userids);
        db_commit();

        return null;
    }

    /**
     * parameter definition for output of remove_members method
     */
    public static function remove_members_returns() {
        return null;
    }

    /**
     * parameter definition for input of decline_members method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function decline_members_parameters() {
        return new external_function_parameters(
                    array(
                            'institution'     => new external_value(PARAM_TEXT, 'Mahara institution'),
                            'users'           => new external_multiple_structure(
                                                    new external_single_structure(
                                                        array(
                                                                'id'              => new external_value(PARAM_NUMBER, 'ID of the favourites owner', VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                                                                'username'        => new external_value(PARAM_RAW, 'Username of the favourites owner', VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                                                            )
                                                        )
                                                    )
                                                )
                                            );
    }

    /**
     * decline one or more users request for membership to an institution
     *
     * @param string $institution
     * @param array $users
     */
    public static function decline_members($institution, $users) {
        global $USER, $WEBSERVICE_INSTITUTION;
        self::check_oauth();

        $params = array('institution' => $institution, 'users' => $users);
        $params = self::validate_parameters(self::decline_members_parameters(), $params);

        if (!$USER->get('admin') && !$USER->is_institutional_admin()) {
            throw new AccessDeniedException("Institution::decline_members | " . get_string('accessdenied', 'auth.webservice'));
        }
        // check the institution is allowed
        if (!$USER->can_edit_institution($params['institution'])) {
            throw new WebserviceInvalidParameterException('decline_members | ' . get_string('accessdeniedforinst', 'auth.webservice', $params['institution']));
        }
        db_begin();
        $userids = array();
        foreach ($params['users'] as $user) {
            $dbuser = self::checkuser($user);

            // Make sure auth is valid
            if (!$authinstance = get_record('auth_instance', 'id', $dbuser->authinstance, 'active', 1)) {
                throw new WebserviceInvalidParameterException('decline_members | ' . get_string('invalidauthtype', 'auth.webservice', $dbuser->authinstance));
            }

            // check the institution is allowed
            // basic check authorisation to edit for the current institution
            if (!$USER->can_edit_institution($authinstance->institution)) {
                throw new WebserviceInvalidParameterException('decline_members | ' . get_string('accessdeniedforinstuser', 'auth.webservice', $authinstance->institution, $dbuser->id));
            }
            $userids[]= $dbuser->id;
        }
        $institution = new Institution($params['institution']);
        $institution->decline_requests($userids);
        db_commit();

        return null;
    }

    /**
     * parameter definition for output of decline_members method
     */
    public static function decline_members_returns() {
        return null;
    }

    /**
     * parameter definition for input of get_members method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_members_parameters() {

        return new external_function_parameters(
                    array(
                            'institution'     => new external_value(PARAM_TEXT, 'Mahara institution'),
                    )
                );
    }

    /**
     * Get institution members
     *
     * @param string $institution
     * @return array An array of arrays describing users
     */
    public static function get_members($institution) {
        global $USER, $WEBSERVICE_INSTITUTION;
        self::check_oauth();

        // Do basic automatic PARAM checks on incoming data, using params description
        $params = self::validate_parameters(self::get_members_parameters(), array('institution' => $institution));
        if (!$USER->get('admin') && !$USER->is_institutional_admin()) {
            throw new AccessDeniedException("Institution::get_members | " . get_string('accessdenied', 'auth.webservice'));
        }
        // check the institution is allowed
        if (!$USER->can_edit_institution($params['institution'])) {
            throw new WebserviceInvalidParameterException('get_members | ' . get_string('accessdeniedforinst', 'auth.webservice', $params['institution']));
        }
        $institution = new Institution($params['institution']);
        $institution->member = true; // Only fetch the users belonging to the institution indicated in $params['institution']
        $data = institutional_admin_user_search('', $institution, 0);
        $users = array();

        if (!empty($data['data'])) {
            foreach ($data['data'] as $user) {
                $users[] = array('id'=> $user['id'], 'username'=>$user['username']);
            }
        }
        return $users;
    }

    /**
     * parameter definition for output of get_members method
     *
     * Returns description of method result value
     * @return external_multiple_structure
     */
    public static function get_members_returns() {
        return new external_multiple_structure(
                    new external_single_structure(
                    array(
                            'id'              => new external_value(PARAM_NUMBER, 'ID of the user'),
                            'username'        => new external_value(PARAM_RAW, 'Username policy is defined in Mahara security config'),
                        )
                    )
                );
    }

    /**
     * parameter definition for input of get_requests method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_requests_parameters() {

        return new external_function_parameters(
            array(
                  'institution'     => new external_value(PARAM_TEXT, 'Mahara institution'),
            )
        );
    }

    /**
     * Get institution requests
     *
     * @param string $institution
     * @return array An array of arrays describing users
     */
    public static function get_requests($institution) {
        global $USER, $WEBSERVICE_INSTITUTION;
        self::check_oauth();

        // Do basic automatic PARAM checks on incoming data, using params description
        $params = self::validate_parameters(self::get_members_parameters(), array('institution'=>$institution));
        if (!$USER->get('admin') && !$USER->is_institutional_admin()) {
            throw new AccessDeniedException("Institution::get_requests | " . get_string('accessdenied', 'auth.webservice'));
        }
        // check the institution is allowed
        if (!$USER->can_edit_institution($params['institution'])) {
            throw new WebserviceInvalidParameterException('get_requests | ' . get_string('accessdeniedforinst', 'auth.webservice', $params['institution']));
        }

        $users = array();
        $dbrequests = get_records_array('usr_institution_request', 'institution', $params['institution']);

        if (!empty($dbrequests)) {
            foreach ($dbrequests as $user) {
                $dbuser = get_record('usr', 'id', $user->usr);
                $users[] = array('id'=> $user->usr, 'username'=>$dbuser->username);
            }
        }
        return $users;
    }

    /**
     * Check if OAuth is enabled and reject
     * @throws Exception
     */
    private static function check_oauth() {
        global $WEBSERVICE_OAUTH_USER;
        if ($WEBSERVICE_OAUTH_USER) {
            throw new MaharaException(get_string('nooauth', 'auth.webservice'));
        }
    }

    /**
     * parameter definition for output of get_requests method
     *
     * Returns description of method result value
     * @return external_multiple_structure
     */
    public static function get_requests_returns() {
        return new external_multiple_structure(
                    new external_single_structure(
                        array(
                                'id'              => new external_value(PARAM_NUMBER, 'ID of the user'),
                                'username'        => new external_value(PARAM_RAW, 'Username policy is defined in Mahara security config'),
                            )
                        )
                    );
    }
}
