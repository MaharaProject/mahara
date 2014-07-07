/**
 * Javascript to change Pieforms fieldsets to tabs
 * @source: http://gitorious.org/mahara/mahara
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

// This will automatically turn the fieldsets of a form with class 'jstabs' into responsive tabs
(function() {
    function addStyles(formid) {
        // Add a stylesheet for styling in JS only
        // See http://www.phpied.com/dynamic-script-and-style-elements-in-ie/
        var styleNode = createDOM('style', {'type': 'text/css'});
        var rule = '#' + formid + ' { visibility: hidden; position: absolute; top: 0; }';
        // Stupid IE workaround
        if (document.all && !window.opera) {
            styleNode.styleSheet.cssText = rule;
        }
        else {
            appendChildNodes(styleNode, rule);
        }
        appendChildNodes(getFirstElementByTagAndClassName('head'), styleNode);
    }

    addLoadEvent(function() {
        var formelement = getFirstElementByTagAndClassName('form', 'jstabs');
        if (!formelement) {
            return;
        }
        var formid = formelement.getAttribute('id');

        addStyles(formid);

        insertSiblingNodesAfter(formid, DIV({'id': formid + '-loading'}, IMG({'src': config.theme['images/loading.gif'], 'alt': ''}), ' ', get_string('loading')));

        var fieldsets = getElementsByTagAndClassName('fieldset', null, formid);

        // Grab the legends
        var legends = getElementsByTagAndClassName('legend', null, formid);
        var isOpen = 0;
        var tabs = [];
        forEach(legends, function(legend) {
            var a = A({'href': ''}, scrapeText(legend));
            var accessibleText = SPAN({'class':'accessible-hidden'}, '(' + get_string('tab') + ')');
            appendChildNodes(a, accessibleText);

            legend.parentNode.tabLink = a;
            legend.parentNode.tabAccessibleText = accessibleText;

            // Pieforms is unhelpful with legend/fieldset ids; get it from children
            var fsid = 'general';
            var row = getFirstElementByTagAndClassName('tr', 'html', legend.parentNode);
            if (row) {
                fsid = getNodeAttribute(row, 'id').replace(new RegExp('^' + formid + '_(.*)description_container'), '$1');
            }

            a.id = fsid + '_a';
            connect(a, 'onclick', function(e) {
                forEach(fieldsets, function(fieldset) {
                    if (fieldset == legend.parentNode) {
                        addElementClass(fieldset.tabLink.parentNode, 'current-tab');
                        addElementClass(fieldset.tabLink, 'current-tab');
                        removeElementClass(fieldset, 'safe-hidden');
                        removeElementClass(fieldset, 'collapsed');
                        fieldset.tabAccessibleText.innerHTML = '(' + get_string('tab') + ' ' + get_string('selected') + ')';
                        $j(fieldset).find(':input').first().focus();
                        $(formid + '_fs').value = fsid;
                    }
                    else if (hasElementClass(fieldset.tabLink.parentNode, 'current-tab')) {
                        removeElementClass(fieldset.tabLink.parentNode, 'current-tab');
                        removeElementClass(fieldset.tabLink, 'current-tab');
                        addElementClass(fieldset, 'collapsed');
                        addElementClass(fieldset, 'safe-hidden');
                        fieldset.tabAccessibleText.innerHTML = '(' + get_string('tab') + ')';
                    }
                });
                if (isOpen == 1) {
                    removeElementClass(tabDIV, 'expand');
                    isOpen = 0;
                }
                e.stop();
            });
            tabs.push(LI(null, a));
        });
        var tabUL = UL({'class': 'in-page-tabs'}, tabs);
        var tabTitleSpan = SPAN({'class': 'rd-tab'});
        var tabTitleLink = A({'href': '#'}, get_string('tabs'), tabTitleSpan);
        tabDIV = DIV({'id': 'in-page-tabs-wrap', 'class': 'tabswrap'}, H3({'class': 'rd-tab-title'}, tabTitleLink), tabUL);
        connect(tabTitleLink, 'onclick', function(e) {
            e.stop();
            if (isOpen == 0) {
                addElementClass(tabDIV, 'expand');
                getFirstElementByTagAndClassName('a', null, tabUL).focus();
            }
            else {
                removeElementClass(tabDIV, 'expand');
            }
            isOpen = 1 - isOpen;
        });

        forEach(fieldsets, function(fieldset) {
            if (hasElementClass(fieldset, 'collapsed')) {
                addElementClass(fieldset, 'safe-hidden');
            }
            else {
                // not collapsed by default, probably was the default one to show
                addElementClass(fieldset.tabLink.parentNode, 'current-tab');
                addElementClass(fieldset.tabLink, 'current-tab');
                fieldset.tabAccessibleText.innerHTML = '(' + get_string('tab') + ' ' + get_string('selected') + ')';
            }
        });

        forEach(legends, function(legend) {
            addElementClass(legend, 'hidden');
        });

        // Remove the top submit buttons
        if ($(formid + '_topsubmit_container')) {
            removeElement(formid + '_topsubmit_container');
        }

        // last part is the submit buttons
        appendChildNodes(formid,
            tabDIV, DIV({'class': 'tabbed-fieldsets subpage'}, fieldsets), getFirstElementByTagAndClassName('td', null, formid + '_submit_container').childNodes
        );
        removeElement(
            getFirstElementByTagAndClassName('table', null, formid)
        );

        // Make the tabs responsive
        if (typeof responsiveNav === 'function') {
            responsiveNav($j('.tabswrap ul li'), $j('.tabswrap'));
        }

        // Now unhide the form
        hideElement(formid + '-loading');
        $(formid).style.position = 'static';
        $(formid).style.visibility = 'visible';
    });
})();
