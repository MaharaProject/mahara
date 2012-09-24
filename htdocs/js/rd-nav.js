/**
 * Responsive design navigation
 * @source: http://gitorious.org/mahara/mahara
 *
 * @licstart
 * Copyright (C) 2012  Catalyst IT Ltd
 *
 * The JavaScript code in this page is free software: you can
 * redistribute it and/or modify it under the terms of the GNU
 * General Public License (GNU GPL) as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option)
 * any later version.  The code is distributed WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU GPL for more details.
 *
 * As additional permission under GNU GPL version 3 section 7, you
 * may distribute non-source (e.g., minimized or compacted) forms of
 * that code without the copy of the GNU GPL normally required by
 * section 4, provided you include this license notice and a URL
 * through which recipients can access the Corresponding Source.
 * @licend
 */

// MAIN NAV (dropdown nav option NOT selected)
// tests size of main nav against window size and adds class if window size is smaller
$j(document).ready(function(){
    $j('body').removeClass('no-js').addClass('js');
    function navClass(navTarget, wrapper) {
        // This is from theme/default/static/style/style.css (a media query)
        var navBuffer = 600;
        $j(window).bind('load resize orientationchange', function() {
            // get window width
            var windowWidth = $j(window).width();
            // test if nav item combined width is greater than window width, add class if it is and vice versa
            if (windowWidth <= navBuffer) {
                wrapper.addClass('rd-navmenu');
            }
            if (windowWidth >= navBuffer) {
                wrapper.removeClass('rd-navmenu');
            }
        });
    }
    navClass($j('#main-nav > ul > li'), $j('#top-wrapper'));
    navClass($j('.tabswrap li'), $j('.tabswrap'));
    navClass($j('#category-list li'), $j('#top-pane'));
    // adds expand when click on menu title in responsive menu
    $j(".rd-nav-title").click(function(event) {
        $j(".main-nav").toggleClass("nav-expand");
    });
    // adds expand when click on arrow to expand tabs
    $j(".rd-tab-title").click(function(event) {
        $j(".tabswrap").toggleClass("expand");
    });
    // adds expand when click on arrow to expand tabs
    $j(".rd-edittab").click(function(event) {
        $j("#category-list").toggleClass("edittab-expand");
    });
});
