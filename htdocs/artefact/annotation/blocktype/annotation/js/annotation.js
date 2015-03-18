/**
 * Javascript for the annotation artefact
 * @source: http://gitorious.org/mahara/mahara
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * Return the specified request variable from the URL.
 * This should be moved to mahara.js to everyone can use it.
 */
function getURLParameter(variable) {
   var query = window.location.search.substring(1);
   var vars = query.split("&");
   for (var i = 0; i < vars.length; i++) {
       var pair = vars[i].split("=");
       if (pair[0] == variable) {
           return pair[1];
       }
   }
   return false;
}


function modifyAnnotationFeedbackSuccess(form, data) {
    var formname = form.name;
    var limit    = getURLParameter('limit');
    var offset   = getURLParameter('offset');

    if (limit === false && offset === false) {
        // Pagination is not used.
        limit = null;
        offset = null;
    }

    // Reload the annotation feedback table with the new feedback that's just been made public.
    sendjsonrequest('../artefact/annotation/annotations.json.php',
        {
            'annotationid' : $(formname + '_annotationid').value,
            'viewid'       : $(formname + '_viewid').value,
            'artefactid'   : $(formname + '_artefactid').value,
            'blockid'      : $(formname + '_blockid').value,
            'limit'        : limit,
            'offset'       : offset,
        }, 'GET', function (data) {
            var blockid = $(formname + '_blockid').value;
            // Populate the div.
            $j('#annotationfeedbackview_' + blockid).html(data.data);
            connectAddAnnotationFeedbackForms();
            connectAnnotationFeedbackLinks();
    });

    formSuccess(form, data);
}

function addAnnotationFeedbackSuccess(form, data) {
    var formname = form.name;
    var blockid  = $(formname + '_blockid').value;
    var limit    = getURLParameter('limit');
    var offset   = getURLParameter('offset');

    if (limit === false && offset === false) {
        // Pagination is not used.
        limit = null;
        offset = null;
    }

    addElementClass(formname, 'hidden');
    if ($('overlay')) {
        removeElement('overlay');
    }

    if (typeof(paginator) != 'undefined' && paginator.id == 'annotationfeedback_pagination_' + blockid) {
        // Make sure its using the annotation paginator for its block not the feedback paginator.
        paginator.updateResults(data);
        paginator.alertProxy('pagechanged', data['data']);
    }
    else {
        // Reload the annotation feedback table with the new feedback that's just been entered.
        sendjsonrequest('../artefact/annotation/annotations.json.php',
            {
                'annotationid' : $(formname + '_annotationid').value,
                'viewid'       : $(formname + '_viewid').value,
                'artefactid'   : $(formname + '_artefactid').value,
                'blockid'      : $(formname + '_blockid').value,
                'limit'        : limit,
                'offset'       : offset,
            }, 'GET', function (data) {
                var blockid = $(formname + '_blockid').value;
                // Populate the div.
                $j('#annotationfeedbackview_' + blockid).html(data.data);
                connectAddAnnotationFeedbackForms();
                connectAnnotationFeedbackLinks();
        });

    }

    // Clear TinyMCE
    if (isTinyMceUsed(formname + '_message')) {
        tinyMCE.activeEditor.setContent('');
    }

    // Clear the textarea (in case TinyMCE is disabled)
    var messageid = 'message';
    if (data.fieldnames && data.fieldnames.message) {
        messageid = data.fieldnames.message;
    }
    $(formname + '_' + messageid).value = '';

    rewriteCancelButtons(formname);
    formSuccess(form, data);
}

function moveAnnotationFeedbackForm(tinymceused, formname, link) {
    if (tinymceused) {
        tinyMCE.execCommand('mceRemoveEditor', false, formname + '_message');
    }
    form = $(formname);
    removeElement(form);
    appendChildNodes($(link).parentNode, form);
    if (tinymceused) {
       tinyMCE.execCommand('mceAddEditor', false, formname + '_message');
    }
}

function rewriteCancelButtons(formname) {
    if ($(formname)) {
        var buttons = getElementsByTagAndClassName('input', 'cancel', formname);
        // hashed field names on anon forms mean we don't know the exact id of this button
        var idprefix = 'cancel_' + formname + '_';
        forEach(buttons, function(button) {
            if (getNodeAttribute(button, 'id').substring(0, idprefix.length) == idprefix) {
                disconnectAll(button);
                connect(button, 'onclick', function (e) {
                    e.stop();
                    addElementClass(formname, 'hidden');
                    if ($('overlay')) {
                        removeElement('overlay');
                    }
                    return false;
                });
            }
        });
    }
}

