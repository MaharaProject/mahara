<?php

/**
 * Core external functions and service definitions.
 *
 * @package    mahara
 * @subpackage module-mobilapi
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

$services = array(
    'Mahara Mobile API' => array(
        'shortname' => 'maharamobile',
        'functions' => [
            'module_mobileapi_get_user_profileicon',
        ],
        'enabled' => 1,
        'restrictedusers' => 0,
        'tokenusers' => 1,

        // Increment this whenever you make a change to the profile or
        // behavior of this service and its exposed functions.
        'apiversion' => 1,
    ),
);

$functions = array(
    'module_mobileapi_get_user_profileicon' => array(
        'classname' => 'module_mobileapi_sync',
        'methodname' => 'get_user_profileicon',
        'description' => "Retrieve user's own profile icon",
        'type' => 'read',
    ),
);
