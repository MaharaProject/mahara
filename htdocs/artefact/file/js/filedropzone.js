/**
 * File browser dropzone
 * @source: http://gitorious.org/mahara/mahara
 *
 * @licstart
 * Copyright (C) 2006-2013  Catalyst IT Ltd
 *
 * The JavaScript code in this page is free software: you can
 * redistribute it and/or modify it under the terms of the GNU
 * General Public License (GNU GPL) as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option)
 * any later version.  The code is distributed WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU GPL for more details.
 *
 * As additional permission under GNU GPL version 3 section 7, you
 * may distribute non-source (e.g., minimized or compacted)  forms of
 * that code without the copy of the GNU GPL normally required by
 * section 4, provided you include this license notice and a URL
 * through which recipients can access the Corresponding Source.
 * @licend
 */

// set up the dropzone
jQuery(document).ready(function() {
    // to avoid any clashes with other javascript
    var j = jQuery.noConflict();
    // turn on the 'drop file here' area for browsers that can handle it.
    j('#fileDropzone').hide();
    if ('draggable' in document.createElement('span')) {
        j('#fileDropzone').css('min-height', '50px');
        j('#fileDropzone').show();
    }

    // Dropzone uploads it's file queue one at a time so to keep
    // a count of uploads we need to override the data.uploadnumber
    var dropzone_uploadnumber = 0;

    var prefix = j('#file_dropzone_container').attr('class');

    // allow the whole page to be droppable
    // and display the previews below upload file selector
    var myDropzone = new Dropzone(document.body, {
        url: document.URL,
        previewsContainer: 'div#fileDropzone',
        maxFilesize: 2,
        dictFileTooBig: strings.maxuploadsize + ' 2mb',
        maxThumbnailFilesize: 1,
        clickable: false,
        createImageThumbnails: false,
        paramName: 'userfile'

    });


    // on sending the file append the form field data and the
    // fields that Pieform would normally create
    myDropzone.on("sending", function(userfile, xhr, formData) {
        j('#files input').each(function() {
            formData.append(this.name, this.value);
        });
        j('#files select').each(function() {
            formData.append(this.name, j('#files select[name="' + this.name + '"] option:selected').val());
        });
        formData.append(prefix + '_upload', '1');
        formData.append('dropzone', '1');
        formData.append('pieform_jssubmission', '1');
        // remove the data from these as we are only trying to
        // upload a file not do any of these options that appear
        // earlier in the hierarchy
        formData.append(prefix + '_createfolder', '');
        formData.append(prefix + '_update', '');
        formData.append(prefix + '_edit', '');
        formData.append(prefix + '_delete', '');
        j('#file_dropzone_container').removeClass('dragover');
    });

    myDropzone.on("addedfile", function(userfile) {
        window[prefix].dragdrop = true;
        window[prefix].upload_presubmit_dropzone(userfile);
        var response = window[prefix].upload_validate();
        if (response) {
            myDropzone.errorProcessing(userfile,response);
        }
    });

    // successful return from the ajax call that will
    // return pieform data - which could contain
    // error, problem or success
    myDropzone.on("success", function(userfile,data) {
        if (data) {
            try {
                data = JSON.parse(data);
            }
            catch(error) {
                myDropzone.errorProcessing(userfile,error);
            }
        }
        dropzone_uploadnumber ++;
        data['uploadnumber'] = dropzone_uploadnumber;
        window[prefix].callback(window[prefix].form, data);
    });

    // handling errors stemming from dropzone itself
    myDropzone.on("error", function(userfile, errmsg, errxhr) {
        var data = {'error':'true'};
        data['message'] = errmsg;
        if (undefined != errxhr) {
            data['message'] += errxhr;
        }
        window[prefix].callback(window[prefix].form, data);
    });

    j(document.body).bind('dragenter', function(ev) {
        ev.stopPropagation();
        ev.preventDefault();
    });
    j('#file_dropzone_container').bind('dragover', function(ev) {
        ev.stopPropagation();
        ev.preventDefault();
        ev.originalEvent.dataTransfer.dropEffect = 'copy';
        j('#file_dropzone_container').addClass('dragover');
        return false;
    });
    j('#file_dropzone_container').bind('dragleave', function(ev) {
        ev.stopPropagation();
        ev.preventDefault();
        ev.originalEvent.dataTransfer.dropEffect = 'move';
        j('#file_dropzone_container').removeClass('dragover');
        return false;
    });
});
