/*jslint browser: true, nomen: true,  white: true */
/* global jQuery, $ */
jQuery(function($) {
"use strict";

    /*
     * Add bootstrap class to the add file content for filebrowser
     */
    function bootstrapFileBrowser() {
        // Wrap filebrowser content with Bootstrap classes
        $('.js-filebrowser').wrapInner('<div class="modal-dialog modal-lg"><div class="modal-content modal-filebrowser"></div></div>');
        $('.js-filebrowser').modal('hide');
    }

    /*
     * Display selected file name next to collapsible element title 
     */
    function updateFileLegend(e){
        // Get collapsible element with select-file class and
        // title of the file from filebrowser.js
        var selectfileTitle = $('.select-file legend a'),
            title = e.originalEvent.data.title;

        // Display the file name
        if(selectfileTitle.find('.file-name').length > 0) {
            selectfileTitle.find('.file-name').text(' - ' + title);
        } else {
            selectfileTitle.find('.collapse-indicator').before('<span class="text-small text-midtone file-name"> - '+ title + '</span>');
        }
    }

    $(document).on('fileselect', function(e){
        updateFileLegend(e);
        // Collapse the file browser
        $('.select-file').find('.collapse').collapse('hide');
    });

    bootstrapFileBrowser();
});
