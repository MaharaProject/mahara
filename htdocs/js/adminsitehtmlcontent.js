/**
 * Automatically populates the WYSIWYG box on the site pages screen
 * with the content of the appropriate page
 * @source: http://gitorious.org/mahara/mahara
 *
 * @licstart
 * Copyright (C) 2012  Catalyst IT Ltd
 * Copyright (C) 2012 Lancaster University
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

var oldPageContent  = '';
var oldPageName     = 'additionalhtmlhead';
var checkOldContent = false;

function updateText() {
    if (checkOldContent && oldPageContent != $('editadditionalhtmlcontent_contenthtml').value && !confirm(get_string('discardcontentedits', 'admin'))) {
        $('editadditionalhtmlcontent_contentname').value = oldPageName;
        return;
    }
    checkOldContent = true;
    sendjsonrequest(
        'editchangecontent.json.php',
        {'contentname' : $('editadditionalhtmlcontent_contentname').value},
        'POST',
        function(data) {
            if (!data.error) {
                $('editadditionalhtmlcontent_contenthtml').value = data.content;
                oldPageContent = $('editadditionalhtmlcontent_contenthtml').value;
                oldPageName = $('editadditionalhtmlcontent_contentname').value;
            }
        }
    );
}

function connectElements() {
    connect('editadditionalhtmlcontent_contentname', 'onchange', updateText);
}

/* Pieform callback*/
function contentSaved(form, data) {
    connectElements();
    oldPageContent = $('editadditionalhtmlcontent_contenthtml').value;
    formSuccess(form, data);
}

addLoadEvent(function() {
    connectElements();
    updateText();
});
