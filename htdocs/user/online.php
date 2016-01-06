<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Stacey Walker
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
require(dirname(dirname(__FILE__)) . '/init.php');
define('TITLE', get_string('onlineusers'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'user');
define('SECTION_PAGE', 'onlineusers');

$offset = param_integer('offset', 0);
$limit  = param_integer('limit', 10);
$data = get_onlineusers($limit, $offset);
build_onlinelist_html($data, 'online');

$smarty = smarty(array('paginator'));
$smarty->assign('lastminutes', floor(get_config('accessidletimeout') / 60));
$smarty->assign('data', $data);
$smarty->display('user/online.tpl');
