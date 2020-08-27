{* this template powers the css that is stored in the database for the configurable theme *}
.navbar-default {
    background-color: {$data.background};
    border-color: {$data.background};
}

.navbar-default .navbar-toggle:not(.collapsed) {
    background-color: transparent;
}
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
    color: {$data.navfg};
    background-color: {$data.navbg};
}

.navbar-main .navbar-nav > li.active > a {
    font-weight: bold;
    color: {$data.navfg};
    background-color: {$data.navbg};
}
.navbar-main .navbar-nav > li.active > a:focus,
.navbar-main .navbar-nav > li.active > a:hover {
    font-weight: bold;
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
    font-size: 13px;
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


.modal-docked .modal-header,
.modal-header {
    background: {$data.background};
    color: {$data.backgroundfg};
}

.dashboard-widget-container .circle-bg,
.dashboard-widget-container .logged-in .widget-detail {
    background-color: #666;
}

.dashboard-widget-container .widget-heading {
    border-top-color: {$data.headings};
}

.dashboard-widget-container .thumbnail-widget .widget-heading {
    border-top-color: {$data.headings};
}

.dashboard-widget-container .thumbnail-widget .widget-heading .circle-bg {
    background-color: {$data.headings};
}

.dashboard-widget-container .widget-detail {
    background-color: {$data.headings} !important;
}

.dashboard-widget-container .logged-in .widget-heading p,
a.card-footer:hover .icon.float-right,
.modal-docked .modal-header .close:hover .times,
.modal-docked .modal-header .close:focus .times {
    color: #767676;
}

@media (max-width: 767px) {
    .dashboard-widget-container .logged-in .widget-detail p,
    .dashboard-widget-container .logged-in:hover .widget-detail p {
        color: {$data.backgroundfg};
    }
}

.header.navbar,
.header.navbar-default .navbar-text,
.header.navbar-default .navbar-nav > li > a,
.dashboard-widget-container .logged-in .widget-detail,
.modal-docked .modal-header,
.modal-header,
.modal-docked .modal-header a,
.modal-docked .modal-header h4,
.close,
.modal-header .close:focus .times,
.modal-header .close:hover .times,
.search-toggle {
    color: {$data.backgroundfg};
}
.close {
    text-shadow: none;
}


h1,
h2,
h3,
h4,
.title a {
   color: {$data.headings};
}
.modal-header h1,
.modal-header h2,
.modal-header h3,
.modal-header h4,
.modal-header h5,
.modal-header h6 {
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


a,
a:visited,
a:link,
a:active,
a:hover,
a:focus,
.list-group-item-link a:hover,
.form-group.submitcancel .cancel,
.form-group.submitcancel .cancel:hover,
.list-group a.text-link,
.text-link,
.list-group-item-heading a:hover,
.list-group-item-heading a:hover .metadata,
.arrow-bar .nav-inpage.nav > li > a,
.arrow-bar .nav-inpage.nav > li > button,
.nav-inpage.nav > li.active > a:focus,
.nav-inpage.nav > li.active > a:hover,
.nav-inpage.nav > li.active > button:focus,
.nav-inpage.nav > li.active > button:hover,
.list-group a.text-success,
.outer-link+.list-group-item-heading,
.tags a,
.tags a.tag {
    color: {$data.link};
}

a:focus, a:hover {
  text-decoration-color: {$data.link};
}

.btn-secondary, a.btn-secondary {
  color: #333;
}

.btn-primary,
a.btn-primary {
    background-color: {$data.background};
    color: {$data.backgroundfg};
    border-color: {$data.backgroundfg};
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
    border-color: {$data.background};
}

a.admin-site {
    color: {$data.backgroundfg};
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
    background-color: {$data.background};
    color: {$data.backgroundfg};
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
    background-color: {$data.background};
    color: {$data.backgroundfg};
}

.card.collapsible.collapsible-small legend h4 a,
.pieform-fieldset.collapsible.collapsible-small legend h4 a {
    color: {$data.link};
}

.form-group.multisubmit .cancel,
.form-group.submitcancel .cancel {
    color: {$data.link};
}

.page-item.active .page-link {
    background-color: {$data.background};
    border-color: {$data.background};
}

.footer .footer-nav .nav-link {
    color: {$data.link};
}

.footer .metadata {
    color: {$data.link};
}

.dropdown-item.active,
.dropdown-item:active {
    background-color: {$data.background};
}

.dropdown-item.active a,
.dropdown-item:active a {
    color: #fff;
}
