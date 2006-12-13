function FileUploader(element, foldername, folderid, uploadcallback, fileexists) {

    var self = this;
    this.element = element;
    this.foldername = foldername ? foldername : get_string('home');
    this.folderid = folderid;
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
        var button = INPUT({'type':'button','value':get_string('uploadfile'), 'onclick':function () { 
            hideElement(self.openbutton);
            showElement(self.form);
        }});
        self.openbutton = button;

        appendChildNodes(self.element, self.form, self.openbutton);
    }

    this.initform = function () {
        var form = FORM({'method':'post', 'id':'uploadform',
                         'enctype':'multipart/form-data', 'encoding':'multipart/form-data',
                         'action':'upload.php', 'target':''});
        appendChildNodes(form,
            TABLE(null,
            TBODY(null, 
             TR(null, TH(null, LABEL(null, get_string('destination'))),
                TD(null, SPAN({'id':'uploaddest'},self.foldername))),
             TR(null, TH(null, LABEL(null, get_string('file'))),
                TD(null, INPUT({'type':'file', 'name':'userfile', 'onchange':function () {
                    var full = self.form.userfile.value;
                    self.form.title.value = full.substring(full.lastIndexOf('/')+1, full.length);
                }}))),
             TR(null, TH(null, LABEL(null, get_string('title'))),
                TD(null, INPUT({'type':'text', 'name':'title'}))),
             TR(null, TH(null, LABEL(null, get_string('description'))),
                TD(null, INPUT({'type':'text', 'name':'description'}))),
             TR(null,TD({'colspan':2, 'id':'uploadformmessage'})),
             TR(null,TD({'colspan':2},
              INPUT({'name':'upload','type':'button','value':get_string('upload'),
                     'onclick':function () { self.sendform(false)}}),
              INPUT({'name':'replace','type':'button','value':get_string('replace'),
                     'onclick':function () { self.sendform(true); }}),
              INPUT({'type':'button','value':get_string('cancel'),'onclick':function () { 
                  if ($('uploadformmessage')) {
                      $('uploadformmessage').innerHTML = '';
                  }
                  self.form.userfile.value = '';
                  self.form.title.value = '';
                  self.form.description.value = '';
                  hideElement(self.form);
                  showElement(self.openbutton);
              }}))))));

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
        var localname = self.form.userfile.value;
        if (isEmpty(localname)) {
            $('uploadformmessage').innerHTML = get_string('filenamefieldisrequired');
            return;
        }
        var destname = self.form.title.value;
        if (isEmpty(destname)) {
            $('uploadformmessage').innerHTML = get_string('titlefieldisrequired');
            return;
        }
        if (localname.indexOf('/') > -1) { 
            localname = localname.substring(localname.lastIndexOf('/')+1, localname.length);
        }
        if (!replacefile && self.fileexists(destname)) {
            $('uploadformmessage').innerHTML = get_string('uploadfileexistsreplacecancel');
            // Show replace button
            setDisplayForElement('inline', self.form.replace);
            return;
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
        var collideaction = replacefile ? 'replace' : 'fail';
        appendChildNodes(self.form, INPUT({'type':'hidden', 'name':'collideaction', 'value':collideaction}),
                         INPUT({'type':'hidden', 'name':'parentfolder', 'value':self.folderid}),
                         INPUT({'type':'hidden', 'name':'uploadnumber', 'value':self.nextupload}));

        self.form.submit();

        // Display upload status
        insertSiblingNodesBefore(self.form,
            DIV({'id':'uploadstatusline'+self.nextupload},
                get_string('uploading',[localname,self.foldername,destname])));
        self.nextupload += 1;
    }

    this.getresult = function(data) {
        if (!data.error) {
            message = get_string('Upload complete');
        }
        else {
            message = get_string('Upload failed');
        }
        $('uploadstatusline'+data.uploadnumber).innerHTML = message;
        this.uploadcallback();
    }

    addLoadEvent(this.init);
}