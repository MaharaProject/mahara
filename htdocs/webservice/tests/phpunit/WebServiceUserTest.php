<?php
/**
 * Test the different web service protocols.
 *
 * @package    mahara
 * @subpackage auth-webservice
 * @author     Catalyst IT Ltd
 * @author     jerome@moodle.com
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

require_once('WebServiceTestBase.class.php');

/**
 * funcitonal tests for user API
 */
class WebServiceUserTest extends WebServiceTestBase {

    /**
    * local setup - outline the test functions in the framework
    *
    * @see TestBase::setUp()
    */
    public function setUp() {
        // default current user to admin
        parent::setUp();

        //protocols to test
        $this->testrest = true;
        $this->testxmlrpc = true;
        $this->testsoap = true;

        ////// READ-ONLY DB tests ////
        $this->readonlytests = array(
            'mahara_user_get_users_by_id' => true,
            'mahara_user_get_users' => true,
        );

        ////// WRITE DB tests ////
        $this->writetests = array(
            'mahara_user_create_users' => true,
            'mahara_user_update_users' => true,
            'mahara_user_delete_users' => true,
            'mahara_user_update_favourites' => true,
        );

        ///// Authentication types ////
        $this->auths = array('token', 'user', 'oauth');

        //performance testing: number of time the web service are run
        $this->iteration = 1;

    }

    ///// WEB SERVICE TEST FUNCTIONS

    // simple get users by ID
    function mahara_user_get_users_by_id($client) {

        error_log('getting users by id');

        $dbusers = get_records_sql_array('SELECT u.id AS id FROM {usr} u
                        INNER JOIN {auth_instance} ai ON u.authinstance = ai.id
                        WHERE u.deleted = 0 AND ai.institution = \'mahara\'', array());
        $users_in = array();
        foreach ($dbusers as $dbuser) {
            if ($dbuser->id == 0) continue;
            $users_in[] = array('id' => $dbuser->id);
        }
        $function = 'mahara_user_get_users_by_id';

        $params = array('users' => $users_in);

        // standard call
        $users = $client->call($function, $params);
        $this->assertEquals(count($users), count($users_in));

        // JSON call
        $users = $client->call($function, $params, true);
        $this->assertEquals(count($users), count($users_in));
    }

    // simple get all users
    function mahara_user_get_users($client) {

        error_log('getting all users');

        $function = 'mahara_user_get_users';
        $dbusers = get_records_sql_array('SELECT u.id AS id FROM {usr} u
                        INNER JOIN {auth_instance} ai ON u.authinstance = ai.id
                        WHERE u.deleted = 0 AND ai.institution = \'mahara\'', array());
        $userids = array();
        foreach ($dbusers as $dbuser) {
            if ($dbuser->id == 0) continue;
            $userids[] = $dbuser->id;
        }
        $params = array();
        $users = $client->call($function, $params);

        $this->assertEquals(count($users), count($userids));
    }

    // create user test
    function mahara_user_create_users($client) {
        //Test data
        //a full user: user1
        $user1 = new stdClass();
        $user1->username = 'testusername1';
        $user1->password = 'testpassword1';
        $user1->firstname = 'testfirstname1';
        $user1->lastname = 'testlastname1';
        $user1->email = 'testemail1@hogwarts.school.nz';
        $user1->auth = 'internal';
        $user1->institution = 'mahara';
        $user1->studentid = 'testidnumber1';
        $user1->preferredname = 'Hello World!';
        $user1->city = 'testcity1';
        $user1->country = 'au';
        //a small user: user2
        $user2 = new stdClass();
        $user2->username = 'testusername2';
        $user2->password = 'testpassword2';
        $user2->firstname = 'testfirstname2';
        $user2->lastname = 'testlastname2';
        $user2->email = 'testemail1@hogwarts.school.nz';
        $user2->auth = 'webservice';
        $user2->institution = 'mahara';
        $users = array($user1, $user2);

        //do not run the test if user1 or user2 already exists
        foreach (array($user1->username, $user2->username) as $username) {
            $existinguser = get_record('usr', 'username', $username);
            if (!empty($existinguser)) {
                delete_user($existinguser->id);
            }
        }

        $function = 'mahara_user_create_users';
        $params = array('users' => $users);
        $resultusers = $client->call($function, $params);

        // store users for deletion at the end
        foreach ($resultusers as $u) {
            $this->created_users[]= $u['id'];
        }
        $this->assertEquals(count($users), count($resultusers));

        //retrieve user1 from the DB and check values
        $dbuser1 = get_record('usr', 'username', $user1->username);
        $this->assertEquals($dbuser1->firstname, $user1->firstname);
        $this->assertEquals($dbuser1->password,
                self::encrypt_password($user1->password, $dbuser1->salt, '$2a$' . get_config('bcrypt_cost') . '$', get_config('passwordsaltmain')));
        $this->assertEquals($dbuser1->lastname, $user1->lastname);
        $this->assertEquals($dbuser1->email, $user1->email);
        $this->assertEquals($dbuser1->studentid, $user1->studentid);
        $this->assertEquals($dbuser1->preferredname, $user1->preferredname);
        foreach (array('city', 'country') as $field) {
            $artefact = get_profile_field($dbuser1->id, $field);
            $this->assertEquals($artefact, $user1->{$field});
        }

        //retrieve user2 from the DB and check values
        $dbuser2 = get_record('usr', 'username', $user2->username);
        $this->assertEquals($dbuser2->firstname, $user2->firstname);
        $this->assertEquals($dbuser2->password,
                self::encrypt_password($user2->password, $dbuser2->salt, '$2a$' . get_config('bcrypt_cost') . '$', get_config('passwordsaltmain')));
        $this->assertEquals($dbuser2->lastname, $user2->lastname);
        $this->assertEquals($dbuser2->email, $user2->email);

        // test errors
        try {
            $resultusers = $client->call($function, $params);
        }
        catch (Exception $e) {
            switch (get_class($client)) {
                case 'webservice_xmlrpc_client':
                    $this->assertEquals('Zend_XmlRpc_Client_FaultException', get_class($e));
                    break;
                case 'webservice_soap_client':
                    $this->assertEquals('SoapFault', get_class($e));
                    break;
                default:
                    $this->assertEquals('Exception', get_class($e));
                    $this->assertEquals('webservice_rest_client', get_class($client));
                    break;
            }
            $this->assertRegExp('/Username already exists/', $e->getMessage());
        }
    }

