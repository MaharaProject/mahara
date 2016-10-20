jQuery(function($) {
    // Variable to adjust for the hiding/showing of columns
    var minstart = 1; // The index of the last column before first page column, indexes start at zero so 1 = two columns
    var curstart = 2; // The index of first page currently being displayed
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
    $('#tablematrix td.mid span:not(.disabled)').on('click', function(e) {
        e.preventDefault();
        cellx = $(this).closest('td').index();
        celly = $(this).closest('tr').index();
        var params = {};
        params.framework = frameworkid;
        params.view = $(this).data("view");
        params.option = $(this).data("option");
        sendjsonrequest('matrixpoint.json.php', params, 'POST', function(data) {
            function show_se_desc(id) {
                $("#instconf_smartevidencedesc_container div:not(.description)").addClass('hidden');
                $("#option_" + id).removeClass('hidden');
            }

            dock.show($('#configureblock'), true, false);
            var newpagemodal = $('#configureblock');
            newpagemodal.find('.blockinstance-header').html(data.data.form.title);
            newpagemodal.find('.blockinstance-content').html(data.data.form.content);
            newpagemodal.find('form').each(function() {
                formchangemanager.add($(this).attr('id'));
            });
            deletebutton = newpagemodal.find('.deletebutton');
            // Lock focus to the newly opened dialog
            deletebutton.focus();
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
                    tinyMCE.execCommand('mceRemoveEditor', false, "instconf_text");
                    feedbacktextarea = $("#addfeedbackmatrix textarea");
                    if (feedbacktextarea.length) {
                        tinyMCE.execCommand('mceRemoveEditor', false, feedbacktextarea.attr('id'));
                    }
                    hide_dock();
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
                    tinyMCE.execCommand('mceRemoveEditor', false, "instconf_text");
                    feedbacktextarea = $("#addfeedbackmatrix textarea");
                    if (feedbacktextarea.length) {
                        tinyMCE.execCommand('mceRemoveEditor', false, feedbacktextarea.attr('id'));
                    }
                    hide_dock();
                }
            });
            tinyMCE.idCounter=0;
            if ($("#instconf_text").length) {
                tinyMCE.execCommand('mceAddEditor', false, "instconf_text");
            }
            if ($("#addfeedbackmatrix").length) {
                textareaid = $("#addfeedbackmatrix textarea").attr('id');
                tinyMCE.execCommand('mceAddEditor', false, textareaid);
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
                    else if (item.name == 'text') {
                        values[item.name] = tinyMCE.get('instconf_text').getContent();
                    }
                    else {
                        values[item.name] = item.value;
                    }
                });
                values['tags'] = tags.join();
                values['framework'] = params.framework;
                values['view'] = params.view;
                values['option'] = params.option;
                values['action'] = 'update';
                tinyMCE.execCommand('mceRemoveEditor', false, "instconf_text");
                editmatrix_update(values);
                hide_dock();
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
                    tinyMCE.execCommand('mceRemoveEditor', false, "instconf_text");
                    feedbacktextarea = $("#addfeedbackmatrix textarea");
                    if (feedbacktextarea.length) {
                        tinyMCE.execCommand('mceRemoveEditor', false, feedbacktextarea.attr('id'));
                    }
                    hide_dock();
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
            if (results.data.class) {
                $('#tablematrix tr:eq(' + celly + ') td:eq(' + cellx + ') span')
                  .attr('class', results.data.class)
                  .attr('title', results.data.title)
                  .data('option', results.data.option)
                  .data('view', results.data.view).empty();
                var completed = parseInt($('#tablematrix tr:eq(' + celly + ') td.completedcount').text(), 10);
                $('#tablematrix tr:eq(' + celly + ') td.completedcount').text(completed + results.data.completed);
            }
            if (results.data.tablerows) {
                if ($("#matrixfeedbacklist").has(".annotationfeedbacktable").length == 0) {
                    $("#matrixfeedbacklist").html('<ul class="annotationfeedbacktable list-group list-group-lite list-unstyled"></div>');
                }
                $("#matrixfeedbacklist .annotationfeedbacktable").html(results.data.tablerows);
                textareaid = $("#addfeedbackmatrix textarea").attr('id');
                tinyMCE.get(textareaid).setContent('');
            }
        });
    }
    // Setup
    carousel_matrix();

    // show / hide tooltips for standard elements
    $('td.code div, tr.standard div').hover(
        function() {
            $(this).find('.popover').removeClass('hidden');
        },
        function() {
            $(this).find('.popover').addClass('hidden');
        }
    );
});
