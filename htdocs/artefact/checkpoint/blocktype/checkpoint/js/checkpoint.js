/**
 * Javascript for the checkpoint artefact
 *
 * @package    mahara
 * @subpackage blocktype-checkpoint
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
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

function checkpointBlockInit() {
    configureAssessmentCancel();
    configureModalOpen();
    connectCheckpointBlocks();
};

jQuery(window).on('pageupdated', {}, function () {
    configureAssessmentCancel();
    configureModalOpen();
});

function configureModalOpen() {
    $('.js-checkpoint-modal').off('click');
    $('.js-checkpoint-modal').on('click', function (e) {
        e.stopPropagation();
        e.preventDefault();
        // needs to initialize the tinyMCE editor when the block is loaded
        PieformManager.signal('onload');

        var blockid = $(this).data('blockid');
        var formname = $('#add_checkpoint_feedback_form_' + blockid);
        formname = formname.find('form')[0].id;
        dock.show($('#add_checkpoint_feedback_form_' + blockid), false, true);
        if ($(this).data('id')) {
            sendjsonrequest(config.wwwroot + 'artefact/checkpoint/checkpointinfo.json.php', {
                'id': $(this).data('id'),
                'block': blockid,
            }, 'POST', function (data) {
                // Populate the form
                $('#' + formname + '_checkpoint').val(data.data.id);
                // Update TinyMCE
                modifyTinyMCEContent(formname, data, data.data.message);
            });
        }
        else {
            $('#' + formname + '_checkpoint').val(0);
            modifyTinyMCEContent(formname, null, '');
        }
    });
}

function configureAssessmentCancel() {
    $('.feedbacktable.modal .cancel').off('click');
    $('.feedbacktable.modal .cancel').on('click', function (e) {
        e.stopPropagation();
        e.preventDefault();
        dock.hide();
    });
};

function modifyCheckpointFeedbackSuccess(form, data) {
    var formname = form.name;
    var limit = getUrlParameter('limit');
    var offset = getUrlParameter('offset');

    // Reload the checkpoint feedback table with the new feedback that's just been made public.

    // Calls the save method on all editor instances
    tinyMCE.triggerSave();
    sendjsonrequest(config.wwwroot + 'artefact/checkpoint/checkpoint.json.php', {
        'checkpoint': jQuery('#' + formname + '_checkpoint').val(),
        'view': jQuery('#' + formname + '_view').val(),
        'block': jQuery('#' + formname + '_block').val(),
        'limit': limit,
        'offset': offset,
    }, 'POST', function (data) {
        var blockid = jQuery('#' + formname + '_block').val();
        // Populate the div.
        (function ($) {
            var scope = $('#checkpointfeedbacktable' + blockid);
            scope.html(data.data.tablerows);
            var scopepagination = scope.parent().find('.pagination-wrapper');
            scopepagination.html(data.data.pagination);
            dock.init(scope);
            initTinyMCE(formname);
            configureModalOpen();
        })(jQuery);
    });
    // if we are in a modal close it
    if (jQuery('#checkpoint_feedbacktable_' + jQuery('#' + formname + '_blockid').val()).hasClass('modal-docked')) {
        dock.hide();
    }
    formSuccess(form, data);
}

function addCheckpointFeedbackSuccess(form, data) {
    var formname = form.name;
    var blockid = jQuery('#' + formname + '_block').val();
    var limit = getUrlParameter('limit');
    var offset = getUrlParameter('offset');
    var tinymce = jQuery('#' + form.id + '_message');
    var checkpointpaginator = window['checkpointpaginator' + blockid];
    if (typeof (checkpointpaginator) != 'undefined' && checkpointpaginator.id == 'checkpoint_pagination_' + blockid) {
        // Make sure its using the checkpoint paginator.
        checkpointpaginator.updateResults(data);
        checkpointpaginator.alertProxy('pagechanged', data['data']);
        configureModalOpen();
    }
    else {
        // Reload the checkpoint feedback table with the new feedback that's just been entered.
        // Calls the save method on all editor instances before
        // checkpoint being submitted.
        tinyMCE.triggerSave();
        sendjsonrequest(config.wwwroot + 'artefact/checkpoint/checkpoint.json.php',
        {
            'block': jQuery('#' + formname + '_block').val(),
            'limit': limit,
            'offset': offset,
        }, 'POST', function (data) {
            var blockid = jQuery('#' + formname + '_block').val();
            // Populate the div
            (function ($) {
                var scope = $('#checkpointfeedbacktable' + blockid);
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
function addCheckpointFeedbackError(form, data) {
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

/**
 * Connect checkpoint blocks to the dropdown achievement submit form
 */
function connectCheckpointBlocks() {
    console.log('connect')
    // Activity page checkpoint blocks achievement level interaction logic
    let achievement_forms = $('.block');
    let select_submit_pairs = [];
    let submit_btns = $(achievement_forms).find('.submit input[id^="achievement_form"]');
    let select_elems = $(achievement_forms).find('select');
    let num_of_checkpoints = submit_btns.length;
    console.log(num_of_checkpoints)

    // Collect up the block, submit button, and select for each checkpoint block
    for (let i = 0; i < num_of_checkpoints; i++) {
        select_submit_pairs.push({
            blockId: submit_btns[i].id.match(/\d+/)[0],
            submit: submit_btns[i],
            select: select_elems[i]
        });
    };

    // Figure out the click interaction
    select_submit_pairs.forEach(checkpoint => {
        $(checkpoint.submit).on('click', function (event) {
            let level = $(checkpoint.select).find('option:selected').val();
            let form = $(checkpoint.submit).parents().find('form');
            let block_id = $(form).find('input[name="block"]')
            event.preventDefault();
            event.stopPropagation();

            sendjsonrequest(
                config.wwwroot + 'artefact/checkpoint/updatecheckpoint.json.php',
                {
                    'level': level,
                    'blockid': checkpoint.blockId
                },
                'POST', data => {
                    $('#checkpoint_levels_' + checkpoint.blockId).html(data.html);
                }, function (error) {
                    console.log(error);
                });
        });
    });
}