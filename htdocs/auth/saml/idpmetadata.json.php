<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('auth', 'saml');

$idp = param_variable('idp', null);
$data = new stdClass();
if (file_exists(AuthSaml::prepare_metadata_path($idp))) {
    $rawxml = file_get_contents(AuthSaml::prepare_metadata_path($idp));
    $data->metarefresh_metadata_url = Metarefresh::get_metadata_url($idp, true);
    $data->metarefresh_metadata_signature = Metarefresh::get_metadata_fingerprint($idp, true);
    $data->metadata = $rawxml;
    $data->error = false;
}
else if ($list = PluginAuthSaml::get_disco_list()) {
    if (isset($list[$idp])) {
        $data->metarefresh_metadata_url = Metarefresh::get_metadata_url($idp, true);
        $data->metarefresh_metadata_signature = Metarefresh::get_metadata_fingerprint($idp, true);
    }
    else {
        $data->error = 'unable to find metadata';
    }
}
else {
    $data->error = 'unable to find metadata';
}

json_reply(false, array('data' => $data));
