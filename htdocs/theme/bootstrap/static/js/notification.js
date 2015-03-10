/*jslint browser: true, nomen: true,  white: true */

jQuery(function($) {
"use strict";
    
    $('.notification .control-wrapper').on('click', function(e) {
        e.stopPropagation();
    });

    // Check all of type
    $('[data-togglecheckbox]').on('change', function(){
        var targetClass = '.' + $(this).attr('data-togglecheckbox');
        $(targetClass).prop('checked', $(this).prop('checked'));
        $(targetClass).trigger('change');
    });

    // Add warning class to all selected notifications
    $('.panel .control input').on('change', function(){
         if ($(this).prop('checked')){
            $(this).closest('.panel').addClass('panel-warning');
        } else {
            $(this).closest('.panel').removeClass('panel-warning');
        }
    });
});