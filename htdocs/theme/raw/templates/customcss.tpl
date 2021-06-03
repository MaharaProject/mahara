{* this template powers the css that is stored in the database for the configurable theme *}

a,
a:hover,
a.hover,
.collapsible legend a:not(.secondary-link),
.card-header a:not(.secondary-link),
.collapsible legend a:not(.secondary-link).collapsed,
.card-header a:not(.secondary-link).collapsed,
.skin .skin-footer,
.tags a,
.tags a.tag,
.jtline .events a,
.jtline .events a:hover,
.form-group.submitcancel .cancel,
.form-group.multisubmit .cancel,
.form-group.submitcancel .cancel:hover,
.form-group.multisubmit .cancel:hover,
.form-group.submitcancel .cancel:focus,
.form-group.multisubmit .cancel:focus,
.pieform-fieldset.collapsible.collapsible-small legend a,
.card.collapsible.collapsible-small legend a,
.card-control .content-expanded,
a.card-control:hover,
a.card-control:hover span,
a.card-footer,
a.card-footer .icon,
a.card-footer .icon:hover,
a.card-footer .icon:hover .icon.float-end,
.collapsible legend .secondary-link,
.card-header .secondary-link,
.card-header .btn-link:hover .icon,
.btn-link,
.btn-link:hover,
.btn-link.hover,
.text-link,
.outer-link + .list-group-item-heading,
.card-as-link.collapsible legend a,
.card-as-link.collapsible legend a.collapsed,
.table-hover > tbody > tr:hover .filename,
.table-hover > tbody > tr .file-download-link:hover,
.page-link,
.page-link:hover,
.nav-inpage.nav > li button,
.nav-inpage.nav > li > a,
.card.as-link.collapsible legend a,
.card.as-link.collapsible legend a.collapsed,
.pieform-fieldset.as-link.collapsible legend a,
.pieform-fieldset.as-link.collapsible legend a.collapsed,
.list-group a.text-link,
.text-link,
a.card-footer:not([href]):not([tabindex]),
a.card-footer:hover,
.btn-draggable a.card-footer:hover .btn-group-vertical > .float-end.btn,
.list-group.ui-sortable a.card-footer:hover .float-end.ui-draggable-dragging,
a.card-footer:hover .btn-draggable .btn-group-vertical > .float-end.btn,
a.card-footer:hover .float-end.modal-loading, a.card-footer:hover .icon.float-end,
a.card-footer:hover .list-group.ui-sortable .float-end.ui-draggable-dragging {
    color: {$data.link};
}
.mytags .tagfreq.badge {
    border-color: {$data.link};
}
.jtline .events a:hover {
    outline-color: {$data.link};
}
a.hover,
a:hover,
a.focus,
a:focus {
    text-decoration-color: {$data.link};
}
a.focus,
a:focus,
.btn.focus,
.btn:focus,
a[data-toggle="collapse"]:focus,
button[data-toggle="collapse"]:focus,
tr[data-toggle="collapse"]:focus,
button:focus,
.form-control:focus,
input:focus[type="password"],
input:focus[type="text"],
select:focus,
textarea:focus,
.form-switch .switch input:focus + .switch-label,
.select2-container:focus,
.select2-container:focus-within,
input[type="file"]:focus,
input[type="radio"]:focus,
.dropdown .picker select.form-control:focus,
.dropdown .picker select:focus,
.pieform .picker select.form-control:focus,
.pieform .picker select:focus,
.form-group.multisubmit .cancel:focus,
.form-group.submitcancel .cancel:focus {
    outline-color: {$data.link};
}

.form-control:focus,
input:focus[type="password"],
input:focus[type="text"],
select:focus,
textarea:focus {
    border-color: {$data.link};
}

.page-link:focus {
    box-shadow: 0 0 0 .25rem {$data.link};
}

.card-header a:not(.secondary-link).btn-group-item {
    color: #333;
}

.navbar-default {
    background-color: {$data.background};
    border-color: {$data.background};
}
.navbar-default .navbar-toggle {
    background-color: transparent;
    border-color: transparent;
}
.navbar-toggle:hover,
.navbar-toggle:focus,
.navbar-toggle.collapsed:focus,
.navbar-toggle.collapsed:hover,
.navbar-default .navbar-toggle:focus,
.navbar-default .navbar-toggle:hover {
    color: #333;
    background-color: #F1F1F1;
}
.navbar-toggle .icon,
.loading-inner {
    color: {$data.backgroundfg};
}
.navbar-toggle:hover .icon,
.navbar-toggle:focus .icon {
    color: #333;
}

