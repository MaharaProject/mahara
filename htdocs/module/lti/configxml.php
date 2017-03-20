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
define('PUBLIC', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
header('Content-type: text/xml; charset=utf-8');

$smarty = smarty();
$smarty->assign('sitename', get_config('sitename'));
$smarty->assign('description', get_string('facebookdescription'));
$smarty->assign('launchurl', get_config('wwwroot').'webservice/rest/server.php');
$smarty->display('module:lti:xmlmetadata.tpl');
