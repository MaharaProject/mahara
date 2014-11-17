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
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 */

/**
 * Test the different web service protocols.
 *
 * @author jerome@moodle.com
 * @author     Piers Harding
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package web service
 * @copyright  Copyright (C) 2011 Catalyst IT Ltd (http://www.catalyst.net.nz)
 */

require_once 'TestBase.class.php';

/**
 * functional tests for institutions
 */
class WebServiceInstitutionTest extends TestBase {

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
            'mahara_institution_get_members' => true,
            'mahara_institution_get_requests' => true,
        );

        ////// WRITE DB tests ////
        $this->writetests = array(
            'mahara_institution_add_members' => true,
            'mahara_institution_remove_members' => true,
            'mahara_institution_invite_members' => true,
            'mahara_institution_decline_members' => true,
        );

        ///// Authentication types ////
        $this->auths = array('token', 'user');

        //performance testing: number of time the web service are run
        $this->iteration = 1;

    }

    ///// WEB SERVICE TEST FUNCTIONS

    // simple get users by ID
    function mahara_institution_get_members($client) {
        error_log('getting members');
        require_once(get_config('docroot') . 'lib/searchlib.php');

        $institution = new Institution('mahara');
        $data = institutional_admin_user_search('', $institution, 0);

        $function = 'mahara_institution_get_members';
        $params = array('institution' => 'mahara');
        $users = $client->call($function, $params);

        $this->assertEquals(count($users), $data['count']);
    }

    // simple get users by ID
    function mahara_institution_get_requests($client) {
        error_log('getting requests');
        require_once(get_config('docroot') . 'lib/searchlib.php');

        //Set test data
        $dbuser1 = $this->create_user1_for_update();
        $dbuser2 = $this->create_user2_for_update();

        $institution = new Institution($this->testinstitution);
        $institution->invite_users(array($dbuser1->id, $dbuser2->id));
        $dbinvites = get_records_array('usr_institution_request', 'institution', $this->testinstitution);

        $function = 'mahara_institution_get_requests';
        $params = array('institution' => $this->testinstitution);
        $users = $client->call($function, $params);

        $this->assertEquals(count($users), count($dbinvites));
    }

    // update user test
    function mahara_institution_invite_members($client) {
        error_log('invite members');

        //Set test data
        $dbuser1 = $this->create_user1_for_update();
        $dbuser2 = $this->create_user2_for_update();

        $this->clean_institution();

        //update the users by web service
        $function = 'mahara_institution_invite_members';
        $params = array('institution' => $this->testinstitution, 'users' => array(array('id' => $dbuser1->id), array('id' => $dbuser2->id),));
        $client->call($function, $params);

        $dbinvites = get_records_array('usr_institution_request', 'institution', $this->testinstitution);

        //compare DB user with the test data
        $this->assertEquals(count($params['users']), count($dbinvites));
    }

    // update user test
    function mahara_institution_add_members($client) {
        error_log('add members');

        //Set test data
        $dbuser1 = $this->create_user1_for_update();
        $dbuser2 = $this->create_user2_for_update();

        $this->clean_institution();

        // members before
        $dbmembers_before = get_records_array('usr_institution', 'institution', $this->testinstitution);

        //update the users by web service
        $function = 'mahara_institution_add_members';
        $params = array('institution' => $this->testinstitution, 'users' => array(array('id' => $dbuser1->id), array('id' => $dbuser2->id),));
        $client->call($function, $params);

        $dbmembers = get_records_array('usr_institution', 'institution', $this->testinstitution);

        //compare DB user with the test data
        $this->assertEquals(count($params['users']), count($dbmembers));
    }


    // update user test
    function mahara_institution_remove_members($client) {
        error_log('remove members');

        //Set test data
        $dbuser1 = $this->create_user1_for_update();
        $dbuser2 = $this->create_user2_for_update();

        $this->clean_institution();

        $institution = new Institution($this->testinstitution);
        $institution->add_members(array($dbuser1->id, $dbuser2->id));
        $dbmembers = get_records_array('usr_institution', 'institution', $this->testinstitution);
        $this->assertEquals(2, count($dbmembers));

        //update the users by web service
        $function = 'mahara_institution_remove_members';
        $params = array('institution' => $this->testinstitution, 'users' => array(array('id' => $dbuser1->id), array('id' => $dbuser2->id),));
        $client->call($function, $params);

        $dbmembers = get_records_array('usr_institution', 'institution', $this->testinstitution);
        $this->assertTrue(empty($dbmembers));
    }

    // update user test
    function mahara_institution_decline_members($client) {
        error_log('decline members');

        //Set test data
        $dbuser1 = $this->create_user1_for_update();
        $dbuser2 = $this->create_user2_for_update();

        $this->clean_institution();

        $institution = new Institution($this->testinstitution);
        $institution->addRequestFromUser($dbuser1);
        $institution->addRequestFromUser($dbuser2);

        $dbinvites = get_records_array('usr_institution_request', 'institution', $this->testinstitution);
        $this->assertEquals(2, count($dbinvites));

        //update the users by web service
        $function = 'mahara_institution_decline_members';
        $params = array('institution' => $this->testinstitution, 'users' => array(array('id' => $dbuser1->id), array('id' => $dbuser2->id),));
        $client->call($function, $params);

        $dbinvites = get_records_array('usr_institution_request', 'institution', $this->testinstitution);
        $this->assertTrue(empty($dbinvites));
    }
}
