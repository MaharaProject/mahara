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
require('init.php');
define('TITLE', get_string('privacystatement'));

$smarty = smarty();
$smarty->assign('page_content', get_site_page_content('privacy'));
$smarty->display('sitepage.tpl');
