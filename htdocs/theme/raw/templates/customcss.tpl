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
a.card-footer .icon:hover .icon.float-right,
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
.btn-draggable a.card-footer:hover .btn-group-vertical > .float-right.btn,
.list-group.ui-sortable a.card-footer:hover .float-right.ui-draggable-dragging,
a.card-footer:hover .btn-draggable .btn-group-vertical > .float-right.btn,
a.card-footer:hover .float-right.modal-loading, a.card-footer:hover .icon.float-right,
a.card-footer:hover .list-group.ui-sortable .float-right.ui-draggable-dragging {
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
tr[data-toggle="collapse"]:focus,
.dropdown .picker select.form-control:focus,
.dropdown .picker select:focus,
.pieform .picker select.form-control:focus,
.pieform .picker select:focus,
button:focus {
    outline-color: {$data.link};
}

.card-header a:not(.secondary-link).btn-group-item {
    color: #333;
}

.navbar-default {
    background-color: {$data.background};
    border-color: {$data.background};
}
.navbar-default .navbar-toggle:not(.collapsed) {
    background-color: transparent;
}
.loading-inner,
.navbar-toggle .icon {
    color: {$data.backgroundfg};
}
.navbar-default .navbar-toggle {
    border-color: transparent;
    background-color: transparent;
}
.navbar-default .navbar-collapse {
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
.navbar-toggle:hover .icon,
.navbar-toggle:focus .icon,
.navbar-toggle.collapsed:focus .icon,
.navbar-toggle.collapsed:hover .icon,
.navbar-default .navbar-toggle:focus .icon,
.navbar-default .navbar-toggle:hover .icon {
    color: #333;
}
@media (max-width: 767px) {
  .search-toggle.navbar-toggle .icon {
      color: {$data.backgroundfg};
  }
}

.navbar-main .navbar-nav > li > a {
    color: {$data.navfg};
    background-color: {$data.navbg};
    border-color: transparent;
}
.navbar-main .navbar-nav > li > a:hover,
.navbar-main .navbar-nav > li > a:focus {
    color: #333;
    background-color: #F1F1F1;
}

.navbar-main .navbar-nav > li.active > a {
    color: {$data.navfg};
    background-color: {$data.navbg};
}
.navbar-main .navbar-nav > li.active > a:focus,
.navbar-main .navbar-nav > li.active > a:hover {
    color: {$data.navfg};
    background-color: {$data.navbg};
}

.navbar-toggle.navbar-showchildren .icon {
    color: {$data.navfg};
}

.navbar-main .navbar-nav .navbar-showchildren,
.navbar-main .navbar-nav > .active .navbar-showchildren,
.navbar-toggle.navbar-showchildren:hover,
.navbar-toggle.navbar-showchildren:focus {
    background-color: transparent;
}

.navbar-toggle.navbar-showchildren:hover .icon,
.navbar-toggle.navbar-showchildren:focus .icon {
    color: {$data.navfg};
}

.navbar-main .child-nav > li > a {
    color: {$data.navfg};
    background-color: {$data.navbg};
}

.navbar-main .child-nav > li > a:hover,
.navbar-main .child-nav > li > a:focus {
    color: #333;
    background-color: #F1F1F1;
}

.navbar-main .child-nav .active > a {
    color: {$data.navfg};
    background-color: {$data.navbg};
}
.navbar-main .child-nav .active > a:hover,
.navbar-main .child-nav .active > a:focus {
    color: #333;
    background-color: #F1F1F1;
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

.nav-tabs > li.active > a,
.nav-tabs > li.active > a:focus,
.nav-tabs > li.active > a:hover,
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

.btn-secondary, a.btn-secondary {
  color: #333;
}

.btn-primary,
a.btn-primary {
    background-color: #575757;
    color: #FFF;
    border-color: #575757;
}
.btn-primary.active,
.btn-primary.focus,
.btn-primary:active,
.btn-primary:focus,
.btn-primary:hover,
.open > .btn-primary.dropdown-toggle {
    border-color: #CCCCCC;
    color: #333;
    background-color: #e0e0e0;
}
.btn-primary.btn:disabled,
.btn-primary.disabled,
.btn-primary[disabled] {
    /* 1px alpha channel white to lighten by 25% */
    background: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAABmJLR0QAAAAAAAD5Q7t/AAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH3wkPAhMnva5W6gAAAA1JREFUCNdj+P//vz0ACTsDPd3TBh4AAAAASUVORK5CYII=') {$data.background};
    border-color: #575757;
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
}
.pagination >.active > span:hover,
.pagination >.active > span:focus {
    background-color: #333;
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
    color: #333;
    background-color: #e0e0e0;
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

.dropdown-item.active,
.dropdown-item:active {
    background-color: #e0e0e0;
}

.dropdown-item.active a,
.dropdown-item:active a {
    color: #333;
}

.dropdown-menu > li:active > a {
    background-color: #e0e0e0;
    color: #333;
}

.dashboard-widget-container .thumbnail-widget .widget-heading .circle-bg {
    background-color: {$data.background};
}
.dashboard-widget-container .thumbnail-widget .widget-heading .circle-bg .icon {
    color: {$data.backgroundfg};
}
.dashboard-widget-container .thumbnail-widget:focus .widget-heading,
.dashboard-widget-container .thumbnail-widget:hover .widget-heading {
    background-color: #F1F1F1;
}

.block-header a {
    color: #FFFFFF;
}
