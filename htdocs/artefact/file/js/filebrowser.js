/**
 * File browser
 *
 * @package    mahara
 * @subpackage artefact-file
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

var FileBrowser = (function($) {
  return function (idprefix, folderid, config, globalconfig) {

    var self = this;
    this.id = idprefix;
    this.folderid = folderid;
    this.config = config;
    this.config.wwwroot = globalconfig.wwwroot;
    this.config.sesskey = globalconfig.sesskey;
    this.config.theme = globalconfig.theme;
    this.nextupload = 0;
    this.createfolder_is_connected = false;

    this.init = function () {
        self.form = jQuery('#' + self.id + '_filelist_container').closest('form.pieform')[0];
        if (!self.form) {
            alert('Filebrowser error 1');
        }
        if (self.config.select && typeof(self.form.submit) != 'function') {
            console.log('Filebrowser error 2: Rename your submit element to something other than "submit"');
        }
        self.foldername = jQuery('#' + self.id + '_foldername').val();
        if (self.config.select) {
            self.select_init();
        }
        if (self.config.upload) {
            self.upload_init();
        }
        self.browse_init();
        if (self.config.edit || self.config.editmeta) {
            self.edit_init();
        }
        jQuery('#' + self.id + '_resizeonuploaduserenable').on('change', function (e) {
            self.clear_create_folder_messages();
        });

    };

    this.submitform = function () {
        window.isRequestProcessing = true;
        // for some reason tinymce throws error when use native submit..
        // introducing custom event and catching it in pieform solves the problem...
        // TODO: fileuploader should be refactored in a nicer way
        jQuery(self.form).triggerHandler('onsubmit');
        self.form.submit();
    };

    this.upload_init = function () {
        if (jQuery('#' + self.id + '_notice').length) {
            // If the file input is disabled immediately without this delay, later javascript changes
            // to the filebrowser are not rendered by Chrome when the filebrowser is inside a block
            // configuration form.
            setTimeout(function () {
                jQuery('#' + self.id + '_userfile').prop('disabled', true);
            }, 1);
        }
        if (!jQuery('#' + self.id + '_upload').length) {

          jQuery('<input>', {
              'type': 'hidden',
              'name': self.id + '_upload',
              'id' : self.id + '_upload',
              'value': 0
          }).insertAfter(jQuery('#' + self.id + '_uploadnumber'));
        }
        self.upload_connectbuttons();
    };

    this.upload_connectbuttons = function () {
        if (jQuery('#' + self.id + '_notice').length) {
          jQuery('#' + self.id + '_notice').on('click', function (e) {
                // error class is too general?
                jQuery('#' + self.id + '_upload_messages div.error').each(function(el) {
                    jQuery(el).remove();
                });
                if (this.checked) {
                    jQuery('#'+ self.id + '_userfile').prop('disabled', false); // setNodeAttribute to false doesn't work here.
                }
                else {
                    jQuery('#'+ self.id + '_userfile').prop('disabled', true);
                }
            });
        }
        jQuery('#' + self.id + '_userfile').on('click', self.clear_create_folder_messages);
        jQuery('#' + self.id + '_userfile').off('change');
        jQuery('#' + self.id + '_userfile').on('change', self.upload_submit);
    };

    this.upload_validate_dropzone = function () {
        if (jQuery('#' + self.id + '_notice').length && !jQuery('#' + self.id + '_notice').prop('checked')) {
            return get_string('youmustagreetothecopyrightnotice');
        }
        return false;
    };

    this.clear_create_folder_messages = function() {
        jQuery('#' + self.id + '_createfolder_messages').empty();
    };

    this.upload_validate = function () {
        if (jQuery('#' + self.id + '_notice').length && !jQuery('#' + self.id + '_notice').prop('checked')) {
            jQuery('#' + self.id+'_upload_messages').append(jQuery('<div>', {'class':'alert alert-danger', 'text':get_string('youmustagreetothecopyrightnotice')}));
            return false;
        }

        if (jQuery('#' + self.id + '_userfile')[0].files.length > self.config.maxfileuploads) {
            var errmsg = jQuery('<div>', {'class':'alert alert-danger'});
            errmsg.html(get_string_ajax('fileuploadtoomany', 'error', self.config.maxfileuploads));
            jQuery('#' + self.id+'_upload_messages').append(errmsg);
            return false;
        }

        if (!(jQuery('#' + self.id + '_userfile')[0].files[0].size < globalconfig.maxuploadsize)) {
            var errmsg = jQuery('<div>', {'class':'alert alert-danger'});
            errmsg.html(get_string_ajax('fileuploadtoobig', 'error', globalconfig.maxuploadsizepretty));
            jQuery('#' + self.id+'_upload_messages').append(errmsg);
            return false;
        }
        return !$.isEmptyObject(jQuery('#' + self.id + '_userfile').val());
    };

    this.add_upload_message = function (messageType, filename) {
        self.nextupload++;
        var message = jQuery(makeMessage(jQuery('<span>'), messageType));
        message.text(' ' + get_string('uploadingfiletofolder', 'artefact.file', filename, self.foldername));
        message.prop('id', 'uploadstatusline' + self.nextupload);
        message.appendTo('#' + self.id + '_upload_messages');
        jQuery('#' + self.id + '_uploadnumber').val(self.nextupload);
    };

    this.upload_presubmit_dropzone = function (e) {
        // Display upload status
        self.add_upload_message('pending', e.name);
        return true;
    };

    this.upload_presubmit = function (e) {
        // Display upload status
        if (jQuery('#' + self.id + '_userfile').prop('files')) {
            for (var i = 0; i < jQuery('#' + self.id + '_userfile').prop('files').length; ++ i) {
                var localname = jQuery('#' + self.id + '_userfile').prop('files')[i].name;
                self.add_upload_message('pending', localname);
            }
        }
        return true;
    };

    this.upload_submit = function (e) {
        e.preventDefault();
        if (!self.upload_validate()) {
            return false;
        }

        self.upload_presubmit();
        jQuery('#' + self.id + '_upload').val(1);
        self.submitform();

        // jQuery(self.id + '_userfile').value = ''; // Won't work in IE
        var accept = jQuery('#' + self.id + '_userfile').prop('accept');
        if (typeof accept === typeof undefined || accept === false) {
            accept = '*';
        }

        jQuery('#' + self.id + '_userfile_container').empty().append(
            jQuery('<input>', {
                'type':'file',
                'class':'file',
                'id':self.id+'_userfile',
                'name':'userfile[]',
                'multiple':'',
                'accept': accept,
            })
        );
        jQuery('#' + self.id + '_userfile').off('change');
        jQuery('#' + self.id + '_userfile').on('change', self.upload_submit);
        jQuery('#' + self.id + '_upload').val(0);
        return false;
    };

    this.fileexists = function (filename, id) {
        for (var i in self.filedata) {
            if (self.filedata[i].title == filename && (!id || i != id)) {
                return true;
            }
        }
        return false;
    };

    this.createfolder_submit = function (e) {
        var message;
        var name = jQuery('#' + self.id + '_createfolder_name')[0];
        if (!name) {
            message = get_string('foldernamerequired');
        }
        else {
            name = name.value.trim();
            if (name == '') {
                message = get_string('foldernamerequired');
            }
            else if (name.length > 1024) {
                message = get_string('nametoolong');
            }
            else if (self.fileexists(name)) {
                message = get_string('filewithnameexists', 'artefact.file', name);
            }
        }
        if (message) {
            e.preventDefault();
            jQuery('#' + self.id + '_createfolder_messages').empty().append(makeMessage(message, 'error'));
            return false;
        }
        else {
            jQuery('#' + self.id + '_createfolder_messages').empty().append(makeMessage(get_string('createfoldersuccess', 'artefact.file'), 'ok'));
        }
        progressbarUpdate('folder');
    };

    this.edit_submit = function (e) {
        var message;
        self.clear_create_folder_messages();
        var name = jQuery('#' + self.id + '_edit_title');
        if (!name.length) {
            message = get_string('namefieldisrequired');
        }
        else {
            name = name.val().trim();
            if (name == '') {
                message = get_string('namefieldisrequired');
            }
            else if (name.length > 1024) {
                message = get_string('nametoolong');
            }
            else if (self.fileexists(name, this.name.replace(/.*_update\[(\d+)\]$/, '$1'))) {
                message = get_string('filewithnameexists', 'artefact.file', name);
            }
        }
        if (message) {
            e.preventDefault();
            jQuery('#' + self.id + '_edit_messages').append(makeMessage(message, 'error'));
            return false;
        }
    };

    this.callback_feedback = function (data) {
        var infoclass = 'info';
        var temp = false;
        if (data.problem) {
            infoclass = 'warning';
        }
        else if (data.error) {
            infoclass = 'error';
        }
        else {
            infoclass = 'ok';
            temp = true;
        }

        quotaUpdate(data.quotaused, data.quota);
        if (data.returnCode == '0' || data.uploaded) {
            // pass the artefacttype to update progress bar
            progressbarUpdate(data.artefacttype, data.deleted);
        }
        var newmessage = makeMessage(jQuery('<div>').text(data.message), infoclass, temp);
        jQuery(newmessage).prop('id', 'uploadstatusline' + data.uploadnumber);
        if (data.uploadnumber) {
            jQuery('#uploadstatusline'+data.uploadnumber).remove();
        }
        jQuery('#' + self.id + '_upload_messages').append(newmessage);
        if (jQuery(newmessage).hasClass('alert-temp')) {
            jQuery(newmessage).delay(3000).fadeOut(500);
        }
    };

    this.hide_edit_form = function () {

        var editrow = jQuery('#' + self.id + '_edit_row');
        self.clear_create_folder_messages();
        if (!editrow.hasClass('d-none')) {
            if ((typeof formchangemanager !== 'undefined') && !formchangemanager.confirmLeavingForm()) {
                return false;
            }
            editrow.addClass('d-none');
            // Reconnect the old edit button to open the form
            if (editrow[0].previousSibling) {
                jQuery(editrow[0].previousSibling).find('button').each(function () {
                    var name = jQuery(this).prop('name').match(new RegExp('^' + self.id + "_([a-z]+)\\[(\\d+)\\]$"));
                    if (name && name[1] && name[1] == 'edit') {
                        jQuery(this).off();
                        jQuery(this).on('click', self.edit_form);
                    }
                });
            }
        }
        return true;
    };

    this.edit_form = function (e) {
        e.preventDefault();

        // In IE, this.value is set to the button text
        var id = jQuery(this).prop('name').replace(/.*_edit\[(\d+)\]$/, '$1');
        self.clear_create_folder_messages();

        if (!self.hide_edit_form()) {
            return;
        }

        jQuery('[id^=' + self.id + '_edit_]').on('change', function (e) {
            self.clear_create_folder_messages();
        });
        jQuery('#' + self.id + '_rotator').on('change', function (e) {
            self.clear_create_folder_messages();
        });

        jQuery('#' + self.id + '_edit_heading').html(self.filedata[id].artefacttype == 'folder' ? get_string('editfolder') : get_string('editfile'));
        var editfilerow = jQuery('#' + self.id + '_edit_heading').closest('tr');
        if (self.filedata[id].artefacttype == 'profileicon') {
            editfilerow.addClass('d-none');
        }
        else {
            editfilerow.removeClass('d-none');
            let descriptionrowlabel = jQuery('#' + self.id + '_edit_description').prev('label');
            if (self.filedata[id].artefacttype == 'image') {
                descriptionrowlabel.text(get_string('caption', 'artefact.image'));
            }
            else {
                descriptionrowlabel.text(get_string('description', 'mahara'));
            }
        }
        if (self.filedata[id].artefacttype == 'image' || self.filedata[id].artefacttype == 'profileicon') {
            var rotator = jQuery('#' + self.id + '_rotator');
            rotator.removeClass('d-none');
            var rotatorimg = rotator.find('img');
            // set up initial info
            var origangle = parseInt(self.filedata[id].orientation, 10);
            rotator.find('#rotate_img').prop('title', get_string('rotate' + ((origangle + 90) % 360) + 'img', 'artefact.file'));
            var jstimestamp = Math.round(new Date().getTime()/1000);
            rotatorimg.prop('src', config.wwwroot + 'artefact/file/download.php?file=' + id + '&maxheight=100&maxwidth=100&ts=' + jstimestamp);
            rotatorimg.data('angle', origangle);
            rotatorimg.prop('style', '');
            rotator.find('#rotate_img').off();
            jQuery('#' + self.id + '_edit_orientation').val(origangle);
            // Do transformation
            rotator.find('#rotate_img').on('click', function() {
                var angle =  (rotatorimg.data('angle') + 90) || 90;
                rotatorimg.css({'transform': 'rotate(' + (angle - origangle) + 'deg)', 'transition': 'all 1s ease'});
                rotatorimg.data('angle', angle);
                jQuery('#' + self.id + '_edit_orientation').val(angle % 360);
                jQuery(this).prop('title', get_string('rotate' + ((angle + 90) % 360) + 'img', 'artefact.file'));
                jQuery(this).trigger('mouseout').trigger('mouseover'); // to allow the next tooltip to show without needing to move mouse away from button
                self.clear_create_folder_messages();
            });
        }
        else {
            jQuery('#' + self.id + '_rotator').addClass('d-none');
        }
        jQuery('#' + self.id + '_edit_title').val(self.filedata[id].title);
        jQuery('#' + self.id + '_edit_description').val(self.filedata[id].description == null ? '' : self.filedata[id].description);
        jQuery('#' + self.id + '_edit_alttext').val(self.filedata[id].alttext == null ? '' : self.filedata[id].alttext);

        // Display options for image file descriptions and alt text
        if (self.filedata[id].artefacttype == 'image') {
            jQuery('#' + self.id + '_edit_isdecorative').closest('.form-group').removeClass('d-none');
            if (self.filedata[id].isdecorative) {
                // We hide the alttext, altiscaption, and desctiption fields if image is decorative only
                jQuery('#' + self.id + '_edit_alttext').closest('.form-group').addClass('d-none');
                jQuery('#' + self.id + '_edit_altiscaption').closest('.form-group').addClass('d-none');
                jQuery('#' + self.id + '_edit_description').closest('.form-group').addClass('d-none');
            }
            else {
                jQuery('#' + self.id + '_edit_alttext').closest('.form-group').removeClass('d-none');
                jQuery('#' + self.id + '_edit_altiscaption').closest('.form-group').removeClass('d-none');
                if (self.filedata[id].altiscaption) {
                    // We hide the description field if image has alttext as caption
                    jQuery('#' + self.id + '_edit_description').closest('.form-group').addClass('d-none');
                }
                else {
                    jQuery('#' + self.id + '_edit_description').closest('.form-group').removeClass('d-none');
                }
            }

            // When we click on the decorative switch
            jQuery('#' + self.id + '_edit_isdecorative').on("click", function() {
                if (this.checked) {
                    jQuery('#' + self.id + '_edit_alttext').closest('.form-group').addClass("d-none");
                    jQuery('#' + self.id + '_edit_description').closest('.form-group').addClass("d-none");
                    jQuery('#' + self.id + '_edit_altiscaption').closest('.form-group').addClass("d-none");
                }
                else {
                    jQuery('#' + self.id + '_edit_alttext').closest('.form-group').removeClass("d-none");
                    jQuery('#' + self.id + '_edit_altiscaption').closest('.form-group').removeClass("d-none");
                    if (!self.filedata[id].altiscaption) {
                        jQuery('#' + self.id + '_edit_description').closest('.form-group').removeClass("d-none");
                    }
                }
            })

            // When we click on the alt is caption switch
            jQuery('#' + self.id + '_edit_altiscaption').on("click", function() {
                if (this.checked) {
                    jQuery('#' + self.id + '_edit_description').closest('.form-group').addClass("d-none");
                }
                else {
                    jQuery('#' + self.id + '_edit_description').closest('.form-group').removeClass("d-none");
                }
            })
        }
        else {
            jQuery('#' + self.id + '_edit_isdecorative').closest('.form-group').addClass('d-none');
            jQuery('#' + self.id + '_edit_alttext').closest('.form-group').addClass('d-none');
            jQuery('#' + self.id + '_edit_altiscaption').closest('.form-group').addClass('d-none');
            jQuery('#' + self.id + '_edit_description').closest('.form-group').removeClass("d-none");
        }

        // For dealing with license field if present
        if (jQuery('#' + self.id + '_edit_license').length) {
            if (self.filedata[id].license == null) {
                jQuery('#' + self.id + '_edit_license').val('');
            }
            else {
                jQuery('#' + self.id + '_edit_license').val(self.filedata[id].license);
                if (jQuery('#' + self.id + '_edit_license').val() != self.filedata[id].license) {
                    // Doesn't exist in the select box, add it!
                    var new_option = jQuery('<option>');
                    new_option.attr('value', self.filedata[id].license);
                    new_option.text(self.filedata[id].license);
                    jQuery('#' + self.id + '_edit_license').append(new_option);
                    jQuery('#' + self.id + '_edit_license').val(self.filedata[id].license);
                }
            }
            jQuery('#' + self.id + '_edit_licensor').val(self.filedata[id].licensor == null ? '' : self.filedata[id].licensor);
            jQuery('#' + self.id + '_edit_licensorurl').val(self.filedata[id].licensorurl == null ? '' : self.filedata[id].licensorurl);
            pieform_select_other(jQuery('#' + self.id + '_edit_license')[0]);
        }

        jQuery('#' + self.id + '_edit_allowcomments').prop('checked', self.filedata[id].allowcomments);
        jQuery('#' + self.id + '_edit_isdecorative').prop('checked', self.filedata[id].isdecorative);
        jQuery('#' + self.id + '_edit_altiscaption').prop('checked', self.filedata[id].altiscaption);

        jQuery('#' + self.id + '_edit_tags').prop('selectedIndex', -1);
        self.tag_select2_clear(self.id + '_edit_tags');
        if (self.filedata[id].tags) {
            for (var x in self.filedata[id].tags) {
                var option = document.createElement("option");
                option.text = self.filedata[id].tags[x];
                option.value = x;
                option.selected = "selected";
                jQuery('#' + self.id + '_edit_tags').append(option);
            }
        }
        jQuery('#' + self.id + '_edit_messages').empty();
        if (self.filedata[id].uploadedby) {
            jQuery('#' + self.id + '_edit_uploadedby').text(self.filedata[id].uploadedby);
        }
        else {
            jQuery('#' + self.id + '_edit_uploadedby').parent().hide();
        }
        jQuery('#' + self.id + '_edit_row input.permission').each(function () {
            var perm = jQuery(this).prop('name').split(':');
            if (self.filedata[id].permissions[perm[1]] && self.filedata[id].permissions[perm[1]][perm[2]] == 1) {
                jQuery(this).prop('checked', true);
            }
            else {
                jQuery(this).prop('checked', false);
            }
        });
        // jQuery(self.id + '_edit_artefact').value = id; // Changes button text in IE
        jQuery('#' + self.id + '_edit_artefact').prop('name', self.id + '_update[' + id + ']');

        self.tag_select2(self.id + '_edit_tags');
        var edit_row = jQuery('#' + self.id + '_edit_row').detach();
        var this_row = jQuery(this).closest('tr');
        edit_row.insertAfter(this_row);
        edit_row.removeClass('d-none');

        jQuery(this).trigger('resize.bs.modal');

        // Make the edit button close the form again
        jQuery(this).off();
        jQuery(this).on('click', function (e) {
            e.preventDefault();
            // Check if there are some dirty changes before close the edit form
            if ((typeof formchangemanager !== 'undefined') && formchangemanager.confirmLeavingForm()) {
                jQuery('#' + self.id + '_edit_row').addClass('d-none');
                jQuery(this).off();
                jQuery(this).on('click', self.edit_form);
                self.clear_create_folder_messages();
            }
            jQuery(this).trigger('resize.bs.modal');
            return false;
        });

        return false;
    };

    this.tag_select2_clear = function (id) {
        var select2 = jQuery('#' + id).data('select2');
        if (select2) {
            jQuery('#' + id).select2();
        }
        jQuery('#' + id).find('option').remove();
    };

    this.tag_select2 = function (id) {
        var placeholder = get_string('defaulthint');

        jQuery('#' + id).select2({
            ajax: {
                url: self.config.wwwroot + "json/taglist.php",
                dataType: 'json',
                type: 'POST',
                delay: 250,
                data: function(params) {
                    self.clear_create_folder_messages();
                    return {
                        'q': params.term,
                        'page': params.page || 0,
                        'sesskey': self.config.sesskey,
                        'offset': 0,
                        'limit': 10,
                        'institution': jQuery('#institutionselect_institution').val(),
                    };
                },
                processResults: function(data, page) {
                    self.clear_create_folder_messages();
                    return {
                        results: data.results,
                        pagination: {
                            more: data.more
                        }
                    };
                }
            },
            language: globalconfig.select2_lang,
            multiple: true,
            width: "300px",
            allowClear: false,
            placeholder: placeholder,
            minimumInputLength: 1,
            tags: true,
        });
    };

    this.edit_init = function () { }

    this.browse_init = function () {
        if (self.config.edit || self.config.editmeta) {
            jQuery('#' + self.id + '_filelist button').each(function () {
                var name = jQuery(this).prop('name').match(new RegExp('^' + self.id + "_([a-z]+)\\[(\\d+)\\]$"));
                if (name && name[1]) {
                    if (name[1] == 'edit') {
                        jQuery(this).off('click');
                        jQuery(this).on('click', self.edit_form);
                    }
                    else if (name[1] == 'delete') {
                        var id = name[2];
                        var warn = '';
                        if (self.filedata[id].artefacttype == 'folder') {
                            if (self.filedata[id].viewcount > 0) {
                                warn += get_string('folderappearsinviews') + ' ';
                            }
                            if (self.filedata[id].childcount > 0) {
                                warn += get_string('foldernotempty') + ' ';
                                if (self.filedata[id].profileiconcount > 0) {
                                    warn += get_string('foldercontainsprofileicons', 'artefact.file', self.filedata[id].profileiconcount) + ' ';
                                }
                                warn += get_string('confirmdeletefolderandcontents');
                            }
                            else if (warn != '') {
                                warn += get_string('confirmdeletefolder');
                            }
                        }
                        else {
                            if (self.filedata[id].defaultprofileicon == id) {
                                warn += get_string('defaultprofileicon') + ' ';
                            }
                            if (self.filedata[id].attachcount > 0) {
                                warn += get_string('fileattachedtoportfolioitems', 'artefact.file', self.filedata[id].attachcount) + ' ';
                            }
                            if (self.filedata[id].viewcount > 0) {
                                warn += get_string('fileappearsinviews') + ' ';
                            }
                            if (self.filedata[id].postcount > 0) {
                                warn += get_string('fileappearsinposts') + ' ';
                            }
                            if (self.filedata[id].skincount > 0) {
                                warn += get_string('fileappearsinskins') + ' ';
                            }
                            warn += get_string('confirmdeletefile');
                        }

                        if (warn != '') {
                            jQuery(this).on('click', function (e) {
                                self.clear_create_folder_messages();
                                if (!confirm(warn)) {
                                    e.preventDefault();
                                    return false;
                                }
                            });
                        }
                    }
                }
            });
            jQuery('#' + self.id + '_edit_cancel').on('click', function (e) {
                e.preventDefault();
                if (typeof formchangemanager !== 'undefined') {
                    var form = jQuery(this).closest('form')[0];
                    formchangemanager.setFormState(form, FORM_INIT);
                }
                self.hide_edit_form();
                return false;
            });
            jQuery('#' + self.id + '_edit_artefact').on('click', self.edit_submit);
            self.clear_create_folder_messages();

            if (self.config.edit) {

                jQuery('#' + self.id + '_filelist div.icon-drag').each(function () {
                    self.make_icon_draggable(this);
                    self.make_icon_keyboard_accessible(this);
                });
                jQuery('#' + self.id + '_filelist tr.folder').each(self.make_droppable);
                jQuery('#' + self.id + '_foldernav a.changefolder').each(self.make_droppable);
            }
        }
        jQuery('#' + self.id + '_upload_browse a.changeowner').each(function () {
            jQuery(this).on('click', function (e) {
                var href = jQuery(this).prop('href');
                jQuery('#' + self.id + '_changeowner').val(1);
                jQuery('#' + self.id + '_owner').val(getUrlParameter('owner', href));
                self.clear_create_folder_messages();
                if (getUrlParameter('ownerid', href)) {
                    jQuery('#' + self.id + '_ownerid').val(getUrlParameter('ownerid', href));
                }
                else {
                    jQuery('#' + self.id + '_ownerid').val('');
                }
                if (getUrlParameter('folder', href)) {
                    jQuery('#' + self.id + '_changefolder').val(getUrlParameter('folder', href));
                }
                self.submitform();
                jQuery('#' + self.id + '_changefolder').val('');
                jQuery('#' + self.id + '_changeowner').val(jQuery('#' + self.id + '_changefolder').val());
                e.preventDefault();
                return false;
            });
        });
        jQuery('#' + self.id + '_upload_browse a.changefolder').each(function () {
            jQuery(this).on('click', function (e) {
                if (self.config.edit) {
                    if ((typeof formchangemanager !== 'undefined') && !formchangemanager.confirmLeavingForm()) {
                        e.preventDefault();
                        self.clear_create_folder_messages();
                        return false;
                    }
                }
                var href = jQuery(this).prop('href');
                jQuery('#' + self.id + '_changefolder').val(getUrlParameter('folder', href));
                if (jQuery('#' + self.id + '_owner').length) {
                    jQuery('#' + self.id + '_owner').val(getUrlParameter('owner', href));
                    jQuery('#' + self.id + '_ownerid').val(getUrlParameter('ownerid', href));
                }
                self.submitform();
                self.clear_create_folder_messages();
                jQuery('#' + self.id + '_changefolder').val('');
                e.preventDefault();
                return false;
            });
        });
        if (jQuery('#' + self.id + '_createfolder').length && !self.createfolder_is_connected) {
            jQuery('#' + self.id + '_createfolder').on('click', self.createfolder_submit);
            self.createfolder_is_connected = true;
        }
        if (self.config.select) {
            if (self.config.selectone) {
                var selectedid = Object.keys(self.selecteddata)[0];
                self.selectoneid = selectedid;
                self.add_to_selected_list(selectedid);
            }
            self.connect_select_buttons();
        }
        self.connect_link_modal();
    };

    this.create_move_list = function(icon, moveid) {
        var self = this;
        if (self.move_list) {
            self.move_list.remove();
        }

        var wrapper = jQuery('<div>');
        var ul = jQuery('<ul>').addClass('file-move-list');
        jQuery('#' + self.id + '_filelist a.changefolder').each(function(i) {
            var title = jQuery(this);
            var elemid = title.attr('id').replace(/^changefolder:/, '');
            if (elemid != moveid) {
                var displaytitle = title.find('.display-title').html();
                if (typeof displaytitle !== 'undefined') {
                    var link = jQuery('<a>').prop('href', '#').html(get_string('moveto', 'artefact.file', displaytitle));
                    link.on('click keydown', function(e) {
                        if ((e.type === 'click' || e.keyCode === 32) && !e.isDefaultPrevented()) {
                            self.setfocus = 'changefolder:' + elemid;
                            self.move_to_folder(moveid, elemid);
                            self.move_list = null;
                            e.preventDefault();
                        }
                    });
                    ul.append(jQuery('<li><span class="icon icon-long-arrow-alt-right left"></span>').append(link));
                }
            }
        });

        if (ul.children().length === 0) {
            wrapper.append(jQuery('<span>').html(get_string_ajax('nofolderformove', 'artefact.file')));
        }

        var cancellink = jQuery('<a>').prop('href', '#').html(get_string('cancel'));
        cancellink.on('click keydown', function(e) {
            if ((e.type === 'click' || e.keyCode === 32) && !e.isDefaultPrevented()) {
                wrapper.remove();
                icon.trigger("focus");
                self.move_list = null;
                e.preventDefault();
            }
        });
        ul.append(jQuery('<li><span class="icon icon-times left"></span>').append(cancellink));
        wrapper.append(ul);

        self.move_list = wrapper;
        return wrapper;
    }

    this.make_icon_keyboard_accessible = function(icon) {
        var self = this;
        var id = icon.id.replace(/.+:/, '');
        jQuery(icon).on('click keydown', function(e) {
            if (e.type === 'click' || e.keyCode === 32 || e.keyCode === 13) {
                var folderlist = self.create_move_list(icon, id);
                jQuery(icon).closest('tr').find('.filename').append(folderlist);
                folderlist.find('a').first().trigger("focus");
                e.preventDefault();
            }
        });
    };

    this.make_droppable = function() {
        jQuery(this).droppable({
          hoverClass: "folderhover",
          drop: function(event, ui) {
              var dragid = ui.draggable.prop('id').replace(/^.*drag:(\d+)$/, '$1');
              var dropid = this.id.replace(/^file:(\d+)$/, '$1');
              if (dragid == dropid) {
                  return;
              }
              self.move_to_folder(dragid, dropid);
          }
        });
    };


    this.move_to_folder = function(dragid, dropid) {
        jQuery('#' + this.id + '_move').val(dragid);
        jQuery('#' + this.id + '_moveto').val(dropid);
        this.submitform();
        jQuery('#' + this.id + '_move').val('');
        jQuery('#' + this.id + '_moveto').val('');
    };

    this.drag = {};

    this.make_icon_draggable = function(elem) {
        jQuery(elem).draggable({
          revert: "invalid",
          helper: function(e) {
            return jQuery('<div>', { 'class': "icon-drag-current"}).css('height','1em');
          }
        });
    };

    this.select_init = function () {
        if (jQuery('#' + self.id + '_open_upload_browse').length) {
            jQuery('#' + self.id + '_open_upload_browse').on('click', function (e) {
                e.preventDefault();
                jQuery('#' + self.id + '_upload_browse').removeClass('d-none');
                jQuery('#' + self.id + '_open_upload_browse_container').addClass('d-none');
                return false;
            });
        }
        if (jQuery('#' + self.id + '_close_upload_browse')) {
            jQuery('#' + self.id + '_close_upload_browse').on('click', function (e) {
                e.preventDefault();
                jQuery('#' + self.id + '_upload_browse').addClass('d-none');
                jQuery('#' + self.id + '_open_upload_browse_container').removeClass('d-none');
                return false;
            });
        }
        jQuery('#' + self.id + '_selectlist button.unselect').each(function () {
            self.clear_create_folder_messages();
            jQuery(this).on('click', self.unselect);
        });
    };

    /**
     * A modal popup to show larger version of image.
     * The popup is hooked onto the name link in filebrowser
     */
    this.connect_link_modal = function () {
        if (jQuery('#' + self.id + '_filelist').length === 0) {
            return;
        }

        var pagemodal = jQuery('#' + (jQuery(this).attr('id') + '_page-modal')); // try pagemodal with variable
        if (pagemodal.length === 0) {
            pagemodal = jQuery('#page-modal'); // try generic pagemodal
            if (pagemodal.length === 0) {
                return;
            }
        }

        var pagemodalbody = pagemodal.find('.modal-body');
        var elem = jQuery('#' + self.id + '_filelist .img-modal-preview');

        elem.each(function() {

            jQuery(this).on('click', function(e) {

                e.preventDefault();
                self.clear_create_folder_messages();
                var previewimg = pagemodal.find('.previewimg');
                if (previewimg.length === 0) {
                    previewimg = jQuery('<img class="previewimg" src="">');
                    pagemodalbody.append(previewimg);
                }
                var imgsrc = jQuery(this).attr('href');
                imgsrc = updateUrlParameter(imgsrc, 'maxwidth', 400);
                imgsrc = updateUrlParameter(imgsrc, 'maxheight', 400);
                previewimg.attr('src', imgsrc);
                jQuery(pagemodal).modal('show');

            });
        });

        //Set the click event for Close button on preview image modal
        jQuery(pagemodal).on('click', '.modal-footer .btn', function() {
            jQuery(this).closest('.modal').modal('hide');
        });

    };

    this.connect_select_buttons = function () {

        if (document.getElementById(self.id + '_filelist') === null) {
            return;
        }

        var elem = document.getElementById(self.id + '_filelist').getElementsByClassName('js-file-select'),
            i;

        for (var i = 0; i < elem.length; i = i + 1) {

            elem[i].addEventListener('click', function(e){

                e.preventDefault();

                // if folder, or a link that goes somewhere exit out
                if (e.target.nodeName === 'A') {
                    return;
                }
                // if an image preview link
                if (jQuery(e.target).parent().hasClass('img-modal-preview')) {
                    return;
                }

                var id = this.getAttribute('data-id'),
                    j;

                // remove visual selection if this is for selecting 1 file
                if (self.config.selectone) {
                    for (j = 0; j < elem.length; j = j + 1) {
                        jQuery(elem[j]).removeClass('active');
                    }
                }
                jQuery(this).removeClass('warning').addClass('active');

                if (!self.selecteddata[id]) {
                     self.add_to_selected_list(id);
                }
                return false;
            });
        }
    };

    this.update_metadata_to_selected_list = function () {
        jQuery('#' + self.id + '_filelist button.editable').each(function () {
            var id = this.name.replace(/.*_edit\[(\d+)\]$/, '$1');
            var row = jQuery(this).closest('tr');
            var newtitle = row.find('.filename a').first();
            var newdescription =  row.find('td.filedescription').first();
            if (self.selecteddata[id]) {
                var hiddeninput = jQuery('#' + self.id + '_selected\\[' + id + '\\]');
                var legend2update = hiddeninput.closest('fieldset').find('legend span.file-name');
                if (legend2update.length) {
                    legend2update.html(' - ' + newtitle.html());
                }
                var row2update = hiddeninput.closest('tr');
                var filetitle = row2update.find('a');
                if (filetitle.length) {
                    filetitle.html(newtitle.html());
                }
                var filedesc = row2update.find('div.filedescription');
                if (filedesc) {
                    filedesc.html(newdescription.html());
                }
            }
        });
    };

    this.add_to_selected_list = function (id, highlight) {
        if (!self.filedata[id]) {
            return;
        }
        var tbody = jQuery('#' + self.id + '_selectlist tbody').first(),
            rows = tbody.find('tr');

        if (self.config.selectone) {
            rows.each(function () {
                var hiddeninput = jQuery(this).find('input.d-none');

                if (hiddeninput.length) {
                    hiddeninput.remove();
                }
            });
            self.selecteddata = {};
        }

        self.selecteddata[id] = {
            'id': id,
            'artefacttype': self.filedata[id].artefacttype,
            'title': self.filedata[id].title,
            'description': self.filedata[id].description,
            'url': self.filedata[id].url
        };

        if (jQuery('#' + self.id + '_select_' + id).length) {
            jQuery('[id="file:' + id + '"]').addClass('active');
        }
        if (self.filedata[id].tags) {
            self.selecteddata[id].tags = self.filedata[id].tags;
        }
        if (self.filedata[id].license) {
            self.selecteddata[id].license = self.filedata[id].license;
        }
        if (self.filedata[id].licensor) {
            self.selecteddata[id].licensor = self.filedata[id].licensor;
        }
        if (self.filedata[id].licensorurl) {
            self.selecteddata[id].licensorurl = self.filedata[id].licensorurl;
        }
        if (self.filedata[id].altiscaption) {
            self.selecteddata[id].altiscaption = self.filedata[id].altiscaption;
        }
        if (self.filedata[id].alttext) {
            self.selecteddata[id].alttext = self.filedata[id].alttext;
        }
        if (self.filedata[id].isdecorative) {
            self.selecteddata[id].isdecorative = self.filedata[id].isdecorative;
        }
        // Check if the file to add was already in the selected list
        var existed = false;
        for (var i = 0; i < rows.length; i++) {
            var r = jQuery(rows[i]);
            var rowbutton = r.find('button.button');
            var rowid = rowbutton.prop('name').replace(/.*_unselect\[(\d+)\]$/, '$1');
            if (rowid == id) {
                existed = true;
                var hiddeninput = r.find('input.d-none').first();
                if (!hiddeninput.length) {
                    hiddeninput = jQuery('<input>', {'type':'hidden', 'class':'d-none', 'id':self.id+'_selected[' + id + ']', 'name':self.id+'_selected[' + id + ']', 'value':id});
                    rowbutton.closest('td').append(hiddeninput);
                }
                continue;
            }
        }
        if (!existed) {
            var remove = jQuery('<button>', {'class': 'btn btn-secondary btn-sm text-small button submit unselect',
                                        'name': self.id+'_unselect[' + id + ']',
                                        'type': 'button',
                                        'id': 'editcomposite_filebrowser_unselect_' + id,
                                        'title': get_string('remove')});
            remove.append(
                jQuery('<span>', {'class': 'icon icon-times text-danger left'}),
                jQuery('<span>', { 'text': get_string('remove')})
            );
            remove.on('click', self.unselect);

            filelink = '';
            if (self.filedata[id].artefacttype == 'folder') {
                filelink = jQuery('<span>', {'class': 'js-display-title'}).text(self.filedata[id].title);
            }
            else {
                filelink = jQuery('<a>', {'href':self.config.wwwroot + 'artefact/file/download.php?file=' + id}).text(self.filedata[id].title);
            }

            fileIconImg = '';
            if (self.filedata[id].icon.length) {
                fileIconImg = jQuery('<img>', {'src':self.filedata[id].icon});
            }
            else {
                fileIconImg = jQuery('<span>', {'class': 'icon icon-' + self.filedata[id].artefacttype + ' icon-lg'});
            }

            tbody.append(jQuery('<tr>', {'class': (highlight ? ' highlight-file' : '')}).append(
                jQuery('<td>', {'class':'icon-container'}).append(fileIconImg),
                jQuery('<td>', {'class':'filename'}).append(filelink),
                jQuery('<td>', {'class':'text-end text-small'}).append(remove, jQuery('<input>', {'type':'hidden', 'class':'d-none', 'id':self.id+'_selected[' + id + ']',
                                                                                            'name':self.id+'_selected[' + id + ']', 'value':id}))
            ));
        }
        // Display the list
        rows = tbody.find('tr');
        var rcount = 0;
        for (i = 0; i < rows.length; i++) {
            var r = jQuery(rows[i]);
            var rowbutton = r.find('button.button').first();
            var rowid = rowbutton.prop('name').replace(/.*_unselect\[(\d+)\]$/, '$1');
            if (typeof(self.selecteddata[rowid]) != 'undefined') {
                r.addClass('active').removeClass('d-none');
                rcount ++;
            }
            else {
                r.addClass('d-none');
            }
        }

       self.createevent('fileselect', document, self.selecteddata[id]);

        if (rcount == 1) {
            jQuery('#' + self.id + '_selectlist').removeClass('d-none');
            jQuery('#' + self.id + '_empty_selectlist').addClass('d-none');
        }
        this.update_metadata_to_selected_list();
        // are we running inside tinymce imagebrowser plugin?
        if (window.imgbrowserconf_artefactid) {
            // propagate the click
            jQuery('#filebrowserupdatetarget').trigger("click");
        }
        if (self.config.selectone && self.selectoneid !== id) {
            // Need to close modal on selection
            self.selectoneid = id;
            jQuery('#' + self.id + '_upload_browse').modal('hide');
        }
    };

    this.createevent = function(eventName, element, data) {
        var e; // The custom event that will be created

        if (document.createEvent) {
            e = document.createEvent("HTMLEvents");
            e.initEvent(eventName, true, true);
        }
        else {
            e = document.createEventObject();
            e.eventType = eventName;
        }

        e.eventName = eventName;
        e.data = data;
        if (document.createEvent) {
            element.dispatchEvent(e);
        }
        else {
            element.fireEvent("on" + e.eventType, e);
        }
    }


    this.unselect = function (e) {
      e.preventDefault();
      var id = this.name.replace(/.*_unselect\[(\d+)\]$/, '$1');
      delete self.selecteddata[id];
      // Display the list
      var rows = jQuery('#' + self.id + '_selectlist tbody').first().find('tr');
      var rcount = 0;
      for (var i = 0; i < rows.length; i++) {
          var r = jQuery(rows[i]);
          var rowbutton = r.find('button.button').first();
          var rowid = rowbutton.prop('name').replace(/.*_unselect\[(\d+)\]$/, '$1');
          if (typeof(self.selecteddata[rowid]) != 'undefined') {
              r.addClass('active').removeClass('d-none');
              rcount ++;
          }
          else {
              var hiddeninput = r.find('input.d-none').first();
              if (hiddeninput.length) {
                  var legend2update = hiddeninput.closest('fieldset').find('legend span.file-name');
                  if (legend2update.length) {
                      legend2update.html('');
                  }
                  hiddeninput.remove();
              }
              r.addClass('d-none');
          }
      }
      if (rcount == 0) {
          jQuery('#' + self.id + '_selectlist').addClass('d-none');
          jQuery('#' + self.id + '_empty_selectlist').removeClass('d-none');
      }
      if (jQuery('#' + self.id + '_select_' + id).length) {
          jQuery('[id="file:' + id + '"]').removeClass('active');
      }
      return false;
    }

    this.callback = function (form, data) {
        if (data.multiuploads) {
            for (var i in data.multiuploads) {
                self.callback(form, data.multiuploads[i]);
            }
            return;
        }
        self.form = form; // ????
        if (data.uploaded || data.error || data.deleted) {
            self.callback_feedback(data);  // add/update message
            if (data.maxuploadsize) {
                // keep max upload size up to date
                jQuery('#' + self.id + '_userfile_maxuploadsize').text('(' + get_string('maxuploadsizeis', 'artefact.file', data.maxuploadsize) + ')');
            }
        }
        // Clear the create folder form
        if (data.foldercreated && jQuery('#' + self.id + '_createfolder_name').length) {
            jQuery('#' + self.id + '_createfolder_name').val('');
        }
        // Only update the file listing if the user hasn't changed folders yet
        if (data.newlist && (data.folder == self.folderid || data.changedfolder)) {
            self.filedata = data.newlist.data;
            if (self.config.edit || self.config.editmeta) {

                var editrow = jQuery('#' + self.id + '_edit_row').detach();
                jQuery('#' + self.id + '_edit_placeholder').append(editrow);
            }
            jQuery('#' + self.id+'_filelist_container').html(data.newlist.html);

            // Focus management
            if (self.setfocus) {
                jQuery( jQuery('#' + self.setfocus)[0] ).trigger('focus');
                self.setfocus = null;
            }
            else if (data.foldercreated) {
                jQuery( jQuery('[id="changefolder:' + data.highlight + '"]')[0]).trigger('focus');
            }

            if (data.changedfolder && data.newpath) {
                jQuery('#' + self.id+'_folder').val(self.folderid = data.folder);
                jQuery('#' + self.id+'_foldername').val(self.foldername = data.newpath.foldername);
                jQuery('#' + self.id+'_foldernav').html(data.newpath.html);
                if (data.changedowner && data.newtabs && data.newtabdata) {
                    self.tabdata = data.newtabdata;
                    jQuery('#' + self.id+'_ownertabs').html(data.newtabs);
                    if (data.newsubtabs) {
                        jQuery('#' + self.id + '_ownersubtabs').html(data.newsubtabs);
                        jQuery('#' + self.id + '_ownersubtabs').removeClass('d-none');
                    }
                    else {
                        jQuery('#' + self.id + '_ownersubtabs').addClass('d-none');
                    }
                    if (jQuery('#' + self.id + '_upload_container').length) {
                        if (data.newtabdata.upload) {
                            jQuery('#' + self.id + '_upload_container').removeClass('d-none');
                        }
                        else {
                            jQuery('#' + self.id + '_upload_container').addClass('d-none');
                        }
                    }
                    self.config.editmeta = data.editmeta;
                }
                if (self.config.upload) {
                    if (data.disableedit && !jQuery('#' + self.id + '_upload_container').hasClass('d-none')) {
                        jQuery('#' + self.id + '_upload_container').addClass('d-none');
                        if (jQuery('#createfolder').length) {
                            jQuery('#createfolder').addClass('d-none');
                        }
                        jQuery('#' + self.id + '_upload_disabled').removeClass('d-none');
                    }
                    else if (data.disableedit == false) {
                        if (!self.tabdata || self.tabdata.upload) {
                            jQuery('#' + self.id + '_upload_container').removeClass('d-none');
                        }
                        if (jQuery('#createfolder').length) {
                            jQuery('#createfolder').removeClass('d-none');
                        }
                        jQuery('#' + self.id + '_upload_disabled').addClass('d-none');
                    }
                }
            }
            else if (data.uploaded && self.config.select && data.highlight) {
                // Newly uploaded files should be automatically selected
                self.add_to_selected_list(data.highlight, true);
            }
            if (self.config.select && self.config.editmeta) {
                self.update_metadata_to_selected_list();
            }
            if (data.tagblockhtml && jQuery('#sb-tags').length) {
                jQuery('#sb-tags').html(data.tagblockhtml);
            }
            jQuery('#' + self.id + '_filelist').find('.control-buttons button').first().trigger('resize.bs.modal');
            self.browse_init();
        }
        else if (data.goto) {
            location.href = data.goto;
        }
        else if (typeof(data.replaceHTML) == 'string') {
            if (data.returnCode == -1) {
                formError(form, data);
            }
            else {
                formSuccess(form, data);
            }
            self.init();
        }
    };
};
}(jQuery));

// This variable = true if the users has updated the field 'Tags' by clicking the tag.
var tags_changed = false;
