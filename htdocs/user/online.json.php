<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Stacey Walker
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);
require(dirname(dirname(__FILE__)) . '/init.php');

$offset = param_integer('offset', 0);
$limit  = param_integer('limit', 10);
$setlimit = param_boolean('setlimit', false);
$data = get_onlineusers($limit, $offset);
build_onlinelist_html($data, 'online');


json_reply(false, array(
    'message' => null,
    'data' => array(
        'tablerows' => $data['tablerows'],
        'pagination' => $data['pagination'],
        'pagination_js' => $data['pagination_js'],
        'count' => $data['count'],
        'results' => $data['count'] . ' ' . ($data['count'] == 1 ? get_string('result') : get_string('results')),
        'offset' => $offset,
        'setlimit' => $setlimit,
    )
));