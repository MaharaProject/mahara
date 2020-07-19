/**
 * File browser
 *
 * @package    mahara
 * @subpackage artefact-file
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
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
        self.form = $('#' + self.id + '_filelist_container').closest('form.pieform')[0];
        if (!self.form) {
            alert('Filebrowser error 1');
        }
        if (self.config.select && typeof(self.form.submit) != 'function') {
            console.log('Filebrowser error 2: Rename your submit element to something other than "submit"');
        }
        self.foldername = $('#' + self.id + '_foldername').val();
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
        $('#' + self.id + '_resizeonuploaduserenable').on('change', function (e) {
            self.clear_create_folder_messages();
        });

    };

    this.submitform = function () {
        window.isRequestProcessing = true;
        // for some reason tinymce throws error when use native submit..
        // introducing custom event and catching it in pieform solves the problem...
        // TODO: fileuploader should be refactored in a nicer way
        $(self.form).triggerHandler('onsubmit');
        self.form.submit();
    };

    this.upload_init = function () {
        if ($('#' + self.id + '_notice').length) {
            // If the file input is disabled immediately without this delay, later javascript changes
            // to the filebrowser are not rendered by Chrome when the filebrowser is inside a block
            // configuration form.
            setTimeout(function () {
                $('#' + self.id + '_userfile').prop('disabled', true);
            }, 1);
        }
        if (!$('#' + self.id + '_upload').length) {

          $('<input>', {
              'type': 'hidden',
              'name': self.id + '_upload',
              'id' : self.id + '_upload',
              'value': 0
          }).insertAfter($('#' + self.id + '_uploadnumber'));
        }
        self.upload_connectbuttons();
    };

    this.upload_connectbuttons = function () {
        if ($('#' + self.id + '_notice').length) {
          $('#' + self.id + '_notice').on('click', function (e) {
                // error class is too general?
                $('#' + self.id + '_upload_messages div.error').each(function(el) {
                    $(el).remove();
                });
                if (this.checked) {
                    $('#'+ self.id + '_userfile').prop('disabled', false); // setNodeAttribute to false doesn't work here.
                }
                else {
                    $('#'+ self.id + '_userfile').prop('disabled', true);
                }
            });
        }
        $('#' + self.id + '_userfile').on('click', self.clear_create_folder_messages);
        $('#' + self.id + '_userfile').off('change');
        $('#' + self.id + '_userfile').on('change', self.upload_submit);
    };

    this.upload_validate_dropzone = function () {
        if ($('#' + self.id + '_notice').length && !$('#' + self.id + '_notice').prop('checked')) {
            return get_string('youmustagreetothecopyrightnotice');
        }
        return false;
    };

    this.clear_create_folder_messages = function() {
        $('#' + self.id + '_createfolder_messages').empty();
    };

    this.upload_validate = function () {
        if ($('#' + self.id + '_notice').length && !$('#' + self.id + '_notice').prop('checked')) {
            $('#' + self.id+'_upload_messages').append($('<div>', {'class':'alert alert-danger', 'text':get_string('youmustagreetothecopyrightnotice')}));
            return false;
        }
        if (!($('#' + self.id + '_userfile')[0].files[0].size < globalconfig.maxuploadsize)) {
            var errmsg = $('<div>', {'class':'alert alert-danger'});
            errmsg.html(get_string_ajax('fileuploadtoobig', 'error', globalconfig.maxuploadsizepretty));
            $('#' + self.id+'_upload_messages').append(errmsg);
            return false;
        }
        return !$.isEmptyObject($('#' + self.id + '_userfile').val());
    };

    this.add_upload_message = function (messageType, filename) {
        self.nextupload++;
        var message = $(makeMessage($('<span>').addClass('icon icon-spinner icon-pulse'), messageType));
        message.text(' ' + get_string('uploadingfiletofolder', 'artefact.file', filename, self.foldername));
        message.prop('id', 'uploadstatusline' + self.nextupload);
        message.appendTo('#' + self.id + '_upload_messages');
        $('#' + self.id + '_uploadnumber').val(self.nextupload);
    };

    this.upload_presubmit_dropzone = function (e) {
        // Display upload status
        self.add_upload_message('info', e.name);
        return true;
    };

    this.upload_presubmit = function (e) {
        // Display upload status
        if ($('#' + self.id + '_userfile').prop('files')) {
            for (var i = 0; i < $('#' + self.id + '_userfile').prop('files').length; ++ i) {
                var localname = $('#' + self.id + '_userfile').prop('files')[i].name;
                self.add_upload_message('ok', localname);
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
        $('#' + self.id + '_upload').val(1);
        self.submitform();

        // $(self.id + '_userfile').value = ''; // Won't work in IE
        $('#' + self.id + '_userfile_container').empty().append(
            $('<input>', {
                'type':'file',
                'class':'file',
                'id':self.id+'_userfile',
                'name':'userfile[]',
                'multiple':''
            })
        );
        $('#' + self.id + '_userfile').off('change');
        $('#' + self.id + '_userfile').on('change', self.upload_submit);
        $('#' + self.id + '_upload').val(0);
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
        var name = $('#' + self.id + '_createfolder_name')[0];
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
            $('#' + self.id + '_createfolder_messages').empty().append(makeMessage(message, 'error'));
            return false;
        }
        else {
            $('#' + self.id + '_createfolder_messages').empty().append(makeMessage(get_string('createfoldersuccess', 'artefact.file'), 'ok'));
        }
        progressbarUpdate('folder');
    };

    this.edit_submit = function (e) {
        var message;
        self.clear_create_folder_messages();
        var name = $('#' + self.id + '_edit_title');
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
            $('#' + self.id + '_edit_messages').append(makeMessage(message, 'error'));
            return false;
        }
    };

    this.callback_feedback = function (data) {
        var infoclass = 'info';
        if (data.problem) {
            infoclass = 'warning';
        }
        else if (data.error) {
            infoclass = 'error';
        }
        else {
            infoclass = 'ok';
        }

        quotaUpdate(data.quotaused, data.quota);
        if (data.returnCode == '0' || data.uploaded) {
            // pass the artefacttype to update progress bar
            progressbarUpdate(data.artefacttype, data.deleted);
        }
        var newmessage = makeMessage($('<div>').text(data.message), infoclass);
        $(newmessage).prop('id', 'uploadstatusline' + data.uploadnumber);
        if (data.uploadnumber) {
            $('#uploadstatusline'+data.uploadnumber).remove();
        }
        $('#' + self.id + '_upload_messages').append(newmessage);
    };

    this.hide_edit_form = function () {

        var editrow = $('#' + self.id + '_edit_row');
        self.clear_create_folder_messages();
        if (!editrow.hasClass('d-none')) {
            if ((typeof formchangemanager !== 'undefined') && !formchangemanager.confirmLeavingForm()) {
                return false;
            }
            editrow.addClass('d-none');
            // Reconnect the old edit button to open the form
            if (editrow[0].previousSibling) {
                $(editrow[0].previousSibling).find('button').each(function () {
                    var name = $(this).prop('name').match(new RegExp('^' + self.id + "_([a-z]+)\\[(\\d+)\\]$"));
                    if (name && name[1] && name[1] == 'edit') {
                        $(this).off();
                        $(this).on('click', self.edit_form);
                    }
                });
            }
        }
        return true;
    };

    this.edit_form = function (e) {
        e.preventDefault();

        // In IE, this.value is set to the button text
        var id = $(this).prop('name').replace(/.*_edit\[(\d+)\]$/, '$1');
        self.clear_create_folder_messages();

        if (!self.hide_edit_form()) {
            return;
        }

        $('[id^=' + self.id + '_edit_]').on('change', function (e) {
            self.clear_create_folder_messages();
        });
        $('#' + self.id + '_rotator').on('change', function (e) {
            self.clear_create_folder_messages();
        });

        $('#' + self.id + '_edit_heading').html(self.filedata[id].artefacttype == 'folder' ? get_string('editfolder') : get_string('editfile'));
        var descriptionrow = $('#' + self.id + '_edit_description').closest('tr');
        if (self.filedata[id].artefacttype == 'profileicon') {
            descriptionrow.addClass('d-none');
        }
        else {
            descriptionrow.removeClass('d-none');
        }
        if (self.filedata[id].artefacttype == 'image' || self.filedata[id].artefacttype == 'profileicon') {
            var rotator = $('#' + self.id + '_rotator');
            rotator.removeClass('d-none');
            var rotatorimg = rotator.find('img');
            // set up initial info
            var origangle = parseInt(self.filedata[id].orientation, 10);
            var jstimestamp = Math.round(new Date().getTime()/1000);
            rotatorimg.prop('src', config.wwwroot + '/artefact/file/download.php?file=' + id + '&maxheight=100&maxwidth=100&ts=' + jstimestamp);
            rotatorimg.data('angle', origangle);
            rotatorimg.prop('style', '');
            rotator.find('span').off();
            $('#' + self.id + '_edit_orientation').val(origangle);
            // Do transformation
            rotator.find('span').on('click', function() {
                var angle =  (rotatorimg.data('angle') + 90) || 90;
                rotatorimg.css({'transform': 'rotate(' + (angle - origangle) + 'deg)', 'transition': 'all 1s ease'});
                rotatorimg.data('angle', angle);
                $('#' + self.id + '_edit_orientation').val(angle % 360);
                self.clear_create_folder_messages();
            });
        }
        else {
            $('#' + self.id + '_rotator').addClass('d-none');
        }
        $('#' + self.id + '_edit_title').val(self.filedata[id].title);
        $('#' + self.id + '_edit_description').val(self.filedata[id].description == null ? '' : self.filedata[id].description);
        if ($('#' + self.id + '_edit_license').length) {
            if (self.filedata[id].license == null) {
                $('#' + self.id + '_edit_license').val('');
            }
            else {
                $('#' + self.id + '_edit_license').val(self.filedata[id].license);
                if ($('#' + self.id + '_edit_license').val() != self.filedata[id].license) {
                    // Doesn't exist in the select box, add it!
                    var new_option = $('<option>');
                    new_option.attr('value', self.filedata[id].license);
                    new_option.text(self.filedata[id].license);
                    $('#' + self.id + '_edit_license').append(new_option);
                    $('#' + self.id + '_edit_license').val(self.filedata[id].license);
                }
            }
            $('#' + self.id + '_edit_licensor').val(self.filedata[id].licensor == null ? '' : self.filedata[id].licensor);
            $('#' + self.id + '_edit_licensorurl').val(self.filedata[id].licensorurl == null ? '' : self.filedata[id].licensorurl);
            pieform_select_other($('#' + self.id + '_edit_license')[0]);
        }
        $('#' + self.id + '_edit_allowcomments').prop('checked', self.filedata[id].allowcomments);

        $('#' + self.id + '_edit_tags').prop('selectedIndex', -1);
        self.tag_select2_clear(self.id + '_edit_tags');
        if (self.filedata[id].tags) {
            for (var x in self.filedata[id].tags) {
                var option = document.createElement("option");
                option.text = self.filedata[id].tags[x];
                option.value = x;
                option.selected = "selected";
                $('#' + self.id + '_edit_tags').append(option);
            }
        }
        $('#' + self.id + '_edit_messages').empty();
        if (self.filedata[id].uploadedby) {
            $('#' + self.id + '_edit_uploadedby').text(self.filedata[id].uploadedby);
        }
        else {
            $('#' + self.id + '_edit_uploadedby').parent().hide();
        }
        $('#' + self.id + '_edit_row input.permission').each(function () {
            var perm = $(this).prop('name').split(':');
            if (self.filedata[id].permissions[perm[1]] && self.filedata[id].permissions[perm[1]][perm[2]] == 1) {
                $(this).prop('checked', true);
            }
            else {
                $(this).prop('checked', false);
            }
        });
        // $(self.id + '_edit_artefact').value = id; // Changes button text in IE
        $('#' + self.id + '_edit_artefact').prop('name', self.id + '_update[' + id + ']');

        self.tag_select2(self.id + '_edit_tags');
        var edit_row = $('#' + self.id + '_edit_row').detach();
        var this_row = $(this).closest('tr');
        edit_row.insertAfter(this_row);
        edit_row.removeClass('d-none');

        $(this).trigger('resize.bs.modal');

        // Make the edit button close the form again
        $(this).off();
        $(this).on('click', function (e) {
            e.preventDefault();
            // Check if there are some dirty changes before close the edit form
            if ((typeof formchangemanager !== 'undefined') && formchangemanager.confirmLeavingForm()) {
                $('#' + self.id + '_edit_row').addClass('d-none');
                $(this).off();
                $(this).on('click', self.edit_form);
                self.clear_create_folder_messages();
            }
            $(this).trigger('resize.bs.modal');
            return false;
        });

        return false;
    };

    this.tag_select2_clear = function (id) {
        var select2 = $('#' + id).data('select2');
        if (select2) {
            $('#' + id).select2();
        }
        $('#' + id).find('option').remove();
    };

    this.tag_select2 = function (id) {
        var placeholder = get_string('defaulthint');

        $('#' + id).select2({
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
                        'institution': $('#institutionselect_institution').val(),
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
            $('#' + self.id + '_filelist button').each(function () {
                var name = $(this).prop('name').match(new RegExp('^' + self.id + "_([a-z]+)\\[(\\d+)\\]$"));
                if (name && name[1]) {
                    if (name[1] == 'edit') {
                        $(this).off('click');
                        $(this).on('click', self.edit_form);
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
                            $(this).on('click', function (e) {
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
            $('#' + self.id + '_edit_cancel').on('click', function (e) {
                e.preventDefault();
                if (typeof formchangemanager !== 'undefined') {
                    var form = $(this).closest('form')[0];
                    formchangemanager.setFormState(form, FORM_INIT);
                }
                self.hide_edit_form();
                return false;
            });
            $('#' + self.id + '_edit_artefact').on('click', self.edit_submit);
            self.clear_create_folder_messages();

            if (self.config.edit) {

                $('#' + self.id + '_filelist div.icon-drag').each(function () {
                    self.make_icon_draggable(this);
                    self.make_icon_keyboard_accessible(this);
                });
                $('#' + self.id + '_filelist tr.folder').each(self.make_droppable);
                $('#' + self.id + '_foldernav a.changefolder').each(self.make_droppable);
            }
        }
        $('#' + self.id + '_upload_browse a.changeowner').each(function () {
            $(this).on('click', function (e) {
                var href = $(this).prop('href');
                $('#' + self.id + '_changeowner').val(1);
                $('#' + self.id + '_owner').val(getUrlParameter('owner', href));
                self.clear_create_folder_messages();
                if (getUrlParameter('ownerid', href)) {
                    $('#' + self.id + '_ownerid').val(getUrlParameter('ownerid', href));
                }
                else {
                    $('#' + self.id + '_ownerid').val('');
                }
                if (getUrlParameter('folder', href)) {
                    $('#' + self.id + '_changefolder').val(getUrlParameter('folder', href));
                }
                self.submitform();
                $('#' + self.id + '_changefolder').val('');
                $('#' + self.id + '_changeowner').val($('#' + self.id + '_changefolder').val());
                e.preventDefault();
                return false;
            });
        });
        $('#' + self.id + '_upload_browse a.changefolder').each(function () {
            $(this).on('click', function (e) {
                if (self.config.edit) {
                    if ((typeof formchangemanager !== 'undefined') && !formchangemanager.confirmLeavingForm()) {
                        e.preventDefault();
                        self.clear_create_folder_messages();
                        return false;
                    }
                }
                var href = $(this).prop('href');
                $('#' + self.id + '_changefolder').val(getUrlParameter('folder', href));
                if ($('#' + self.id + '_owner').length) {
                    $('#' + self.id + '_owner').val(getUrlParameter('owner', href));
                    $('#' + self.id + '_ownerid').val(getUrlParameter('ownerid', href));
                }
                self.submitform();
                self.clear_create_folder_messages();
                $('#' + self.id + '_changefolder').val('');
                e.preventDefault();
                return false;
            });
        });
        if ($('#' + self.id + '_createfolder').length && !self.createfolder_is_connected) {
            $('#' + self.id + '_createfolder').on('click', self.createfolder_submit);
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

        var wrapper = $('<div>');
        var ul = $('<ul>').addClass('file-move-list');
        $('#' + self.id + '_filelist a.changefolder').each(function(i) {
            var title = $(this);
            var elemid = title.attr('id').replace(/^changefolder:/, '');
            if (elemid != moveid) {
                var displaytitle = title.find('.display-title').html();
                if (typeof displaytitle !== 'undefined') {
                    var link = $('<a>').prop('href', '#').html(get_string('moveto', 'artefact.file', displaytitle));
                    link.on('click keydown', function(e) {
                        if ((e.type === 'click' || e.keyCode === 32) && !e.isDefaultPrevented()) {
                            self.setfocus = 'changefolder:' + elemid;
                            self.move_to_folder(moveid, elemid);
                            self.move_list = null;
                            e.preventDefault();
                        }
                    });
                    ul.append($('<li><span class="icon icon-long-arrow-right left"></span>').append(link));
                }
            }
        });

        if (ul.children().length === 0) {
            wrapper.append($('<span>').html(get_string_ajax('nofolderformove', 'artefact.file')));
        }

        var cancellink = $('<a>').prop('href', '#').html(get_string('cancel'));
        cancellink.on('click keydown', function(e) {
            if ((e.type === 'click' || e.keyCode === 32) && !e.isDefaultPrevented()) {
                wrapper.remove();
                icon.trigger("focus");
                self.move_list = null;
                e.preventDefault();
            }
        });
        ul.append($('<li><span class="icon icon-times left"></span>').append(cancellink));
        wrapper.append(ul);

        self.move_list = wrapper;
        return wrapper;
    }

    this.make_icon_keyboard_accessible = function(icon) {
        var self = this;
        var id = icon.id.replace(/.+:/, '');
        $(icon).on('click keydown', function(e) {
            if (e.type === 'click' || e.keyCode === 32 || e.keyCode === 13) {
                var folderlist = self.create_move_list(icon, id);
                $(icon).closest('tr').find('.filename').append(folderlist);
                folderlist.find('a').first().trigger("focus");
                e.preventDefault();
            }
        });
    };

    this.make_droppable = function() {
        $(this).droppable({
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
        $('#' + this.id + '_move').val(dragid);
        $('#' + this.id + '_moveto').val(dropid);
        this.submitform();
        $('#' + this.id + '_move').val('');
        $('#' + this.id + '_moveto').val('');
    };

    this.drag = {};

    this.make_icon_draggable = function(elem) {
        $(elem).draggable({
          revert: "invalid",
          helper: function(e) {
            return $('<div>', { 'class': "icon-drag-current"}).css('height','1em');
          }
        });
    };

    this.select_init = function () {
        if ($('#' + self.id + '_open_upload_browse').length) {
            $('#' + self.id + '_open_upload_browse').on('click', function (e) {
                e.preventDefault();
                $('#' + self.id + '_upload_browse').removeClass('d-none');
                $('#' + self.id + '_open_upload_browse_container').addClass('d-none');
                return false;
            });
        }
        if ($('#' + self.id + '_close_upload_browse')) {
            $('#' + self.id + '_close_upload_browse').on('click', function (e) {
                e.preventDefault();
                $('#' + self.id + '_upload_browse').addClass('d-none');
                $('#' + self.id + '_open_upload_browse_container').removeClass('d-none');
                return false;
            });
        }
        $('#' + self.id + '_selectlist button.unselect').each(function () {
            self.clear_create_folder_messages();
            $(this).on('click', self.unselect);
        });
    };

    /**
     * A modal popup to show larger version of image.
     * The popup is hooked onto the name link in filebrowser
     */
    this.connect_link_modal = function () {
        if ($('#' + self.id + '_filelist').length === 0) {
            return;
        }
        var pagemodal = $('#page-modal');
        if (pagemodal.length === 0) {
            return;
        }

        var pagemodalbody = $('#page-modal .modal-body');

        var elem = $('#' + self.id + '_filelist .img-modal-preview');

        elem.each(function() {

            $(this).on('click', function(e) {

                e.preventDefault();
                self.clear_create_folder_messages();
                var previewimg = $('#previewimg');
                if (previewimg.length === 0) {
                    previewimg = $('<img id="previewimg" src="">');
                    pagemodalbody.append(previewimg);
                }
                var imgsrc = $(this).attr('href');
                imgsrc = updateUrlParameter(imgsrc, 'maxwidth', 400);
                imgsrc = updateUrlParameter(imgsrc, 'maxheight', 400);
                previewimg.attr('src',imgsrc);
                $('#page-modal').modal('show');

            });
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
                if ($(e.target).parent().hasClass('img-modal-preview')) {
                    return;
                }

                var id = this.getAttribute('data-id'),
                    j;

                // remove visual selection if this is for selecting 1 file
                if (self.config.selectone) {
                    for (j = 0; j < elem.length; j = j + 1) {
                        $(elem[j]).removeClass('active');
                    }
                }
                $(this).removeClass('warning').addClass('active');

                if (!self.selecteddata[id]) {
                     self.add_to_selected_list(id);
                }
                return false;
            });
        }
    };

    this.update_metadata_to_selected_list = function () {
        $('#' + self.id + '_filelist button.editable').each(function () {
            var id = this.name.replace(/.*_edit\[(\d+)\]$/, '$1');
            var row = $(this).closest('tr');
            var newtitle = row.find('a').first();
            var newdescription =  row.find('td.filedescription').first();
            if (self.selecteddata[id]) {
                var hiddeninput = $('#' + self.id + '_selected\\[' + id + '\\]');
                var legend2update = hiddeninput.closest('fieldset').find('legend h4 span.file-name');
                if (legend2update.length) {
                    legend2update.html(' - ' + newtitle.html());
                }
                var row2update = hiddeninput.closest('tr');
                var filetitle = row2update.find('a');
                if (filetitle.length) {
                    filetitle.html(newtitle.html());
                }
                var filedesc = row2update.find('td.filedescription');
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
        var tbody = $('#' + self.id + '_selectlist tbody').first(),
            rows = tbody.find('tr');

        if (self.config.selectone) {
            rows.each(function () {
                var hiddeninput = $(this).find('input.d-none');

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

        if ($('#' + self.id + '_select_' + id).length) {
            $('[id="file:' + id + '"]').addClass('active');
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
        // Check if the file to add was already in the selected list
        var existed = false;
        for (var i = 0; i < rows.length; i++) {
            var r = $(rows[i]);
            var rowbutton = r.find('button.button');
            var rowid = rowbutton.prop('name').replace(/.*_unselect\[(\d+)\]$/, '$1');
            if (rowid == id) {
                existed = true;
                var hiddeninput = r.find('input.d-none').first();
                if (!hiddeninput.length) {
                    hiddeninput = $('<input>', {'type':'hidden', 'class':'d-none', 'id':self.id+'_selected[' + id + ']', 'name':self.id+'_selected[' + id + ']', 'value':id});
                    rowbutton.closest('td').append(hiddeninput);
                }
                continue;
            }
        }
        if (!existed) {
            var remove = $('<button>', {'class': 'btn btn-link text-small button submit unselect',
                                            'type': 'submit', 'name': self.id+'_unselect[' + id + ']', 'title': get_string('remove')});
            remove.append(
                $('<span>', {'class': 'icon icon-times icon-lg text-danger left'}),
                $('<span>', { 'text': get_string('remove')})
            );
            remove.on('click', self.unselect);

            filelink = '';
            if (self.filedata[id].artefacttype == 'folder') {
                filelink = $('').text(self.filedata[id].title);
            }
            else {
                filelink = $('<a>', {'href':self.config.wwwroot + 'artefact/file/download.php?file=' + id}).text(self.filedata[id].title);
            }

            fileIconImg = '';
            if (self.filedata[id].icon.length) {
                fileIconImg = $('<img>', {'src':self.filedata[id].icon});
            }
            else {
                fileIconImg = $('<span>', {'class': 'icon icon-' + self.filedata[id].artefacttype + ' icon-lg'});
            }

            tbody.append($('<tr>', {'class': (highlight ? ' highlight-file' : '')}).append(
                $('<td>').append(fileIconImg),
                $('<td>').append(filelink),
                $('<td>', {'class':'text-right s'}).append(remove, $('<input>', {'type':'hidden', 'class':'d-none', 'id':self.id+'_selected[' + id + ']',
                                                                                            'name':self.id+'_selected[' + id + ']', 'value':id}))
            ));
        }
        // Display the list
        rows = tbody.find('tr');
        var rcount = 0;
        for (i = 0; i < rows.length; i++) {
            var r = $(rows[i]);
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
            $('#' + self.id + '_selectlist').removeClass('d-none');
            $('#' + self.id + '_empty_selectlist').addClass('d-none');
        }
        this.update_metadata_to_selected_list();
        // are we running inside tinymce imagebrowser plugin?
        if (window.imgbrowserconf_artefactid) {
            // propagate the click
            $('#filebrowserupdatetarget').trigger("click");
        }
        if (self.config.selectone && self.selectoneid !== id) {
            // Need to close modal on selection
            self.selectoneid = id;
            $('#' + self.id + '_upload_browse').modal('hide');
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
      var rows = $('#' + self.id + '_selectlist tbody').first().find('tr');
      var rcount = 0;
      for (var i = 0; i < rows.length; i++) {
          var r = $(rows[i]);
          var rowbutton = r.find('button.button').first();
          var rowid = rowbutton.prop('name').replace(/.*_unselect\[(\d+)\]$/, '$1');
          if (typeof(self.selecteddata[rowid]) != 'undefined') {
              r.addClass('active').removeClass('d-none');
              rcount ++;
          }
          else {
              var hiddeninput = r.find('input.d-none').first();
              if (hiddeninput.length) {
                  hiddeninput.remove();
              }
              r.addClass('d-none');
          }
      }
      if (rcount == 0) {
          $('#' + self.id + '_selectlist').addClass('d-none');
          $('#' + self.id + '_empty_selectlist').removeClass('d-none');
      }
      if ($('#' + self.id + '_select_' + id).length) {
          $('[id="file:' + id + '"]').addClass('active');
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
                $('#' + self.id + '_userfile_maxuploadsize').text('(' + get_string('maxuploadsize') + ' ' + data.maxuploadsize + ')');
            }
        }
        // Clear the create folder form
        if (data.foldercreated && $('#' + self.id + '_createfolder_name').length) {
            $('#' + self.id + '_createfolder_name').val('');
        }
        // Only update the file listing if the user hasn't changed folders yet
        if (data.newlist && (data.folder == self.folderid || data.changedfolder)) {
            self.filedata = data.newlist.data;
            if (self.config.edit || self.config.editmeta) {

                var editrow = $('#' + self.id + '_edit_row').detach();
                $('#' + self.id + '_edit_placeholder').append(editrow);
            }
            $('#' + self.id+'_filelist_container').html(data.newlist.html);

            // Focus management
            if (self.setfocus) {
                $( $('#' + self.setfocus)[0] ).trigger('focus');
                self.setfocus = null;
            }
            else if (data.foldercreated) {
                $( $('[id="changefolder:' + data.highlight + '"]')[0]).trigger('focus');
            }

            if (data.changedfolder && data.newpath) {
                $('#' + self.id+'_folder').val(self.folderid = data.folder);
                $('#' + self.id+'_foldername').val(self.foldername = data.newpath.foldername);
                $('#' + self.id+'_foldernav').html(data.newpath.html);
                if (data.changedowner && data.newtabs && data.newtabdata) {
                    self.tabdata = data.newtabdata;
                    $('#' + self.id+'_ownertabs').html(data.newtabs);
                    if (data.newsubtabs) {
                        $('#' + self.id + '_ownersubtabs').html(data.newsubtabs);
                        $('#' + self.id + '_ownersubtabs').removeClass('d-none');
                    }
                    else {
                        $('#' + self.id + '_ownersubtabs').addClass('d-none');
                    }
                    if ($('#' + self.id + '_upload_container').length) {
                        if (data.newtabdata.upload) {
                            $('#' + self.id + '_upload_container').removeClass('d-none');
                        }
                        else {
                            $('#' + self.id + '_upload_container').addClass('d-none');
                        }
                    }
                    self.config.editmeta = data.editmeta;
                }
                if (self.config.upload) {
                    if (data.disableedit && !$('#' + self.id + '_upload_container').hasClass('d-none')) {
                        $('#' + self.id + '_upload_container').addClass('d-none');
                        if ($('#createfolder').length) {
                            $('#createfolder').addClass('d-none');
                        }
                        $('#' + self.id + '_upload_disabled').removeClass('d-none');
                    }
                    else if (data.disableedit == false) {
                        if (!self.tabdata || self.tabdata.upload) {
                            $('#' + self.id + '_upload_container').removeClass('d-none');
                        }
                        if ($('#createfolder').length) {
                            $('#createfolder').removeClass('d-none');
                        }
                        $('#' + self.id + '_upload_disabled').addClass('d-none');
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
            if (data.tagblockhtml && $('#sb-tags').length) {
                $('#sb-tags').html(data.tagblockhtml);
            }
            $('#' + self.id + '_filelist').find('.control-buttons button').first().trigger('resize.bs.modal');
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
