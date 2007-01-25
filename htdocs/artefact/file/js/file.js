// file.js

// The file browser part needs to be kept relatively separated from
// the file uploader because they are used slightly differently in the
// my files screen and the edit blog post screen

var changedir = function () {};

function FileBrowser(element, source, statevars, changedircallback, actionname, actioncallback) {
    var self = this;
    this.element = element;
    this.source = source;
    this.statevars = statevars ? statevars : {};
    this.pathids = {'/':null};
    this.cwd = '/';
    this.changedircallback = (typeof(changedircallback) == 'function') ? changedircallback : function () {};
    this.actioncallback = (typeof(actioncallback) == 'function') ? actioncallback : function () {};
    this.actionname = actionname;
    this.canmodify = !actionname;
    this.filenames = {};
    this.deletescript = config.wwwroot+'artefact/file/delete.json.php';
    this.createfolderscript = config.wwwroot+'artefact/file/createfolder.json.php';
    this.updatemetadatascript = config.wwwroot+'artefact/file/updatemetadata.json.php';
    this.downloadscript = config.wwwroot+'artefact/file/download.php';

    if (this.actionname) {
        this.lastcolumnfunc = function(r) {
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
            var editb = INPUT({'type':'button', 'class':'button', 'value':get_string('edit')});
            editb.onclick = function () { self.openeditform(r); };
            if (r.childcount > 0) {
                return TD(null, editb);
            }
            var deleteb = INPUT({'type':'button', 'class':'button', 'value':get_string('delete')});
            deleteb.onclick = function () {
                if (confirm(get_string(r.artefacttype == 'folder' ? 'deletefolder?' : 'deletefile?'))) {
                    if (!r.attachcount || r.attachcount == 0
                        || confirm(get_string('unlinkthisfilefromblogposts?'))) {
                        sendjsonrequest(self.deletescript, {'id': r.id}, self.deleted);
                    }
                }
            };
            return TD(null, editb, deleteb);
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
            self.createfolderbutton = button;
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
            return TR({'class': 'r' + (n%2),'id':'row_' + r.id});
        };
        self.filelist.init();
        changedir = self.changedir; // Ick; needs to be set globally for some links to work
        self.changedir(self.cwd);
    }

    this.deleted = function (data) {
        quotaUpdate(data.quotaused, data.quota);
        self.refresh();
    };

    this.refresh = function () { self.changedir(self.cwd); };

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

        if (fileid) {
            var script = self.updatemetadatascript;
            data['id'] = fileid;
        }
        else {
            var script = self.createfolderscript;
        }
        if (self.cwd != '/') {
            data['parentfolder'] = self.pathids[self.cwd];
        }
        sendjsonrequest(script, data, self.refresh);
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
        return FORM({'method':'post', 'id':formid, 'style':'display: none;'},
                TABLE(null,
                 TBODY(null,
                  TR(null,TH({'colSpan':2},LABEL(null,get_string('createfolder')))),
                  TR(null,TH(null,LABEL(get_string('destination'))),
                     TD(null, SPAN({'id':'createdest'},self.cwd))),
                  TR(null,TH(null,LABEL(get_string('name'))),
                     TD(null,INPUT({'type':'text','class':'text','name':'name','value':'',
                                    'size':40}))),
                  TR(null,TH(null,LABEL(get_string('description'))),
                     TD(null,INPUT({'type':'text','class':'text','name':'description',
                                    'value':'','size':40}))),
                  TR(null,TD({'colspan':2},SPAN({'id':formid+'message'}))),
                  TR(null,TD({'colspan':2},createbutton,replacebutton,cancelbutton)))));
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
        if (r.artefacttype == 'folder') {
            var dir = self.cwd + r.title + '/';
            self.pathids[dir] = r.id;
            var link = A({'href':'', 'onclick':"return changedir('" + dir.replace(/\'/g,"\\\'") + "')"},
                         r.title);
            return TD(null, link);
        }
        if (self.actionname) {
            return TD(null, r.title);
        }
        return TD(null, A({'href':self.downloadscript + '?file=' + r.id}, r.title));
    }

    this.fileexists = function (filename) { 
        return self.filenames[filename] == true;
    }

    this.updatedestination = function () {
        if ($('createdest')) {
            $('createdest').innerHTML = self.cwd;
        }
    }

    this.changedir = function(path) {
        self.cwd = path;
        self.linked_path();
        self.updatedestination();
        self.changedircallback(self.pathids[path], path);
        self.filenames = {};
        var args = path == '/' ? null : {'folder':self.pathids[path]};
        self.filelist.doupdate(args);
        return false;
    }

    this.linked_path = function() {
        var dirs = self.cwd.split('/');
        var homedir = A({'href':'', 'onclick':"return changedir('/')"}, get_string('home'));
        var sofar = '/';
        var folders = [homedir];
        for (i=0; i<dirs.length; i++) {
            if (dirs[i] != '') {
                sofar = sofar + dirs[i] + '/';
                var dir = A({'href':'', 'onclick':"return changedir('" 
                             + sofar.replace(/\'/g,"\\\'") + "')"}, dirs[i]);
                folders.push(' / ');
                folders.push(dir);
            }
        }
        replaceChildNodes($('foldernav'),folders);
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
        self.openbutton = button;

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
            hideElement(self.form.replace);
            hideElement(self.form);
            showElement(self.openbutton);
        };
        var notice = SPAN(null);
        notice.innerHTML = copyrightnotice;
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
                TD(null, INPUT({'type':'text', 'class':'text', 'name':'title', 'size':40}))),
             TR(null, TH(null, LABEL(null, get_string('description'))),
                TD(null, INPUT({'type':'text', 'class':'text', 'name':'description', 'size':40}))),
             TR(null,TD({'colspan':2, 'id':'uploadformmessage'})),
             TR(null,TD({'colspan':2},
              INPUT({'name':'upload','type':'button','class':'button',
                     'value':get_string('upload'),
                     'onclick':function () { if (self.sendform(false)) { cancelform(); } }}),
              INPUT({'name':'replace','type':'button','class':'button',
                     'value':get_string('overwrite'),
                     'onclick':function () { if (self.sendform(true)) { cancelform(); } }}),
              INPUT({'type':'button','class':'button','value':get_string('cancel'),
                     'onclick':cancelform}))))));


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
        if (self.folderid) {
            appendChildNodes(self.form, 
                             INPUT({'type':'hidden', 'name':'parentfolder', 'value':self.folderid}));
        }
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
