/**
 * Forces full reload of the page if certain site options have been changed
 *
 * Copyright: 2006-2008 Catalyst IT Ltd
 * This file is licensed under the same terms as Mahara itself
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
