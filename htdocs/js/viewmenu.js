/**
 * Javascript for the view menu
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */
/*
 * This called when data of submitted feedback form are invalid
 * This shows the tinymce editor and error message
 */
function addFeedbackError(form, data) {
    if (isTinyMceUsed() && $('#configureblock').hasClass('closed')) {
        var mce = tinyMCE.get('add_feedback_form_message');
        mce.show();
        jQuery('.mce-toolbar.mce-first').siblings().toggleClass('d-none');
        mce.focus();
    }
    // The comment block modal will try to close but since it has errors keep it open
    if (form['id'] == 'add_feedback_form') {
        if (jQuery('#feedback-form').hasClass('modal-docked')) {
            jQuery('#feedback-form').removeClass('closed').addClass('active');
        }
    }
    // Error was made on view page, display error messages there
    if ( $('#configureblock').hasClass('closed') && !$('#feedback-form').length ) {
        formError(form, data);
    }
    else {
        // Error was made in a modal, find out if comment block or configure block and display error message there
        var errmsg = get_string('errorprocessingform', 'mahara');
        if ($('#configureblock').hasClass('active')) {
            $('#modal_messages').addClass('alert alert-danger').html(errmsg);
        }
        else if ($('#feedback-form').hasClass('active')) {
            $('#comment_modal_messages').addClass('alert alert-danger').html(errmsg);
        }
    }

    // Re-attach js events that weren't passed through
    if ($('#configureblock').hasClass('active')) {
        set_up_modal_events();
    }
}

function addFeedbackSuccess(form, data) {

    if (data.data.baseplacement) {
        paginator.updateResults(data);
    }
    // Clear rating from previous submission
    jQuery('#add_feedback_form_rating_container input.star').each(function() {
        jQuery(this).prop('checked', false);
    });
    if (data.data.baseplacement) {
        paginator.alertProxy('pagechanged', data['data']);
    }

    // Clear add feedback form TinyMCE
    if (isTinyMceUsed()) {
        var currentMCE = tinyMCE.get('add_feedback_form_message');
        currentMCE.setContent('');
    }
    // Clear the textarea (in case TinyMCE is disabled)
    var messageid = 'message';
    if (data.fieldnames && data.fieldnames.message) {
        messageid = data.fieldnames.message;
    }
    jQuery('#add_feedback_form_' + messageid).val('');

    // Clear the "Make public" switch back to its default "public" setting
    jQuery('input#add_feedback_form_ispublic').prop('checked', true);

    // need to change the watchlist link
    if (data.data.updatelink) {
        // we must make safe the update link on php side
        jQuery('#toggle_watchlist_link').html(data.data.updatelink);
    }

    // Update the comment link so the correct number of comments is displayed
    var commentlink = '';
    if (data.data.blockid) {
        commentlink = $('.commentlink').filter("[data-blockid=" + data.data.blockid + "][data-artefactid=" + data.data.artefact + "]");
    }
    else {
        commentlink = $('.commentlink').filter("[data-artefactid=" + data.data.artefact + "]");
    }
    var newlink = '<span class="icon icon-comments" role="presentation" aria-hidden="true"></span>';
    if (!(commentlink.closest('div[class*=block-header]').hasClass('bh-displayiconsonly'))) {
        newlink += ' ' + get_string('commentsanddetails', 'artefact.comment', data.data.count);
    }
    else {
        newlink += ' (' + data.data.count + ')';
        newlink += '<span class="bh-margin-left icon icon-search-plus" role="presentation" aria-hidden="true"></span>';
    }
    commentlink.html(newlink);

    resetFeedbackReplyto();
    formSuccess(form, data);

    // Check if the form is displayed inside a modal
    // then close the modal
    if (jQuery('#feedback-form').length) {
        dock.hide();
        // update the size of the comment block
        updateBlockSizes();
    }
}

function objectionSuccess(form, data) {
    jQuery('#objection_form_message').val('');
    formSuccess(form, data);
    // close the form when the form is submited
    // Using bootstrap modal
    if (jQuery('#report-form').length) {
        jQuery('#report-form').modal('hide');
    }
    // Update the objection menu link to be message sent one
    if (jQuery('#objection_link').length && typeof data.objection_cancelled == 'undefined') {
        jQuery('#objection_link').parent().html('<span class="nolink"><span class="icon icon-flag text-danger left" role="presentation" aria-hidden="true"></span>' + get_string_ajax('objectionablematerialreported', 'mahara') + '</span>');
    }
}

