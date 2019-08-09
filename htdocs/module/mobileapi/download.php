<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.


/**
 * A script to serve files from web service client
 *
 * @package    core_webservice
 * @copyright  2011 Dongsheng Cai <dongsheng@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * AJAX_SCRIPT - exception will be converted into JSON
 */
define('INTERNAL', 1);
define('JSON', 1);
define('NOSESSKEY', 1);
define('PUBLIC', 1);
require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once($CFG->docroot . '/webservice/lib.php');

// Allow CORS requests.
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Credentials: false');

/**
 * A "deconstructed" webserver class to handle only the parts of the
 * webservice validation that I need.
 *
 * TODO: Generalize this into a more generic form, so it can be
 * used for other webservices?
 */
class mobileapi_profileicon_webservice_server extends webservice_base_server {
    public function __construct($authmethod = null) {
        //authenticate the user
        parent::__construct(WEBSERVICE_AUTHMETHOD_PERMANENT_TOKEN);
        $this->token = param_alphanum('wstoken');
        $this->functionname = param_alphanumext('wsfunction');
    }
    public function can_user_download_via_webservice() {

        // Check that the token is valid.
        // (This will also determine which service the token is for.)
        $this->authenticate_user(EXTERNAL_TOKEN_USER);

        // Make sure they're specifically accessing the maharamobile service.
        $maharamobileserviceid = get_field('external_services', 'id', 'shortname', 'maharamobile', 'component', 'module/mobileapi');
        if (!($maharamobileserviceid && $this->restricted_serviceid === $maharamobileserviceid )) {
            throw new WebserviceAccessException(get_string('servicenotallowed', 'module.mobileapi'));
        }
        $this->load_function_info();

        // If it hasn't crashed by now, they're good!
        return true;
    }
    public function run(){}
    protected function parse_request(){}
    protected function send_response(){}
    protected function send_error($ex = null){
        echo json_encode(array('exception' => get_class($ex), 'errorcode' => (isset($ex->errorcode) ? $ex->errorcode : $ex->getCode()), 'message' => $ex->getMessage(), 'debuginfo' => (isset($ex->debuginfo) ? $ex->debuginfo : ''))) . "\n";
    }
}

$server = new mobileapi_profileicon_webservice_server();
$server->can_user_download_via_webservice();

switch(param_alphanumext('wsfunction')) {
    case 'module_mobileapi_get_user_profileicon':
        require_once($CFG->docroot . 'lib/file.php');
        safe_require('artefact', 'file');
        // The underlying functions expect maxsize, not maxdimension
        if (array_key_exists('maxdimension', $_REQUEST)) {
            $_REQUEST['maxsize'] = $_REQUEST['maxdimension'];
        }

        ArtefactTypeProfileIcon::download_thumbnail_for_user($USER->get('id'));
        exit();
        break;
    default:
        throw new WebserviceInvalidResponseException('This function has nothing to download.');
}
