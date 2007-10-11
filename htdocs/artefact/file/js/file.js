// file.js

// The file browser part needs to be kept relatively separated from
// the file uploader because they are used slightly differently in the
// my files screen and the edit blog post screen

function FileBrowser(element, source, statevars, changedircallback, actionname, actioncallback, startDirectory) {
    var self = this;
    this.element = element;
    this.source = source;
    this.statevars = statevars ? statevars : {};
    this.rootDirectory = {
        'name': get_string('home'),
        'parent': null,
        'children': {},
        'folderid': 0
    };
    this.currentDirectory = this.rootDirectory;
    this.actioncallback = (typeof(actioncallback) == 'function') ? actioncallback : function () {};
    this.actionname = actionname;
    this.canmodify = !actionname;
    this.filenames = {};
    this.deletescript = config.wwwroot+'artefact/file/delete.json.php';
    this.createfolderscript = config.wwwroot+'artefact/file/createfolder.json.php';
    this.updatemetadatascript = config.wwwroot+'artefact/file/updatemetadata.json.php';
    this.downloadscript = config.wwwroot+'artefact/file/download.php';
    this.movescript = config.wwwroot+'artefact/file/move.json.php';
    this.maxnamestrlen = 34;

    if (typeof(startDirectory) == 'object') {
        var dirWalk = this.rootDirectory;

        forEach(startDirectory, function(folder) {
            if (folder.id == 0) {
                return;
            }
            var dirNode = {
                'folderid': folder.id,
                'name': folder.name,
                'children': {},
                'parent': dirWalk
            };

            dirWalk.children[folder.name] = dirNode;
            dirWalk = dirNode;
        });

        this.currentDirectory = dirWalk;
    }

    if (this.actionname) {
        this.lastcolumnfunc = function(r) {
            if (r.isparent) {
                return TD(null);
            }
            if (r.artefacttype != 'folder') {
                var button = INPUT({'type':'button', 'class':'button', 'value':self.actionname});
                button.onclick = function () { self.actioncallback(r) };
                return TD(null, button);
            }
            return TD(null);
        }
    }
    else {
        this.lastcolumnfunc = function (r) {
            if (r.isparent) {
                return TD(null);
            }
            var editb = INPUT({'type':'button', 'class':'button', 'value':get_string('edit')});
            editb.onclick = function () { self.openeditform(r); };
            var edith = SPAN(null);
            edith.innerHTML = get_string('edit.help');
            if (r.childcount > 0) {
                return TD(null, editb, edith);
            }
            var deleteb = INPUT({'type':'button', 'class':'button', 'value':get_string('delete')});
            deleteb.onclick = function () {
                if (confirm(get_string(r.artefacttype == 'folder' ? 'deletefolder?' : 'deletefile?'))) {
                    if (!r.attachcount || r.attachcount == 0
                        || confirm(get_string('unlinkthisfilefromblogposts?'))) {
                        sendjsonrequest(self.deletescript, {'id': r.id}, 'POST', self.deleted);
                    }
                }
            };
            var deleteh = SPAN(null);
            deleteh.innerHTML = get_string('delete.help');
            return TD(null, editb, edith, deleteb, deleteh);
        }
    }

    this.init = function() {

        if (self.canmodify) {
            // Create the button which opens up the create folder form
            var button = INPUT({'type':'button', 'class':'button',
                                'value':get_string('createfolder'), 'onclick':function () { 
                hideElement(self.createfolderbutton);
                showElement(self.createfolderform);
            }});
            var help = SPAN(null);
            help.innerHTML = get_string('createfolder.help');
            self.createfolderbutton = SPAN(null, button, help);
            self.createfolderform = self.initcreatefolderform();
            insertSiblingNodesBefore(self.element, self.createfolderbutton, self.createfolderform);
        }

        // Folder navigation links
        insertSiblingNodesBefore(self.element, DIV({'id':'foldernav'}));

        self.filelist = new TableRenderer(
            self.element,
            self.source,
            [
                function (r) { return TD(null, self.icon(r.artefacttype)); },
                self.formatname,
                'description',
                function (r) { return TD(null, (r.artefacttype != 'folder') ? self.showsize(r.size) : null); },
                'mtime',
                // @todo this function should be changed for when we
                // are using the browser to attach files
                self.lastcolumnfunc
            ]
        );
        self.filelist.emptycontent = get_string('nofilesfound');
        self.filelist.paginate = false;
        for (property in self.statevars) {
            self.filelist[property] = self.statevars[property];
            self.filelist.statevars.push(property);
        }
        self.filelist.rowfunction = function (r, n) {
            var row = TR({'class': 'r' + (n%2),'id':'row_' + r.id});
            addElementClass(row, 'directory-item');
            addElementClass(row, r.artefacttype);
            if (self.canmodify) {
                self.makeRowDraggable(row);
            }
            return row;
        };
        self.chdir(self.currentDirectory);
    }

    this.makeRowDroppable = function(row) {
        new Droppable(row, {
            accept: ['directory-item'],
            hoverclass: 'folderhover',
            ondrop: function (dragged, dropped) {
                sendjsonrequest(
                    self.movescript,
                    { artefact : dragged.id.replace(/row_/, ''),
                      newparent : dropped.id.replace(/row_/, '') },
                    'POST',
                    self.refresh);
            }
        });
    };

    this.drag = {};

    this.makeRowDraggable = function(row) {
        new Draggable(row, {
            starteffect: function(row) {
                // The existing row gets dragged around with only its first two children (icon & filename).
                // self.drag.clone is a copy of the row which gets left behind.

                map(self.makeRowDroppable,
                    getElementsByTagAndClassName('tr', 'folder', 'filelist'));

                var children = getElementsByTagAndClassName('td', null, row);
                var newchildren = [];  // copy the cells
                for (var i = 0; i < children.length; i++) {
                    newchildren[i] = children[i].cloneNode(true);
                    if (i > 1) {
                        removeElement(children[i]);
                    }
                }

                self.drag.clone = TR({'id':row.id}, newchildren);
                setElementClass(self.drag.clone, row.className);
                insertSiblingNodesAfter(row, self.drag.clone);

                // Try to give the dragged row the same width as the first two cells
                var id = getElementDimensions(children[0]);
                var nd = getElementDimensions(children[1]);
                setElementDimensions(children[0], id);
                setElementDimensions(children[1], nd);

                MochiKit.Position.absolutize(row);
                setStyle(row, {
                    'border': '2px solid #000', // doesn't show up in IE6
                    'width': (id.w + nd.w) + 'px',
                    'height': id.h + 'px'
                });

                setOpacity(row, 0.5);
            },
            revert: function(element) {
                // Throw away the row being dragged
                removeElement(element);
                element = null;
                self.refresh();
            }
        });
        // Draggable sets position = 'relative', but we set it back
        // here because with position = 'relative' in IE6 the rows
        // stay put instead of moving down when the create/upload
        // forms are opened on the page.
        row.style.position = 'static';
    };

    this.deleted = function (data) {
        quotaUpdate(data.quotaused, data.quota);
        self.refresh();
    };

    this.refresh = function () { self.chdir(self.currentDirectory); };

    this.savemetadata = function (fileid, formid, replacefile, originalname) {
        var name = $(formid).name.value;
        if (isEmpty(name)) {
            $(formid + 'message').innerHTML = get_string('namefieldisrequired');
            return false;
        }
        if (!replacefile && self.fileexists(name) && name != originalname) {
            $(formid+'message').innerHTML = get_string('fileexistsoverwritecancel');
            setDisplayForElement('inline', $(formid).replace);
            //$(formid).name.value = newfilename(name, this.fileexists); // not a good idea yet.
            $(formid).name.focus();
            return false;
        }
        $(formid+'message').innerHTML = '';
        hideElement($(formid).replace);

        var collideaction = replacefile ? 'replace' : 'fail';
        var data = self.statevars;
        data['name'] = $(formid).name.value;
        data['collideaction'] = collideaction;
        data['description'] = $(formid).description.value;
        data['tags'] = $(formid).tags.value;

        if (fileid) {
            var script = self.updatemetadatascript;
            data['id'] = fileid;
        }
        else {
            var script = self.createfolderscript;
        }
        data['parentfolder'] = self.currentDirectory.folderid;
        sendjsonrequest(script, data, 'POST', self.refresh);
        return true;
    }

    this.openeditform = function(fileinfo) {
        var editrows = [];
        var editid = 'edit_' + fileinfo.id;
        var formid = editid + '_form';
        var rowid = 'row_' + fileinfo.id;
        var cancelform = function() {
            setDisplayForElement('', rowid);
            removeElement(editid);
        };
        var savebutton = INPUT({'type':'button', 'class':'button', 'value':get_string('savechanges')});
        savebutton.onclick = function () { self.savemetadata(fileinfo.id, formid, false, fileinfo.title); };
        var replacebutton = INPUT({'type':'button', 'class':'button', 'value':get_string('overwrite'),
                                   'name':'replace', 'style':'display: none;'});
        replacebutton.onclick = function () { self.savemetadata(fileinfo.id, formid, true); };
        var cancelbutton = INPUT({'type':'button', 'class':'button', 
                                  'value':get_string('cancel'), 'onclick':cancelform});
        var editformtitle = get_string(fileinfo.artefacttype == 'folder' ? 'editfolder' : 'editfile');
        var edittable = TABLE({'align':'center'},TBODY(null,
                         TR(null,TH({'colspan':2},LABEL(editformtitle))),
                         TR(null,TH(null,LABEL(get_string('name'))),
                          TD(null,INPUT({'type':'text','class':'text','name':'name',
                                         'value':fileinfo.title,'size':40}))),
                         TR(null,TH(null,LABEL(get_string('description'))),
                          TD(null,INPUT({'type':'text','class':'text','name':'description',
                                         'value':fileinfo.description,'size':40}))),
                         TR(null, TH(null, LABEL(null, get_string('tags'))),
                            TD(null, create_tags_control('tags', fileinfo.tags))),
                         TR(null,TD({'colspan':2},SPAN({'id':formid+'message'}))),
                         TR(null,TD({'colspan':2}, savebutton, replacebutton, cancelbutton))));
        hideElement(rowid);
        insertSiblingNodesBefore(rowid, TR({'id':editid},
                                           TD({'colSpan':6},
                                              FORM({'id':formid,'action':''},edittable))));
        keepElementInViewport(editid);
    }

    this.initcreatefolderform = function () {
        var formid = 'createfolderform';
        var cancelcreateform = function () {
            setDisplayForElement('inline', self.createfolderbutton);
            hideElement($(formid).replace);
            $(formid).name.value = '';
            $(formid).description.value = '';
            swapDOM($(formid).tags.parentNode.parentNode.parentNode.parentNode, create_tags_control('tags'));
            $(formid+'message').innerHTML = '';
            hideElement(formid);
        };
        var cancelbutton = INPUT({'type':'button','class':'button',
                                  'value':get_string('cancel'), 'onclick':cancelcreateform});
        var createbutton = INPUT({'type':'button','class':'button',
                                  'value':get_string('create'),'onclick':function () {
            if (self.savemetadata(null, formid, false)) {
                cancelcreateform();
            }
        }});
        var replacebutton = INPUT({'type':'button', 'class':'button',
                                   'value':get_string('overwrite'), 'name':'replace', 
                                   'style':'display: none;', 'onclick':function() {
            if (self.savemetadata(null, formid, true)) {
                cancelcreateform();
            }
        }});
        var namehelp = SPAN(null); namehelp.innerHTML = get_string('name.help');
        var deschelp = SPAN(null); deschelp.innerHTML = get_string('folderdescription.help');
        var cancelhelp = SPAN(null); cancelhelp.innerHTML = get_string('cancelfolder.help');
        return FORM({'method':'post', 'id':formid, 'style':'display: none;'},
                TABLE(null,
                 TBODY(null,
                  TR(null,TH({'colSpan':2},LABEL(null,get_string('createfolder')))),
                  TR(null,TH(null,LABEL(get_string('destination'))),
                     TD(null, SPAN({'id':'createdest'},self.generatePath(self.currentDirectory)))),
                  TR(null,TH(null,LABEL(get_string('name'))),
                     TD(null,INPUT({'type':'text','class':'text','name':'name','value':'',
                                    'size':40}), namehelp)),
                  TR(null,TH(null,LABEL(get_string('description'))),
                     TD(null,INPUT({'type':'text','class':'text','name':'description',
                                    'value':'','size':40}), deschelp)),
                  TR(null, TH(null, LABEL(null, get_string('tags'))), TD({'colspan':'2'}, create_tags_control('tags'))),
                  TR(null,TD({'colspan':2},SPAN({'id':formid+'message'}))),
                  TR(null,TD({'colspan':2},createbutton,replacebutton,cancelbutton,cancelhelp)))));
    };

    this.showsize = function(bytes) {
        if (bytes < 1024) {
            return bytes + (bytes > 0 ? 'b' : '');
        }
        if (bytes < 1048576) {
            return Math.floor((bytes / 1024) * 10 + 0.5) / 10 + 'k';
        }
        return Math.floor((bytes / 1048576) * 10 + 0.5) / 10 + 'M';
    }

    this.icon = function (type) {
        return IMG({'src':get_themeurl('images/'+type+'.gif')});
    }

    this.formatname = function(r) {
        self.filenames[r.title] = true;
        var parentattribs = {};
        if (r.title.length > self.maxnamestrlen + 3) {
            var parts = map(
                function (s) {
                    if (s.length > self.maxnamestrlen + 3)
                        return s.substring(0,self.maxnamestrlen/2) + '...'
                        + s.substring(s.length-self.maxnamestrlen/2,s.length);
                    else 
                        return s;
                },
                r.title.split(' '));
            var displaytitle = parts.join(' ');
            if (displaytitle != r.title) {
                parentattribs.title = r.title;
            }
        } else {
            var displaytitle = r.title;
        }
        if (r.isparent) {
            parentattribs.href = '';
            var link = A(parentattribs, displaytitle);
            connect(link, 'onclick', function (e) {
                self.chdir(self.currentDirectory.parent);
                e.stop();
            });
            var cell = TD(null, link);
        } else if (r.artefacttype == 'folder') {
            // If we haven't seen this directory before
            if (!self.currentDirectory.children[r.title]) {
                self.currentDirectory.children[r.title] = {
                    'name': r.title,
                    'parent': self.currentDirectory,
                    'children': {},
                    'folderid': r.id
                }
            }
            parentattribs.href = '';
            var link = A(parentattribs, displaytitle);
            connect(link, 'onclick', function (e) {
                self.chdir(self.currentDirectory.children[r.title]);
                e.stop();
            });
            var cell = TD(null, link);
        } else if (self.actionname) {
            var cell = TD(parentattribs, displaytitle);
        } else {
            parentattribs.href = self.downloadscript + '?file=' + r.id;
            var cell = TD(null, A(parentattribs, displaytitle));
        }
        return cell;
    }

    this.fileexists = function (filename) { 
        return self.filenames[filename] == true;
    }

    this.chdir = function(dirNode) {
        self.currentDirectory = dirNode;
        if (typeof(self.changedircallback) == 'function') {
            self.changedircallback(dirNode.folderid, self.generatePath(dirNode));
        }
        if ($('createdest')) {
            $('createdest').innerHTML = self.generatePath(dirNode);
        }
        self.filenames = {};
        self.filelist.doupdate({'folder': dirNode.folderid});
        self.breadcrumbUpdate();
    }

    this.generatePath = function(dirNode) {
        var folders = [];
        while (dirNode.parent) {
            folders.unshift(dirNode.name);

            dirNode = dirNode.parent;
        }

        return get_string('home') + ' / ' + folders.join(' / ');
    }

    this.breadcrumbUpdate = function() {
        var folders = [];

        var cwd = self.currentDirectory;
        while ( cwd ) {
            var link = A({'href': ''}, cwd.name);
            connect(link, 'onclick', partial(function (dir, e) {
                self.chdir(dir);
                e.stop();
            }, cwd));

            if (self.canmodify) {
                new Droppable(link, {
                    accept: ['directory-item'],
                    hoverclass: 'folderhover',
                    ondrop: partial(function (dirid, dragged) {
                        sendjsonrequest(
                            self.movescript,
                            { artefact : dragged.id.replace(/row_/, ''),
                              newparent : dirid },
                            'POST',
                            self.refresh);
                    }, cwd.folderid)
                });
            }

            folders.unshift(link);

            if ( cwd.parent ) {
                folders.unshift(' / ');
            }
            cwd = cwd.parent;
        }

        replaceChildNodes('foldernav', folders);
    }

    addLoadEvent(this.init);

}


