<?php
/**
 * Test the different web service protocols.
 *
 * @package    mahara
 * @subpackage auth-webservice
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */


// disable the WSDL cache
ini_set("soap.wsdl_cache_enabled", "0");

require_once(get_config('libroot') . 'ddl.php');
require_once(get_config('libroot') . 'upgrade.php');

$path = get_config('docroot') . 'lib/zend';
set_include_path($path . PATH_SEPARATOR . get_include_path());

require_once(get_config('docroot') . 'webservice/lib.php');
require_once(get_config('docroot') . 'artefact/lib.php');
require_once('institution.php');
require_once('group.php');

/**
 * How to configure this unit tests:
 * 0- Enable the web service you wish to test
 * 1- Create a service with all functions
 * 2- Create a token associate this service and to an admin (or a user with all required capabilities)
 * 3- Configure setUp() function:
 *      a- write the token
 *      b- activate the protocols you wish to test
 *      c- activate the functions you wish to test (readonlytests and writetests arrays)
 *      d- set the number of times the web services are run
 * Do not run WRITE test function on a production site as they impact the DB (even though every
 * test should clean the modified data)
 *
 * How to write a new function:
 * 1- Add the function name to the array readonlytests/writetests
 * 2- Set it as false when you commit!
 * 3- write the function  - Do not prefix the function name by 'test'
 */

/**
 * Test base class that contains the setUp/tearDown functions common to all
 * tests
 *
 * Also, the testRun framework that runs the list of tests in each specific class
 * against all the protocols and auth methods
 */
class WebServiceTestBase extends MaharaUnitTest {

    public $testtoken;
    public $testrest;
    public $testxmlrpc;
    public $testsoap;
    public $timerrest;
    public $timerxmlrpc;
    public $timersoap;
    public $readonlytests;
    public $writetests;
    public $servicename;
    public $testuser;
    public $testpasswd;
    public $created_users;
    public $created_groups;
    public $test_institution;
    public $consumer;
    public $consumer_key;
    public $request_token;
    public $access_token;

