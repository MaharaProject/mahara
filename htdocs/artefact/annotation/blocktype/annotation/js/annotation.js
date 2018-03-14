/**
 * Javascript for the annotation artefact
 *
 * @package    mahara
 * @subpackage blocktype-annotation
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */



function isTinyMceUsed(elementname) {
    return (typeof tinyMCE !== 'undefined' && typeof tinyMCE.get(elementname) !== 'undefined');
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
    var limit    = getUrlParameter('limit');
    var offset   = getUrlParameter('offset');

    // Reload the annotation feedback table with the new feedback that's just been made public.

    // Calls the save method on all editor instances before
    // annotation being submitted.
    tinyMCE.triggerSave();
    sendjsonrequest(config.wwwroot + 'artefact/annotation/annotations.json.php',
        {
            'annotationid' : jQuery('#' + formname + '_annotationid').val(),
            'viewid'       : jQuery('#' + formname + '_viewid').val(),
            'artefactid'   : jQuery('#' + formname + '_artefactid').val(),
            'blockid'      : jQuery('#' + formname + '_blockid').val(),
            'limit'        : limit,
            'offset'       : offset,
        }, 'GET', function (data) {
            var blockid = jQuery('#' + formname + '_blockid').val();
            // Populate the div.

            (function($) {
                var scope = $('#annotationfeedbackview_' + blockid);
                scope.html(data.data);
                dock.init(scope);
                initTinyMCE(formname);
            })(jQuery);
    });
    // if we are in a modal close it
    if (jQuery('#annotation_feedbacktable_' + jQuery('#' + formname + '_blockid').val()).hasClass('modal-docked')) {
        dock.hide();
    }
    formSuccess(form, data);
}

function addAnnotationFeedbackSuccess(form, data) {
    var formname = form.name;
    var blockid  = jQuery('#' + formname + '_blockid').val();
    var limit    = getUrlParameter('limit');
    var offset   = getUrlParameter('offset');
    var tinymce = jQuery('#' + form.id + '_message');

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
        sendjsonrequest(config.wwwroot + 'artefact/annotation/annotations.json.php',
            {
                'annotationid' : jQuery('#' + formname + '_annotationid').val(),
                'viewid'       : jQuery('#' + formname + '_viewid').val(),
                'artefactid'   : jQuery('#' + formname + '_artefactid').val(),
                'blockid'      : jQuery('#' + formname + '_blockid').val(),
                'limit'        : limit,
                'offset'       : offset,
            }, 'GET', function (data) {
                var blockid = jQuery('#' + formname + '_blockid').val();
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
    jQuery('#' + formname + '_' + messageid).val('');
    formSuccess(form, data);
}

function addAnnotationFeedbackError(form, data) {
    id = form.id;
    id = id.replace(/^add_/, '').replace(/_form/,'');
    jQuery('#' + id).removeClass('closed').addClass('active');
    formError(form, data);
}
