/**
 * Responsive design navigation
 * @source: http://gitorious.org/mahara/mahara
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

// MAIN NAV (dropdown nav option NOT selected)
// tests size of main nav against window size and adds class if window size is smaller
$j(document).ready(function(){
    $j('body').removeClass('no-js').addClass('js');
    function navClass(navTarget, wrapper) {
        // This is from theme/default/static/style/style.css (a media query)
        var navBuffer = 768;
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
    navClass($j('#main-nav-footer > ul > li'), $j('#footer'));
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
    // adds expand when click on menu title in responsive footer menu
    $j(".rd-nav-footer-title").click(function(event) {
        $j(".main-nav-footer").toggleClass("nav-footer-expand");
    });
});