function reviewSuccess(form, data) {
    jQuery('#review_form_message').val('');
    formSuccess(form, data);
    // close the form when the form is submited
    // Using bootstrap modal
    if (jQuery('#review-form').length) {
        jQuery('#review-form').modal('hide');
    }
}

function resetFeedbackReplyto() {
    jQuery('#comment_reply_parent').hide();
    jQuery('#add_feedback_form_replyto').val('');
    jQuery('#add_feedback_form_ispublic_container .form-switch').show().removeClass('d-none');
    jQuery('#add_feedback_form_ispublic_container .add_feedback_form_privacy_message').remove();
}

function isTinyMceUsed() {
    return (typeof(tinyMCE) != 'undefined' && typeof(tinyMCE.get('add_feedback_form_message')) != 'undefined');
}

// Attach all js events to the comments and details modal
function set_up_modal_events() {

    var modal_textarea_id = null;
    $('#configureblock').find('textarea.wysiwyg').each(function() {
        modal_textarea_id = $(this).attr('id');

        if (isTinyMceUsed()) {
            // Remove any existing tinymce
            tinymce.EditorManager.execCommand('mceRemoveEditor', true, modal_textarea_id);
            // Attach tinymce
            tinymce.EditorManager.execCommand('mceAddEditor',true, modal_textarea_id);
        }
    });

    $('#configureblock .submitcancel.submit').off('click');
    $('#configureblock .submitcancel.submit').on('click', function(e) {

        if (isTinyMceUsed()) {
            if (tinymce.activeEditor.getContent()) {
                $('#configureblock').find('textarea.wysiwyg').each(function() {
                    modal_textarea_id = $(this).attr('id');
                    tinymce.EditorManager.execCommand('mceRemoveEditor', true, modal_textarea_id);
                });
                dock.hide();
            }
        }
        else {
            var hasEmptyFields = false;
            // Comment text area
            $('#configureblock').find('textarea.wysiwyg').each(function() {
                if ($(this).val().length === 0) {
                    hasEmptyFields = true;
                }
            });
            // Name text input
            $('#configureblock .required').find(':text').each(function() {
                if ($(this).val().length === 0) {
                    hasEmptyFields = true;
                }
            });

            if (!hasEmptyFields) {
                dock.hide();
            }
        }

        $("#configureblock input:file").each(function() {
            var element = $(this);
            if (element.val() != '') {
                // Found at least one attachment
                if (isTinyMceUsed()) {
                    if (tinymce.activeEditor.getContent()) {
                        $('#configureblock').find('textarea.wysiwyg').each(function() {
                            modal_textarea_id = $(this).attr('id');
                            tinymce.EditorManager.execCommand('mceRemoveEditor', true, modal_textarea_id);
                        });
                    }
                    dock.hide();
                }
            }
        });
    });

    $('#configureblock .submitcancel.cancel').off('click');
    $('#configureblock .submitcancel.cancel').on('click', function(e) {
        if (isTinyMceUsed()) {
            if (tinymce.activeEditor.getContent()) {
                $('#configureblock').find('textarea.wysiwyg').each(function() {
                    modal_textarea_id = $(this).attr('id');
                    tinymce.EditorManager.execCommand('mceRemoveEditor', true, modal_textarea_id);
                });
            }
        }
        e.stopPropagation();
        e.preventDefault();
        dock.hide();
    });

    $('[data-confirm]').on('click', function() {
        var content = $(this).attr('data-confirm');
        return confirm(content);
    });
}

// Make json call, attach js events, populate and open the comments and details modal
function open_modal(e) {
    e.preventDefault();
    $('#modal_messages').html('').removeClass();

    var block = $('#configureblock');

    var params = {
        'viewid': viewid
    }

    if ($(e.target).closest('a').data('artefactid')) {
        params['artefactid'] = $(e.target).closest('a').data('artefactid');
    }

    if ($(e.target).closest('a').data('blockid') != null) {
        params['blockid'] = $(e.target).closest('a').data('blockid');
    }

    sendjsonrequest(config['wwwroot'] + 'view/viewblocks.json.php',  params, 'POST', function(data) {
        block.find('.modal-title').text(data.title);
        $('.blockinstance-content').html(data.html);

        set_up_modal_events();
        dock.show(block, false, true);

        // $('.feedbacktable .list-group-lite').addClass('fullwidth');
        $(block).find('.feedbacktable .list-group-lite').addClass('fullwidth');
        $(block).find('.feedbacktable').on('click', '.js-reply', null, function(e){
            var replybutton = $(this);
            e.preventDefault();
            setupCommentButton(replybutton);
        });

        // Focus on the delete button for accessiblity
        $('#configureblock').find('.close').trigger('focus');
    });
}

