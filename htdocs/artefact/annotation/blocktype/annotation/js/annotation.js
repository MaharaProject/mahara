/**
 * Javascript for the annotation artefact
 *
 * @package    mahara
 * @subpackage blocktype-annotation
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

/**
 * Return the specified request variable from the URL.
 * This should be moved to mahara.js to everyone can use it.
 */
function getURLParameter(variable) {
    var query = window.location.search.substring(1);
    var vars = query.split("&");
    for (var i = 0; i < vars.length; i++) {
        var pair = vars[i].split("=");
        if (pair[0] == variable) {
            return pair[1];
        }
    }
   return false;
}

function isTinyMceUsed(elementname) {
    return (tinyMCE !== undefined && tinyMCE.get(elementname) !== undefined);
}

function initTinyMCE(formname){
    var textareaId = formname + '_message';
    if (isTinyMceUsed(formname)) {
        tinyMCE.execCommand('mceRemoveEditor', false, textareaId);
        tinyMCE.execCommand('mceAddEditor', false, textareaId);
    }
}

function modifyAnnotationFeedbackSuccess(form, data) {
    var formname = form.name;
    var limit    = getURLParameter('limit');
    var offset   = getURLParameter('offset');

    if (limit === false && offset === false) {
        // Pagination is not used.
        limit = null;
        offset = null;
    }

    // Reload the annotation feedback table with the new feedback that's just been made public.

    // Calls the save method on all editor instances before
    // annotation being submitted.
    tinyMCE.triggerSave();
    sendjsonrequest('../artefact/annotation/annotations.json.php',
        {
            'annotationid' : $(formname + '_annotationid').value,
            'viewid'       : $(formname + '_viewid').value,
            'artefactid'   : $(formname + '_artefactid').value,
            'blockid'      : $(formname + '_blockid').value,
            'limit'        : limit,
            'offset'       : offset,
        }, 'GET', function (data) {
            var blockid = $(formname + '_blockid').value;
            // Populate the div.

            (function($) {
                var scope = $('#annotationfeedbackview_' + blockid);
                scope.html(data.data);
                dock.init(scope);
                initTinyMCE(formname);
            })(jQuery);
    });
    // if we are in a modal close it
    if (jQuery('#annotation_feedbacktable_' + $(formname + '_blockid').value).hasClass('modal-docked')) {
        dock.hide();
    }
    formSuccess(form, data);
}

function addAnnotationFeedbackSuccess(form, data) {
    var formname = form.name;
    var blockid  = $(formname + '_blockid').value;
    var limit    = getURLParameter('limit');
    var offset   = getURLParameter('offset');
    var tinymce = $(form.id + '_message');

    if (limit === false && offset === false) {
        // Pagination is not used.
        limit = null;
        offset = null;
    }

    if (typeof(paginator) != 'undefined' && paginator.id == 'annotationfeedback_pagination_' + blockid) {
        // Make sure its using the annotation paginator for its block not the feedback paginator.
        paginator.updateResults(data);
        paginator.alertProxy('pagechanged', data['data']);
    }
    else {
        // Reload the annotation feedback table with the new feedback that's just been entered.

        // Calls the save method on all editor instances before
        // annotation being submitted.
        tinyMCE.triggerSave();
        sendjsonrequest('../artefact/annotation/annotations.json.php',
            {
                'annotationid' : $(formname + '_annotationid').value,
                'viewid'       : $(formname + '_viewid').value,
                'artefactid'   : $(formname + '_artefactid').value,
                'blockid'      : $(formname + '_blockid').value,
                'limit'        : limit,
                'offset'       : offset,
            }, 'GET', function (data) {
                var blockid = $(formname + '_blockid').value;
                // Populate the div
                (function($) {
                    var scope = $('#annotationfeedbackview_' + blockid);
                    scope.html(data.data);
                    dock.init(scope);
                    initTinyMCE(formname);
                })(jQuery);
        });
    }
    // Clear TinyMCE
    if (isTinyMceUsed(formname + '_message')) {
        tinyMCE.activeEditor.setContent('');
    }

    // Clear the textarea (in case TinyMCE is disabled)
    var messageid = 'message';
    if (data.fieldnames && data.fieldnames.message) {
        messageid = data.fieldnames.message;
    }
    $(formname + '_' + messageid).value = '';
    formSuccess(form, data);
}

function addAnnotationFeedbackError(form, data) {
    id = form.id;
    id = id.replace(/^add_/, '').replace(/_form/,'');
    jQuery('#' + id).removeClass('closed').addClass('active');
    formError(form, data);
}
