body,
body#micro {
    background: {$data.background};
}

a, a:visited, a:link, a:active,
a:hover, a:focus {
    color: {$data.link};
}

h1,
h2,
h3,
h4,
h5,
h6 {
   color: {$data.headings};
}

.main-column table h3 a,
.main-column table h3 a:visited,
.main-column table h3 a:link,
.listing div h3 a,
.listing div h3 a:visited,
.listing div h3 a:link {
    color: {$data.headings} !important;
}

.r0,
.r0 td,
.d0,
.r1,
.r1 td,
.d1 {
    background-color: {$data.rowbg};
}

label,
legend {
    color: {$data.headings};
}

legend a,
legend a:link,
legend a:visited,
legend a:active,
legend a:hover {
    color: {$data.link} !important;
}


.icon,
.linkbtn {
    color: {$data.link};
}

#languageselect label {
    color: {$data.backgroundfg};
}

#main-nav {
    background: {$data.navbg};
}
#main-nav li,
#main-nav li a,
#main-nav li a:link,
#main-nav li a:visited,
#main-nav li a:active,
#main-nav li a:hover {
    color: {$data.navfg};
}
#main-nav li.selected a,
#main-nav li.selected a:link,
#main-nav li.selected a:visited,
#main-nav li.selected a:active {
    color: {$data.subfg};
    background: {$data.subbg};
}

#sub-nav {
    background-color: {$data.subbg};
}
#sub-nav li a,
#sub-nav li a:link,
#sub-nav li a:visited,
#sub-nav li a:active,
#sub-nav li a:hover {
    color: {$data.subfg};
}

div.sideblock {
    background-color: {$data.sidebarbg};
}
div.sideblock .sidebar-content {
    background: {$data.sidebarfg};
}
div.sideblock h3,
div.sideblock h3 a,
div.sideblock h3 a:link,
div.sideblock h3 a:visited,
div.sideblock h3 a:active,
div.sideblock h3 a:hover,
div.sideblock #lastminutes {
    color: {$data.sidebarfg};
}

div.sideblock .sidebar-content a,
div.sideblock .sidebar-content a:link,
div.sideblock .sidebar-content a:visited,
div.sideblock .sidebar-content a:active,
div.sideblock .sidebar-content a:hover,
#quota_used,
#quota_total {
    color: {$data.sidebarlink};
}

#footer-wrap,
#footernav,
#footernav a,
#footernav a:link,
#footernav a:active,
#footernav a:visited,
#footernav a:hover,
#performance-info,
#performance-info span,
#version {
    color: {$data.backgroundfg};
}

.viewheader .title,
.viewheader a {
    color: {$data.backgroundfg};
}

#main-nav .dropdown-sub li a,
#main-nav .dropdown-sub li a:link,
#main-nav .dropdown-sub li a:visited,
#main-nav .dropdown-sub li a:active {
    color: {$data.navfg} !important;
    background: {$data.navbg} !important;
}
#main-nav .dropdown-sub li a:hover{
    color: {$data.subfg} !important;
    background: {$data.subbg} !important;
}

ul.colnav li a {
    color: {$data.navfg} !important;
    background: {$data.navbg} !important;
}