    /**
     * Setup test data
     */
    protected function setUp() {
        // default current user to admin
        global $USER;
        $USER->id = 1;
        $USER->admin = 1;

        set_config('webservice_enabled', true);
        set_config('webservice_rest_enabled', true);
        set_config('webservice_xmlrpc_enabled', true);
        set_config('webservice_soap_enabled', true);
        set_config('webservice_oauth_enabled', true);

        //token to test
        $this->servicename = 'test webservices';
        $this->testuser = 'wstestuser';
        $this->testinstitution = 'mytestinstitutionone';

        // clean out first
        $this->tearDown();

        if (!$authinstance = get_record('auth_instance', 'institution', 'mahara', 'authname', 'webservice')) {
            $authinstance = new stdClass();
            $authinstance->instancename = 'webservice';
            $authinstance->institution = 'mahara';
            $authinstance->authname = 'webservice';
            $lastinstance = get_records_array('auth_instance', 'institution', 'mahara', 'priority DESC', '*', '0', '1');
            if ($lastinstance == false) {
                $authinstance->priority = 0;
            }
            else {
                $authinstance->priority = $lastinstance[0]->priority + 1;
            }
            $authinstance->id = insert_record('auth_instance', $authinstance, 'id', true);
        }
        $this->authinstance = $authinstance;
        $this->institution = new Institution($authinstance->institution);

        // create the new test user
        if (!$dbuser = get_record('usr', 'username', $this->testuser)) {
            db_begin();
            $new_user = new StdClass;
            $new_user->authinstance = $authinstance->id;
            $new_user->username     = $this->testuser;
            $new_user->firstname    = 'Firstname';
            $new_user->lastname     = 'Lastname';
            $new_user->password     = $this->testuser;
            $new_user->email        = $this->testuser . '@hogwarts.school.nz';
            $new_user->passwordchange = 0;
            $new_user->admin        = 1;
            $profilefields = new StdClass;
            $userid = create_user($new_user, $profilefields, $this->institution, $authinstance);
            $dbuser = get_record('usr', 'username', $this->testuser);
            db_commit();
        }

        // construct a test service from all available functions
        $dbservice = get_record('external_services', 'name', $this->servicename);
        if (empty($dbservice)) {
            $service = array('name' => $this->servicename, 'tokenusers' => 0, 'restrictedusers' => 0, 'enabled' => 1, 'component' => 'webservice', 'ctime' => db_format_timestamp(time()));
            insert_record('external_services', $service);
            $dbservice = get_record('external_services', 'name', $this->servicename);
        }
        $dbfunctions = get_records_array('external_functions', null, null, 'name');
        foreach ($dbfunctions as $function) {
            $sfexists = record_exists('external_services_functions', 'externalserviceid', $dbservice->id, 'functionname', $function->name);
            if (!$sfexists) {
                $service_function = array('externalserviceid' => $dbservice->id, 'functionname' => $function->name);
                insert_record('external_services_functions', $service_function);
                $dbservice->mtime = db_format_timestamp(time());
                update_record('external_services', $dbservice);
            }
        }

        // create an OAuth registry object
        require_once(get_config('docroot') . 'webservice/libs/oauth-php/OAuthServer.php');
        require_once(get_config('docroot') . 'webservice/libs/oauth-php/OAuthStore.php');
        require_once(get_config('docroot') . 'webservice/libs/oauth-php/OAuthRequester.php');
        $store = OAuthStore::instance('Mahara', array(), true);
        $new_app = array(
                    'application_title' => 'Test Application',
                    'application_uri'   => 'http://example.com',
                    'requester_name'    => $dbuser->firstname . ' ' . $dbuser->lastname,
                    'requester_email'   => $dbuser->email,
                    'callback_uri'      => 'http://example.com',
                    'institution'       => 'mahara',
                    'externalserviceid' => $dbservice->id,
          );
        $this->consumer_key = $store->updateConsumer($new_app, $dbuser->id, true);
        $this->consumer = (object) $store->getConsumer($this->consumer_key, $dbuser->id);

        // Now do the request and access token
        $this->request_token  = $store->addConsumerRequestToken($this->consumer_key, array());

        // authorise
        $verifier = $store->authorizeConsumerRequestToken($this->request_token['token'], $dbuser->id, 'localhost');

        // exchange access token
        $options = array();
        $options['verifier'] = $verifier;
        $this->access_token  = $store->exchangeConsumerRequestForAccessToken($this->request_token['token'], $options);

        // generate a test token
        $token = webservice_generate_token(EXTERNAL_TOKEN_PERMANENT, $dbservice, $dbuser->id);
        $dbtoken = get_record('external_tokens', 'token', $token);
        $this->testtoken = $dbtoken->token;

        // create an external test user instance
        $dbserviceuser = (object) array('externalserviceid' => $dbservice->id,
                        'userid' => $dbuser->id,
                        'institution' => 'mahara',
                        'ctime' => db_format_timestamp(time()),
                        'wssigenc' => 0,
                        'publickeyexpires' => 0);
        $dbserviceuser->id = insert_record('external_services_users', $dbserviceuser, 'id', true);

        // setup test groups
        $groupid = group_create(array(
            'shortname'      => 'mytestgroup1',
            'name'           => 'The test group 1',
            'description'    => 'a description for test group 1',
            'institution'    => 'mahara',
            'grouptype'      => 'standard',
            'open'           => 1,
            'controlled'     => 0,
            'request'        => 0,
            'submitpages'    => 0,
            'hidemembers'    => 0,
            'invitefriends'  => 0,
            'suggestfriends' => 0,
            'hidden'         => 0,
            'hidemembersfrommembers' => 0,
            'public'         => 0,
            'usersautoadded' => 0,
            'members'        => array($dbuser->id => 'admin'),
            'viewnotify'     => 0,
        ));

        // create test institution
        $dbinstitution = get_record('institution', 'name', $this->testinstitution);
        if (empty($dbinstitution)) {
            db_begin();
            $newinstitution = new StdClass;
            $institution = $newinstitution->name    = $this->testinstitution;
            $newinstitution->displayname            = $institution . ' - display name';
            $newinstitution->authplugin             = 'internal';
            $newinstitution->showonlineusers        = 1;
            $newinstitution->registerallowed        = 0;
            $newinstitution->theme                  =  null;
            $newinstitution->defaultquota           = get_config_plugin('artefact', 'file', 'defaultquota');
            $newinstitution->defaultmembershipperiod  = null;
            $newinstitution->maxuseraccounts        = null;
            $newinstitution->allowinstitutionpublicviews  = 1;
            insert_record('institution', $newinstitution);
            $authinstance = (object)array(
                'instancename' => 'internal',
                'priority'     => 0,
                'institution'  => $newinstitution->name,
                'authname'     => 'internal',
            );
            insert_record('auth_instance', $authinstance);
            db_commit();
        }

        //protocols to test
        $this->testrest = false;
        $this->testxmlrpc = false;
        $this->testsoap = false;

        ////// READ-ONLY DB tests ////
        $this->readonlytests = array(
        );

        ////// WRITE DB tests ////
        $this->writetests = array(
        );

        ///// Authentication types ////
        $this->auths = array(
        );

        //performance testing: number of time the web service are run
        $this->iteration = 1;

        // keep track of users created and deleted
        $this->created_users = array();

        // keep track of groups
        $this->created_groups = array();

        //DO NOT CHANGE
        //reset the timers
        $this->timerrest = 0;
        $this->timerxmlrpc = 0;
        $this->timersoap = 0;
    }

