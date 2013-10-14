/* {$view_text_font_notice} */
{$view_text_font_face|safe}
/* {$view_heading_font_notice} */
{$view_heading_font_face|safe}
body,
body#micro {
    background-color: {$body_background_color};
    background-image: {$body_background_image|safe};
    background-repeat: {$body_background_repeat};
    background-attachment: {$body_background_attachment};
    background-position: {$body_background_position};
    font-family: {$view_text_font_family|safe};
    font-size: {$view_text_font_size};
    color: {$view_text_font_color};
}
/* Layout */
#header,
.main-nav ul,
#sub-nav ul,
#mainmiddle,
#footer {
    min-width: 0;
    max-width: 100%;
}
#container {
    width: {$view_background_width};
    min-width: {$view_background_width};
}
/* General */
a,
a:link,
a:visited {
    color: {$view_link_normal_color};
    text-decoration: {$view_link_normal_underline};
}
a:hover,
a:active {
    color: {$view_link_hover_color};
    text-decoration: {$view_link_hover_underline};
}
h1,
h2,
h3,
h4,
h5,
h6,
.title {
    color: {$view_text_heading_color};
    font-family: {$view_heading_font_family|safe};
}
thead th {
    border-bottom: 1px solid {$view_table_border_color};
    color: {$view_text_emphasized_color};
}
fieldset {
    border: 1px solid {$view_table_border_color};
}
label,
th,
th label {
    color: {$view_text_emphasized_color};
}
/** odd rows in tables **/
.r0,
.r0 td,
.d0 {
    background-color: {$view_table_odd_row_color};
}
/** even rows in tables **/
.r1,
.r1 td,
.d1 {
    background-color: {$view_table_even_row_color};
}
table.attachments {
    background-color: {$view_table_even_row_color};
    border: 2px solid {$view_table_even_row_color};
}
/* Buttons */
input.submit,
input.cancel,
button,
.buttondk,
input.button,
input.select,
.btn, .btn:link, .btn:visited {
    background: {$view_button_normal_color};
    border-color: {$view_button_normal_color};
    color: {$view_button_text_color};
}
/** hover for buttons **/
input.submit:hover,
input.cancel:hover,
button:hover,
.buttondk:hover,
input.button:hover,
input.select:hover,
.btn:hover {
    background: {$view_button_hover_color};
    color: {$view_button_text_color};
}
/** depress for buttons **/
input.submit:active,
input.cancel:active,
button:active,
.buttondk:active,
input.button:active,
input.select:active,
.btn:active {
    border-color: {$view_button_hover_color};
    background: {$view_button_hover_color};
    color: {$view_button_text_color};
}
/* Header */
#top-wrapper,
#footer-wrap {
    background: {$header_background_color};
}
.viewheadertop,
.viewheadertop .title,
#micro .viewtitle,
#micro .collection-title {
    color: {$header_text_font_color};
}
.viewheadertop a,
.viewheadertop a:link,
.viewheadertop a:visited {
    color: {$header_link_normal_color};
    text-decoration: {$header_link_normal_underline};
}
.viewheadertop a:hover,
.viewheadertop a:active {
    color: {$header_link_hover_color};
    text-decoration: {$header_link_hover_underline};
}
#right-nav li a,
#right-nav li.identity a,
#footer a,
#footer a:link,
#footer a:visited,
#footer a:active {
    color: {$header_link_normal_color};
    text-decoration: {$header_link_normal_underline};
}
#right-nav li a:hover,
#footer a:hover {
    color: {$header_link_hover_color};
    text-decoration: {$header_link_hover_underline};
}
/* Collection navigation */
#collectionnavwrap {
    background: {$view_table_odd_row_color};
}
ul.colnav li a,
ul.colnav li a:link,
ul.colnav li a:visited,
ul.colnav li a:active {
    background-color: {$view_table_even_row_color};
    color: {$view_link_normal_color};
}
/* Middle content */
#column-container,
#mainmiddlewrap {
    background-color: {$view_background_color};
    background-image: {$view_background_image|safe};
    background-repeat: {$view_background_repeat};
    background-attachment: {$view_background_attachment};
    background-position: {$view_background_position};
}
/* Blocks */
.blockinstance-header .title {
    color: {$view_text_emphasized_color};
    font-family: {$view_heading_font_family|safe};
    border-bottom: 2px solid {$view_table_border_color};
}
.blockinstance-header .title a,
.blockinstance-header .title a:link,
.blockinstance-header .title a:visited,
.blockinstance-header .title a:active {
    color: {$view_text_emphasized_color};
    font-family: {$view_heading_font_family|safe};
    text-decoration: none;
}
.blockinstance-header .title a:hover {
    color: {$view_link_hover_color};
    text-decoration: none;
}
#feedbacktable .commentrightwrap,
#feedbacktable .private .commentrightwrap,
#commentfiles,
.submissionform {
    background: {$view_table_odd_row_color};
}
.morelinkwrap {
    background: none;
}
#feedback_pagination {
    background-color: {$view_table_odd_row_color};
}
/* View footer */
.viewfooter {
    border: 0 none;
}
/** advanced: custom css **/
{$view_custom_css|safe}
