/**
 * Javascript for the profile form
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

// Stuff
addLoadEvent(function() {
    var fieldsets = getElementsByTagAndClassName('fieldset', null, 'profileform');

    // Grab the legends
    var legends = getElementsByTagAndClassName('legend', null, 'profileform');

    var tabs = [];
    forEach(legends, function(legend) {
        var a = A({'href': ''}, scrapeText(legend));
        legend.parentNode.tabLink = a;
        // Pieforms is unhelpful with legend/fieldset ids; get it from children
        var fsid = 'general';
        var row = getFirstElementByTagAndClassName('tr', 'html', legend.parentNode);
        if (row) {
            fsid = getNodeAttribute(row, 'id').replace(/^profileform_(.*)description_container$/, '$1');
        }
        a.id = fsid + '_a';
        connect(a, 'onclick', function(e) {
            forEach(fieldsets, function(fieldset) {
                if (fieldset == legend.parentNode) {
                    addElementClass(fieldset.tabLink.parentNode, 'current-tab');
                    addElementClass(fieldset.tabLink, 'current-tab');
                    removeElementClass(fieldset, 'safe-hidden');
                    removeElementClass(fieldset, 'collapsed');
                    $('profileform_fs').value = fsid;
                }
                else {
                    removeElementClass(fieldset.tabLink.parentNode, 'current-tab');
                    removeElementClass(fieldset.tabLink, 'current-tab');
                    addElementClass(fieldset, 'collapsed');
                }
            });
            e.stop();
        });
        tabs.push(LI(null, a));
    });
    var tabUL = UL({'class': 'in-page-tabs'}, tabs);

    forEach(fieldsets, function(fieldset) {
        if (hasElementClass(fieldset, 'collapsed')) {
            addElementClass(fieldset, 'safe-hidden');
            removeElementClass(fieldset, 'collapsed');
        }
        else {
            // not collapsed by default, probably was the default one to show
            addElementClass(fieldset.tabLink.parentNode, 'current-tab');
            addElementClass(fieldset.tabLink, 'current-tab');
        }
    });

    forEach(legends, function(legend) {
        addElementClass(legend, 'hidden');
    });

    // Remove the top submit buttons
    removeElement('profileform_topsubmit_container');

    // last part is the submit buttons
    appendChildNodes('profileform',
        tabUL, DIV({'class': 'profile-fieldsets subpage'}, fieldsets), getFirstElementByTagAndClassName('td', null, 'profileform_submit_container').childNodes
    );
    removeElement(
        getFirstElementByTagAndClassName('table', null, 'profileform')
    );

    // Connect events to each form element to check if they're changed and set
    // a dirty flag
    var formDirty = false;
    forEach(getElementsByTagAndClassName(null, null, 'profileform'), function(i) {
        if (i.tagName != 'INPUT' && i.tagName != 'TEXTAREA') return;
        if (!hasElementClass(i, 'text') && !hasElementClass(i, 'textarea')) return;
        connect(i, 'onchange', function(e) {
            formDirty = true;
        });
    });

    // Now unhide the profile form
    hideElement('profile-loading');
    $('profileform').style.position = 'static';
    $('profileform').style.visibility = 'visible';
});

// Add a stylesheet for styling in JS only
// See http://www.phpied.com/dynamic-script-and-style-elements-in-ie/
var styleNode = createDOM('style', {'type': 'text/css'});
var rule = '#profileform { visibility: hidden; position: absolute; top: 0; }';
// Stupid IE workaround
if (document.all && !window.opera) {
    styleNode.styleSheet.cssText = rule;
}
else {
    appendChildNodes(styleNode, rule);
}
appendChildNodes(getFirstElementByTagAndClassName('head'), styleNode);