    /**
     * common test framework for all tests - cycles through the number
     * of iterations, auth types, and protocols
     */
    public function testRun() {
        $this->markTestSkipped('cURL requests need to be mocked properly. Skipping for now.');
        // do we have any tests
        if (!$this->testrest and !$this->testxmlrpc and !$this->testsoap) {
            log_debug("Web service unit tests are not run as not setup (see /webservice/simpletest/testwebservice.php)");
        }

        // need a token to test
        if (!empty($this->testtoken)) {

            // test the REST interface
            if ($this->testrest) {
                log_debug("Testing REST");
                $this->timerrest = time();
                require_once(get_config('docroot') . "webservice/rest/lib.php");
                // iterate the token and user auth types
                foreach ($this->auths as $type) {
                    log_debug("Auth Type: " . $type);
                    switch ($type) {
                        case 'token':
                             $restclient = new webservice_rest_client(get_config('wwwroot') . 'webservice/rest/server.php',
                                                                     array('wstoken' => $this->testtoken), $type);
                             break;
                        case 'user':
                             $restclient = new webservice_rest_client(get_config('wwwroot') . 'webservice/rest/server.php',
                                                                     array('wsusername' => $this->testuser, 'wspassword' => $this->testuser), $type);
                             break;
                        case 'oauth':
                             $restclient = new webservice_rest_client(get_config('wwwroot') . 'webservice/rest/server.php',
                                                                     array(), $type);
                             $restclient->set_oauth($this->consumer, $this->access_token);
                             break;
                    }
                    for ($i = 1; $i <= $this->iteration; $i = $i + 1) {
                        foreach ($this->readonlytests as $functionname => $run) {
                            if ($run) {
                                $this->{$functionname}($restclient);
                            }
                        }
                        foreach ($this->writetests as $functionname => $run) {
                            if ($run) {
                                $this->{$functionname}($restclient);
                            }
                        }
                    }
                }

                $this->timerrest = time() - $this->timerrest;
            }

            // test the XML-RPC interface
            if ($this->testxmlrpc) {
                log_debug("Testing XML RPC");
                $this->timerxmlrpc = time();
                require_once(get_config('docroot') . "webservice/xmlrpc/lib.php");
                // iterate the token and user auth types
                foreach (array('token', 'user') as $type) {
                    $xmlrpcclient = new webservice_xmlrpc_client(get_config('wwwroot') . 'webservice/xmlrpc/server.php',
                                                                 ($type == 'token' ? array('wstoken' => $this->testtoken) :
                                                                  array('wsusername' => $this->testuser, 'wspassword' => $this->testuser)));

                    for ($i = 1; $i <= $this->iteration; $i = $i + 1) {
                        foreach ($this->readonlytests as $functionname => $run) {
                            if ($run) {
                                $this->{$functionname}($xmlrpcclient);
                            }
                        }
                        foreach ($this->writetests as $functionname => $run) {
                            if ($run) {
                                $this->{$functionname}($xmlrpcclient);
                            }
                        }
                    }
                }

                $this->timerxmlrpc = time() - $this->timerxmlrpc;
            }

            // test the SOAP interface
            if ($this->testsoap) {
                log_debug("Testing SOAP");
                $this->timersoap = time();
                require_once(get_config('docroot') . "webservice/soap/lib.php");

                // iterate the token and user auth types
                foreach (array(array('wstoken' => $this->testtoken),
                               array('wsusername' => $this->testuser, 'wspassword' => $this->testuser),
                               array('wsse' => 1)) as $parms) {
                    // stop failed to load external entity error
                    libxml_disable_entity_loader(false);
                    if (isset($parms['wsse'])) {
                        //force SOAP synchronous mode
                        $soapclient = new webservice_soap_client(get_config('wwwroot') . 'webservice/soap/server.php', array('wsservice' => $this->servicename),
                                                                 array("features" => SOAP_WAIT_ONE_WAY_CALLS));
                        //when function return null
                        $wsseSoapClient = new webservice_soap_client_wsse(array($soapclient, '_doRequest'), $soapclient->wsdl, $soapclient->getOptions());
                        $wsseSoapClient->__setUsernameToken($this->testuser, $this->testuser);
                        $soapclient->setSoapClient($wsseSoapClient);
                    }
                    else {
                        //force SOAP synchronous mode
                        $soapclient = new webservice_soap_client(get_config('wwwroot') . 'webservice/soap/server.php', $parms,
                                                                 array("features" => SOAP_WAIT_ONE_WAY_CALLS));
                        //when function return null
                    }
                    $soapclient->setWsdlCache(false);
                    for ($i = 1; $i <= $this->iteration; $i = $i + 1) {
                        foreach ($this->readonlytests as $functionname => $run) {
                            if ($run) {
                                $this->{$functionname}($soapclient);
                            }
                        }
                        foreach ($this->writetests as $functionname => $run) {
                            if ($run) {
                                $this->{$functionname}($soapclient);
                            }
                        }
                    }
                }

                $this->timersoap = time() - $this->timersoap;
            }
        }
    }

