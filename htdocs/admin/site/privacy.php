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
define('MENUITEM', 'configsite/privacy');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'privacy');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('privacy', 'admin'));

if (!is_logged_in()) {
    throw new AccessDeniedException();
}

$data = '<div class="no-results"> Site settings here </div>';

$smarty = smarty();
setpageicon($smarty, 'icon-umbrella');

$smarty->assign('data', $data);
$smarty->display('admin/site/privacy.tpl');
