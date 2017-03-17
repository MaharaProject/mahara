{* this template powers the css that is stored in the database for the configurable theme *}
.navbar-default {
    background-color: {$data.background};
    border-color: {$data.background};
}

.navbar-toggle .icon {
    color: {$data.backgroundfg};
}
.navbar-default .navbar-toggle {
    border-color: transparent;
}
.navbar-default .navbar-collapse {
    border-color: transparent;
}
.navbar-toggle:hover,
.navbar-toggle:focus {
    background: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH3wkPAhEFWvh1jAAAAA1JREFUCNdjYGBgcAAAAEUAQT9reqQAAAAASUVORK5CYII=') {$data.background};
}

.navbar-main .navbar-nav > li > a {
    color: {$data.navfg};
    background-color: {$data.navbg};
    border-color: transparent;
}
.navbar-main .navbar-nav > li > a:hover,
.navbar-main .navbar-nav > li > a:focus {
    color: #333;
    background-color: #f9f9f9;
}

.navbar-main .navbar-nav > li.active > a {
    font-weight: bold;
}

.navbar-toggle.navbar-showchildren .icon {
    color: {$data.navfg};
}

.navbar-main .navbar-nav > .active .navbar-showchildren,
.navbar-toggle.navbar-showchildren:hover,
.navbar-toggle.navbar-showchildren:focus {
    background: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH3wkPAhEFWvh1jAAAAA1JREFUCNdjYGBgcAAAAEUAQT9reqQAAAAASUVORK5CYII=') {$data.background};
}

.navbar-toggle.navbar-showchildren:hover .icon,
.navbar-toggle.navbar-showchildren:focus .icon {
    color: {$data.navfg};
}

.navbar-main .child-nav > li > a {
    color: {$data.navbg};
    background-color: {$data.navfg};
}

.navbar-main .child-nav > li > a:hover,
.navbar-main .child-nav > li > a:focus {
    color: #333;
    background-color: #f9f9f9;
}

.modal-docked .modal-header,
.modal-header {
    background: {$data.background};
}

.dashboard-widget-container .circle-bg,
.dashboard-widget-container .logged-in .widget-detail {
    background-color: #666;
}

.dashboard-widget-container .widget-heading {
    border-top-color: {$data.headings};
}

.dashboard-widget-container .logged-in .widget-heading p,
a.panel-footer:hover .icon.pull-right,
.modal-docked .modal-header .close:hover .times,
.modal-docked .modal-header .close:focus .times {
    color: #666;
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


.btn-primary,
a.btn-primary {
    background: {$data.background};
    color: {$data.backgroundfg};
    border-color: #ccc;
}
.btn-primary.active,
.btn-primary.focus,
.btn-primary:active,
.btn-primary:focus,
.btn-primary:hover,
.open > .btn-primary.dropdown-toggle {
    border-color: #ccc;
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
    color: #666;
}
.pagination > li > a:hover,
.pagination > li > a:focus,
.pagination> li > span:hover,
.pagination> li > span:focus {
    color: #333;
}
.pagination >.active > span {
    background-color: #666;
    border-color: #666;
}
.pagination >.active > span:hover,
.pagination >.active > span:focus {
    background-color: #333;
}

.custom-dropdown > ul > li > span {
    background-color: #666;
}
