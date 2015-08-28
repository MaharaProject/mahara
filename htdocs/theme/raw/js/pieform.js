/*jslint browser: true, nomen: true,  white: true */
/* global jQuery, $ */
jQuery(function($) {
"use strict";

    function submitConfirm() {
        $('[data-confirm]').on('click', function() {
            var content = $(this).attr('data-confirm');
            return confirm(content);
        });
    }
    submitConfirm();
});
