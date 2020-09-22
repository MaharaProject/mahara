/**
 * Previous and next buttons for navigating between
 * pages within a collection
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */
// this feature relies on a custom dropdown element which is initialised in style.js

// initialise previous and next buttons
function collection_nav_init(preview=false) {
    var currentIndex = $('#currentindex').data('currentindex');
    var indexLength = $('.custom-dropdown > ul').children().length;

    function findLink(target) {
        $('.custom-dropdown > ul').children().each(function() {
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
    if (currentIndex !== (indexLength - 1)) {
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
