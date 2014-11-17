<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
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
 * @author     Piers Harding
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package web service
 * @copyright  Copyright (C) 2011 Catalyst IT Ltd (http://www.catalyst.net.nz)
 */

// must be run from the command line
if (isset($_SERVER['REMOTE_ADDR']) || isset($_SERVER['GATEWAY_INTERFACE'])){
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