    // delete user test
    function mahara_user_delete_users($client) {
        //Set test data
        //a full user: user1
        if (!$authinstance = get_record('auth_instance', 'institution', 'mahara', 'authname', 'webservice')) {
            throw new WebserviceInvalidParameterException('Invalid authentication type: mahara/webservce');
        }
        $institution = new Institution($authinstance->institution);

        //can run this test only if test usernames don't exist
        foreach (array( 'deletetestusername1', 'deletetestusername2') as $username) {
            $existinguser = get_record('usr', 'username', $username);
            if (!empty($existinguser)) {
                delete_user($existinguser->id);
            }
        }
        db_begin();
        $new_user = new StdClass;
        $new_user->authinstance = $authinstance->id;
        $new_user->username     = 'deletetestusername1';
        $new_user->firstname    = $new_user->username;
        $new_user->lastname     = $new_user->username;
        $new_user->password     = $new_user->username;
        $new_user->email        = $new_user->username . '@hogwarts.school.nz';
        $new_user->passwordchange = 0;
        $new_user->admin        = 0;
        $profilefields = new StdClass;
        $userid = create_user($new_user, $profilefields, $institution, $authinstance);
        db_commit();
        $dbuser1 = get_record('usr', 'username', $new_user->username);
        $this->assertTrue(($dbuser1 instanceof stdClass));
        $this->created_users[]= $dbuser1->id;

        db_begin();
        $new_user = new StdClass;
        $new_user->authinstance = $authinstance->id;
        $new_user->username     = 'deletetestusername2';
        $new_user->firstname    = $new_user->username;
        $new_user->lastname     = $new_user->username;
        $new_user->password     = $new_user->username;
        $new_user->email        = $new_user->username . '@hogwarts.school.nz';
        $new_user->passwordchange = 0;
        $new_user->admin        = 0;
        $profilefields = new StdClass;
        $userid = create_user($new_user, $profilefields, $institution, $authinstance);
        db_commit();
        $dbuser2 = get_record('usr', 'username', $new_user->username);
        $this->assertTrue(($dbuser2 instanceof stdClass));
        $this->created_users[]= $dbuser2->id;

        //delete the users by webservice
        $function = 'mahara_user_delete_users';
        $params = array('users' => array(array('id' => $dbuser1->id), array('id' => $dbuser2->id)));
        $client->call($function, $params);

        //search for them => TESTS they don't exists
        foreach (array($dbuser1, $dbuser2) as $user) {
            $user = get_record('usr', 'id', $user->id, 'deleted', 0);
            $this->assertTrue(empty($user));
        }

        // test errors
        try {
            $resultusers = $client->call($function, $params);
            var_dump($resultusers);
        }
        catch (Exception $e) {
            switch (get_class($client)) {
                case 'webservice_xmlrpc_client':
                    $this->assertEquals('Zend_XmlRpc_Client_FaultException', get_class($e));
                    break;
                case 'webservice_soap_client':
                    $this->assertEquals('SoapFault', get_class($e));
                    break;
                default:
                    $this->assertEquals('Exception', get_class($e));
                $this->assertEquals('webservice_rest_client', get_class($client));
                break;
            }
            $this->assertRegExp('/invalid user/', $e->getMessage());
        }
    }

