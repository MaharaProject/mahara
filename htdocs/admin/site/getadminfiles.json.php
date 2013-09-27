<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

$public = param_boolean('public');

safe_require('artefact', 'file');

$result = array();

$result['adminfiles'] = ArtefactTypeFile::get_admin_files($public);
if (empty($result['adminfiles'])) {
    $result['adminfiles'] = null;
}

$result['error'] = false;
$result['message'] = false;

json_headers();
echo json_encode($result);
