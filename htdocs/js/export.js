/**
 * JS behaviour for the export UI
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

// TODO: i18n

addLoadEvent(function() {
    removeElementClass($('whatviewsselection'), 'hidden');

    var containers = {
        'views': {'container': $('whatviews'), 'visible': false}
    };
    if ($('whatcollections')) {
        containers.collections = {'container': $('whatcollections'), 'visible': false};
    }

    var radios = [];

    function toggleRadios(state) {
        forEach(radios, function(r) {
            r.disabled = state;
        });
    }
    var enableRadios  = partial(toggleRadios, '');
    var disableRadios = partial(toggleRadios, 'disabled');

    // Make the radio buttons show/hide the view selector
    forEach(getElementsByTagAndClassName('input', 'radio', 'whattoexport-buttons'), function(radio) {
        radios.push(radio);
        connect(radio, 'onclick', function(e) {
            if (radio.checked) {
                for (var c in containers) {
                    if (c != radio.value && containers[c].visible) {
                        disableRadios();
                        containers[c].visible = false;
                        slideUp(containers[c].container, {'duration': 0.5, 'afterFinish': enableRadios});
                        break;
                    }
                }
                if (radio.value != 'all' && !containers[radio.value].visible) {
                    disableRadios();
                    containers[radio.value].visible = true;
                    slideDown(containers[radio.value].container, {
                        'duration': 0.5, 'afterFinish': enableRadios,
                        'beforeSetup': function() {
                            removeElementClass(containers[radio.value].container, 'js-hidden');
                        }
                    });
                }
            }
        });
        // Open the view selector if the views checkbox is select on page load
        if (radio.value != 'all' && radio.checked && !containers[radio.value].visible) {
            containers[radio.value].visible = true;
            removeElementClass(containers[radio.value].container, 'js-hidden');
        }
    });

    // Hook up 'click to preview' links
    forEach(getElementsByTagAndClassName('a', 'viewlink', containers.views.container), function(i) {
        disconnectAll(i);
        setNodeAttribute(i, 'title', 'Click to preview');
        connect(i, 'onclick', function (e) {
            e.stop();
            var href = getNodeAttribute(this, 'href');
            var params = parseQueryString(href.substring(href.indexOf('?') + 1, href.length));
            sendjsonrequest(config['wwwroot'] + 'view/viewcontent.json.php', params, 'POST', partial(showPreview, 'big'));
        });
    });

    // Checkbox helpers
    var checkboxes = getElementsByTagAndClassName('input', 'checkbox', 'whatviews');
    var checkboxHelperDiv = DIV();

    var checkboxSelectAll = $('selection_all');
    connect(checkboxSelectAll, 'onclick', function(e) {
        e.stop();
        forEach(checkboxes, function(i) {
            i.checked = true;
        });
    });

    var checkboxReverseSelection = $('selection_reverse');
    connect(checkboxReverseSelection, 'onclick', function(e) {
        e.stop();
        forEach(checkboxes, function(i) {
            i.checked = !i.checked;
        });
    });

    insertSiblingNodesBefore(getFirstElementByTagAndClassName('table', null, containers.views.container), checkboxHelperDiv);

});
