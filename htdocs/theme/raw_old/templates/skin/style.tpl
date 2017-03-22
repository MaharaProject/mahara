/* {$view_text_font_notice} */
{$view_text_font_face|safe}
/* {$view_heading_font_notice} */
{$view_heading_font_face|safe}
body {
    background-color: {$body_background_color};
    background-image: {$body_background_image|safe};
    background-repeat: {$body_background_repeat};
    background-attachment: {$body_background_attachment};
    background-position: {$body_background_position};
}

/* we want some elements to have a solid background irrespective of user settings */
body > .main-content > .row {
    background-color: #FFFFFF;
}
@media (min-width: 768px) {
    body > .main-content > .row {
        border-top-left-radius: 3px;
        border-top-right-radius: 3px;
        border-bottom-right-radius: 3px;
        border-bottom-left-radius: 3px;
        margin-bottom: 20px; /* to show the user's custom body background, if set */
    }
}
@media (max-width: 767px) {
    .main-content {
        padding-top: 0 !important;
    }
}


/** all other custom settings should be scoped to be within .user-page-content **/
/* with the exception of the page title and page description */

/* page settings (also page description) */

.user-page-content,
.user-page-content .panel .panel-body table,
.user-page-content .panel-body ul,
#view-description {
    font-family: {$view_text_font_family|safe};
    color: {$view_text_font_color};
    {if $view_text_font_size != 'medium'}
        font-size: {$view_text_font_size};
    {/if}
}

.user-page-content pre {
    color: {$view_text_font_color};
    {if $view_text_font_size != 'medium'}
        font-size: {$view_text_font_size};
    {/if}
}


/* links and headings */

.user-page-content a,
.user-page-content a:link,
.user-page-content a:visited {
    color: {$view_link_normal_color};
    text-decoration: {$view_link_normal_underline};
}
.user-page-content a:hover,
.user-page-content a:active {
    color: {$view_link_hover_color};
    text-decoration: {$view_link_hover_underline};
}
.user-page-content h1,
.user-page-content h2,
.user-page-content h3,
.user-page-content .panel-body h3,
.user-page-content h4,
.user-page-content h5,
.user-page-content h6,
.user-page-content .list-group-item-heading,
#viewh1 {
    color: {$view_text_heading_color};
    font-family: {$view_heading_font_family|safe};
    font-weight: bold;
}


/* blocks */

.user-page-content .panel .title:not(.feedtitle) {
    font-weight: bold;
    color: {$view_text_emphasized_color};
    font-family: {$view_heading_font_family|safe};
    border-color: {$view_text_emphasized_color};
}
.user-page-content .panel .title a,
.user-page-content .panel .title a:link,
.user-page-content .panel .title a:visited,
.user-page-content .panel .title a:active {
    color: {$view_text_emphasized_color};
    text-decoration: none;
}
.user-page-content .panel .title a:hover {
    color: {$view_link_hover_color};
    text-decoration: none;
}
.user-page-content .link-blocktype:hover {
    background-color: transparent;
}
.user-page-content .panel {
    background-color: transparent; /* take away default white panel bg */
}


/* list groups */

.user-page-content .list-group-item {
    background-color: transparent;
}
.user-page-content .panel > .block .list-group .list-group-item {
    border-color: {$view_text_emphasized_color};
}


/* pagination */

.user-page-content .pagination > .active > a,
.user-page-content .pagination > .active > a:focus,
.user-page-content .pagination > .active > a:hover,
.user-page-content .pagination > .active > span,
.user-page-content .pagination > .active > span:focus,
.user-page-content .pagination > .active > span:hover {
    background-color: {$view_link_normal_color};
}


/** advanced: custom css **/

{$view_custom_css|safe}
