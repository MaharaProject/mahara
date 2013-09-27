<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-myfriends
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
safe_require('blocktype', 'myfriends');
require_once('user.php');

$offset = param_integer('offset');
$limit  = param_integer('limit', MAXFRIENDDISPLAY);
$bi = new BlockInstance(param_integer('block'));
if (!can_view_view($bi->get('view'))) {
    json_reply(true, get_string('accessdenied', 'error'));
}
$userid = $bi->get_view()->get('owner');

$friends = get_friends($userid, $limit, $offset);
PluginBlocktypeMyfriends::build_myfriends_html($friends, $userid, $bi);
unset($friends['data']);

json_reply(false, array('data' => $friends));
