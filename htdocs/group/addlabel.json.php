<?php
/**
 * Add personal labels to groups.
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
require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'group.php');

global $USER;

$request = param_variable('q');
$page = param_integer('page');
if ($page < 1) {
    $page = 1;
}
$resultsperpage = 10;

$more = true;
$tmpresults = array();

while ($more && count($tmpresults) < $resultsperpage) {
    $results = group_labels_for_group($request, null, $resultsperpage, $resultsperpage * ($page - 1));
    $more = $results['count'] > $resultsperpage * $page;

    if (!$results['data']) {
        $results['data'] = array();
    }

    foreach ($results['data'] as $result) {
        if (count($tmpresults) >= $resultsperpage) {
            $more = true;
            continue;
        }

        $tmpresults[] = (object) array(
            'id' => $result->label,
            'text' => hsc($result->label)
        );
    }
    $page++;
}

echo json_encode(array(
    'more' => $more,
    'results' => $tmpresults,
));
