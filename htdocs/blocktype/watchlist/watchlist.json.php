<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype/groupviews
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * returns shared views in a given group id
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

safe_require('blocktype', 'watchlist');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'pieforms/pieform.php');

$blockid = param_integer('id');
$offset = param_integer('offset', 0);
$editing = param_integer('editing', false);

$instance = new BlockInstance($blockid);

if (!can_view_view($instance->get_view())) {
    json_reply(true, get_string('accessdenied', 'error'));
}

$views = PluginBlocktypeWatchlist::fetch_items($instance, $offset, $editing);

json_reply(false, array('data' => $views));
