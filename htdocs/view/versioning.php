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
define('SECTION_PAGE', 'versioning');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');
define('TITLE', get_string('timeline', 'view'));

$offset = param_integer('offset', 0);

$view = param_integer('view');
if (!can_view_view($view)) {
    throw new AccessDeniedException(get_string('accessdenied', 'error'));
}

$fromdate = param_variable('fromdate', '-3 months');
$todate = param_variable('todate', 'tomorrow');

$viewobject = new View($view);
define('SUBSECTIONHEADING', $viewobject->display_title(true, false, false));

function timeline_submit(Pieform $form, $values) {
    redirect('/view/versioning.php?view='.$values['viewid'].'&fromdate='.$values['from'].'&todate='.$values['to']);
}

if ($fromdate || $todate ) {
    $versions = View::get_versions($view, $fromdate, $todate);
}
else {
    $versions = View::get_versions($view);
}

if ($versions->total == 0) {
    throw new AccessDeniedException(get_string('noversionsexist', 'view', $viewobject->get('title')));
}

$form = View::get_timeline_form($view, $fromdate, $todate);

$smarty = smarty(array('paginator', 'js/jquery/jquery-ui/js/jquery-ui.min.js', 'js/jTLine/js/jtline.js'), array(), array('view' => array(
        'versionnumber',
        'gotonextversion',
        'gotopreviousversion',
        'previousversion',
        'nextversion',
        'versionnumber',
    ),
  ), array('sidebars' => false));
$smarty->assign('versions', $versions->data);
$smarty->assign('timelineform', $form);
$smarty->assign('views', $versions->count);
$smarty->assign('fromdate', urlencode($fromdate));
$smarty->assign('todate', urlencode($todate));
$smarty->assign('viewurl', $viewobject->get_url());
$smarty->assign('view', $view);
$smarty->assign('viewtitle', hsc($viewobject->get('title')));
$smarty->assign('headingclass', 'page-header');
$smarty->display('view/versioning.tpl');