    /**
     * reset a test institution
     */
    protected function clean_institution() {
        // clean down the institution
        $dbinstitution = get_record("institution", 'name', $this->testinstitution);
        if (!empty($dbinstitution)) {
            db_begin();
            $institution = new Institution($this->testinstitution);
            $dbinvites = get_records_array('usr_institution_request', 'institution', $this->testinstitution);
            if (!empty($dbinvites)) {
                $userids = array();
                foreach ($dbinvites as $dbinvite) {
                    $userids[]= $dbinvite->usr;
                }
                $institution->decline_requests($userids);
            }
            $dbmembers = get_records_array('usr_institution', 'institution', $this->testinstitution);
            if (!empty($dbmembers)) {
                $userids = array();
                foreach ($dbmembers as $dbmember) {
                    $userids[]= $dbmember->usr;
                }
                $institution->removeMembers($userids);
            }
            db_commit();
        }
    }

    /**
     * clean out all the test data
     */
    protected function tearDown() {

        // fix the connection - this gets lost because of the tests
        configure_dbconnection();

        // clean down the institution
        $this->clean_institution();

        // delete test token
        if ($this->testtoken) {
            delete_records('external_tokens', 'token', $this->testtoken);
        }

        // remove the web service descriptions
        $dbservice = get_record('external_services', 'name', $this->servicename);
        if ($dbservice) {
            $dbregistry = get_record('oauth_server_registry', 'externalserviceid', $dbservice->id);
            if ($dbregistry) {
                delete_records('oauth_server_token', 'osr_id_ref', $dbregistry->id);
            }
            delete_records('oauth_server_registry', 'externalserviceid', $dbservice->id);
            delete_records('external_services_users', 'externalserviceid', $dbservice->id);
            delete_records('external_tokens', 'externalserviceid', $dbservice->id);
            delete_records('external_services_functions', 'externalserviceid', $dbservice->id);
            delete_records('external_services', 'id', $dbservice->id);
        }

        // remove the test user
        $dbuser = get_record('usr', 'username', $this->testuser);
        if ($dbuser) {
            $this->created_users[]= $dbuser->id;
        }

        // remove all left over users
        if ($this->created_users) {
            foreach ($this->created_users as $userid) {
                delete_user($userid);
            }
        }

        // remove left over groups
        $dbgroup = get_record('group', 'shortname', 'mytestgroup1', 'institution', 'mahara');
        if ($dbgroup) {
            $this->created_groups[]= $dbgroup->id;
        }
        if ($this->created_groups) {
            foreach ($this->created_groups as $groupid) {
                group_delete($groupid);
            }
        }
    }

    /**
     * password encryption copied from auth/internal - needed for comparison
     * Given a password and an optional salt, encrypt the given password.
     *
     * Passwords are stored in SHA1 form.
     *
     * @param string $password The password to encrypt
     * @param string $salt     The salt to use to encrypt the password
     * @param string $alg      The algorithm to use, defaults to $6$ which is SHA512
     * @param string $sitesalt A salt to combine with the user's salt to add an extra layer or salting
     * @todo salt mandatory
     */
    function encrypt_password($password, $salt='', $alg='$6$', $sitesalt='') {
        if ($salt == '') {
            $salt = substr(md5(rand(1000000, 9999999)), 2, 8);
        }
        if ($alg == '$6$') {
            // $6$ is the identifier for the SHA512 algorithm
            // Return a hash which is sha512(originalHash, salt), where original is sha1(salt + password)
            $password = sha1($salt . $password);
            // Generate a salt based on a supplied salt and the passwordsaltmain
            $fullsalt = substr(md5($sitesalt . $salt), 0, 16); // SHA512 expects 16 chars of salt
        }
        else { // This is most likely bcrypt $2a$, but any other algorithm can take up to 22 chars of salt
            // Generate a salt based on a supplied salt and the passwordsaltmain
            $fullsalt = substr(md5($sitesalt . $salt), 0, 22); // bcrypt expects 22 chars of salt
        }
        $hash = crypt($password, $alg . $fullsalt);
        // Strip out the computed salt
        // We strip out the salt hide the computed salt (in case the sitesalt was used which isn't in the database)
        $hash = substr($hash, 0, strlen($alg)) . substr($hash, strlen($alg) + strlen($fullsalt));
        return $hash;
    }

