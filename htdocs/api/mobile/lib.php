<?php
/**
 *
 * @package    mahara
 * @subpackage mobile
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

function mobile_api_json_reply( $arr ) {
    header('Content-type: application/json');
    header('Pragma: no-cache');

    echo json_encode($arr);
    perf_to_log();
    exit;
}