.navbar-default .navbar-collapse {
    border-color: transparent;
}
@media (max-width: 767px) {
  .search-toggle.navbar-toggle .icon {
      color: {$data.backgroundfg};
  }
}

.navbar-main .navbar-nav > li > a,
.navbar-main .navbar-nav > li > button.menu-dropdown-toggle {
    color: {$data.navfg};
    background-color: {$data.navbg};
    border-color: transparent;
}
.navbar-main .navbar-nav > li > a .icon,
.navbar-main .navbar-nav > li > button.menu-dropdown-toggle .icon {
    color: {$data.navfg};
}
.navbar-main .navbar-nav > li > a .icon.navbar-showchildren,
.navbar-main .navbar-nav > li > button.menu-dropdown-toggle .icon.navbar-showchildren {
      color: {$data.navfg};
}
.navbar-main .navbar-nav > li > a:hover,
.navbar-main .navbar-nav > li > a:focus,
.navbar-main .navbar-nav > li > button.menu-dropdown-toggle:focus,
.navbar-main .navbar-nav > li > button.menu-dropdown-toggle:hover {
    color: #333;
    background-color: #F1F1F1;
}

.navbar-main .navbar-nav > li > a:hover .icon,
.navbar-main .navbar-nav > li > a:focus .icon,
.navbar-main .navbar-nav > li > button.menu-dropdown-toggle:hover .icon,
.navbar-main .navbar-nav > li > button.menu-dropdown-toggle:focus .icon {
    color: #333;
}
.navbar-main .navbar-nav > li > a:hover .icon.navbar-showchildren,
.navbar-main .navbar-nav > li > a:focus .icon.navbar-showchildren,
.navbar-main .navbar-nav > li > button.menu-dropdown-toggle:hover .icon.navbar-showchildren,
.navbar-main .navbar-nav > li > button.menu-dropdown-toggle:focus .icon.navbar-showchildren {
    color: #333;
}

.navbar-main .navbar-nav > .active > a,
.navbar-main .navbar-nav > .active > button.menu-dropdown-toggle {
    color: {$data.navfg};
    background-color: {$data.navbg};
}
.navbar-main .navbar-nav > .active > a .icon,
.navbar-main .navbar-nav > .active > button.menu-dropdown-toggle .icon {
    color: {$data.navfg};
}
.navbar-main .navbar-nav > .active > a:focus,
.navbar-main .navbar-nav > .active > a:hover,
.navbar-main .navbar-nav > .active > button.menu-dropdown-toggle:focus,
.navbar-main .navbar-nav > .active > button.menu-dropdown-toggle:hover {
    color: {$data.navfg};
    background-color: {$data.navbg};
}
.navbar-main .navbar-nav > .active > a:focus .icon,
.navbar-main .navbar-nav > .active > a:hover .icon,
.navbar-main .navbar-nav > .active > button.menu-dropdown-toggle:focus .icon,
.navbar-main .navbar-nav > .active > button.menu-dropdown-toggle:hover .icon {
    color: {$data.navfg};
}
.navbar-main .navbar-nav > .active > a:focus .icon.navbar-showchildren,
.navbar-main .navbar-nav > .active > a:hover .icon.navbar-showchildren,
.navbar-main .navbar-nav > .active > button.menu-dropdown-toggle:focus .icon.navbar-showchildren,
.navbar-main .navbar-nav > .active > button.menu-dropdown-toggle:hover .icon.navbar-showchildren {
    ccolor: #333;
}

.navbar-main .child-nav > li > a {
    color: #333;
    background-color: #F1F1F1;
}
.navbar-main .child-nav > li > a:hover,
.navbar-main .child-nav > li > a:focus {
    color: #333;
    background-color: #FFFFFF;
}
.navbar-main .child-nav .active > a {
    color: #333;
    background-color: #F1F1F1;
}
.navbar-main .child-nav .active > a:hover,
.navbar-main .child-nav .active > a:focus {
    color: #333;
    background-color: #FFFFFF;
}

.topright-menu .login-link a {
    color: {$data.navfg};
    background-color: {$data.navbg};
}
.topright-menu .login-link a:focus,
.topright-menu .login-link a:hover {
    color: #333;
    background-color: #F1F1F1;
}

