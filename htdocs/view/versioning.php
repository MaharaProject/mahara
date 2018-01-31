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

$fromdate = param_variable('fromdate', NULL);
$todate = param_variable('todate', NULL);

$viewobject = new View($view);
define('SUBSECTIONHEADING', $viewobject->display_title(true, false, false));

function timeline_submit(Pieform $form, $values) {
    redirect('/view/versioning.php?view='.$values['viewid'].'&fromdate='.$values['from'].'&todate='.$values['to']);
}

if ($fromdate && $todate ) {
    $versions = View::get_versions($view, db_format_timestamp($fromdate), db_format_timestamp($todate));
}
else {
    $versions = View::get_versions($view);
}
$form = View::get_timeline_form($view);

$smarty = smarty(array('paginator', 'js/jquery/jquery-ui/js/jquery-ui.min.js', 'js/jTLine/js/jtline.js'), array(), array(), array('sidebars' => false));
// $smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('versions', $versions->data);
// $smarty->assign('pagination', $pagination['html']);
$html = $smarty->fetch('view/versionresults.tpl');
$smarty->assign('timelineform', $form);
$smarty->assign('viewresults', $html);
$smarty->assign('views', $versions->count);
$smarty->assign('fromdate', $fromdate);
$smarty->assign('todate', $todate);
$smarty->assign('view', $view);
$smarty->assign('headingclass', 'page-header');
$smarty->display('view/versioning.tpl');
