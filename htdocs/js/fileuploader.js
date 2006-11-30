function FileUploader(element, script, callback, foldername, folderid) {
    var self = this;
    this.element = element;
    this.script = script;
    this.foldername = foldername ? foldername : get_string('home');
    this.folderid = folderid;

    this.init = function() {
        self.nextupload = 1;

        // Create the upload form
        self.uploadform = self.initform();

        // Create the button which opens up the upload form
        var buttontext = self.nextupload == 1 ? get_string('uploadfile') : get_string('uploadanotherfile');
        var button = INPUT({'type':'button','value':buttontext});
        button.onclick = function () { 
            hideElement(self.openbutton);
            showElement(self.uploadform);
        };
        self.openbutton = button;

        appendChildNodes(self.element, self.uploadform, self.openbutton);
    }

    this.initform = function () {
        var form = FORM({'method':'post', 'id':'uploadform',
                         'enctype':'multipart/form-data', 'encoding':'multipart/form-data',
                         'action':'upload.php', 'target':'iframe'+self.nextupload});

        var rows = map(self.formrow,
                       [{'text':'destination', 'elem':SPAN({'id':'uploaddest'},self.foldername)},
                        {'text':'file', 'elem':INPUT({'type':'file','name':'userfile'})},
                        {'text':'title', 'elem':INPUT({'type':'text','name':'title'})},
                        {'text':'description', 'elem':INPUT({'type':'text','name':'description'})}]);

        var uploadbutton = INPUT({'type':'button','value':get_string('upload')});
        uploadbutton.onclick = self.sendform;

        var cancelbutton = INPUT({'type':'button','value':get_string('cancel')});
        cancelbutton.onclick = function () { 
            hideElement(self.uploadform);
            showElement(self.openbutton);
        };

        appendChildNodes(form, TABLE(null, TBODY(null, rows)), uploadbutton, cancelbutton);
        hideElement(form);

        return form;
    }

    this.formrow = function (r) {
        return TR(null, TD(null, get_string(r.text) + ':'),
                  TD(null, r.elem));
    }

    this.updatedestination = function (folderid, foldername) {
        self.foldername = foldername;
        self.folderid = folderid;
        $('uploaddest').innerHTML = foldername;
    }

    addLoadEvent(this.init);

    //var formparams = [INPUT({'type':'hidden','name':'uploaddir','value':self.folder}),
    //                      INPUT({'type':'hidden','name':'uploadnum','value':self.nextupload})];

    //var messageline = DIV({'id':'uploadmessage','class':'uploadmessage'});

    //var extrabuttons = DIV({'id':'extrabuttons'});
    //var buttonline = DIV({'id':'buttonline'},uploadbutton,cancelbutton);


}