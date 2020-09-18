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

/* Heading background customizations */
.pageheader {
    background-color: {$header_background_color};
    background: {$header_background_image|safe} ;
    background-size: 100%;
}
h1#viewh1 {
    color:  {$view_text_heading_color};
}
.collection-title {
    color:  {$view_text_heading_color};
    border-bottom: 1px solid {$view_text_heading_color};
}

/* Used to style author, tag links in page headers */
.pageheader-content .text-small {
    color: {$view_text_heading_color};
}
.pageheader-content .text-small a {
    color: {$view_text_heading_color};
    text-decoration: underline;
}
.pageheader-content .text-small a:hover,
.pageheader-content .text-small a:active {
    color: {$view_text_heading_color};
    text-decoration: underline;
}

/* All other custom settings should be scoped to be within .user-page-content */
/* with the exception of the page title and page description */

/* page settings (also page description) */

.user-page-content,
.user-page-content .card .card-body table,
.user-page-content .card-body ul,
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
.user-page-content .card-body h3,
.user-page-content h4,
.user-page-content h5,
.user-page-content h6,
.user-page-content .list-group-item-heading,
h1#viewh1 {
    font-family: {$view_heading_font_family|safe};
}


/* blocks */

.user-page-content .block-header a {
    color: #FFFFFF;
}
.user-page-content .card .card-header:not(.feedtitle) {
    font-weight: bold;
    color: {$view_block_header_font_color};
    font-family: {$view_block_header_font|safe};
    border-color: {$view_block_header_font_color};
    background: none;
}
.user-page-content .card .card-header a:hover {
    color: {$view_link_hover_color};
    text-decoration: none;
}
.user-page-content .card .card-header .collapse-indicator,
.card .card-header::before {
    color: {$view_block_header_font_color};
}
.user-page-content .link-blocktype:hover {
    background-color: transparent;
}
.user-page-content .card {
    background-color: transparent; /* take away default white card bg */
}
.user-page-content h1,
.user-page-content h2,
.user-page-content h3,
.user-page-content h4,
.user-page-content h5,
.user-page-content h6,
.user-page-content .text-midtone,
.user-page-content .metadata,
.user-page-content .postedon {
    color: {$view_text_font_color};
}
a[data-toggle="collapse"] .collapse-indicator,
tr[data-toggle="collapse"] .collapse-indicator,
.card.collapsible:not(.card-secondary).has-attachment .card-header .collapse-indicator,
.card.collapsible:not(.card-secondary).has-attachment .card-header .metadata {
    color: {$view_link_normal_color};
}

/* list groups */

.user-page-content .list-group-item {
    background-color: transparent;
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


/* advanced: custom css */

{$view_custom_css|safe}
