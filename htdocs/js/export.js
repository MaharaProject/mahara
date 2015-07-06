/**
 * JS behaviour for the export UI
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
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

    // Make the export format radio buttons show/hide the includefeedback checkbox
    forEach(getElementsByTagAndClassName('input', 'radio', 'exportformat-buttons'), function(radio) {
        connect(radio, 'onclick', function(e) {
            hideElement($('includefeedback'));
            if (radio.checked) {
                if (radio.value == 'html') {
                    showElement($('includefeedback'));
                }
            }
        });
    });

    // Hook up 'click to preview' links
    forEach(getElementsByTagAndClassName('a', 'viewlink', containers.views.container), function(i) {
        disconnectAll(i);
        setNodeAttribute(i, 'title', 'Click to preview');
        connect(i, 'onclick', function (e) {
            e.stop();
            var href = getNodeAttribute(this, 'href');
            var params = parseQueryString(href.substring(href.indexOf('?') + 1, href.length));
            params['export'] = 1;
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

    insertSiblingNodesBefore(getFirstElementByTagAndClassName('div', null, containers.views.container), checkboxHelperDiv);
});
