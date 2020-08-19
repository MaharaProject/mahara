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

    'Moodle Assignment Submission' => array(
        'shortname' => 'maharaltimoodleassign',
        'functions' => [
            'mahara_user_get_extended_context',
            'mahara_submission_get_views_for_user',
            'mahara_submission_submit_view_for_assessment',
            'mahara_submission_release_submitted_view',
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
        'hasconfig' => 1,
    ),
);
