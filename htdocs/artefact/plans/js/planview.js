/**
 *
 * @package    mahara
 * @subpackage artefact-plans
 * @author     Alexander Del Ponte <delponte@uni-bremen.de>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

function toggleTaskChosen(element, outcome) {
    var newChosenValue = outcome.status;
    $(element).data('chosen', newChosenValue);

    var taskbuttons = $(element).closest('.task').find('.btn-tasks');
    taskbuttons.html(outcome.buttons);
    var checkBox = $(element).find('input');

    checkBox.prop('checked', newChosenValue);
}

function toggleTaskCompleted(element, outcome) {
    var newCompletedValue = outcome.status;
    $(element).data('completed', newCompletedValue);
    $(element).removeClass('icon-square icon-check-square text-success').addClass(outcome.classes);
}

function setCheckboxClickEvents() {
    $('.completed-checkbox').on('click', function (event) {
        event.preventDefault();
        if ($(this).data('processing')) {
            return;
        }
        $(this).data('processing', true);
        var thistoggle = $(this);
        var params = {
            'taskid': $(this).data('taskid'),
            'completed': $(this).data('completed')
        }
        sendjsonrequest(config.wwwroot + "artefact/plans/task/toggle_task_completed.json.php", params, 'POST', function(data) {
            if (data.error) {
                if (data.returnCode === 1) {
                    location.reload();
                }
                else {
                    displayMessage(data.message, 'error');
                }
            }
            else {
                toggleTaskCompleted(thistoggle, data);
                if (data.submissionurl) {
                    window.location.href = data.submissionurl;
                }
            }
            thistoggle.data('processing', false);
        });
    });
}

function isEmptyCollectionByPortfolioUrl(url) {
    return (url.split('/').pop().split('?')[0] === 'views.php');
}

function isSpecialCollectionPageByPortfolioUrl(url) {
    const regex = /matrix\.php|progresscompletion\.php/;
    return url.match(regex);
}

function setShowPortfolioClickEvents() {
    // Add a goto portfolio button if needed
    if ($('#page-modal .modal-footer .goto').length == 0) {
        $('#page-modal .modal-footer').prepend('<button class="btn btn-secondary goto" type="button">' + get_string_ajax('displayview', 'view') + '</button>');
    }
    else {
        $('#page-modal .goto').show();
    }
    $('.btn-view, .btn-outcome').off('click');
    $('.btn-view, .btn-outcome').on('click', function (event) {
        event.preventDefault();
        if (isEmptyCollectionByPortfolioUrl($(this).data('url'))) {
            alert(get_string('emptycollection'));
            return;
        }
        let contentpath = config.wwwroot + 'view/viewcontent.json.php';
        let iscollectionid = 0;
        if (isSpecialCollectionPageByPortfolioUrl($(this).data('url'))) {
            contentpath = config.wwwroot + 'collection/viewcontent.json.php';
            iscollectionid = 1;
        }

        var Me = this;
        var params = {
            'id': getUrlParameter('id', $(Me).data('url')) || '',
            'export': 1,
            'iscollection': iscollectionid
        };

        sendjsonrequest(contentpath, params, 'POST', showPreview.bind(null, 'big'));
        $('#page-modal .goto').off('click');
        $('#page-modal .goto').on('click', function() {
            var newWindow = window.open();
            newWindow.opener = null;
            newWindow.location.href = $(Me).data('url');
            $('#page-modal').modal("hide");
        });
    });
}

function setChooseTaskClickEvents() {
    $('.btn-toggle').on('click', function (event) {
        event.preventDefault();
        if ($(this).data('processing')) {
            return;
        }
        if ($(this).data('chosen')) {
            if (!confirm(get_string('unselecttaskconfirm'))) {
                return;
            }
        }
        $(this).data('processing', true);
        var thistoggle = $(this);
        var params = {
            'taskid': $(this).data('taskid'),
            'chosen': $(this).data('chosen')
        }
        sendjsonrequest(config.wwwroot + "artefact/plans/groupplans/toggle_grouptask.json.php", params, 'POST', function(data) {
            if (data.error) {
                if (data.returnCode === 1) {
                    location.reload();
                }
                else {
                    displayMessage(data.message, 'error');
                }
            }
            else {
                toggleTaskChosen(thistoggle, data);
                if (data.status) {
                    displayMessageTemp(get_string('grouptaskselected'), 'ok', true);
                }
                else {
                    displayMessageTemp(get_string('grouptaskunselected'), 'ok', true);
                }
            }
            thistoggle.data('processing', false);
        });
    });
}

// Set settings to true for dynamic display length according to the amount of text to read
function displayMessageTemp(message, type, settings) {
    if (settings === true) {
        settings = {delay: 1000 + message.length * 30, fadeOut: 1000};
    }
    else {
        settings = settings || {delay: 4000, fadeOut: 1000};
    }

    displayMessage(message, type);
    $('#messages div').last().delay(settings.delay).fadeOut(settings.fadeOut, function() {
        $(this).remove();
    });
}

$(function () {
    // Init event handlers on first page load
    setCheckboxClickEvents();
    setShowPortfolioClickEvents();
    setChooseTaskClickEvents();

    // Remove added click event after closing
    $('#page-modal').on('hidden.bs.modal', function() {
        $('#page-modal .goto').off('click');
        $('#page-modal .goto').hide();
    });

    // Handle event binding when pagination is run - Is also called after preview - ToDo: Handle pagination only
    $(document).ajaxSend(function(e) {
        // before pagination loads the new page, remove event handlers
        $('.completed-checkbox').off();
        $('.btn-view').off();
        $('.btn-outcome').off();
        $('.btn-toggle').off();
    }).ajaxComplete(function(e) {
        // when received the new page, add event handlers
        setCheckboxClickEvents();
        setShowPortfolioClickEvents();
        setChooseTaskClickEvents();
    });
});

