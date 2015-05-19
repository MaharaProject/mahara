/**
 * Forces full reload of the page if certain site options have been
 * changed
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

// Add here as appropriate
var forceReloadElements = ['sitename', 'lang', 'theme',
                           'defaultaccountlifetime_units',
                           'defaultaccountlifetimeupdate'];
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

    connect('siteoptions_allowpublicviews', 'onclick', update_allowpublicprofiles);
}

// Javascript success handler for the form. Re-wires up the elements
function checkReload(form, data) {
    update_allowpublicprofiles();

    isReloadRequired = false;
    connectElements();
    formSuccess(form, data);
}

function update_allowpublicprofiles() {
    if ($('siteoptions_allowpublicviews').checked) {
        $('siteoptions_allowpublicprofiles').checked = true;
        $('siteoptions_allowpublicprofiles').setAttribute('disabled', 'disabled');
    }
    else {
        $('siteoptions_allowpublicprofiles').removeAttribute('disabled');
    }
}

addLoadEvent(connectElements);
