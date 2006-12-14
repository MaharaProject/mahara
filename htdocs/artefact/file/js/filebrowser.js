var changedir = function () {};

function FileBrowser(element, changedircallback) {
    var self = this;
    this.element = element;
    this.pathids = {'/':null};
    this.cwd = '/';
    if (typeof(changedircallback) == 'function') {
        this.changedircallback = changedircallback;
    }
    else {
        this.changedircallback = function () {};
    }
    this.filenames = {};

    this.init = function() {

        // Create the button which opens up the create folder form
        var button = INPUT({'type':'button','value':get_string('createfolder'), 'onclick':function () { 
            hideElement(self.createfolderbutton);
            showElement(self.createfolderform);
        }});
        self.createfolderbutton = button;
        self.createfolderform = self.initcreatefolderform();
        insertSiblingNodesBefore(self.element, self.createfolderbutton, self.createfolderform);

        // Folder navigation links
        insertSiblingNodesBefore(self.element, DIV({'id':'foldernav'}));

        self.filelist = new TableRenderer(
            self.element,
            'myfiles.json.php',
            [
                self.formatname,
                'description',
                function (r) { return TD(null, (r.artefacttype != 'folder') ? self.showsize(r.size) : null); },
                'mtime',
                // @todo this function should be changed for when we
                // are using the browser to attach files
                self.editdelete
            ]
        );
        self.filelist.emptycontent = get_string('nofilesfound');
        self.filelist.paginate = false;
        self.filelist.statevars.push('folder');
        self.filelist.rowfunction = function (r) { return TR({'id':'row_' + r.id}); };
        self.filelist.init();
        changedir = self.changedir; // Ick; needs to be set globally for some links to work
        self.changedir(self.cwd);
    }

    this.refresh = function () { self.changedir(self.cwd); };
    
    this.editdelete = function(r) {
        var editb = INPUT({'type':'button', 'value':get_string('edit')});
        editb.onclick = function () { self.openeditform(r); };
        var deleteb = INPUT({'type':'button', 'value':get_string('delete')});
        deleteb.onclick = function () {
            if (confirm(get_string(r.artefacttype == 'folder' ? 'deletefolderandcontents?' : 'deletefile?'))) {
                sendjsonrequest('delete.json.php', {'id': r.id}, self.refresh);
            }
        };
        return TD(null, editb, deleteb);
    }

    this.savemetadata = function (fileid, formid, replacefile, originalname) {
        var name = $(formid).name.value;
        if (isEmpty(name)) {
            $(formid + 'message').innerHTML = get_string('namefieldisrequired');
            return;
        }
        if (!replacefile && self.fileexists(name) && name != originalname) {
            $(formid+'message').innerHTML = get_string('fileexistsoverwritecancel');
            setDisplayForElement('inline', $(formid).replace);
            return;
        }
        $(formid+'message').innerHTML = '';
        hideElement($(formid).replace);

        var collideaction = replacefile ? 'replace' : 'fail';
        var data = {'name':$(formid).name.value, 'collideaction':collideaction,
                    'description':$(formid).description.value};
        if (fileid) {
            var script = 'updatemetadata.json.php';
            data['id'] = fileid;
        }
        else {
            var script = 'createfolder.json.php';
            if (self.cwd != '/') {
                data['parentfolder'] = self.pathids[self.cwd];
            }
        }
        sendjsonrequest(script, data, self.refresh);
    }

    this.openeditform = function(fileinfo) {
        var editrows = [];
        var editid = 'edit_' + fileinfo.id;
        var formid = editid + '_form';
        var rowid = 'row_' + fileinfo.id;
        var cancelform = function() {
            setDisplayForElement(null, rowid);
            removeElement(editid);
        };
        var savebutton = INPUT({'type':'button','value':get_string('savechanges')});
        savebutton.onclick = function () { self.savemetadata(fileinfo.id, formid, false, fileinfo.title); };
        var replacebutton = INPUT({'type':'button', 'value':get_string('overwrite'),
                                   'name':'replace', 'style':'display: none;'});
        replacebutton.onclick = function () { self.savemetadata(fileinfo.id, formid, true); };
        var cancelbutton = INPUT({'type':'button', 'value':get_string('cancel'), 'onclick':cancelform});
        var editformtitle = get_string(fileinfo.artefacttype == 'folder' ? 'editfolder' : 'editfile');
        var edittable = TABLE({'align':'center'},TBODY(null,
                         TR(null,TH({'colspan':2},LABEL(editformtitle))),
                         TR(null,TH(null,LABEL(get_string('name'))),
                          TD(null,INPUT({'type':'text','name':'name','value':fileinfo.title,'size':40}))),
                         TR(null,TH(null,LABEL(get_string('description'))),
                          TD(null,INPUT({'type':'text','name':'description',
                                         'value':fileinfo.description,'size':40}))),
                         TR(null,TD({'colspan':2},SPAN({'id':formid+'message'}))),
                         TR(null,TD({'colspan':2}, savebutton, replacebutton, cancelbutton))));
        hideElement(rowid);
        insertSiblingNodesBefore(rowid, TR({'id':editid},
                                           TD({'colSpan':5},
                                              FORM({'id':formid,'action':''},edittable))));
    }

    this.initcreatefolderform = function () {
        var formid = 'createfolderform';
        var cancelbutton = INPUT({'type':'button','value':get_string('cancel'), 'onclick':function () {
            setDisplayForElement(null, self.createfolderbutton);
            hideElement($(formid).replace);
            $(formid).name.value = '';
            $(formid).description.value = '';
            $(formid+'message').innerHTML = '';
            hideElement(formid);
        }});
        var createbutton = INPUT({'type':'button','value':get_string('create'),'onclick':function () {
            self.savemetadata(null, formid, false);
        }});
        var replacebutton = INPUT({'type':'button', 'value':get_string('overwrite'),
                                   'name':'replace', 'style':'display: none;', 'onclick':function() {
            self.savemetadata(null, formid, true);
        }});
        return FORM({'method':'post', 'id':formid, 'style':'display: none;'},
                TABLE(null,
                 TBODY(null,
                  TR(null,TH({'colSpan':2},LABEL(null,get_string('createfolder')))),
                  TR(null,TH(null,LABEL(get_string('destination'))),
                     TD(null, SPAN({'id':'createdest'},self.cwd))),
                  TR(null,TH(null,LABEL(get_string('name'))),
                     TD(null,INPUT({'type':'text','name':'name','value':'','size':40}))),
                  TR(null,TH(null,LABEL(get_string('description'))),
                     TD(null,INPUT({'type':'text','name':'description','value':'','size':40}))),
                  TR(null,TD({'colspan':2},SPAN({'id':formid+'message'}))),
                  TR(null,TD({'colspan':2},createbutton,replacebutton,cancelbutton)))));
    };

    this.showsize = function(bytes) {
        if (bytes < 1024) {
            return bytes + 'b';
        }
        if (bytes < 1048576) {
            return Math.floor((bytes / 1024) * 10 + 0.5) / 10 + 'k';
        }
        return Math.floor((bytes / 1048576) * 10 + 0.5) / 10 + 'M';
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
        return TD(null, A({'href':'download.php?file=' + r.id}, r.title));
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
