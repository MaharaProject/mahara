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
require_once(get_config('docroot') . 'api/xmlrpc/lib.php');

global $WEBSERVICE_OAUTH_USER;

/**
* Class container for core Mahara user related API calls
*/
class mahara_view_external extends external_api {

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
     * parameter definition for input of  get_views_for_user method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function get_views_for_user_parameters() {
       return new external_function_parameters(
            array(
                'users' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id'              => new external_value(PARAM_NUMBER, 'ID of the favourites owner', VALUE_OPTIONAL),
                            'username'        => new external_value(PARAM_RAW, 'Username of the favourites owner', VALUE_OPTIONAL),
                            'remoteuser'      => new external_value(PARAM_RAW, 'Remote username of the favourites owner', VALUE_OPTIONAL),
                            'email'           => new external_value(PARAM_RAW, 'Email address of the favourites owner', VALUE_OPTIONAL),
                            'query'           => new external_value(PARAM_RAW, 'View query filter to apply', VALUE_OPTIONAL),
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
            throw new WebserviceInvalidParameterException(get_string('invaliduserid', 'auth.webservice', $id));
        }
    }

    /**
     * Get user information for one or more users
     *
     * @param array $users  array of users
     * @return array An array of arrays describing users
     */
    public static function get_views_for_user($users) {
        global $WEBSERVICE_INSTITUTION, $WEBSERVICE_OAUTH_USER, $USER;

        $params = self::validate_parameters(self::get_views_for_user_parameters(),
                array('users'=>$users));
        $result = array();

        log_debug('in get_views_for_user: '.var_export($params, true));
        // if this is a get all users - then lets get them all
        if (empty($params['users'])) {
            return $result;
        }

        //TODO: check if there is any performance issue: we do one DB request to retrieve
        //  all user, then for each user the profile_load_data does at least two DB requests
        foreach ($params['users'] as $u) {
            $user = self::checkuser($u);
            // skip deleted users
            if (!empty($user->deleted)) {
                continue;
            }
            // check the institution
            if (!mahara_external_in_institution($user, $WEBSERVICE_INSTITUTION)) {
                continue;
            }

            $auth_instance = get_record('auth_instance', 'id', $user->authinstance);
            $USER->reanimate($user->id, $user->authinstance);
            require_once('view.php');
            $data = View::view_search((isset($u['query']) ? $u['query'] : null), null, (object) array('owner' => $USER->get('id')), null, null, 0, true, null, null, true);
            require_once('collection.php');
            $data->collections = Collection::get_mycollections_data(0, 0, $USER->get('id'));
            foreach ($data->collections->data as $c) {
                $cobj = new Collection($c->id);
                if ($c->numviews > 0) {
                    $c->url = $cobj->get_url(false, true);
                    $c->fullurl = get_config('wwwroot') . $c->url;
                }
                else {
                    $c->fullurl = '';
                    $c->url = '';
                }
            }
            $data->displayname = display_name($user);
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

            $views = array('ids' => implode(',', $data->ids), 'data' => array(), 'collections' => array('data' => array()), 'count' => $data->count);
            foreach ($data->data as $view) {
                $view = array('title' => $view['title'],
                              'description' => $view['description'],
                              'mtime' => $view['mtime'],
                              'ctime' => $view['ctime'],
                              'collid' => $view['collid'],
                              'type' => $view['type'],
                              'id' => $view['id'],
                              'displaytitle' => $view['displaytitle'],
                              'url' => $view['url'],
                              'fullurl' => $view['fullurl'],
                              'submittedtime' => $view['submittedtime'],
                              );
                $views['data'][]= $view;
            }

            foreach ($data->collections->data as $collection) {
                $collection = array('name' => $collection->name,
                              'description' => $collection->description,
                              'id' => $collection->id,
                              'url' => $collection->url,
                              'fullurl' => $collection->fullurl,
                              'numviews' => $collection->numviews,
                              'submittedtime' => $collection->submittedtime,
                              );
                $views['collections']['data'][]= $collection;
            }
            $views['collections']['count'] = $data->collections->count;
            $views['displayname'] = $data->displayname;

            $userarray['views'] = $views;

            $result[] = $userarray;
        }

        log_debug('get_views_for_user Results: '.var_export($result, true));
        return $result;
    }

    /**
     * parameter definition for output of get_views_for_user method
     *
     * Returns description of method result value
     * @return external_description
     */
    public static function get_views_for_user_returns() {
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
                    'views'           => new external_single_structure(
                                                array(
                                                    'ids'   => new external_value(PARAM_RAW, 'ids for views'),
                                                    'count'   => new external_value(PARAM_NUMBER, 'count of views'),
                                                    'displayname'=> new external_value(PARAM_RAW, 'User displayname'),
                                                    'data' => new external_multiple_structure(
                                            new external_single_structure(
                                                array(
                                                    'title'       => new external_value(PARAM_RAW, 'View title'),
                                                    'description' => new external_value(PARAM_RAW, 'View description'),
                                                    'mtime' => new external_value(PARAM_RAW, 'View modification time'),
                                                    'ctime' => new external_value(PARAM_RAW, 'View creation time'),
                                                    'collid' => new external_value(PARAM_NUMBER, 'View coll id'),
                                                    'type' => new external_value(PARAM_RAW, 'View type'),
                                                    'id' => new external_value(PARAM_NUMBER, 'View ID'),
                                                    'displaytitle' => new external_value(PARAM_RAW, 'View display title'),
                                                    'url' => new external_value(PARAM_RAW, 'View relative URL'),
                                                    'fullurl' => new external_value(PARAM_RAW, 'View full URL'),
                                                    'submittedtime' => new external_value(PARAM_RAW, 'Time when submitted'),
                                                ), 'A View')
                                        ),
                                                    'collections' => new external_single_structure(
                                                array(
                                                    'count'       => new external_value(PARAM_NUMBER, 'Collections count'),
                                                    'data' =>
                                        new external_multiple_structure(
                                            new external_single_structure(
                                                array(
                                                    'name'       => new external_value(PARAM_RAW, 'View name'),
                                                    'description' => new external_value(PARAM_RAW, 'View description'),
                                                    'id' => new external_value(PARAM_NUMBER, 'View ID'),
                                                    'url' => new external_value(PARAM_RAW, 'View relative URL'),
                                                    'numviews' => new external_value(PARAM_NUMBER, 'number of views'),
                                                    'submittedtime' => new external_value(PARAM_RAW, 'Time when submitted'),
                                                    'fullurl' => new external_value(PARAM_RAW, 'View full URL'),
                                                ), 'A View')
                                        ),
                                                ), 'Collections')
                                                ), 'Views'),
                        )
                )
        );
    }


    /**
     * parameter definition for input of  submit_view_for_assessment method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function submit_view_for_assessment_parameters() {
       return new external_function_parameters(
            array(
                'views' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id'              => new external_value(PARAM_INTEGER, 'ID of the favourites owner', VALUE_OPTIONAL),
                            'username'        => new external_value(PARAM_RAW, 'Username of the favourites owner', VALUE_OPTIONAL),
                            'remoteuser'      => new external_value(PARAM_RAW, 'Remote username of the favourites owner', VALUE_OPTIONAL),
                            'email'           => new external_value(PARAM_RAW, 'Email address of the favourites owner', VALUE_OPTIONAL),
                            'viewid'          => new external_value(PARAM_INTEGER, 'View ID', VALUE_REQUIRED),
                            'iscollection'    => new external_value(PARAM_BOOL, 'Is a Collection', VALUE_OPTIONAL),
                            'lock'            => new external_value(PARAM_BOOL, 'Lock the object', VALUE_OPTIONAL),
                            'apilevel'        => new external_value(PARAM_RAW, 'API level requested'),
                            'wwwroot'         => new external_value(PARAM_RAW, 'Client URN to distinguish remote lockers', VALUE_REQUIRED),
                            )
                        )
                    )
                )
            );
    }


    /**
     * Get user information for one or more users
     *
     * @param array $views  array of views
     * @return array An array of arrays describing views
     */
    public static function submit_view_for_assessment($views) {
        global $WEBSERVICE_INSTITUTION, $WEBSERVICE_OAUTH_USER, $USER;

        $params = self::validate_parameters(self::submit_view_for_assessment_parameters(),
                array('views' => $views));
        $result = array();


        // if this is a get all views - then lets get them all
        if (empty($params['views'])) {
            return $result;
        }

        // root looks something like https://mahara.local.net/maharadev/

        //TODO: check if there is any performance issue: we do one DB request to retrieve
        //  all user, then for each user the profile_load_data does at least two DB requests
        foreach ($params['views'] as $v) {
            $user = self::checkuser($v);
            // skip deleted users
            if (!empty($user->deleted)) {
                continue;
            }
            // check the institution
            if (!mahara_external_in_institution($user, $WEBSERVICE_INSTITUTION)) {
                continue;
            }
            $lock = (isset($v['lock']) ? $v['lock'] : true);
            $remotewwwroot = $v['wwwroot'];
            $viewid = $v['viewid'];

            // Figure out which API level Moodle wants to use
            if ($v['apilevel'] && is_string($v['apilevel']) && count(explode(':', $v['apilevel'], 2)) == 2) {
                list($apiname, $apinumber) = explode(':', $v['apilevel'], 2);
            }
            else {
                throw new XmlrpcClientException('Invalid application level ' . hsc((string) $apilevel));
            }

            if ($apiname === 'moodle-assignsubmission-mahara' && ((int) $apinumber) >= 2) {
                // Level 2 or later API. We'll use a later MNet call for access control, no need
                // for an access token.
                $usetokens = false;
                $returnapi = 'moodle-assignsubmission-mahara:2';
            }
            else {
                // "Classic" api. Use access tokens. If the client wants the page to remain unlocked
                // they'll have to do a subsequent "unlock" call and rely on the token sticking around.
                $usetokens = true;
                $lock = true;
                $returnapi = 'moodle-assignsubmission-mahara:1';
            }

            require_once('view.php');
            // $remotehost = parse_url($v['wwwroot'], PHP_URL_HOST);
            // $authinstance->config['wwwroot'];
            $userid = $user->id;

            db_begin();
            if (isset($v['iscollection']) && $v['iscollection']) {
                require_once('collection.php');
                $collection = new Collection($viewid);
                $title = $collection->get('name');
                $description = $collection->get('description');
                log_debug("is a collection");

                // Can't submit an empty collection, because it won't be viewable.
                if (!$collection->views()) {
                    throw new CollectionSubmissionException(get_string('cantsubmitemptycollection', 'view'));
                }

                if ($lock) {
                    log_debug("we are locking");
                    // Check whether the collection is already submitted
                    if ($collection->is_submitted()) {
                        log_debug("collection already submitted");
                        // If this is already submitted to something else, throw an exception
                        if ($collection->get('submittedgroup') || $collection->get('submittedhost') !== $remotewwwroot) {
                            throw new CollectionSubmissionException(get_string('collectionalreadysubmitted', 'view'));
                        }

                        // It may have been submitted to a different assignment in the same remote
                        // site, but there's no way we can tell. So we'll just send the access token
                        // back.
                        $access = $collection->get_invisible_token();
                    }
                    else {
                        log_debug("do the submit");
                        $collection->submit(null, $remotewwwroot, $userid);
                        $access = $collection->new_token(false);
                    }
                    $token = $access->token;
                    $url = 'view/view.php?mt=' . $token;
                }

                // The client has indicated via its API level that we don't need to use access tokens
                if (!$usetokens) {
                    $token = null;
                    $url = $collection->get_url(false, true);
                }
            }
            else {
                log_debug("its a view");
                $view = new View($viewid);
                $title = $view->get('title');
                $description = $view->get('description');

                if ($lock) {
                    log_debug('we are locking');
                    if ($view->is_submitted()) {
                        log_debug('view already submitted');
                        // If this is already submitted to something else, throw an exception
                        if ($view->get('submittedgroup') || $view->get('submittedhost') !== $remotewwwroot) {
                            throw new ViewSubmissionException(get_string('viewalreadysubmitted', 'view'));
                        }

                        // It may have been submitted to a different assignment in the same remote
                        // site, but there's no way we can tell. So we'll just send the access token
                        // back.
                        $access = View::get_invisible_token($viewid);
                    }
                    else {
                        log_debug('doing the submit');
                        View::_db_submit(array($viewid), null, $remotewwwroot, $userid);
                        $access = View::new_token($viewid, false);
                    }
                    $token = $access->token;
                    $url = 'view/view.php?mt=' . $token;
                }

                // The client has indicated via its API level that we don't need to use access tokens
                if (!$usetokens) {
                    $token = null;
                    $url = $view->get_url(false, true);
                }
            }

            $data = array(
                'id'           => $user->id,
                'username'     => $user->username,
                'viewid'       => $viewid,
                'iscollection' => $v['iscollection'],
                'lock'         => $lock,
                'title'        => $title,
                'description'  => $description,
                'fullurl'      => get_config('wwwroot') . $url,
                'url'          => '/' . $url,
                'accesskey'    => $token,
                'apilevel'     => $returnapi,
            );

            // Provide each artefact plugin the opportunity to handle the remote submission and
            // provide return data for the webservice caller
            foreach (plugins_installed('artefact') as $plugin) {
                safe_require('artefact', $plugin->name);
                $classname = generate_class_name('artefact', $plugin->name);
                if (is_callable($classname . '::view_submit_external_data')) {
                    $data[$plugin->name] = call_static_method($classname, 'view_submit_external_data', $viewid, $iscollection);
                }
            }
            db_commit();
            $result[]= $data;
        }

        log_debug('submit_view_for_assessment Results: '.var_export($result, true));
        return $result;
    }


    /**
     * parameter definition for output of submit_view_for_assessment method
     *
     * Returns description of method result value
     * @return external_description
     */
    public static function submit_view_for_assessment_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                        array(
                            'id'           => new external_value(PARAM_NUMBER, 'ID of the user'),
                            'username'     => new external_value(PARAM_RAW, 'Username policy is defined in Mahara security config'),
                            'viewid'       => new external_value(PARAM_INTEGER, 'View ID'),
                            'iscollection' => new external_value(PARAM_BOOL, 'Is a collection'),
                            'lock'         => new external_value(PARAM_BOOL, 'Locked'),
                            'title'        => new external_value(PARAM_RAW, 'Title'),
                            'description'  => new external_value(PARAM_RAW, 'Description'),
                            'fullurl'      => new external_value(PARAM_RAW, 'Full URL'),
                            'url'          => new external_value(PARAM_RAW, 'Relative URL'),
                            'accesskey'    => new external_value(PARAM_RAW, 'Access Key'),
                            'apilevel'     => new external_value(PARAM_RAW, 'API Level'),
                        )
                )
        );
    }



    /**
     * parameter definition for input of  release_submitted_view method
     *
     * Returns description of method parameters
     * @return external_function_parameters
     */
    public static function release_submitted_view_parameters() {
       return new external_function_parameters(
            array(
                'views' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id'              => new external_value(PARAM_INTEGER, 'ID of the favourites owner', VALUE_OPTIONAL),
                            'username'        => new external_value(PARAM_RAW, 'Username of the favourites owner', VALUE_OPTIONAL),
                            'remoteuser'      => new external_value(PARAM_RAW, 'Remote username of the favourites owner', VALUE_OPTIONAL),
                            'email'           => new external_value(PARAM_RAW, 'Email address of the favourites owner', VALUE_OPTIONAL),
                            'viewid'          => new external_value(PARAM_INTEGER, 'View ID', VALUE_REQUIRED),
                            'iscollection'    => new external_value(PARAM_BOOL, 'Is a Collection', VALUE_OPTIONAL),
                            'viewoutcomes'    => new external_value(PARAM_RAW, 'View outcomes', VALUE_OPTIONAL),
                            )
                        )
                    )
                )
            );
    }


    /**
     * Get user information for one or more users
     *
     * @param array $views  array of views
     * @return array An array of arrays describing views
     */
    public static function release_submitted_view($views) {
        global $WEBSERVICE_INSTITUTION, $WEBSERVICE_OAUTH_USER, $USER;

        $params = self::validate_parameters(self::release_submitted_view_parameters(),
                array('views' => $views));
        $result = array();
        log_debug('in unlock: '.var_export($params, true));


        // if this is a get all views - then lets get them all
        if (empty($params['views'])) {
            return $result;
        }

        //TODO: check if there is any performance issue: we do one DB request to retrieve
        //  all user, then for each user the profile_load_data does at least two DB requests
        foreach ($params['views'] as $v) {
            $user = self::checkuser($v);
            // skip deleted users
            if (!empty($user->deleted)) {
                continue;
            }
            // check the institution
            if (!mahara_external_in_institution($user, $WEBSERVICE_INSTITUTION)) {
                continue;
            }
            $v['viewoutcomes'] = explode(',', $v['viewoutcomes']);

            require_once('view.php');
            $userid = $user->id;

            db_begin();
            $teacher = $user;
            if (isset($v['iscollection']) && $v['iscollection']) {
                require_once('collection.php');
                $collection = new Collection($v['viewid']);
                log_debug('releasing collection');
                $collection->release($teacher);
            }
            else {
                $view = new View($v['viewid']);
                log_debug('releasing view');
                View::_db_release(array($v['viewid']), $view->get('owner'));
            }

            // Provide each artefact plugin the opportunity to handle the remote submission release
            foreach (plugins_installed('artefact') as $plugin) {
                safe_require('artefact', $plugin->name);
                $classname = generate_class_name('artefact', $plugin->name);
                if (is_callable($classname . '::view_release_external_data')) {
                    call_static_method($classname, 'view_release_external_data', $v['viewid'], $v['viewoutcomes'], $teacher ? $teacher->id : 0, (isset($v['iscollection']) && $v['iscollection']));
                }
            }
            db_commit();
        }

        return null;
    }


    /**
     * parameter definition for output of release_submitted_view method
     *
     * Returns description of method result value
     * @return external_description
     */
    public static function release_submitted_view_returns() {
        return null;
    }
}
