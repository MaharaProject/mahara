/**
 * Previous and next buttons for navigating between
 * pages within a collection
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */
// this feature relies on a custom dropdown element which is initialised in style.js

// initialise previous and next buttons
function collection_nav_init(preview=false) {
    var currentIndex = $('#currentindex').data('currentindex');
    var indexLength = $('.custom-dropdown .dropdown-menu > li').children().length;

    function findLink(target) {
        $('.dropdown-menu').children().each(function() {
            var elem = $($(this).children()[0]);
            if (elem.data('index') === target && elem.data('location')) {
                if (preview) {
                    elem.trigger('click');
                }
                else {
                    document.location.href = elem.data('location');
                }
            }
        });
    }

    // setup prev
    if (currentIndex !== 0) {
        $('.prevpage').removeClass('disabled');
        $('.prevpage').off("click");
        $('.prevpage').on("click", function() {
            var target = currentIndex - 1;
            findLink(target);
        });
        if (isTouchDevice()) {
            // setup swipe prev
            $('#header-content').off('swiperight');
            $('#header-content').on('swiperight', function() {
                var target = currentIndex - 1;
                findLink(target);
            });
        }
    }

    // setup next
    if ((currentIndex + 1) < indexLength) {
        $('.nextpage').removeClass('disabled');
        $('.nextpage').off("click");
        $('.nextpage').on("click", function() {
            var target = currentIndex + 1;
            findLink(target);
        });
        if (isTouchDevice()) {
            // setup swipe next
            $('#header-content').off('swipeleft');
            $('#header-content').on('swipeleft', function() {
                var target = currentIndex + 1;
                findLink(target);
            });
        }
    }
}

function isTouchDevice() {
    return 'ontouchstart' in document.documentElement;
}

jQuery(function($) {
"use strict";
    collection_nav_init();
});