function delete_comment_from_modal_submit(form, data) {
    if (!data.data.artefact) {
        paginator.updateResults(data);
        paginator.alertProxy('pagechanged', data);
        return;
    }
    var params = {
        'viewid': viewid,
        'artefactid': data.data['artefact'],
        'blockid': data.data['blockid'],
    }

    if (!data.data['artefact']) {
        params['artefactid'] = form.artefactid.value;
    }
    tinymce.EditorManager.execCommand('mceRemoveEditor',true, $('#configureblock').find('textarea.wysiwyg').attr('id'));
    sendjsonrequest(config['wwwroot'] + 'view/viewblocks.json.php',  params, 'POST', function(data) {
        $('#configureblock').find('.modal-title').text(data.title);
        $('.blockinstance-content').html(data.html);
        set_up_modal_events();

        $('.feedbacktable').on('click', '.js-reply', null, function(e){
            var replybutton = $(this);
            e.preventDefault();
            setupCommentButton(replybutton);
        });

        $('#modal_messages').removeClass().addClass('alert alert-success').html(get_string('commentremoved', 'artefact.comment'));
    });

    // Update the comment link with correct count
    var commentlink = '';
    if (data.data.blockid) {
        commentlink = $('.commentlink').filter("[data-blockid=" + data.data.blockid + "][data-artefactid=" + data.data.artefact + "]");
    }
    else {
        commentlink = $('.commentlink').filter("[data-artefactid=" + data.data.artefact + "]");
    }

    var newlink ='';
    if (data.data.count == 0) {
        if (commentlink.closest('div[class*=block-header]').hasClass('bh-displayiconsonly')) {
            newlink += '<span class="icon icon-comments" role="presentation" aria-hidden="true"></span>';
            newlink += '<span class="bh-margin-left icon icon-search-plus" role="presentation" aria-hidden="true"></span>';
        }
        else {
            newlink += '<span class="icon icon-plus" role="presentation" aria-hidden="true"></span>';
            newlink += ' ' + get_string('addcomment', 'artefact.comment');
            newlink += '<span class="bh-margin-left icon icon-search-plus" role="presentation" aria-hidden="true"></span>';
            newlink += ' ' + get_string('Details', 'artefact.comment');
        }
    }
    else {
        if (commentlink.closest('div[class*=block-header]').hasClass('bh-displayiconsonly')) {
            newlink = '<span class="icon icon-comments" role="presentation" aria-hidden="true"></span> (';
            newlink += data.data.count + ')';
            newlink += '<span class="bh-margin-left icon icon-search-plus" role="presentation" aria-hidden="true"></span>';
        }
        else {
            newlink = '<span class="icon icon-comments" role="presentation" aria-hidden="true"></span>';
            newlink += ' ' + get_string('commentsanddetails', 'artefact.comment', data.data.count);
        }
    }

    // Add the new link to the comment link
    commentlink.html(newlink);

    if ($('#configureblock').hasClass('closed')) {
        formSuccess(form, data);
    }
}

function setupCommentButton(element) {
    // Set up the onclick method for all comment reply buttons
    var replybutton = $(element);
    // Each comment stores its ID as a "replyto" data attribute
    var replyto = replybutton.data('replyto');
    var canpublicreply = replybutton.data('canpublicreply');
    var canprivatereply = replybutton.data('canprivatereply');
    if (replyto) {
        // Put this comment's ID in the "replyto" hidden form field
        if (replybutton.data('blockid')) {
            $('#add_feedback_form_' + replybutton.data('blockid') + '_replyto').val(replyto);
        }
        else {
            $('#add_feedback_form_replyto').val(replyto);
        }

        var replyview = $('#comment_reply_parent');
        // Remove any previous "reply to" comment that was being displayed
        replyview.find('div').remove();

        // Display a copy of this comment below the feedback form
        var commentcopy = $('#comment' + replyto).clone();
        // Disable the action buttons from the display copy
        commentcopy.find('.comment-item-buttons').remove();
        commentcopy.appendTo(replyview);
        replyview.show().removeClass('d-none');

        // Check whether we need to force a "private" or "public" message
        // (This is only for display. We'll also check & enforce this on the server side.)
        var makepublicswitch = $('#add_feedback_form_ispublic_container .form-switch');
        $('#add_feedback_form_ispublic_container .add_feedback_form_privacy_message').remove();
        if (canpublicreply && canprivatereply) {
            // If they have both options, show the normal switch
            makepublicswitch.show().removeClass('d-none');
        }
        else {

            makepublicswitch.hide();
            var msg = null;
            // They can only post a public reply
            if (!canprivatereply) {
                makepublicswitch.find("input#add_feedback_form_ispublic").prop('checked', true);
                msg = $(".add_feedback_form_forcepublic_message").clone().show().removeClass("d-none");
            }
            // They can only post a private reply
            else {
                makepublicswitch.find("input#add_feedback_form_ispublic").prop('checked', false);
                msg = $(".add_feedback_form_forceprivate_message").clone().show().removeClass("d-none");
            }

            $('#add_feedback_form_ispublic_container').append(msg);
        }
    }
}

