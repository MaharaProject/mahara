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

    // need to change the watchlist link
    if (data.data.updatelink) {
        jQuery('#toggle_watchlist_link').text(data.data.updatelink);
    }

    formSuccess(form, data);

    // Check if the form is displayed inside a modal
    // then close the modal
    if ($j('#feedback-form').length) {
        $j('#feedback-form').modal('hide');
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

function isTinyMceUsed() {
    return (typeof(tinyMCE) != 'undefined' && typeof(tinyMCE.get('add_feedback_form_message')) != 'undefined');
}

jQuery(function($j) {

    if ($j('#toggle_watchlist_link').length) {
        $j('#toggle_watchlist_link').click(function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (typeof artefactid === 'undefined') {
                artefactid = 0;
            }
            $j.post(config.wwwroot + 'view/togglewatchlist.json.php', {
                'view': viewid,
                'artefact': artefactid,
                'sesskey': config.sesskey
            }).done(function(data) {
                if (data.message.newtext) {
                    var icon = '<span class="icon icon-eye prs"></span>';
                    if (data.message.watched) {
                        icon = '<span class="icon icon-eye-slash prs"></span>';
                    }
                    $j('#toggle_watchlist_link').html(icon + data.message.newtext);
                    displayMessage(data.message.message, 'ok', true);
                }
            });
        });
    }

    $j(".copyview").each(function() {
        $j(this).click(function(e) {
            if (e.target.href.match(/collection=(.*)/)) {
                e.preventDefault();
                // We need to let user choose from collection or view only
                var collection = e.target.href.match(/collection=(.*)/)[1];
                if (!$j('#dialog-confirm').length) {
                    $j('body').append('<div id="dialog-confirm" title="' + get_string('confirmcopytitle') + '">' + get_string('confirmcopydesc') + '</div>');
                }
                $j('#dialog-confirm').dialog({
                    resizable: false,
                    height: 200,
                    modal: true,
                    buttons: [
                        {
                            text: get_string('View'),
                            click: function() {
                                // drop the collection bit from the url
                                var url = e.target.href.replace(/collection=(.*)/, '');
                                window.location = url;
                            }
                        },
                        {
                            text: get_string('Collection'),
                            click: function() {
                                window.location = e.target.href;
                            }
                        }
                    ]
                });
            }
        });
    });
});