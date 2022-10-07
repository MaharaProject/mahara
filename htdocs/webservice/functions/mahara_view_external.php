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
                            'id'              => new external_value(PARAM_NUMBER, get_string('portfolioownerid', WEBSERVICE_LANG), VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                            'username'        => new external_value(PARAM_RAW, get_string('portfolioownerusername', WEBSERVICE_LANG), VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                            'remoteuser'      => new external_value(PARAM_RAW, get_string('portfolioremoteuser', WEBSERVICE_LANG), VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                            'email'           => new external_value(PARAM_RAW, get_string('portfolioowneremail', WEBSERVICE_LANG), VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                            'query'           => new external_value(PARAM_RAW, get_string('portfolioquery', WEBSERVICE_LANG), VALUE_OPTIONAL),
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
            $dbinstances = get_records_array('auth_instance', 'institution', $WEBSERVICE_INSTITUTION, 'active', 1);
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
        if ($return_user = get_user($id)) {
            if ($return_user->deleted) {
                throw new WebserviceInvalidParameterException(get_string('invaliduserid', 'auth.webservice', $id));
            }
            // get the remoteuser
            $return_user->remoteuser = get_field(
                'auth_remote_user',
                'remoteusername',
                'authinstance',
                $return_user->authinstance,
                'localusr',
                $return_user->id
            );
            foreach (array('jabberusername', 'introduction', 'country', 'city', 'address',
                           'town', 'homenumber', 'businessnumber', 'mobilenumber', 'faxnumber',
                           'officialwebsite', 'personalwebsite', 'blogaddress', 'aimscreenname',
                           'icqnumber', 'msnnumber', 'yahoochat', 'skypeusername', 'jabberusername',
                           'occupation', 'industry') as $attr) {
                if ($art = get_record('artefact', 'artefacttype', $attr, 'owner', $return_user->id)) {
                    $return_user->{$attr} = $art->title;
                }
            }
            return $return_user;
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

            $auth_instance = get_record('auth_instance', 'id', $user->authinstance, 'active', 1);
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
            $auths = get_records_sql_array('SELECT aru.remoteusername AS remoteusername, ai.authname AS authname, ai.active FROM {auth_remote_user} aru
                                              INNER JOIN {auth_instance} ai ON aru.authinstance = ai.id
                                              WHERE ai.institution = ? AND aru.localusr = ?', array($WEBSERVICE_INSTITUTION, $user->id));
            if ($auths) {
                foreach ($auths as $auth) {
                    $userarray['auths'][]= array('auth' => $auth->authname, 'remoteuser' => $auth->remoteusername, 'active' => $auth->active);
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
                    'id'              => new external_value(PARAM_NUMBER, get_string('portfolioownerid', WEBSERVICE_LANG)),
                    'username'        => new external_value(PARAM_RAW, get_string('portfolioownerusername', WEBSERVICE_LANG)),
                    'firstname'       => new external_value(PARAM_NOTAGS, get_string('firstname', WEBSERVICE_LANG)),
                    'lastname'        => new external_value(PARAM_NOTAGS, get_string('lastname', WEBSERVICE_LANG)),
                    'email'           => new external_value(PARAM_TEXT, get_string('portfolioowneremail', WEBSERVICE_LANG)),
                    'auth'            => new external_value(PARAM_SAFEDIR, get_string('authplugins', WEBSERVICE_LANG)),
                    'studentid'       => new external_value(PARAM_RAW, get_string('studentidinst', WEBSERVICE_LANG)),
                    'institution'     => new external_value(PARAM_SAFEDIR, get_string('institution', WEBSERVICE_LANG)),
                    'preferredname'   => new external_value(PARAM_RAW, get_string('preferredname', WEBSERVICE_LANG)),
                    'introduction'    => new external_value(PARAM_RAW, get_string('introduction', WEBSERVICE_LANG)),
                    'country'         => new external_value(PARAM_ALPHA, get_string('country', WEBSERVICE_LANG)),
                    'city'            => new external_value(PARAM_NOTAGS, get_string('city', WEBSERVICE_LANG)),
                    'address'         => new external_value(PARAM_RAW, get_string('useraddress', WEBSERVICE_LANG)),
                    'town'            => new external_value(PARAM_NOTAGS, get_string('town', WEBSERVICE_LANG)),
                    'homenumber'      => new external_value(PARAM_RAW, get_string('homenumber', WEBSERVICE_LANG)),
                    'businessnumber'  => new external_value(PARAM_RAW, get_string('businessnumber', WEBSERVICE_LANG)),
                    'mobilenumber'    => new external_value(PARAM_RAW, get_string('mobilenumber', WEBSERVICE_LANG)),
                    'faxnumber'       => new external_value(PARAM_RAW, get_string('faxnumber', WEBSERVICE_LANG)),
                    'officialwebsite' => new external_value(PARAM_RAW, get_string('officialwebsite', WEBSERVICE_LANG)),
                    'personalwebsite' => new external_value(PARAM_RAW, get_string('personalwebsite', WEBSERVICE_LANG)),
                    'blogaddress'     => new external_value(PARAM_RAW, get_string('blogaddress', WEBSERVICE_LANG)),
                    'aimscreenname'   => new external_value(PARAM_ALPHANUMEXT, get_string('aimscreenname', WEBSERVICE_LANG)),
                    'icqnumber'       => new external_value(PARAM_ALPHANUMEXT, get_string('icqnumber', WEBSERVICE_LANG)),
                    'msnnumber'       => new external_value(PARAM_ALPHANUMEXT, get_string('msnnumber', WEBSERVICE_LANG)),
                    'yahoochat'       => new external_value(PARAM_ALPHANUMEXT, get_string('yahoochat', WEBSERVICE_LANG)),
                    'skypeusername'   => new external_value(PARAM_ALPHANUMEXT, get_string('skypeusername', WEBSERVICE_LANG)),
                    'jabberusername'  => new external_value(PARAM_RAW, get_string('jabberusername', WEBSERVICE_LANG)),
                    'occupation'      => new external_value(PARAM_TEXT, get_string('occupation', WEBSERVICE_LANG)),
                    'industry'        => new external_value(PARAM_TEXT, get_string('industry', WEBSERVICE_LANG)),
                    'auths'           => new external_multiple_structure(
                                            new external_single_structure(
                                                array(
                                                    'auth'       => new external_value(PARAM_SAFEDIR, get_string('authplugins', WEBSERVICE_LANG)),
                                                    'remoteuser' => new external_value(PARAM_RAW, get_string('remoteuser', WEBSERVICE_LANG)),
                                                ), get_string('remoteusersconnected', WEBSERVICE_LANG))
                                        ),
                    'views'           => new external_single_structure(
                                                array(
                                                    'ids'   => new external_value(PARAM_RAW,  get_string('viewsids', WEBSERVICE_LANG)),
                                                    'count'   => new external_value(PARAM_NUMBER, get_string('viewscount', WEBSERVICE_LANG)),
                                                    'displayname' => new external_value(PARAM_RAW,  get_string('displayname', WEBSERVICE_LANG)),
                                                    'data' => new external_multiple_structure(
                                            new external_single_structure(
                                                array(
                                                    'title'       => new external_value(PARAM_RAW, get_string('viewtitle', WEBSERVICE_LANG)),
                                                    'description' => new external_value(PARAM_RAW, get_string('viewdesc', WEBSERVICE_LANG)),
                                                    'mtime' => new external_value(PARAM_RAW, get_string('viewmodtime', WEBSERVICE_LANG)),
                                                    'ctime' => new external_value(PARAM_RAW, get_string('viewcreatetime', WEBSERVICE_LANG)),
                                                    'collid' => new external_value(PARAM_NUMBER, get_string('viewcollid', WEBSERVICE_LANG)),
                                                    'type' => new external_value(PARAM_RAW, get_string('viewtype', WEBSERVICE_LANG)),
                                                    'id' => new external_value(PARAM_NUMBER, get_string('viewid', WEBSERVICE_LANG)),
                                                    'displaytitle' => new external_value(PARAM_RAW, get_string('displaytitle', WEBSERVICE_LANG)),
                                                    'url' => new external_value(PARAM_RAW, get_string('viewrelativeurl', WEBSERVICE_LANG)),
                                                    'fullurl' => new external_value(PARAM_RAW, get_string('viewfullurl', WEBSERVICE_LANG)),
                                                    'submittedtime' => new external_value(PARAM_RAW, get_string('submittedtime', WEBSERVICE_LANG)),
                                                ),
                                                get_string('view', WEBSERVICE_LANG)
                                            )
                                        ),
                                                    'collections' => new external_single_structure(
                                                array(
                                                    'count'       => new external_value(PARAM_NUMBER, get_string('collectionscount', WEBSERVICE_LANG)),
                                                    'data' =>
                                        new external_multiple_structure(
                                            new external_single_structure(
                                                array(
                                                    'name'       => new external_value(PARAM_RAW, get_string('viewtitle', WEBSERVICE_LANG)),
                                                    'description' => new external_value(PARAM_RAW, get_string('viewdesc', WEBSERVICE_LANG)),
                                                    'id' => new external_value(PARAM_NUMBER, get_string('viewid', WEBSERVICE_LANG)),
                                                    'url' => new external_value(PARAM_RAW, get_string('viewrelativeurl', WEBSERVICE_LANG)),
                                                    'numviews' => new external_value(PARAM_NUMBER, get_string('viewscount', WEBSERVICE_LANG)),
                                                    'submittedtime' => new external_value(PARAM_RAW, get_string('submittedtime', WEBSERVICE_LANG)),
                                                    'fullurl' => new external_value(PARAM_RAW, get_string('viewfullurl', WEBSERVICE_LANG)),
                                                ), get_string('view', WEBSERVICE_LANG))
                                        ),
                                                ), get_string('collections', WEBSERVICE_LANG))
                                                ), get_string('views', WEBSERVICE_LANG)),
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
                            'id'              => new external_value(PARAM_INTEGER, get_string('idownersubmitportfolio', WEBSERVICE_LANG), VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                            'username'        => new external_value(PARAM_RAW, get_string('usernamesubmitportfolio', WEBSERVICE_LANG), VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                            'remoteuser'      => new external_value(PARAM_RAW, get_string('remoteusersubmitportfolio', WEBSERVICE_LANG), VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                            'email'           => new external_value(PARAM_RAW, get_string('owneremailsubmitportfolio', WEBSERVICE_LANG), VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                            'viewid'          => new external_value(PARAM_INTEGER, get_string('viewidsubmit1', WEBSERVICE_LANG), VALUE_REQUIRED),
                            'iscollection'    => new external_value(PARAM_BOOL, get_string('iscollection', WEBSERVICE_LANG), VALUE_OPTIONAL),
                            'lock'            => new external_value(PARAM_BOOL, get_string('lock', WEBSERVICE_LANG), VALUE_OPTIONAL),
                            'apilevel'        => new external_value(PARAM_RAW, get_string('apilevel', WEBSERVICE_LANG)),
                            'wwwroot'         => new external_value(PARAM_RAW, get_string('wwwroot', WEBSERVICE_LANG), VALUE_REQUIRED),
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
            $url = '';
            $token = null;
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
                throw new XmlrpcClientException('Invalid application level ' . hsc((string) $v['apilevel']));
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
                        $view->submit(null, $remotewwwroot, $userid);
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
                    $data[$plugin->name] = $classname::view_submit_external_data($viewid, $v['iscollection']);
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
                            'id'           => new external_value(PARAM_NUMBER, get_string('userid', WEBSERVICE_LANG)),
                            'username'     => new external_value(PARAM_RAW, get_string('username', WEBSERVICE_LANG)),
                            'viewid'       => new external_value(PARAM_INTEGER, get_string('viewid', WEBSERVICE_LANG)),
                            'iscollection' => new external_value(PARAM_BOOL, get_string('isacollection', WEBSERVICE_LANG)),
                            'lock'         => new external_value(PARAM_BOOL, get_string('locked', WEBSERVICE_LANG)),
                            'title'        => new external_value(PARAM_RAW, get_string('viewtitle', WEBSERVICE_LANG)),
                            'description'  => new external_value(PARAM_RAW, get_string('viewdesc', WEBSERVICE_LANG)),
                            'fullurl'      => new external_value(PARAM_RAW,  get_string('fullurl', WEBSERVICE_LANG)),
                            'url'          => new external_value(PARAM_RAW, get_string('relativeurl', WEBSERVICE_LANG)),
                            'accesskey'    => new external_value(PARAM_RAW, get_string('accesskey', WEBSERVICE_LANG)),
                            'apilevel'     => new external_value(PARAM_RAW, get_string('requestedapilvl', WEBSERVICE_LANG)),
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
                            'id'              => new external_value(PARAM_INTEGER, get_string('releaserid', WEBSERVICE_LANG), VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                            'username'        => new external_value(PARAM_RAW, get_string('releaserusername', WEBSERVICE_LANG), VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                            'remoteuser'      => new external_value(PARAM_RAW, get_string('releaserremoteusername', WEBSERVICE_LANG), VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                            'email'           => new external_value(PARAM_RAW, get_string('releaseremail', WEBSERVICE_LANG), VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                            'viewid'          => new external_value(PARAM_INTEGER, get_string('viewidsubmit1', WEBSERVICE_LANG), VALUE_REQUIRED),
                            'iscollection'    => new external_value(PARAM_BOOL, get_string('iscollection', WEBSERVICE_LANG), VALUE_OPTIONAL),
                            'viewoutcomes'    => new external_value(PARAM_RAW,  get_string('viewoutcomes', WEBSERVICE_LANG), VALUE_OPTIONAL),
                            'archiveonrelease' => new external_value(PARAM_BOOL, get_string('archiveonrelease', WEBSERVICE_LANG), VALUE_OPTIONAL),
                            'externalid'      => new external_value(PARAM_RAW, get_string('submissionextid', WEBSERVICE_LANG), VALUE_OPTIONAL),
                            'externalname'    => new external_value(PARAM_RAW, get_string('submissionextname', WEBSERVICE_LANG), VALUE_OPTIONAL),
                            'externalfullurl' => new external_value(PARAM_RAW, get_string('externalfullurl', WEBSERVICE_LANG), VALUE_OPTIONAL),
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
            $v_is_collection = (isset($v['iscollection']) && $v['iscollection']);

            require_once('view.php');
            $userid = $user->id;

            db_begin();
            $teacher = new User();
            $teacher->find_by_id($userid);
            $teacher_id = $teacher ? $teacher->id : 0;
            $external = new stdClass();
            $external->id = $v['externalid'];
            $external->name = $v['externalname'];
            $external->url = $v['externalfullurl'];
            if (isset($v['iscollection']) && $v['iscollection']) {
                require_once('collection.php');
                $collection = new Collection($v['viewid']);
                log_debug('releasing collection');
                if ($v['archiveonrelease']) {
                    $collection->pendingrelease($teacher, $external);
                }
                else {
                    $collection->release($teacher);
                }
            }
            else {
                $view = new View($v['viewid']);
                log_debug('releasing view');
                if ($v['archiveonrelease']) {
                    $view->pendingrelease($teacher, $external);
                }
                else {
                    $view->release($teacher);
                }
            }

            // Provide each artefact plugin the opportunity to handle the remote submission release
            foreach (plugins_installed('artefact') as $plugin) {
                safe_require('artefact', $plugin->name);
                $classname = generate_class_name('artefact', $plugin->name);
                if (is_callable($classname . '::view_release_external_data')) {
                    $classname::view_release_external_data(
                        $v['viewid'],
                        $v['viewoutcomes'],
                        $teacher_id,
                        $v_is_collection
                    );
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

    /**
     * Webservice parameter definition for input of "generate_view_for_plagiarism_test" method
     *
     * Returns description of method parameters
     *
     * @return external_function_parameters
     */
    public static function generate_view_for_plagiarism_test_parameters() {
        return new external_function_parameters(
            array(
                'views' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'viewid'          => new external_value(PARAM_INTEGER, get_string('viewidtotest', WEBSERVICE_LANG), VALUE_REQUIRED),
                            'iscollection'    => new external_value(PARAM_BOOL, get_string('iscollection', WEBSERVICE_LANG), VALUE_OPTIONAL),
                            'submittedhost'   => new external_value(PARAM_RAW, get_string('submittedhost', WEBSERVICE_LANG), VALUE_REQUIRED),
                            'exporttype'      => new external_value(PARAM_TEXT, get_string('liteexporttype', WEBSERVICE_LANG), VALUE_REQUIRED),
                        )
                    )
                )
            )
        );
    }

    /**
     * Check that the portfolio id supplied is a valid submitted portfolio
     *
     * @param array $v array of view webservice parameters
     *
     * @return array An array containing userobject, viewobject, portfoliotype, and portfolioid
     */
    private static function _check_valid_portfolio($v) {
        global $WEBSERVICE_INSTITUTION;
        $cid = $v['iscollection'];
        if ($cid) {
            $vid = get_field_sql("SELECT view FROM {collection_view} WHERE collection = ? ORDER BY displayorder LIMIT 1", array($v['viewid'])); // Make sure the collection is valid
        }
        else {
            $vid = get_field('view', 'id', 'id', $v['viewid']); // Make sure viewid is valid
            // doublecheck if view is part of a collection and if so return the collection export
            if ($collectionid = get_field_sql("SELECT collection FROM {collection_view} WHERE view = ? ORDER BY displayorder LIMIT 1", array($v['viewid']))) {
                $cid = true;
                $v['viewid'] = $collectionid;
            }
        }
        $portfoliotype = $cid ? 'collection' : 'view';
        $portfolioid = $v['viewid'];
        if (!$vid) {
            throw new WebserviceInvalidParameterException(get_string('invalidviewid', 'auth.webservice', $portfoliotype, $portfolioid));
        }
        else {
            require_once('view.php');
            $view = new View($vid);
        }

        if (empty($view->get('owner'))) {
            throw new WebserviceInvalidParameterException(get_string('invalidviewid', 'auth.webservice', $portfoliotype, $portfolioid));
        }
        $user = self::checkuser(array('id' => $view->get('owner')));
        // check the institution
        if (!mahara_external_in_institution($user, $WEBSERVICE_INSTITUTION)) {
            throw new WebserviceInvalidParameterException(get_string('invalidviewiduser', 'auth.webservice', $portfoliotype, $portfolioid));
        }
        // Check that the view has currently submitted
        if (!$view->is_submitted()) {
            throw new WebserviceInvalidParameterException(get_string('viewnotsubmitted', 'auth.webservice', $portfoliotype, $portfolioid));
        }
        if ($view->get('submittedhost') != $v['submittedhost']) {
            throw new WebserviceInvalidParameterException(get_string('viewnotsubmittedtothishost', 'auth.webservice', $portfoliotype, $portfolioid, $v['submittedhost']));
        }
        $userobj = new User();
        $userobj->find_by_id($user->id);

        return array($userobj, $view, $portfoliotype, $portfolioid);
    }

    /**
     * Generate the export lite file for the view / collection id supplied
     *
     * @param array $views  array of views
     * @return array An array of arrays describing generation outcome
     */
    public static function generate_view_for_plagiarism_test($views) {
        global $USER, $exporter;

        $params = self::validate_parameters(self::generate_view_for_plagiarism_test_parameters(),
                array('views' => $views));
        $result = array();
        foreach ($params['views'] as $v) {
            list($user, $view, $portfoliotype, $portfolioid) = self::_check_valid_portfolio($v);
            $exporttype = $v['exporttype'];
            safe_require('export', $exporttype);
            $class = generate_class_name('export', $exporttype);
            if (!$class::is_active()) {
                throw new WebserviceInvalidParameterException(get_string('exporttypenotavailable', 'auth.webservice', $exporttype));
            }
            // OK we are now looking good
            if ($portfoliotype == 'collection') {
                $views = get_column('collection_view', 'view', 'collection', $portfolioid);
                $exporter = new $class($user, $views, PluginExport::EXPORT_LIST_OF_COLLECTIONS);
            }
            else {
                $views = array($portfolioid);
                $exporter = new $class($user, $views, PluginExport::EXPORT_ARTEFACTS_FOR_VIEWS);
            }
            $exporter->includefeedback = true;

            $errors = $info = array();
            try {
                $info = $exporter->export(false);
                create_zip_archive($info['exportdir'], $info['zipfile'], $info['dirs']);
            }
            catch (SystemException $e) {
                $errors[] = get_string('exportzipfileerror', 'export', $e->getMessage());
            }

            // We need to move this to avoid it being deleted by the export_cleanup_old_exports cron
            // so we need to put it somewhere safe
            $exportlitedir = get_config('dataroot') . $exporttype . '/' . $portfoliotype . '/' . $portfolioid . '/';
            $filetimestamp = '';
            if (!check_dir_exists($exportlitedir)) {
                $errors[] = get_string('exportlitenotwritable', 'export', $exportlitedir);
            }
            else {
                copy($info['exportdir'] . $info['zipfile'], $exportlitedir . $info['zipfile']);
                $filetimestamp = preg_replace('/.*?-(\d+)\.zip$/', '$1', $info['zipfile']);
            }

            if (!empty($errors)) {
                throw new WebserviceInvalidParameterException(implode("\n", $errors));
            }

            // Return valid view id in $fileurl and get the download.php file to work out if a collection or not
            $fileurl = get_config('wwwroot') . 'webservice/download.php?t=' . $exporttype . '&c=' . $filetimestamp . '&v=' . $views[0];
            $data = array(
                'id'           => $user->get('id'),
                'username'     => $user->get('username'),
                'viewid'       => $portfolioid,
                'iscollection' => $v['iscollection'],
                'fileurl'      => $fileurl,
                'submittedhost' => $v['submittedhost'],
            );

            $result[]= $data;
        }

        return $result;
    }

    /**
     * Webservice parameter definition for output of "generate_view_for_plagiarism_test" method
     *
     * Returns description of method result value
     *
     * @return external_description
     */
    public static function generate_view_for_plagiarism_test_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                array(
                    'id'            => new external_value(PARAM_INTEGER, get_string('releaserid', WEBSERVICE_LANG), VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                    'username'      => new external_value(PARAM_RAW, get_string('releaserusername', WEBSERVICE_LANG), VALUE_OPTIONAL, null, NULL_ALLOWED, 'id'),
                    'viewid'        => new external_value(PARAM_INTEGER, get_string('viewid', WEBSERVICE_LANG)),
                    'iscollection'  => new external_value(PARAM_BOOL, get_string('iscollection', WEBSERVICE_LANG), VALUE_OPTIONAL),
                    'fileurl'      => new external_value(PARAM_RAW, get_string('fileurl', WEBSERVICE_LANG)),
                    'submittedhost' => new external_value(PARAM_RAW, get_string('submittedhost', WEBSERVICE_LANG)),
                )
            )
        );
    }
}
