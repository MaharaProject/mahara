function FileBrowser(idprefix, folderid, config) {

    var self = this;
    this.id = idprefix;
    this.folderid = folderid;
    this.config = config;
    this.nextupload = 0;

    this.init = function () {
        self.form = $(self.formname);
        self.foldername = self.form.foldername.value;
        if (self.config.upload) {
            self.upload_init();
        }
        self.browse_init();
        if (self.config.upload) {
            self.edit_init();
        }
    }

    this.upload_init = function () {
        if ($(self.id + '_notice')) {
            addElementClass(self.id + '_elements', 'hidden');
            addElementClass(self.id + '_uploadcancel', 'hidden');
        }
        self.upload_connectbuttons();
    }

    this.upload_connectbuttons = function () {
        if ($(self.id + '_notice')) {
            connect(self.id + '_notice', 'onclick', function (e) {
                // error class is too general?
                forEach(getElementsByTagAndClassName('div', 'error', self.id + '_upload_messages'), removeElement);
                if (this.checked) {
                    removeElementClass(self.id + '_elements', 'hidden');
                } else {
                    addElementClass(self.id + '_elements', 'hidden');
                }
            });
            connect(self.id + '_uploadcancel', 'onclick', function () {
                removeElementClass(self.id + '_openbutton', 'hidden');
                addElementClass(self.id + '_agreement', 'hidden');
                $(self.id + '_notice').checked = false;
                addElementClass(self.id + '_elements', 'hidden');
                addElementClass(this, 'hidden');
            });
        }
        connect(self.id + '_userfile', 'onchange', self.upload_submit);
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
        signal(self.form, 'onsubmit');
        self.form.submit();
        // $(self.id + '_userfile').value = ''; // Won't work in IE
        replaceChildNodes(self.id + '_userfile_container', INPUT({'type':'file', 'class':'file', 'id':self.id+'_userfile', 'name':'userfile', 'size':40}));
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
            else if (self.fileexists(name, this.value)) {
                message = get_string('filewithnameexists', name);
            }
        }
        if (message) {
            e.stop();
            replaceChildNodes(self.id + '_edit_messages', makeMessage(message, 'error'));
            return false;
        }
    }

    this.browse_submit = function (e) {
        e.stop();
        signal(self.form, 'onsubmit');
        self.form.submit();
        return false;
    }

    this.upload_success = function (data) {
        if (data.problem) {
            var image = 'images/icon_problem.gif';
        }
        else if (!data.error) {
            var image = 'images/success.gif';
        }
        else {
            var image = 'images/failure.gif';
        }

        quotaUpdate(data.quotaused, data.quota);
        var newmessage = makeMessage(DIV(null,IMG({'src':get_themeurl(image)}), ' ', data.message));
        replaceChildNodes($('uploadstatusline'+data.uploadnumber), newmessage);
    }

    this.edit_form = function (e) {
        e.stop();
        var id = this.value;
        addElementClass(self.id + '_edit_row', 'hidden');
        $(self.id + '_edit_heading').innerHTML = self.filedata[id].artefacttype == 'folder' ? get_string('editfolder') : get_string('editfile');
        $(self.id + '_edit_title').value = self.filedata[id].title;
        $(self.id + '_edit_description').value = self.filedata[id].description;
        $(self.id + '_edit_tags').value = self.filedata[id].tags.join(', ');
        replaceChildNodes($(self.id + '_edit_messages'));
        forEach(getElementsByTagAndClassName('input', 'permission', self.id + '_edit_row'), function (elem) {
            var perm = getNodeAttribute(elem, 'name').split(':');
            if (self.filedata[id].permissions[perm[1]][perm[2]] == 1) {
                elem.checked = true;
            }
        });
        $(self.id + '_edit_artefact').value = id;
        var edit_row = removeElement(self.id + '_edit_row');
        var this_row = getFirstParentByTagAndClassName(this, 'tr');
        insertSiblingNodesAfter(this_row, edit_row);
        removeElementClass(edit_row, 'hidden');
        return false;
    }

    this.edit_init = function () { augment_tags_control(self.id + '_edit_tags'); }

    this.browse_init = function () {
        if (self.config.edit) {
            forEach(getElementsByTagAndClassName('button', null, 'filelist'), function (elem) {
                var name = getNodeAttribute(elem, 'name');
                if (name == 'edit') {
                    connect(elem, 'onclick', self.edit_form);
                }
            });
            connect(self.id + '_edit_cancel', 'onclick', function (e) {
                e.stop();
                addElementClass(self.id + '_edit_row', 'hidden');
                return false;
            });
            connect(self.id + '_edit_artefact', 'onclick', self.edit_submit);
            forEach(getElementsByTagAndClassName('div', 'icon-drag', 'filelist'), function (elem) {
                self.make_icon_draggable(elem);
            });
        }
        forEach(getElementsByTagAndClassName('a', 'changefolder', null), function (elem) {
            connect(elem, 'onclick', function (e) {
                var params = parseQueryString(getNodeAttribute(this, 'href'));
                $(self.id + '_folder').value = params.folder;
                self.browse_submit(e);
            });
        });
        if ($(self.id + '_createfolder')) {
            connect($(self.id + '_createfolder'), 'onclick', self.createfolder_submit);
        }
    }

    this.make_row_droppable = function(row) {
        new Droppable(row, {
            accept: ['icon-drag'],
            hoverclass: 'folderhover',
            ondrop: function (dragged, dropped) {
                self.form.move.value = dragged.id.replace(/drag:/, '');
                self.form.moveto.value = dropped.id.replace(/file:/, '');
                signal(self.form, 'onsubmit');
                self.form.submit();
                self.form.move.value = '';
                self.form.moveto.value = '';
            }
        });
    };

    this.make_icon_draggable = function(elem) {
        new Draggable(elem, {
            starteffect: function(elem) {
                // self.drag.clone is a copy of the icon which gets left behind.

                map(self.make_row_droppable, getElementsByTagAndClassName('tr', 'folder', 'filelist'));

                var row = getFirstParentByTagAndClassName(elem, 'tr', 'directory-item');
                var rowid = getNodeAttribute(row, 'id');
                var artefactid = rowid.substr(rowid.indexOf(':')+1);

                self.drag.clone = elem.cloneNode(true);
                setNodeAttribute(elem, 'id', 'drag:' + artefactid);
                insertSiblingNodesAfter(elem, self.drag.clone);
                setStyle(getFirstElementByTagAndClassName('img', null, elem), {'vertical-align':'middle'});
                // Could read filename from self.filedata, but the one on the page is already shortened for display
                appendChildNodes(elem, A({'href':''}, scrapeText(getFirstElementByTagAndClassName('td', 'filename', row))));

                MochiKit.Position.absolutize(elem);
                setStyle(elem, {
                    'border': '2px solid #000', // doesn't show up in IE6
                    'padding': '4px',
                    'line-height': '2em'
                });

                setOpacity(elem, 0.5);
            },
            revert: function(element) {
                // Throw away the element being dragged
                removeElement(element);
                element = null;
                self.make_icon_draggable(self.drag.clone);
            }
        });
        // Draggable sets position = 'relative', but we set it back
        // here because with position = 'relative' in IE6 the rows
        // stay put instead of moving down when the create/upload
        // forms are opened on the page.
        elem.style.position = 'static';
    };

    this.drag = {};

    this.success = function (form, data) {
        var stop = false; // Whether to call the form's success callback afterwards
        if (self.config.upload && data.action == 'upload') {
            self.upload_success(data);
            stop = true;
        }
        if (data.newlist && (data.folder == self.folderid || data.action == 'changefolder')) {
            self.filedata = data.newlist.data;
            if (self.config.edit) {
                replaceChildNodes(self.id + '_edit_placeholder', removeElement(self.id + '_edit_row'));
            }
            $(self.id+'_filelist_container').innerHTML = data.newlist.html;
            if (data.action == 'changefolder' && data.newpath) {
                $(self.id+'_folder').value = self.folderid = data.folder;
                $(self.id+'_foldername').value = self.foldername = data.newpath.foldername;
                $(self.id+'_foldernav').innerHTML = data.newpath.html;
                stop = true;
            }
            else if (data.action == 'move') {
            }
            stop = true;
            self.browse_init();
        }
        if (data.action == 'delete') {
            quotaUpdate(data.quotaused, data.quota);
        }
        return stop;
    }

}
