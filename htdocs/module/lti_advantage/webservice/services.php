<?php

/**
 * Core external functions and service definitions.
 *
 * @package    mahara
 * @subpackage module-lti
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

$services = array(
    'LTI Advantage' => array(
        'shortname' => 'maharaltiadvantage',
        'functions' => [
            'module_lti_advantage_launch',
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
    'module_lti_advantage_launch' => array(
        'classname' => 'module_lti_advantage_launch',
        'methodname' => 'launch_advantage',
        'description' => "Launch an LTI advantage activity",
        'type' => 'write',
        'hasconfig' => 1,
    ),
);
