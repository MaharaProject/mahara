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

// must be run from the command line
if (isset($_SERVER['REMOTE_ADDR']) || isset($_SERVER['GATEWAY_INTERFACE'])) {
    die('Direct access to this script is forbidden.');
}

$create_group = array(
                        array(
                            'name' => 'The test group 1 - create',
                            'shortname' => 'testgroupshortname1',
                            'description' => 'a description for test group 1',
                            'institution' => 'mahara',
                            'grouptype' => 'course',
                            'open' => '1',
                            'members' => array(array('username' => 'admin', 'role' => 'admin')),
                        )
                );


$change_group = array(
                        array(
                            // this can be done by id instead of match on shortname+institution
                            // 'id' => 123,
                            'name' => 'The test group 1 - changed',
                            'shortname' => 'testgroupshortname1',
                            'description' => 'a description for test group 1 - changed',
                            'institution' => 'mahara',
                            'grouptype' => 'standard',
                            'request' => '1',
                            'members' => array(array('username' => 'admin', 'role' => 'admin')),
                        )
                );

// this can be done by id eg: array('id' => 123)
$get_group = array(array('shortname' => 'testgroupshortname1', 'institution' => 'mahara'));

// this can be done by id eg: array('id' => 123)
$delete_group = array(array('shortname' => 'testgroupshortname1', 'institution' => 'mahara'));

$update_members = array(
                  array('shortname' => 'testgroupshortname1',
                        'institution' => 'mahara',
                        'members' => array( array('username' => 'veryimprobabletestusername1', 'role' => 'member', 'action' => 'add'))));

$functions = array(
        'mahara_group_create_groups' => array('groups' => $create_group),
        'mahara_group_update_groups' => array('groups' => $change_group),
        'mahara_group_update_group_members' => array('groups' => $update_members),
        'mahara_group_get_groups' => array(),
        'mahara_group_get_groups_by_id' => array('groups' => $get_group),
        'mahara_group_delete_groups' => array('groups' => $delete_group),
);
