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

// Set no caching for thumbnails...
// Never use Expires = 0 to prevent caching!
// See: http://docs.oracle.com/cd/E13158_01/alui/wci/docs103/devguide/tsk_pagelets_settingcaching_httpexpires.html
header("Expires: Tue, 10 Nov 2009 00:00:00 GMT");  // Date in the past - Mahara project registered at Launchpad.net
header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT");
header("Cache-Control: no-store, no-cache, must-revalidate"); // HTTP/1.1
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Font Notice
$view_text_font_notice = Skin::get_css_font_notice_from_font_name($skin->viewskin['view_text_font_family']);
$view_heading_font_notice = Skin::get_css_font_notice_from_font_name($skin->viewskin['view_heading_font_family']);

// BODY
$body_background_color = $skin->viewskin['body_background_color'];
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
$body_background_repeat = Skin::background_repeat_number_to_value($skin->viewskin['body_background_repeat']);
$body_background_attachment = $skin->viewskin['body_background_attachment'];
$body_background_position = Skin::background_position_number_to_value($skin->viewskin['body_background_position']);

// HEADER
$header_background_color = $skin->viewskin['header_background_color'];
$header_text_font_color = $skin->viewskin['header_text_font_color'];
$header_link_normal_color = $skin->viewskin['header_link_normal_color'];
$header_link_normal_underline = ($skin->viewskin['header_link_normal_underline'] == true ? 'underline' : 'none');
$header_link_hover_color = $skin->viewskin['header_link_hover_color'];
$header_link_hover_underline = ($skin->viewskin['header_link_hover_underline'] == true ? 'underline' : 'none');

// VIEW
$view_background_color = $skin->viewskin['view_background_color'];
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
$view_background_repeat = Skin::background_repeat_number_to_value($skin->viewskin['view_background_repeat']);
$view_background_attachment = $skin->viewskin['view_background_attachment'];
$view_background_position = Skin::background_position_number_to_value($skin->viewskin['view_background_position']);
$view_background_width = $skin->viewskin['view_background_width'].'%';

// TEXT
$view_text_font_face = Skin::get_css_font_face_from_font_name($skin->viewskin['view_text_font_family']);
$view_text_font_family = Skin::get_css_font_family_from_font_name($skin->viewskin['view_text_font_family']);
$tabs_height = Skin::get_tabs_height_from_font_name($skin->viewskin['view_text_font_family']);
$view_heading_font_face = Skin::get_css_font_face_from_font_name($skin->viewskin['view_heading_font_family']);
$view_heading_font_family = Skin::get_css_font_family_from_font_name($skin->viewskin['view_heading_font_family']);
$view_text_font_size = $skin->viewskin['view_text_font_size'];
$view_text_font_color = $skin->viewskin['view_text_font_color'];
$view_text_heading_color = $skin->viewskin['view_text_heading_color'];
$view_text_emphasized_color = $skin->viewskin['view_text_emphasized_color'];

// LINK
$view_link_normal_color = $skin->viewskin['view_link_normal_color'];
$view_link_normal_underline = ($skin->viewskin['view_link_normal_underline'] == true ? 'underline' : 'none');
$view_link_hover_color = $skin->viewskin['view_link_hover_color'];
$view_link_hover_underline = ($skin->viewskin['view_link_hover_underline'] == true ? 'underline' : 'none');

// TABLE
$view_table_border_color = $skin->viewskin['view_table_border_color'];
$view_table_header_color = $skin->viewskin['view_table_header_color'];
$view_table_header_text_color = $skin->viewskin['view_table_header_text_color'];
$view_table_odd_row_color = $skin->viewskin['view_table_odd_row_color'];
$view_table_even_row_color = $skin->viewskin['view_table_even_row_color'];

// BUTTON
$view_button_normal_color = $skin->viewskin['view_button_normal_color'];
$view_button_hover_color = $skin->viewskin['view_button_hover_color'];
$view_button_text_color = $skin->viewskin['view_button_text_color'];

// ADVANCED
$view_custom_css = $skin->viewskin['view_custom_css'];

