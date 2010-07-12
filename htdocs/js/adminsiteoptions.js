/**
 * Forces full reload of the page if certain site options have been
 * changed
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

// Add here as appropriate
var forceReloadElements = ['sitename', 'lang', 'theme'];
var isReloadRequired = false;

// Disconnects the pieform submit handler and changes the form target back to
// the page itself (rather than pieform's hidden iframe), so a full post/reload
// cycle will happen when the form is submitted
function reloadRequired() {
    isReloadRequired = true;
    disconnectAll('siteoptions');
    $('siteoptions').target = '';
}

// Wires up appropriate elements to cause a full page reload if they're changed
function connectElements() {
    forEach(forceReloadElements, function(element) {
        if ($('siteoptions_' + element)) {
            connect('siteoptions_' + element, 'onchange', reloadRequired);
        }
    });
}

// Javascript success handler for the form. Re-wires up the elements
function checkReload(form, data) {
    isReloadRequired = false;
    connectElements();
    formSuccess(form, data);
}

addLoadEvent(connectElements);
