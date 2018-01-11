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
define('MENUITEM', 'create/views');

define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'test');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');
define('TITLE', get_string('timeline', 'view'));

$offset = param_integer('offset', 0);

$view = param_integer('view');
if (!can_view_view($view)) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}
$viewobject = new View($view);
define('SUBSECTIONHEADING', $viewobject->display_title(true, false, false));

$versions = View::get_versions($view);

$smarty = smarty(array('paginator', 'js/jquery/jquery-ui/js/jquery-ui.min.js'));
// $smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('versions', $versions->data);
// $smarty->assign('pagination', $pagination['html']);
$html = $smarty->fetch('view/versionresults.tpl');

$smarty->assign('viewresults', $html);
$smarty->assign('views', $versions->count);
$smarty->assign('headingclass', 'page-header');
$smarty->display('view/versioning.tpl');
$smarty->display('view/versionresults.tpl');
