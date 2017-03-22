{* this template powers the css that is stored in the database for the configurable theme *}

.header.navbar,
.modal-docked .modal-header,
.modal-header {
    background: {$data.background};
}

.dashboard-widget-container .circle-bg,
.dashboard-widget-container .logged-in .widget-detail {
    background-color: #666;
}

.navbar.header {
    border-color: {$data.background};
}

@media (max-width: 767px) {
    .top-nav.navbar-nav {
        /* 1px alpha channel black to darken by 25% */
        background: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH3wkPAhEFWvh1jAAAAA1JREFUCNdjYGBgcAAAAEUAQT9reqQAAAAASUVORK5CYII=') {$data.background};
    }
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
.navbar-default .navbar-toggle .icon-bar {
    background-color: {$data.backgroundfg};
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


.btn-primary {
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


.main-nav {
    background: {$data.navbg};
}
.main-nav li a,
.main-nav li a:link,
.main-nav li a:visited,
.main-nav li a:active,
.main-nav li a:hover,
.main-nav li a:focus,
.navbar-inverse .navbar-nav > li > a:focus,
.navbar-inverse .navbar-nav > li > a:hover,
.navbar-inverse .navbar-link,
.navbar-inverse .navbar-link:hover,
.navbar-inverse .navbar-link:focus {
    color: {$data.navfg};
}

.main-nav .dropdown-nav-home li a,
.main-nav .dropdown-nav-home li a:link {
    color: {$data.link};
}

.main-nav .dropdown-nav-home li a:active,
.main-nav .dropdown-nav-home li a:hover,
.main-nav .dropdown-nav-home li a:focus {
    color: #555 !important;
}

.main-nav .nav > li a:hover,
.main-nav .nav > li a:focus,
.main-nav .nav > li a:active {
    color: {$data.navfg};
    background-color: rgba(223,223,223,.5);
}

.navbar-default .navbar-text.navbar-link,
.navbar-default .navbar-text.navbar-link:hover,
.navbar-default .navbar-text.navbar-link:focus {
    color: {$data.navfg};
}

@media (max-width: 767px) {
    .navbar-inverse .navbar-nav > .active > a,
    .navbar-inverse .navbar-nav > .active > a:focus,
    .navbar-inverse .navbar-nav > .active > a:hover,
    .navbar-showchildren.collapsed .icon {
        color: {$data.navfg};
    }

    .main-nav .child-nav a {
        color: #333 !important;
    }

    .main-nav .nav > li > a {
        border-top-color: transparent;
        border-bottom-color: transparent;
    }
}

#sub-nav.navbar-default .navbar-nav > .active > a,
#sub-nav.navbar-default .navbar-nav > .active > a:link,
#sub-nav.navbar-default .navbar-nav > .active > a:visited {
    background: {$data.background};
    color: {$data.backgroundfg};
    border-radius: 3px;
}

#sub-nav.navbar-default .navbar-nav > .active > a:active,
#sub-nav.navbar-default .navbar-nav > .active > a:hover {
    background: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAABmJLR0QAAAAAAAD5Q7t/AAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH3wkPAhMnva5W6gAAAA1JREFUCNdj+P//vz0ACTsDPd3TBh4AAAAASUVORK5CYII=') {$data.background};
}
#sub-nav.navbar-default .navbar-nav > li > a:focus,
#sub-nav.navbar-default .navbar-nav > li > a:hover {
    background: #f9f9f9;
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

header.header .header-search-form .form-control {
    background: url('data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAABmJLR0QA/wD/AP+gvaeTAAAACXBIWXMAAAsTAAALEwEAmpwYAAAAB3RJTUUH3wkPAic6ZYLJxAAAAA1JREFUCNdj+P//vzwACRsDHRWss5MAAAAASUVORK5CYII=') {$data.background};
    border: 0px none;
    color: {$data.backgroundfg};
}
header.header .header-search-form .form-control:focus {
    background-color: {$data.backgroundfg};
    color: {$data.background};
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
