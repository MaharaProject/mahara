/**
 * General javascript routines for Mahara
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

function checkActiveTab(activeTab) {
    $('a[href="#' + activeTab + '"]').tab('show');
    showTab('#' + activeTab);
}

function showTab(el) {
    var i;
    var x = $(".tab");
    for (i = 0; i < x.length; i++) {
        $(x[i]).addClass("js-hidden");
    }
    $(el).removeClass("js-hidden");
    $(el + '-text').removeClass("js-hidden");

    // Institution legal page specific.
    if (typeof types != 'undefined') {
        // Needed in case an institution has just one type of content (privacy/T&C).
        showNoContentAddOne(el);
        // Needed to keep the same tab active when user is changing the institutions from the institution selector.
        var newurl = updateUrlParameter(location.href, 'fs', el.replace("#", ""));
        history.pushState(null, null, newurl);
    }
}

function showNoContentAddOne(el) {
    $('.nocontent').addClass("js-hidden");
    var activetab = el.replace("#", "");
    if (types.indexOf(activetab) != -1) {
        $('#results').removeClass("js-hidden");
        $('#no-results').addClass("js-hidden");
        $('#no-' + activetab).addClass("js-hidden");
    }
    else {
        $('#results').addClass("js-hidden");
        $('#no-results').removeClass("js-hidden");
        $('#no-' + activetab).removeClass("js-hidden");
        var url = $('#no-results').find('a').prop('href');
        if (url) {
            if (getUrlParameter('fs', url)) {
                var newurl = updateUrlParameter(url, 'fs', activetab);
                $('#no-results').find('a').prop('href', newurl);
            }
        }
    }
}

function reloadUsers() {
    var appendfs = '';
    if (url = getUrlParameter('fs', location.href)) {
        appendfs = '&fs=' + url;
    }
    window.location.href = config.wwwroot + 'admin/users/institutionprivacy.php?institution=' + $('#usertypeselect_institution').val() + appendfs;
}

// User's Legal page: show the submit button if the user changes
// the value of one or more switches from YES to NO.
function showSubmitButton() {
    if ($('body').find(".redraw-consent").length == 0) {
        $('#agreetoprivacy_submit_container').addClass('js-hidden');
        $('#agreetoprivacy_submit').addClass('js-hidden');
    }
    else {
        $('#agreetoprivacy_submit_container').removeClass('js-hidden');
        $('#agreetoprivacy_submit').removeClass('js-hidden');
    }
}