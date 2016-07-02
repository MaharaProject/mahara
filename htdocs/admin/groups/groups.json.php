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
define('ADMIN', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('group.php');
require_once('searchlib.php');

$query  = param_variable('query', '');
$offset = param_integer('offset', 0);
$limit = param_integer('limit', 10);
$institution = param_alphanum('institution', 'all');

$data = build_grouplist_html($query, $limit, $offset, $count, $institution);
$data['count'] = $count;
$data['offset'] = $offset;
$data['query'] = $query;

json_reply(false, array('data' => $data));