.header.navbar,
.header.navbar-default .navbar-text,
.header.navbar-default .navbar-nav > li > a,
.search-toggle {
    color: {$data.backgroundfg};
}

.nav-tabs.nav li > a.active,
.nav-tabs.nav li > a.active:focus,
.nav-tabs.nav li > a.active:hover,
.nav-tabs.nav li > a:focus,
.nav-tabs.nav li > a:hover {
   color: {$data.headings};
   border-bottom-color: {$data.headings};
}

.btn-link-danger,
.btn-link-danger:link {
    color: #a94442;
}

.btn-link-danger:hover,
.btn-link-danger:focus {
  color: #983d3b;
}

.btn-primary,
.btn-primary:not(:disabled):not(.disabled),
.btn-primary.btn-sm {
    color: #FFF;
    background-color: #575757;
    border-color: #575757;
    box-shadow: none;
}
.btn-primary:hover,
.btn-primary:focus,
.btn-primary:active,
.btn-primary.btn-sm:hover,
.btn-primary.btn-sm:focus,
.btn-primary.btn-sm:active,
.btn-primary:not(:disabled):not(.disabled).active,
.btn-primary:not(:disabled):not(.disabled):active,
.show > .btn-primary.dropdown-toggle {
    color: #FFF;
    background-color: #333;
    border-color: #575757;
}

.btn:not(:disabled):not(.disabled).active:focus,
.btn:not(:disabled):not(.disabled):active:focus,
.btn-primary:not(:disabled):not(.disabled).active:focus,
.btn-primary:not(:disabled):not(.disabled):active:focus,
.show > .btn-primary.dropdown-toggle:focus {
    box-shadow: inset 0 3px 5px rgba(0,0,0,.125),0 0 0 .25rem rgba(111,111,111,.5);
}

.btn-secondary {
    color: #333;
    background-color: #F9F9F9;
    border-color: #d1d1d1;
    box-shadow: none;
}
.btn-secondary:hover,
.btn-secondary:focus,
.btn-secondary:active {
    color: #333;
    background-color: #e0e0e0;
    border-color: #d1d1d1;
}
.btn-secondary.disabled,
.btn-secondary.disabled:hover,
.btn-secondary.disabled:focus,
.btn-secondary.disabled:active {
    color: #ddd;
    border-color: #ddd;
    background: #f9f9f9;
}

.show>.btn-primary.dropdown-toggle {
    border-color: #CCCCCC;
    color: #333;
}

.show>.btn-secondary.dropdown-toggle {
    color: #333;
    background-color: #F9F9F9;
}

.arrow-bar {
    background-color: #F1F1F1;
}
.arrow-bar .arrow {
    background-color: #DBDBDB;
}
.arrow-bar .arrow:after {
    border-left-color: #DBDBDB;
}
.nav-inpage.nav > li.active > a,
.nav-inpage.nav > li.active > button {
    border: 1px solid #DBDBDB;
    background-color: #FFFFFF;
    color: #333;
}

.admin .arrow-bar {
    background-color: #f9f9f9;
}
.pagination > li > a,
.pagination> li > span {
    color: #767676;
}
.pagination > li > a:hover,
.pagination > li > a:focus,
.pagination> li > span:hover,
.pagination> li > span:focus {
    color: #333;
}
.pagination >.active > span {
    background-color: #767676;
    border-color: #767676;
    color: #FFF;
}
.pagination >.active > span:hover,
.pagination >.active > span:focus {
    background-color: #333;
    color: #FFF;
}

.custom-dropdown > ul > li > span {
  color: #333;
  background-color: #e0e0e0;
}

/* this is for the timeline */
.jtline .filling-line {
    background-color: {$data.background};
}

.no-touch .jtline .events a:hover::after,
.jtline .events a.selected::after {
    background-color: {$data.background};
    border-color: {$data.background};
}

.jtline .events a.older-event::after,
.no-touch .cd-timeline-navigation a:hover,
.no-touch .cd-timeline-navigation-second a:hover {
    border-color: {$data.background};
}

.cd-timeline-navigation a::after,
.cd-timeline-navigation-second a::after {
    color: {$data.background};
}

.progress-bar {
    color: #FFF;
    background-color: #333;
}

.page-item.active .page-link {
    color: #333;
    background-color: #e0e0e0;
    border-color: #e0e0e0;
}

.footer .footer-nav .nav-link,
.footer .footer-nav .nav-link:focus,
.footer .footer-nav .nav-link:hover {
    color: {$data.link};
}

