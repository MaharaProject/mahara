/**
 * Automatically populates the WYSIWYG box on the site pages screen with the
 * content of the appropriate page
 *
 * Author: Nigel McNie
 * Copyright: (C) 2008 Catalyst IT Ltd.
 * Licensed under the GNU GPL 3.0 or later
 */

var oldPageContent = '';
var oldPageName = 'home';
var checkOldContent = false;

function updateWYSIWYGText() {
    if (checkOldContent && oldPageContent != tinyMCE.getContent() && !confirm(get_string('discardpageedits', 'admin'))) {
        $('editsitepage_pagename').value = oldPageName;
        return;
    }
    checkOldContent = true;
    sendjsonrequest(
        'editchangepage.json.php',
        {'pagename' :$('editsitepage_pagename').value},
        'POST',
        function(data) {
            if (!data.error) {
                tinyMCE.setContent(data.content);
                oldPageContent = tinyMCE.getContent();
                oldPageName = $('editsitepage_pagename').value;
            }
        }
    );
}

function connectElements() {
    connect('editsitepage_pagename', 'onchange', updateWYSIWYGText);
}

function contentSaved(form, data) {
    connectElements();
    oldPageContent = tinyMCE.getContent();
    formSuccess(form, data);
}

addLoadEvent(function() {
    connectElements();
    updateWYSIWYGText();
});
