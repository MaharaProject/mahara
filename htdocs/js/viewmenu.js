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
        jQuery('.mce-toolbar.mce-first').siblings().toggleClass('hidden');
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
    forEach(getElementsByTagAndClassName('input', 'star', 'add_feedback_form_rating_container'), function (r) {
        r.checked = false;
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
    $('add_feedback_form_' + messageid).value = '';

    // Clear the "Make public" switch back to its default "public" setting
    $j('input#add_feedback_form_ispublic').prop('checked', true);

    // need to change the watchlist link
    if (data.data.updatelink) {
        jQuery('#toggle_watchlist_link').text(data.data.updatelink);
    }
    resetFeedbackReplyto();
    formSuccess(form, data);

    // Check if the form is displayed inside a modal
    // then close the modal
    if ($j('#feedback-form').length) {
        dock.hide();
    }
}

function objectionSuccess(form, data) {
    $('objection_form_message').value = '';
    formSuccess(form, data);
    // close the form when the form is submited
    // Using bootstrap modal
    if ($j('#report-form').length) {
        $j('#report-form').modal('hide');
    }
}

function resetFeedbackReplyto() {
    $j('#comment_reply_parent').hide();
    $j('#add_feedback_form_replyto').val('');
    $j('#add_feedback_form_ispublic_container .form-switch').show().removeClass('hidden');
    $j('#add_feedback_form_ispublic_container .add_feedback_form_privacy_message').remove();
}

function isTinyMceUsed() {
    return (typeof(tinyMCE) != 'undefined' && typeof(tinyMCE.get('add_feedback_form_message')) != 'undefined');
}

jQuery(function($j) {
    // Watchlist
    if ($j('#toggle_watchlist_link').length) {
        $j('#toggle_watchlist_link').click(function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (typeof artefactid === 'undefined') {
                artefactid = 0;
            }
            sendjsonrequest(config.wwwroot + 'view/togglewatchlist.json.php', {'view': viewid, 'artefact': artefactid}, 'POST', function(data) {
                $('toggle_watchlist_link').innerHTML = data.newtext;
            });
        });
    }

    // Copy view
    var copyurl = $j("#copyview-button").attr('href');
    $j("#copyview-button").on('click', function(event) {
        if (event.currentTarget.href.match(/collection=(.*)/)) {
            event.preventDefault();
            event.stopPropagation();
            $j("#copyview-form").modal('show');
        }
    });
    $j("#copy-view-button").on('click', function() {
        // drop the collection bit from the url
        var url = copyurl.replace(/collection=(.*)/, '');
        window.location = url;
    });
    $j("#copy-collection-button").on('click', function() {
        window.location = copyurl;
    });

    function setupCommentButton(element) {
        // Set up the onclick method for all comment reply buttons
        var replybutton = $j(element);
        // Each comment stores its ID as a "replyto" data attribute
        var replyto = replybutton.data('replyto');
        var canpublicreply = replybutton.data('canpublicreply');
        var canprivatereply = replybutton.data('canprivatereply');
        if (replyto) {
            // Put this comment's ID in the "replyto" hidden form field
            $j('#add_feedback_form_replyto').val(replyto);

            var replyview = $j('#comment_reply_parent');
            // Remove any previous "reply to" comment that was being displayed
            replyview.find('div').remove();

            // Display a copy of this comment below the feedback form
            var commentcopy = $j('#comment' + replyto).clone();
            // Disable the action buttons from the display copy
            commentcopy.find('.comment-item-buttons').remove();
            commentcopy.appendTo(replyview);
            replyview.show().removeClass('hidden');

            // Check whether we need to force a "private" or "public" message
            // (This is only for display. We'll also check & enforce this on the server side.)
            var makepublicswitch = $j('#add_feedback_form_ispublic_container .form-switch');
            $j('#add_feedback_form_ispublic_container .add_feedback_form_privacy_message').remove();
            if (canpublicreply && canprivatereply) {
                // If they have both options, show the normal switch
                makepublicswitch.show().removeClass('hidden');
            }
            else {

                makepublicswitch.hide();
                var msg = null;
                // They can only post a public reply
                if (!canprivatereply) {
                    makepublicswitch.find("input#add_feedback_form_ispublic").prop('checked', true);
                    msg = $j(".add_feedback_form_forcepublic_message").clone().show().removeClass("hidden");
                }
                // They can only post a private reply
                else {
                    makepublicswitch.find("input#add_feedback_form_ispublic").prop('checked', false);
                    msg = $j(".add_feedback_form_forceprivate_message").clone().show().removeClass("hidden");
                }

                $j('#add_feedback_form_ispublic_container').append(msg);
            }
        }
    }

    $j('#feedbacktable').on('click', '.js-reply', null, function(e){
        var replybutton = $j(this);

        e.preventDefault();
        setupCommentButton(replybutton);

        if (replybutton.parents('.js-feedbackbase').length) {
            $j('#add_feedback_heading').focus();
            jQuery('html, body').animate({ scrollTop: jQuery('#add_feedback_heading').offset().top }, 'fast');
            return false;
        }

        if (replybutton.parents('.js-feedbackblock').length) {
            var commentModal = $j('#add_feedback_link').attr('data-target');
            var target = $j(commentModal);
            dock.show(target, false, true);
        }
    });

    $j('.js-add-comment-modal').on('click', function(e) {
        var replyviewContent = $j('#comment_reply_parent').children();

        e.preventDefault();
        // Remove any previous "reply to" comment that was being displayed
        replyviewContent.remove();
        $j('input#add_feedback_form_replyto').val('');
    });
});
