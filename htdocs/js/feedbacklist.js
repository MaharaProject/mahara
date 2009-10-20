// The list of existing feedback.

function addFeedbackSuccess(form, data) {
    addElementClass('add_feedback_form', 'js-hidden');
    paginator.updateResults(data);
    $('add_feedback_form_message').value = '';
}

function objectionSuccess() {
    addElementClass('objection_form', 'js-hidden');
    $('objection_form_message').value = '';
}

addLoadEvent(function () {
    if ($('add_feedback_form')) {
        if ($('add_feedback_link')) {
            connect('add_feedback_link', 'onclick', function(e) {
                e.stop();
                if ($('objection_form')) {
                    addElementClass('objection_form', 'js-hidden');
                }
                removeElementClass('add_feedback_form', 'js-hidden');
                return false;
            });
        }
        connect('cancel_add_feedback_form_submit', 'onclick', function (e) {
            e.stop();
            addElementClass('add_feedback_form', 'js-hidden');
            return false;
        });
    }

    if ($('objection_form')) {
        if ($('objection_link')) {
            connect('objection_link', 'onclick', function(e) {
                e.stop();
                if ($('add_feedback_form')) {
                    addElementClass('add_feedback_form', 'js-hidden');
                }
                removeElementClass('objection_form', 'js-hidden');
                return false;
            });
        }
        connect('cancel_objection_form_submit', 'onclick', function (e) {
            e.stop();
            addElementClass('objection_form', 'js-hidden');
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
