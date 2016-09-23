//self executing function for namespacing code
(function( CustomLayoutManager, $, undefined ) {

    // Public Methods
    CustomLayoutManager.customlayout_add_row = function() {
        var numrows = parseInt($('#viewlayout_customlayoutnumrows').val(), 10);
        if ((numrows < get_max_custom_rows()) && (numrows >= 1)) {
            var newid;
            var newrow = $('#customrow_' + numrows).clone();
            var currentncols = $('#customrow_' + numrows).find('#selectnumcolsrow_' + numrows).val();
            var currentcollayout = $('#customrow_' + numrows).find('#selectcollayoutrow_' + numrows).val();

            newrow.find('.customrowtitle').html('<strong>' + get_string('rownr', 'view', numrows + 1) + '</strong>');
            newrow.attr('id', 'customrow_' + (numrows + 1));

            newid = 'selectnumcolsrow_' + (numrows + 1);
            newrow.find('#selectnumcolsrow_' + numrows).val(currentncols);
            newrow.find('#selectnumcolsrow_' + numrows).attr('id', newid);
            newrow.find('label[for="selectnumcolsrow_' + numrows + '"]').attr('for', newid);

            newid = 'selectcollayoutrow_' + (numrows + 1);
            newrow.find('#selectcollayoutrow_' + numrows).val(currentcollayout);
            newrow.find('#selectcollayoutrow_' + numrows).attr('id', 'selectcollayoutrow_' + (numrows + 1));
            newrow.find('label[for="selectcollayoutrow_' + numrows + '"]').attr('for', newid);

            if ((oldremovebutton = $(newrow).find('button')).length != 0) {
                oldremovebutton.attr('class', 'pull-left btn btn-sm btn-default removecustomrow_' + (numrows + 1));
            }
            else {
                // wanring: classes are modified above for any subsequent button instances
                newrow.append('<button name="removerow" class="pull-left btn btn-sm btn-default removecustomrow_' + (numrows + 1) + '" onclick="CustomLayoutManager.customlayout_remove_row(this)"><span class="icon icon-lg icon-trash text-danger"></span><span class="hidden-xs pls"> ' + get_string('removethisrow', 'view') + '</span></button>');
            }
            $('#customrow_' + numrows).after(newrow);
            $('#viewlayout_customlayoutnumrows').val(numrows + 1);
            customlayout_change_layout();
            newrow.find('select').first().focus();
        }

        if (parseInt($('#viewlayout_customlayoutnumrows').val(), 10) >= get_max_custom_rows()) {
            $('#addrow').attr('disabled', 'disabled');
        }
    };

    CustomLayoutManager.customlayout_remove_row = function(row) {
        var numrows = parseInt($('#viewlayout_customlayoutnumrows').val(), 10);
        $(row).closest('.customrow').remove();
        $('#viewlayout_customlayoutnumrows').val(numrows - 1);
        var inc = 1;
        $('#customrows .customrow').each(function() {
            $(this).find('.customrowtitle').html('<strong>' + get_string('rownr', 'view', inc) + '</strong>');
            $(this).attr('id', 'customrow_' + inc);
            $(this).find('.selectnumcols').attr('id', 'selectnumcolsrow_' + inc);
            $(this).find('input').attr('class', 'removecustomrow_' + inc);
            $(this).find('.selectcollayout').attr('id', 'selectcollayoutrow_' + inc++);
        });
        customlayout_change_layout();

        if (parseInt($('#viewlayout_customlayoutnumrows').val(), 10) < get_max_custom_rows()) {
            $('#addrow').removeAttr('disabled');
        }
    };

    CustomLayoutManager.customlayout_change_numcolumns = function(columnoptions) {
        var currentrow = $(columnoptions).attr('id').substr($(columnoptions).attr('id').lastIndexOf('_') + 1);
        var numcols = parseInt(columnoptions.options[columnoptions.selectedIndex].value, 10);
        // reverse in order to select the first option
        $.each($('#selectcollayoutrow_' + currentrow + ' > option').get().reverse(), function() {
            if (this.text.split('-').length != numcols) {
                $(this).prop('disabled', true);
                $(this).prop('selected', false);
            }
            else {
                $(this).prop('disabled', false);
                $(this).prop('selected', true);
            }
        });
        customlayout_change_layout();
    };

    CustomLayoutManager.customlayout_change_column_layout = function() {
        customlayout_change_layout();
    };

    CustomLayoutManager.customlayout_submit_layout = function() {
        var numrows = parseInt($('#viewlayout_customlayoutnumrows').val(), 10);
        var collayouts = '';
        for (i=0; i<numrows; i++) {
            collayouts += '_row' + [i+1] + '_' + $('#selectcollayoutrow_' + (i+1)).val();
        }

        if (typeof formchangemanager !== 'undefined') {
            formchangemanager.setFormState($('#viewlayout'), FORM_CHANGED);
        }

        var pd   = {
             'id': $('#viewlayout_viewid').val(),
             'change': 1
             }
        pd['action_addcustomlayout_numrows_' + numrows + collayouts] = 1;
        sendjsonrequest(config['wwwroot'] + 'view/blocks.json.php', pd, 'POST', function(data) {

            var layoutid = data.data.layoutid;

            if (data.data.newlayout) {
                // insert new layout
                // clone and tweak
                var clone = $('.advancedlayoutselect input[type=radio]:first').parent().parent().clone();
                var id = 'viewlayout_advancedlayoutselect' + unique_timestamp();
                $('label', clone).attr('for', id).text(data.data.text);
                $('input', clone).attr('id', id).val(layoutid);
                $('svg', clone).replaceWith(data.data.layoutpreview);

                //insert into appropriate row
                var rowcontainer = $('#viewlayout_advancedlayoutselect_row'+numrows);
                if (rowcontainer.length) {
                    $(rowcontainer).append(clone);
                }
                else {
                    // make a row for it
                    var rowtitlediv = $('<h3>').attr('class', 'title');
                    rowtitlediv.html('<strong>' + get_string('nrrows', 'view', numrows) + '</strong>');
                    var rowcontainer = $('<div>').attr({
                        'id': 'viewlayout_advancedlayoutselect_row' + numrows,
                        'class': 'fr'
                    });
                    var hr = $('<hr>').attr('class', 'cb');
                    $(rowcontainer).append(clone);
                    $('#viewlayout_advancedlayoutselect_container').append(rowtitlediv);
                    $('#viewlayout_advancedlayoutselect_container').append(rowcontainer);
                    $('#viewlayout_advancedlayoutselect_container').append(hr);


                }
            }

            $('#viewlayout_advancedlayoutselect_container').collapse('show');

            // select and highlight layout
            var radio = $('.advancedlayoutselect :radio[value=' + layoutid +']');
            $(radio).attr("checked","checked");
            $('#viewlayout_layoutselect').val(layoutid);
            highlight_layout($(radio).parent());
            link_thumbs_to_radio_buttons();

            $(radio).focus();

        });
    };

    // Private Methods
    ////////////////////

    function init() {
        $('#viewlayout_basic_container legend a, #viewlayout_adv_container legend a').click(function(event) {
            var containerclicked = $( $(this).context ).attr('aria-controls');
            var basiccollapse = advancedcollapse = customcollapse = 'hide';
            if (containerclicked == '#viewlayout_layoutselect_container') {
                basiccollapse = 'toggle';
            }
            else if (containerclicked == '#viewlayout_advancedlayoutselect_container') {
                advancedcollapse = 'toggle';
            }
            else if (containerclicked == '#viewlayout_createcustomlayout_container') {
                customcollapse = 'toggle';
            }
            $('#viewlayout_layoutselect_container').collapse(basiccollapse);
            $('#viewlayout_advancedlayoutselect_container').collapse(advancedcollapse);
            $('#viewlayout_createcustomlayout_container').collapse(customcollapse);

            var layoutselected = $('#viewlayout_layoutselect').val();
            var layoutfallback = $('#viewlayout_layoutfallback').val();
            if ($('.layoutselect :radio[value=' + layoutselected +']').length ) {
                $('.layoutselect :radio[value=' + layoutselected +']').attr("checked","checked");
            }
            else {
                $('.layoutselect :radio[value=' + layoutfallback + ']').attr("checked","checked");
                $('#viewlayout_layoutselect').val(layoutfallback);
            }
            if ($('.advancedlayoutselect :radio[value=' + layoutselected +']').length ) {
                $('.advancedlayoutselect :radio[value=' + layoutselected +']').attr("checked","checked");
            }
            else {
                $('.advancedlayoutselect :radio[value=' + layoutfallback + ']').attr("checked","checked");
                $('#viewlayout_layoutselect').val(layoutfallback);
            }
        });

        $("input[name='layoutselect']").change(function(event) {
            $('#viewlayout_layoutselect').val($(this).val());
        });

        $("input[name='advancedlayoutselect']").change(function(event) {
            $('#viewlayout_layoutselect').val($(this).val());
        });

        link_thumbs_to_radio_buttons();

        $('#basiclayouthelp').click(function(event) {
            contextualHelp("viewlayout","layoutselect","core","view","","",this);
            return false;
        });
        $('#customlayouthelp').click(function(event) {
            contextualHelp("viewlayout","createcustomlayouttitle","core","view","","",this);
            return false;
        });

        $('#togglecustomlayoutoptions').hide();
        $('#createcustomlayouttitle').click(function(event) {
            $('#viewlayout_createcustomlayout_container').toggleClass("collapsed");
            $('#togglecustomlayoutoptions').toggle();
        });
    }

    function link_thumbs_to_radio_buttons() {
        $('.layoutoption > .thumbnail').each(function(event) {
            $(this).click(function(e) {
                $(this).find(':radio').prop('checked', true);
                $('#viewlayout_layoutselect').val( $(this).find(':radio').val() );
            });
        });
    }

    function unique_timestamp() {
          var time = new Date().getTime();
          while (time == new Date().getTime());
          return new Date().getTime();
    }

    function highlight_layout (element) {
        $(element).css('background', '#555');
        $(element).animate({backgroundColor:'#EEE'}, 3000);
    }

    function customlayout_change_layout() {
        var numrows = parseInt($('#viewlayout_customlayoutnumrows').val(), 10);
        var collayouts = '';
        for (i=0; i<numrows; i++) {
            collayouts += '_row' + [i+1] + '_' + $('#selectcollayoutrow_' + (i+1)).val();
        }

        var pd   = {
             'id': $('#viewlayout_viewid').val(),
             'change': 1
             }
        pd['action_updatecustomlayoutpreview_numrows_' + numrows + collayouts] = 1;
         sendjsonrequest(config['wwwroot'] + 'view/blocks.json.php', pd, 'POST', function(data) {
            $('#custompreview').html(data.data);
         });

        if (typeof formchangemanager !== 'undefined') {
            formchangemanager.setFormState($('#viewlayout'), FORM_CHANGED);
        }
    }

    $(document).ready(function() {
        init();
    });

}( window.CustomLayoutManager = window.CustomLayoutManager || {}, jQuery ));
