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

$create_user = array(
                        array(
                            'username' => 'veryimprobabletestusername1',
                            'password' => 'testpassword1',
                            'firstname' => 'testfirstname1',
                            'lastname' => 'testlastname1',
                            'email' => 'testemail1@hogwarts.school.nz',
                            'auth' => 'internal',
                            'institution' => 'mahara',
                            'studentid' => 'testidnumber1',
                            'preferredname' => 'Hello World!',
                            'city' => 'testcity1',
                            'country' => 'au',
                        )
                );


$change_user = array(
                        array(
                            'username' => 'veryimprobabletestusername1',
//                            'password' => 'testpassword1_updated',
                            'firstname' => 'testfirstname1_updated',
                            'lastname' => 'testlastname1_updated',
                            'email' => 'testemail1_updated@hogwarts.school.nz',
                            'studentid' => 'testidnumber1_updated',
                            'preferredname' => 'Hello World!_updated',
                            'city' => 'testcity1_updated',
                            'country' => 'au',
                        )
                );

$get_user = array(array('username' => 'veryimprobabletestusername1'));

$delete_user = array(array('username' => 'veryimprobabletestusername1'));

$update_favourites = array(array('username' => 'veryimprobabletestusername1',
                                                  'shortname' => 'testshortname1',
                                                  'institution' => 'mahara',
                                                  'favourites' => array( array('username' => 'admin'))));

$get_favourites = array(array('shortname' => 'testshortname1', 'username' => 'veryimprobabletestusername1'));

$functions = array(
        'mahara_user_create_users' => array('users' => $create_user),
        'mahara_user_update_users' => array('users' => $change_user),
        'mahara_user_delete_users' => array('users' => $delete_user),
        'mahara_user_update_favourites' => array('users' => $update_favourites),
        'mahara_user_get_favourites' => array('users' => $get_favourites),
        'mahara_user_get_users_by_id' => array('users' => $get_user),
        'mahara_user_get_users' => array(),
);
