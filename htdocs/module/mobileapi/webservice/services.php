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
            'module_mobileapi_sync',
            'module_mobileapi_get_blogs',
            'module_mobileapi_get_folders',
            'module_mobileapi_get_notifications',
            'module_mobileapi_get_tags',
            'module_mobileapi_get_user_profile',
            'module_mobileapi_get_user_profileicon',
            'module_mobileapi_upload_file',
            'module_mobileapi_upload_blog_post',
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
    'module_mobileapi_sync' => array(
        'classname' => 'module_mobileapi_sync',
        'methodname' => 'sync',
        'description' => "Retrieve user's own blogs, folders, notifications, tags, and profile information",
        'type' => 'read',
    ),
    'module_mobileapi_get_blogs' => array(
        'classname' => 'module_mobileapi_sync',
        'methodname' => 'get_blogs',
        'description' => "Retrieve user's own blogs",
        'type' => 'read',
    ),
    'module_mobileapi_get_folders' => array(
        'classname' => 'module_mobileapi_sync',
        'methodname' => 'get_folders',
        'description' => "Retrieve user's own folders",
        'type' => 'read',
    ),
    'module_mobileapi_get_notifications' => array(
        'classname' => 'module_mobileapi_sync',
        'methodname' => 'get_notifications',
        'description' => "Retrieve user's own notifications",
        'type' => 'read',
    ),
    'module_mobileapi_get_tags' => array(
        'classname' => 'module_mobileapi_sync',
        'methodname' => 'get_tags',
        'description' => "Retrieve user's own tags",
        'type' => 'read',
    ),
    'module_mobileapi_get_user_profile' => array(
        'classname' => 'module_mobileapi_sync',
        'methodname' => 'get_user_profile',
        'description' => "Retrieve user's own profile information",
        'type' => 'read',
    ),
    'module_mobileapi_get_user_profileicon' => array(
        'classname' => 'module_mobileapi_sync',
        'methodname' => 'get_user_profileicon',
        'description' => "Retrieve user's own profile icon",
        'type' => 'read',
    ),
    'module_mobileapi_upload_file' => array(
        'classname' => 'module_mobileapi_upload',
        'methodname' => 'upload_file',
        'description' => "Upload a file artefact to user's storage space",
        'type' => 'write',
    ),
    'module_mobileapi_upload_blog_post' => array(
        'classname' => 'module_mobileapi_upload',
        'methodname' => 'upload_blog_post',
        'description' => "Upload a blog post into one of user's blogs",
        'type' => 'write',
    ),
);
