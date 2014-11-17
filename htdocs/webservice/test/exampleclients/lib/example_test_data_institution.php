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


$functions = array(
        'mahara_institution_add_members' => array('institution' => 'mahara', 'users' => array(array('username' => 'veryimprobabletestusername1'), )),
        'mahara_institution_remove_members' => array('institution' => 'mahara', 'users' => array(array('username' => 'veryimprobabletestusername1'),)),
        'mahara_institution_invite_members' => array('institution' => 'mahara', 'users' => array(array('username' => 'veryimprobabletestusername1'),)),
        'mahara_institution_decline_members' => array('institution' => 'mahara', 'users' => array(array('username' => 'veryimprobabletestusername1'),)),
        'mahara_institution_get_members' => array('institution' => 'mahara'),
        'mahara_institution_get_requests' => array('institution' => 'mahara'),
);
