<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage skin
 * @author     Gregor Anzelj
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
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
    $body_background_image = 'none';
}
else {
    $body_background_image = 'url(\'' . get_config('wwwroot') . 'artefact/file/download.php?file=' . $skin->viewskin['body_background_image'];
    if ($viewid) {
        $body_background_image .= "&view={$viewid}";
    }
    $body_background_image .= '\')';
}
$smarty->assign('body_background_image', $body_background_image);
$smarty->assign('body_background_repeat', Skin::background_repeat_number_to_value($skin->viewskin['body_background_repeat']));
$smarty->assign('body_background_attachment', $skin->viewskin['body_background_attachment']);
$smarty->assign('body_background_position', Skin::background_position_number_to_value($skin->viewskin['body_background_position']));

// HEADER
$smarty->assign('header_background_color', $skin->viewskin['header_background_color']);
$smarty->assign('header_text_font_color', $skin->viewskin['header_text_font_color']);
$smarty->assign('header_link_normal_color', $skin->viewskin['header_link_normal_color']);
$smarty->assign('header_link_normal_underline', ($skin->viewskin['header_link_normal_underline'] == true ? 'underline' : 'none'));
$smarty->assign('header_link_hover_color', $skin->viewskin['header_link_hover_color']);
$smarty->assign('header_link_hover_underline', ($skin->viewskin['header_link_hover_underline'] == true ? 'underline' : 'none'));

// VIEW
$smarty->assign('view_background_color', $skin->viewskin['view_background_color']);
if (empty($skin->viewskin['view_background_image']) || $skin->viewskin['view_background_image'] == null) {
    $view_background_image = 'none';
}
else {
    $view_background_image = 'url(\'' . get_config('wwwroot') . 'artefact/file/download.php?file=' . $skin->viewskin['view_background_image'];
    if ($viewid) {
        $view_background_image .= "&view={$viewid}";
    }
    $view_background_image .= '\')';
}
$smarty->assign('view_background_image', $view_background_image);
$smarty->assign('view_background_repeat', Skin::background_repeat_number_to_value($skin->viewskin['view_background_repeat']));
$smarty->assign('view_background_attachment', $skin->viewskin['view_background_attachment']);
$smarty->assign('view_background_position', Skin::background_position_number_to_value($skin->viewskin['view_background_position']));
$smarty->assign('view_background_width', $skin->viewskin['view_background_width'].'%');

// TEXT
$smarty->assign('view_text_font_face', Skin::get_css_font_face_from_font_name($skin->viewskin['view_text_font_family']));
$smarty->assign('view_text_font_family', Skin::get_css_font_family_from_font_name($skin->viewskin['view_text_font_family']));
$smarty->assign('tabs_height', Skin::get_tabs_height_from_font_name($skin->viewskin['view_text_font_family']));
$smarty->assign('view_heading_font_face', Skin::get_css_font_face_from_font_name($skin->viewskin['view_heading_font_family']));
$smarty->assign('view_heading_font_family', Skin::get_css_font_family_from_font_name($skin->viewskin['view_heading_font_family']));
$smarty->assign('view_text_font_size', $skin->viewskin['view_text_font_size']);
$smarty->assign('view_text_font_color', $skin->viewskin['view_text_font_color']);
$smarty->assign('view_text_heading_color', $skin->viewskin['view_text_heading_color']);
$smarty->assign('view_text_emphasized_color', $skin->viewskin['view_text_emphasized_color']);

// LINK
$smarty->assign('view_link_normal_color', $skin->viewskin['view_link_normal_color']);
$smarty->assign('view_link_normal_underline', ($skin->viewskin['view_link_normal_underline'] == true ? 'underline' : 'none'));
$smarty->assign('view_link_hover_color', $skin->viewskin['view_link_hover_color']);
$smarty->assign('view_link_hover_underline', ($skin->viewskin['view_link_hover_underline'] == true ? 'underline' : 'none'));

// TABLE
$smarty->assign('view_table_border_color', $skin->viewskin['view_table_border_color']);
$smarty->assign('view_table_header_color', $skin->viewskin['view_table_header_color']);
$smarty->assign('view_table_header_text_color', $skin->viewskin['view_table_header_text_color']);
$smarty->assign('view_table_odd_row_color', $skin->viewskin['view_table_odd_row_color']);
$smarty->assign('view_table_even_row_color', $skin->viewskin['view_table_even_row_color']);

// BUTTON
$smarty->assign('view_button_normal_color', $skin->viewskin['view_button_normal_color']);
$smarty->assign('view_button_hover_color', $skin->viewskin['view_button_hover_color']);
$smarty->assign('view_button_text_color', $skin->viewskin['view_button_text_color']);

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
