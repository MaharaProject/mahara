/**
 * Javascript for the peerassessment artefact
 *
 * @package    mahara
 * @subpackage blocktype-peerassessment
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */



function isTinyMceUsed(elementname) {
    return (typeof tinyMCE !== 'undefined' && typeof tinyMCE.get(elementname) !== 'undefined');
}

function initTinyMCE(formname) {
    var textareaId = formname + '_message';
    if (isTinyMceUsed(formname)) {
        tinyMCE.execCommand('mceRemoveEditor', false, textareaId);
        tinyMCE.execCommand('mceAddEditor', false, textareaId);
    }
}

$(function() {
    configureAssessmentCancel();
    configureModalOpen();
});

jQuery(window).on('pageupdated', {}, function() {
    configureAssessmentCancel();
    configureModalOpen();
});

function configureModalOpen() {
    $('.js-peerassessment-modal').off('click');
    $('.js-peerassessment-modal').on('click', function(e) {
        e.stopPropagation();
        e.preventDefault();
        var blockid = $(this).data('blockid');
        var formname = $('#assessment_feedbackform_' + blockid).find('form')[0].id;
        dock.show($('#assessment_feedbackform_' + blockid), false, true);
        if ($(this).data('id')) {
            sendjsonrequest(config.wwwroot + 'artefact/peerassessment/assessmentinfo.json.php', {
                'id' : $(this).data('id'),
                'block' : blockid,
            }, 'POST', function (data) {
                // Populate the form
                $('#' + formname + '_assessment').val(data.data.id);
                // Update TinyMCE
                modifyTinyMCEContent(formname, data, data.data.message);
            });
        }
        else {
            $('#' + formname + '_assessment').val(0);
            modifyTinyMCEContent(formname, null, '');
        }
    });
}

function configureAssessmentCancel() {
    $('.feedbacktable.modal .cancel').off('click');
    $('.feedbacktable.modal .cancel').on('click', function(e) {
        e.stopPropagation();
        e.preventDefault();
        dock.hide();
    });
};

function modifyPeerassessmentSuccess(form, data) {
    var formname = form.name;
    var limit    = getUrlParameter('limit');
    var offset   = getUrlParameter('offset');

    // Reload the peerassessment feedback table with the new feedback that's just been made public.

    // Calls the save method on all editor instances
    tinyMCE.triggerSave();
    sendjsonrequest(config.wwwroot + 'artefact/peerassessment/peerassessment.json.php', {
        'assessment' : jQuery('#' + formname + '_assessment').val(),
        'view'       : jQuery('#' + formname + '_view').val(),
        'block'      : jQuery('#' + formname + '_block').val(),
        'limit'      : limit,
        'offset'       : offset,
    }, 'POST', function (data) {
        var blockid = jQuery('#' + formname + '_block').val();
            // Populate the div.
            (function($) {
                var scope = $('#assessmentfeedbacktable' + blockid);
                scope.html(data.data.tablerows);
                var scopepagination = scope.parent().find('.pagination-wrapper');
                scopepagination.html(data.data.pagination);
                dock.init(scope);
                initTinyMCE(formname);
                configureModalOpen();
            })(jQuery);
    });
    // if we are in a modal close it
    if (jQuery('#assessment_feedbacktable_' + jQuery('#' + formname + '_blockid').val()).hasClass('modal-docked')) {
        dock.hide();
    }
    formSuccess(form, data);
}

function addPeerassessmentSuccess(form, data) {
    var formname = form.name;
    var blockid  = jQuery('#' + formname + '_block').val();
    var limit    = getUrlParameter('limit');
    var offset   = getUrlParameter('offset');
    var tinymce = jQuery('#' + form.id + '_message');
    var assessmentpaginator = window['assessmentpaginator' + blockid];
    if (typeof(assessmentpaginator) != 'undefined' && assessmentpaginator.id == 'peerassessment_pagination_' + blockid) {
        // Make sure its using the peerassessment paginator.
        assessmentpaginator.updateResults(data);
        assessmentpaginator.alertProxy('pagechanged', data['data']);
        configureModalOpen();
    }
    else {
        // Reload the peerassessment feedback table with the new feedback that's just been entered.
        // Calls the save method on all editor instances before
        // assessment being submitted.
        tinyMCE.triggerSave();
        sendjsonrequest(config.wwwroot + 'artefact/peerassessment/peerassessment.json.php',
            {
                'block'      : jQuery('#' + formname + '_block').val(),
                'limit'        : limit,
                'offset'       : offset,
            }, 'POST', function (data) {
                var blockid = jQuery('#' + formname + '_block').val();
                // Populate the div
                (function($) {
                    var scope = $('#assessmentfeedbacktable' + blockid);
                    scope.html(data.data.tablerows);
                    var scopepagination = scope.parent().find('.pagination-wrapper');
                    scopepagination.html(data.data.pagination);
                    dock.init(scope);
                    initTinyMCE(formname);
                    configureModalOpen();
                })(jQuery);
        });
    }
    dock.hide();
    // Clear TinyMCE
    modifyTinyMCEContent(formname, data, '');
    formSuccess(form, data);
}

function modifyTinyMCEContent(formname, data, content) {
    if (isTinyMceUsed(formname + '_message')) {
        tinyMCE.get(formname + '_message').setContent(content);
    }

    // Clear the textarea (in case TinyMCE is disabled)
    var messageid = 'message';
    if (data && data.fieldnames && data.fieldnames.message) {
        messageid = data.fieldnames.message;
    }
    jQuery('#' + formname + '_' + messageid).val(content);
}

/*
 * This called when data of submitted feedback form are invalid
 * This shows the tinymce editor and error message
 */
function addPeerassessmentError(form, data) {
    var formname = form.id;
    if (isTinyMceUsed()) {
        var mce = tinyMCE.get(formname + '_message');
        mce.show();
        jQuery('.mce-toolbar.mce-first').siblings().addClass('hidden');
        mce.focus();
    }
    if (jQuery('#' + formname).hasClass('modal-docked')) {
        jQuery('#' + formname).removeClass('closed').addClass('active');
    }
    configureAssessmentCancel();
    formError(form, data);
}
