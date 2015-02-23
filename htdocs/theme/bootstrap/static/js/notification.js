/*jslint browser: true, nomen: true,  white: true */

jQuery(function($) {
"use strict";
    
    $('.notification-item .control-wrapper').click(function(event) {
        event.stopPropagation();
    });
});