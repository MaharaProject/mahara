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
$usersperpage = 10;

$more = true;
$tmpuser = array();

while ($more && count($tmpuser) < $usersperpage) {
    $users = search_user($request, $usersperpage, $usersperpage * ($page - 1));
    $more = $users['count'] > $usersperpage * $page;

    if (!$users['data']) {
        $users['data'] = array();
    }

    foreach ($users['data'] as $user) {
        if (count($tmpuser) >= $usersperpage) {
            $more = true;
            continue;
        }

        $tmpuser[] = (object) array('id' => $user['id'],
            'text' => display_name($user['id']));
    }
    $page++;
}

echo json_encode(array(
    'more' => $more,
    'results' => $tmpuser,
));