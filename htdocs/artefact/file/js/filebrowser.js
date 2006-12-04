var changedir = function () {};

function FileBrowser(element, changedircallback) {
    var self = this;
    this.element = element;
    this.pathids = {'/':null};
    this.cwd = '/';
    this.changedircallback = changedircallback;

    this.init = function() {
        self.filelist = new TableRenderer(
            self.element,
            'myfiles.json.php',
            [
                self.formatname,
                function (r) { return TD(null, (r.artefacttype != 'folder') ? self.showsize(r.size) : null); },
                'mtime',
                self.editdelete
            ]
        );
        self.filelist.emptycontent = get_string('nofilesfound');
        self.filelist.paginate = false;
        self.filelist.statevars.push('folder');
        self.filelist.rowfunction = function (r) { return TR({'id':'row_' + r.id}); };
        self.filelist.init();
        changedir = self.changedir;
        self.changedir(self.cwd);
    }

    this.refresh = function () { self.changedir(self.cwd); };

    this.editdelete = function(r) {
        var editb = INPUT({'type':'button', 'value':get_string('edit')});
        editb.onclick = function () { self.openeditform(r); };
        var deleteb = INPUT({'type':'button', 'value':get_string('delete')});
        deleteb.onclick = function () { self.deletefile(r.id); };
        return TD(null, editb, deleteb);
    }

    this.editformtitle = function(s) {
        return TR(null,TD({'colSpan':2},s));
    }

    this.textinputrow = function(str, value) {
        return TR(null,TD(null,get_string(str)),
                  TD(null,INPUT({'type':'text','name':str,'value':value})));
    }

    this.destinationrow = function () {
        return TR(null,TD(null,get_string('destination')), TD(null,cwd));
    }

    this.folderformrows = function(fileinfo) {
        var rows = [];
        var name = '';
        var description = '';
        if (fileinfo == null) {
            rows = [self.editformtitle(get_string('createfolder'))];
            rows.push(destinationrow);
        } else {
            rows = [self.editformtitle(get_string('editfolder'))];
            name = fileinfo.name;
            description = fileinfo.name;
        }
        rows.push(self.textinputrow('name',name));
        rows.push(self.textinputrow('description',description));
        return rows;
    }

    this.openeditform = function(fileinfo) {
        var editrows = [];
        var elemid = fileinfo.artefacttype + fileinfo.id;
        var savebutton = INPUT({'type':'button','value':get_string('savechanges')});
        //savebutton.onclick = function () { updatefilemetadata(elemid) };
        if (fileinfo['artefacttype'] == 'folder') {
            editrows = self.folderformrows(fileinfo);
        }
        else {
            editrows = [self.editformtitle(get_string('editfile')),
                        self.textinputrow('name',fileinfo.name),
                        self.textinputrow('title',fileinfo.name),
                        self.textinputrow('description',fileinfo.name)];
        }
        var editid = 'edit_' + elemid;
        var rowid = 'row_'+fileinfo.id;
        var cancelbutton = INPUT({'type':'button', 'value':get_string('cancel')});
        cancelbutton.onclick = function () {
            $(rowid).style.visibility = '';
            removeElement(editid);
        }
        var edittable = TABLE({'align':'center'},TBODY(null,editrows));
        var buttons = [savebutton,cancelbutton];
        $(rowid).style.visibility = 'hidden';
        insertSiblingNodesBefore(rowid, TR({'id':editid},
                                           TD({'colSpan':4},
                                              FORM({'id':editid+'_form','action':''},edittable,buttons))));
    }

    this.deletefile = function(id) { alert('delete ' + id); };

    this.showsize = function(bytes) {
        if (bytes < 1024) {
            return bytes + 'b';
        }
        if (bytes < 1048576) {
            return Math.floor((bytes / 1024) * 10) / 10 + 'k';
        }
        return Math.floor((bytes / 1048576) * 10) / 10 + 'M';
    }

    this.formatname = function(r) {
        if (r.artefacttype == 'file') {
            return TD(null, r.name);
        }
        if (r.artefacttype == 'folder') {
            var dir = self.cwd + r.name + '/';
            self.pathids[dir] = r.id;
            var link = A({'href':'', 'onclick':"return changedir('" + dir.replace(/\'/g,"\\\'") + "')"},
                         r.name);
            return TD(null, link);
        }
    }

    this.changedir = function(path) {
        alert(path + ' ' + self.pathids[path]);
        self.cwd = path;
        self.linked_path();
        self.changedircallback(self.pathids[path], path);
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
