// Stuff
addLoadEvent(function() {
    var fieldsets = getElementsByTagAndClassName('fieldset', null, 'profileform');

    // Grab the legends
    var legends = getElementsByTagAndClassName('legend', null, 'profileform');

    var tabs = [];
    forEach(legends, function(legend) {
        var a = A({'href': ''}, legend.firstChild);
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

    connect('cancel_profileform_submit', 'onclick', function(e) {
        if (formDirty) {
            if (!confirm(get_string('loseyourchanges'))) {
                e.stop();
            }
        }
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
