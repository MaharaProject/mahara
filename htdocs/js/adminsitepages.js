/**
 * Automatically populates the WYSIWYG box on the site pages screen with the
 * content of the appropriate page
 *
 * Copyright: 2006-2008 Catalyst IT Ltd
 * This file is licensed under the same terms as Mahara itself
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
