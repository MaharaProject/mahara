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
    if (isTinyMceUsed()) {
        var mce = tinyMCE.get('add_feedback_form_message');
        mce.show();
        jQuery('.mce-toolbar.mce-first').siblings().toggleClass('d-none');
        mce.focus();
    }
    if (jQuery('#feedback-form').hasClass('modal-docked')) {
        jQuery('#feedback-form').removeClass('closed').addClass('active');
    }
    formError(form, data);
}

function addFeedbackSuccess(form, data) {
    paginator.updateResults(data);
    // Clear rating from previous submission
    jQuery('#add_feedback_form_rating_container input.star').each(function() {
        jQuery(this).prop('checked', false);
    });
    paginator.alertProxy('pagechanged', data['data']);

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
    resetFeedbackReplyto();
    formSuccess(form, data);

    // Check if the form is displayed inside a modal
    // then close the modal
    if (jQuery('#feedback-form').length) {
        dock.hide();
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
        jQuery('#objection_link').parent().html('<span class="nolink"><span class="icon icon-lg icon-flag text-danger left" role="presentation" aria-hidden="true"></span>' + get_string_ajax('objectionablematerialreported', 'mahara') + '</span>');
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

jQuery(function($) {
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

    function setupCommentButton(element) {
        // Set up the onclick method for all comment reply buttons
        var replybutton = $(element);
        // Each comment stores its ID as a "replyto" data attribute
        var replyto = replybutton.data('replyto');
        var canpublicreply = replybutton.data('canpublicreply');
        var canprivatereply = replybutton.data('canprivatereply');
        if (replyto) {
            // Put this comment's ID in the "replyto" hidden form field
            $('#add_feedback_form_replyto').val(replyto);

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
    });
});
