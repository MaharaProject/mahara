// Stuff
addLoadEvent(function() {
    var fieldsets = getElementsByTagAndClassName('fieldset', null, 'profileform');

    // Grab the legends
    var legends = getElementsByTagAndClassName('legend', null, 'profileform');

    var tabs = [];
    forEach(legends, function(legend) {
        var a = legend.firstChild;
        legend.parentNode.tabLink = a;
        a.id = legend.id + '_a';
        disconnectAll(a);
        connect(a, 'onclick', function(e) {
            forEach(fieldsets, function(fieldset) {
                if (fieldset == legend.parentNode) {
                    addElementClass(fieldset.tabLink, 'current-tab');
                    removeElementClass(fieldset, 'safe-hidden');
                }
                else {
                    removeElementClass(fieldset.tabLink, 'current-tab');
                    addElementClass(fieldset, 'safe-hidden');
                }
            });
            e.stop();
        });
        tabs.push(LI(null, a));
    });
    var tabUL = UL({'class': 'profile-tabs'}, tabs);

    forEach(fieldsets, function(fieldset) {
        if (hasElementClass(fieldset, 'collapsed')) {
            addElementClass(fieldset, 'safe-hidden');
            removeElementClass(fieldset, 'collapsed');
        }
        else {
            // not collapsed by default, probably was the default one to show
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
        tabUL, DIV({'class': 'profile-fieldsets'}, fieldsets), getFirstElementByTagAndClassName('td', null, 'profileform_submit_container').childNodes
    );
    removeElement(
        getFirstElementByTagAndClassName('table', null, 'profileform')
    );
});
