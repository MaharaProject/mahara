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
require_once('searchlib.php');

$tag    = param_variable('tag', null);
$limit  = param_integer('limit', 10);
$offset = param_integer('offset', 0);
$sort   = param_alpha('sort', 'name');
$type   = param_alpha('type', null);
$owner  = (object) array('type' => 'user', 'id' => $USER->get('id'));

$data = get_portfolio_items_by_tag($tag, $owner, $limit, $offset, $sort, $type);
build_portfolio_search_html($data);
$data->tagdisplay = is_null($tag) ? get_string('alltags') : hsc(str_shorten_text($tag, 50));
$data->tagurl = urlencode($tag);

json_reply(false, array('data' => $data));
