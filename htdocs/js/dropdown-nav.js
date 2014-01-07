/**
 * Keyboard accessibility for dropdown navigation
 * @source: http://gitorious.org/mahara/mahara
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

// Sets up keyboard controls for dropdown navigation
function setup_navigation() {
    var menu = $j('.main-nav > ul');

    var menuhoverclass = 'show-menu';
    var menulinks = menu.find('> li > span > a');

    // Add aria attributes to menus
    $j(menulinks).each(function() {
        var submenus = $j(this).parent().next('ul');

        if (submenus.length > 0) {
            submenus.attr({ 'aria-hidden': 'true' });
        }
    });

    $j(menulinks).focus(function(){
        $j(this).closest('ul')
            .find('.'+menuhoverclass).attr('aria-hidden', 'true').removeClass(menuhoverclass);
        $j(this).parent().next('ul').attr('aria-hidden', 'false').addClass(menuhoverclass);
    });

    // Bind navigation keys
    var navigationkeys = {
        LEFT: 37, UP: 38, RIGHT: 39, DOWN: 40, SPACE: 32, ESC: 27, TAB: 9
    }

    $j(menulinks).keydown(function(e) {
        if (e.keyCode == navigationkeys.LEFT) {
            // Previous menu
            if ($j(this).closest('li').prev('li').length > 0) {
                e.preventDefault();
                $j(this).closest('li').prev('li').find('a').first().focus();
            }
        }
        else if (e.keyCode == navigationkeys.RIGHT) {
            // Next menu
            if ($j(this).closest('li').next('li').length > 0) {
                e.preventDefault();
                $j(this).closest('li').next('li').find('a').first().focus();
            }
        }
        else if (e.keyCode == navigationkeys.UP) {
            // Select last item in previous menu
            var prevmenu = $j(this).closest('li').prev('li').find('ul');
            if (prevmenu.length > 0) {
                e.preventDefault();
                $j(this).closest('li').find('.'+menuhoverclass)
                    .attr('aria-hidden', 'true').removeClass(menuhoverclass);
                prevmenu.attr('aria-hidden', 'false').addClass(menuhoverclass)
                    .find('a').last().focus();
            }
        }
        else if (e.keyCode == navigationkeys.DOWN) {
            // Open menu, select first item
            if ($j(this).closest('li').find('ul').length > 0) {
                e.preventDefault();
                $j(this).closest('li').find('ul')
                    .attr('aria-hidden', 'false').addClass(menuhoverclass)
                    .find('a').first().focus();
            }
        }
        else if (e.keyCode == navigationkeys.SPACE) {
            // If submenu is hidden, open it
            e.preventDefault();
            $j(this).closest('li').find('ul[aria-hidden=true]')
                .attr('aria-hidden', 'false').addClass(menuhoverclass)
                .find('a').first().focus();
        }
        else if (e.keyCode == navigationkeys.ESC) {
            // Close all submenus
            e.preventDefault();
            $j('.'+menuhoverclass)
                .attr('aria-hidden', 'true').removeClass(menuhoverclass);
        }
    });

    var links = $j(menulinks).closest('li').find('ul').find('a');
    $j(links).keydown(function(e) {
        if (e.keyCode == navigationkeys.UP) {
            // Focus previous submenu item or parent menu item
            e.preventDefault();
            if ($j(this).closest('li').prev('li').length == 0) {
                $j(this).parents('ul').parents('li').find('a').first().focus();
            }
            else {
                $j(this).closest('li').prev('li').find('a').first().focus();
            }
        }
        else if (e.keyCode == navigationkeys.DOWN) {
            // Focus next submenu item or next main menu item
            if ($j(this).closest('li').next('li').length == 0) {
                var nextmenuitem = $j(this).closest('ul').parent('li').next('li');
                if (nextmenuitem.length > 0) {
                    e.preventDefault();
                    nextmenuitem.find('a').first().focus();
                }
            }
            else {
                e.preventDefault();
                $j(this).closest('li').next('li').find('a').first().focus();
            }
        }
        else if (e.keyCode == navigationkeys.LEFT) {
            // Focus previous main menu item
            e.preventDefault();
            var prevmenuitem = $j(this).closest('ul').parent('li').prev('li');
            if (prevmenuitem.length > 0) {
                prevmenuitem.find('a').first().focus();
            }
        }
        else if (e.keyCode == navigationkeys.RIGHT) {
            // Focus next main menu item
            e.preventDefault();
            var nextmenuitem = $j(this).closest('ul').parent('li').next('li');
            if (nextmenuitem.length > 0) {
                nextmenuitem.find('a').first().focus();
            }
        }
        else if (e.keyCode == navigationkeys.ESC) {
            // Exit submenu
            e.preventDefault();
            $j(this)
                .closest('ul').first().prev('span').find('a').focus()
                .closest('ul').first().find('.'+menuhoverclass).attr('aria-hidden', 'true').removeClass(menuhoverclass);
        }
        else if (e.keyCode == navigationkeys.SPACE) {
            e.preventDefault();
            window.location = $j(this).attr('href');
        }
    });

    // Hide menu if click or focus occurs outside of navigation
    menu.find('a').last().keydown(function(e) {
        if (e.keyCode == navigationkeys.TAB) {
            $j('.'+menuhoverclass).attr('aria-hidden', 'true').removeClass(menuhoverclass);
        }
    });
    $j(document).click(function() {
        $j('.'+menuhoverclass).attr('aria-hidden', 'true').removeClass(menuhoverclass);
    });

    menu.click(function(e) {
        e.stopPropagation();
    });
}

jQuery(document).ready(function() {
    setup_navigation();
});
