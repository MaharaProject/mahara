<?php
/**
 *
 * @package    mahara
 * @subpackage skin
 * @author     Gregor Anzelj
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2010-2013 Gregor Anzelj <gregor.anzelj@gmail.com>
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
define('NOCHECKPASSWORDCHANGE', 1);
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('skin.php');

if (!get_config('skins')) {
    throw new FeatureNotEnabledException();
}

$id = param_integer('skin', 0);
if ($id == 0) {
    echo '';
    exit;
}

$viewid = param_integer('view', null);

$skin = get_record('skin', 'id', $id);
$skinobj = new Skin($id);
if (!$skinobj->can_view()) {
    throw new AccessDeniedException();
}
$skin->viewskin = unserialize($skin->viewskin);
$smarty = smarty();

// Font Notice
$smarty->assign('view_text_font_notice', Skin::get_css_font_notice_from_font_name($skin->viewskin['view_text_font_family']));
$smarty->assign('view_heading_font_notice', Skin::get_css_font_notice_from_font_name($skin->viewskin['view_heading_font_family']));

// BODY
$smarty->assign('body_background_color', $skin->viewskin['body_background_color']);
if (empty($skin->viewskin['body_background_image']) || $skin->viewskin['body_background_image'] == null) {
    $body_background_image = '';
}
else {
    $body_background_image = 'url(\'' . get_config('wwwroot') . 'artefact/file/download.php?file=' . $skin->viewskin['body_background_image'];
    if ($viewid) {
        $body_background_image .= "&view={$viewid}";
    }
    $body_background_image .= '\')';
}
$smarty->assign('body_background_image', $body_background_image);
$smarty->assign('body_background_repeat', (!empty($body_background_image)) ? Skin::background_repeat_number_to_value($skin->viewskin['body_background_repeat']) : '');
$smarty->assign('body_background_attachment', (!empty($body_background_image)) ? $skin->viewskin['body_background_attachment'] : '');
$smarty->assign('body_background_position', (!empty($body_background_image)) ? Skin::background_position_number_to_value($skin->viewskin['body_background_position']) : '');

// HEADER
$smarty->assign('header_background_color', $skin->viewskin['header_background_color']);

if (empty($skin->viewskin['header_background_image'])) {
    $header_background_image = '';
}
else {
    $header_background_image = 'url(\'' . get_config('wwwroot') . 'artefact/file/download.php?file=' . $skin->viewskin['header_background_image'];
    if ($viewid) {
        $header_background_image .= "&view={$viewid}";
    }
    $header_background_image .= '\')';
}

$smarty->assign('header_background_image', $header_background_image);

// TEXT
$smarty->assign('view_text_font_face', Skin::get_css_font_face_from_font_name($skin->viewskin['view_text_font_family']));
$smarty->assign('view_text_font_family', Skin::get_css_font_family_from_font_name($skin->viewskin['view_text_font_family'], 'text'));
$smarty->assign('tabs_height', Skin::get_tabs_height_from_font_name($skin->viewskin['view_text_font_family']));  // TODO remove this
$smarty->assign('view_heading_font_face', Skin::get_css_font_face_from_font_name($skin->viewskin['view_heading_font_family']));
$smarty->assign('view_heading_font_family', Skin::get_css_font_family_from_font_name($skin->viewskin['view_heading_font_family'], 'heading'));
if (isset($skin->viewskin['view_block_header_font'])) {
    $smarty->assign('view_block_header_font', Skin::get_css_font_family_from_font_name($skin->viewskin['view_block_header_font']));
}
if (isset($skin->viewskin['view_block_header_font_color'])) {
    $smarty->assign('view_block_header_font_color', $skin->viewskin['view_block_header_font_color']);
}
$smarty->assign('view_text_font_size', $skin->viewskin['view_text_font_size']);
$smarty->assign('view_text_font_color', $skin->viewskin['view_text_font_color']);
$smarty->assign('view_text_heading_color', $skin->viewskin['view_text_heading_color']);

// LINK
$smarty->assign('view_link_normal_color', $skin->viewskin['view_link_normal_color']);
$smarty->assign('view_link_normal_underline', ($skin->viewskin['view_link_normal_underline'] == true ? 'underline' : 'none'));
$smarty->assign('view_link_hover_color', $skin->viewskin['view_link_hover_color']);
$smarty->assign('view_link_hover_underline', ($skin->viewskin['view_link_hover_underline'] == true ? 'underline' : 'none'));

// ADVANCED
$smarty->assign('view_custom_css', $skin->viewskin['view_custom_css']);


// Set no caching for thumbnails...
// Never use Expires = 0 to prevent caching!
// See: http://docs.oracle.com/cd/E13158_01/alui/wci/docs103/devguide/tsk_pagelets_settingcaching_httpexpires.html
header("Expires: Tue, 10 Nov 2009 00:00:00 GMT");  // Date in the past - Mahara project registered at Launchpad.net
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header('Content-type: text/css');
echo $smarty->fetch('skin/style.tpl');
exit;
