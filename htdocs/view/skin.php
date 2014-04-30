<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Gregor Anzelj
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2010-2013 Gregor Anzelj <gregor.anzelj@gmail.com>
 *
 */

define('INTERNAL', 1);
require_once(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require_once('skin.php');
require_once('view.php');
require_once(get_config('libroot') . 'group.php');
define('TITLE', get_string('chooseviewskin', 'skin'));

$id = param_integer('id');
$new = param_boolean('new');
$view = new View($id);
$issiteview = $view->get('institution') == 'mahara';

if (!can_use_skins(null, false, $issiteview)) {
    throw new FeatureNotEnabledException();
}

$view->set_edit_nav();
$view->set_user_theme();
// Is page skin already saved/set for current page?
$skin = param_integer('skin', null);
$saved = false;
if (!isset($skin)) {
    $skin = $view->get('skin');
    $saved = true;
}
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

if (!$USER->can_edit_view($view) || $view->get('owner') == "0") {
    throw new AccessDeniedException();
}


$skinform = pieform(array(
    'name' => 'viewskin',
    'elements' => array(
        'skin'  => array(
            'type' => 'hidden',
            'value' => $currentskin->id,
        ),
        'view' => array(
            'type' => 'hidden',
            'value' => $view->get('id'),
        ),
        'new' => array(
            'type' => 'hidden',
            'value' => $new,
        ),
        'submit' => array(
            'type' => 'submit',
            'value' => get_string('save'),
        ),
    ),
));

// SEE: http://valums.com/scroll-menu-jquery/
$js = <<<EOF
jQuery(function($){
    // Get our elements for faster access and set overlay width
    var usrdiv = $('div.userskins'),
        usrul = $('ul.userskins'),
        favdiv = $('div.favorskins'),
        favul = $('ul.favorskins'),
        sitediv = $('div.siteskins'),
        siteul = $('ul.siteskins'),
        ulPadding = 10;

    // Get menu width
    var usrdivWidth = usrdiv.width();
    var favdivWidth = favdiv.width();
    var sitedivWidth = sitediv.width();

    // Remove scrollbars
    usrdiv.css({overflow: 'hidden'});
    favdiv.css({overflow: 'hidden'});
    sitediv.css({overflow: 'hidden'});

    // Find last image container
    var usrlastLi = usrul.find('li:last-child');
    var favlastLi = favul.find('li:last-child');
    var sitelastLi = siteul.find('li:last-child');

    // When user move mouse over menu
    usrdiv.mousemove(function(e){
        // As images are loaded ul width increases,
        // so we recalculate it each time
        var usrulWidth = usrlastLi[0].offsetLeft + usrlastLi.outerWidth() + ulPadding;
        var left = (e.pageX - usrdiv.offset().left) * (usrulWidth-usrdivWidth) / usrdivWidth;
        usrdiv.scrollLeft(left);
    });

    // When user move mouse over menu
    favdiv.mousemove(function(e){
        // As images are loaded ul width increases,
        // so we recalculate it each time
        var favulWidth = favlastLi[0].offsetLeft + favlastLi.outerWidth() + ulPadding;
        var left = (e.pageX - favdiv.offset().left) * (favulWidth-favdivWidth) / favdivWidth;
        favdiv.scrollLeft(left);
    });

    // When user move mouse over menu
    sitediv.mousemove(function(e){
        // As images are loaded ul width increases,
        // so we recalculate it each time
        var siteulWidth = sitelastLi[0].offsetLeft + sitelastLi.outerWidth() + ulPadding;
        var left = (e.pageX - sitediv.offset().left) * (siteulWidth-sitedivWidth) / sitedivWidth;
        sitediv.scrollLeft(left);
    });
});
EOF;

$css = array(
    '<link rel="stylesheet" type="text/css" href="' . get_config('wwwroot') . 'theme/raw/static/style/skin.css">',
);

$displaylink = $view->get_url();
if ($new) {
    $displaylink .= (strpos($displaylink, '?') === false ? '?' : '&') . 'new=1';
}

$smarty = smarty(array(), $css, array(), array('sidebars' => false));
$smarty->assign('INLINEJAVASCRIPT', $js);
$smarty->assign('saved', $saved);
$smarty->assign('incompatible', $incompatible);
$smarty->assign('currentskin', $currentskin->id);
$smarty->assign('currenttitle', $currentskin->title);
$smarty->assign('currentmetadata', (!empty($currentskin->metadata)) ? $currentskin->metadata : null);
$smarty->assign('userskins', $userskins);
$smarty->assign('favorskins', $favorskins);
$smarty->assign('siteskins', $siteskins);
$smarty->assign('form', $skinform);
$smarty->assign('viewid', $view->get('id'));
$smarty->assign('viewtype', $view->get('type'));
$smarty->assign('viewtitle', $view->get('title'));
$smarty->assign('edittitle', $view->can_edit_title());
$smarty->assign('displaylink', $displaylink);
$smarty->assign('new', $new);
if (get_config('viewmicroheaders')) {
    $smarty->assign('maharalogofilename', 'images/site-logo-small.png');
    $smarty->assign('microheaders', true);
    $smarty->assign('microheadertitle', $view->display_title(true, false, false));
}
$smarty->assign('issiteview', $issiteview);
$smarty->display('view/skin.tpl');

function viewskin_validate(Pieform $form, $values) {
    $skinid = $values['skin'];
    if ($skinid) {
        $skin = new Skin($values['skin']);
        if (!$skin->can_use()) {
            throw new AcessDeniedException();
        }
    }
}

function viewskin_submit(Pieform $form, $values) {
    global $SESSION;

    $view = new View($values['view']);
    $new = $values['new'];
    $view->set('skin', $values['skin']);
    $view->commit();
    handle_event('saveview', $view->get('id'));
    $SESSION->add_ok_msg(get_string('viewskinchanged', 'skin'));
    redirect('/view/view.php?id=' . $view->get('id') . ($new ? '&new=1' : ''));
}
