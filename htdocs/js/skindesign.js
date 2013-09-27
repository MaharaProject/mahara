/**
 * Javascript for the skin design form
 * @source: http://gitorious.org/mahara/mahara
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

addLoadEvent(function() {
    var fieldsets = getElementsByTagAndClassName('fieldset', null, 'designskinform');

    // Grab the legends
    var legends = getElementsByTagAndClassName('legend', null, 'designskinform');

    var tabs = [];
    forEach(legends, function(legend) {
        var a = A({'href': ''}, scrapeText(legend));
        legend.parentNode.tabLink = a;
        // Pieforms is unhelpful with legend/fieldset ids; get it from children
        var fsid = 'viewbuttons';
        var row = getFirstElementByTagAndClassName('tr', 'html', legend.parentNode);
        if (row) {
            fsid = getNodeAttribute(row, 'id').replace(/^designskinform_(.*)description_container$/, '$1');
        }
        a.id = fsid + '_a';
        connect(a, 'onclick', function(e) {
            forEach(fieldsets, function(fieldset) {
                if (fieldset == legend.parentNode) {
                    addElementClass(fieldset.tabLink.parentNode, 'current-tab');
                    addElementClass(fieldset.tabLink, 'current-tab');
                    removeElementClass(fieldset, 'safe-hidden');
                    removeElementClass(fieldset, 'collapsed');
                    $('designskinform_fs').value = fsid;
                }
                else {
                    removeElementClass(fieldset.tabLink.parentNode, 'current-tab');
                    removeElementClass(fieldset.tabLink, 'current-tab');
                    addElementClass(fieldset, 'safe-hidden');
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
    //removeElement('designskinform_topsubmit_container');

    // last part is the submit buttons
    appendChildNodes('designskinform',
        tabUL, DIV({'class': 'designskin-fieldsets subpage'}, fieldsets), getFirstElementByTagAndClassName('td', null, 'designskinform_submit_container').childNodes
    );
    removeElement(
        getFirstElementByTagAndClassName('table', null, 'designskinform')
    );

    // Connect events to each form element to check if they're changed and set
    // a dirty flag
    var formDirty = false;
    forEach(getElementsByTagAndClassName(null, null, 'designskinform'), function(i) {
        if (i.tagName != 'INPUT' && i.tagName != 'TEXTAREA') return;
        if (!hasElementClass(i, 'text') && !hasElementClass(i, 'textarea')) return;
        connect(i, 'onchange', function(e) {
            formDirty = true;
        });
    });

    // Now unhide the profile form
    hideElement('viewskin-loading');
    $('designskinform').style.position = 'static';
    $('designskinform').style.visibility = 'visible';
});

// Add a stylesheet for styling in JS only
// See http://www.phpied.com/dynamic-script-and-style-elements-in-ie/
var styleNode = createDOM('style', {'type': 'text/css'});
var rule = '#designskinform { visibility: hidden; position: absolute; top: 0; }';
// Workaround for IE (all versions)
if (document.all && !window.opera) {
    styleNode.styleSheet.cssText = rule;
}
else {
    appendChildNodes(styleNode, rule);
}
appendChildNodes(getFirstElementByTagAndClassName('head'), styleNode);