    /**
     * Create test users from one place to share between update
     * and favourites
     */
    function create_user1_for_update() {
        //Set test data
        //can run this test only if test usernames don't exist
        foreach (array( 'veryimprobabletestusername1', 'veryimprobabletestusername1_updated') as $username) {
            $existinguser = get_record('usr', 'username', $username);
            if (!empty($existinguser)) {
                delete_user($existinguser->id);
            }
        }

        //a full user: user1
        $user1 = new stdClass();
        $user1->authinstance = $this->authinstance->id;
        $user1->username = 'veryimprobabletestusername1';
        if ($dbuser1 = get_record('usr', 'username', $user1->username)) {
            return $dbuser1;
        }
        $user1->password = 'testpassword1';
        $user1->firstname = 'testfirstname1';
        $user1->lastname = 'testlastname1';
        $user1->email = 'testemail1@hogwarts.school.nz';
        $user1->studentid = 'testidnumber1';
        $user1->preferredname = 'Hello World!';
        $user1->city = 'testcity1';
        $user1->country = 'au';
        $profilefields = new StdClass;
        db_begin();
        $userid = create_user($user1, $profilefields, $this->institution, $this->authinstance);
        db_commit();
        $dbuser1 = get_record('usr', 'username', $user1->username);
        $this->assertTrue(($dbuser1 instanceof stdClass));
        $userobj = new User();
        $userobj = $userobj->find_by_id($dbuser1->id);
        $authobj_tmp = AuthFactory::create($dbuser1->authinstance);
        $authobj_tmp->change_password($userobj, $dbuser1->password, false);
        $this->created_users[]= $dbuser1->id;
        $dbuser1 = get_record('usr', 'username', $user1->username);
        return $dbuser1;
    }

    /**
     * Create test users from one place to share between update
     * and favourites
     */
    function create_user2_for_update() {
        //can run this test only if test usernames don't exist
        foreach (array( 'veryimprobabletestusername2', 'veryimprobabletestusername2_updated') as $username) {
            $existinguser = get_record('usr', 'username', $username);
            if (!empty($existinguser)) {
                delete_user($existinguser->id);
            }
        }

        $user2 = new stdClass();
        $user2->authinstance = $this->authinstance->id;
        $user2->username = 'veryimprobabletestusername2';
        if ($dbuser2 = get_record('usr', 'username', $user2->username)) {
            return $dbuser2;
        }
        $user2->password = 'testpassword2';
        $user2->firstname = 'testfirstname2';
        $user2->lastname = 'testlastname2';
        $user2->email = 'testemail1@hogwarts.school.nz';
        $profilefields = new StdClass;
        db_begin();
        $userid = create_user($user2, $profilefields, $this->institution, $this->authinstance);
        db_commit();
        $dbuser2 = get_record('usr', 'username', $user2->username);
        $this->assertTrue(($dbuser2 instanceof stdClass));
        $userobj = new User();
        $userobj = $userobj->find_by_id($dbuser2->id);
        $authobj_tmp = AuthFactory::create($dbuser2->authinstance);
        $authobj_tmp->change_password($userobj, $dbuser2->password, false);
        $this->created_users[]= $dbuser2->id;
        $dbuser2 = get_record('usr', 'username', $user2->username);
        return $dbuser2;
    }

    /**
     * get rid of a zero id record that I created and cannot easily delete
     *
     * @param array $favs
     * @return array $favs
     */
    protected static function prune_nasty_zero($favs) {
        $zero = false;
        foreach ($favs as $k => $fav) {
            $fav = (object)$fav;
            if ($fav->id == 0) {
                $zero = $k;
                break;
            }
        }
        if ($zero !== false) {
            unset($favs["$zero"]);
        }
        return $favs;
    }

    /**
     * Find the non-admin userid
     *
     * @param array $favs
     * @return int $fav
     */
    protected static function find_new_fav($favs) {
        foreach ($favs as $k => $fav) {
            if ($fav->id > 1) {
                return $fav->id;
            }
        }
        return false;
    }
}
