// The list of existing feedback.

function addFeedbackSuccess(form, data) {
    addElementClass('add_feedback_form', 'hidden');
    paginator.updateResults(data);
    $('add_feedback_form_message').value = '';
}

function objectionSuccess() {
    addElementClass('objection_form', 'hidden');
    $('objection_form_message').value = '';
}

addLoadEvent(function () {
    if ($('add_feedback_form')) {
        if ($('add_feedback_link')) {

            var isIE6 = document.all && !window.opera &&
                (!document.documentElement || typeof(document.documentElement.style.maxHeight) == "undefined");

            connect('add_feedback_link', 'onclick', function(e) {
                e.stop();
                if ($('objection_form')) {
                    addElementClass('objection_form', 'hidden');
                }
                removeElementClass('add_feedback_form', 'js-hidden');
                removeElementClass('add_feedback_form', 'hidden');

                // IE6 fails to hide tinymce properly after feedback
                // submission, so force it to reload the page by disconnecting
                // the submit handler
                if (isIE6) {
                    disconnectAll('add_feedback_form', 'onsubmit');
                }

                return false;
            });
        }
        connect('cancel_add_feedback_form_submit', 'onclick', function (e) {
            e.stop();
            addElementClass('add_feedback_form', 'hidden');
            return false;
        });
    }

    if ($('objection_form')) {
        if ($('objection_link')) {
            connect('objection_link', 'onclick', function(e) {
                e.stop();
                if ($('add_feedback_form')) {
                    addElementClass('add_feedback_form', 'hidden');
                }
                removeElementClass('objection_form', 'js-safe-hidden');
                removeElementClass('objection_form', 'hidden');
                return false;
            });
        }
        connect('cancel_objection_form_submit', 'onclick', function (e) {
            e.stop();
            addElementClass('objection_form', 'hidden');
            return false;
        });
    }

    if ($('toggle_watchlist_link')) {
        connect('toggle_watchlist_link', 'onclick', function (e) {
            e.stop();
            sendjsonrequest('togglewatchlist.json.php', {'view': viewid}, 'POST', function(data) {
                $('toggle_watchlist_link').innerHTML = data.newtext;
            });
        });
    }
});
