
function FileUploader(element, foldername, folderid, uploadcallback) {
    var self = this;
    this.element = element;
    this.foldername = foldername ? foldername : get_string('home');
    this.folderid = folderid;
    this.uploadcallback = uploadcallback;

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
             TR(null,TD({'colspan':2},
              INPUT({'name':'submit','type':'button','value':get_string('upload'),'onclick':self.sendform}),
              INPUT({'name':'replace','type':'button','value':get_string('upload'),'onclick':partial(self.sendform,true)}),
              INPUT({'type':'button','value':get_string('cancel'),'onclick':function () { 
                  self.form.userfile.value = '';
                  self.form.title.value = '';
                  self.form.description.value = '';
                  hideElement(self.form);
                  showElement(self.openbutton);
              }}))),
             TR(null,TD({'colspan':2, 'id':'uploadformmessage'})))));

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

    this.fileexists = function (filename) {
        return true;
    }

    this.sendform = function (replace) {
        $('uploadformmessage').innerHTML = '';
        var localname = self.form.userfile.value;
        if (isEmpty(localname)) {
            $('uploadformmessage').innerHTML = get_string('Filename field is required.');
            return;
        }
        var destname = self.form.title.value;
        if (isEmpty(destname)) {
            $('uploadformmessage').innerHTML = get_string('Title field is required.');
            return;
        }
        if (localname.indexOf('/') > -1) { 
            localname = localname.substring(localname.lastIndexOf('/')+1, localname.length);
        }
        if (!replace && self.fileexists(destname)) {
            $('uploadformmessage').innerHTML = get_string('uploadfileexistsrenamereplacecancel');
            // Show replace button
            showElement(self.form.replace);
            return;
        }

        // Create iframe in which to load the file
        appendChildNodes(self.element,
                         createDOM('iframe',{'name':'iframe'+self.nextupload,
                                             'id':'iframe'+self.nextupload,
                                             'src':'blank.html','style':'display: none;'},[]));
        self.form.target = 'iframe' + self.nextupload;
        //self.form.submit();

        // Display upload status
        insertSiblingNodesBefore(self.form,
            DIV({'id':'uploadstatusline'+self.nextupload},
                get_string('uploading',[localname,self.form.foldername,destname])));
        self.nextupload += 1;
    }


    addLoadEvent(this.init);


    //var formparams = [INPUT({'type':'hidden','name':'uploaddir','value':self.folder}),
    //                      INPUT({'type':'hidden','name':'uploadnum','value':self.nextupload})];

    //var messageline = DIV({'id':'uploadmessage','class':'uploadmessage'});

    //var extrabuttons = DIV({'id':'extrabuttons'});
    //var buttonline = DIV({'id':'buttonline'},uploadbutton,cancelbutton);


}