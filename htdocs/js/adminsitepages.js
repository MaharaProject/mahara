/**
 * Automatically populates the WYSIWYG box on the site pages screen
 * with the content of the appropriate page
 * @source: http://gitorious.org/mahara/mahara
 *
 * @licstart
 * Copyright (C) 2006-2010  Catalyst IT Ltd
 *
 * The JavaScript code in this page is free software: you can
 * redistribute it and/or modify it under the terms of the GNU
 * General Public License (GNU GPL) as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option)
 * any later version.  The code is distributed WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU GPL for more details.
 *
 * As additional permission under GNU GPL version 3 section 7, you
 * may distribute non-source (e.g., minimized or compacted) forms of
 * that code without the copy of the GNU GPL normally required by
 * section 4, provided you include this license notice and a URL
 * through which recipients can access the Corresponding Source.
 * @licend
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
        'editchangepage.json.php',
        {'pagename' :$('editsitepage_pagename').value},
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
