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
define('ADMIN', 1);
define('MENUITEM', 'configsite/siteviews');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'siteviews');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('libroot') . 'view.php');
require_once('pieforms/pieform.php');

$title = get_string('siteviews', 'admin');
define('TITLE', $title);

list($searchform, $data, $pagination) = View::views_by_owner(null, 'mahara');

$createviewform = pieform(create_view_form(null, 'mahara'));

$smarty = smarty();
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('views', $data->data);
$smarty->assign('institution', 'mahara');
$smarty->assign('pagination', $pagination['html']);
$smarty->assign('searchform', $searchform);
$smarty->assign('createviewform', $createviewform);
$smarty->display('view/index.tpl');
