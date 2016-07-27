/**
 * File browser dropzone
 *
 * @package    mahara
 * @subpackage artefact-file
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
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
    var current_drop_number = 0;

    var prefix = j('#file_dropzone_container').attr('class');
    var enclosingform = j('#file_dropzone_container').closest('form');

    // allow the whole page to be droppable
    // and display the previews below upload file selector
    var myDropzone = new Dropzone(document.body, {
        url: document.URL,
        previewsContainer: 'div#fileDropzone',
        maxFilesize: (upload_max_filesize / 1048576),
        dictFileTooBig: strings.maxuploadsize + ' ' + (upload_max_filesize / 1048576) + 'mb',
        maxThumbnailFilesize: 1,
        clickable: false,
        createImageThumbnails: false,
        paramName: 'userfile',
        accept : function(file, done) {
            // Check if provided file is a folder.
            if ((file.size) || (file.type) || (file.fullPath)) {
                // Folders shouldn't be more than 1MB.
                if (file.size > 1048576) {
                    return done();
                }
                else {
                    // Folders in some browsers potentially can have size. (e.g. in Safari)
                    // Let's try to load the file. This should be quick as it's small.
                    var fileLoaded = false;
                    var reader = new FileReader();
                    // Reading as a string.
                    reader.readAsBinaryString(file);

                    // This should be triggered on files only and never triggered on folders.
                    reader.onload = (function (event) {
                        fileLoaded = true;
                        return done();
                    });

                    // This should be triggered on files and folders.
                    reader.onloadend = (function (event) {
                        // Looks like loading the file has been failed. It is folder!
                        if (fileLoaded == false ) {
                            return done(strings.fileisfolder.replace("{{filename}}", file.name));
                        }
                    });
                }
            }
            else {
                return done(strings.fileisfolder.replace("{{filename}}", file.name));
            }
        },
    });

    // on sending the file append the form field data and the
    // fields that Pieform would normally create
    myDropzone.on("sending", function(userfile, xhr, formData) {
        enclosingform.find('input').each(function() {
            var reg = /^cancel_/;
            if (this.type == 'checkbox') {
                if (this.checked == true) {
                    formData.append(this.name, this.value);
                }
            }
            else if (!reg.test(this.name)) {
                formData.append(this.name, this.value);
            }
        });
        enclosingform.find('select').each(function() {
            formData.append(this.name, enclosingform.find('select[name="' + this.name + '"] option:selected').val());
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
        formData.append(prefix + '_canceledit', '');
        j('#file_dropzone_container').removeClass('dragover');
    });

    myDropzone.on("selectedfiles", function(userfile) {
        dropzone_uploadnumber = window[prefix].nextupload - current_drop_number;
    });

    myDropzone.on("addedfile", function(userfile) {
        current_drop_number ++;
        window[prefix].dragdrop = true;
        window[prefix].upload_presubmit_dropzone(userfile);
    });

    // successful return from the ajax call that will
    // return pieform data - which could contain
    // error, problem or success
    myDropzone.on("success", function(userfile,data) {
        current_drop_number = 0;
        if (data) {
            try {
                data = JSON.parse(data);
            }
            catch(error) {
                myDropzone.errorProcessing(userfile,error);
            }
        }
        if (data.returnCode == '-2') {
            myDropzone.errorProcessing(userfile,'An error has occurred');
        }
        dropzone_uploadnumber ++;
        data['uploadnumber'] = dropzone_uploadnumber;
        window[prefix].callback(window[prefix].form, data);
    });

    // handling errors stemming from dropzone itself
    myDropzone.on("error", function(userfile, errmsg, errxhr) {
        current_drop_number = 0;
        var data = {'error':'true'};
        data['message'] = errmsg;
        if (undefined != errxhr) {
            data['message'] += errxhr;
        }
        dropzone_uploadnumber ++;
        data['uploadnumber'] = dropzone_uploadnumber;
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
