/**
 * Automatically populates the WYSIWYG box on the site pages screen
 * with the content of the appropriate page
 * @source: http://gitorious.org/mahara/mahara
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

var oldPageContent = '';
var oldPageName = 'home';
var checkOldContent = false;

function updateWYSIWYGText() {
    if (checkOldContent && oldPageContent != tinyMCE.activeEditor.getContent() && !confirm(get_string('discardpageedits', 'admin'))) {
        $('editsitepage_pagename').value = oldPageName;
        return;
    }
    if (!tinyMCE.isMSIE) {
        // Disable changed content check for IE (see below)
        checkOldContent = true;
    }
    sendjsonrequest(
        'editchangecontent.json.php',
        {'contentname' :$('editsitepage_pagename').value},
        'POST',
        function(data) {
            if (!data.error) {
                tinyMCE.activeEditor.setContent(data.content);
                oldPageContent = tinyMCE.activeEditor.getContent();
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
    if (!tinyMCE.isMSIE) {
        // Disabling changed content check for IE; Need to work out
        // why the getBody() call in getContent fails to return the
        // body element.
        oldPageContent = tinyMCE.activeEditor.getContent();
    }
    formSuccess(form, data);
}

addLoadEvent(function() {
    connectElements();
    updateWYSIWYGText();
});
