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


$functions = array(
        'mahara_institution_add_members' => array('institution' => 'mahara', 'users' => array(array('username' => 'veryimprobabletestusername1'), )),
        'mahara_institution_remove_members' => array('institution' => 'mahara', 'users' => array(array('username' => 'veryimprobabletestusername1'),)),
        'mahara_institution_invite_members' => array('institution' => 'mahara', 'users' => array(array('username' => 'veryimprobabletestusername1'),)),
        'mahara_institution_decline_members' => array('institution' => 'mahara', 'users' => array(array('username' => 'veryimprobabletestusername1'),)),
        'mahara_institution_get_members' => array('institution' => 'mahara'),
        'mahara_institution_get_requests' => array('institution' => 'mahara'),
);