// Show/Hide the comments and details block headers
function toggleDetailsBtn() {
    $('#details-btn').off('click');
    $('#details-btn').on('click', function(e) {
        var detailsActive = 0;
        var headers = $('#main-column-container').find('.block-header');

        if (!$('#details-btn').hasClass('active')) {
            $('#details-btn').addClass('active');
            headers.removeClass('d-none');
            detailsActive = 1;
        }
        else {
            $('#details-btn').removeClass('active');
            headers.addClass('d-none');
            detailsActive = 0;
        }

        var params = {
            'field': 'view_details_active',
            'value': detailsActive,
        }
        // Save this details mode state to the user account preferences table
        sendjsonrequest(config['wwwroot'] + 'view/viewdetailsfilter.json.php',  params, 'POST', function(data) {
        });
    });
}

// Make sure active block headers still display after pagination
$(document).on('pageupdated', function(e, data) {
    var headers = $('#main-column-container').find('.block-header');
    if ($('#details-btn').hasClass('active')) {
        headers.removeClass('d-none');
    }
});

jQuery(window).on('blocksloaded', {}, function() {

    toggleDetailsBtn();

    // Watchlist
    if ($('#toggle_watchlist_link').length) {
        $('#toggle_watchlist_link').on("click", function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (typeof artefactid === 'undefined') {
                artefactid = 0;
            }
            sendjsonrequest(config.wwwroot + 'view/togglewatchlist.json.php', {'view': viewid, 'artefact': artefactid}, 'POST', function(data) {
                jQuery('#toggle_watchlist_link').html(data.newtext);
            });
        });
    }

    // Copy view
    var copyurl = $("#copyview-button").attr('href');
    $("#copyview-button").on('click', function(event) {
        if (event.currentTarget.href.match(/collection=(.*)/)) {
            event.preventDefault();
            event.stopPropagation();
            $("#copyview-form").modal('show');
        }
    });
    $("#copy-view-button").on('click', function() {
        // drop the collection bit from the url
        var url = copyurl.replace(/collection=(.*)/, '');
        $(this).text(get_string('processing') + ' ...').prop('disabled', true).trigger("blur");
        processingStart();
        window.location = url;
    });
    $("#copy-collection-button").on('click', function() {
        $(this).text(get_string('processing') + ' ...').prop('disabled', true).trigger("blur");
        processingStart();
        window.location = copyurl;
    });

    $('#feedbacktable').on('click', '.js-reply', null, function(e){
        var replybutton = $(this);

        e.preventDefault();
        setupCommentButton(replybutton);

        if (replybutton.parents('.js-feedbackbase').length) {
            $('#add_feedback_heading').trigger("focus");
            jQuery('html, body').animate({ scrollTop: jQuery('#add_feedback_heading').offset().top }, 'fast');
            return false;
        }

        if (replybutton.parents('.js-feedbackblock').length) {
            var commentModal = $('#add_feedback_link').attr('data-target');
            var target = $(commentModal);
            dock.show(target, false, true);
        }
    });

    $('.js-add-comment-modal').on('click', function(e) {
        var replyviewContent = $('#comment_reply_parent').children();

        e.preventDefault();
        // Remove any previous "reply to" comment that was being displayed
        replyviewContent.remove();
        $('input#add_feedback_form_replyto').val('');

        $('.modal').on('shown.bs.modal', function() {
            $('#feedback-form').find('.close').trigger("focus");
        });
    });

    $('#configureblock, #feedback-form').on('keydown', function(e) {
        if (e.keyCode === $j.ui.keyCode.ESCAPE ) {
            dock.hide();
        }
    });

});
