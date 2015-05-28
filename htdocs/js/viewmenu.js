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
    // addElementClass('add_feedback_form', 'hidden');
    if ($('overlay')) {
        removeElement('overlay');
    }
    paginator.updateResults(data);
    // Clear rating from previous submission
    forEach(getElementsByTagAndClassName('input', 'star', 'add_feedback_form_rating_container'), function (r) {
        r.checked = false;
    });
    paginator.alertProxy('pagechanged', data['data']);

    // Clear TinyMCE
    if (isTinyMceUsed()) {
        tinyMCE.activeEditor.setContent('');
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
    rewriteCancelButtons();
    formSuccess(form, data);
}

function objectionSuccess(form, data) {
    // addElementClass('objection_form', 'hidden');
    $('objection_form_message').value = '';
    rewriteCancelButtons();
    formSuccess(form, data);
}

function moveFeedbackForm(tinymceused) {
    if (tinymceused) {
        tinyMCE.execCommand('mceRemoveEditor', false, 'add_feedback_form_message');
    }
    form = $('add_feedback_form');
    removeElement(form);
    appendChildNodes($('add_feedback_link').parentNode, form);
    if (tinymceused) {
       tinyMCE.execCommand('mceAddEditor', false, 'add_feedback_form_message');
    }
}

function rewriteCancelButtons() {
    if ($('add_feedback_form')) {
        var buttons = getElementsByTagAndClassName('input', 'cancel', 'add_feedback_form');
        // hashed field names on anon forms mean we don't know the exact id of this button
        var idprefix = 'cancel_add_feedback_form_';
        forEach(buttons, function(button) {
            if (getNodeAttribute(button, 'id').substring(0, idprefix.length) == idprefix) {
                disconnectAll(button);
                connect(button, 'onclick', function (e) {
                    e.stop();
                    addElementClass('add_feedback_form', 'hidden');
                    if ($('overlay')) {
                        removeElement('overlay');
                    }
                    return false;
                });
            }
        });
    }
    if ($('cancel_objection_form_submit')) {
        disconnectAll('cancel_objection_form_submit');
        connect('cancel_objection_form_submit', 'onclick', function (e) {
            e.stop();
            addElementClass('objection_form', 'hidden');
            return false;
        });
    }
}

function isTinyMceUsed() {
    return (typeof(tinyMCE) != 'undefined' && typeof(tinyMCE.get('add_feedback_form_message')) != 'undefined');
}

addLoadEvent(function () {


    rewriteCancelButtons();

    if ($('toggle_watchlist_link')) {
        connect('toggle_watchlist_link', 'onclick', function (e) {
            e.stop();
            if (typeof artefactid === 'undefined') {
                artefactid = null;
            }
            sendjsonrequest(config.wwwroot + 'view/togglewatchlist.json.php', {'view': viewid, 'artefact': artefactid}, 'POST', function(data) {
                if (data.newtext) {
                    var icon = '<span class="fa fa-eye prs"></span>';
                    if(data.watched){
                        icon = '<span class="fa fa-eye-slash prs"></span>';
                    } 
                    $('toggle_watchlist_link').innerHTML = icon + data.newtext;
                }
            });
        });
    }
});

jQuery(function($j) {
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
                                console.log('copypage');
                                window.location = url;
                            }
                        },
                        {
                            text: get_string('Collection'),
                            click: function() {
                                console.log('copycollection');
                                window.location = e.target.href;
                            }
                        }
                    ]
                });
            }
        });
    });
});