    // update user test
    function mahara_user_update_users($client) {
        //Set test data
        $dbuser1 = $this->create_user1_for_update();
        $dbuser2 = $this->create_user2_for_update();

        //update the test data
        $user1 = new stdClass();
        $user1->id = $dbuser1->id;
        $user1->username = 'veryimprobabletestusername1_updated';
        $user1->password = 'testpassword1_updated';
        $user1->firstname = 'testfirstname1_updated';
        $user1->lastname = 'testlastname1_updated';
        $user1->email = 'testemail1_updated@hogwarts.school.nz';
        $user1->studentid = 'testidnumber1_updated';
        $user1->preferredname = 'Hello World!_updated';
        $user1->city = 'testcity1_updated';
        $user1->country = 'au';
        $user2 = new stdClass();
        $user2->id = $dbuser2->id;
        $user2->username = 'veryimprobabletestusername2_updated';
        $user2->password = 'testpassword2_updated';
        $user2->firstname = 'testfirstname2_updated';
        $user2->lastname = 'testlastname2_updated';
        $user2->email = 'testemail1_updated@hogwarts.school.nz';
        $users = array($user1, $user2);

        //update the users by web service
        $function = 'mahara_user_update_users';
        $params = array('users' => $users);
        $client->call($function, $params);

        //compare DB user with the test data
        $dbuser1 = get_record('usr', 'username', $user1->username);
        $this->assertEquals($dbuser1->firstname, $user1->firstname);
        $this->assertEquals($dbuser1->password,
                self::encrypt_password($user1->password, $dbuser1->salt, '$2a$' . get_config('bcrypt_cost') . '$', get_config('passwordsaltmain')));
        $this->assertEquals($dbuser1->lastname, $user1->lastname);
        $this->assertEquals($dbuser1->email, $user1->email);
        $this->assertEquals($dbuser1->studentid, $user1->studentid);
        $this->assertEquals($dbuser1->preferredname, $user1->preferredname);
        foreach (array('city', 'country') as $field) {
            $artefact = get_profile_field($dbuser1->id, $field);
            $this->assertEquals($artefact, $user1->{$field});
        }

        $dbuser2 = get_record('usr', 'username', $user2->username);
        $this->assertEquals($dbuser2->firstname, $user2->firstname);
        $this->assertEquals($dbuser2->password,
                self::encrypt_password($user2->password, $dbuser2->salt, '$2a$' . get_config('bcrypt_cost') . '$', get_config('passwordsaltmain')));
        $this->assertEquals($dbuser2->lastname, $user2->lastname);
        $this->assertEquals($dbuser2->email, $user2->email);
    }

    // update user test
    function mahara_user_update_favourites($client) {
        //Set test data
        $dbuser1 = $this->create_user1_for_update();
        $dbuser2 = $this->create_user2_for_update();

        //update the test data
        $user1 = new stdClass();
        $user1->id = $dbuser1->id;
        $user1->shortname = 'testshortname1';
        $user1->institution = 'mahara';
        $user1->favourites = array(array('id' => 1), array('username' => $dbuser2->username));
        $user2 = new stdClass();
        $user2->username = $dbuser2->username;
        $user2->shortname = 'testshortname1';
        $user2->institution = 'mahara';
        $user2->favourites = array(array('id' => 1), array('username' => $dbuser1->username));
        $users = array($user1, $user2);

        //update the users by web service
        $function = 'mahara_user_update_favourites';
        $params = array('users' => $users);
        $client->call($function, $params);

        // check the new favourites lists
        $fav1 = self::prune_nasty_zero(get_user_favorites($dbuser1->id, 100, 0));
        $fav2 = self::prune_nasty_zero(get_user_favorites($dbuser2->id, 100, 0));
        $this->assertEquals(count($fav1), count($user1->favourites));
        $this->assertEquals($dbuser2->id, self::find_new_fav($fav1));
        $this->assertEquals(count($fav2), count($user2->favourites));
        $this->assertEquals($dbuser1->id, self::find_new_fav($fav2));

        $function = 'mahara_user_get_favourites';
        $params = array('users' => array(array('shortname' => 'testshortname1', 'userid' => $dbuser1->id),array('shortname' => 'testshortname1', 'userid' => $dbuser2->id)));
        $users = $client->call($function, $params);
        foreach ($users as $user) {
            $favs = self::prune_nasty_zero($user['favourites']);
            $this->assertEquals(count($favs), count($user1->favourites));
            $this->assertEquals($user['shortname'], $user1->shortname);
            $this->assertEquals($user['institution'], $user1->institution);
        }

        // get all favourites
        $function = 'mahara_user_get_all_favourites';
        $params = array('shortname' => 'testshortname1');
        $users = $client->call($function, $params);
        $this->assertTrue(count($users) >= 2);
        foreach ($users as $user) {
            // skip users that we don't know
            if ($user['id'] != $dbuser1->id && $user['id'] != $dbuser2->id) {
                continue;
            }
            $favs = self::prune_nasty_zero($user['favourites']);
            $this->assertEquals(count($favs), count($user1->favourites));
            $this->assertEquals($user['shortname'], $user1->shortname);
            $this->assertEquals($user['institution'], $user1->institution);
        }
    }
}
