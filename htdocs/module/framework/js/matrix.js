jQuery(function($) {
    // Variable to adjust for the hiding/showing of columns
    var statusheaders = $('.statusheader').length;
    var dashes = $('th.smartevidencedash').length;
    var minstart = statusheaders + dashes; // The index of the last column before first page column, indexes start at zero so 1 = two columns
    var curstart = 1 + statusheaders + dashes; // The index of first page currently being displayed
    var range = 4; // The number of pages to display
    var curend = curstart + range; // The index of last page currently being displayed
    var maxend = $( "#tablematrix tr th" ).length; // The number of columns in the table

    function carousel_matrix() {
        $('#tablematrix td:not(.special), #tablematrix th').each(function() {
            var index = $(this).index();
            if ((index > minstart && index < curstart) || index > curend) {
                $(this).hide();
            }
            else {
                $(this).show();
            }
        });

        if (curstart <= (minstart + 1)) {
            $('#prev').hide();
        }
        else {
            $('#prev').show();
        }
        if (curend >= (maxend - 1)) {
            $('#next').hide();
        }
        else {
            $('#next').show();
        }
    }

    $('#prev, #next').on('click', function(e) {
        e.preventDefault();
        var action = $(this).attr('id');
        if (action == 'next') {
            curend = Math.min(curend + 1, maxend - 1);
            curstart = curend - range;
            carousel_matrix();
        }
        if (action == 'prev') {
            curstart = Math.max(curstart - 1, minstart + 1);
            curend = curstart + range;
            carousel_matrix();
        }
    });

    var cellx = celly = 0;
    $('#tablematrix td.mid span.icon:not(.disabled)').on('click', function(e) {
        e.preventDefault();
        cellx = $(this).closest('td').index();
        celly = $(this).closest('tr').index();
        var params = {};
        params.framework = frameworkid;
        params.view = $(this).data("view");
        params.option = $(this).data("option");
        sendjsonrequest('matrixpoint.json.php', params, 'POST', function(data) {
            var hastinymce = false;
            if (typeof tinyMCE !== 'undefined') {
                hastinymce = true;
            }
            function show_se_desc(id) {
                $("#instconf_smartevidencedesc_container div:not(.description)").addClass('d-none');
                $("#option_" + id).removeClass('d-none');
            }

            dock.show($('#configureblock'), true, false);

            // delay processing so animation can complete smoothly
            setTimeout(function() {
                var newpagemodal = $('#configureblock');
                newpagemodal.find('.blockinstance-header').html(data.data.form.title);
                newpagemodal.find('.blockinstance-content').html(data.data.form.content);
                newpagemodal.find('form').each(function() {
                    formchangemanager.add($(this).attr('id'));
                });
                deletebutton = newpagemodal.find('.deletebutton');
                // Lock focus to the newly opened dialog
                deletebutton.trigger("focus");
                deletebutton.off('click'); // Remove any previous click event
                deletebutton.on('click', function(e) {
                    e.stopPropagation();
                    e.preventDefault();
                    var confirm = null;
                    if (typeof formchangemanager !== 'undefined') {
                        confirm = formchangemanager.confirmLeavingForm();
                    }
                    if (confirm === null || confirm === true) {
                        if (data.data.form.isnew) {
                            // need to delete empty annotation on cancel
                            params.action = 'delete';
                            params.blockconfig = $('#instconf_blockconfig').val();
                            editmatrix_update(params);
                        }
                        if (hastinymce) {
                            tinyMCE.execCommand('mceRemoveEditor', false, "instconf_text");
                        }
                        feedbacktextarea = $("#addfeedbackmatrix textarea");
                        if (feedbacktextarea.length && hastinymce) {
                            tinyMCE.execCommand('mceRemoveEditor', false, feedbacktextarea.attr('id'));
                        }
                        hide_dock();
                        //focus on matrix annotation
                        $('#tablematrix tr:eq(' + celly + ') td:eq(' + cellx + ') span.icon').find('a').trigger("focus");
                    }
                });
                cancelbutton = newpagemodal.find('.submitcancel.cancel');
                cancelbutton.off('click'); // Remove any previous click event
                cancelbutton.on('click', function(e) {
                    e.stopPropagation();
                    e.preventDefault();
                    var confirm = null;
                    if (typeof formchangemanager !== 'undefined') {
                        confirm = formchangemanager.confirmLeavingForm();
                    }
                    if (confirm === null || confirm === true) {
                        if (data.data.form.isnew) {
                            params.action = 'delete';
                            params.blockconfig = $('#instconf_blockconfig').val();
                            editmatrix_update(params);
                        }
                        if (hastinymce) {
                            tinyMCE.execCommand('mceRemoveEditor', false, "instconf_text");
                        }
                        feedbacktextarea = $("#addfeedbackmatrix textarea");
                        if (feedbacktextarea.length && hastinymce) {
                            tinyMCE.execCommand('mceRemoveEditor', false, feedbacktextarea.attr('id'));
                        }
                        hide_dock();
                        //focus on matrix annotation
                        $('#tablematrix tr:eq(' + celly + ') td:eq(' + cellx + ') span.icon').find('a').trigger("focus");
                    }
                });
                if (hastinymce) {
                    tinyMCE.idCounter=0;
                    if ($("#instconf_text").length) {
                        tinyMCE.execCommand('mceAddEditor', false, "instconf_text");
                    }
                    if ($("#addfeedbackmatrix").length) {
                        textareaid = $("#addfeedbackmatrix textarea").attr('id');
                        tinyMCE.execCommand('mceAddEditor', false, textareaid);
                    }
                }
                // Only allow the point selected to be active in the 'Standard' dropdown
                $("#instconf_smartevidence option:not(:selected)").prop('disabled', true);
                // block title will be overwritten with framework choice so make it disabled
                $("#instconf_title").attr('disabled', true);
                // Set up evidence choices and show/hide related descriptions
                $("#instconf_smartevidence").select2();

                show_se_desc($("#instconf_smartevidence").val());
                $("#instconf_smartevidence").on('change', function() {
                    show_se_desc($(this).val());
                });

                // When we are saving the annotation block config form
                $('#instconf').on('submit', function(se) {
                    se.preventDefault();
                    var sdata = $("#instconf :input").serializeArray();
                    var values = {};
                    var tags = new Array();
                    sdata.forEach(function(item, index) {
                        if (item.name == 'tags[]') {
                            tags.push(item.value);
                        }
                        else if (item.name == 'text' && hastinymce) {
                            values[item.name] = tinyMCE.get('instconf_text').getContent();
                        }
                        else {
                            values[item.name] = item.value;
                        }
                    });

                    if (values["text"].length == 0) {
                        if ($("#instconf_text").parent().find('.errmsg').length == 0) {
                            $("#instconf_text").parent().append('<div class="errmsg"><span>' + get_string_ajax('annotationempty', 'artefact.annotation') + '</span></div>');
                        }
                        $('#instconf button.submitcancel.submit').prop("disabled", false);
                    }
                    else {
                        values['tags'] = tags.join();
                        values['framework'] = params.framework;
                        values['view'] = params.view;
                        values['option'] = params.option;
                        values['action'] = 'update';
                        if (hastinymce) {
                            tinyMCE.execCommand('mceRemoveEditor', false, "instconf_text");
                        }
                        editmatrix_update(values);
                        hide_dock();
                        //focus on matrix annotation
                        $('#tablematrix tr:eq(' + celly + ') td:eq(' + cellx + ') span.icon').find('a').trigger("focus");
                    }
                });
                // When we are saving the annotation feedback form - changing the evidence status
                $('#annotationfeedback').on('submit', function(se) {
                    se.preventDefault();
                    var confirm = null;
                    if (typeof formchangemanager !== 'undefined') {
                        confirm = formchangemanager.confirmLeavingForm();
                    }
                    if (confirm === null || confirm === true) {
                        var sdata = $("#annotationfeedback :input").serializeArray();
                        var values = {};
                        sdata.forEach(function(item, index) {
                            values[item.name] = item.value;
                        });
                        values['framework'] = params.framework;
                        values['view'] = params.view;
                        values['option'] = params.option;
                        values['action'] = 'evidence';
                        editmatrix_update(values);
                        if (hastinymce) {
                            tinyMCE.execCommand('mceRemoveEditor', false, "instconf_text");
                        }
                        feedbacktextarea = $("#addfeedbackmatrix textarea");
                        if (feedbacktextarea.length && hastinymce) {
                            tinyMCE.execCommand('mceRemoveEditor', false, feedbacktextarea.attr('id'));
                        }
                        hide_dock();
                        //focus on matrix annotation
                        $('#tablematrix tr:eq(' + celly + ') td:eq(' + cellx + ') span.icon').find('a').trigger("focus");
                    }
                    else {
                        se.stopPropagation();
                        se.processingStop();
                    }
                });
                // When we are saving the annotation feedback form - adding new feedback
                $('#addfeedbackmatrix').on('submit', function(se) {
                    se.preventDefault();
                    var sdata = $("#addfeedbackmatrix :input").serializeArray();
                    var values = {};
                    sdata.forEach(function(item, index) {
                        values[item.name] = item.value;
                    });

                    textareaid = $("#addfeedbackmatrix textarea").attr('id');
                    if (values['message'].length == 0) {
                        // add error message
                        $("#" + textareaid).parent().append('<div class="errmsg"><span>' +
                            get_string_ajax('annotationfeedbackempty', 'artefact.annotation') +
                            '</span></div>');
                    }
                    else {
                        values['framework'] = params.framework;
                        values['view'] = params.view;
                        values['option'] = params.option;
                        values['action'] = 'feedback';
                        editmatrix_update(values);
                    }
                });

                // Modals can have different components, we need to define which
                // is the last clickable element
                var last;
                // if there is a list of feedbacks, choose the last one
                if (newpagemodal.find('#matrixfeedbacklist a').length) {
                    last = newpagemodal.find('#matrixfeedbacklist a').last();
                }
                // if there is a feedback section, choose the submit button
                else if ($('#addfeedbackmatrix').length) {
                    last = $('#addfeedbackmatrix').find('.submit.btn');
                }
                // if no feedback section and there is a cancel button, choose it
                else if (newpagemodal.find('.submitcancel.cancel').length) {
                    last = newpagemodal.find('.submitcancel.cancel');
                }
                // no clickable elements then tabbing stays in delete button
                else {
                    last = newpagemodal.find('.deletebutton');
                }
                keytabbinginadialog(newpagemodal, newpagemodal.find('.deletebutton'), last);
            }, 200);
        });
    });

    function hide_dock() {
        dock.hide();
        $('#configureblock').find('.blockinstance-header').empty();
        $('#configureblock').find('.blockinstance-content').empty();
    }

    function editmatrix_update(data) {
        params = data;
        sendjsonrequest('matrixpoint.json.php', params, 'POST', function(results) {
            var hastinymce = false;
            if (typeof tinyMCE !== 'undefined') {
                hastinymce = true;
            }
            if (results.data.class) {
                $('#tablematrix tr:eq(' + celly + ') td:eq(' + cellx + ') span.icon')
                  .attr('class', results.data.class)
                  .attr('title', results.data.title)
                  .data('option', results.data.option)
                  .data('view', results.data.view).empty();
                if (results.data.readyforassessment) {
                    var readyforassessment = parseInt($('#tablematrix tr:eq(' + celly + ') td.completedcount.readyforassessment span:nth-child(2)').text(), 10);
                    $('#tablematrix tr:eq(' + celly + ') td.completedcount.readyforassessment span:nth-child(2)').text(readyforassessment + results.data.readyforassessment);
                }
                if (results.data.dontmatch) {
                    var dontmatch = parseInt($('#tablematrix tr:eq(' + celly + ') td.completedcount.dontmatch span:nth-child(2)').text(), 10);
                    $('#tablematrix tr:eq(' + celly + ') td.completedcount.dontmatch span:nth-child(2)').text(dontmatch + results.data.dontmatch);
                }
                if (results.data.partiallycomplete) {
                    var partiallycomplete = parseInt($('#tablematrix tr:eq(' + celly + ') td.completedcount.partiallycomplete span:nth-child(2)').text(), 10);
                    $('#tablematrix tr:eq(' + celly + ') td.completedcount.partiallycomplete span:nth-child(2)').text(partiallycomplete + results.data.partiallycomplete);
                }
                if (results.data.completed) {
                    var completed = parseInt($('#tablematrix tr:eq(' + celly + ') td.completedcount.completed span:nth-child(2)').text(), 10);
                    $('#tablematrix tr:eq(' + celly + ') td.completedcount.completed span:nth-child(2)').text(completed + results.data.completed);
                }

            }
            if (results.data.tablerows) {
                if ($("#matrixfeedbacklist").has(".annotationfeedbacktable").length == 0) {
                    $("#matrixfeedbacklist").html('<ul class="annotationfeedbacktable list-group list-group-lite list-unstyled"></div>');
                }
                $("#matrixfeedbacklist .annotationfeedbacktable").html(results.data.tablerows);
                if (hastinymce) {
                    textareaid = $("#addfeedbackmatrix textarea").attr('id');
                    tinyMCE.get(textareaid).setContent('');
                }
            }
        });
    }
    // Setup
    carousel_matrix();

    // show / hide tooltips for standard elements
    $('tr.standard div').on('mouseenter', function() {
        $(this).find('.popover').removeClass('d-none');
    }).on('mouseleave', function() {
        $(this).find('.popover').addClass('d-none');
    });

    // Make each standard (row heading description) show when
    // clicking the name or pressing enter key
    // Hide it when leaving

    $('td.code div').on({
        click: function () {
            if ($(this).find('.popover').hasClass('d-none')) {
                $(this).find('.popover').removeClass('d-none');
            }
            else {
                $(this).find('.popover').addClass('d-none');
            }
        },
        mouseenter: function() {
            $(this).find('.popover').removeClass('d-none');
        },
        mouseleave: function() {
            $(this).find('.popover').addClass('d-none');
        },
        keyup: function(event) {
            if (event.keyCode == 13) {
                $(this).trigger("click");
            }
        },
        focusout: function() {
            $(this).closest('div').find('.popover').addClass('d-none');
        }
    });

    // Allow for the expand/collapse of the standards
    $('tr.standard').off(); // clear any existing click state
    $('tr.standard').on('click', function() {
        var section = $(this);
        var id = section.data('standard');
        var state = null;
        if (section.attr('aria-expanded') === 'true') {
            // Set the width of the first <th> so that the pages line doesn't jump about
            var standardnameswidth = $(this).next().find('td:nth-child(1)').outerWidth();
            $('tr:nth-child(2) th:nth-child(1)').css('width', standardnameswidth + 'px');
            section.attr('aria-expanded', 'false');
            section.addClass('collapsed');
            state = 'closed';
        }
        else {
            section.attr('aria-expanded', 'true');
            section.removeClass('collapsed');
            state = 'open';
        }
        var params = {};
        params.section = id;
        params.state = state;
        params.collection = section.data('collection');
        sendjsonrequest('matrixstate.json.php', params, 'POST', function(data) {
            // Use json request to set the info to session
            var description = data.settings.description;
            var container = section.find('.shortname-container');
            if (state == 'closed') {
                container.find('.sr-only.action').html(description.close);
                container.find('.sr-only.status').html(description.sectioncollapsed);
            }
            else {
                container.find('.sr-only.action').html(description.open);
                container.find('.sr-only.status').html('');
            }
        });
        $('tr.examplefor' + id).toggle('600', 'swing').removeClass('d-none');
    });
});
