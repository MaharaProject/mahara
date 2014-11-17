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
