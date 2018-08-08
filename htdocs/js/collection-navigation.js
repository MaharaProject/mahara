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

jQuery(function($) {
"use strict";

    // this feature relies on a custom dropdown element which is initialised in style.js

    // initialise previous and next buttons
    (function() {
        var currentIndex = $('#currentindex').data('currentindex');
        var indexLength = $('.custom-dropdown > ul').children().length;

        function findLink(target) {
            $('.custom-dropdown > ul').children().each(function() {
                var elem = $($(this).children()[0]);
                if (elem.data('index') === target && elem.data('location')) {
                    document.location.href = elem.data('location');
                }
            });
        }

        // setup prev
        if (currentIndex !== 0) {
            $('.prevpage').removeClass('disabled');

            $('.prevpage').on("click", function() {
                var target = currentIndex - 1;
                findLink(target);
            });
            // setup swipe prev
            $('#main-column-container').on('swiperight', function() {
                var target = currentIndex - 1;
                findLink(target);
            });
        }

        // setup next
        if (currentIndex !== (indexLength - 1)) {
            $('.nextpage').removeClass('disabled');

            $('.nextpage').on("click", function() {
                var target = currentIndex + 1;
                findLink(target);
            });
            // setup swipe next
            $('#main-column-container').on('swipeleft', function() {
                var target = currentIndex + 1;
                findLink(target);
            });
        }

    }());

});
