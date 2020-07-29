/**
 * Javascript for the views interface
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2013 Mike Kelly UAL m.f.kelly@arts.ac.uk
 *
 */

// self executing function for namespacing code
(function (ViewManager, $) {
    "use strict";

    //Private Properties
    ////////////////////
    var cookieName = 'contenteditorcollapsed',
        collapsed = false,
        contentEditor = null,
        workspace = null,
        viewThemeSelect = null,
        viewsLoading = null,
        navBuffer = 660;

    // Public Properties
    ViewManager.contentEditorWidth = 145;

    //Public Methods
    ////////////////
    ViewManager.addCSSRules = function() {
        var styleNode = $('<link>');
        styleNode.attr({
            'rel' : 'stylesheet',
            'type': 'text/css',
            'href': config['wwwroot'] + 'theme/views-js.css'
        });
        $('head').prepend(styleNode);
    };

    ViewManager.replaceConfigureBlock = function(data) {
        var oldblock = $('#blockinstance_' + data.blockid);
        if (oldblock.length) {
            // Dispose the block videojs player if exists
            try {
                videojs('audio_' + data.blockid).dispose();
            }
            catch (err) {
            }
            try {
                videojs('video_' + data.blockid).dispose();
            }
            catch (err) {
            }
            // doing it this way stop inline js in the
            // data.data.html breaking things
            var temp = $('<div>').append(data.data.html);
            // Append any inline js to data.data.javascript
            temp.find('*').each(function() {
                if ($(this).prop('nodeName') === 'SCRIPT' && $(this).prop('src') === '') {
                    data.data.javascript += $(this).prop('innerHTML');
                }
            });
            var newblock = temp.find('div.blockinstance');

            $('.blockinstance-header', newblock).on("mousedown", function() {
                    $('.js-col-row .column-content').each(function() {
                        $(this).addClass('block-drop-on', 100);
                    });
                });

            $('.blockinstance-header', newblock).on("mouseup", function() {
                $('.js-col-row .column-content').each(function() {
                    $(this).removeClass('block-drop-on', 500);
                });
            });

            swapNodes(oldblock.get()[0], newblock.get()[0]); // using DOM objects, not jQuery objects so we needn't worry about IDs

            var embedjs = data.data.javascript;
            if (embedjs.indexOf("AC_Voki_Embed") !== -1) {
                var paramsstr = embedjs.substring(embedjs.lastIndexOf("(")+1,embedjs.lastIndexOf(")"));
                var params = paramsstr.split(',');
                if (params.length == 7 ) { // old voki embed code has only 7 parameters
                    // change the last parameter to 1 so it returns the embed code instead of showing it
                    var newScript = 'AC_Voki_Embed(';
                    for (var i = 0; i<params.length-1; i++) {
                        newScript += params[i] + ', ';
                    }
                    newScript += "1)";
                    var embedCode = get_string_ajax('reloadtoview', 'mahara');
                    if (window['AC_Voki_Embed']) {
                        embedCode = eval(newScript);
                    }
                    // add embed code to already loaded page
                    var newChild = document.createElement('div');
                    newChild.innerHTML = embedCode;
                    newblock.get()[0].getElementsByClassName('mediaplayer')[0].appendChild(newChild);
                }
                else {
                  // patch for new voki code, need to reload page so it shows the embed code
                  $(window).trigger('embednewvoki');
                }
            }
            else {
              eval(data.data.javascript);
            }

            rewriteConfigureButton(newblock.find('.configurebutton'));
            rewriteDeleteButton(newblock.find('.deletebutton'));
        }

        hideDock();
        showMediaPlayers();
        setTimeout(function() {
            newblock.find('.configurebutton').trigger("focus");
        }, 1);
    };

    /**
     * Pieform callback function for after a block config form is successfully
     * submitted
     */
    ViewManager.blockConfigSuccess = function(form, data) {
        if (data.formelementsuccess) {
            eval(data.formelementsuccess + '(form, data)');
        }
        if (data.blockid) {
            ViewManager.replaceConfigureBlock(data);
        }
        if (data.otherblocks) {
            jQuery.each(data.otherblocks, function( ind, val ) {
                ViewManager.replaceConfigureBlock(val);
            });
        }
    }

    /**
     * Pieform callback function for after a block config form fails validation
     */
    ViewManager.blockConfigError = function(form, data) {
        if (data.formelementerror) {
            eval(data.formelementerror + '(form, data)');
        }

        // TODO: reduce code duplication between here and getConfigureForm
        // and addConfigureBlock
        var blockinstanceId = jQuery(form).find('#instconf_blockconfig').val();
        var cancelbutton = jQuery('#cancel_instconf_action_configureblockinstance_id_' + blockinstanceId);
        if (jQuery(form).find('#instconf_new').val() == 1) {
            // Wire up the cancel button in the new form
            var deletebutton = jQuery('#configureblock .deletebutton');
            if (cancelbutton.length > 0) {
                cancelbutton.attr('name', deletebutton.attr('name'));
                cancelbutton.off();
                rewriteCancelButton(cancelbutton, blockinstanceId);
            }
        }
        else {
            cancelbutton.on('click',function(e) {
                var configbutton = jQuery('.view-container button[name="action_configureblockinstance_id_' + blockinstanceId + '"]');
                onModalCancel(e, configbutton);
            });
        }
        // Restart any TinyMCE fields if needed
        if (typeof tinyMCE !== 'undefined') {
            jQuery(form).find('textarea.wysiwyg').each(function() {
                tinyMCE.execCommand('mceAddEditor', false, $(this).prop('id'));
            });
        }

    }

    //Private Methods
    /////////////////
    function init() {

        // Set private variables
        contentEditor = $('[data-role="content-toolbar"]');
        workspace = $('[data-role="workspace"]');
        viewThemeSelect = $('#viewtheme-select');

        attachAccordion();
        attachToolbarToggle();


        // Rewrite the configure buttons to be ajax
        rewriteConfigureButtons();

        // Rewrite the delete buttons to be ajax
        rewriteDeleteButtons();

        // Show the keyboard-accessible ajax move buttons
        rewriteMoveButtons();

        // Rewrite the 'add column' buttons to be ajax
        rewriteAddColumnButtons();

        // Rewrite the 'remove column' buttons to be ajax
        rewriteRemoveColumnButtons();

        // Ensure the enabled/disabled state of the add/remove buttons is correct
        checkColumnButtonDisabledState();

        // Setup the 'add block' dialog
        setupPositionBlockDialog();

        showColumnBackgroundsOnSort();

        rewriteViewThemeSelector();

        makeNewBlocksDraggable();
        makeExistingBlocksSortable();


        $(workspace).show();

        $(window).on('resize colresize', function(){
            equalHeights();
        });

        $(window).on('embednewvoki', function() {
            location.reload();
        });

    // images need time to load before height can be properly calculated
     window.setTimeout(function(){
        $(window).trigger('colresize');
     }, 300);


    } // init

    function equalHeights (){

        var rows = $('.js-col-row'),
            i, j,
            height,
            cols;

        for(i = 0; i < rows.length ; i = i + 1){
            height = 0;
            cols = $(rows[i]).find('.column .column-content');
            cols.height('auto');

            for(j = 0; j < cols.length ; j = j + 1){
                height = $(cols[j]).height() > height ? $(cols[j]).height() : height;
            }

            cols.height(height);
            }
    }


    function attachAccordion(){
        // Update the status of a collapsible block category by adding/removing class 'expanded'
        $j('div#content-editor-foldable div.block-category-title').on('click', function() {
            // Collapse all other expanded category
            $j(this).parent().find('div.expanded').toggleClass('expanded');
            $j(this).toggleClass('expanded');
        });

        contentEditor.find('.btn-accordion').accordion({
            icons: false,
            heightStyle: 'content',
            collapsible: true,
            active: false,
            header: ".block-category-title",
            activate: function(event, ui) {
                // When all animation is off ($j.fx.off == true)
                // We can not rely on the class 'ui-state-active' as it is only
                // added when accordion widget animation functions are activated
                var active = $(this).find('.expanded');
                if (active.length) {
                    var category = active.next('div'),
                        categoryid = category.attr('id'),
                        pd = {
                            'id': $('#viewid').val(),
                            'change': 0,
                            'action': 'blocktype_list',
                            'c': categoryid
                        };

                    sendjsonrequest(config['wwwroot'] + 'view/blocks.json.php', pd, 'POST', function(data) {
                        $(category).html(data.data);
                        makeNewBlocksDraggable();
                        showColumnBackgroundsOnSort();

                        // the column has changed size, pass on to listeners
                        $(window).trigger('colresize');
                    });
                    return false;
                }
            }
        });

    }

    function attachToolbarToggle (){

        // collapse the toolbar if the cookie says its collapsed
        if(loadCookieContentEditorCollapsed()){
            $('[data-target="col-collapse"]').addClass('col-collapsed');
        }

        // Attach expand/collapse to click and tap events
        $('[data-trigger="col-collapse"]').on('click tap', function(){
            var target = $(this).closest('[data-target="col-collapse"]');

            target.toggleClass('col-collapsed');

            // trigger toolbar resize
            $(window).trigger('colresize');

            if(target.hasClass('col-collapsed')){
                writeCookieContentEditorCollapsed(true);
            } else {
                writeCookieContentEditorCollapsed(false);
            }
        });
    }



    function loadCookieContentEditorCollapsed() {
        if (document.cookie) {
            var index = document.cookie.indexOf(cookieName),
                valbegin,
                valend,
                isCollapsed;

            if (index !== -1) {

                valbegin = (document.cookie.indexOf("=", index) + 1);
                valend = document.cookie.indexOf(";", index);

                if (valend === -1) {
                    valend = document.cookie.length;
                }

                isCollapsed = document.cookie.substring(valbegin, valend);

                if (isCollapsed === "1") {
                    return true;
                } else {
                    return false;
                }
            }
        }
        return false;
    }

    function writeCookieContentEditorCollapsed(isCollapsed) {
        document.cookie=cookieName+"="+ (isCollapsed ? '1': '0') +"; expires=Wednesday, 01-Aug-2040 08:00:00 GMT";
    }

    function makeNewBlocksDraggable() {

        $('.blocktype-drag').draggable({
            start: function(event, ui) {
                showColumnBackgrounds();
                $(window).trigger('colresize');
            },
            helper: function(event) {
                var original = $(this),
                    helper = $("<div></div>").append(original.clone());

                helper.children().each(function(index) {
                    // Set helper cell sizes to match at least the original sizes
                    $(this).css('min-width', '200px');
                });

                return helper;
            },
            connectToSortable: '.js-col-row .column .column-content',
            stop: function(event, ui) {
                // see also showColumnBackgroundsOnSort for clicking in place without dragging
                hideColumnBackgrounds();
            },
            appendTo: 'body'
        });

        $('.blocktype-drag').off('click keydown'); // remove old event handlers

        $('.blocktype-drag').on('click keydown', function(e) {
            // Add a block when click left button or press 'Space bar' or 'Enter' key
            if (isHit(e) && !$('#addblock').hasClass('in')) {
                e.stopPropagation();
                e.preventDefault();
                startAddBlock($(this));
            }
        });
    }

    function startAddBlock(element) {
        var addblockdialog = $('#addblock');
        addblockdialog.modal('show');

        addblockdialog.one('dialog.end', function(event, options) {
            if (options.saved) {
                addNewBlock(options, element.find('.blocktype-radio').val());
            }
            else {
                element.trigger("focus");
            }
        });

        addblockdialog.find('h4.modal-title').text(get_string('addblock', 'view', element.text()));
        computeColumnInputs(addblockdialog);
        addblockdialog.find('.block-inner').removeClass('d-none');
        addblockdialog.find('.cell-chooser input:first').prop('checked', true);
        addblockdialog.find('.cell-chooser input:first').parent().addClass('focused active');

        addblockdialog.find('.deletebutton').trigger("focus");
        keytabbinginadialog(addblockdialog, addblockdialog.find('.deletebutton'), addblockdialog.find('.cancel'));
    }

    function makeExistingBlocksSortable() {

        // Make existing and new blocks sortable
        $('.column .column-content').sortable({
            handle: '.js-heading',
            items: '.js-blockinstance',
            cursorAt: {left: 100, top: 10},
            connectWith: '.js-col-row .column .column-content',
            placeholder: 'block-placeholder',
            tolerance: "pointer",
            activate: function(event, ui) {
                // Fix for dragging blocks to narrow divs:
                // Wide elements must be centred on narrow divs to make droppable.
                // This is not always evident to the user.
                // Instead set a standard small width when starting to sort.
                // Dynamically setting width on over event doesn't work, as
                // Sortable seems to cache helper proportions.
                // Also if height of dragging block is greater than height
                // row(s) above it then it can't be dropped in that row.
                // Could use a custom version of Sortable in future?
                ui.helper.width(200);
                ui.helper.height('auto');
            },
            beforeStop: function(event, ui) {

                var whereTo = getBlockinstanceCoordinates(ui.helper);

                if (ui.helper.find('.blocktype-radio').length) {

                    addNewBlock(whereTo, ui.helper.find('input.blocktype-radio').val());
                    $('.ui-draggable-dragging').remove();

                } else {
                    //move existing block
                    var uihId = ui.helper.attr('id'),
                        blockinstanceId = uihId.substr(uihId.lastIndexOf('_') + 1);

                    moveBlock(whereTo, blockinstanceId);
                }

                window.setTimeout(function(){
                    $(window).trigger('colresize');
                }, 300);

            },

            update: function(event, ui) {
                $('.js-col-row .column-content').each(function() {
                    $(this).css('min-height', '');
                });
            },

            start: function(event, ui) {
                // Fix for dragging blocks to narrow divs:
                // Wide elements must be centred on narrow divs to make droppable.
                // This is not always evident to the user.
                // Instead set a standard small width when starting to sort.
                // Dynamically setting width on over event doesn't work, as
                // Sortable seems to cache helper proportions.
                // Also if height of dragging block is greater than height
                // row(s) above it then it can't be dropped in that row.
                // Could use a custom version of Sortable in future?
                ui.helper.width($(this).outerWidth());
                ui.helper.height($(this).find('.drag-handle').outerHeight());
            }
        });

    } // end of makeNewBlocksSortable()

    function cellChanged() {

        $(this).closest('.js-cell-chooser').find('.active').removeClass('active');
        $(this).parent().addClass('active');

        var position = $(this).val().split('-'),
            element = workspace.find('.js-col-row').eq(parseInt(position[0], 10) - 1).find('.column').eq(parseInt(position[1], 10) - 1),
            options = [get_string('blockordertopcell')],
            selectbox = $('#newblock_position');

        element.find('.column-content .blockinstance .blockinstance-header').each(function() {
            options.push(get_string('blockorderafter', 'view', $(this).html()));
        });


        selectbox.html('<option>' + options.join('</option><option>') + '</option>');
    }

    function addNewBlock(whereTo, blocktype) {
        var pd = {
                'id': $('#viewid').val(),
                'change': 1,
                'blocktype': blocktype
            };

        if (config.blockeditormaxwidth) {
            pd['cfheight'] = $(window).height() - 100;
        }
        pd['action_addblocktype_row_' + whereTo['row'] + '_column_' + whereTo['column'] + '_order_' + whereTo['order']] = true;

        sendjsonrequest(config['wwwroot'] + 'view/blocks.json.php', pd, 'POST', function(data) {

            var div = $('<div>').html(data.data.display.html),
                blockinstance = div.find('div.blockinstance'),
                configureButton = blockinstance.find('.configurebutton');

            addBlockCss(data.css);
            // Make configure button clickable, but disabled as blocks are rendered in configure mode by default

            if (configureButton) {
                rewriteConfigureButton(configureButton);
                $('#action-dummy').attr('name', 'action_addblocktype_row_' + whereTo['row'] + '_column_' + whereTo['column'] + '_order_' + whereTo['order']);
            }

            insertBlockStub(blockinstance, whereTo);

            if (data.data.configure) {
                showDock($('#configureblock'), true);
                addConfigureBlock(blockinstance, data.data.configure, true);
            } else {
                // if block has has_instance_config() set to false, eg 'comment' block
                rewriteDeleteButton(blockinstance.find('.deletebutton'));
                blockinstance.find('.deletebutton').trigger("focus");
            }
        },
        function() {
            // On error callback we need to reset the Dock
            hideDock();
        });
    }

    function addBlockCss(csslist) {
        $(csslist).each(function(ind, css) {
            if ($('head link[href="'+$(css).attr('href')+'"]').length == 0) {
                $('head').prepend($(css));
            }
        });
    }

    function showColumnBackgrounds() {
        $('.js-col-row .column-content').addClass('block-drop-on', 100);
    }

    function hideColumnBackgrounds() {
        $('.js-col-row .column-content').removeClass('block-drop-on', 500);
    }

    function showColumnBackgroundsOnSort() {
        $('.blockinstance .blockinstance-header, .blocktype-list .blocktype').on("mousedown", function() {
            showColumnBackgrounds();
        });

        $('.blockinstance .blockinstance-header, .blocktype-list .blocktype').on("mouseup", function() {
            hideColumnBackgrounds();
        });
    }


    function insertBlockStub(newblock, whereTo) {
        var columnContent = $('#row_'+whereTo['row']+'_column_'+whereTo['column']).find('div.column-content');
        if (whereTo['order'] == 1) {
            $(columnContent).prepend(newblock);
        }
        else {
            var count = 1;
            columnContent.children().each(function() {
                count++;
                if (count == whereTo['order']) {
                    $(this).after(newblock);
                    return false;
                }
            });

            if (whereTo['order'] > count) {
                columnContent.append(newblock);
            }
        }
    }
    /**
     * Rewrites the blockinstance configure buttons to be AJAX
     */
    function rewriteConfigureButtons() {
        rewriteConfigureButton(workspace.find('.configurebutton'));
    }

    /**
     * Rewrites a configure button to be AJAX
     */
    function rewriteConfigureButton(button) {
        button.off('click');
        button.on('click', function(e) {
            e.stopPropagation();
            e.preventDefault();

            getConfigureForm($(this).closest('.js-blockinstance'));
        });
    }

    function rewriteDeleteButtons() {
        rewriteDeleteButton(workspace.find('.deletebutton'));
    }

    /**
     * Rewrites one delete button to be AJAX
     *
     * @param button: The button to rewrite it for
     * @param pblockinstanceId: If this is being called from the modal popup, we won't be able
     * to retrieve the button's ID. So this optional parameter can supply the button ID directly
     * in that case.
     */
    function rewriteDeleteButton(button, pblockinstanceId) {
        button.off('click');

        button.on('click', function(e) {

            e.stopPropagation();
            e.preventDefault();

            var self = $(this),
                pd = {'id': $('#viewid').val(), 'change': 1},
                blockinstanceId;

            // If pblockinstanceId wasn't passed, retrieve the id from the button.
            if ((pblockinstanceId === undefined) && self.attr('data-id')) {
                blockinstanceId = self.attr('data-id');
            }
            // If pblockinstanceId was passed, then use that.
            else {
                blockinstanceId = pblockinstanceId;
            }

            self.prop('disabled', true);

            if (confirm(get_string('confirmdeleteblockinstance'))) {

                pd[self.attr('name')] = 1;

                sendjsonrequest(config['wwwroot'] + 'view/blocks.json.php', pd, 'POST', function(data) {
                    if (blockinstanceId !== undefined && blockinstanceId !== null) {
                        $('#blockinstance_' + blockinstanceId).remove();
                    }

                    if (!$('#configureblock').hasClass('d-none')) {
                        hideDock();
                        showMediaPlayers();
                        self.trigger("focus");
                    }
                    //reset column heights
                    $('.column-content').each(function() {
                        $(this).css('min-height', '');
                    });

                    self.prop('disabled', false);

                }, function() {

                    self.prop('disabled', false);
                });
            }
            else {
                self.prop('disabled', false);
            }
        });
    }

    /*
     * Shows all keyboard-accessible ajax move buttons
     */
    function rewriteMoveButtons() {
        rewriteMoveButton(workspace.find('.keyboardmovebutton'));
    }

    /*
     * Shows and sets up one keyboard-accessible ajax move button
     *
     */
    function rewriteMoveButton(button) {

        button.removeClass('d-none');

        button.on('click', function(e) {
            e.stopPropagation();
            e.preventDefault();

            computeColumnInputs($('#newblock'));

            var self = $(this),
                addblockdialog = $('#newblock').removeClass('d-none');
                prevcell = self.closest('.column-content'),
                order = prevcell.children().index(self.closest('.blockinstance')),
                row = workspace.find('.js-col-row').index(self.closest('.js-col-row')),
                column = self.closest('.js-col-row').children().index(self.closest('.column')),
                radio = addblockdialog.find('.cell-chooser').children().eq(row).find('input').eq(column),
                changefunction = function() {
                    if (radio.prop('checked')) {
                        $('#newblock_position option').eq(order + 1).remove();
                    }
                };


            radio.on('change', changefunction);
            radio.prop('checked', true).trigger('change');

            $('#newblock_position').prop('selectedIndex', order);

            addblockdialog.one('dialog.end', function(event, options) {
                if (options.saved) {
                    var blockinstanceId = self.attr('data-id'),
                        newcell,
                        currentblock,
                        lastindex;

                    moveBlock(options, blockinstanceId);

                    newcell = workspace.find('.js-col-row').eq(options['row'] - 1)
                        .find('.column-content').eq(options['column'] - 1);

                    currentblock = self.closest('.blockinstance');
                    lastindex = newcell.children().length;

                    if (newcell[0] == prevcell[0]) {
                        lastindex -= 1;
                    }

                    newcell.append(currentblock);
                    options['order'] -= 1;
                    if (options['order'] < lastindex) {
                        newcell.children().eq(options['order']).before(newcell.children().last());
                    }
                }

                radio.off('change', changefunction);
                self.trigger("focus");
            });

            addblockdialog.find('h4.modal-title').text(self.attr('alt'));

            addblockdialog.find('.deletebutton').trigger("focus");

            keytabbinginadialog(addblockdialog, addblockdialog.find('.deletebutton'), addblockdialog.find('.cancel'));
        });
    }

    function computeColumnInputs(dialog) {
        var inputcontainer = dialog.find('.blockinstance-content #newblock_cellchooser_container'),
            result = $('<div>').addClass('cell-chooser js-cell-chooser'),
            firstcell,
            rows = workspace.find('.js-col-row'),
            i,
            j,
            row,
            cols,
            radios,
            label,
            value,
            radio;


        for(i = 0; i < rows.length; i = i + 1){

            row = $('<div class="cell-row">');
            cols = $(rows[i]).find('.column');
            radios = [];

            for(j = 0; j < cols.length; j = j + 1){

                value = (i + 1) + '-' + (j + 1); //rowNumber-colNumber
                radio = $('<input>').attr({
                    'type': 'radio',
                    'style': $(cols[j]).attr('style'),
                    'id': 'cellchooser_' + value,
                    'name': 'cellchooser',
                    'value': value
                });



                label = $('<label>').addClass('cell').attr('for', 'cellchooser_' + value).attr('style', $(cols[j]).attr('style'));

                label.append(radio)
                    .append($('<span>').addClass('pseudolabel mll').html(get_string('cellposition', 'view', i + 1, j + 1)));

                row.append(label);

                radio.on('change', cellChanged);

                radio.on('focus', function() {
                    $(this).parent().addClass('focused');
                });

                radio.on('blur', function() {
                    $(this).parent().removeClass('focused');
                });

            }

            result.append(row);
        }

        dialog.find('.dock-loading').remove();
        inputcontainer.html('').append(result);

        firstcell = inputcontainer.find('input').first();
        firstcell.prop('checked', true);
        cellChanged.call(firstcell);
    }


    function moveBlock(whereTo, instanceId) {
        var pd = {
            'id': $('#viewid').val(),
            'change': 1
        };
        if (config.blockeditormaxwidth) {
            pd['cfheight'] = $(window).height() - 100;
        }
        pd['action_moveblockinstance_id_' + instanceId + '_row_' + whereTo['row'] + '_column_' + whereTo['column'] + '_order_' + whereTo['order']] = true;
        sendjsonrequest(config['wwwroot'] + 'view/blocks.json.php', pd, 'POST', function(data) {
            if (data.data.html) {
                $('#blockinstance_' + instanceId + ' .blockinstance-content').html(data.data.html);
            }
            hideColumnBackgrounds();
        });
    }

    /**
     * Rewrites cancel button to remove a block
     */
    function rewriteCancelButton(button, blockinstanceId) {
        button.on('click', function(event) {

            event.stopPropagation();
            event.preventDefault();

            var pd = {'id': $('#viewid').val(), 'change': 1};

            pd[button.attr('name')] = 1;

            sendjsonrequest(config['wwwroot'] + 'view/blocks.json.php', pd, 'POST', function(data) {

                $('#blockinstance_' + blockinstanceId).remove();

                if (!$('#configureblock').hasClass('d-none')) {
                    hideDock();
                    showMediaPlayers();
                    button.trigger("focus");
                }
            });

        });
    }

    /**
     * Rewrites the add column buttons to be AJAX
     *
     */
    function rewriteAddColumnButtons() {
        $('[data-action="addcolumn"]').each(function() {

            $(this).off('click'); // prevent double binding

            $(this).on('click', function(e) {
                e.stopPropagation();
                e.preventDefault();

                // Work around for a konqueror bug - konqueror passes onclick
                // events to disabled buttons
                if (!$(this).disabled) {
                    $(this).prop('disabled', true);

                    var name = $(this).attr('name'),
                        match = name.match(/action_addcolumn_row_(\d+)_before_(\d+)/),
                        rowid = parseInt(match[1], 10),
                        colid = parseInt(match[2], 10),
                        pd   = {'id': $('#viewid').val(), 'change': 1};


                    pd['action_addcolumn_row_' + rowid + '_before_' + colid] = 1;

                    sendjsonrequest(config['wwwroot'] + 'view/blocks.json.php', pd, 'POST', function(data) {

                        addColumn(rowid, colid, data);
                        checkColumnButtonDisabledState();

                    }, function() {

                        checkColumnButtonDisabledState();

                    });
                }

            });
        });
    }

    /**
     * Rewrite the remove column buttons to be AJAX
     *
     * If the first parameter is a string/element, only the buttons below that
     * element will be rewritten
     */
    function rewriteRemoveColumnButtons() {
        workspace.find('.removecolumn').off('click'); // prevent double binding

        workspace.find('.removecolumn').on('click', function(e) {

            e.stopPropagation();
            e.preventDefault();;

            // Work around for a konqueror bug - konqueror passes onclick
            // events to disabled buttons
            if (!this.disabled) {
                $(this).attr('disabled', 'disabled');

                var name = $(this).attr('name'),
                    match = name.match(/action_removecolumn_row_(\d+)_column_(\d+)/),
                    rowid = parseInt(match[1], 10),
                    colid = parseInt(match[2], 10),
                    pd   = {'id': $('#viewid').val(), 'change': 1};

                pd['action_removecolumn_row_' + rowid + '_column_' + colid] = 1;
                sendjsonrequest(config['wwwroot'] + 'view/blocks.json.php', pd, 'POST', function(data) {

                    removeColumn(rowid, colid);
                    checkColumnButtonDisabledState();

                }, function() {

                    checkColumnButtonDisabledState();

                });
            }
        });
    }

    /**
     * Disables the 'add column' buttons
     */
    function checkColumnButtonDisabledState() {
        // For each row
        workspace.find('.js-col-row').each(function() {

            // Get the existing number of columns
            var match = $(this).find('.column'),
                numColumns = match.length,
                state = (numColumns === 5);


            $('.addcolumn', $(this)).each(function() {
                if (state) {
                    $(this).prop('disabled', true);
                }
                else {
                    $(this).prop('disabled', false);
                }
            });

            state = (numColumns === 1);

            $('.removecolumn', $(this)).each(function() {
                if (state) {
                    $(this).prop('disabled', true);
                }
                else {
                    $(this).prop('disabled', false);
                }
            });
        });
    }

    /**
     * return true if the mousedown is <LEFT BUTTON> or the keydown is <Space> or <Enter>
     */
    function isHit(e) {
        return (e.which === 1 || e.button === 1 || e.keyCode === $j.ui.keyCode.SPACE || e.keyCode === $j.ui.keyCode.ENTER);
    }
    /*
     * Initialises the dialog used to add and move blocks
     */
    function setupPositionBlockDialog() {

        $('#newblock .cancel, #addblock .deletebutton').on('mousedown keydown', function(e) {
            if (isHit(e) || e.keyCode === $j.ui.keyCode.ESCAPE) {
                closePositionBlockDialog(e, {'saved': false});
            }
        });

        $('#newblock .submit').on('click keydown', function(e) {
            if (isHit(e)) {
                var position = $('#newblock .cell-chooser input:checked').val().split('-'),
                    order = $('#newblock_position').prop('selectedIndex') + 1;

                closePositionBlockDialog(e, {
                    'saved': true,
                    'row': position[0], 'column': position[1], 'order': order
                });
            }
        });

        // To allow for pushing enter button when on selecting the 'cell' column line
        $('#newblock').on('keydown', function(e) {
            if (e.keyCode == 13) {

                var position = $('#newblock .cell-chooser input:checked').val().split('-'),
                    order = $('#newblock_position').prop('selectedIndex') + 1;

                closePositionBlockDialog(e, {
                    'saved': true,
                    'row': position[0], 'column': position[1], 'order': order
                });
            }
        });
    }

    /*
     * Closes the add/move block dialog
     */
    function closePositionBlockDialog(e, options) {
        e.stopPropagation();
        e.preventDefault();

        var addblockdialog = $('#addblock');

        options.trigger = e.type;
        addblockdialog.modal('hide').trigger('dialog.end', options);
    }

    /*
     * Trigger an empty dock
     */
    function showDock(newblock, replaceContent) {
        dock.show(newblock, replaceContent, false);
    }

    function getConfigureForm(blockinstance) {

        var button = blockinstance.find('.configurebutton'),
            blockinstanceId = blockinstance.attr('data-id'),
            content = blockinstance.find('.js-blockinstance-content'),
            oldContent = content.html(),
            loading = $('<span>').attr('class', 'icon icon-spinner icon-spin block-loading'),
            pd = {'id': $('#viewid').val(), 'change': 1};


        showDock($('#configureblock'), true);

        // delay processing so animation can complete smoothly
        // this may not be neccessary once json requests are done with jquery
        setTimeout(function(){

            pd[button.attr('name')] = 1;

            sendjsonrequest('blocks.json.php', pd, 'POST', function(data) {

                addConfigureBlock(blockinstance, data.data);


                $('#action-dummy').attr('name', button.attr('name'));

                var cancelButton = $('#cancel_instconf_action_configureblockinstance_id_' + blockinstanceId),
                    heightTarget = $('#configureblock').find('[data-height]');

                if(heightTarget.length > 0){
                    limitHeight(heightTarget);
                }

                cancelButton.on('click',function(e) {
                    onModalCancel(e, button);
                });
            });

        }, 500);


    }

    function onModalCancel(e, button){
        e.stopPropagation();
        e.preventDefault();

        hideDock();
        showMediaPlayers();
        button.trigger("focus");
    }

    function limitHeight(target) {

        $(window).on('resize', function(){

            target.height('auto'); //reset so measurements will be accurate

            var targetHeight = $(target).find(target.attr('data-height')).height(),
                windowHeight = $(window).height() - 50,
                height = windowHeight < targetHeight ? windowHeight : targetHeight;


            target.height(height);
        });
    }

    /**
     * This function is called before the modal is opened. In theory it could be used to make changes
     * to the display of elements before the modal opens (for things that might interfere with the
     * modal.
     *
     * It's currently empty because everything works fine without it.
     */
    function hideMediaPlayers() {
    }

    /**
     * This function is called after the modal is closed. If you have deactivated things using
     * hideMediaPlayers, this can be a good place to re-open them.
     *
     * It is also used as a hacky place to hold other things that should be triggered after the
     * modal closes.
     */
    function showMediaPlayers() {
        if (tinyMCE && tinyMCE.activeEditor && tinyMCE.activeEditor.id) {
            tinyMCE.execCommand('mceRemoveEditor', false, tinyMCE.activeEditor.id);
        }
        if (config.mathjax && MathJax !== undefined) {
            MathJax.Hub.Queue(["Typeset", MathJax.Hub]);
        }
    }

    /**
     * Wire up the view theme selector
     */
    function rewriteViewThemeSelector() {
        if (!viewThemeSelect) {
            return;
        }
        var currentTheme = $('option:selected', viewThemeSelect).val();
        viewThemeSelect.on('change', function() {
                if ($('option:selected', viewThemeSelect).val() != currentTheme) {
                    $(viewThemeSelect).closest('form').trigger('submit');
                }
        });
    }

    function addConfigureBlock(oldblock, configblock, removeoncancel) {

        hideMediaPlayers();

        var temp = $('<div>').html(configblock.html),
            newblock = $('#configureblock'),
            title = temp.find('.blockinstance .blockinstance-header').html(),
            content = temp.find('.blockinstance .blockinstance-content').html(),
            blockinstanceId  = temp.find('.blockinstance').attr('data-id'),
            deletebutton,
            cancelbutton;



        newblock.find('.blockinstance-header').html(title);
        newblock.find('.blockinstance-content').html(content);

        deletebutton = newblock.find('.deletebutton');
        deletebutton.off().attr('name', 'action_removeblockinstance_id_' + blockinstanceId);

        // Lock focus to the newly opened dialog
        deletebutton.trigger("focus");

        if (removeoncancel !== undefined) {
            rewriteDeleteButton(deletebutton, blockinstanceId);

            cancelbutton = $('#cancel_instconf_action_configureblockinstance_id_' + blockinstanceId);

            if (cancelbutton.length > 0) {
                cancelbutton.attr('name', deletebutton.attr('name'));
                cancelbutton.off();
                rewriteCancelButton(cancelbutton, blockinstanceId);
            }
        } else {

            deletebutton.on('click', function(e) {
                e.stopPropagation();
                e.preventDefault();

                hideDock();
                showMediaPlayers();

                setTimeout(function() {
                    oldblock.find('.configurebutton').trigger("focus");
                }, 1);
            });
        }


        $(window).trigger('maharagetconfigureform');

        // still needed for tinymce :-/
        // @todo - find a way to remove the eval
        (function() {
            eval(configblock.javascript);
        })();

        keytabbinginadialog(newblock, newblock.find('.deletebutton'), newblock.find('.cancel'));


    } // end of addConfigureBlock()


    function hideDock() {
      // Reset the form change checker
      var form = formchangemanager.find('instconf');
      if (form !== null) {
          form.unbind();
          form.reset();
      }

      dock.hide();
    }

    function swapNodes(a, b) {
        var aparent = a.parentNode;
        var asibling = a.nextSibling===b? a : a.nextSibling;
        b.parentNode.insertBefore(a, b);
        aparent.insertBefore(b, asibling);
    }

    /**
     * Find the co-ordinates of a given block instance
     *
     * This returns a {row: x, column: y, order: z} hash
     */
    function getBlockinstanceCoordinates(blockinstance) {
        // Work out where to send the block to
        var columnContainer = $('.block-placeholder').closest('div.column'),
            row = parseInt(columnContainer.attr('id').match(/row_(\d+)_column_(\d+)/)[1], 10),
            column = parseInt(columnContainer.attr('id').match(/row_(\d+)_column_(\d+)/)[2], 10),
            columnContent = columnContainer.find('div.column-content'),
            order  = 0;


        columnContent.children().each(function() {
            if ($(this).attr('id') == blockinstance.attr('id')) {
                order++;
                return false;
            }
            else if ($(this).hasClass('blockinstance')) {
                order++;
            }
        });
        return {'row': row, 'column': column, 'order': order};
    }

    function renumberColumns(rowid) {
        var columns = $('#row_'+rowid).find('.column'),
            numColumns = columns.length,
            addrightbutton,
            i;


        // Renumber all columns
        for (i = 1; i <= numColumns; i = i + 1) {

            $(columns[i - 1]).attr('id', 'row_' + rowid + '_column_' + i);

            $('.addcolumn', $('#row_' + rowid + '_column_' + i)).attr('name', 'action_addcolumn_row_' + rowid + '_before_' + i);
            $('.removecolumn', $('#row_' + rowid + '_column_' + i)).attr('name', 'action_removecolumn_row_' + rowid + '_column_' + i);

        }

        // If the column being added is the very first one, the 'left' add column button needs to be removed
        $('#row_' + rowid + '_column_2 .js-add-column-left').remove();
        $('#row_' + rowid + '_column_2 .btn-three').removeClass('btn-three').addClass('btn-two');

        // Renumber the columns classes of the remaining columns, and remove any set widths
        $(columns).removeClass('columns1 columns2 columns3 columns4 columns5');
        $(columns).addClass('columns' + numColumns);
        $(columns).attr('style', '');

        //Update last
        $('.lastcolumn').removeClass('lastcolumn');
        $('.js-col-row .column:last-child').addClass('lastcolumn');

        // Move the add button between the columns
        columns.find('.js-add-column-right').removeClass('js-add-column-right').addClass('js-add-column-center');
        $('.js-col-row .column:last-child').find('.addcolumn').addClass('js-add-column-right').removeClass('js-add-column-center');
    }

    /**
     * Adds a column to the view
     */
    function addColumn(rowid, colid, data) {

        // Get the existing number of columns
        var tempDiv = $('<div>');

        /// Now we insert the new column into the DOM. Inserting the HTML into a
        // new element and then into the DOM means we can add the new column
        // without changing any of the existing DOM tree (and thus destroying
        // events)
        tempDiv.html(data.data);

        if (colid === 1) {
            $(':first', tempDiv).insertBefore('#row_' + rowid + '_column_1');
        }
        else {
            $(':first', tempDiv).insertAfter('#row_' + rowid + '_column_' + (colid - 1));
        }

        renumberColumns(rowid);

        // Wire up the new column buttons to be AJAX
        rewriteAddColumnButtons();
        rewriteRemoveColumnButtons();
        makeExistingBlocksSortable();

    }

    /**
     * Removes a column from the view, sizes the others to take its place and
     * moves the blockinstances in it to the other columns
     */
    function removeColumn(rowid, colid) {
        var addColumnLeftButtonContainer,
            blockInstances = $('#row_' + rowid + '_column_' + colid + ' .blockinstance'),
            columns = $('#row_'+rowid).find('.column'),
            numColumns = columns.length,
            i = 1,
            currentTallest;

        if (colid === 1) {
            // We are removing the first column, which has the button for adding a column to the left of itself. We want to keep this
            addColumnLeftButtonContainer = $('#row_' + rowid).find('.js-add-column-left').first();
        }

        // Remove the column itself
        $('#row_' + rowid + '_column_' + colid).remove();

        renumberColumns(rowid);

        if (addColumnLeftButtonContainer) {
            $('#row_' + rowid + '_column_1 .js-remove-column').before(addColumnLeftButtonContainer);
            $('#row_' + rowid + '_column_1 .btn-two').removeClass('btn-two').addClass('btn-three');
        }


        // Put the block instances that were in the removed column into the other columns
        $(blockInstances).each(function() {
            $('#row_' + rowid + '_column_' + i + ' .column-content').append($(this));
            if (i < (numColumns - 1)) {
                i++;
            }
            $(this).find('.column-content').each(function(i) {
                if ($(this).height() > currentTallest) {
                    currentTallest = $(this).height();
                }
            });
            $(this).find('.column-content').css({'min-height': currentTallest});
        });

        rewriteAddColumnButtons();
        rewriteRemoveColumnButtons();
    }


    /**
     * Initialise
     *
     */
    $(function() {
        init();
        /**
         * changes the intructions so they are for ajax
         */
        $('#blocksinstruction').html(strings['blocksinstructionajaxlive']);
        $('#viewinstructions-dropdown').on('hide.bs.collapse show.bs.collapse', function(event) {
            var pd = {
                'viewid': $('#viewid').val(),
                'action': event.type
            };
            sendjsonrequest(config['wwwroot'] + 'view/instructions.json.php',
                pd, 'POST', function() {}
            );
        });
    });

}( window.ViewManager = window.ViewManager || {}, jQuery ));

ViewManager.addCSSRules();

/**
 * Pieform callback method. Just a wrapper around the ViewManager function,
 * because Pieforms doesn't like periods in callback method names.
 * @param form
 * @param data
 */
function blockConfigSuccess(form, data) {
    return ViewManager.blockConfigSuccess(form, data);
}

/**
 * Pieform callback method. Just a wrapper around the ViewManager function,
 * because Pieforms doesn't like periods in callback method names.
 * @param form
 * @param data
 */
function blockConfigError(form, data) {
    return ViewManager.blockConfigError(form, data);
}
