// The list of existing feedback.
var feedbacklist = new TableRenderer('feedbacktable', config.wwwroot + 'view/getfeedback.json.php', []);

feedbacklist.limit = 10;
feedbacklist.rowfunction = function(r, n, d) {
    var td = TD(null);
    td.innerHTML = r.message;
    if (r.attachid && r.ownedbythisuser) {
        appendChildNodes(td, DIV(null, get_string('feedbackattachmessage')));
    }

    var publicPrivate = null;
    if (r.ispublic == 1) {
        var makePrivate = null;
        if (r.ownedbythisuser) {
            makePrivateLink = A({'href': ''}, get_string('makeprivate'));
            connect(makePrivateLink, 'onclick', function (e) {
                sendjsonrequest(
                    'changefeedback.json.php',
                    r,
                    'POST',
                    function (data) {
                        if (!data.error) {
                            replaceChildNodes(makePrivateLink.parentNode, get_string('thisfeedbackisprivate'));
                        }
                    }
                );

                e.stop();
            });
            makePrivate = [' - ', makePrivateLink];
        }
        publicPrivate = SPAN(null, get_string('thisfeedbackispublic'), makePrivate);
    }
    else {
        publicPrivate = get_string('thisfeedbackisprivate');
    }

    var attachment = null;
    if (r.attachid) {
        attachment = [' | ', get_string('attachment'), ': ', A({'href':config.wwwroot + 'artefact/file/download.php?file=' + r.attachid}, r.attachtitle), ' (', r.attachsize, ')'];
    }

    if (r.author) {
        var icon = DIV({'class': 'icon'}, A({'href': config.wwwroot + 'user/view.php?id=' + r.author}, IMG({'src': config.wwwroot + 'thumb.php?type=profileicon&id=' + r.author + '&maxsize=20', 'valign': 'middle'})));
        var authorname = A({'href': config.wwwroot + 'user/view.php?id=' + r.author}, r.name);
    }
    else {
        var icon = null;
        var authorname = r.name;
    }
    appendChildNodes(td, DIV({'class': 'details'}, icon, authorname, ' | ', r.date, ' | ', publicPrivate, attachment));

    return TR({'class': 'r' + (n % 2)}, td);
};
feedbacklist.emptycontent = get_string('nopublicfeedback');

function addFeedbackSuccess() {
    addElementClass('add_feedback_form', 'js-hidden');
    $('add_feedback_form_message').innerHTML = '';
    feedbacklist.doupdate();
}

function objectionSuccess() {
    addElementClass('objection_form', 'js-hidden');
    $('objection_form_message').innerHTML = '';
}

addLoadEvent(function () {
    if ($('add_feedback_form')) {
        if ($('add_feedback_link')) {
            connect('add_feedback_link', 'onclick', function(e) {
                e.stop();
                if ($('objection_form')) {
                    addElementClass('objection_form', 'js-hidden');
                }
                $('add_feedback_form').reset();
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
                $('objection_form').reset();
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
            sendjsonrequest('togglewatchlist.json.php', {'view': feedbacklist.view}, 'POST', function(data) {
                $('toggle_watchlist_link').innerHTML = data.newtext;
            });
        });
    }
});
