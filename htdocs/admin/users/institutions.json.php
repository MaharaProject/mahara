<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Ruslan Kabalin <ruslan.kabalin@luns.net.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2010 Lancaster University Network Services Limited
 *                      http://www.luns.net.uk
 *
 */

define('INTERNAL', 1);
define('INSTITUTIONALADMIN', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

$query  = param_variable('query', '');
$offset = param_integer('offset', 0);
$limit = param_integer('limit', 0);

// Get a list of institutions
require_once(get_config('libroot') . 'institution.php');
if (!$USER->get('admin')) { // Filter the list for institutional admins
    $filter      = $USER->get('admininstitutions');
    $showdefault = false;
}
else {
    $filter      = false;
    $showdefault = true;
}
$data = build_institutions_html($filter, $showdefault, $query, $limit, $offset, $count);
$data['count'] = $count;
$data['limit'] = $limit;
$data['offset'] = $offset;
$data['query'] = $query;

json_reply(false, array('data' => $data));
