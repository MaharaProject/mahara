/**
 * Responsive design navigation
 * @source: http://gitorious.org/mahara/mahara
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

// MAIN NAV (dropdown nav option NOT selected)
// tests size of main nav against window size and adds class if window size is smaller
function responsiveNav(navTarget, wrapper) {
    if (wrapper.length == 0) {
        return;
    }
    var navWidth = 0;
    navTarget.each(function() {
        navWidth += $j(this).outerWidth();
    });
    // Use the media query from theme/default/static/style/style.css
    var breakpoint = 768;
    $j(window).bind('load resize orientationchange', function() {
        // get window width
        var windowWidth = $j(window).width();
        var wrapperWidth = wrapper.width();
        // test if nav item combined width is greater than window width, add class if it is and vice versa
        if (windowWidth < breakpoint || wrapperWidth < navWidth) {
            wrapper.addClass('rd-navmenu');
        }
        else if (windowWidth >= breakpoint || wrapperWidth >= navWidth) {
            wrapper.removeClass('rd-navmenu');
        }
    });
}
$j(document).ready(function(){
    $j('body').removeClass('no-js').addClass('js');
    responsiveNav($j('#main-nav > ul > li'), $j('#top-wrapper'));
    responsiveNav($j('.tabswrap li'), $j('.tabswrap'));
    responsiveNav($j('#category-list li'), $j('#top-pane'));
    responsiveNav($j('#main-nav-footer > ul > li'), $j('#footer'));
    // adds expand when click on menu title in responsive menu
    $j(".rd-nav-title a").click(function(event) {
        $j(".main-nav").toggleClass("nav-expand");
        if ($j('.main-nav').hasClass('nav-expand')) {
            $j('.main-nav ul').find('a').first().focus();
        }
        return false;
    });
    // adds expand when click on arrow to expand tabs
    $j(".rd-tab-title a").click(function(event) {
        $j(".tabswrap").toggleClass("expand");
        if ($j('.tabswrap').hasClass('expand')) {
            $j('.tabswrap ul').find('a').first().focus();
        }
        return false;
    });
    // adds expand when click on menu title in responsive footer menu
    // Why does this exist?
    $j(".rd-nav-footer-title").click(function(event) {
        $j(".main-nav-footer").toggleClass("nav-footer-expand");
    });
});