function FileUploader(element, uploadscript, statevars, foldername, folderid, uploadcallback, fileexists) {

    var self = this;
    this.element = element;
    this.uploadscript = uploadscript;
    this.statevars = statevars ? statevars : {};
    this.folderid = folderid;
    this.foldername = foldername ? foldername : get_string('home');
    this.uploadcallback = uploadcallback;

    if (typeof(fileexists) == 'function') {
        this.fileexists = fileexists;
    }
    else {
        this.fileexists = function (filename) { alert(filename); };
    }

    this.init = function() {
        self.nextupload = 1;

        // Create the upload form
        self.form = self.initform();

        // Create the button which opens up the upload form
        var button = INPUT({'type':'button', 'class':'button',
                            'value':get_string('uploadfile'), 'onclick':function () { 
            hideElement(self.openbutton);
            showElement(self.form);
            keepElementInViewport(self.form);
        }});
        var uploadhelp = SPAN({'id':'uploadfilehelp'});
        uploadhelp.innerHTML = get_string('uploadfile.help');
        self.openbutton = SPAN(null, button, uploadhelp);

        appendChildNodes(self.element, self.form, self.openbutton);
    }

    this.initform = function () {
        var form = FORM({'method':'post', 'id':'uploadform',
                         'enctype':'multipart/form-data', 'encoding':'multipart/form-data',
                         'action':self.uploadscript, 'target':''});
        var cancelform = function () {
            if ($('uploadformmessage')) {
                $('uploadformmessage').innerHTML = '';
            }
            self.form.notice.checked = '';
            self.form.userfile.value = '';
            self.form.title.value = '';
            self.form.description.value = '';
            swapDOM(self.form.tags.parentNode.parentNode.parentNode.parentNode, create_tags_control('tags'));
            hideElement(self.form.replace);
            hideElement(self.form);
            showElement(self.openbutton);
        };
        var notice = SPAN(null);
        notice.innerHTML = copyrightnotice + get_string('notice.help');
        var titlehelp = SPAN(null); titlehelp.innerHTML = get_string('title.help');
        var deschelp = SPAN(null); deschelp.innerHTML = get_string('description.help');
        var cancelhelp = SPAN(null); cancelhelp.innerHTML = get_string('cancel.help');
        var destinationattributes = (self.folderid === false) ? {'style':'display: none;'} : null;
        appendChildNodes(form,
            TABLE(null,
            TBODY(null, 
                  TR(null, TH({'colSpan':2}, LABEL(null, get_string('uploadfile')))),
             TR(destinationattributes, TH(null, LABEL(null, get_string('destination'))),
                TD(null, SPAN({'id':'uploaddest'},self.foldername))),
             TR(null, TH(null,LABEL(null,get_string('copyrightnotice'))),
                TD(null,INPUT({'type':'checkbox','class':'checkbox','name':'notice'}),notice)),
             TR(null, TH(null, LABEL(null, get_string('file'))),
                TD(null, INPUT({'type':'file','class':'file','name':'userfile','size':40,'onchange':function () {
                    self.form.title.value = basename(self.form.userfile.value);
                }}))),
             TR(null, TH(null, LABEL(null, get_string('title'))),
                TD(null, INPUT({'type':'text', 'class':'text', 'name':'title', 'size':40}), titlehelp)),
             TR(null, TH(null, LABEL(null, get_string('description'))),
                TD(null, INPUT({'type':'text', 'class':'text', 'name':'description', 'size':40}), deschelp)),
             TR(null, TH(null, LABEL(null, get_string('tags'))),
                TD({'colspan': 2}, create_tags_control('tags'))),
             TR(null,TD({'colspan':2, 'id':'uploadformmessage'})),
             TR(null,TD({'colspan':2},
              INPUT({'name':'upload','type':'button','class':'button',
                     'value':get_string('upload'),
                     'onclick':function () { if (self.sendform(false)) { cancelform(); } }}),
              INPUT({'name':'replace','type':'button','class':'button',
                     'value':get_string('overwrite'),
                     'onclick':function () { if (self.sendform(true)) { cancelform(); } }}),
              INPUT({'type':'button','class':'button','value':get_string('cancel'),
                     'onclick':cancelform}), cancelhelp)))));


        hideElement(form.replace);
        hideElement(form);
        return form;
    }

    this.updatedestination = function (folderid, foldername) {
        self.foldername = foldername;
        self.folderid = folderid;
        if ($('uploaddest')) {
            $('uploaddest').innerHTML = foldername;
        }
    }

    this.sendform = function (replacefile) {
        if (!self.form.notice.checked) {
            $('uploadformmessage').innerHTML = get_string('youmustagreetothecopyrightnotice');
            return false;
        }
        var localname = self.form.userfile.value;
        if (isEmpty(localname)) {
            $('uploadformmessage').innerHTML = get_string('filenamefieldisrequired');
            return false;
        }
        var destname = self.form.title.value;
        if (isEmpty(destname)) {
            $('uploadformmessage').innerHTML = get_string('titlefieldisrequired');
            return false;
        }
        localname = basename(localname);
        if (!replacefile && self.fileexists(destname)) {
            $('uploadformmessage').innerHTML = get_string('uploadfileexistsoverwritecancel');
            // Show replace button
            setDisplayForElement('inline', self.form.replace);
            self.form.title.focus();
            return false;
        }
        $('uploadformmessage').innerHTML = '';
        hideElement(self.form.replace);

        // Create iframe in which to load the file
        appendChildNodes(self.element,
                         createDOM('iframe',{'name':'iframe'+self.nextupload,
                                             'id':'iframe'+self.nextupload,
                                             'src':'blank.html',
                                             'style':'display: none;'}));

        // Safari loads the upload page in a new window when the iframe has display set to none.
        if (navigator.userAgent.indexOf('Safari/') != -1) {
            setNodeAttribute('iframe'+self.nextupload, 'style', 'display: width: 0px; height: 0px; border: 0px;');
        }
        setNodeAttribute(self.form, 'target', 'iframe' + self.nextupload);

        for (property in self.statevars) {
            appendChildNodes(self.form, 
                             INPUT({'type':'hidden', 'name':property, 'value':self.statevars[property]}));
        }
        
        var collideaction = replacefile ? 'replace' : 'fail';
        appendChildNodes(self.form, 
                         INPUT({'type':'hidden', 'name':'parentfoldername', 'value':self.foldername}),
                         INPUT({'type':'hidden', 'name':'collideaction', 'value':collideaction}),
                         INPUT({'type':'hidden', 'name':'uploadnumber', 'value':self.nextupload}));

        appendChildNodes(self.form, 
                         INPUT({'type':'hidden', 'name':'parentfolder', 'value':self.folderid}));

        if (self.createid) {
            appendChildNodes(self.form, 
                             INPUT({'type':'hidden', 'name':'createid', 'value':self.createid}));
        }

        self.form.submit();

        // Display upload status
        insertSiblingNodesBefore(self.form,
           DIV({'id':'uploadstatusline'+self.nextupload}, 
               IMG({'src':get_themeurl('images/loading.gif')}), ' ', 
               get_string('uploadingfiletofolder',localname,self.foldername)));
        self.nextupload += 1;
        return true;
    }

    this.getresult = function(data) {
        if (!data.error) {
            var image = 'images/success.gif';
        }
        else {
            var image = 'images/failure.gif';
        }

        quotaUpdate(data.quotaused, data.quota);
        replaceChildNodes($('uploadstatusline'+data.uploadnumber), 
                          IMG({'src':get_themeurl(image)}), ' ', 
                          data.message, ' ',
                          A({'style': 'cursor: pointer;', 
                             'onclick':'removeElement(this.parentNode)'},'[X]'));
        this.uploadcallback(data);
    }

    addLoadEvent(this.init);
}
