<?php

/**
 * Core external functions and service definitions.
 *
 * @package    mahara
 * @subpackage module-lti
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

$services = array(
    'LTI integration' => array(
        'shortname' => 'maharalti',
        'functions' => [
            'module_lti_launch',
        ],
        'enabled' => 1,
        'restrictedusers' => 0,
        'tokenusers' => 0,

        // Increment this whenever you make a change to the profile or
        // behavior of this service and its exposed functions.
        'apiversion' => 1,
    ),
);

$functions = array(
    'module_lti_launch' => array(
        'classname' => 'module_lti_launch',
        'methodname' => 'launch',
        'description' => "Launch an LTI activity",
        'type' => 'write',
    ),
);