//==========================//
// Start of skin stylesheet //
//==========================//
$stylesheet = <<< EOF
/* $view_text_font_notice */
$view_text_font_face
/* $view_heading_font_notice */
$view_heading_font_face
body,
body#micro {
    background: $body_background_color $body_background_image $body_background_repeat $body_background_attachment $body_background_position;
    font-family: $view_text_font_family;
    font-size: $view_text_font_size;
    color: $view_text_font_color;
}
table td {
    font-family: $view_text_font_family;
    color: $view_text_font_color;
}
table th {
    font-family: $view_text_font_family;
    color: $view_table_header_text_color;
    background-color: $view_table_header_color;
}
label {
    color: $view_text_font_color;
}
/** view width and margins **/
#container,
#containerX {
    width: $view_background_width;
    min-width: $view_background_width;
}
/** VIEW HEADER **/
#container #top-wrapper,
#containerX #top-wrapper,
#micro #footer-wrap {
    background-image: none;
    background-color: $header_background_color;
}
#view-description {
    border-bottom: 1px dotted $header_text_font_color;
}
.viewheadertop,
.viewheadertop .title {
    color: $header_text_font_color;
}
/** view header links **/
.viewheadertop a,
.viewheadertop a:link,
.viewheadertop a:visited {
    color: $header_link_normal_color;
    text-decoration: $header_link_normal_underline;
}
.viewheadertop a:hover,
.viewheadertop a:active {
    color: $header_link_hover_color;
    text-decoration: $header_link_hover_underline;
}
/** VIEW **/
/** view links **/
a,
a:link,
a:visited {
    color: $view_link_normal_color;
    text-decoration: $view_link_normal_underline;
}
.blockinstance-header h4 a,
.blockinstance-header h4 a:link,
.blockinstance-header h4 a:visited {
    color: $view_link_normal_color;
    text-decoration: none;
}
a:hover,
a:active {
    color: $view_link_hover_color;
    text-decoration: $view_link_hover_underline;
}
.blockinstance-header h4 a:hover,
.blockinstance-header h4 a:active {
    color: $view_link_hover_color;
    text-decoration: none;
}
/** view background **/
#containerX #column-container,
#container #column-container,
#containerX #main-column-container,
#container #main-column-container {
    background: $view_background_color $view_background_image $view_background_repeat $view_background_attachment $view_background_position;
}
/** VIEW BLOCKTYPE CATEGORIES **/
#category-list ul {
    background-image: none;
    font-size: small;
    height: $tabs_height;
}
#category-list li.current a,
#category-list li.current a:link,
#category-list li.current a:visited,
#category-list li.current a:hover,
#category-list li.current a:active {
    margin: 0 0.5em 0 0;
    background-image: none;
    background-color: $view_button_hover_color;
    color: $view_button_text_color;
    text-decoration: none;
    font-weight: bold;
    padding: 5px 10px;
}
#category-list li a,
#category-list li a:link,
#category-list li a:visited,
#category-list li a:active {
    margin: 0 0.5em 0 0;
    background-image: none;
    background-color: $view_button_normal_color;
    color: $view_button_text_color;
    text-decoration: $header_link_normal_underline;
    font-weight: normal;
    padding: 5px 10px;
}
#category-list li a:hover,
#category-list li a:active {
    color: $header_link_hover_color;
    text-decoration: $header_link_hover_underline;
}
#blocktype-list {
    background: $view_table_odd_row_color none repeat scroll 0 0;
    border: 1px solid $view_table_border_color;
}
/** VIEW PREVIEW **/
#page #bottom-pane {
    border: 5px solid $view_table_border_color;
}
#blocksinstruction {
    background: $view_table_border_color none repeat scroll 0 0;
    color: $header_text_font_color;
}
/** view heading **/
h1,
h2,
.blockinstance-header .title a,
.blockinstance-header .title a:link,
.blockinstance-header .title a:hover,
.blockinstance-header .title a:visited {
    color: $view_text_heading_color;
    font-family: $view_heading_font_family;
}
/** view sub-headings **/
h3,
h4,
.blockinstance-header .title {
    color: $view_text_emphasized_color;
    font-family: $view_heading_font_family;
}
.blockinstance-header h4 {
    border-bottom:2px solid $view_table_border_color;
}
/** emphasized text = header text in tables **/
.main-column thead th {
    border-bottom: 1px solid $view_table_border_color;
    color: $view_text_emphasized_color;
}
b, em,
.main-column tbody th {
    color: $view_text_emphasized_color;
}
/** odd rows in tables **/
.r0, .r0 td {
    background-color: $view_table_odd_row_color;
}
/** even rows in tables **/
.r1, .r1 td {
    background-color: $view_table_even_row_color;
}
#feedback_pagination {
    background-color: $view_table_odd_row_color;
}
/** buttons **/
a.btn,
input.submit,
input.cancel,
button,
input.button,
input.buttondk,
input.select,
input#files_filebrowser_edit_artefact {
    background: $view_button_normal_color none repeat scroll 0 0;
    border-color: $view_table_odd_row_color $view_button_normal_color $view_button_normal_color $view_table_odd_row_color;
    color: $view_button_text_color;
}
/** hover for buttons **/
a:hover.btn,
input.submit:hover,
input.cancel:hover,
button:hover,
input.button:hover,
input.buttondk:hover,
input.select:hover,
input#files_filebrowser_edit_artefact:hover {
    background: $view_button_hover_color;
    text-decoration: none;
}
/** depress for buttons **/
input.submit:active,
input.cancel:active,
button:active,
input.button:active,
input.buttondk:active,
input.select:active,
input#files_filebrowser_edit_artefact:active,
.rbuttons a.btn:active {
    border-color: $view_button_normal_color $view_table_odd_row_color $view_table_odd_row_color $view_button_normal_color;
    background: $view_button_hover_color;
    color: $view_button_text_color;
}
/** view footer **/
.viewfooter {
    border: 0 none;
}
/** advanced: custom css **/
$view_custom_css
EOF;
// End of skin stylesheet


header('Content-type: text/css');
echo $stylesheet;
exit;
