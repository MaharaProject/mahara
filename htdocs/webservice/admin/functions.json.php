<?php
/**
 *
 * @package    mahara
 * @subpackage webservices
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('docroot') . '/lib/searchlib.php');

global $USER;

$request = param_variable('q');
$page = param_integer('page');
if ($page < 1) {
    $page = 1;
}
$itemsperpage = 5;

$more = true;
$tmpitem = array();

while ($more && count($tmpitem) < $itemsperpage) {
    $items = search_functions($request, $itemsperpage, $itemsperpage * ($page - 1));
    $more = $items['count'] > $itemsperpage * $page;

    if (!$items['data']) {
        $items['data'] = array();
    }

    foreach ($items['data'] as $item) {
        if (count($tmpitem) >= $itemsperpage) {
            $more = true;
            continue;
        }

        $tmpitem[] = (object) array('id' => $item->id,
            'text' => $item->name);
    }
    $page++;
}

echo json_encode(array(
    'more' => $more,
    'results' => $tmpitem,
));

function search_functions($request, $limit, $offset) {
    $data = array('count' => 0, 'data' => false);
    $sql = "SELECT * FROM {external_functions} WHERE name LIKE ?";
    $values = array('%' . $request . '%');
    if ($results = get_records_sql_array($sql, $values, $offset, $limit)) {
        $data['count'] = sizeof(get_records_sql_array($sql, $values));
        $data['data'] = (array)$results;
    }
    return $data;
}