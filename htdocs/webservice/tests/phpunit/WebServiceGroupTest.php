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
 * functional tests for Groups
 */
class WebServiceGroupTest extends WebServiceTestBase {

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
            'mahara_group_get_groups_by_id' => true,
            'mahara_group_get_groups' => true,
        );

        ////// WRITE DB tests ////
        $this->writetests = array(
            'mahara_group_create_groups' => true,
            'mahara_group_update_groups' => true,
            'mahara_group_delete_groups' => true,
            'mahara_group_update_group_members' => true,
        );

        ///// Authentication types ////
        $this->auths = array('token', 'user', 'oauth');

        //performance testing: number of time the web service are run
        $this->iteration = 1;
    }

    ///// WEB SERVICE TEST FUNCTIONS

    // simple get groups by ID
    function mahara_group_get_groups_by_id($client) {
        $dbgroups = get_records_sql_array('SELECT * FROM {group} WHERE institution = ? AND shortname = ? AND deleted = 0', array('mahara', 'mytestgroup1'));
        $groupids = array();
        foreach ($dbgroups as $dbgroup) {
            if ($dbgroup->id == 0) continue;
            $groupids[] = array('id' => $dbgroup->id);
        }
        $function = 'mahara_group_get_groups_by_id';

        $params = array('groups' => $groupids);
        $groups = $client->call($function, $params);
        $this->assertEquals(count($groups), count($groupids));
    }

    // simple get all groups
    function mahara_group_get_groups($client) {
        $function = 'mahara_group_get_groups';
        $dbgroups = get_records_sql_array('SELECT * FROM {group} WHERE institution = ? AND shortname = ? AND deleted = 0', array('mahara', 'mytestgroup1'));
        $params = array();
        $groups = $client->call($function, $params);

        $this->assertEquals(count($groups), count($groups));
    }

    // create user test
    function mahara_group_create_groups($client) {
        //Set test data
        $dbuser1 = $this->create_user1_for_update();
        $dbuser2 = $this->create_user2_for_update();

        //Test data
        //a full group: group1
        $group1 = new stdClass();
        $group1->name           = 'The test group 1 - create';
        $group1->shortname      = 'testgroupshortname1';
        $group1->description    = 'a description for test group 1';
        $group1->institution    = 'mahara';
        $group1->grouptype      = 'course';
        $group1->editroles      = 'notmember';
        $group1->request        = 1;
        $group1->members        = array(array('id' => $dbuser1->id, 'role' => 'admin'), array('id' => $dbuser2->id, 'role' => 'admin'));

        //a small group: group2
        $group2 = new stdClass();
        $group2->shortname      = 'testgroupshortname2';
        $group2->name           = 'The test group 2 - create';
        $group2->description    = 'a description for test group 2';
        $group2->institution    = 'mahara';
        $group2->grouptype      = 'standard';
        $group2->open           = 1;
        $group2->request        = 0;
        $group2->controlled     = 0;
        $group2->submitpages    = 0;
        $group2->public         = 0;
        $group2->usersautoadded = 0;
        $group2->members        = array(array('username' => $dbuser1->username, 'role' => 'admin'), array('username' => $dbuser2->username, 'role' => 'admin'));
        $groups = array($group1, $group2);

        //do not run the test if group1 or group2 already exists
        foreach (array($group1->shortname, $group2->shortname) as $shortname) {
            $existinggroup = get_record('group', 'shortname', $shortname, 'institution', 'mahara');
            if (!empty($existinggroup)) {
                group_delete($existinggroup->id);
            }
        }

        $function = 'mahara_group_create_groups';
        $params = array('groups' => $groups);
        $resultgroups = $client->call($function, $params);

        // store groups for deletion at the end
        foreach ($resultgroups as $g) {
            $this->created_groups[]= $g['id'];
        }
        $this->assertEquals(count($groups), count($resultgroups));
        $dbgroup1 = get_record('group', 'shortname', $group1->shortname, 'institution', 'mahara');
        $dbgroupmembers1 = get_records_array('group_member', 'group', $dbgroup1->id);

        $dbgroup2 = get_record('group', 'shortname', $group2->shortname, 'institution', 'mahara');
        $dbgroupmembers2 = get_records_array('group_member', 'group', $dbgroup2->id);

        $dbgroup1->open = ($dbgroup1->jointype == 'open' ? 1 : 0);
        $dbgroup1->controlled = ($dbgroup1->jointype == 'controlled' ? 1 : 0);
        $dbgroup1->submitpages = (isset($dbgroup1->submitpages) ? $dbgroup1->submitpages : 0);
        $dbgroup2->open = ($dbgroup2->jointype == 'open' ? 1 : 0);
        $dbgroup2->controlled = ($dbgroup2->jointype == 'controlled' ? 1 : 0);
        $dbgroup2->submitpages = (isset($dbgroup2->submitpages) ? $dbgroup2->submitpages : 0);

        //retrieve groups from the DB and check values
        $this->assertEquals($dbgroup1->name, $group1->name);
        $this->assertEquals($dbgroup1->description, $group1->description);
        $this->assertEquals($dbgroup1->grouptype, $group1->grouptype);
        $this->assertEquals($dbgroup1->category, null);
        $this->assertEquals($dbgroup1->editroles, $group1->editroles);
        $this->assertEquals($dbgroup1->open, 0);
        $this->assertEquals($dbgroup1->request, 1);
        $this->assertEquals($dbgroup1->controlled, 0);
        $this->assertEquals($dbgroup1->submitpages, 0);
        $this->assertEquals($dbgroup1->public, 0);
        $this->assertEquals($dbgroup1->usersautoadded, 0);
        $this->assertEquals($dbgroup1->viewnotify, 1);
        // current user added as admin
        $this->assertEquals(count($dbgroupmembers1), count($group1->members) + 1);

        $this->assertEquals($dbgroup2->name, $group2->name);
        $this->assertEquals($dbgroup2->description, $group2->description);
        $this->assertEquals($dbgroup2->grouptype, $group2->grouptype);
        $this->assertEquals($dbgroup2->open, $group2->open);
        $this->assertEquals($dbgroup2->request, $group2->request);
        $this->assertEquals($dbgroup2->controlled, $group2->controlled);
        $this->assertEquals($dbgroup2->submitpages, $group2->submitpages);
        $this->assertEquals($dbgroup2->public, $group2->public);
        $this->assertEquals($dbgroup2->usersautoadded, $group2->usersautoadded);
        $this->assertEquals($dbgroup2->viewnotify, 1);
        // current user added as admin
        $this->assertEquals(count($dbgroupmembers2), count($group2->members) + 1);
    }

    // delete user test
    function mahara_group_delete_groups($client) {
        //Set test data
        $dbuser1 = $this->create_user1_for_update();
        $dbuser2 = $this->create_user2_for_update();

        //Test data
        //a full group: group1
        $group1 = new stdClass();
        $group1->name           = 'The test group 1 - create';
        $group1->shortname      = 'testgroupshortname1';
        $group1->description    = 'a description for test group 1';
        $group1->institution    = 'mahara';
        $group1->grouptype      = 'standard';
        $group1->open           = 1;
        $group1->request        = 0;
        $group1->controlled     = 0;
        $group1->submitpages    = 0;
        $group1->public         = 0;
        $group1->usersautoadded = 0;
        $group1->members        = array($dbuser1->id => 'admin', $dbuser2->id => 'admin');

        //a small group: group2
        $group2 = new stdClass();
        $group2->shortname      = 'testgroupshortname2';
        $group2->name           = 'The test group 2 - create';
        $group2->description    = 'a description for test group 2';
        $group2->institution    = 'mahara';
        $group2->grouptype      = 'standard';
        $group2->open           = 1;
        $group2->request        = 0;
        $group2->controlled     = 0;
        $group2->submitpages    = 0;
        $group2->public         = 0;
        $group2->usersautoadded = 0;
        $group2->members        = array($dbuser1->id => 'admin', $dbuser2->id => 'admin');

        //do not run the test if group1 or group2 already exists
        foreach (array($group1->shortname, $group2->shortname) as $shortname) {
            $existinggroup = get_record('group', 'shortname', $shortname, 'institution', 'mahara');
            if (!empty($existinggroup)) {
                group_delete($existinggroup->id);
            }
        }

        // setup test groups
        $groupid1 = group_create((array) $group1);
        $groupid2 = group_create((array) $group2);

        $dbgroup1 = get_record('group', 'shortname', $group1->shortname, 'institution', 'mahara');
        $dbgroup2 = get_record('group', 'shortname', $group2->shortname, 'institution', 'mahara');

        //delete the users by webservice
        $function = 'mahara_group_delete_groups';
        $params = array('groups' => array(array('id' => $dbgroup1->id), array('shortname' => $dbgroup2->shortname, 'institution' => $dbgroup2->institution)));
        $client->call($function, $params);

        //search for them => TESTS they don't exists
        foreach (array($dbgroup1, $dbgroup2) as $group) {
            $group = get_record('group', 'id', $group->id, 'deleted', 0);
            $this->assertTrue(empty($group));
        }
    }

    // update user test
    function mahara_group_update_groups($client) {
        //Set test data
        $dbuser1 = $this->create_user1_for_update();
        $dbuser2 = $this->create_user2_for_update();

        //Test data
        //a full group: group1
        $group1 = new stdClass();
        $group1->name           = 'The test group 1 - create';
        $group1->shortname      = 'testgroupshortname1';
        $group1->description    = 'a description for test group 1';
        $group1->institution    = 'mahara';
        $group1->grouptype      = 'standard';
        $group1->open           = 1;
        $group1->request        = 0;
        $group1->controlled     = 0;
        $group1->submitpages    = 0;
        $group1->public         = 0;
        $group1->usersautoadded = 0;
        $group1->members        = array($dbuser1->id => 'admin', $dbuser2->id => 'admin');

        //a small group: group2
        $group2 = new stdClass();
        $group2->shortname      = 'testgroupshortname2';
        $group2->name           = 'The test group 2 - create';
        $group2->description    = 'a description for test group 2';
        $group2->institution    = 'mahara';
        $group2->grouptype      = 'standard';
        $group2->open           = 1;
        $group2->request        = 0;
        $group2->controlled     = 0;
        $group2->submitpages    = 0;
        $group2->public         = 0;
        $group2->usersautoadded = 0;
        $group2->members        = array($dbuser1->id => 'admin', $dbuser2->id => 'admin');

        //do not run the test if group1 or group2 already exists
        foreach (array($group1->shortname, $group2->shortname) as $shortname) {
            $existinggroup = get_record('group', 'shortname', $shortname, 'institution', 'mahara');
            if (!empty($existinggroup)) {
                group_delete($existinggroup->id);
            }
        }

        // setup test groups
        $groupid1 = group_create((array) $group1);
        $groupid2 = group_create((array) $group2);
        $this->created_groups[]= $groupid1;
        $this->created_groups[]= $groupid2;

        $dbgroup1 = get_record('group', 'shortname', $group1->shortname, 'institution', 'mahara');
        $dbgroup2 = get_record('group', 'shortname', $group2->shortname, 'institution', 'mahara');

        //update the test data
        $group1 = new stdClass();
        $group1->id             = $dbgroup1->id;
        $group1->name           = 'The test group 1 - changed';
        $group1->shortname      = 'testgroupshortname1 - changed';
        $group1->description    = 'a description for test group 1 - changed';
        $group1->institution    = 'mahara';
        $group1->grouptype      = 'standard';
        $group1->open           = 1;
        $group1->request        = 0;
        $group1->controlled     = 0;
        $group1->submitpages    = 0;
        $group1->public         = 0;
        $group1->usersautoadded = 0;
        $group1->members        = array(array('id' => $dbuser1->id, 'role' => 'admin'), array('id' => $dbuser2->id, 'role' => 'admin'));

        //a small group: group2
        $group2 = new stdClass();
        $group2->shortname      = 'testgroupshortname2';
        $group2->name           = 'The test group 2 - changed';
        $group2->description    = 'a description for test group 2 - changed';
        $group2->institution    = 'mahara';
        $group2->grouptype      = 'course';
        $group2->editroles      = 'notmember';
        $group2->public         = 0;
        $group2->usersautoadded = 0;
        $group2->members        = array(array('username' => $dbuser2->username, 'role' => 'admin'));
        $groups = array($group1, $group2);

        //update the users by web service
        $function = 'mahara_group_update_groups';
        $params = array('groups' => $groups);
        $client->call($function, $params);

        $dbgroup1 = get_record('group', 'id', $groupid1);
        $dbgroupmembers1 = get_records_array('group_member', 'group', $dbgroup1->id);

        $dbgroup2 = get_record('group', 'id', $groupid2);
        $dbgroupmembers2 = get_records_array('group_member', 'group', $dbgroup2->id);

        // temporary hack untl group changes are sorted XXX
        $dbgroup1->open = ($dbgroup1->jointype == 'open' ? 1 : 0);
        $dbgroup1->controlled = ($dbgroup1->jointype == 'controlled' ? 1 : 0);
        $dbgroup1->submitpages = (isset($dbgroup1->submitpages) ? $dbgroup1->submitpages : 0);
        $dbgroup2->open = ($dbgroup2->jointype == 'open' ? 1 : 0);
        $dbgroup2->controlled = ($dbgroup2->jointype == 'controlled' ? 1 : 0);
        $dbgroup2->submitpages = (isset($dbgroup2->submitpages) ? $dbgroup2->submitpages : 0);

        //compare DB group with the test data
        //retrieve groups from the DB and check values
        $this->assertEquals($dbgroup1->name, $group1->name);
        $this->assertEquals($dbgroup1->description, $group1->description);
        $this->assertEquals($dbgroup1->grouptype, $group1->grouptype);
        $this->assertEquals($dbgroup1->open, $group1->open);
        $this->assertEquals($dbgroup1->request, $group1->request);
        $this->assertEquals($dbgroup1->controlled, $group1->controlled);
        $this->assertEquals($dbgroup1->submitpages, $group1->submitpages);
        $this->assertEquals($dbgroup1->public, $group1->public);
        $this->assertEquals($dbgroup1->usersautoadded, $group1->usersautoadded);
        // current user added as admin
        $this->assertEquals(count($dbgroupmembers1), count($group1->members) + 1);

        $this->assertEquals($dbgroup2->name, $group2->name);
        $this->assertEquals($dbgroup2->description, $group2->description);
        $this->assertEquals($dbgroup2->grouptype, $group2->grouptype);
        $this->assertEquals($dbgroup2->editroles, $group2->editroles);
        $this->assertEquals($dbgroup2->open, 1);
        $this->assertEquals($dbgroup2->request, 0);
        $this->assertEquals($dbgroup2->controlled, 0);
        $this->assertEquals($dbgroup2->submitpages, 0);
        $this->assertEquals($dbgroup2->public, $group2->public);
        $this->assertEquals($dbgroup2->usersautoadded, $group2->usersautoadded);
        // current user added as admin
        $this->assertEquals(count($dbgroupmembers2), count($group2->members) + 1);
    }

    // update user test
    function mahara_group_update_group_members($client) {
        //Set test data
        $dbuser1 = $this->create_user1_for_update();
        $dbuser2 = $this->create_user2_for_update();

        //Test data
        //a full group: group1
        $group1 = new stdClass();
        $group1->name           = 'The test group 1 - create';
        $group1->shortname      = 'testgroupshortname1';
        $group1->description    = 'a description for test group 1';
        $group1->institution    = 'mahara';
        $group1->grouptype      = 'standard';
        $group1->open           = 1;
        $group1->request        = 0;
        $group1->controlled     = 0;
        $group1->submitpages    = 0;
        $group1->public         = 0;
        $group1->usersautoadded = 0;
        $group1->members        = array($dbuser1->id => 'admin', $dbuser2->id => 'admin');

        //a small group: group2
        $group2 = new stdClass();
        $group2->shortname      = 'testgroupshortname2';
        $group2->name           = 'The test group 2 - create';
        $group2->description    = 'a description for test group 2';
        $group2->institution    = 'mahara';
        $group2->grouptype      = 'standard';
        $group2->open           = 1;
        $group2->request        = 0;
        $group2->controlled     = 0;
        $group2->submitpages    = 0;
        $group2->public         = 0;
        $group2->usersautoadded = 0;
        $group2->members        = array($dbuser1->id => 'admin');

        //do not run the test if group1 or group2 already exists
        foreach (array($group1->shortname, $group2->shortname) as $shortname) {
            $existinggroup = get_record('group', 'shortname', $shortname, 'institution', 'mahara');
            if (!empty($existinggroup)) {
                group_delete($existinggroup->id);
            }
        }

        // setup test groups
        $groupid1 = group_create((array) $group1);
        $groupid2 = group_create((array) $group2);
        $this->created_groups[]= $groupid1;
        $this->created_groups[]= $groupid2;

        $dbgroup1 = get_record('group', 'shortname', $group1->shortname, 'institution', 'mahara');
        $dbgroup2 = get_record('group', 'shortname', $group2->shortname, 'institution', 'mahara');

        //update the test data
        $group1 = new stdClass();
        $group1->id             = $dbgroup1->id;
        $group1->shortname      = 'testgroupshortname1';
        $group1->institution    = 'mahara';
        $group1->members        = array(array('id' => $dbuser1->id, 'action' => 'remove'));

        //a small group: group2
        $group2 = new stdClass();
        $group2->shortname      = 'testgroupshortname2';
        $group2->institution    = 'mahara';
        $group2->members        = array(array('username' => $dbuser2->username, 'role' => 'admin', 'action' => 'add'));
        $groups = array($group1, $group2);

        //update the users by web service
        $function = 'mahara_group_update_group_members';
        $params = array('groups' => $groups);
        $client->call($function, $params);

        $dbgroup1 = get_record('group', 'id', $groupid1);
        $dbgroupmembers1 = get_records_array('group_member', 'group', $dbgroup1->id);

        $dbgroup2 = get_record('group', 'id', $groupid2);
        $dbgroupmembers2 = get_records_array('group_member', 'group', $dbgroup2->id);

        //compare DB group with the test data
        // current user added as admin
        $this->assertEquals(count($dbgroupmembers1), 1);
        // current user added as admin
        $this->assertEquals(count($dbgroupmembers2), 2);
    }
}
