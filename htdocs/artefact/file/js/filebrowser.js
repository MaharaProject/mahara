/**
 * File browser
 * @source: http://gitorious.org/mahara/mahara
 *
 * @licstart
 * Copyright (C) 2006-2010  Catalyst IT Ltd
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
 * may distribute non-source (e.g., minimized or compacted) forms of
 * that code without the copy of the GNU GPL normally required by
 * section 4, provided you include this license notice and a URL
 * through which recipients can access the Corresponding Source.
 * @licend
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
            setNodeAttribute(self.id + '_userfile', 'disabled', true);
        }
        if (!$(self.id + '_upload')) {
            insertSiblingNodesAfter(self.id + '_uploadnumber', INPUT({
                'type': 'hidden',
                'name': self.id + '_upload',
                'id' : self.id + '_upload',
                'value':0
            }));
        }
        if (is_FF() && self.config.select && !$(self.id + '_uploadsubmit') && $(self.id + '_uploadsubmit_container')) {
            var submitbutton = INPUT({
                'class': 'submit',
                'type': 'submit',
                'name': self.id + '_uploadsubmit',
                'id': self.id + '_uploadsubmit',
                'value': get_string('upload')
            });
            appendChildNodes(self.id + '_uploadsubmit_container', submitbutton);
            self.config.submitbutton = true;
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
        if (self.config.submitbutton && $(self.id + '_uploadsubmit')) {
            connect(self.id + '_uploadsubmit', 'onclick', self.upload_submit);
        }
        else {
            connect(self.id + '_userfile', 'onchange', self.upload_submit);
            if ($(self.id + '_uploadsubmit')) {
                removeElement(self.id + '_uploadsubmit');
            }
        }
    }

    this.upload_validate = function () {
        if ($(self.id + '_notice') && !$(self.id + '_notice').checked) {
            appendChildNodes(self.id+'_upload_messages', DIV({'class':'error'}, get_string('youmustagreetothecopyrightnotice')));
            return false;
        }
        return !isEmpty($(self.id + '_userfile').value);
    }

    this.upload_presubmit = function (e) {
        // Display upload status
        self.nextupload++;
        var localname = basename($(self.id + '_userfile').value);
        var message = makeMessage(DIV(null,
            IMG({'src':get_themeurl('images/loading.gif')}), ' ',
            get_string('uploadingfiletofolder',localname,self.foldername)
        ), 'info');
        setNodeAttribute(message, 'id', 'uploadstatusline' + self.nextupload);
        appendChildNodes(self.id + '_upload_messages', message);
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
            'name':'userfile',
            'size':40
        }));
        if (!$(self.id + '_uploadsubmit')) {
            connect(self.id + '_userfile', 'onchange', self.upload_submit);
        }
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
        if (data.problem) {
            var image = 'images/icon_problem.gif';
        }
        else if (data.error) {
            var image = 'images/failure.gif';
        }
        else {
            var image = 'images/success.gif';
        }

        quotaUpdate(data.quotaused, data.quota);
        var newmessage = makeMessage(DIV(null,IMG({'src':get_themeurl(image)}), ' ', data.message), 'info');
        setNodeAttribute(newmessage, 'id', 'uploadstatusline' + data.uploadnumber);
        if (data.uploadnumber) {
            removeElement($('uploadstatusline'+data.uploadnumber));
        }
        appendChildNodes(self.id + '_upload_messages', newmessage);
    }

    this.hide_edit_form = function () {
        var editrow = $(self.id + '_edit_row');
        if (!hasElementClass(editrow, 'hidden')) {
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
    }

    this.edit_form = function (e) {
        e.stop();
        // In IE, this.value is set to the button text
        var id = getNodeAttribute(this, 'name').replace(/.*_edit\[(\d+)\]$/, '$1');
        self.hide_edit_form();
        $(self.id + '_edit_heading').innerHTML = self.filedata[id].artefacttype == 'folder' ? get_string('editfolder') : get_string('editfile');
        $(self.id + '_edit_title').value = self.filedata[id].title;
        $(self.id + '_edit_description').value = self.filedata[id].description == null ? '' : self.filedata[id].description;
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
            addElementClass(self.id + '_edit_row', 'hidden');
            disconnectAll(this);
            connect(this, 'onclick', self.edit_form);
            return false;
        });

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
                                warn += get_string('confirmdeletefolderandcontents');
                            }
                            else if (warn != '') {
                                warn += get_string('confirmdeletefolder');
                            }
                        }
                        else {
                            if (self.filedata[id].attachcount > 0) {
                                warn += get_string('fileattached', self.filedata[id].attachcount) + ' ';
                            }
                            if (self.filedata[id].viewcount > 0) {
                                warn += get_string('fileappearsinviews') + ' ';
                            }
                            if (warn != '') {
                                warn += get_string('confirmdeletefile');
                            }
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
                });
                forEach(getElementsByTagAndClassName('tr', 'folder', self.id + '_filelist'), self.make_row_droppable);
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
        if ($(self.id + '_createfolder')) {
            connect($(self.id + '_createfolder'), 'onclick', self.createfolder_submit);
        }
        if (self.config.select) {
            self.connect_select_buttons();
        }
    }

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
                $(self.id + '_move').value = dragid;
                $(self.id + '_moveto').value = dropid;
                self.submitform();
                $(self.id + '_move').value = '';
                $(self.id + '_moveto').value = '';
            }
        });

        // Droppable() calls makePositioned() on the row, which causes the
        // the border disappear from its child elements.  Set it back to
        // 'static' and see if this causes any problems...
        // setStyle(row, {'position': 'static'});
        undoPositioned(row);
    };

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
        // Draggable sets position = 'relative', but we set it back
        // here because with position = 'relative' in IE6 the rows
        // stay put instead of moving down when the create/upload
        // forms are opened on the page.
        elem.style.position = 'static';
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

    this.add_to_selected_list = function (id, highlight) {
        if (!self.filedata[id]) {
            return;
        }
        var tbody = getFirstElementByTagAndClassName('tbody', null, self.id + '_selectlist');
        var rows = getElementsByTagAndClassName('tr', null, tbody);
        if (rows.length == 0) {
            removeElementClass(self.id + '_selectlist', 'hidden');
            addElementClass(self.id + '_empty_selectlist', 'hidden');
        }
        else if (highlight) {
            forEach(rows, function (r) { removeElementClass(r, 'highlight-file'); });
        }
        if (self.config.selectone) {
            forEach(rows, function (row) {
                var selectbutton = $(self.id + '_select_' + getFirstElementByTagAndClassName(null, 'unselect', row).name.replace(/.*_unselect\[(\d+)\]$/, '$1'));
                if (selectbutton) {
                    removeElementClass(selectbutton, 'hidden');
                }
                removeElement(row);
            });
            rows = [];
            self.selecteddata = {};
        }
        var remove = INPUT({'type': 'submit', 'class':'button small unselect', 'name':self.id+'_unselect[' + id + ']', 'value':get_string('remove')});
        connect(remove, 'onclick', self.unselect);
        appendChildNodes(tbody, TR({'class': 'r' + rows.length % 2 + (highlight ? ' highlight-file' : '')},
                                   TD(null, IMG({'src':self.filedata[id].icon})),
                                   TD(null, A({'href':self.config.wwwroot + 'artefact/file/download.php?file=' + id, 'target':'_blank'}, self.filedata[id].title)),
                                   TD(null, self.filedata[id].description),
                                   TD(null, remove, INPUT({'type':'hidden', 'name':self.id+'_selected[' + id + ']', 'value':id}))
                                  ));
        self.selecteddata[id] = {
            'id': id,
            'artefacttype': self.filedata[id].artefacttype,
            'title': self.filedata[id].title,
            'description': self.filedata[id].description
        };
        if ($(self.id + '_select_' + id)) {
            addElementClass(self.id + '_select_' + id, 'hidden');
        }
        if (self.filedata[id].tags) {
            self.selecteddata[id].tags = self.filedata[id].tags;
        }
    }

    this.unselect = function (e) {
        e.stop();
        var id = this.name.replace(/.*_unselect\[(\d+)\]$/, '$1');
        delete self.selecteddata[id];
        removeElement(getFirstParentByTagAndClassName(this, 'tr'));
        var rows = getElementsByTagAndClassName('tr', null, getFirstElementByTagAndClassName('tbody', null, self.id + '_selectlist'));
        if (rows.length == 0) {
            addElementClass(self.id + '_selectlist', 'hidden');
            removeElementClass(self.id + '_empty_selectlist', 'hidden');
        }
        else {
            // Fix row classes
            for (var r = 0; r < rows.length; r++) {
                setNodeAttribute(rows[r], 'class', 'r' + r % 2);
            }
        }
        if ($(self.id + '_select_' + id)) {
            removeElementClass(self.id + '_select_' + id, 'hidden');
        }
        return false;
    }

    this.callback = function (form, data) {
        self.form = form; // ????
        if (data.uploaded || data.error || data.deleted) {
            self.callback_feedback(data);  // add/update message
            if (data.maxuploadsize) {
                // keep max upload size up to date
                replaceChildNodes(self.id + '_userfile_maxuploadsize', '(' + get_string('maxuploadsize') + ' ' + data.maxuploadsize + ')');
            }
        }
        // Only update the file listing if the user hasn't changed folders yet
        if (data.newlist && (data.folder == self.folderid || data.changedfolder)) {
            self.filedata = data.newlist.data;
            if (self.config.edit || self.config.editmeta) {
                replaceChildNodes(self.id + '_edit_placeholder', removeElement(self.id + '_edit_row'));
            }
            $(self.id+'_filelist_container').innerHTML = data.newlist.html;
            if (data.changedfolder && data.newpath) {
                $(self.id+'_folder').value = self.folderid = data.folder;
                $(self.id+'_foldername').value = self.foldername = data.newpath.foldername;
                $(self.id+'_foldernav').innerHTML = data.newpath.html;
                if (data.changedowner && data.newtabs) {
                    $(self.id+'_ownertabs').innerHTML = data.newtabs;
                    $(self.id+'_ownersubtabs').innerHTML = data.newsubtabs;
                    if (data.tabupload) {
                        removeElementClass(self.id + '_upload_container', 'hidden');
                    } else {
                        addElementClass(self.id + '_upload_container', 'hidden');
                    }
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
                        removeElementClass(self.id + '_upload_container', 'hidden');
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
            if (data.tagblockhtml && $('sb-tags')) {
                $('sb-tags').innerHTML = data.tagblockhtml;
            }
            self.browse_init();
        }
        else if (data.goto) {
            location.href = data.goto;
        }
        else if (typeof(data.replaceHTML) == 'string') {
            formSuccess(form, data);
            self.init();
        }
    }

}

