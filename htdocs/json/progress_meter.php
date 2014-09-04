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

require(dirname(dirname(__FILE__)) . '/init.php');

json_headers();

$instance = param_alphanum('instance');
if (isset($_SESSION['progress_meters'][$instance])) {
    $data = $_SESSION['progress_meters'][$instance];

    if ($data['finished']) {
        $SESSION->set_progress($instance, FALSE);
    }
}
else {
    $data = array();
}

json_reply(false, array('data' => $data));
