/**
 * Javascript for the views interface
 * @source: http://gitorious.org/mahara/mahara
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2013 Mike Kelly UAL m.f.kelly@arts.ac.uk
 *
 */

// self executing function for namespacing code
(function( ViewManager, $, undefined ) {

    //Private Properties
    ////////////////////
    var cookieName = 'contenteditorcollapsed';
    var collapsed = false;
    //set these in init()
    var contentEditor = null;
    var bottomPane = null;
    var viewThemeSelect = null;
    var viewsLoading = null;
    var navBuffer = 660;

    // Public Properties
    // Whether the browser is IE - needed for some hacks
    ViewManager.isOldIE = $.browser.msie && $.browser.version < 9.0;
    ViewManager.contentEditorWidth = 145;
    ViewManager.isMobile = config['handheld_device'] || (navigator.userAgent.match(/iPhone/i))
                           || (navigator.userAgent.match(/iPod/i))
                           || (navigator.userAgent.match(/iPad/i))
                           || (navigator.platform.toLowerCase().indexOf("win") !== -1 && navigator.userAgent.toLowerCase().indexOf("touch") !== -1)
                           || (navigator.platform.toLowerCase().indexOf("win") !== -1 && navigator.userAgent.indexOf("ARM") !== -1);
    // Whether the brower is iPhone, IPad, IPod, Windows 8 Tablet or using Windows RT
    if (ViewManager.isMobile) {
        ViewManager.contentEditorWidth = 175;
    }
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

            $('.blockinstance-header', newblock).mousedown(function() {
                    $('.row .column-content').each(function() {
                        $(this).addClass('block-drop-on', 100);
                    });
                });

            $('.blockinstance-header', newblock).mouseup(function() {
                    $('.row .column-content').each(function() {
                        $(this).removeClass('block-drop-on', 500);
                    });
                });

            swapNodes(oldblock.get()[0], newblock.get()[0]); // using DOM objects, not jQuery objects so we needn't worry about IDs
            eval(data.data.javascript);
            rewriteConfigureButton(newblock.find('input.configurebutton'));
            rewriteDeleteButton(newblock.find('input.deletebutton'));
        }
        removeConfigureBlocks();
        showMediaPlayers();
        setTimeout(function() {
            newblock.find('input.configurebutton').focus();
        }, 1);
    };

    //Private Methods
    /////////////////
    function init() {

        contentEditor = $('#content-editor');
        bottomPane = $('#bottom-pane');
        viewThemeSelect = $('#viewtheme-select');
        viewsLoading = $('#views-loading');

        if (ViewManager.isMobile) {
            // Unhide the radio button if the browser is iPhone, IPad or IPod
            $('#editcontent-sidebar').addClass('withradio');
            $('#page').addClass('withradio');
            $('#content-editor input.blocktype-radio').each(function() {
                $(this).show();
            });
            $('#accordion a.nonjs').each(function() {
                $(this).hide();
            });
            $('#accordion div.withjs').each(function() {
                $(this).show();
            });
            $('#accordion *').css('zoom', '1');
            $('#main-column-container .tabswrap ul li a').css('float', 'left'); // fix li elements not floating left by floating anchors
        }
        else {
            // Hide 'new block here' buttons
            $('#bottom-pane div.add-button').each(function() {
                $(this).remove();
            });

            // Hide controls in each block instance that are not needed
            $('#bottom-pane input.movebutton').each(function() {
                $(this).remove();
            });

            // Hide radio buttons for moving block types into place
            $('#content-editor input.blocktype-radio').each(function() {
                $(this).hide();
            });

            // Remove the a href links that are needed for when js is turned off
            $('#accordion a.nonjs').each(function() {
                $(this).hide();
            });

            // Display the divs that are needed when js is turned on
            $('#accordion div.withjs').each(function() {
                $(this).show();
            });
        }

        $('#accordion').accordion({
            icons: false,
            heightStyle: 'content',
            collapsible: true,
            active: false,
            activate: function(event, ui) {
                var active = $(this).find('.ui-state-active');
                if (active.length) {
                    var category = active.next('div');
                    var categoryid = category.attr('id');
                    var pd = {
                            'id': $('#viewid').val(),
                            'change': 0,
                            'action': 'blocktype_list',
                            'c': categoryid
                        };

                    sendjsonrequest(config['wwwroot'] + 'view/blocks.json.php', pd, 'POST', function(data) {
                        $(category).html(data.data);
                        makeNewBlocksDraggable();
                        showColumnBackgroundsOnSort();
                        // Unhide the radio button if the browser is iPhone, IPad or IPod
                        if (ViewManager.isMobile) {
                            // Unhide the radio button if the browser is iPhone, IPad or IPod
                            $('#editcontent-sidebar').addClass('withradio');
                            $('#page').addClass('withradio');
                            $('#content-editor input.blocktype-radio').each(function() {
                                $(this).show();
                            });
                            $('#accordion a.nonjs').each(function() {
                                $(this).hide();
                            });
                            $('#accordion div.withjs').each(function() {
                                $(this).show();
                            });
                            $('#accordion *').css('zoom', '1');
                            $('#main-column-container .tabswrap ul li a').css('float', 'left'); // fix li elements not floating left by floating anchors
                        }
                        checkEditAreaHeight();
                    });
                    return false;
                }
            }
        });

        setContentEditorPosition();

        $('#content-editor-header').click(function() {
            var windowWidth = windowWide();
            if (windowWidth) {
                toggleContentEditorPosition(true);
            }
            else {
                toggleContentEditorFold();
            }
        });

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

        // Set equal column heights
        setTimeout(function() {
            //safari needs delay to load images
            setEqualColumnHeights('#column-container > .row', 40);
        }, 150);

        showColumnBackgroundsOnSort();

        rewriteViewThemeSelector();

        makeNewBlocksDraggable();
        makeExistingBlocksSortable();

        $(viewsLoading).remove();

        $(bottomPane).show();
    } // init

    function checkEditAreaHeight() {
        // to make sure the 'floating' panel when opened is not longer than
        // the 'containing' div
        var editwrapper = $('#editcontent-sidebar-wrapper');
        var editwrapperheight = (parseInt(editwrapper.css('height'), 10) + parseInt(editwrapper.css('padding-top'), 10) + parseInt(editwrapper.css('padding-bottom'), 10));
        if ($('#main-column').height() < editwrapperheight) {
            var windowWidth = windowWide();
            if (windowWidth) {
                $('#main-column').animate({
                    height: editwrapperheight + 'px'
                }, 200, function () {});
            }
            else {
                $('#main-column').css('height',editwrapperheight + 'px');
            }
        }
    }

    function windowWide() {
        var windowWidth;
        if (ViewManager.isOldIE) {
            windowWidth = ($j(window).width() >= navBuffer);
        }
        else {
            windowWidth = Modernizr.mq('(min-width: 660px)');
        }
        return windowWidth;
    }

    function setContentEditorPosition() {
        // don't reposition content editor if mobile view
        var windowWidth = windowWide();
        if (!windowWidth) {
            $('#editcontent-sidebar').css('left', '0px');
            $('#main-column.editcontent').css('padding-left', '0px');
            $('#footer-wrap.editcontent #footernav').css('padding-left', '0px');
            return;
        }
        else {
            var offset = $('#mainmiddle').offset();
        }
        $('#content-editor-foldable').show();
        var isCollapsed = loadCookieContentEditorCollapsed();
        if (isCollapsed != collapsed) {
            toggleContentEditorPosition(false);
        }
        if (isCollapsed == false) {
            $('#editcontent-sidebar').addClass('open');
            $('#page').addClass('open');
            $('#main-column.editcontent').css('padding-left', ViewManager.contentEditorWidth+5 + 'px');
            $('#footer-wrap.editcontent #footernav').css('padding-left', ViewManager.contentEditorWidth+5 + 'px');
        }
    }

    function loadCookieContentEditorCollapsed() {
        if (document.cookie) {
             var index = document.cookie.indexOf(cookieName);
             if (index != -1) {
                 var valbegin = (document.cookie.indexOf("=", index) + 1);
                 var valend = document.cookie.indexOf(";", index);
                 if (valend == -1) {
                     valend = document.cookie.length;
                 }
                 isCollapsed = document.cookie.substring(valbegin, valend);
                 if (isCollapsed == 1) {
                     return true;
                 }
                 else {
                     return false;
                 }
             }
        }
        return false;
    }

    function writeCookieContentEditorCollapsed(isCollapsed) {
        if (!config['cc_enabled'] || (config['cc_enabled'] && document.cookie.indexOf("cc_necessary") >= 0)) {
            document.cookie=cookieName+"="+ (isCollapsed ? '1': '0') +"; expires=Wednesday, 01-Aug-2040 08:00:00 GMT";
        }
    }

    function toggleContentEditorFold() {
        if (collapsed) {
            $('#editcontent-sidebar').removeClass('collapsed');
            $('#page').removeClass('collapsed');
            writeCookieContentEditorCollapsed(false);
            collapsed = false;
        }
        else {
            $('#editcontent-sidebar').addClass('collapsed');
            $('#page').addClass('collapsed');
            writeCookieContentEditorCollapsed(true);
            collapsed = true;
        }
        $('#content-editor-foldable').toggle();
    }

    function toggleContentEditorPosition(animate) {
        $('.pointer').each(function() {
            $(this).remove();
        });

        var windowWidth = windowWide();
        var windowIsWide;

        if (collapsed) {
            $('#editcontent-sidebar').addClass('open');
            $('#editcontent-sidebar').removeClass('collapsed');
            $('#page').addClass('open');
            $('#page').removeClass('collapsed');
            if (animate) {
                $('#editcontent-sidebar').animate({
                    left: '0px'
                  }, 200, function () {
                      collapsed = false;
                  });
                if (!windowIsWide) {
                    $('#main-column.editcontent').animate({
                        paddingLeft: ViewManager.contentEditorWidth+5 + 'px'
                    }, 200);
                    $('#footer-wrap.editcontent #footernav').animate({
                        paddingLeft: ViewManager.contentEditorWidth+5 + 'px'
                    }, 200);
                }
            }
            else {
                $('#editcontent-sidebar').css('left', '0px');
                if (!windowIsWide) {
                    $('#main-column.editcontent').css('padding-left', ViewManager.contentEditorWidth+5 + 'px');
                    $('#footer-wrap.editcontent #footernav').css('padding-left', ViewManager.contentEditorWidth+5 + 'px');
                }
                collapsed = false;
            }
            writeCookieContentEditorCollapsed(false);
            return false;

        }
        else {
            $('#editcontent-sidebar').removeClass('open');
            $('#editcontent-sidebar').addClass('collapsed');
            $('#page').removeClass('open');
            $('#page').addClass('collapsed');
            if (animate) {
                $('#editcontent-sidebar').animate({
                  }, 200, function () {
                      collapsed = true;
                  });
                $('#main-column.editcontent').animate({
                    paddingLeft: '30px'
                }, 200);
                $('#footer-wrap.editcontent #footernav').animate({
                    paddingLeft: '33px'
                }, 200);
            }
            else {
                $('#main-column.editcontent').css('padding-left', '30px');
                $('#footer-wrap.editcontent #footernav').css('padding-left', '33px');
                collapsed = true;
            }
            writeCookieContentEditorCollapsed(true);
            return false;
        }
    }

    function makeNewBlocksDraggable() {
        $('.blocktype-list div.blocktype').each(function() {
            $(this).draggable({
                start: function(event, ui) {
                    showColumnBackgrounds();
                },
                helper: function(event) {
                    var original = $(this);
                    var helper = $("<div />").append(original.clone());
                    helper.children().each(function(index) {
                      // Set helper cell sizes to match the original sizes
                      $(this).width(original.eq(index).width());
                    });
                    return helper;
                  },
                connectToSortable: '.row .column .column-content',
                stop: function(event, ui) {
                    // see also showColumnBackgroundsOnSort for clicking in place without dragging
                    hideColumnBackgrounds();
                },
                appendTo: 'body'
            });

            $(this).find('.blocktypelink').off('mouseup keydown'); // remove old event handlers
            $(this).find('.blocktypelink').on('mouseup keydown', function(e) {
                // Add a block when click left button or press 'Space bar' or 'Enter' key
                if (isHit(e) && $('#addblock').is(':hidden')) {
                    startAddBlock($(this));
                }
            });
        });
    }

    /**
     * Make sure the previous/next key tabbing will move within the dialog
     */
    function keytabbinginadialog(dialog, firstelement, lastelement) {
        firstelement.keydown(function(e) {
            if (e.keyCode === $j.ui.keyCode.TAB && e.shiftKey) {
                lastelement.focus();
                e.preventDefault();
            }
        });
        lastelement.keydown(function(e) {
            if (e.keyCode === $j.ui.keyCode.TAB && !e.shiftKey) {
                firstelement.focus();
                e.preventDefault();
            }
        });
    }
    function startAddBlock(element) {
        var addblockdialog = $('#addblock').removeClass('hidden');
        addblockdialog.one('dialog.end', function(event, options) {
            if (options.saved) {
                addNewBlock(options, element.parent().find('.blocktype-radio').val());
            }
            else {
                element.focus();
            }
        });
        addblockdialog.find('h2.title').text(get_string('addblock', element.text()));
        computeColumnInputs(addblockdialog);
        setDialogPosition(addblockdialog);

        $('body').append($('<div>').attr('id', 'overlay'));

        addblockdialog.find('.deletebutton').focus();

        keytabbinginadialog(addblockdialog, addblockdialog.find('.deletebutton'), addblockdialog.find('.cancel'));
    }

    function makeExistingBlocksSortable() {
        // Make existing and new blocks sortable
        $('.column .column-content').sortable({
            handle: 'div.blockinstance-header',
            items: 'div.blockinstance',
            cursorAt: {left: 5},
            connectWith: '.row .column .column-content',
            placeholder: 'block-placeholder',
            beforeStop: function(event, ui) {

                var whereTo = getBlockinstanceCoordinates(ui.helper);

                if (ui.helper.find('.blocktype-radio').length) {
                    addNewBlock(whereTo, ui.helper.find('input.blocktype-radio').val());
                    $('.block-placeholder').siblings('.blocktype').remove();
                }
                else {
                    //move existing block
                    var uihId = ui.helper.attr('id');
                    var blockinstanceId = uihId.substr(uihId.lastIndexOf('_') + 1);
                    moveBlock(whereTo, blockinstanceId);
                }
            },

            update: function(event, ui) {
                $('.row .column-content').each(function() {
                    $(this).css('min-height', '');
                });
                setEqualColumnHeights('#column-container > .row', 40);
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
                ui.helper.width(200);
                ui.helper.height(80);
            }
        });
    } // end of makeNewBlocksSortable()

    function cellChanged() {
        $(this).closest('.cellchooser').find('.active').removeClass('active');
        $(this).parent().addClass('active');
        var position = $(this).val().split('-');
        var element = $('#column-container > .row').eq(parseInt(position[0], 10) - 1).find('.column').eq(parseInt(position[1], 10) - 1);
        var options = [get_string('blockordertop')];
        element.find('.column-content .blockinstance .blockinstance-header').each(function() {
            options.push(get_string('blockorderafter', $(this).find('h2.title').html()));
        });
        var selectbox = $('#addblock_position');
        selectbox.html('<option>' + options.join('</option><option>') + '</option>');
    }

    function addNewBlock(whereTo, blocktype) {
        var pd = {
                'id': $('#viewid').val(),
                'change': 1,
                'blocktype': blocktype
            };

        if (config.blockeditormaxwidth) {
            pd['cfheight'] = getViewportDimensions().h - 100;
        }
        pd['action_addblocktype_row_' + whereTo['row'] + '_column_' + whereTo['column'] + '_order_' + whereTo['order']] = true;

        sendjsonrequest(config['wwwroot'] + 'view/blocks.json.php', pd, 'POST', function(data) {
            var div = $('<div>').html(data.data.display.html);
            var blockinstance = div.find('div.blockinstance');
            addBlockCss(data.css);
            // Make configure button clickable, but disabled as blocks are rendered in configure mode by default
            var configureButton = blockinstance.find('input.configurebutton');
            if (configureButton) {
                rewriteConfigureButton(configureButton);
                $('#action-dummy').attr('name', 'action_addblocktype_row_' + whereTo['row'] + '_column_' + whereTo['column'] + '_order_' + whereTo['order']);
            }
            rewriteDeleteButton(blockinstance.find('input.deletebutton'));
            insertBlockStub(blockinstance, whereTo);
            if (data.data.configure) {
                addConfigureBlock(blockinstance, data.data.configure, true);
            }
            else {
                blockinstance.find('.deletebutton').focus();
            }
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
        $('.row .column-content').each(function() {
            $(this).addClass('block-drop-on', 100);
        });
    }

    function hideColumnBackgrounds() {
        $('.row .column-content').each(function() {
            $(this).removeClass('block-drop-on', 500);
        });
    }

    function showColumnBackgroundsOnSort() {
        $('.blockinstance .blockinstance-header, .blocktype-list div.blocktype').each(function() {
            $(this).mousedown(function() {
                showColumnBackgrounds();
            });

            $(this).mouseup(function() {
                hideColumnBackgrounds();
            });
        });
    }

    /*
    * Set empty column container divs to be same height as
    * tallest column in that row.
    * Pass in rows
    */
    function setEqualColumnHeights(rows, minheight) {
        $(rows).each(function() {
            if (minheight != undefined) {
                var currentTallest = minheight;
            }
            else {
                var currentTallest = 0;
            }
            $(this).find('.column-content').each(function(i) {
                if ($(this).height() > currentTallest) {
                    currentTallest = $(this).height();
                }
            });
            $(this).find('.column-content').css({'min-height': currentTallest});
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
        $('#bottom-pane input.configurebutton').each(function() {
            rewriteConfigureButton($(this));
        });
    }

    /**
     * Rewrites one configure button to be AJAX
     */
    function rewriteConfigureButton(button) {
        button.click(function(event) {
            event.stopPropagation();
            event.preventDefault();
            getConfigureForm(button.closest('div.blockinstance'));
        });
    }

    /**
     * Rewrites the blockinstance delete buttons to be AJAX
     */
    // Why does this exist?
    this.rewriteCategorySelectList = function() {
        console.log('rewriting category select');
        forEach(getElementsByTagAndClassName('a', null, 'category-list'), function(i) {
            connect(i, 'onclick', function(e) {
                var queryString = parseQueryString(i.href.substr(i.href.indexOf('?')));
                removeElementClass(getFirstElementByTagAndClassName('li', 'current', 'category-list'), 'current');
                addElementClass(i.parentNode, 'current');
                sendjsonrequest(config['wwwroot'] + 'view/blocks.json.php', {'id': $('viewid').value, 'action': 'blocktype_list', 'c': queryString['c']}, 'POST', function(data) {
                    setNodeAttribute('category', 'value', queryString['c']);
                    $('blocktype-list').innerHTML = data.data;
                    console.log(self);
                    self.makeBlockTypesDraggable();
                    self.showBlockTypeDescription();
                });
                e.stop();
            });
        });
    }

    function rewriteDeleteButtons() {
        $('#bottom-pane input.deletebutton').each(function() {
           rewriteDeleteButton($(this));
        });
    }

    /**
     * Rewrites one delete button to be AJAX
     */
    function rewriteDeleteButton(button) {
        button.click(function(event) {
            button.attr('disabled', 'disabled');
            if (confirm(get_string('confirmdeleteblockinstance'))) {
                var pd = {'id': $('#viewid').val(), 'change': 1};
                pd[button.attr('name')] = 1;
                sendjsonrequest(config['wwwroot'] + 'view/blocks.json.php', pd, 'POST', function(data) {
                    var blockinstanceId = button.attr('name').substr(button.attr('name').lastIndexOf('_') + 1);
                    $('#blockinstance_' + blockinstanceId).remove();
                    if (!$('#configureblock').hasClass('hidden')) {
                        removeConfigureBlocks();
                        showMediaPlayers();
                        button.focus();
                    }
                    //reset column heights
                    $('.column-content').each(function() {
                        $(this).css('min-height', '');
                    });
                    setEqualColumnHeights($('#column-container > .row'), 50);
                    button.removeAttr('disabled');
                }, function() {
                    button.removeAttr('disabled');
                });
            }
            else {
                button.removeAttr('disabled');
            }
            event.stopPropagation();
            event.preventDefault();
        });
    }

    /*
     * Shows all keyboard-accessible ajax move buttons
     */
    function rewriteMoveButtons() {
        $('#bottom-pane input.keyboardmovebutton').each(function() {
            rewriteMoveButton($(this));
        });
    }

    /*
     * Shows and sets up one keyboard-accessible ajax move button
     */
    function rewriteMoveButton(button) {
        button.removeClass('hidden');

        button.click(function(event) {
            event.stopPropagation();
            event.preventDefault();

            var addblockdialog = $('#addblock').removeClass('hidden');

            computeColumnInputs(addblockdialog);
            var prevcell = button.closest('.column-content');
            var order = prevcell.children().index(button.closest('.blockinstance'));
            var row = $('#column-container > .row').index(button.closest('#column-container > .row'));
            var column = button.closest('#column-container > .row').children().index(button.closest('.column'));
            var radio = addblockdialog.find('.cellchooser').children().eq(row).find('input').eq(column);
            var changefunction = function() {
                if (radio.prop('checked')) {
                    $('#addblock_position option').eq(order + 1).remove();
                }
            };
            radio.change(changefunction);
            radio.prop('checked', true).change();
            $('#addblock_position').prop('selectedIndex', order);

            addblockdialog.one('dialog.end', function(event, options) {
                if (options.saved) {
                    var blockinstanceId = button.attr('name').match(/[0-9]+$/)[0];
                    moveBlock(options, blockinstanceId);
                    var newcell = $('#column-container > .row').eq(options['row'] - 1)
                        .find('.column-content').eq(options['column'] - 1);
                    var currentblock = button.closest('.blockinstance');
                    var lastindex = newcell.children().length;
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
                button.focus();
            });
            addblockdialog.find('h2.title').text(get_string('moveblock'));

            setDialogPosition(addblockdialog);

            $('body').append($('<div>').attr('id', 'overlay'));

            addblockdialog.find('.deletebutton').focus();
            keytabbinginadialog(addblockdialog, addblockdialog.find('.deletebutton'), addblockdialog.find('.cancel'));
        });
    }

    function computeColumnInputs(dialog) {
        var inputcontainer = dialog.find('#addblock_cellchooser_container td');
        var result = $('<div>').addClass('cellchooser');
        $('#column-container > .row').each(function(i) {
            var row = $('<div>');
            $(this).find('.column').each(function(j) {
                var value = (i + 1) + '-' + (j + 1);
                var radio = $('<input>').addClass('accessible-hidden').attr({
                    'type': 'radio',
                    'style': $(this).attr('style'),
                    'id': 'cellchooser_' + value,
                    'name': 'cellchooser',
                    'value': value
                });
                radio.change(cellChanged);
                radio.focus(function() {
                    $(this).parent().addClass('focused');
                });
                radio.blur(function() {
                    $(this).parent().removeClass('focused');
                });
                var label = $('<label>').addClass('cell').attr('for', 'cellchooser_' + value);
                label.append(radio)
                    .append($('<span>').addClass('accessible-hidden').html(get_string('cellposition', i + 1, j + 1)));
                row.append(label);
            });
            result.append(row);
        });
        inputcontainer.html('').append(result);
        var firstcell = inputcontainer.find('input').first();
        firstcell.prop('checked', true);
        cellChanged.call(firstcell);
    }

    function moveBlock(whereTo, instanceId) {
        var pd = {
            'id': $('#viewid').val(),
            'change': 1
        };
        if (config.blockeditormaxwidth) {
            pd['cfheight'] = getViewportDimensions().h - 100;
        }
        pd['action_moveblockinstance_id_' + instanceId + '_row_' + whereTo['row'] + '_column_' + whereTo['column'] + '_order_' + whereTo['order']] = true;
        sendjsonrequest(config['wwwroot'] + 'view/blocks.json.php', pd, 'POST', function(data) {
            hideColumnBackgrounds();
        });
    }

    /**
     * Rewrites cancel button to remove a block
     */
    function rewriteCancelButton(button, blockinstanceId) {
        button.click(function(event) {
            var pd = {'id': $('#viewid').val(), 'change': 1};
            pd[button.attr('name')] = 1;
            sendjsonrequest(config['wwwroot'] + 'view/blocks.json.php', pd, 'POST', function(data) {
                $('#blockinstance_' + blockinstanceId).remove();
                if (!$('#configureblock').hasClass('hidden')) {
                    removeConfigureBlocks();
                    showMediaPlayers();
                    button.focus();
                }
            });
            event.stopPropagation();
            event.preventDefault();
        });
    }

    /**
     * Rewrites the add column buttons to be AJAX
     *
     * If the first parameter is a string/element, only the buttons below that
     * element will be rewritten
     */
    function rewriteAddColumnButtons() {
        var parentNode;
        if (typeof(arguments[0]) != 'undefined') {
            parentNode = arguments[0];
            // Make the top pane a dropzone for cancelling adding block types
            if (self.topPane) {
                var count = 0;
                new Droppable('top-pane', {
                    'onhover': function() {
                        if (count++ == 5) {
                            count = 0;
                            // Hide the dropzone
                            hideElement(self.blockPlaceholder);
                        }
                    }
                });
            }
        }
        // Unhide the radio button if the browser is iPhone, IPad or IPod
        else if (ViewManager.isMobile && self.topPane) {
            forEach(getElementsByTagAndClassName('input', 'blocktype-radio', 'top-pane'), function(i) {
                    setNodeAttribute(i, 'style', 'display:inline');
                });
            // Hide radio buttons for moving block types into place
            $('#top-pane input.blocktype-radio').each(function() {
                $(this).hide();
            });
        }
        else {
            parentNode = bottomPane;
        }

        $('input.addcolumn', parentNode).each(function() {
            $(this).click(function(event) {
                // Work around for a konqueror bug - konqueror passes onclick
                // events to disabled buttons
                if (!$(this).disabled) {
                    $(this).attr('disabled', 'disabled');
                    var name = event.target.name;
                    var match = name.match(/action_addcolumn_row_(\d+)_before_(\d+)/);
                    var rowid = parseInt(match[1], 10);
                    var colid = parseInt(match[2], 10);
                    var pd   = {'id': $('#viewid').val(), 'change': 1}
                    pd['action_addcolumn_row_' + rowid + '_before_' + colid] = 1;
                    sendjsonrequest(config['wwwroot'] + 'view/blocks.json.php', pd, 'POST', function(data) {
                        addColumn(rowid, colid, data);
                        checkColumnButtonDisabledState();
                    }, function() {
                        checkColumnButtonDisabledState();
                    });
                }
                event.stopPropagation();
                event.preventDefault();
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
        var parentNode;
        if (typeof(arguments[0]) != 'undefined') {
            parentNode = arguments[0];
        }
        else {
            parentNode = bottomPane;
        }

        $('input.removecolumn', parentNode).each(function() {
            $(this).click(function(event) {
                // Work around for a konqueror bug - konqueror passes onclick
                // events to disabled buttons
                if (!this.disabled) {
                    $(this).attr('disabled', 'disabled');
                    var name = event.target.name;
                    var match = name.match(/action_removecolumn_row_(\d+)_column_(\d+)/);
                    var rowid = parseInt(match[1], 10);
                    var colid = parseInt(match[2], 10);
                    var pd   = {'id': $('#viewid').val(), 'change': 1}
                    pd['action_removecolumn_row_' + rowid + '_column_' + colid] = 1;
                    sendjsonrequest(config['wwwroot'] + 'view/blocks.json.php', pd, 'POST', function(data) {
                        removeColumn(rowid, colid);
                        checkColumnButtonDisabledState();
                    }, function() {
                        checkColumnButtonDisabledState();
                    });
                }
                event.stopPropagation();
                event.preventDefault();
            });
        });
    }

    /**
     * Disables the 'add column' buttons
     */
    function checkColumnButtonDisabledState() {
        // For each row
        $('#column-container > .row').each(function() {

            // Get the existing number of columns
            var match = $('div.column:first', $(this)).attr('class').match(/columns([0-9]+)/)[1];
            var numColumns = parseInt(match, 10);

            var state = (numColumns == 5);
            $('input.addcolumn', $(this)).each(function() {
                if (state) {
                    $(this).attr('disabled', 'disabled');
                }
                else {
                     $(this).removeAttr('disabled');
                }
            });

            var state = (numColumns == 1);
            $('input.removecolumn', $(this)).each(function() {
                if (state) {
                    $(this).attr('disabled', 'disabled');
                }
                else {
                     $(this).removeAttr('disabled');
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
        $('body').append($('#addblock'));
        $('#addblock').css('width', 500);

        $('#addblock .cancel, #addblock .deletebutton').on('mousedown keydown', function(e) {
            if (isHit(e)) {
                closePositionBlockDialog(e, {'saved': false});
            }
        });

        $('#addblock .submit').on('mousedown keydown', function(e) {
            if (isHit(e)) {
                var position = $('#addblock .cellchooser input:checked').val().split('-');
                var order = $('#addblock_position').prop('selectedIndex') + 1;
                closePositionBlockDialog(e, {
                    'saved': true,
                    'row': position[0], 'column': position[1], 'order': order
                });
            }
        });
        // To allow for pushing enter button when on selecting the 'cell' column line
        $('#addblock').on('keydown', function(e) {
            if (e.keyCode == 13) {
                var position = $('#addblock .cellchooser input:checked').val().split('-');
                var order = $('#addblock_position').prop('selectedIndex') + 1;
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
        addblockdialog.addClass('hidden').trigger('dialog.end', options);
        $('#overlay').remove();
    }

    /**
     * Adds a column to the view
     */
    function addColumn(rowid, colid, data) {
        // Get the existing number of columns
        var match = $('#row_' + rowid + ' div.column:first').attr('class').match(/columns([0-9]+)/)[1];
        var numColumns = parseInt(match, 10);

        // Here we are doing two things:
        // 1) The existing columns that are higher than the one being inserted need to be renumbered
        // 2) All columns need their 'columnsN' class renumbered one higher
        // 3) All columns need their 'width' style attribute removed, if they have one
        for (var oldID = numColumns; oldID >= 1; oldID--) {
            var column = $('#row_' + rowid + '_column_' + oldID);
            var newID = oldID + 1;
            if (oldID >= colid) {
                $('#row_' + rowid + '_column_' + oldID).attr('id', 'row_' + rowid + '_column_' + newID);

                // Renumber the add/remove column buttons
                $('input.addcolumn', $('#row_' + rowid + '_column_' + newID)).attr('name', 'action_addcolumn_row_' + rowid + '_before_' + (newID + 1));
                $('input.removecolumn', $('#row_' + rowid + '_column_' + newID)).attr('name', 'action_removecolumn_row_' + rowid + '_column_' + newID);
            }
            $(column).removeClass('columns' + numColumns);
            $(column).addClass('columns' + (numColumns + 1));
            $(column).removeAttr('style');
        }

        // If the column being added is the very first one, the 'left' add column button needs to be removed
        if (colid == 1) {
            $('#row_' + rowid + '_column_2 div.add-column-left').remove();
        }

        // If we're adding a column to the very right, move the add button between the columns
        if (colid > numColumns) {
            var rightColumnDiv = $('#row_' + rowid + '_column_' + numColumns + ' div.add-column-right');
            $(rightColumnDiv).removeClass('add-column-right');
            $(rightColumnDiv).addClass('add-column-center');
        }

        // Now we insert the new column into the DOM. Inserting the HTML into a
        // new element and then into the DOM means we can add the new column
        // without changing any of the existing DOM tree (and thus destroying
        // events)
        var tempDiv = $('<div>');
        tempDiv.html(data.data);
        if (colid == 1) {
            $(':first', tempDiv).insertBefore('#row_' + rowid + '_column_2');
        }
        else {
            $(':first', tempDiv).insertAfter('#row_' + rowid + '_column_' + (colid - 1));
        }

        if (numColumns == 1) {
            $('layout-link').removeClass('disabled');
        }
        else if (numColumns == 4) {
            $('layout-link').addClass('disabled');
        }

        // Wire up the new column buttons to be AJAX
        rewriteAddColumnButtons('#row_' + rowid + '_column_' + colid);
        rewriteRemoveColumnButtons('#row_' + rowid + '_column_' + colid);
        makeExistingBlocksSortable(); //('#row_' + rowid);
        setEqualColumnHeights('#column-container > .row', 40);
    }

    /**
     * Removes a column from the view, sizes the others to take its place and
     * moves the blockinstances in it to the other columns
     */
    function removeColumn(rowid, colid) {
        var addColumnLeftButtonContainer;
        if (colid == 1) {
            // We are removing the first column, which has the button for adding a column to the left of itself. We want to keep this
            addColumnLeftButtonContainer = $('#row_' + rowid + '_column_1 .add-column-left');
        }

        // Save the blockinstances that are in the column to remove
        var blockInstances = $('#row_' + rowid + '_column_' + colid + ' .blockinstance');

        // Remove the column itself
        $('#row_' + rowid + '_column_' + colid).remove();
        // Get the existing number of columns
        var match = $('#row_' + rowid + ' div.column:first').attr('class').match(/columns([0-9]+)/)[1];
        var numColumns = parseInt(match, 10);

        // Renumber the columnsN classes of the remaining columns, and remove any set widths
        $('#row_' + rowid + ' .columns' + numColumns).each(function() {
            $(this).removeClass('columns' + numColumns);
            $(this).addClass('columns' + (numColumns - 1));
            $(this).removeAttr('style');
        });

        // All columns above the one removed need to be renumbered
        if (colid < numColumns) {
            for (var i = colid; i < numColumns; i++) {
                var oldID = i + 1;
                var newID = i;
                $('#row_' + rowid + '_column_' + oldID).attr('id', 'row_' + rowid + '_column_' + newID);

                // Renumber the add/remove column buttons
                $('#row_' + rowid + '_column_' + newID + ' input.addcolumn').attr('name', 'action_addcolumn_row_' + rowid + '_before_' + oldID);
                $('#row_' + rowid + '_column_' + newID + ' input.removecolumn').attr('name', 'action_removecolumn_row_' +rowid + '_column_' + newID);
            }
        }

        if (numColumns == 2) {
            $('layout-link').addClass('disabled');
        }
        else if (numColumns == 5) {
            $('layout-link').removeClass('disabled');
        }

        // The last column needs the class of the header changed, the first column possibly too
        if (addColumnLeftButtonContainer) {
            $('#row_' + rowid + '_column_1 .remove-column').before(addColumnLeftButtonContainer);
        }

        var lastColumn = $('#row_' + rowid + '_column_' + (numColumns - 1));
        var addColumnRightButtonContainer = $('.add-column-right', lastColumn);
        if (!addColumnRightButtonContainer) {
            var addColumnRightButtonContainer = $('.add-column-center', lastColumn);
            $(addColumnRightButtonContainer).removeClass('add-column-center');
            $(addColumnRightButtonContainer).addClass('add-column-right');
        }

        // Put the block instances that were in the removed column into the other columns
        var i = 1;
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
        setEqualColumnHeights('#column-container > .row', 40);
    }

    function getConfigureForm(blockinstance) {
        var button = blockinstance.find('input.configurebutton');
        var blockinstanceId = blockinstance.attr('id').substr(blockinstance.attr('id').lastIndexOf('_') + 1);
        var contentDiv = blockinstance.find('div.blockinstance-content');

        var pd = {'id': $('#viewid').val(), 'change': 1};
        if (config.blockeditormaxwidth) {
            // Shouldn't have to pass browser window dimensions here, but can't find
            // another way to get tinymce elements to use up the available height.
            pd['cfheight'] = $(window).height() - 100;
        }
        pd[button.attr('name')] = 1;

        var oldContent = contentDiv.html();

        // Put a loading message in place while the form downloads
        var loading = $('<img>').attr('src', config.theme['images/loading.gif']);
        contentDiv.empty().append(loading).append(' Loading...');
        sendjsonrequest('blocks.json.php', pd, 'POST', function(data) {
            contentDiv.html(oldContent);
            addConfigureBlock(blockinstance, data.data);
            $('#action-dummy').attr('name', button.attr('name'));

            var cancelButton = $('#cancel_instconf_action_configureblockinstance_id_' + blockinstanceId);
            cancelButton.click(function(event) {
                event.stopPropagation();
                event.preventDefault();
                removeConfigureBlocks();
                showMediaPlayers();
                removeExpanders($('#blockinstance_' + blockinstanceId));
                setupExpanders($('body'));
                button.focus();
            });
        });
    }

    function hideMediaPlayers() {
        $('#column-container .mediaplayer-container').each(function() {
            $(this).height($(this).height()); // retain height while hiding
            $('mediaplayer:first', this).hide();
            $('object', this).each(function() {
                $(this).addClass('in-mediaplayer');
            });
        });

        // Try to find and hide players floating around in text blocks, etc. by looking for object elements
        $('#column-container object').each(function() {
            if (!$(this).hasClass('in-mediaplayer')) {
                var temp = $('<div>').addClass('hidden mediaplayer-placeholder');
                $(temp).height($(this).height());
                $(this).after(temp);
                $(this).addClass('hidden');
                $(temp).removeClass('hidden');
            }
        });
    }

    function showMediaPlayers() {
        if (!config['handheld_device'] && tinyMCE && tinyMCE.activeEditor && tinyMCE.activeEditor.id) {
            tinyMCE.execCommand('mceRemoveEditor', false, tinyMCE.activeEditor.id);
        }
        $('#column-container .mediaplayer-container').each(function() {
            $(this).css({'height': ''});
            $('mediaplayer:first', this).show();
            $(this).height($(this).height());
        });
        $('#column-container .mediaplayer-placeholder').each(function() {
            $(this).addClass('hidden');
            $(this).prev().removeClass('hidden');
            $(this).remove();
        });
        $('#overlay').remove();
        $('#container').removeAttr('aria-hidden');
    }

    /**
     * Wire up the view theme selector
     */
    function rewriteViewThemeSelector() {
        if (!viewThemeSelect) {
            return;
        }
        var currentTheme = $('option:selected', viewThemeSelect).val();
        viewThemeSelect.change(function() {
                if ($('option:selected', viewThemeSelect).val() != currentTheme) {
                    $(viewThemeSelect).closest('form').submit();
                }
        });
    }

    function addConfigureBlock(oldblock, configblock, removeoncancel) {
        hideMediaPlayers();
        var temp = $('<div>').html(configblock.html);
        var newblock = $('#configureblock').addClass('hidden');
        var title = temp.find('.blockinstance .blockinstance-header').html();
        var content = temp.find('.blockinstance .blockinstance-content').html();
        newblock.find('.blockinstance-header').html(title);
        newblock.find('.blockinstance-content').html(content);
        $('body').append(newblock);

        var blockinstanceId = temp.find('.blockinstance').attr('id');
        blockinstanceId = blockinstanceId.substr(0, blockinstanceId.length - '_configure'.length);
        blockinstanceId = blockinstanceId.substr(blockinstanceId.lastIndexOf('_') + 1);

        setDialogPosition(newblock);

        var deletebutton = newblock.find('input.deletebutton');
        deletebutton.unbind().attr('name', 'action_removeblockinstance_id_' + blockinstanceId);

        if (removeoncancel) {
            rewriteDeleteButton(deletebutton);

            var cancelbutton = $('#cancel_instconf_action_configureblockinstance_id_' + blockinstanceId);
            if (cancelbutton) {
                cancelbutton.attr('name', deletebutton.attr('name'));
                cancelbutton.unbind();
                rewriteCancelButton(cancelbutton, blockinstanceId);
            }
        }
        else {
            deletebutton.click(function(event) {
                event.stopPropagation();
                event.preventDefault();
                removeConfigureBlocks();
                showMediaPlayers();
                setTimeout(function() {
                    oldblock.find('input.configurebutton').focus();
                }, 1);
            });
        }

        newblock.removeClass('hidden');
        appendChildNodes(document.body, DIV({id: 'overlay'}));
        (function($) {
            // configblock.javascript might use MochiKit so $ must have its default value
            eval(configblock.javascript);
        })(getElement);

        // Lock focus to the newly opened dialog
        newblock.find('.deletebutton').focus();
        keytabbinginadialog(newblock, newblock.find('.deletebutton'), newblock.find('.cancel'));
        $('#container').attr('aria-hidden', 'true');
    } // end of addConfigureBlock()

    function removeConfigureBlocks() {
        // FF3 hangs unless you delay removal of the iframe inside the old configure block
        setTimeout(function() {
            $('div.configure').each( function() {
                $(this).addClass('hidden');
            });
        }, 1);
    }

    /*
     * Moves the given dialog so that it's centered on the screen
     */
    function setDialogPosition(block) {
        var style = {
            'position': 'absolute',
            'z-index': 1
        };

        var d = {
            'w': block.width(),
            'h': block.height()
        }
        var vpdim = {
            'w': $(window).width(),
            'h': $(window).height()
        }

        var h = Math.max(d.h, 200);
        var w = Math.max(d.w, 625);
        if (config.blockeditormaxwidth && block.find('textarea.wysiwyg').length) {
            w = vpdim.w - 80;
            style.height = h + 'px';
        }

        var tborder = parseFloat(block.css('border-top-width'));
        var tpadding = parseFloat(block.css('padding-top'));
        var newtop = getViewportPosition().y + Math.max((vpdim.h - h) / 2 - tborder - tpadding, 5);
        style.top = newtop + 'px';

        var lborder = parseFloat(block.css('border-left-width'));
        var lpadding = parseFloat(block.css('padding-left'));
        style.left = ((vpdim.w - w) / 2 - lborder - lpadding) + 'px';
        style.width = w + 'px';

        for (var prop in style) {
            block.css(prop, style[prop]);
        }
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
        var columnContainer = $('.block-placeholder').closest('div.column');
        var row = parseInt(columnContainer.attr('id').match(/row_(\d+)_column_(\d+)/)[1], 10);
        var column = parseInt(columnContainer.attr('id').match(/row_(\d+)_column_(\d+)/)[2], 10);
        var columnContent = columnContainer.find('div.column-content');
        var order  = 0;
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

    $(window).resize(function () {
        setContentEditorPosition();
    });

    var lastscrolltop = -1;
    var topofpaneltopage = 0;
    var topofbottompane = 0;
    $(window).scroll(function() {
        var windowscrolltop = $(this).scrollTop();
        if (lastscrolltop == -1) {
            topofpaneltopage = $('#editcontent-sidebar-wrapper').offset().top;
            topofbottompane = $('#bottom-pane').offset().top;
        }
        var topofpanel = $('#editcontent-sidebar-wrapper').position().top;
        var foot = $('#footer-wrap').position().top;
        if (topofpanel < topofpaneltopage) {
            topofpanel = topofpaneltopage;
        }
        if (windowscrolltop > lastscrolltop) {
            // move the panel when it hits the top of the browser window and stop
            // it when it almost reaches the foor-wrap div to avoid infinite scroll
            // downward when panel extends below browser window base
            if ((topofpanel <= windowscrolltop) && (foot > (windowscrolltop + 150))) {
                $('#editcontent-sidebar-wrapper').css('top',(windowscrolltop - (topofpaneltopage - 20)));
            }
        }
        else {
            // upwards scrolling code
            if (windowscrolltop >= topofpaneltopage) {
                $('#editcontent-sidebar-wrapper').css('top',(windowscrolltop - (topofpaneltopage - 20)));
            }
            else {
                // to correct alignment if scrolling too fast
                if (windowscrolltop < topofpaneltopage) {
                    $('#editcontent-sidebar-wrapper').css('top',20);
                }
            }
        }
        lastscrolltop = windowscrolltop;
    });

    /**
     * Initialise
     *
     */
    $(document).ready(function() {
        init();
        /**
         * changes the intructions so they are for ajax
         */
        $('#blocksinstruction').html(strings['blocksinstructionajax']);
    });

}( window.ViewManager = window.ViewManager || {}, jQuery ));

ViewManager.addCSSRules();

$j = jQuery;

function blockConfigSuccess(form, data) {
    if (data.formelementsuccess) {
        eval(data.formelementsuccess + '(form, data)');
    }
    if (data.blockid) {
        ViewManager.replaceConfigureBlock(data);
    }
    if (data.otherblocks) {
        $j.each(data.otherblocks, function( ind, val ) {
            ViewManager.replaceConfigureBlock(val);
        });
    }
    setupExpanders($j('body'));
}

function blockConfigError(form, data) {
    if (data.formelementerror) {
        eval(data.formelementerror + '(form, data)');
        return;
    }
}

function updateBlockConfigWidth(configblock, width) {
    var vpdim = getViewportDimensions();
    var w = Math.max(width, 625);
    var style = {
        'position': 'absolute',
        'z-index': 1
    };
    var lborder = parseFloat(getStyle(configblock, 'border-left-width'));
    var lpadding = parseFloat(getStyle(configblock, 'padding-left'));
    style.left = ((vpdim.w - w) / 2 - lborder - lpadding) + 'px';
    style.width = w + 'px';

    setStyle(configblock, style);
}

