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
define('JSON', 1);
require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'skin.php');
safe_require('artefact', 'file');

$id = param_integer('id');

if (!can_view_view($id)) {
   json_reply(true, get_string('accessdenied', 'error'));
   exit;
}

$view = new View($id);

$skin = param_integer('skin', null);

if ($skin > 0 && ($skinobj = new Skin($skin)) && !$skinobj->can_use()) {
    json_reply(true, get_string('accessdenied', 'error'));
    exit;
}

$pieformname = param_alphanum('pieformname', null);

$issiteview = $view->get('institution') == 'mahara';

if (!can_use_skins(null, false, $issiteview)) {
    throw new FeatureNotEnabledException();
}

$saved = false;

if (!$skin || !($currentskin = get_record('skin', 'id', $skin))) {
    $currentskin = new stdClass();
    $currentskin->id = 0;
    $currentskin->title = get_string('skinnotselected', 'skin');
}
$incompatible = (isset($THEME->skins) && $THEME->skins === false && $currentskin->id != 0);
if ($incompatible) {
    $incompatible = ($view->get('theme')) ? 'notcompatiblewithpagetheme' : 'notcompatiblewiththeme';
    $incompatible = get_string($incompatible, 'skin', $THEME->displayname);
}
$metadata = array();
if (!empty($currentskin->id)) {
    $owner = new User();
    $owner->find_by_id($currentskin->owner);
    $currentskin->metadata = array(
        'displayname' => '<a href="' . get_config('wwwroot') . 'user/view.php?id=' . $currentskin->owner . '">' . display_name($owner) . '</a>',
        'description' => nl2br($currentskin->description),
        'ctime' => format_date(strtotime($currentskin->ctime)),
        'mtime' => format_date(strtotime($currentskin->mtime)),
     );
}

$userskins   = Skin::get_user_skins();
$favorskins  = Skin::get_favorite_skins();
$siteskins   = Skin::get_site_skins();
$defaultskin = Skin::get_default_skin();

$smarty = smarty_core();
$smarty->assign('saved', $saved);
$smarty->assign('incompatible', $incompatible);
$smarty->assign('currentskin', $currentskin->id);
$smarty->assign('currenttitle', $currentskin->title);
$smarty->assign('currentmetadata', (!empty($currentskin->metadata)) ? $currentskin->metadata : null);
$smarty->assign('userskins', $userskins);
$smarty->assign('favorskins', $favorskins);
$smarty->assign('siteskins', $siteskins);
$smarty->assign('defaultskin', $defaultskin);
$smarty->assign('viewid', $view->get('id'));
$smarty->assign('viewtype', $view->get('type'));
$smarty->assign('edittitle', $view->can_edit_title());
$smarty->assign('issiteview', $issiteview);
$html =  $smarty->fetch('view/skin.tpl');

json_reply(false, array(
    'message' => null,
    'html' => $html,
    'skin' => $skin,
    'pieformname' => $pieformname,
));
