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

$result = array();

$groupcategories = get_records_array('group_category','','','displayorder');

$rows = array();
if ($groupcategories) {
    foreach ($groupcategories as $i) {
        $r = array();
        $r['id'] = $i->id;
        $r['name'] = $i->title;
        $rows[] = $r;
    }
}

$result['groupcategories'] = array_values($rows);
$result['error'] = false;
$result['message'] = false;

json_headers();
echo json_encode($result);
