/**
 * Keyboard accessibility for dropdown navigation
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

jQuery(document).ready(function($) {
    var navigationkeys = {
        LEFT: 37, UP: 38, RIGHT: 39, DOWN: 40, SPACE: 32, ENTER: 13, ESC: 27, TAB: 9
    };

    var menuid = 'dropdown-nav';
    var menu = $('ul#' + menuid);

    var menuhoverclass = 'open';
    var menulinks = menu.find('> li > a');

    // Add aria attributes to menus
    menulinks.each(function() {
        $(this).siblings('ul.has-dropdown').attr({ 'aria-hidden': 'true' });
    });

    // Open menus on focus
    menulinks.focus(function() {
        $(this).closest('ul').find('.'+menuhoverclass)
            .attr('aria-hidden', 'true').removeClass(menuhoverclass);
        $(this).siblings('ul.has-dropdown')
            .attr('aria-hidden', 'false').addClass(menuhoverclass);
    });

    // Stop two dropdowns from opening at once when focusing one menu item and hovering another
    menulinks.mouseenter(close_open_menu);

    // Key bindings to navigate top-level menu items
    menulinks.keydown(function(e) {
        if (e.keyCode == navigationkeys.LEFT) {
            // Previous menu
            if ($(this).closest('li').prev('li').length > 0) {
                e.preventDefault();
                $(this).closest('li').prev('li').find('a').first().focus();
            }
        }
        else if (e.keyCode == navigationkeys.RIGHT) {
            // Next menu
            if ($(this).closest('li').next('li').length > 0) {
                e.preventDefault();
                $(this).closest('li').next('li').find('a').first().focus();
            }
        }
        else if (e.keyCode == navigationkeys.UP) {
            // Select last item in previous menu
            var prevmenu = $(this).closest('li').prev('li').find('ul');
            if (prevmenu.length > 0) {
                e.preventDefault();
                $(this).closest('li').find('.'+menuhoverclass)
                    .attr('aria-hidden', 'true').removeClass(menuhoverclass);
                prevmenu.attr('aria-hidden', 'false').addClass(menuhoverclass)
                    .find('a').last().focus();
            }
        }
        else if (e.keyCode == navigationkeys.DOWN) {
            // Open menu, select first item
            if ($(this).closest('li').find('ul').length > 0) {
                e.preventDefault();
                $(this).closest('li').find('ul')
                    .attr('aria-hidden', 'false').addClass(menuhoverclass)
                    .find('a').first().focus();
            }
        }
        else if (e.keyCode == navigationkeys.SPACE || e.keyCode == navigationkeys.ENTER) {
            // If submenu is hidden, open it
            e.preventDefault();
            $(this).closest('li').find('ul')
                .attr('aria-hidden', 'false').addClass(menuhoverclass)
                .find('a').first().focus();
        }
        else if (e.keyCode == navigationkeys.ESC) {
            // Close all submenus
            e.preventDefault();
            $('.'+menuhoverclass)
                .attr('aria-hidden', 'true').removeClass(menuhoverclass);
        }
    });

    // Key bindings to navigate dropdown submenus
    var links = menulinks.closest('li').find('ul').find('a');
    links.keydown(function(e) {
        if (e.keyCode == navigationkeys.UP) {
            // Focus previous submenu item or parent menu item
            e.preventDefault();
            if ($(this).closest('li').prev('li').length === 0) {
                $(this).parents('ul').parents('li').find('a').first().focus();
            }
            else {
                $(this).closest('li').prev('li').find('a').first().focus();
            }
        }
        else if (e.keyCode == navigationkeys.DOWN) {
            // Focus next submenu item or next main menu item
            if ($(this).closest('li').next('li').length === 0) {
                var nextmenuitem = $(this).closest('ul').parent('li').next('li');
                if (nextmenuitem.length > 0) {
                    e.preventDefault();
                    nextmenuitem.find('a').first().focus();
                }
            }
            else {
                e.preventDefault();
                $(this).closest('li').next('li').find('a').first().focus();
            }
        }
        else if (e.keyCode == navigationkeys.LEFT) {
            // Focus previous main menu item
            e.preventDefault();
            var prevmenuitem = $(this).closest('ul').parent('li').prev('li');
            if (prevmenuitem.length > 0) {
                prevmenuitem.find('a').first().focus();
            }
        }
        else if (e.keyCode == navigationkeys.RIGHT) {
            // Focus next main menu item
            e.preventDefault();
            var nextmenuitem = $(this).closest('ul').parent('li').next('li');
            if (nextmenuitem.length > 0) {
                nextmenuitem.find('a').first().focus();
            }
        }
        else if (e.keyCode == navigationkeys.ESC) {
            // Exit submenu
            e.preventDefault();
            var ul = $(this).closest('ul');
            ul.siblings('a').focus();
            ul.attr('aria-hidden', 'true').removeClass(menuhoverclass);
        }
        else if (e.keyCode == navigationkeys.SPACE) {
            e.preventDefault();
            window.location = $(this).attr('href');
        }
    });

    // Hide menu if click or focus occurs outside of navigation
    menu.find('a').last().keydown(function(e) {
        if (e.keyCode == navigationkeys.TAB) {
            close_open_menu();
        }
    });
    $(document).on('click', function(event) {
      if (!$(event.target).closest('.has-dropdown').length) {
        close_open_menu();
      }
    });

    // Helper to close any open dropdowns
    function close_open_menu() {
        $('#' + menuid + ' .' + menuhoverclass).attr('aria-hidden', 'true').removeClass(menuhoverclass);
    }
});