.footer .metadata {
    color: {$data.link};
}

.dashboard-widget-container .thumbnail-widget .widget-heading .circle-bg {
    background-color: {$data.background};
}
.dashboard-widget-container .thumbnail-widget .widget-heading .circle-bg .icon {
    color: {$data.backgroundfg};
}
.dashboard-widget-container .thumbnail-widget.logged-in:focus .widget-heading,
.dashboard-widget-container .thumbnail-widget.logged-in:hover .widget-heading,
.dashboard-widget-container .thumbnail-widget:focus .widget-heading,
.dashboard-widget-container .thumbnail-widget:hover .widget-heading {
    background-color: #F1F1F1;
}

.block-header a,
.list-group .block-header.btn-group-top .btn-secondary,
.list-group .block-header.btn-group-top .btn-secondary:focus,
.list-group .block-header.btn-group-top .btn-secondary:hover {
    color: #FFFFFF;
    background-color: #333;
}
.block-header.active-block a,
.list-group .block-header.btn-group-top.active-block .btn-secondary,
.block-header.btn-group-top .btn-secondary:not(:disabled):not(.disabled):active {
    color: #333;
    background-color: #F1F1F1;
    outline: 2px solid {$data.link};
}

.bootstrap-datetimepicker-widget table td span:hover,
.bootstrap-datetimepicker-widget table thead tr:first-child th:hover,
.bootstrap-datetimepicker-widget table td.day:hover,
.bootstrap-datetimepicker-widget table td.hour:hover,
.bootstrap-datetimepicker-widget table td.minute:hover,
.bootstrap-datetimepicker-widget table td.second:hover {
    background: #F1F1F1;
}

.bootstrap-datetimepicker-widget table td span.active,
.bootstrap-datetimepicker-widget table td.active,
.bootstrap-datetimepicker-widget table td.active:hover {
    background-color: #575757;
    color: #FFF;
}
.bootstrap-datetimepicker-widget table td.today:before {
    border-bottom-color: #575757;
}

a[data-toggle="collapse"]:focus .collapse-indicator,
a[data-toggle="collapse"]:hover .collapse-indicator,
tr[data-toggle="collapse"]:focus .collapse-indicator,
tr[data-toggle="collapse"]:hover .collapse-indicator,
button[data-toggle="collapse"]:focus .collapse-indicator,
button[data-toggle="collapse"]:hover .collapse-indicator {
    color: #333;
}
tr[data-toggle="collapse"] .collapse-indicator,
button[data-toggle="collapse"] .collapse-indicator,
a[data-toggle="collapse"] .collapse-indicator {
    color: #575757;
}

.link-blocktype:focus,
.link-blocktype:hover {
    color: #333;
}

.btn-draggable .nav .navbar-showchildren:focus .btn-group-vertical > .btn,
.btn-draggable .nav .navbar-showchildren:hover .btn-group-vertical > .btn,
.list-group.ui-sortable .nav .navbar-showchildren:focus .ui-draggable-dragging,
.list-group.ui-sortable .nav .navbar-showchildren:hover .ui-draggable-dragging,
.nav .navbar-showchildren:focus .btn-draggable .btn-group-vertical > .btn,
.nav .navbar-showchildren:focus .icon,
.nav .navbar-showchildren:focus .list-group.ui-sortable .ui-draggable-dragging,
.nav .navbar-showchildren:hover .btn-draggable .btn-group-vertical > .btn,
.nav .navbar-showchildren:hover .icon,
.nav .navbar-showchildren:hover .list-group.ui-sortable .ui-draggable-dragging {
    color: #333;
}

table.dataTable thead .sorting,
table.dataTable thead .sorting_asc,
table.dataTable thead .sorting_desc,
table.dataTable thead .sorting_asc_disabled,
table.dataTable thead .sorting_desc_disabled,
table.dataTable thead .sorting:focus,
table.dataTable thead .sorting:hover,
table.dataTable thead .sorting_asc:focus,
table.dataTable thead .sorting_asc:hover,
table.dataTable thead .sorting_asc_disabled:focus,
table.dataTable thead .sorting_asc_disabled:hover,
table.dataTable thead .sorting_desc:focus,
table.dataTable thead .sorting_desc:hover,
table.dataTable thead .sorting_desc_disabled:focus,
table.dataTable thead .sorting_desc_disabled:hover {
    color: {$data.link};
}
