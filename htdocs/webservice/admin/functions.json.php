<?php
/**
 * Service functions to be delivered to a Select2 field via ajax
 *
 * Used in the webservice logs page to help find errors for a particular function easier
 *
 * @package    mahara
 * @subpackage webservices
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
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

/**
 * Find the active webservice functions via query
 *
 * @param string  $request The query from user input
 * @param integer $limit   The number of lines to return
 * @param integer $offset  The offset in database
 * @return array containing count and data for the rows found
 */
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