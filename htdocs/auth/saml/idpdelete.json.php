<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('auth', 'saml');

$idp = param_variable('idp', null);
$data = new stdClass();
$data->error = false;
if (file_exists(AuthSaml::prepare_metadata_path($idp))) {
    // Double check that the idp is not being used
    if (get_field('auth_instance_config', 'instance', 'field', 'institutionidpentityid', 'value', $idp)) {
        $data->error = 'metadata in use - unable to delete';
    }
    else {
        // Ok to delete
        if (!unlink(AuthSaml::prepare_metadata_path($idp))) {
            $data->error = 'unable to delete metadata';
        }
        else {
            $data->success = true;
        }
    }
}
else {
    $data->error = 'unable to find metadata';
}

json_reply(false, array('data' => $data));
