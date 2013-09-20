/* {$view_text_font_notice} */
{$view_text_font_face|safe}
/* {$view_heading_font_notice} */
{$view_heading_font_face|safe}
body,
body#micro {
    background: {$body_background_color} {$body_background_image|safe} {$body_background_repeat} {$body_background_attachment} {$body_background_position};
    font-family: {$view_text_font_family|safe};
    font-size: {$view_text_font_size};
    color: {$view_text_font_color};
}
table td {
    font-family: {$view_text_font_family|safe};
    color: {$view_text_font_color};
}
table th {
    font-family: {$view_text_font_family|safe};
    color: {$view_table_header_text_color};
    background-color: {$view_table_header_color};
}
label {
    color: {$view_text_font_color};
}
/** view width and margins **/
#container,
#containerX {
    width: {$view_background_width};
    min-width: {$view_background_width};
}
/** VIEW HEADER **/
#container #top-wrapper,
#containerX #top-wrapper,
#micro #footer-wrap {
    background-image: none;
    background-color: {$header_background_color};
}
#view-description {
    border-bottom: 1px dotted {$header_text_font_color};
}
.viewheadertop,
.viewheadertop .title {
    color: {$header_text_font_color};
}
/** view header links **/
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
/** VIEW **/
/** view links **/
a,
a:link,
a:visited {
    color: {$view_link_normal_color};
    text-decoration: {$view_link_normal_underline};
}
.blockinstance-header h4 a,
.blockinstance-header h4 a:link,
.blockinstance-header h4 a:visited {
    color: {$view_link_normal_color};
    text-decoration: none;
}
a:hover,
a:active {
    color: {$view_link_hover_color};
    text-decoration: {$view_link_hover_underline};
}
.blockinstance-header h4 a:hover,
.blockinstance-header h4 a:active {
    color: {$view_link_hover_color};
    text-decoration: none;
}
/** view background **/
#containerX #column-container,
#container #column-container,
#containerX #main-column-container,
#container #main-column-container,
div#mainmiddlewrap {
    background: {$view_background_color} {$view_background_image|safe} {$view_background_repeat} {$view_background_attachment} {$view_background_position};
}
/** VIEW BLOCKTYPE CATEGORIES **/
#category-list ul {
    background-image: none;
    font-size: small;
    height: {$tabs_height};
}
#category-list li.current a,
#category-list li.current a:link,
#category-list li.current a:visited,
#category-list li.current a:hover,
#category-list li.current a:active {
    margin: 0 0.5em 0 0;
    background-image: none;
    background-color: {$view_button_hover_color};
    color: {$view_button_text_color};
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
    background-color: {$view_button_normal_color};
    color: {$view_button_text_color};
    text-decoration: {$header_link_normal_underline};
    font-weight: normal;
    padding: 5px 10px;
}
#category-list li a:hover,
#category-list li a:active {
    color: {$header_link_hover_color};
    text-decoration: {$header_link_hover_underline};
}
#blocktype-list {
    background: {$view_table_odd_row_color} none repeat scroll 0 0;
    border: 1px solid {$view_table_border_color};
}
/** VIEW PREVIEW **/
#page #bottom-pane {
    border: 5px solid {$view_table_border_color};
}
#blocksinstruction {
    background: {$view_table_border_color} none repeat scroll 0 0;
    color: {$header_text_font_color};
}
/** view heading **/
h1,
h2,
.blockinstance-header .title a,
.blockinstance-header .title a:link,
.blockinstance-header .title a:hover,
.blockinstance-header .title a:visited {
    color: {$view_text_heading_color};
    font-family: {$view_heading_font_family|safe};
}
/** view sub-headings **/
h3,
h4,
.blockinstance-header .title {
    color: {$view_text_emphasized_color};
    font-family: {$view_heading_font_family|safe};
}
.blockinstance-header h4 {
    border-bottom:2px solid {$view_table_border_color};
}
/** emphasized text = header text in tables **/
.main-column thead th {
    border-bottom: 1px solid {$view_table_border_color};
    color: {$view_text_emphasized_color};
}
b, em,
.main-column tbody th {
    color: {$view_text_emphasized_color};
}
/** odd rows in tables **/
.r0, .r0 td {
    background-color: {$view_table_odd_row_color};
}
/** even rows in tables **/
.r1, .r1 td {
    background-color: {$view_table_even_row_color};
}
#feedback_pagination {
    background-color: {$view_table_odd_row_color};
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
    background: {$view_button_normal_color} none repeat scroll 0 0;
    border-color: {$view_table_odd_row_color} {$view_button_normal_color} {$view_button_normal_color} {$view_table_odd_row_color};
    color: {$view_button_text_color};
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
    background: {$view_button_hover_color};
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
    border-color: {$view_button_normal_color} {$view_table_odd_row_color} {$view_table_odd_row_color} {$view_button_normal_color};
    background: {$view_button_hover_color};
    color: {$view_button_text_color};
}
/** view footer **/
.viewfooter {
    border: 0 none;
}
/** advanced: custom css **/
{$view_custom_css|safe}