function isTinyMceUsed(elementname) {
    return (typeof(tinyMCE) != 'undefined' && typeof(tinyMCE.get(elementname)) != 'undefined');
}

function connectAddAnnotationFeedbackForms() {
    // There could be several add_annotation_feedback_form forms on the view (one for each annotation).
    var annotationfeedbackforms = getElementsByTagAndClassName('form', 'add_annotation_feedback_form');
    var formidprefix = 'add_annotation_feedback_form_';

    forEach(annotationfeedbackforms, function(annotationfeedbackform) {
        if (getNodeAttribute(annotationfeedbackform, 'id').substring(0, formidprefix.length) == formidprefix) {

            var annotationformid = getNodeAttribute(annotationfeedbackform, 'id');
            rewriteCancelButtons(annotationformid);

            if ($(annotationfeedbackform)) {

                var links = getElementsByTagAndClassName('a', 'placeannotationfeedback');
                // There can be more than one add_annotation_feedback_link on a view
                // but only one per block.
                var idprefix = 'add_annotation_feedback_link_';

                forEach(links, function(link) {
                    if (getNodeAttribute(link, 'id').substring(0, idprefix.length) == idprefix) {
                        if ($(link)) {
                            if (typeof(tinyMCE) != 'undefined') {
                                tinyMCE.on('SetupEditor', function(editor) {
                                    if (editor.id == annotationformid + '_message') {
                                        editor.on('init', function() {
                                            editor.hide();
                                        });
                                    }
                                });
                            }

                            disconnectAll(link);

                            connect(link, 'onclick', function(e) {
                                // get the name of the form we're using.
                                var blockid = getNodeAttribute(link, 'id').substring(idprefix.length);
                                var annotationformid = formidprefix + blockid;

                                var tinymceused = isTinyMceUsed(annotationformid + '_message');

                                e.stop();
                                removeElementClass(annotationformid, 'js-hidden');
                                removeElementClass(annotationformid, 'hidden');
                                if (typeof(annotationfeedbacklinkinblock) != 'undefined') {
                                    // need to display it as a 'popup' form
                                    moveAnnotationFeedbackForm(tinymceused, annotationformid, link);
                                    addElementClass(annotationformid, 'blockinstance');
                                    addElementClass(annotationformid, 'configure');
                                    addElementClass(annotationformid, 'annotation_form_overlay');
                                    addElementClass(annotationformid, 'vertcentre');
                                    appendChildNodes(document.body, DIV({id: 'overlay'}));
                                }

                                if (tinymceused) {
                                    var mce = tinyMCE.get(annotationformid + '_message');
                                    mce.show();
                                    jQuery('.mce-toolbar.mce-first').siblings().toggleClass('hidden');
                                    // Clear out old content that may have been left from a cancel.
                                    mce.setContent('');
                                    mce.focus();
                                }
                                else {
                                    $j('#' + annotationformid + ' input:text').eq(1).focus();
                                    // Clear out old content that may have been left from a cancel.
                                    $j('#' + annotationformid + ' input:text').eq(1).text('');
                                }

                                return false;
                            });
                        }
                    }
                });
            }
        }
    });
}

function connectAnnotationFeedbackLinks() {
    var links = getElementsByTagAndClassName('a', 'annotationfeedbacklink');
    forEach(links, function(link) {
        var idprefix = 'annotation_feedback_link_';
        if (getNodeAttribute(link, 'id').substring(0, idprefix.length) == idprefix) {

            if ($(link)) {

                disconnectAll(link);

                connect(link, 'onclick', function(e) {
                    var blockid = getNodeAttribute(link, 'id').substring(idprefix.length);
                    var chtml = $j('#annotationfeedbacktable_' + blockid).parent();

                    // Add a 'close' link at the bottom of the list for convenience.
                    if ($j('#closer_' + blockid).length == 0) {
                        var closer = $j('<a id="closer_' + blockid + '" href="#" class="close-link">' + get_string('Close') + '</a>').click(function(e) {
                            $j(this).parent().toggle(400, function() {
                                link.focus();
                            });
                            e.preventDefault();
                        });
                        chtml.append(closer);
                    }

                    chtml.toggle(400, function() {
                        if (chtml.is(':visible')) {
                            chtml.find('a').first().focus();
                        }
                        else {
                            link.focus();
                        }
                    });

                    e.preventDefault();
                });
            }
        }
    });
}
