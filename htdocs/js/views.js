/**
 * Javascript for the views interface
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
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
            var newblock = temp.find('div.gridstackblock');
            // check if block has header link for quick edit
            var oldheader = oldblock.find('.block-header.quick-edit');
            if (oldheader.length) {
                var replaceheader = '';
                if (data.data.blockheader.length) {
                    replaceheader = $(data.data.blockheader);
                    replaceheader.removeClass('d-none');
                }
                else {
                    replaceheader = oldheader;
                }
                if (newblock.find('.block-header.quick-edit').length > 0) {
                    // remove new one as it's events are not present
                    newblock.find('.block-header.quick-edit').remove();
                }

                // add the wired up header to the new block
                newblock.prepend(replaceheader);
            }

            swapNodes(oldblock.get()[0], newblock.get()[0]); // using DOM objects, not jQuery objects so we needn't worry about IDs

            if (typeof(data.draftclass) != 'undefined' && data.draftclass && !$(newblock).closest(".grid-stack-item-content").hasClass('draft')) {
                $(newblock).closest(".grid-stack-item-content").addClass('draft');
            }
            else {
                $(newblock).closest(".grid-stack-item-content").removeClass('draft');
            }

            var embedjs = data.data.javascript;
            if (typeof(embedjs)!='undefined' && embedjs.indexOf("AC_Voki_Embed") !== -1) {
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
        if (data.closemodal) {
            hideDock();
            showMediaPlayers();
            setTimeout(function() {
                newblock.find('.configurebutton').trigger("focus");
            }, 1);
        }
        else {
            return newblock;
        }
        if (typeof(window.dragonDrop) != 'undefined') {
            var list = $('.grid-stack')[0];
            window.dragonDrop.initElements(list);
        }

        if (data.data.blockheader && data.data.blockheader.length) {
            activateModalLinks();
        }
    };

    /**
     * Pieform callback function for after a block config form is successfully
     * submitted
     */
    ViewManager.blockConfigSuccess = function(form, data) {
        if (data.formelementsuccess) {
            eval(data.formelementsuccess + '(form, data)');
        }
        data.closemodal = true;
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

        $(window).trigger('maharagetconfigureform');

        // Restart any TinyMCE fields if needed
        if (typeof tinyMCE !== 'undefined') {
            jQuery(form).find('textarea.wysiwyg').each(function() {
                tinyMCE.execCommand('mceAddEditor', false, $(this).prop('id'));
            });
        }

    }

    ViewManager.blockOptions = function() {
        $('#placeholderlist .card-option .card').each(function (idx, val) {
            $(val).off();
            $(val).on('click', function(ev, d) {
                ev.stopPropagation();
                ev.preventDefault();
                var blockid = $(ev.currentTarget).data('blockid');
                var option = $(ev.currentTarget).data('option');
                var title = encodeURIComponent($('#instconf_title').val());
                title = title.replace(/\./g, "%2E"); // Deal with . in the title
                title = title.replace(/\_/g, "%5F"); // Deal with _ in the title clashing with action string
                var isnew = $('#instconf_new').val() == '1' ? '1' : '0';
                var pd = {
                    'id': $('#viewid').val(),
                    'change': 1,
                    'blocktype': 'placeholder',
                };
                pd['action_changeblockinstance_id_' + blockid + '_new_' + isnew + '_blocktype_' + option + '_title_' + title] = true;
                sendjsonrequest(config['wwwroot'] + 'view/blocks.json.php', pd, 'POST', function(data) {
                    // Update block on page to be of new type
                    var newdata = {};
                    newdata.blockid = data.data.blockid;
                    newdata.viewid = data.data.viewid;
                    newdata.data = {};
                    newdata.data.html = data.data.display.html;
                    newdata.data.javascript = data.data.display.javascript;
                    var blockinstance = ViewManager.replaceConfigureBlock(newdata);
                    if (data.data.configure) {
                        // The new block has configuration so update config modal to have new config form
                        if (data.data.isnew) {
                            addConfigureBlock(blockinstance, data.data.configure, true);
                        }
                        else {
                            // wire up the cancel button on chosen blocktype form to revert the block back to placeholder block
                            addConfigureBlock(blockinstance, data.data.configure);
                            var blockinstanceId = blockinstance.attr('data-id');
                            var cancelbutton = jQuery('#cancel_instconf_action_configureblockinstance_id_' + blockinstanceId);
                            cancelbutton.off('click');
                            cancelbutton.on('click',function(e) {
                                e.stopPropagation();
                                e.preventDefault();
                                var revpd = {
                                    'id': $('#viewid').val(),
                                    'change': 1,
                                    'blocktype': 'placeholder',
                                };
                                revpd['action_revertblockinstance_id_' + data.data.blockid + '_title_' + data.data.oldtitle] = true;
                                sendjsonrequest(config['wwwroot'] + 'view/blocks.json.php', revpd, 'POST', function(revdata) {
                                    console.log('success: ' + revdata.data.message);
                                    var revnewdata = {};
                                    revnewdata.blockid = revdata.data.blockid;
                                    revnewdata.viewid = revdata.data.viewid;
                                    revnewdata.data = {};
                                    revnewdata.data.html = revdata.data.display.html;
                                    revnewdata.data.javascript = revdata.data.display.javascript;
                                    var blockinstance = ViewManager.replaceConfigureBlock(revnewdata);
                                    var configbutton = jQuery('.view-container button[name="action_configureblockinstance_id_' + revdata.data.blockid + '"]');
                                    onModalCancel(e, configbutton);
                                },
                                function (revdata) {
                                    if (revdata.message) {
                                        console.log('error: ' + revdata.message);
                                    }
                                });
                            });
                        }
                    }
                    else {
                        // No configure form so we just need to close the modal
                        hideDock();
                    }
                },
                function (data) {
                    if (data.message && data.placement) {
                        $('#' + data.placement).find('.alert').remove();
                        $('#' + data.placement).prepend('<div class="alert alert-danger">' + data.message + '</div>');
                    }
                });
            });
        });
    }


    ViewManager.init = function() {

        // Set private variables
        contentEditor = $('[data-role="content-toolbar"]');
        workspace = $('[data-role="workspace"]');
        viewThemeSelect = $('#viewtheme-select');

        attachToolbarToggle();

        // Rewrite the configure buttons to be ajax
        rewriteConfigureButtons();

        // Rewrite the delete buttons to be ajax
        rewriteDeleteButtons();

        // Setup the 'add block' dialog
        setupPositionBlockDialog();

        makeNewBlocksDraggable();

        $(workspace).show();

        $(window).on('resize colresize', function() {
            equalHeights();
        });

        $(window).on('embednewvoki', function() {
            location.reload();
        });

        // images need time to load before height can be properly calculated
        window.setTimeout(function() {
            $(window).trigger('colresize');
            $(window).trigger('blocksloaded');
        }, 300);

    } // init

    //Private Methods
    /////////////////
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

    function attachToolbarToggle (){

        // collapse the toolbar if the cookie says its collapsed
        if(loadCookieContentEditorCollapsed()){
            $('[data-bs-target="col-collapse"]').addClass('col-collapsed');
        }

        // Attach expand/collapse to click and tap events
        $('[data-trigger="col-collapse"]').on('click tap', function(){
            var target = $(this).closest('[data-bs-target="col-collapse"]');

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

        $('.blocktype-drag.not-accessible').draggable({
            start: function(event, ui) {
                $(this).attr('gs-width', GRIDSTACK_CONSTANTS.desktopWidth);
                $(this).attr('gs-height', GRIDSTACK_CONSTANTS.defaultHeight);
            },
            helper: function(event) {
              var original = $(this),
                  helper = $("<div></div>").append(original.clone());
              helper.find('.labelspan').removeClass('hidden');
              helper.children().each(function(index) {
                  // Set helper cell sizes to match at least the original sizes
                  $(this).css('min-width', '200px');
              });
              return helper;
            },
            connectToSortable: '.grid-stack',
            appendTo: 'body',
        });


        $( ".gridedit" ).droppable({
            drop: function(event, ui) {
                var placeholder = $('.grid-stack').children().last(),
                x = placeholder.attr('gs-x'),
                y = placeholder.attr('gs-y'),
                grid = document.querySelector('.grid-stack').gridstack;

                grid.removeWidget(placeholder);
                $(placeholder).remove();

                $('.grid-stack .blocktype-drag').removeClass('btn btn-primary');
                addNewBlock({'positionx': x, 'positiony': y}, 'placeholder');
            }
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

    var addblockstarted = false; // To stop the double clicking of add block button causing multiple saving problem
    function startAddBlock(element) {
        var addblockdialog = jQuery('#addblock');
        addblockdialog.modal('show');
        if (!addblockstarted) {
            addblockstarted = true;
            addblockdialog.one('dialog.end', function(event, options) {
                if (options.saved) {
                    addNewBlock(options.position, 'placeholder');
                }
                else {
                    element.trigger("focus");
                }
            });

            addblockdialog.find('.modal-title').text(get_string('addnewblock', 'view', element.text()));
            addblockdialog.find('.block-inner').removeClass('d-none');

            addblockdialog.find('.deletebutton').trigger("focus");
            keytabbinginadialog(addblockdialog, addblockdialog.find('.deletebutton'), addblockdialog.find('.cancel'));
        }
    }

    function addNewBlock(whereTo, blocktype) {
        addblockstarted = false;
        var pd = {
                'id': $('#viewid').val(),
                'change': 1,
                'blocktype': blocktype,
                'positionx': 0,
                'positiony': 0,
            };

        if (config.blockeditormaxwidth) {
            pd['cfheight'] = $(window).height() - 100;
        }
        var grid = document.querySelector('.grid-stack').gridstack;
        if (whereTo == 'bottom') {
            // To place it at the base we need to find the last item position and
            // make our one lower than that by adding the dimension height value
            if (grid.el.childNodes.length > 1) {
                pd['positiony'] = grid.el.childNodes[grid.el.childNodes.length - 1].getAttribute('gs-y') + 3;
            }
            else {
                pd['positiony'] = 0;
            }
        }
        else {
            if (typeof(whereTo['positionx']) !== 'undefined') {
                pd['positionx'] = whereTo['positionx'];
            }
            if (typeof(whereTo['positiony']) !== 'undefined') {
                pd['positiony'] = whereTo['positiony'];
            }
        }

        let width = GRIDSTACK_CONSTANTS.desktopWidth; // Default gridstack block width for desktop
        if (grid._widthOrContainer() <= grid.opts.minWidth) {
            width = 1; // Default gridstack block width for mobile
            pd['gridonecolumn'] = true;
        }
        pd['action_addblocktype_positionx_' + pd['positionx'] + '_positiony_' + pd['positiony'] + '_width_' + width + '_height_' + '3'] = true;
        sendjsonrequest(config['wwwroot'] + 'view/blocks.json.php', pd, 'POST', function(data) {
            var div = $('<div>').html(data.data.display.html),
                blockinstance = div.find('div.grid-stack-item'),
                configureButton = blockinstance.find('.configurebutton'),
                blockId = blockinstance.attr('id').substr(6),
                dimensions = {
                    positionx: blockinstance[0].getAttribute('gs-x'),
                    positiony: blockinstance[0].getAttribute('gs-y'),
                }
            addBlockCss(data.css);

            var grid = GridStack.init();
            var minWidth = grid.opts.minCellColumns;
            dimensions.width = GRIDSTACK_CONSTANTS.desktopWidth;
            dimensions.height = GRIDSTACK_CONSTANTS.defaultHeight;
            addNewWidget(blockinstance[0], blockId, dimensions, grid, 'placeholder', minWidth, dimensions.height);

            if (data.data.configure) {
                showDock($('#configureblock'), true);
                addConfigureBlock(blockinstance, data.data.configure, true);
            } else {
                // if block has has_instance_config() set to false, eg 'comment' block
                rewriteDeleteButton(blockinstance.find('.deletebutton'));
                blockinstance.find('.deletebutton').trigger("focus");
            }
            if (typeof(window.dragonDrop) != 'undefined') {
                var list = $('.grid-stack')[0];
                if (whereTo == 'top') {
                    // new block will show on top of the page but it's still as the last child in the DOM
                    // need to place it first of the list before dragon drop reset
                    var children = list.children;
                    var length = children.length;
                    list.insertBefore(children[length-1], children[0]);
                }
            }
            else {
                if (typeof whereTo === 'string') {
                    $('html, body').animate({ scrollTop: $(blockinstance).offset().top }, 'slow');
                }
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
        button.off('click touchstart');
        button.on('click touchstart', function(e) {
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
        if (button.hasClass('gallery')) {
            return;
        }
        button.off('click touchstart');
        button.on('click touchstart', function(e) {
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

                    var gridstackobj = document.querySelector('.grid-stack').gridstack;
                    var blocktoremove = document.getElementById('block_' + blockinstanceId);
                    gridstackobj.removeWidget(blocktoremove);

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
                    if (typeof(window.dragonDrop) != 'undefined') {
                        var list = $('.grid-stack')[0];
                        window.dragonDrop.initElements(list);
                    }


                }, function() {

                    self.prop('disabled', false);
                });
            }
            else {
                self.prop('disabled', false);
            }
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

                var gridstackobj = document.querySelector('.grid-stack').gridstack;
                var blocktoremove = document.getElementById('block_' + blockinstanceId);
                gridstackobj.removeWidget(blocktoremove);

                if (!$('#configureblock').hasClass('d-none')) {
                    hideDock();
                    showMediaPlayers();
                    button.trigger("focus");
                }
            });

        });
    }

    /**
     * return true if the mousedown is <LEFT BUTTON> or the keydown is <Space> or <Enter>
     */
    function isHit(e) {
        return (e.which === 1 || e.button === 1 || e.keyCode === maharaui.keyCode.SPACE || e.keyCode === maharaui.keyCode.ENTER);
    }
    /*
     * Initialises the dialog used to add and move blocks
     */
    function setupPositionBlockDialog() {

        $('#newblock .cancel, #addblock .deletebutton').on('mousedown keydown', function(e) {
            if (isHit(e) || e.keyCode === maharaui.keyCode.ESCAPE) {
                closePositionBlockDialog(e, {'saved': false});
            }
        });

        $('#newblock .submit').on('click keydown', function(e) {
            if (isHit(e)) {
                var position = $('#newblock_position').prop('selectedIndex');

                closePositionBlockDialog(e, {
                    'saved': true,
                    'position': (position == 0 ? 'top' : 'bottom'),
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

        var addblockdialog = jQuery('#addblock');

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
            loading = $('<span>').attr('class', 'icon icon-spinner icon-pulse block-loading'),
            pd = {'id': $('#viewid').val(), 'change': 1};


        showDock($('#configureblock'), true);

        // delay processing so animation can complete smoothly
        // this may not be necessary once json requests are done with jquery
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
        }
        else {
            deletebutton.on('click', function(e) {
                if ((formchangemanager.checkDirtyChanges() && confirm(get_string('confirmcloseblockinstance'))) || !formchangemanager.checkDirtyChanges()) {
                    e.stopPropagation();
                    e.preventDefault();

                    hideDock();
                    showMediaPlayers();

                    setTimeout(function() {
                        oldblock.find('.configurebutton').trigger("focus");
                    }, 1);
                }
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

function editViewInit() {
    return ViewManager.init();
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

function wire_blockoptions() {
    return ViewManager.blockOptions();
}
