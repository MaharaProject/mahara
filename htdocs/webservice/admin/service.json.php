<?php
/**
 * Service function information to be delivered to the token edit screen via ajax
 *
 * @package    mahara
 * @subpackage auth-webservice
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

$serviceid  = param_integer('service');
form_validate(param_variable('sesskey', null));

$show_auth_method_select = false;

$functions = get_records_array('external_services_functions', 'externalserviceid', $serviceid);
$function_list = array();
if ($functions) {
    foreach ($functions as $function) {
        $dbfunction = get_record('external_functions', 'name', $function->functionname);
        $function_list[] = '<a href="' . get_config('wwwroot') . 'webservice/wsdoc.php?id=' . $dbfunction->id . '">' . $function->functionname . '</a>';

        if ($function->functionname === 'mahara_upload_file') {
            $show_auth_method_select = true;
        }
    }
    $html = '<span class="pseudolabel">Functions</span>'; // Add the label to make easier find/replace everything in parent div
    $html .= implode(', ', $function_list);
    json_reply(false, (object) array(
        'servicelist' => $html,
        'show_auth_method_select' => $show_auth_method_select
    ));
}
else {
    json_reply(true, get_string('invalidservice', 'auth.webservice'));
}