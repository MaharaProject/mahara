<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-wall
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('wallpost', 'blocktype.wall'));
require_once(get_config('docroot') . 'blocktype/lib.php');

$wall = param_integer('instance');
$instance = new BlockInstance($wall);

safe_require('blocktype', 'wall');
PluginBlocktypeWall::wallpost_form($instance);
