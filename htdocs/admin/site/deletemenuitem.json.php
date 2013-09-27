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

json_headers();

$itemid = param_integer('itemid');

if (!delete_records('site_menu','id', $itemid)) {
    json_reply('local', get_string('deletefailed','admin'));
}

json_reply(false, get_string('menuitemdeleted','admin'));
