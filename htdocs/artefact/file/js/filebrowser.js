/**
 * File browser
 * @source: http://gitorious.org/mahara/mahara
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

function FileBrowser(idprefix, folderid, config, globalconfig) {

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
        self.form = getFirstParentByTagAndClassName(self.id + '_filelist_container', 'form', 'pieform');
        if (!self.form) {
            alert('Filebrowser error 1');
        }
        if (self.config.select && typeof(self.form.submit) != 'function') {
            // logWarn('Filebrowser error 2'); // Rename your submit element to something other than "submit".
        }
        self.foldername = $(self.id + '_foldername').value;
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

    }

    this.submitform = function () {
        signal(self.form, 'onsubmit');
        self.form.submit();
    };

    this.upload_init = function () {
        if ($(self.id + '_notice')) {
            // If the file input is disabled immediately without this delay, later javascript changes
            // to the filebrowser are not rendered by Chrome when the filebrowser is inside a block
            // configuration form.
            callLater(0.0001, function() { setNodeAttribute(self.id + '_userfile', 'disabled', true); });
        }
        if (!$(self.id + '_upload')) {
            insertSiblingNodesAfter(self.id + '_uploadnumber', INPUT({
                'type': 'hidden',
                'name': self.id + '_upload',
                'id' : self.id + '_upload',
                'value':0
            }));
        }
        self.upload_connectbuttons();
    }

    this.upload_connectbuttons = function () {
        if ($(self.id + '_notice')) {
            connect(self.id + '_notice', 'onclick', function (e) {
                // error class is too general?
                forEach(getElementsByTagAndClassName('div', 'error', self.id + '_upload_messages'), removeElement);
                if (this.checked) {
                    $(self.id + '_userfile').disabled = false; // setNodeAttribute to false doesn't work here.
                } else {
                    setNodeAttribute(self.id + '_userfile', 'disabled', true);
                }
            });
        }
        connect(self.id + '_userfile', 'onchange', self.upload_submit);
    }

    this.upload_validate_dropzone = function () {
        if ($(self.id + '_notice') && !$(self.id + '_notice').checked) {
            return get_string('youmustagreetothecopyrightnotice');
        }
        return false;
    }

    this.upload_validate = function () {
        if ($(self.id + '_notice') && !$(self.id + '_notice').checked) {
            appendChildNodes(self.id+'_upload_messages', DIV({'class':'error'}, get_string('youmustagreetothecopyrightnotice')));
            return false;
        }
        return !isEmpty($(self.id + '_userfile').value);
    }

    this.upload_presubmit_dropzone = function (e) {
        // Display upload status
        self.nextupload++;
        var message = makeMessage(DIV(null,
            IMG({'src':get_themeurl('images/loading.gif')}), ' ',
            get_string('uploadingfiletofolder',e.name,self.foldername)
            ), 'info');
        setNodeAttribute(message, 'id', 'uploadstatusline' + self.nextupload);
        appendChildNodes(self.id + '_upload_messages', message);
        $(self.id+'_uploadnumber').value = self.nextupload;
        return true;
    }

    this.upload_presubmit = function (e) {
        // Display upload status
        if ($(self.id + '_userfile').files) {
            for (var i = 0; i < $(self.id + '_userfile').files.length; ++ i) {
                self.nextupload++;
                if (is_FF()) {
                    var localname = $(self.id + '_userfile').files[i].name;
                }
                else {
                    var localname = $(self.id + '_userfile').files[i].fileName;
                }
                var message = makeMessage(DIV(null,
                    IMG({'src':get_themeurl('images/loading.gif')}), ' ',
                    get_string('uploadingfiletofolder',localname,self.foldername)
                    ), 'ok');
                setNodeAttribute(message, 'id', 'uploadstatusline' + self.nextupload);
                appendChildNodes(self.id + '_upload_messages', message);
            }
        }
        $(self.id+'_uploadnumber').value = self.nextupload;
        return true;
    }

    this.upload_submit = function (e) {
        e.stop();
        if (!self.upload_validate()) {
            return false;
        }

        self.upload_presubmit();
        $(self.id + '_upload').value = 1;
        self.submitform();

        // $(self.id + '_userfile').value = ''; // Won't work in IE
        replaceChildNodes(self.id + '_userfile_container', INPUT({
            'type':'file',
            'class':'file',
            'id':self.id+'_userfile',
            'name':'userfile[]',
            'multiple':'',
            'size':40
        }));
        connect(self.id + '_userfile', 'onchange', self.upload_submit);
        $(self.id + '_upload').value = 0;
        return false;
    }

    this.fileexists = function (filename, id) {
        for (var i in self.filedata) {
            if (self.filedata[i].title == filename && (!id || i != id)) {
                return true;
            }
        }
        return false;
    }

    this.createfolder_submit = function (e) {
        var message;
        var name = $(self.id + '_createfolder_name');
        if (!name) {
            message = get_string('foldernamerequired');
        }
        else {
            name = name.value;
            if (name == '') {
                message = get_string('foldernamerequired');
            }
            else if (name.length > 1024) {
                message = get_string('nametoolong');
            }
            else if (self.fileexists(name)) {
                message = get_string('filewithnameexists', name);
            }
        }
        if (message) {
            e.stop();
            replaceChildNodes(self.id + '_createfolder_messages', makeMessage(message, 'error'));
            return false;
        }
        progressbarUpdate('folder');
    }

    this.edit_submit = function (e) {
        var message;
        var name = $(self.id + '_edit_title');
        if (!name) {
            message = get_string('namefieldisrequired');
        }
        else {
            name = name.value;
            if (name == '') {
                message = get_string('namefieldisrequired');
            }
            else if (name.length > 1024) {
                message = get_string('nametoolong');
            }
            else if (self.fileexists(name, this.name.replace(/.*_update\[(\d+)\]$/, '$1'))) {
                message = get_string('filewithnameexists', name);
            }
        }
        if (message) {
            e.stop();
            replaceChildNodes(self.id + '_edit_messages', makeMessage(message, 'error'));
            return false;
        }
    }

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
        if (data.returnCode == '0') {
            // pass the artefacttype to update progress bar
            progressbarUpdate(data.artefacttype, data.deleted);
        }
        var newmessage = makeMessage(DIV(null,' ', data.message), infoclass);
        setNodeAttribute(newmessage, 'id', 'uploadstatusline' + data.uploadnumber);
        if (data.uploadnumber) {
            removeElement($('uploadstatusline'+data.uploadnumber));
        }
        appendChildNodes(self.id + '_upload_messages', newmessage);
    }

    this.hide_edit_form = function () {

        var editrow = $(self.id + '_edit_row');
        if (!hasElementClass(editrow, 'hidden')) {
            if ((typeof formchangemanager !== 'undefined') && !formchangemanager.confirmLeavingForm()) {
                return false;
            }
            addElementClass(editrow, 'hidden');
            // Reconnect the old edit button to open the form
            if (editrow.previousSibling) {
                forEach(getElementsByTagAndClassName('input', null, editrow.previousSibling), function (elem) {
                    var name = getNodeAttribute(elem, 'name').match(new RegExp('^' + self.id + "_([a-z]+)\\[(\\d+)\\]$"));
                    if (name && name[1] && name[1] == 'edit') {
                        disconnectAll(elem);
                        connect(elem, 'onclick', self.edit_form);
                    }
                });
            }
        }
        return true;
    }

    this.edit_form = function (e) {
        e.stop();

        // In IE, this.value is set to the button text
        var id = getNodeAttribute(this, 'name').replace(/.*_edit\[(\d+)\]$/, '$1');
        if (!self.hide_edit_form()) {
            return;
        }
        $(self.id + '_edit_heading').innerHTML = self.filedata[id].artefacttype == 'folder' ? get_string('editfolder') : get_string('editfile');
        var descriptionrow = getFirstParentByTagAndClassName($(self.id + '_edit_description'), 'tr');
        if (self.filedata[id].artefacttype == 'profileicon') {
            addElementClass(descriptionrow, 'hidden');
        }
        else {
            removeElementClass(descriptionrow, 'hidden');
        }
        $(self.id + '_edit_title').value = self.filedata[id].title;
        $(self.id + '_edit_description').value = self.filedata[id].description == null ? '' : self.filedata[id].description;
        if ($(self.id + '_edit_license')) {
            if (self.filedata[id].license == null) {
                $(self.id + '_edit_license').value = ''
            }
            else {
                $(self.id + '_edit_license').value = self.filedata[id].license;
                if ($(self.id + '_edit_license').value != self.filedata[id].license) {
                    // Doesn't exist in the select box, add it!
                    var new_option = jQuery('<option/>');
                    new_option.attr('value', self.filedata[id].license);
                    new_option.text(self.filedata[id].license);
                    jQuery($(self.id + '_edit_license')).append(new_option);
                    $(self.id + '_edit_license').value = self.filedata[id].license;
                }
            }
            $(self.id + '_edit_licensor').value = self.filedata[id].licensor == null ? '' : self.filedata[id].licensor;
            $(self.id + '_edit_licensorurl').value = self.filedata[id].licensorurl == null ? '' : self.filedata[id].licensorurl;
            pieform_select_other($(self.id + '_edit_license'));
        }
        $(self.id + '_edit_allowcomments').checked = self.filedata[id].allowcomments;
        $(self.id + '_edit_tags').value = self.filedata[id].tags.join(', ');
        replaceChildNodes($(self.id + '_edit_messages'));
        forEach(getElementsByTagAndClassName('input', 'permission', self.id + '_edit_row'), function (elem) {
            var perm = getNodeAttribute(elem, 'name').split(':');
            if (self.filedata[id].permissions[perm[1]] && self.filedata[id].permissions[perm[1]][perm[2]] == 1) {
                elem.checked = true;
            }
            else {
                elem.checked = false;
            }
        });
        // $(self.id + '_edit_artefact').value = id; // Changes button text in IE
        setNodeAttribute(self.id + '_edit_artefact', 'name', self.id + '_update[' + id + ']');
        var tags_control_elem = augment_tags_control(self.id + '_edit_tags', true);
        swapDOM($(self.id + '_edit_tags').parentNode.firstChild, tags_control_elem);
        var edit_row = removeElement(self.id + '_edit_row');
        var this_row = getFirstParentByTagAndClassName(this, 'tr');
        insertSiblingNodesAfter(this_row, edit_row);
        removeElementClass(edit_row, 'hidden');

        // Make the edit button close the form again
        disconnectAll(this);
        connect(this, 'onclick', function (e) {
            e.stop();
            // Check if there are some dirty changes before close the edit form
            if ((typeof formchangemanager !== 'undefined') && formchangemanager.confirmLeavingForm()) {
                addElementClass(self.id + '_edit_row', 'hidden');
                disconnectAll(this);
                connect(this, 'onclick', self.edit_form);
            }
            return false;
        });

        // Recalculate the width of config block
        if ($('artefactchooser-body')) {
            var width = getElementDimensions(getFirstParentByTagAndClassName($('artefactchooser-body'), 'td', null)).w;
            updateBlockConfigWidth(getFirstParentByTagAndClassName(self.form, 'div', 'blockinstance'), width);
        }

        return false;
    }

    this.edit_init = function () { augment_tags_control(self.id + '_edit_tags'); }

    this.browse_init = function () {
        if (self.config.edit || self.config.editmeta) {
            forEach(getElementsByTagAndClassName('input', null, self.id + '_filelist'), function (elem) {
                var name = getNodeAttribute(elem, 'name').match(new RegExp('^' + self.id + "_([a-z]+)\\[(\\d+)\\]$"));
                if (name && name[1]) {
                    if (name[1] == 'edit') {
                        connect(elem, 'onclick', self.edit_form);
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
                                    warn += get_string('foldercontainsprofileicons', self.filedata[id].profileiconcount) + ' ';
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
                                warn += get_string('fileattachedtoportfolioitems', self.filedata[id].attachcount) + ' ';
                            }
                            if (self.filedata[id].viewcount > 0) {
                                warn += get_string('fileappearsinviews') + ' ';
                            }
                            if (self.filedata[id].skincount > 0) {
                                warn += get_string('fileappearsinskins') + ' ';
                            }
                            warn += get_string('confirmdeletefile');
                        }

                        if (warn != '') {
                            connect(elem, 'onclick', function (e) {
                                if (!confirm(warn)) {
                                    e.stop();
                                    return false;
                                }
                            });
                        }
                    }
                }
            });
            connect(self.id + '_edit_cancel', 'onclick', function (e) {
                e.stop();
                if (typeof formchangemanager !== 'undefined') {
                    var form = jQuery(this).closest('form')[0];
                    formchangemanager.setFormState(form, FORM_INIT);
                }
                self.hide_edit_form();
                return false;
            });
            connect(self.id + '_edit_artefact', 'onclick', self.edit_submit);

            if (self.config.edit) {
                // IE doesn't like it when Mochikit has droppables registered without elements attached.
                forEach(Draggables.drags, function (drag) { drag.destroy(); });
                forEach(Droppables.drops, function (drop) { drop.destroy(); });

                forEach(getElementsByTagAndClassName('div', 'icon-drag', self.id + '_filelist'), function (elem) {
                    self.make_icon_draggable(elem);
                    self.make_icon_keyboard_accessible(elem);
                });
                forEach(getElementsByTagAndClassName('tr', 'folder', self.id + '_filelist'), self.make_row_droppable);
                forEach(getElementsByTagAndClassName('a', 'changefolder', self.id + '_foldernav'), self.make_folderlink_droppable);
            }
        }
        forEach(getElementsByTagAndClassName('a', 'changeowner', self.id + '_upload_browse'), function (elem) {
            connect(elem, 'onclick', function (e) {
                var href = getNodeAttribute(this, 'href');
                var params = parseQueryString(href.substring(href.indexOf('?')+1));
                $(self.id + '_changeowner').value = 1;
                $(self.id + '_owner').value = params.owner;
                if (params.ownerid) {
                    $(self.id + '_ownerid').value = params.ownerid;
                }
                else {
                    $(self.id + '_ownerid').value = '';
                }
                if (params.folder) {
                    $(self.id + '_changefolder').value = params.folder;
                }
                self.submitform();
                $(self.id + '_changeowner').value = $(self.id + '_changefolder').value = '';
                e.stop();
                return false;
            });
        });
        forEach(getElementsByTagAndClassName('a', 'changefolder', self.id + '_upload_browse'), function (elem) {
            connect(elem, 'onclick', function (e) {
                if (self.config.edit) {
                    if ((typeof formchangemanager !== 'undefined') && !formchangemanager.confirmLeavingForm()) {
                        e.stop();
                        return false;
                    }
                }
                var href = getNodeAttribute(this, 'href');
                var params = parseQueryString(href.substring(href.indexOf('?')+1));
                $(self.id + '_changefolder').value = params.folder;
                if ($(self.id + '_owner')) {
                    $(self.id + '_owner').value = params.owner ? params.owner : '';
                    $(self.id + '_ownerid').value = params.ownerid ? params.ownerid : '';
                }
                self.submitform();
                $(self.id + '_changefolder').value = '';
                e.stop();
                return false;
            });
        });
        if ($(self.id + '_createfolder') && !self.createfolder_is_connected) {
            connect($(self.id + '_createfolder'), 'onclick', self.createfolder_submit);
            self.createfolder_is_connected = true;
        }
        if (self.config.select) {
            self.connect_select_buttons();
        }
    }

    this.create_move_list = function(icon, moveid) {
        var self = this;

        if (self.move_list) {
            self.move_list.remove();
        }

        var ul = $j('<ul>').addClass('file-move-list');

        $j('#' + self.id + '_filelist a.changefolder').each(function(i) {
            var title = $j(this);
            var elemid = title.attr('href').replace(/.+folder=/, '');
            if (elemid != moveid) {
                var displaytitle = title.find('.display-title').html();
                var link = $j('<a>').attr('href', '#').html(get_string('moveto', displaytitle));
                link.on('mousedown keydown', function(e) {
                    if (e.type == 'mousedown' && e.buttons == 0) {
                        // Stops the link being activated when it shouldn't (eg. when setting focus to the list)
                        return false;
                    }
                    else if (e.type == 'mousedown' || e.keyCode == 32 || e.keyCode == 13) {
                        self.setfocus = 'changefolder:' + elemid;
                        self.move_to_folder(moveid, elemid);
                        self.move_list = null;
                        return false;
                    }
                });
                ul.append($j('<li>').append(link));
            }
        });

        var cancellink = $j('<a>').attr('href', '#').html(get_string('cancel'));
        cancellink.on('click keydown', function(e) {
            if (e.type == 'click' && e.buttons == 0) {
                return false;
            }
            else if (e.type == 'click' || e.keyCode == 32 || e.keyCode == 13) {
                ul.remove();
                icon.focus();
                self.move_list = null;
            }
        });
        ul.append($j('<li>').append(cancellink));

        self.move_list = ul;
        return ul;
    }

    this.make_icon_keyboard_accessible = function(icon) {
        var self = this;
        var id = icon.id.replace(/.+:/, '');
        $j(icon).on('mousedown keydown', function(e) {
            if (e.type == 'mousedown' || e.keyCode == 32 || e.keyCode == 13) {
                var folderlist = self.create_move_list(icon, id);
                $j(icon).closest('tr').find('.filename').append(folderlist);
                folderlist.find('a').first().focus();
            }
        });
    };

    this.make_row_droppable = function(row) {
        new Droppable(row, {
            accept: ['icon-drag-current'],
            hoverclass: 'folderhover',
            ondrop: function (dragged, dropped) {
                var dragid = dragged.id.replace(/^.*drag:(\d+)$/, '$1');
                var dropid = dropped.id.replace(/^file:(\d+)$/, '$1');
                if (dragid == dropid) {
                    return;
                }
                self.move_to_folder(dragid, dropid);
            }
        });

        // Droppable() calls makePositioned() on the row, which causes the
        // the border disappear from its child elements.  Set it back to
        // 'static' and see if this causes any problems...
        // setStyle(row, {'position': 'static'});
        undoPositioned(row);
    };

    this.make_folderlink_droppable = function(link) {
        new Droppable(link, {
            accept: ['icon-drag-current'],
            hoverclass: 'folderhover',
            ondrop: function (dragged, dropped) {
                var dragid = dragged.id.replace(/^.*drag:(\d+)$/, '$1');
                var dropid = dropped.href.replace(/^.*\?folder=(\d+)$/, '$1');
                if (dragid == dropid) {
                    return;
                }
                self.move_to_folder(dragid, dropid);
            }
        });
    };

    this.move_to_folder = function(dragid, dropid) {
        $(this.id + '_move').value = dragid;
        $(this.id + '_moveto').value = dropid;
        this.submitform();
        $(this.id + '_move').value = '';
        $(this.id + '_moveto').value = '';
    }

    this.drag = {};

    this.make_icon_draggable = function(elem) {
        new Draggable(elem, {
            starteffect: function(elem) {
                if (!self.drag.clone) {
                    // Works better in IE if we just drag an empty div around without the child image.
                    // Otherwise the element seems to get dropped during the drag and we end up with a
                    // crossed-circle cursor.

                    self.drag.clone = DIV({'id':elem.id, 'class':'icon-drag'});
                    setNodeAttribute(elem, 'id', 'copy-of-' + elem.id);

                    removeElementClass(elem, 'icon-drag');
                    addElementClass(elem, 'icon-drag-current');

                    insertSiblingNodesAfter(elem, self.drag.clone);

                    var child = getFirstElementByTagAndClassName('img', null, elem);
                    var dimensions = elementDimensions(child);

                    removeElement(child);
                    appendChildNodes(self.drag.clone, child);

                    setStyle(elem, {
                        'position': 'absolute',
                        'border': '2px solid #aaa'
                    });
                    setElementDimensions(elem, dimensions);
                }
            },
            revert: function (element) {
                if (self.drag.clone) {
                    removeElement(element);
                    forEach(Draggables.drags, function(drag) {
                        if (drag.element == element) {
                            drag.destroy();
                        }
                    });
                    element = null;
                    self.make_icon_draggable(self.drag.clone);
                    self.drag = {};
                }
            }
        });
    };

    this.select_init = function () {
        if ($(self.id + '_open_upload_browse')) {
            connect(self.id + '_open_upload_browse', 'onclick', function (e) {
                e.stop();
                removeElementClass(self.id + '_upload_browse', 'hidden');
                addElementClass(self.id + '_open_upload_browse_container', 'hidden');
                return false;
            });
        }
        if ($(self.id + '_close_upload_browse')) {
            connect(self.id + '_close_upload_browse', 'onclick', function (e) {
                e.stop();
                addElementClass(self.id + '_upload_browse', 'hidden');
                removeElementClass(self.id + '_open_upload_browse_container', 'hidden');
                return false;
            });
        }
        forEach(getElementsByTagAndClassName('input', 'unselect', self.id + '_selectlist'), function (elem) {
            connect(elem, 'onclick', self.unselect);
        });
        self.connect_select_buttons();
    }

    this.connect_select_buttons = function () {
        forEach(getElementsByTagAndClassName('input', 'select', self.id + '_filelist'), function (elem) {
            var id = elem.name.replace(/.*_select\[(\d+)\]$/, '$1');
            if (self.selecteddata[id]) {
                addElementClass(elem, 'hidden');
            }
            connect(elem, 'onclick', function (e) {
                e.stop();
                var id = this.name.replace(/.*_select\[(\d+)\]$/, '$1');
                if (!self.selecteddata[id]) {
                    self.add_to_selected_list(id);
                }
                return false;
            });
        });
    }

    this.update_metadata_to_selected_list = function () {
        forEach(getElementsByTagAndClassName('input', 'btn_edit', self.id + '_filelist'), function (elem) {
            var id = elem.name.replace(/.*_edit\[(\d+)\]$/, '$1');
            var row = getFirstParentByTagAndClassName(elem, 'tr');
            var newtitle = getFirstElementByTagAndClassName('a', null, row);
            var newdescription = getFirstElementByTagAndClassName('td', 'filedescription', row);
            if (self.selecteddata[id]) {
                var hiddeninput = $(self.id + '_selected[' + id + ']');
                var row2update = getFirstParentByTagAndClassName(hiddeninput, 'tr');
                var filetitle = getFirstElementByTagAndClassName('a', null, row2update);
                if (filetitle) {
                    filetitle.innerHTML = newtitle.innerHTML;
                }
                var filedesc = getFirstElementByTagAndClassName('td', 'filedescription', row2update);
                if (filedesc) {
                    filedesc.innerHTML = newdescription.innerHTML;
                }
            }
        });
    }

    this.add_to_selected_list = function (id, highlight) {
        if (!self.filedata[id]) {
            return;
        }
        var tbody = getFirstElementByTagAndClassName('tbody', null, self.id + '_selectlist');
        var rows = getElementsByTagAndClassName('tr', null, tbody);
        if (self.config.selectone) {
            forEach(rows, function (row) {
                var hiddeninput = getFirstElementByTagAndClassName('input', 'hidden', row);
                if (hiddeninput) {
                    var rowid = hiddeninput.name.replace(/.*_selected\[(\d+)\]$/, '$1');
                    removeElement(hiddeninput);
                    var selectbutton = $(self.id + '_select_' + rowid);
                    if (selectbutton) {
                        removeElementClass(selectbutton, 'hidden');
                    }
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
        if ($(self.id + '_select_' + id)) {
            addElementClass(self.id + '_select_' + id, 'hidden');
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
        for (i = 0; i < rows.length; i++) {
            var r = rows[i];
            var rowbutton = getFirstElementByTagAndClassName('input', 'button', r);
            var rowid = rowbutton.name.replace(/.*_unselect\[(\d+)\]$/, '$1');
            if (rowid == id) {
                existed = true;
                var hiddeninput = getFirstElementByTagAndClassName('input', 'hidden', r);
                if (!hiddeninput) {
                    hiddeninput = INPUT({'type':'hidden', 'class':'hidden', 'id':self.id+'_selected[' + id + ']', 'name':self.id+'_selected[' + id + ']', 'value':id});
                    appendChildNodes(getFirstParentByTagAndClassName(rowbutton, 'td'), hiddeninput);
                }
                continue;
            }
        };
        if (!existed) {
            var remove = INPUT({'type': 'submit', 'class':'button submit unselect', 'name':self.id+'_unselect[' + id + ']', 'value':get_string('remove')});
            connect(remove, 'onclick', self.unselect);
            filelink = ''
            if (self.filedata[id].artefacttype == 'folder') {
                filelink = self.filedata[id].title;
            }
            else {
                filelink = A({'href':self.config.wwwroot + 'artefact/file/download.php?file=' + id, 'target':'_blank'}, self.filedata[id].title);
            }
            appendChildNodes(tbody, TR({'class': (highlight ? ' highlight-file' : '')},
                   TD(null, IMG({'src':self.filedata[id].icon})),
                   TD(null, filelink),
                   TD({'class':'filedescription'}, self.filedata[id].description),
                   TD({'class':'right s'}, remove, INPUT({'type':'hidden', 'class':'hidden', 'id':self.id+'_selected[' + id + ']', 'name':self.id+'_selected[' + id + ']', 'value':id}))
                  ));
        }
        // Display the list
        var rows = getElementsByTagAndClassName('tr', null, tbody);
        var rcount = 0;
        for (i = 0; i < rows.length; i++) {
            var r = rows[i];
            var rowbutton = getFirstElementByTagAndClassName('input', 'button', r);
            var rowid = rowbutton.name.replace(/.*_unselect\[(\d+)\]$/, '$1');
            if (typeof(self.selecteddata[rowid]) != 'undefined') {
                setNodeAttribute(r, 'class', 'r' + rcount % 2);
                removeElementClass(r, 'hidden');
                rcount ++;
            }
            else {
                addElementClass(r, 'hidden');
            }
        };
        if (rcount == 1) {
            removeElementClass(self.id + '_selectlist', 'hidden');
            addElementClass(self.id + '_empty_selectlist', 'hidden');
        }
        this.update_metadata_to_selected_list();
    }

    this.unselect = function (e) {
        e.stop();
        var id = this.name.replace(/.*_unselect\[(\d+)\]$/, '$1');
        delete self.selecteddata[id];
        // Display the list
        var rows = getElementsByTagAndClassName('tr', null, getFirstElementByTagAndClassName('tbody', null, self.id + '_selectlist'));
        var rcount = 0;
        for (i = 0; i < rows.length; i++) {
            var r = rows[i];
            var rowbutton = getFirstElementByTagAndClassName('input', 'button', r);
            var rowid = rowbutton.name.replace(/.*_unselect\[(\d+)\]$/, '$1');
            if (typeof(self.selecteddata[rowid]) != 'undefined') {
                setNodeAttribute(r, 'class', 'r' + rcount % 2);
                removeElementClass(r, 'hidden');
                rcount ++;
            }
            else {
                var hiddeninput = getFirstElementByTagAndClassName('input', 'hidden', r);
                if (hiddeninput) {
                    removeElement(hiddeninput);
                }
                addElementClass(r, 'hidden');
            }
        };
        if (rcount == 0) {
                addElementClass(self.id + '_selectlist', 'hidden');
                removeElementClass(self.id + '_empty_selectlist', 'hidden');
        }
        if ($(self.id + '_select_' + id)) {
            removeElementClass(self.id + '_select_' + id, 'hidden');
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
                replaceChildNodes(self.id + '_userfile_maxuploadsize', '(' + get_string('maxuploadsize') + ' ' + data.maxuploadsize + ')');
            }
        }
        // Clear the create folder form
        if (data.foldercreated && $(self.id + '_createfolder_name')) {
            $(self.id + '_createfolder_name').value = '';
        }
        // Only update the file listing if the user hasn't changed folders yet
        if (data.newlist && (data.folder == self.folderid || data.changedfolder)) {
            self.filedata = data.newlist.data;
            if (self.config.edit || self.config.editmeta) {
                replaceChildNodes(self.id + '_edit_placeholder', removeElement(self.id + '_edit_row'));
            }
            $(self.id+'_filelist_container').innerHTML = data.newlist.html;
            if (self.setfocus) {
                $(self.setfocus).focus();
                self.setfocus = null;
            }
            if (data.changedfolder && data.newpath) {
                $(self.id+'_folder').value = self.folderid = data.folder;
                $(self.id+'_foldername').value = self.foldername = data.newpath.foldername;
                $(self.id+'_foldernav').innerHTML = data.newpath.html;
                if (data.changedowner && data.newtabs && data.newtabdata) {
                    self.tabdata = data.newtabdata;
                    $(self.id+'_ownertabs').innerHTML = data.newtabs;
                    if (data.newsubtabs) {
                        $(self.id + '_ownersubtabs').innerHTML = data.newsubtabs;
                        removeElementClass(self.id + '_ownersubtabs', 'hidden')
                    }
                    else {
                        addElementClass(self.id + '_ownersubtabs', 'hidden');
                    }
                    if ($(self.id + '_upload_container')) {
                        if (data.newtabdata.upload) {
                            removeElementClass(self.id + '_upload_container', 'hidden');
                        }
                        else {
                            addElementClass(self.id + '_upload_container', 'hidden');
                        }
                    }
                    self.config.editmeta = data.editmeta;
                }
                if (self.config.upload) {
                    if (data.disableedit && !hasElementClass(self.id + '_upload_container', 'hidden')) {
                        addElementClass(self.id + '_upload_container', 'hidden');
                        if ($('createfolder')) {
                            addElementClass('createfolder', 'hidden');
                        }
                        removeElementClass(self.id + '_upload_disabled', 'hidden');
                    }
                    else if (hasElementClass(self.id + '_upload_container', 'hidden') && !data.disableedit) {
                        if (!self.tabdata || self.tabdata.upload) {
                            removeElementClass(self.id + '_upload_container', 'hidden');
                        }
                        if ($('createfolder')) {
                            removeElementClass('createfolder', 'hidden');
                        }
                        addElementClass(self.id + '_upload_disabled', 'hidden');
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
            if (data.tagblockhtml && $('sb-tags')) {
                $('sb-tags').innerHTML = data.tagblockhtml;
            }
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
        // Recalculate the width of config block
        if ($('artefactchooser-body')) {
            var width = getElementDimensions(getFirstParentByTagAndClassName($('artefactchooser-body'), 'table', 'maharatable')).w;
            updateBlockConfigWidth(getFirstParentByTagAndClassName(self.form, 'div', 'blockinstance'), width);
        }
    }

}

// This variable = true if the users has updated the field 'Tags' by clicking the tag.
var tags_changed = false;
