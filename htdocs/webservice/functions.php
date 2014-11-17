<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2009 Moodle Pty Ltd (http://moodle.com)
 * Copyright (C) 2011 Catalyst IT Ltd (http://www.catalyst.net.nz)
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
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
            'icqnumber',
            'msnnumber',
            'aimscreenname',
            'yahoochat',
            'skypeusername',
            'jabberusername',
            'occupation',
            'industry',
        );

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
                            'username'        => new external_value(PARAM_RAW, 'Username policy is defined in Mahara security config'),
                            'password'        => new external_value(PARAM_RAW, 'Plain text password consisting of any characters'),
                            'firstname'       => new external_value(PARAM_NOTAGS, 'The first name(s) of the user'),
                            'lastname'        => new external_value(PARAM_NOTAGS, 'The family name of the user'),
                            'email'           => new external_value(PARAM_EMAIL, 'A valid and unique email address'),
                            'institution'     => new external_value(PARAM_SAFEDIR, 'Mahara institution', VALUE_DEFAULT, 'mahara', NULL_NOT_ALLOWED),
                            'auth'            => new external_value(PARAM_SAFEDIR, 'Auth plugins include manual, ldap, imap, etc', VALUE_DEFAULT, 'internal', NULL_NOT_ALLOWED),
                            'quota'           => new external_value(PARAM_INTEGER, 'Option storage quota', VALUE_OPTIONAL),
                            'forcepasswordchange' => new external_value(PARAM_INTEGER, 'Boolean 1/0 for forcing password change on first login', VALUE_DEFAULT, '0'),
                            'studentid'       => new external_value(PARAM_RAW, 'An arbitrary ID code number for the student', VALUE_DEFAULT, ''),
                            'remoteuser'      => new external_value(PARAM_RAW, 'Remote user Id', VALUE_DEFAULT, ''),
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
                            'aimscreenname'   => new external_value(PARAM_ALPHANUMEXT, 'AIM screen name', VALUE_OPTIONAL),
                            'icqnumber'       => new external_value(PARAM_ALPHANUMEXT, 'ICQ Number', VALUE_OPTIONAL),
                            'msnnumber'       => new external_value(PARAM_ALPHANUMEXT, 'MSN Number', VALUE_OPTIONAL),
                            'yahoochat'       => new external_value(PARAM_ALPHANUMEXT, 'Yahoo chat', VALUE_OPTIONAL),
                            'skypeusername'   => new external_value(PARAM_ALPHANUMEXT, 'Skype username', VALUE_OPTIONAL),
                            'jabberusername'  => new external_value(PARAM_RAW, 'Jabber/XMPP username', VALUE_OPTIONAL),
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
     * @return array An array of arrays
     */
    public static function create_users($users) {
        global $USER, $WEBSERVICE_INSTITUTION, $WEBSERVICE_OAUTH_USER;

        // Do basic automatic PARAM checks on incoming data, using params description
        // If any problems are found then exceptions are thrown with helpful error messages
        $params = self::validate_parameters(self::create_users_parameters(), array('users'=>$users));
        db_begin();
        $userids = array();
        foreach ($params['users'] as $user) {
            // Make sure that the username doesn't already exist
            if (get_record('usr', 'username', $user['username'])) {
                throw new WebserviceInvalidParameterException(get_string('usernameexists', 'auth.webservice') . $user['username']);
            }

            // check the institution is allowed
            // basic check authorisation to edit for the current institution
            if (!$USER->can_edit_institution($user['institution'])) {
                throw new WebserviceInvalidParameterException('create_users: ' . get_string('accessdeniedforinst', 'auth.webservice') . $user['institution']);
            }

            // Make sure auth is valid
            if (!$authinstance = get_record('auth_instance', 'institution', $user['institution'], 'authname', $user['auth'])) {
                throw new WebserviceInvalidParameterException(get_string('invalidauthtype', 'auth.webservice') . $user['institution'] . '/' . $user['auth']);
            }

            $institution = new Institution($authinstance->institution);

            $maxusers = $institution->maxuseraccounts;
            if (!empty($maxusers)) {
                $members = count_records_sql('
                    SELECT COUNT(*) FROM {usr} u INNER JOIN {usr_institution} i ON u.id = i.usr
                    WHERE i.institution = ? AND u.deleted = ?', array($institution->name, 0));
                if ($members + 1 > $maxusers) {
                    throw new WebserviceInvalidParameterException(get_string('instexceedmax', 'auth.webservice') . $institution->name);
                }
            }

            // build up the user object to create
            $new_user = new StdClass;
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

            if (isset($user['studentid'])) {
                $new_user->studentid = $user['studentid'];
            }
            if (isset($user['preferredname'])) {
                $new_user->preferredname = $user['preferredname'];
            }

            // handle profile fields
            $profilefields = new StdClass;
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
                            'id'              => new external_value(PARAM_NUMBER, 'ID of the favourites owner', VALUE_OPTIONAL),
                            'username'        => new external_value(PARAM_RAW, 'Username of the favourites owner', VALUE_OPTIONAL),
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
                throw new WebserviceInvalidParameterException(get_string('invalidauthtype', 'auth.webservice') . $user->authinstance);
            }
            // check the institution is allowed
            // basic check authorisation to edit for the current institution
            if (!$USER->can_edit_institution($authinstance->institution)) {
                throw new WebserviceInvalidParameterException('delete_users: ' . get_string('accessdeniedforinst', 'auth.webservice') . $authinstance->institution . ' on user: ' . $user->id);
            }

            // only allow deletion of users that have not signed in
            if (!empty($user->lastlogin) && !$user->suspendedcusr) {
                throw new WebserviceInvalidParameterException('delete_users: ' . get_string('cannotdeleteaccount', 'auth.webservice') . $user->id);
            }

            // must not allow deleting of admins or self!!!
            if ($user->admin) {
                throw new MaharaException('useradminodelete', 'error');
            }
            if ($USER->get('id') == $user->id) {
                throw new MaharaException('usernotdeletederror', 'error');
            }
            delete_user($user->id);
        }
        db_commit();

        return null;
    }

   /**
    * parameter definition for output of delete_users method
    *
    * Returns description of method result value
    * @return external_description
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
                            'id'              => new external_value(PARAM_NUMBER, 'ID of the user', VALUE_OPTIONAL),
                            'username'        => new external_value(PARAM_RAW, 'Username policy is defined in Mahara security config', VALUE_OPTIONAL),
                            'password'        => new external_value(PARAM_RAW, 'Plain text password consisting of any characters', VALUE_OPTIONAL),
                            'firstname'       => new external_value(PARAM_NOTAGS, 'The first name(s) of the user', VALUE_OPTIONAL),
                            'lastname'        => new external_value(PARAM_NOTAGS, 'The family name of the user', VALUE_OPTIONAL),
                            'email'           => new external_value(PARAM_EMAIL, 'A valid and unique email address', VALUE_OPTIONAL),
                            'institution'     => new external_value(PARAM_TEXT, 'Mahara institution', VALUE_OPTIONAL),
                            'auth'            => new external_value(PARAM_TEXT, 'Auth plugins include manual, ldap, imap, etc', VALUE_OPTIONAL),
                            'quota'           => new external_value(PARAM_INTEGER, 'Option storage quota', VALUE_OPTIONAL),
                            'forcepasswordchange' => new external_value(PARAM_INTEGER, 'Boolean 1/0 for forcing password change on first login', VALUE_OPTIONAL),
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
                            'aimscreenname'   => new external_value(PARAM_ALPHANUMEXT, 'AIM screen name', VALUE_OPTIONAL),
                            'icqnumber'       => new external_value(PARAM_ALPHANUMEXT, 'ICQ Number', VALUE_OPTIONAL),
                            'msnnumber'       => new external_value(PARAM_ALPHANUMEXT, 'MSN Number', VALUE_OPTIONAL),
                            'yahoochat'       => new external_value(PARAM_ALPHANUMEXT, 'Yahoo chat', VALUE_OPTIONAL),
                            'skypeusername'   => new external_value(PARAM_ALPHANUMEXT, 'Skype username', VALUE_OPTIONAL),
                            'jabberusername'  => new external_value(PARAM_RAW, 'Jabber/XMPP username', VALUE_OPTIONAL),
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
                throw new WebserviceInvalidParameterException('update_users: ' . get_string('nousernameorid', 'auth.webservice'));
            }
            if (empty($dbuser)) {
                throw new WebserviceInvalidParameterException('update_users: ' . get_string('invaliduser', 'auth.webservice') . $user['id'] . '/' . $user['username']);
            }

            // Make sure auth is valid
            if (!$authinstance = get_record('auth_instance', 'id', $dbuser->authinstance)) {
                throw new WebserviceInvalidParameterException(get_string('invalidauthtype', 'auth.webservice') . $dbuser->authinstance);
            }
            // check for changed authinstance
            if (isset($user['auth']) && isset($user['institution'])) {
                $ai = get_record('auth_instance', 'institution', $user['institution'], 'authname', $user['auth']);
                if (empty($ai)) {
                    throw new WebserviceInvalidParameterException('update_users: ' . get_string('invalidauthtype', 'auth.webservice') . $user['auth'] . ' on user: ' . $dbuser->id);
                }
                $authinstance = $ai;
            }
            else if (isset($user['auth'])) {
                throw new WebserviceInvalidParameterException('update_users: ' . get_string('mustsetauth', 'auth.webservice') . $dbuser->id);
            }

            // check the institution is allowed
            // basic check authorisation to edit for the current institution
            if (!$USER->can_edit_institution($authinstance->institution)) {
                throw new WebserviceInvalidParameterException('update_users: ' . get_string('accessdeniedforinst', 'auth.webservice') . $authinstance->institution . ' on user: ' . $dbuser->id);
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

            $profilefields = new StdClass;
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
            update_user($updated_user, $profilefields, $remoteuser);
        }
        db_commit();

        return null;
    }

   /**
    * parameter definition for output of update_users method
    *
    * Returns description of method result value
    * @return external_description
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
                            'id'              => new external_value(PARAM_NUMBER, 'ID of the favourites owner', VALUE_OPTIONAL),
                            'username'        => new external_value(PARAM_RAW, 'Username of the favourites owner', VALUE_OPTIONAL),
                            'remoteuser'      => new external_value(PARAM_RAW, 'Remote username of the favourites owner', VALUE_OPTIONAL),
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
                throw new WebserviceInvalidParameterException(get_string('invalidusername', 'auth.webservice') . $user['username']);
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
                throw new WebserviceInvalidParameterException(get_string('invalidremoteusername', 'auth.webservice') . $user['username']);
            }
            $id = $dbuser->id;
        }
        else {
            throw new WebserviceInvalidParameterException(get_string('musthaveid', 'auth.webservice'));
        }
        // now get the user
        if ($user = get_user($id)) {
            if ($user->deleted) {
                throw new WebserviceInvalidParameterException(get_string('invaliduser', 'auth.webservice') . $id);
            }
            // get the remoteuser
            $user->remoteuser = get_field('auth_remote_user', 'remoteusername', 'authinstance', $user->authinstance, 'localusr', $user->id);
            foreach (array('jabberusername', 'introduction', 'country', 'city', 'address',
                           'town', 'homenumber', 'businessnumber', 'mobilenumber', 'faxnumber',
                           'officialwebsite', 'personalwebsite', 'blogaddress', 'aimscreenname',
                           'icqnumber', 'msnnumber', 'yahoochat', 'skypeusername', 'jabberusername',
                           'occupation', 'industry') as $attr) {
                if ($art = get_record('artefact', 'artefacttype', $attr, 'owner', $user->id)) {
                    $user->{$attr} = $art->title;
                }
            }
            return $user;
        }
        else {
            throw new WebserviceInvalidParameterException(get_string('invaliduser', 'auth.webservice') . $id);
        }
    }

    /**
     * Get user information for one or more users
     *
     * @param array $users  array of users
     * @return array An array of arrays describing users
     */
    public static function get_users_by_id($users) {
        global $WEBSERVICE_INSTITUTION, $WEBSERVICE_OAUTH_USER, $USER;

        $params = self::validate_parameters(self::get_users_by_id_parameters(),
                array('users'=>$users));

        // if this is a get all users - then lets get them all
        if (empty($params['users'])) {
            $params['users'] = array();
            $dbusers = get_records_sql_array('SELECT u.id AS id FROM {usr} u
                                                INNER JOIN {auth_instance} ai ON u.authinstance = ai.id
                                                LEFT JOIN {usr_institution} ui ON u.id = ui.usr AND ui.institution = ?
                                                WHERE u.deleted = ? AND (ai.institution = ?
                                                                      OR ui.institution = ?)', array($WEBSERVICE_INSTITUTION, 0, $WEBSERVICE_INSTITUTION, $WEBSERVICE_INSTITUTION));
            if ($dbusers) {
                foreach ($dbusers as $dbuser) {
                    // eliminate bad uid
                    if ($dbuser->id == 0) {
                        continue;
                    }
                    $params['users'][] = array('id' => $dbuser->id);
                }
            }
        }

        //TODO: check if there is any performance issue: we do one DB request to retrieve
        //  all user, then for each user the profile_load_data does at least two DB requests
        $users = array();
        foreach ($params['users'] as $user) {
            $users[]= self::checkuser($user);
        }

        $result = array();
        foreach ($users as $user) {
            if (empty($user->deleted)) {
                // check the institution
                if (!mahara_external_in_institution($user, $WEBSERVICE_INSTITUTION)) {
                    throw new WebserviceInvalidParameterException(get_string('notauthforuserid', 'auth.webservice') . $user->id . ' institution: ' . $WEBSERVICE_INSTITUTION);
                }

                $auth_instance = get_record('auth_instance', 'id', $user->authinstance);

                $userarray = array();
               //we want to return an array not an object
                /// now we transfer all profile_field_xxx into the customfields
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
                    'aimscreenname'   => new external_value(PARAM_ALPHANUMEXT, 'AIM screen name'),
                    'icqnumber'       => new external_value(PARAM_ALPHANUMEXT, 'ICQ Number'),
                    'msnnumber'       => new external_value(PARAM_ALPHANUMEXT, 'MSN Number'),
                    'yahoochat'       => new external_value(PARAM_ALPHANUMEXT, 'Yahoo chat'),
                    'skypeusername'   => new external_value(PARAM_ALPHANUMEXT, 'Skype username'),
                    'jabberusername'  => new external_value(PARAM_RAW, 'Jabber/XMPP username'),
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
        return new external_function_parameters(array());
    }

    /**
     * Get user information for all users
     *
     * @param array $userids  array of user ids
     * @return array An array of arrays describing users
     */
    public static function get_users() {
        return self::get_users_by_id(array());
    }

    /**
     * parameter definition for output of get_users method
     *
     * Returns description of method result value
     * @return external_description
     */
    public static function get_users_returns() {
        return self::get_users_by_id_returns();
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
     * @param array $userids  array of user ids
     * @return array An array of arrays describing users
     */
    public static function get_my_user() {
        global $USER;
        return array_shift(self::get_users_by_id(array(array('id' => $USER->get('id')))));
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
                    'aimscreenname'   => new external_value(PARAM_ALPHANUMEXT, 'AIM screen name'),
                    'icqnumber'       => new external_value(PARAM_ALPHANUMEXT, 'ICQ Number'),
                    'msnnumber'       => new external_value(PARAM_ALPHANUMEXT, 'MSN Number'),
                    'yahoochat'       => new external_value(PARAM_ALPHANUMEXT, 'Yahoo chat'),
                    'skypeusername'   => new external_value(PARAM_ALPHANUMEXT, 'Skype username'),
                    'jabberusername'  => new external_value(PARAM_RAW, 'Jabber/XMPP username'),
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
                            'id'              => new external_value(PARAM_NUMBER, 'ID of the favourites owner', VALUE_OPTIONAL),
                            'username'        => new external_value(PARAM_RAW, 'Username of the favourites owner', VALUE_OPTIONAL),
                            'shortname'       => new external_value(PARAM_SAFEDIR, 'Favourites shorname', VALUE_DEFAULT, 'favourites', NULL_NOT_ALLOWED),
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
                throw new WebserviceInvalidParameterException('update_favourites: ' . get_string('invalidauthtype', 'auth.webservice') . $dbuser->authinstance);
            }
            // check the institution is allowed
            // basic check authorisation to edit for the current institution
            if (!$USER->can_edit_institution($authinstance->institution)) {
                throw new WebserviceInvalidParameterException('update_favourites: ' . get_string('accessdeniedforinst', 'auth.webservice') . $authinstance->institution . ' on user: ' . $dbuser->id);
            }

            // are we allowed to delete for this institution
            if ($WEBSERVICE_INSTITUTION != $user['institution'] || !$USER->can_edit_institution($user['institution'])) {
                throw new WebserviceInvalidParameterException('update_favourites: ' . get_string('accessdeniedforinst', 'auth.webservice') . $user['institution']);
            }

            // check that the favourites exist and we are allowed to administer them
            $favourites = array();
            foreach ($user['favourites'] as $favourite) {
                $dbuser = self::checkuser($favourite);
                // Make sure auth is valid
                if (!$authinstance = get_record('auth_instance', 'id', $dbuser->authinstance)) {
                    throw new WebserviceInvalidParameterException('update_favourites: ' . get_string('invalidauthtype', 'auth.webservice') . $dbuser->authinstance);
                }

                // check the institution is allowed
                // basic check authorisation to edit for the current institution of the user
                if (!$USER->can_edit_institution($authinstance->institution)) {
                    throw new WebserviceInvalidParameterException('update_favourites: ' . get_string('accessdeniedforinst', 'auth.webservice') . $authinstance->institution . ' on user: ' . $dbuser->username);
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
    *
    * Returns description of method result value
    * @return external_description
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
                throw new WebserviceInvalidParameterException('get_favourites: ' . get_string('notauthforuserid', 'auth.webservice') . $user['userid'] . ' institution: ' . $auth_instance->institution);
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
     */
    public static function get_all_favourites($shortname) {
        global $WEBSERVICE_INSTITUTION, $WEBSERVICE_OAUTH_USER;

        $params = self::validate_parameters(self::get_all_favourites_parameters(), array('shortname' => $shortname));

        $dbfavourites = get_records_sql_array('SELECT * from {favorite} WHERE shortname = ? AND institution = ?',array($shortname, $WEBSERVICE_INSTITUTION));
        if (empty($dbfavourites)) {
            throw new WebserviceInvalidParameterException('get_favourites: ' . get_string('invalidfavourite', 'auth.webservice') . $shortname . '/' . $WEBSERVICE_INSTITUTION);
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

/**
 * Class container for core Mahara group related API calls
 */
class mahara_group_external extends external_api {

    // possible membership roles
    private static $member_roles = array('admin', 'tutor', 'member');

    /**
     * parameter definition for input of create_groups method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function create_groups_parameters() {

        $group_types = group_get_grouptypes();
        $group_edit_roles = array_keys(group_get_editroles_options());
        return new external_function_parameters(
        array(
                'groups' => new external_multiple_structure(
                                new external_single_structure(
                                    array(
                                        'name'            => new external_value(PARAM_RAW, 'Group name'),
                                        'shortname'       => new external_value(PARAM_RAW, 'Group shortname for API only controlled groups', VALUE_OPTIONAL),
                                        'description'     => new external_value(PARAM_NOTAGS, 'Group description'),
                                        'institution'     => new external_value(PARAM_TEXT, 'Mahara institution - required for API controlled groups', VALUE_OPTIONAL),
                                        'grouptype'       => new external_value(PARAM_ALPHANUMEXT, 'Group type: ' . implode(',', $group_types)),
                                        'category'        => new external_value(PARAM_TEXT, 'Group category - the title of an existing group category', VALUE_OPTIONAL),
                                        'editroles'       => new external_value(PARAM_ALPHANUMEXT, 'Edit roles allowed: ' . implode(',', $group_edit_roles), VALUE_OPTIONAL),
                                        'open'            => new external_value(PARAM_INTEGER, 'Boolean 1/0 open - Users can join the group without approval from group administrators', VALUE_DEFAULT, '0'),
                                        'controlled'      => new external_value(PARAM_INTEGER, 'Boolean 1/0 controlled - Group administrators can add users to the group without their consent, and members cannot choose to leave', VALUE_DEFAULT, '0'),
                                        'request'         => new external_value(PARAM_INTEGER, 'Boolean 1/0 request - Users can send membership requests to group administrators', VALUE_DEFAULT, '0'),
                                        'submitpages'     => new external_value(PARAM_INTEGER, 'Boolean 1/0 submitpages - Members can submit pages to the group', VALUE_DEFAULT),
                                        'public'          => new external_value(PARAM_INTEGER, 'Boolean 1/0 public group', VALUE_DEFAULT),
                                        'viewnotify'      => new external_value(PARAM_INTEGER, 'Boolean 1/0 for Shared page notifications', VALUE_DEFAULT),
                                        'usersautoadded'  => new external_value(PARAM_INTEGER, 'Boolean 1/0 for auto-adding users', VALUE_DEFAULT),
                                        'members'         => new external_multiple_structure(
                                                                new external_single_structure(
                                                                    array(
                                                                        'id'       => new external_value(PARAM_NUMBER, 'member user Id', VALUE_OPTIONAL),
                                                                        'username' => new external_value(PARAM_RAW, 'member username', VALUE_OPTIONAL),
                                                                        'role'     => new external_value(PARAM_ALPHANUMEXT, 'member role: ' . implode(', ', self::$member_roles))
                                                                        ), 'Group membership')
                                                            ),
                                    )
                            )
                    )
            )
        );
    }

    /**
     * Create one or more group
     *
     * @param array $groups  An array of groups to create.
     * @return array An array of arrays
     */
    public static function create_groups($groups) {
        global $USER, $WEBSERVICE_INSTITUTION;

        // Do basic automatic PARAM checks on incoming data, using params description
        $params = self::validate_parameters(self::create_groups_parameters(), array('groups' => $groups));
        db_begin();
        $groupids = array();
        foreach ($params['groups'] as $group) {
            // Make sure that the group doesn't already exist
            if (!empty($group['name'])) {
                // don't checked deleted as the real function doesn't
                if (get_record('group', 'name', $group['name'])) {
                    throw new WebserviceInvalidParameterException(get_string('groupexists', 'auth.webservice') . $group['name']);
                }
            }
            // special API controlled group creations
            else if (isset($group['shortname']) && strlen($group['shortname'])) {
                // check the institution is allowed
                if (isset($group['institution']) && strlen($group['institution'])) {
                    if ($WEBSERVICE_INSTITUTION != $group['institution']) {
                        throw new WebserviceInvalidParameterException('create_groups: ' . get_string('accessdeniedforinst', 'auth.webservice') . $group['institution'] . ' on group: ' . $group['name']);
                    }
                    if (!$USER->can_edit_institution($group['institution'])) {
                        throw new WebserviceInvalidParameterException('create_groups: ' . get_string('accessdeniedforinst', 'auth.webservice') . $group['institution'] . ' on group: ' . $group['name']);
                    }
                }
                else {
                    throw new WebserviceInvalidParameterException('create_groups: ' . get_string('instmustbeongroup', 'auth.webservice') . $group['name'] . '/' . $group['shortname']);
                }
                // does the group exist?
                if (get_record('group', 'shortname', $group['shortname'], 'institution', $group['institution'])) {
                    throw new WebserviceInvalidParameterException(get_string('groupexists', 'auth.webservice') . $group['shortname']);
                }
            }
            else {
                throw new WebserviceInvalidParameterException('create_groups: ' . get_string('noname', 'auth.webservice'));
            }

            // convert the category
            if (!empty($group['category'])) {
                $groupcategory = get_record('group_category','title', $group['category']);
                if (empty($groupcategory)) {
                    throw new WebserviceInvalidParameterException('create_groups: ' . get_string('catinvalid', 'auth.webservice') . $group['category']);
                }
                $group['category'] = $groupcategory->id;
            }

            // validate the join type combinations
            if ($group['open'] && $group['request']) {
                throw new WebserviceInvalidParameterException('create_groups: ' . get_string('invalidjointype', 'auth.webservice') . ' open+request');
            }
            if ($group['open'] && $group['controlled']) {
                throw new WebserviceInvalidParameterException('create_groups: ' . get_string('invalidjointype', 'auth.webservice') . ' open+controlled');
            }

            if (!$group['open'] && !$group['request'] && !$group['controlled']) {
                throw new WebserviceInvalidParameterException('create_groups: ' . get_string('correctjointype', 'auth.webservice'));
            }
            if (isset($group['editroles']) && !in_array($group['editroles'], array_keys(group_get_editroles_options()))) {
                throw new WebserviceInvalidParameterException('create_groups: ' . get_string('groupeditroles', 'auth.webservice', $group['editroles'], implode(', ', array_keys(group_get_editroles_options()))));
            }

            // check that the members exist and we are allowed to administer them
            $members = array($USER->get('id') => 'admin');
            foreach ($group['members'] as $member) {
                if (!empty($member['id'])) {
                    $dbuser = get_record('usr', 'id', $member['id'], 'deleted', 0);
                }
                else if (!empty($member['username'])) {
                    $dbuser = get_record('usr', 'username', $member['username'], 'deleted', 0);
                }
                else {
                    throw new WebserviceInvalidParameterException('create_groups: ' . get_string('nousernameorid', 'auth.webservice') . ' - group: ' . $group['name']);
                }
                if (empty($dbuser)) {
                    throw new WebserviceInvalidParameterException('create_groups: ' . get_string('invaliduser', 'auth.webservice') . $member['id'] . '/' . $member['username'] . ' - group: ' . $group['name']);
                }

                // check user is in this institution if this is an institution controlled group
                if ((isset($group['shortname']) && strlen($group['shortname'])) && (isset($group['institution']) && strlen($group['institution']))) {
                    if (!mahara_external_in_institution($dbuser, $WEBSERVICE_INSTITUTION)) {
                        throw new WebserviceInvalidParameterException(get_string('notauthforuserid', 'auth.webservice') . $dbuser->id . ' institution: ' . $WEBSERVICE_INSTITUTION . ' to group: ' . $group['shortname']);
                    }
                }
                else {
                    // Make sure auth is valid
                    if (!$authinstance = get_record('auth_instance', 'id', $dbuser->authinstance)) {
                        throw new WebserviceInvalidParameterException(get_string('invalidauthtype', 'auth.webservice') . $dbuser->authinstance);
                    }
                    // check the institution is allowed
                    // basic check authorisation to edit for the current institution of the user
                    if (!$USER->can_edit_institution($authinstance->institution)) {
                        throw new WebserviceInvalidParameterException('create_groups: ' . get_string('accessdeniedforinst', 'auth.webservice') . $authinstance->institution . ' on user: ' . $dbuser->username);
                    }
                }
                // check the specified role
                if (!in_array($member['role'], self::$member_roles)) {
                    throw new WebserviceInvalidParameterException('create_groups: ' .  get_string('invalidmemroles', 'auth.webservice', $member['role'], $dbuser->username));
                }
                $members[$dbuser->id]= $member['role'];
            }

            // set the basic elements
            $create = array(
                'shortname'      => (isset($group['shortname']) ? $group['shortname'] : null),
                'name'           => (isset($group['name']) ? $group['name'] : null),
                'description'    => $group['description'],
                'institution'    => (isset($group['institution']) ? $group['institution'] : null),
                'grouptype'      => $group['grouptype'],
                'members'        => $members,
            );

            // check for the rest
            foreach (array('category', 'open', 'controlled', 'request', 'submitpages', 'editroles',
                           'hidemembers', 'invitefriends', 'suggestfriends', 'hidden', 'quota',
                           'hidemembersfrommembers', 'public', 'usersautoadded', 'viewnotify',) as $attr) {
                if (isset($group[$attr]) && $group[$attr] !== false && $group[$attr] !== null && strlen("" . $group[$attr])) {
                    $create[$attr] = $group[$attr];
                }
            }

            // create the group
            $id = group_create($create);

            $groupids[] = array('id'=> $id, 'name'=> $group['name']);
        }
        db_commit();

        return $groupids;
    }

    /**
     * parameter definition for output of create_groups method
     *
     * Returns description of method result value
     * @return external_description
     */
    public static function create_groups_returns() {
        return new external_multiple_structure(
                            new external_single_structure(
                                array(
                                        'id'       => new external_value(PARAM_INT, 'group id'),
                                        'name'     => new external_value(PARAM_RAW, 'group name'),
                                )
                            )
                        );
    }

    /**
     * parameter definition for input of delete_groups method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function delete_groups_parameters() {
        return new external_function_parameters(
                    array(
                            'groups' => new external_multiple_structure(
                                            new external_single_structure(
                                                array(
                                                        'id'              => new external_value(PARAM_NUMBER, 'ID of the group', VALUE_OPTIONAL),
                                                        'name'            => new external_value(PARAM_RAW, 'Group name', VALUE_OPTIONAL),
                                                        'shortname'       => new external_value(PARAM_RAW, 'Group shortname for API only controlled groups', VALUE_OPTIONAL),
                                                        'institution'     => new external_value(PARAM_TEXT, 'Mahara institution - required for API controlled groups', VALUE_OPTIONAL),
                                                )
                                            )
                                        )
                                    )
                                );
    }

    /**
     * Delete one or more groups
     *
     * @param array $groups
     */
    public static function delete_groups($groups) {
        global $USER, $WEBSERVICE_INSTITUTION;

        $params = self::validate_parameters(self::delete_groups_parameters(), array('groups'=>$groups));

        db_begin();
        foreach ($params['groups'] as $group) {
            // Make sure that the group doesn't already exist
            if (!empty($group['id'])) {
                if (!$dbgroup = get_record('group', 'id', $group['id'], 'deleted', 0)) {
                    throw new WebserviceInvalidParameterException('delete_groups: ' . get_string('groupnotexist', 'auth.webservice') . $group['id']);
                }
            }
            else if (!empty($group['name'])) {
                if (!$dbgroup = get_record('group', 'name', $group['name'], 'deleted', 0)) {
                    throw new WebserviceInvalidParameterException('delete_groups: ' . get_string('groupnotexist', 'auth.webservice') . $group['name']);
                }
            }
            else if (!empty($group['shortname'])) {
                if (empty($group['institution'])) {
                    throw new WebserviceInvalidParameterException('delete_groups: ' . get_string('instmustset', 'auth.webservice') . $group['shortname']);
                }
                if (!$dbgroup = get_record('group', 'shortname', $group['shortname'], 'institution', $group['institution'], 'deleted', 0)) {
                    throw new WebserviceInvalidParameterException('delete_groups: ' . get_string('groupnotexist', 'auth.webservice') . $group['shortname'] . '/' . $group['institution']);
                }
            }
            else {
                throw new WebserviceInvalidParameterException('delete_groups: ' . get_string('nogroup', 'auth.webservice'));
            }

            // are we allowed to delete for this institution
            if (!empty($dbgroup->institution)) {
                if ($WEBSERVICE_INSTITUTION != $dbgroup->institution) {
                    throw new WebserviceInvalidParameterException('delete_groups: ' . get_string('accessdeniedforinst', 'auth.webservice') . $group['institution'] . ' on group: ' . $group['name']);
                }
                if (!$USER->can_edit_institution($dbgroup->institution)) {
                    throw new WebserviceInvalidParameterException('delete_groups: ' . get_string('accessdeniedforinst', 'auth.webservice') . $group['institution'] . ' on group: ' . $group['shortname']);
                }
            }

            // now do the delete
            group_delete($dbgroup->id);
        }
        db_commit();

        return null;
    }

    /**
     * parameter definition for output of delete_groups method
     *
     * Returns description of method result value
     * @return external_description
     */
    public static function delete_groups_returns() {
        return null;
    }

    /**
     * parameter definition for input of update_groups method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function update_groups_parameters() {

        $group_types = group_get_grouptypes();
        $group_edit_roles = array_keys(group_get_editroles_options());
        return new external_function_parameters(
                    array(
                        'groups' =>
                            new external_multiple_structure(
                                new external_single_structure(
                                    array(
                                            'id'              => new external_value(PARAM_NUMBER, 'ID of the group', VALUE_OPTIONAL),
                                            'name'            => new external_value(PARAM_RAW, 'Group name', VALUE_OPTIONAL),
                                            'shortname'       => new external_value(PARAM_RAW, 'Group shortname for API only controlled groups', VALUE_OPTIONAL),
                                            'description'     => new external_value(PARAM_NOTAGS, 'Group description'),
                                            'institution'     => new external_value(PARAM_TEXT, 'Mahara institution - required for API controlled groups', VALUE_OPTIONAL),
                                            'grouptype'       => new external_value(PARAM_ALPHANUMEXT, 'Group type: ' . implode(',', $group_types), VALUE_OPTIONAL),
                                            'category'        => new external_value(PARAM_TEXT, 'Group category - the title of an existing group category', VALUE_OPTIONAL),
                                            'editroles'       => new external_value(PARAM_ALPHANUMEXT, 'Edit roles allowed: ' . implode(',', $group_edit_roles), VALUE_OPTIONAL),
                                            'open'            => new external_value(PARAM_INTEGER, 'Boolean 1/0 open - Users can join the group without approval from group administrators', VALUE_DEFAULT),
                                            'controlled'      => new external_value(PARAM_INTEGER, 'Boolean 1/0 controlled - Group administrators can add users to the group without their consent, and members cannot choose to leave', VALUE_DEFAULT),
                                            'request'         => new external_value(PARAM_INTEGER, 'Boolean 1/0 request - Users can send membership requests to group administrators', VALUE_DEFAULT),
                                            'submitpages'     => new external_value(PARAM_INTEGER, 'Boolean 1/0 submitpages - Members can submit pages to the group', VALUE_DEFAULT),
                                            'public'          => new external_value(PARAM_INTEGER, 'Boolean 1/0 public group', VALUE_DEFAULT),
                                            'viewnotify'      => new external_value(PARAM_INTEGER, 'Boolean 1/0 for Shared page notifications', VALUE_DEFAULT),
                                            'usersautoadded'  => new external_value(PARAM_INTEGER, 'Boolean 1/0 for auto-adding users', VALUE_DEFAULT),
                                            'members'         => new external_multiple_structure(
                                                                    new external_single_structure(
                                                                        array(
                                                                                'id'       => new external_value(PARAM_NUMBER, 'member user Id', VALUE_OPTIONAL),
                                                                                'username' => new external_value(PARAM_RAW, 'member username', VALUE_OPTIONAL),
                                                                                'role'     => new external_value(PARAM_ALPHANUMEXT, 'member role: ' . implode(', ', self::$member_roles))
                                                                        ), 'Group membership')
                                                                    ),
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
    public static function update_groups($groups) {
        global $USER, $WEBSERVICE_INSTITUTION;

        // Do basic automatic PARAM checks on incoming data, using params description
        $params = self::validate_parameters(self::update_groups_parameters(), array('groups'=>$groups));

        db_begin();
        $groupids = array();
        foreach ($params['groups'] as $group) {
            // Make sure that the group doesn't already exist
            if (!empty($group['id'])) {
                if (!$dbgroup = get_record('group', 'id', $group['id'], 'deleted', 0)) {
                    throw new WebserviceInvalidParameterException('update_groups: ' . get_string('groupnotexist', 'auth.webservice') . $group['id']);
                }
            }
            else if (!empty($group['shortname'])) {
                if (empty($group['institution'])) {
                    throw new WebserviceInvalidParameterException('update_groups: ' . get_string('instmustset', 'auth.webservice') . $group['shortname']);
                }
                if (!$dbgroup = get_record('group', 'shortname', $group['shortname'], 'institution', $group['institution'], 'deleted', 0)) {
                    throw new WebserviceInvalidParameterException('update_groups: ' . get_string('groupnotexist', 'auth.webservice') . $group['shortname'] . '/' . $group['institution']);
                }
            }
            else if (!empty($group['name'])) {
                if (!$dbgroup = get_record('group', 'name', $group['name'], 'deleted', 0)) {
                    throw new WebserviceInvalidParameterException('update_groups: ' . get_string('groupnotexist', 'auth.webservice') . $group['name']);
                }
            }
            else {
                throw new WebserviceInvalidParameterException('update_groups: ' . get_string('nogroup', 'auth.webservice'));
            }

            // are we allowed to delete for this institution
            if ($WEBSERVICE_INSTITUTION != $dbgroup->institution) {
                throw new WebserviceInvalidParameterException('update_groups: ' . get_string('accessdeniedforinst', 'auth.webservice') . $group['institution'] . ' on group: ' . $group['name']);
            }
            if (!$USER->can_edit_institution($dbgroup->institution)) {
                throw new WebserviceInvalidParameterException('update_groups: ' . get_string('accessdeniedforinst', 'auth.webservice') . $group['institution'] . ' on group: ' . $group['shortname']);
            }

            // convert the category
            if (!empty($group['category'])) {
                $groupcategory = get_record('group_category','title', $group['category']);
                if (empty($groupcategory)) {
                    throw new WebserviceInvalidParameterException('update_groups: ' . get_string('catinvalid', 'auth.webservice') . $group['category']);
                }
                $group['category'] = $groupcategory->id;
            }

            // validate the join type combinations
            if (isset($group['open']) || isset($group['request']) || isset($group['controlled'])) {
                foreach (array('open', 'request', 'controlled') as $membertype) {
                    if (!isset($group[$membertype]) || empty($group[$membertype])) {
                        $group[$membertype] = 0;
                    }
                }
                if ($group['open'] && $group['request']) {
                    throw new WebserviceInvalidParameterException('update_groups: ' . get_string('invalidjointype', 'auth.webservice') . ' open+request');
                }
                if ($group['open'] && $group['controlled']) {
                    throw new WebserviceInvalidParameterException('update_groups: ' . get_string('invalidjointype', 'auth.webservice') . ' open+controlled');
                }

                if (!$group['open'] && !$group['request'] && !$group['controlled']) {
                    throw new WebserviceInvalidParameterException('update_groups: ' . get_string('correctjointype', 'auth.webservice'));
                }
            }
            if (isset($group['editroles']) && !in_array($group['editroles'], array_keys(group_get_editroles_options()))) {
                throw new WebserviceInvalidParameterException('update_groups: ' . get_string('groupeditroles', 'auth.webservice', $group['editroles'], implode(', ', array_keys(group_get_editroles_options()))));
            }

            // check that the members exist and we are allowed to administer them
            $members = array($USER->get('id') => 'admin');
            foreach ($group['members'] as $member) {
                if (!empty($member['id'])) {
                    $dbuser = get_record('usr', 'id', $member['id'], 'deleted', 0);
                }
                else if (!empty($member['username'])) {
                    $dbuser = get_record('usr', 'username', $member['username'], 'deleted', 0);
                }
                else {
                    throw new WebserviceInvalidParameterException('update_groups: ' . get_string('nousernameorid', 'auth.webservice') . ' - group: ' . $group['name']);
                }
                if (empty($dbuser)) {
                    throw new WebserviceInvalidParameterException('update_groups: ' . get_string('invaliduser', 'auth.webservice') . $member['id'] . '/' . $member['username'] . ' - group: ' . $group['name']);
                }

                // check user is in this institution if this is an institution controlled group
                if (!empty($dbgroup->shortname) && !empty($dbgroup->institution)) {
                    if (!mahara_external_in_institution($dbuser, $WEBSERVICE_INSTITUTION)) {
                        throw new WebserviceInvalidParameterException('update_groups: ' . get_string('notauthforuserid', 'auth.webservice') . $dbuser->id . ' institution: ' . $WEBSERVICE_INSTITUTION . ' to group: ' . $group['shortname']);
                    }
                }
                else {
                    // Make sure auth is valid
                    if (!$authinstance = get_record('auth_instance', 'id', $dbuser->authinstance)) {
                        throw new WebserviceInvalidParameterException('update_groups: ' . get_string('invalidauthtype', 'auth.webservice') . $dbuser->authinstance);
                    }
                    // check the institution is allowed
                    // basic check authorisation to edit for the current institution of the user
                    if (!$USER->can_edit_institution($authinstance->institution)) {
                        throw new WebserviceInvalidParameterException('update_groups: ' . get_string('accessdeniedforinst', 'auth.webservice') . $authinstance->institution . ' on user: ' . $dbuser->username);
                    }
                }

                // check the specified role
                if (!in_array($member['role'], self::$member_roles)) {
                    throw new WebserviceInvalidParameterException('update_groups: ' .  get_string('invalidmemroles', 'auth.webservice', $member['role'], $dbuser->username));
                }
                $members[$dbuser->id] = $member['role'];
            }

            // build up the changes
            // not allowed to change these
            $newvalues = (object) array('id'  => $dbgroup->id,);
            foreach (array('name', 'description', 'grouptype', 'category', 'editroles',
                           'open', 'controlled', 'request', 'submitpages', 'quota',
                           'hidemembers', 'invitefriends', 'suggestfriends',
                           'hidden', 'hidemembersfrommembers',
                           'usersautoadded', 'public', 'viewnotify') as $attr) {
                if (isset($group[$attr]) && $group[$attr] !== false && $group[$attr] !== null && strlen("" . $group[$attr])) {
                    $newvalues->{$attr} = $group[$attr];
                }
            }
            group_update($newvalues);

            // now update the group membership
            group_update_members($dbgroup->id, $members);

        }
        db_commit();

        return null;
    }

    /**
     * parameter definition for output of update_groups method
     *
     * Returns description of method result value
     * @return external_description
     */
    public static function update_groups_returns() {
        return null;
    }

    /**
     * parameter definition for input of update_group_members method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function update_group_members_parameters() {

        return new external_function_parameters(
                    array(
                            'groups' =>
                                new external_multiple_structure(
                                    new external_single_structure(
                                        array(
                                                'id'              => new external_value(PARAM_NUMBER, 'ID of the group', VALUE_OPTIONAL),
                                                'name'            => new external_value(PARAM_RAW, 'Group name', VALUE_OPTIONAL),
                                                'shortname'       => new external_value(PARAM_RAW, 'Group shortname for API only controlled groups', VALUE_OPTIONAL),
                                                'institution'     => new external_value(PARAM_TEXT, 'Mahara institution - required for API controlled groups', VALUE_OPTIONAL),
                                                'members'         => new external_multiple_structure(
                                                                        new external_single_structure(
                                                                            array(
                                                                                    'id'       => new external_value(PARAM_NUMBER, 'member user Id', VALUE_OPTIONAL),
                                                                                    'username' => new external_value(PARAM_RAW, 'member username', VALUE_OPTIONAL),
                                                                                    'role'     => new external_value(PARAM_ALPHANUMEXT, 'member role: admin, tutor, member', VALUE_OPTIONAL),
                                                                                    'action'   => new external_value(PARAM_ALPHANUMEXT, 'member action: add, or remove')
                                                                                ), 'Group membership actions')
                                                                        ),
                                                                    )
                                                                )
                                                            )
                                                        )
                                                    );
    }

    /**
     * update one or more sets of group membership
     *
     * @param array $users
     */
    public static function update_group_members($groups) {
        global $USER, $WEBSERVICE_INSTITUTION;

        // Do basic automatic PARAM checks on incoming data, using params description
        $params = self::validate_parameters(self::update_group_members_parameters(), array('groups'=>$groups));

        db_begin();
        $groupids = array();
        foreach ($params['groups'] as $group) {
            // Make sure that the group doesn't already exist
            if (!empty($group['id'])) {
                if (!$dbgroup = get_record('group', 'id', $group['id'], 'deleted', 0)) {
                    throw new WebserviceInvalidParameterException('update_group_members: ' . get_string('groupnotexist', 'auth.webservice') . $group['id']);
                }
            }
            else if (!empty($group['name'])) {
                if (!$dbgroup = get_record('group', 'name', $group['name'], 'deleted', 0)) {
                    throw new WebserviceInvalidParameterException('update_groups: ' . get_string('groupnotexist', 'auth.webservice') . $group['name']);
                }
            }
            else if (!empty($group['shortname'])) {
                if (empty($group['institution'])) {
                    throw new WebserviceInvalidParameterException('update_group_members: ' . get_string('instmustset', 'auth.webservice') . $group['shortname']);
                }
                if (!$dbgroup = get_record('group', 'shortname', $group['shortname'], 'institution', $group['institution'], 'deleted', 0)) {
                    throw new WebserviceInvalidParameterException('update_groups: ' . get_string('groupnotexist', 'auth.webservice') . $group['shortname'] . '/' . $group['institution']);
                }
            }
            else {
                throw new WebserviceInvalidParameterException('update_group_members: ' . get_string('nogroup', 'auth.webservice'));
            }

            // are we allowed to administer this group
            if (!empty($dbgroup->institution) && $WEBSERVICE_INSTITUTION != $dbgroup->institution) {
                throw new WebserviceInvalidParameterException('update_group_members: ' . get_string('accessdeniedforinst', 'auth.webservice') . $group['institution'] . ' on group: ' . $group['name']);
            }
            if (!empty($dbgroup->institution) && !$USER->can_edit_institution($dbgroup->institution)) {
                throw new WebserviceInvalidParameterException('update_group_members: ' . get_string('accessdeniedforinst', 'auth.webservice') . $group['institution'] . ' on group: ' . $group['shortname']);
            }

            // get old members
            $oldmembers = get_records_array('group_member', 'group', $dbgroup->id, '', 'member,role');
            $existingmembers = array();
            if (!empty($oldmembers)) {
                foreach ($oldmembers as $member) {
                    $existingmembers[$member->member] = $member->role;
                }
            }

            // check that the members exist and we are allowed to administer them
            foreach ($group['members'] as $member) {
                if (!empty($member['id'])) {
                    $dbuser = get_record('usr', 'id', $member['id'], 'deleted', 0);
                }
                else if (!empty($member['username'])) {
                    $dbuser = get_record('usr', 'username', $member['username'], 'deleted', 0);
                }
                else {
                    throw new WebserviceInvalidParameterException('update_group_members: ' . get_string('nousernameorid', 'auth.webservice') . ' - group: ' . $group['name']);
                }
                if (empty($dbuser)) {
                    throw new WebserviceInvalidParameterException('update_group_members: ' . get_string('invaliduser', 'auth.webservice') . $member['id'] . '/' . $member['username'] . ' - group: ' . $group['name']);
                }


                // check user is in this institution if this is an institution controlled group
                if (!empty($dbgroup->shortname) && !empty($dbgroup->institution)) {
                    if (!mahara_external_in_institution($dbuser, $WEBSERVICE_INSTITUTION)) {
                        throw new WebserviceInvalidParameterException('update_group_members: ' . get_string('notauthforuserid', 'auth.webservice') . $dbuser->id . ' institution: ' . $WEBSERVICE_INSTITUTION . ' to group: ' . $group['shortname']);
                    }
                }
                else {
                    // Make sure auth is valid
                    if (!$authinstance = get_record('auth_instance', 'id', $dbuser->authinstance)) {
                        throw new WebserviceInvalidParameterException('update_group_members: ' . get_string('invalidauthtype', 'auth.webservice') . $dbuser->authinstance);
                    }
                    // check the institution is allowed
                    // basic check authorisation to edit for the current institution of the user
                    if (!$USER->can_edit_institution($authinstance->institution)) {
                        throw new WebserviceInvalidParameterException('update_group_members: ' . get_string('accessdeniedforinst', 'auth.webservice') . $authinstance->institution . ' on user: ' . $dbuser->username);
                    }
                }

                // determine the changes to the group membership
                if ($member['action'] == 'remove') {
                    if (isset($existingmembers[$dbuser->id])) {
                        unset($existingmembers[$dbuser->id]);
                    }
                    // silently fail
                }
                // add also can be used to update role
                else if ($member['action'] == 'add') {
                    // check the specified role
                    if (!in_array($member['role'], self::$member_roles)) {
                        throw new WebserviceInvalidParameterException('update_group_members: ' .  get_string('invalidmemroles', 'auth.webservice', $member['role'], $dbuser->username));
                    }
                    $existingmembers[$dbuser->id] = $member['role'];
                    // silently fail
                }
                else {
                    throw new WebserviceInvalidParameterException('update_group_members: ' . get_string('membersinvalidaction', 'auth.webservice', $member['action'], $dbuser->id, $dbuser->username, $group['name']));
                }
            }

            // now update the group membership
            group_update_members($dbgroup->id, $existingmembers);

        }
        db_commit();

        return null;
    }

    /**
     * parameter definition for output of update_group_members method
     *
     * Returns description of method result value
     * @return external_description
     */
    public static function update_group_members_returns() {
        return null;
    }

    /**
     * parameter definition for input of get_groups_by_id method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_groups_by_id_parameters() {
        return new external_function_parameters(
                    array(
                            'groups' => new external_multiple_structure(
                                            new external_single_structure(
                                                array(
                                                        'id'              => new external_value(PARAM_NUMBER, 'ID of the group', VALUE_OPTIONAL),
                                                        'shortname'       => new external_value(PARAM_RAW, 'Group shortname for API only controlled groups', VALUE_OPTIONAL),
                                                        'institution'     => new external_value(PARAM_TEXT, 'Mahara institution - required for API controlled groups', VALUE_OPTIONAL),
                                                    )
                                                )
                                            )
                                        )
                                    );
    }

    /**
     * Get user information for one or more groups
     *
     * @param array $groups  array of groups
     * @return array An array of arrays describing groups
     */
    public static function get_groups_by_id($groups) {
        global $WEBSERVICE_INSTITUTION, $USER;

        $params = self::validate_parameters(self::get_groups_by_id_parameters(),
        array('groups' => $groups));

        // if this is a get all users - then lets get them all
        if (empty($params['groups'])) {
            $params['groups'] = array();
            $dbgroups = get_records_sql_array('SELECT * FROM {group} WHERE institution = ? AND deleted = ?', array($WEBSERVICE_INSTITUTION, 0));
            if ($dbgroups) {
                foreach ($dbgroups as $dbgroup) {
                    $params['groups'][] = array('id' => $dbgroup->id);
                }
            }
        }

        // now process the ids
        $groups = array();
        foreach ($params['groups'] as $group) {
            // Make sure that the group doesn't already exist
            if (!empty($group['id'])) {
                if (!$dbgroup = get_record('group', 'id', $group['id'], 'deleted', 0)) {
                    throw new WebserviceInvalidParameterException('get_groups_by_id: ' . get_string('groupnotexist', 'auth.webservice') . $group['id']);
                }
            }
            else if (!empty($group['shortname'])) {
                if (empty($group['institution'])) {
                    throw new WebserviceInvalidParameterException('get_groups_by_id: ' . get_string('instmustset', 'auth.webservice') . $group['shortname']);
                }
                if (!$dbgroup = get_record('group', 'shortname', $group['shortname'], 'institution', $group['institution'], 'deleted', 0)) {
                    throw new WebserviceInvalidParameterException('get_groups_by_id: ' . get_string('groupnotexist', 'auth.webservice') . $group['shortname']);
                }
            }
            else {
                throw new WebserviceInvalidParameterException('get_groups_by_id: ' . get_string('nogroup', 'auth.webservice'));
            }

            // must have access to the related institution
            if ($WEBSERVICE_INSTITUTION != $dbgroup->institution) {
                throw new WebserviceInvalidParameterException('get_group: (' . $WEBSERVICE_INSTITUTION . ') ' . get_string('accessdeniedforinst', 'auth.webservice') . $dbgroup->institution . ' on group: ' . $dbgroup->shortname);
            }
            if (!$USER->can_edit_institution($dbgroup->institution)) {
                throw new WebserviceInvalidParameterException('get_group: ' . get_string('accessdeniedforinst', 'auth.webservice') . $dbgroup->institution . ' on group: ' . $dbgroup->shortname);
            }

            // get the members
            $dbmembers = get_records_sql_array('SELECT gm.member AS userid, u.username AS username, gm.role AS role FROM {group_member} gm LEFT JOIN {usr} u ON gm.member = u.id WHERE "group" = ?', array($dbgroup->id));
            $members = array();
            if ($dbmembers) {
                foreach ($dbmembers as $member) {
                    $members []= array('id' => $member->userid, 'username' => $member->username, 'role' => $member->role);
                }
            }

            // form up the output
            $groups[]= array(
                        'id'             => $dbgroup->id,
                        'name'           => $dbgroup->name,
                        'shortname'      => $dbgroup->shortname,
                        'description'    => $dbgroup->description,
                        'institution'    => $dbgroup->institution,
                        'grouptype'      => $dbgroup->grouptype,
                        'category'       => ($dbgroup->category ? get_field('group_category', 'title', 'id', $dbgroup->category) : ''),
                        'editroles'      => $dbgroup->editroles,
                        'open'           => ($dbgroup->jointype == 'open' ? 1 : 0),
                        'controlled'     => ($dbgroup->jointype == 'controlled' ? 1 : 0),
                        'request'        => $dbgroup->request,
                        'submitpages'    => (isset($dbgroup->submitpages) ? $dbgroup->submitpages : 0),
                        'public'         => $dbgroup->public,
                        'viewnotify'     => $dbgroup->viewnotify,
                        'usersautoadded' => $dbgroup->usersautoadded,
                        'members'        => $members,
            );
        }
        return $groups;
    }

    /**
     * parameter definition for output of get_groups_by_id method
     *
     * Returns description of method result value
     * @return external_description
     */
    public static function get_groups_by_id_returns() {
        $group_types = group_get_grouptypes();
        $group_edit_roles = array_keys(group_get_editroles_options());
        return new external_multiple_structure(
                    new external_single_structure(
                        array(
                                'id'              => new external_value(PARAM_NUMBER, 'ID of the group'),
                                'name'            => new external_value(PARAM_RAW, 'Group name'),
                                'shortname'       => new external_value(PARAM_RAW, 'Group shortname for API only controlled groups'),
                                'description'     => new external_value(PARAM_NOTAGS, 'Group description'),
                                'institution'     => new external_value(PARAM_TEXT, 'Mahara institution - required for API controlled groups'),
                                'grouptype'       => new external_value(PARAM_ALPHANUMEXT, 'Group type: ' . implode(',', $group_types)),
                                'category'        => new external_value(PARAM_TEXT, 'Group category - the title of an existing group category'),
                                'editroles'       => new external_value(PARAM_ALPHANUMEXT, 'Edit roles allowed: ' . implode(',', $group_edit_roles)),
                                'open'            => new external_value(PARAM_INTEGER, 'Boolean 1/0 open - Users can join the group without approval from group administrators'),
                                'controlled'      => new external_value(PARAM_INTEGER, 'Boolean 1/0 controlled - Group administrators can add users to the group without their consent, and members cannot choose to leave'),
                                'request'         => new external_value(PARAM_INTEGER, 'Boolean 1/0 request - Users can send membership requests to group administrators'),
                                'submitpages'     => new external_value(PARAM_INTEGER, 'Boolean 1/0 submitpages - Members can submit pages to the group'),
                                'public'          => new external_value(PARAM_INTEGER, 'Boolean 1/0 public group'),
                                'viewnotify'      => new external_value(PARAM_INTEGER, 'Boolean 1/0 for Shared page notifications'),
                                'usersautoadded'  => new external_value(PARAM_INTEGER, 'Boolean 1/0 for auto-adding users'),
                                'members'         => new external_multiple_structure(
                                                        new external_single_structure(
                                                            array(
                                                                    'id' => new external_value(PARAM_NUMBER, 'member user Id'),
                                                                    'username' => new external_value(PARAM_RAW, 'member username'),
                                                                    'role' => new external_value(PARAM_ALPHANUMEXT, 'member role: admin, ')
                                                                    ), 'Group membership')
                                                                ),
                                                            )
                                                        )
                                                    );
    }

    /**
     * parameter definition for input of get_groups method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_groups_parameters() {
        return new external_function_parameters(array());
    }

    /**
     * Get group information for all groups
     *
     * @param array $groupids  array of group ids
     * @return array An array of arrays describing groups
     */
    public static function get_groups() {
        return self::get_groups_by_id(array());
    }

    /**
     * parameter definition for output of get_groups method
     *
     * Returns description of method result value
     * @return external_description
     */
    public static function get_groups_returns() {
        return self::get_groups_by_id_returns();
    }
}

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
                throw new WebserviceInvalidParameterException(get_string('invalidusername', 'auth.webservice') . $user['username']);
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
            throw new WebserviceInvalidParameterException(get_string('invaliduser', 'auth.webservice') . $id);
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
                                                                'id'              => new external_value(PARAM_NUMBER, 'ID of the favourites owner', VALUE_OPTIONAL),
                                                                'username'        => new external_value(PARAM_RAW, 'Username of the favourites owner', VALUE_OPTIONAL),
                                                                )
                                                            )
                                                        )
                                                    )
                                                );
    }

    /**
     * Add one or more members to an institution
     *
     * @param array $users
     */
    public static function add_members($institution, $users) {
        global $USER, $WEBSERVICE_INSTITUTION;
        self::check_oauth();

        $params = array('institution' => $institution, 'users' => $users);
        $params = self::validate_parameters(self::add_members_parameters(), $params);

        if (!$USER->get('admin') && !$USER->is_institutional_admin()) {
            throw new AccessDeniedException("Institution::add_members: access denied");
        }
        // check the institution is allowed
        if (!$USER->can_edit_institution($params['institution'])) {
            throw new WebserviceInvalidParameterException('add_members: ' . get_string('accessdeniedforinst', 'auth.webservice') . $params['institution']);
        }
        db_begin();
        $userids = array();
        foreach ($params['users'] as $user) {
            $dbuser = self::checkuser($user);
            // Make sure auth is valid
            if (!$authinstance = get_record('auth_instance', 'id', $dbuser->authinstance)) {
                throw new WebserviceInvalidParameterException(get_string('invalidauthtype', 'auth.webservice') . $dbuser->authinstance);
            }
            // check the institution is allowed
            // basic check authorisation to edit for the current institution
            if (!$USER->can_edit_institution($authinstance->institution)) {
                throw new WebserviceInvalidParameterException('add_members: ' . get_string('accessdeniedforinst', 'auth.webservice') . $authinstance->institution . ' on user: ' . $dbuser->id);
            }
            $userids[]= $dbuser->id;
        }
        $institution = new Institution($params['institution']);
        $maxusers = $institution->maxuseraccounts;
        if (!empty($maxusers)) {
            $members = $institution->countMembers();
            if ($members + count($userids) > $maxusers) {
                throw new AccessDeniedException("Institution::add_members: " . get_string('institutionuserserrortoomanyinvites', 'admin'));
            }
        }
        $institution->add_members($userids);
        db_commit();

        return null;
    }

    /**
     * parameter definition for output of add_members method
     *
     * Returns description of method result value
     * @return external_description
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
                                                            'id'              => new external_value(PARAM_NUMBER, 'ID of the favourites owner', VALUE_OPTIONAL),
                                                            'username'        => new external_value(PARAM_RAW, 'Username of the favourites owner', VALUE_OPTIONAL),
                                                        )
                                                    )
                                                )
                                            )
                                        );
    }

    /**
     * Invite one or more users to an institution
     *
     * @param array $users
     */
    public static function invite_members($institution, $users) {
        global $USER, $WEBSERVICE_INSTITUTION;
        self::check_oauth();

        $params = array('institution' => $institution, 'users' => $users);
        $params = self::validate_parameters(self::invite_members_parameters(), $params);

        if (!$USER->get('admin') && !$USER->is_institutional_admin()) {
            throw new AccessDeniedException("Institution::invite_members: access denied");
        }
        // check the institution is allowed
        if (!$USER->can_edit_institution($params['institution'])) {
            throw new WebserviceInvalidParameterException('invite_members: ' . get_string('accessdeniedforinst', 'auth.webservice') . $params['institution']);
        }
        db_begin();
        $userids = array();
        foreach ($params['users'] as $user) {
            $dbuser = self::checkuser($user);

            // Make sure auth is valid
            if (!$authinstance = get_record('auth_instance', 'id', $dbuser->authinstance)) {
                throw new WebserviceInvalidParameterException('invite_members: ' . get_string('invalidauthtype', 'auth.webservice') . $dbuser->authinstance);
            }
            // check the institution is allowed
            // basic check authorisation to edit for the current institution
            if (!$USER->can_edit_institution($authinstance->institution)) {
                throw new WebserviceInvalidParameterException('invite_members: ' . get_string('accessdeniedforinst', 'auth.webservice') . $authinstance->institution . ' on user: ' . $dbuser->id);
            }
            $userids[]= $dbuser->id;
        }
        $institution = new Institution($params['institution']);
        $maxusers = $institution->maxuseraccounts;
        if (!empty($maxusers)) {
            if ($members + $institution->countInvites() + count($userids) > $maxusers) {
                throw new AccessDeniedException("Institution::invite_members: " . get_string('institutionuserserrortoomanyinvites', 'admin'));
            }
        }

        $institution->invite_users($userids);
        db_commit();

        return null;
    }

    /**
     * parameter definition for output of invite_members method
     *
     * Returns description of method result value
     * @return external_description
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
                                                                'id'              => new external_value(PARAM_NUMBER, 'ID of the favourites owner', VALUE_OPTIONAL),
                                                                'username'        => new external_value(PARAM_RAW, 'Username of the favourites owner', VALUE_OPTIONAL),
                                                            )
                                                        )
                                                    )
                                                )
                                            );
    }

    /**
     * remove one or more users from an institution
     *
     * @param array $users
     */
    public static function remove_members($institution, $users) {
        global $USER, $WEBSERVICE_INSTITUTION;
        self::check_oauth();

        $params = array('institution' => $institution, 'users' => $users);
        $params = self::validate_parameters(self::remove_members_parameters(), $params);

        if (!$USER->get('admin') && !$USER->is_institutional_admin()) {
            throw new AccessDeniedException("Institution::remove_members: access denied");
        }
        // check the institution is allowed
        if (!$USER->can_edit_institution($params['institution'])) {
            throw new WebserviceInvalidParameterException('remove_members: ' . get_string('accessdeniedforinst', 'auth.webservice') . $params['institution']);
        }
        db_begin();
        $userids = array();
        foreach ($params['users'] as $user) {
            $dbuser = self::checkuser($user);

            // Make sure auth is valid
            if (!$authinstance = get_record('auth_instance', 'id', $dbuser->authinstance)) {
                throw new WebserviceInvalidParameterException('remove_members: ' . get_string('invalidauthtype', 'auth.webservice') . $dbuser->authinstance);
            }

            // check the institution is allowed
            // basic check authorisation to edit for the current institution
            if (!$USER->can_edit_institution($authinstance->institution)) {
                throw new WebserviceInvalidParameterException('remove_members: ' . get_string('accessdeniedforinst', 'auth.webservice') . $authinstance->institution . ' on user: ' . $dbuser->id);
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
     *
     * Returns description of method result value
     * @return external_description
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
                                                                'id'              => new external_value(PARAM_NUMBER, 'ID of the favourites owner', VALUE_OPTIONAL),
                                                                'username'        => new external_value(PARAM_RAW, 'Username of the favourites owner', VALUE_OPTIONAL),
                                                            )
                                                        )
                                                    )
                                                )
                                            );
    }

    /**
     * decline one or more users request for membership to an institution
     *
     * @param array $users
     */
    public static function decline_members($institution, $users) {
        global $USER, $WEBSERVICE_INSTITUTION;
        self::check_oauth();

        $params = array('institution' => $institution, 'users' => $users);
        $params = self::validate_parameters(self::decline_members_parameters(), $params);

        if (!$USER->get('admin') && !$USER->is_institutional_admin()) {
            throw new AccessDeniedException("Institution::decline_members: access denied");
        }
        // check the institution is allowed
        if (!$USER->can_edit_institution($params['institution'])) {
            throw new WebserviceInvalidParameterException('decline_members: ' . get_string('accessdeniedforinst', 'auth.webservice') . $params['institution']);
        }
        db_begin();
        $userids = array();
        foreach ($params['users'] as $user) {
            $dbuser = self::checkuser($user);

            // Make sure auth is valid
            if (!$authinstance = get_record('auth_instance', 'id', $dbuser->authinstance)) {
                throw new WebserviceInvalidParameterException('decline_members: ' . get_string('invalidauthtype', 'auth.webservice') . $dbuser->authinstance);
            }

            // check the institution is allowed
            // basic check authorisation to edit for the current institution
            if (!$USER->can_edit_institution($authinstance->institution)) {
                throw new WebserviceInvalidParameterException('decline_members: ' . get_string('accessdeniedforinst', 'auth.webservice') . $authinstance->institution . ' on user: ' . $dbuser->id);
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
     *
     * Returns description of method result value
     * @return external_description
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
     * @param array $groups  An array of groups to create.
     * @return array An array of arrays
     */
    public static function get_members($institution) {
        global $USER, $WEBSERVICE_INSTITUTION;
        self::check_oauth();

        // Do basic automatic PARAM checks on incoming data, using params description
        $params = self::validate_parameters(self::get_members_parameters(), array('institution'=>$institution));
        if (!$USER->get('admin') && !$USER->is_institutional_admin()) {
            throw new AccessDeniedException("Institution::get_members: access denied");
        }
        // check the institution is allowed
        if (!$USER->can_edit_institution($params['institution'])) {
            throw new WebserviceInvalidParameterException('get_members: ' . get_string('accessdeniedforinst', 'auth.webservice') . $params['institution']);
        }
        $institution = new Institution($params['institution']);
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
     * @return external_description
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
     * @param array $groups  An array of groups to create.
     * @return array An array of arrays
     */
    public static function get_requests($institution) {
        global $USER, $WEBSERVICE_INSTITUTION;
        self::check_oauth();

        // Do basic automatic PARAM checks on incoming data, using params description
        $params = self::validate_parameters(self::get_members_parameters(), array('institution'=>$institution));
        if (!$USER->get('admin') && !$USER->is_institutional_admin()) {
            throw new AccessDeniedException("Institution::get_requests: " . get_string('accessdenied', 'auth.webservice'));
        }
        // check the institution is allowed
        if (!$USER->can_edit_institution($params['institution'])) {
            throw new WebserviceInvalidParameterException('get_requests:' . get_string('accessdeniedforinst', 'auth.webservice') . $params['institution']);
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
     * @return external_description
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
