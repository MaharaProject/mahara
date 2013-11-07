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

    //Public Properties
    // Whether the browser is IE7 - needed for some hacks
    ViewManager.isIE8 = $.browser.msie && $.browser.version == 8.0;
    ViewManager.isIE7 = $.browser.msie && $.browser.version == 7.0;
    ViewManager.isIE6 = $.browser.msie && $.browser.version == 6.0;
    ViewManager.isOldIE = $.browser.msie && $.browser.version < 9.0;
    ViewManager.contentEditorWidth = 145;
    // Whether the brower is iPhone, IPad or IPod
    if (config['handheld_device'] || (navigator.userAgent.match(/iPhone/i)) || (navigator.userAgent.match(/iPod/i)) || (navigator.userAgent.match(/iPad/i))) {
        ViewManager.isIE6 = true;
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
            var temp = $('<div>' + data.data.html + '</div>');
            // Append any inline js to data.data.javascript
            for (i in temp) {
                if (temp[i].nodeName === 'SCRIPT' && temp[i].src === '') {
                    data.data.javascript += temp[i].innerHTML;
                }
            }
            var newblock = temp.find('div.blockinstance');

            $('.blockinstance-header', newblock).mousedown(function() {
                    $('.row .column-content').each(function() {
                        $(this).animate({backgroundColor:'#F5F5F5'}, 100);
                    });
                });

            $('.blockinstance-header', newblock).mouseup(function() {
                    $('.row .column-content').each(function() {
                        $(this).animate({backgroundColor:'#FFFFFF'}, 500);
                    });
                });

            swapNodes(oldblock.get()[0], newblock.get()[0]); // using DOM objects, not jQuery objects so we needn't worry about IDs
            eval(data.data.javascript);
            rewriteConfigureButton(newblock.find('input.configurebutton'));
            rewriteDeleteButton(newblock.find('input.deletebutton'));
        }
        removeConfigureBlocks();
        showMediaPlayers();
    };

    //Private Methods
    /////////////////
    function init() {

        contentEditor = $('#content-editor');
        bottomPane = $('#bottom-pane');
        viewThemeSelect = $('#viewtheme-select');
        viewsLoading = $('#views-loading');

        if (!ViewManager.isIE6) {
            // Hide 'new block here' buttons
            $('#bottom-pane div.add-button').each(function() {
                $(this).remove();
            });

            // Hide controls in each block instance that are not needed
            $('#bottom-pane input.movebutton').each(function() {
                $(this).remove();
            });

            // Remove radio buttons for moving block types into place
            $('#content-editor input.blocktype-radio').each(function() {
                if (ViewManager.isIE6 || ViewManager.isIE7 || ViewManager.isIE8) {
                    $(this).hide();
                }
                else {
                    $(this).get(0).type = 'hidden';
                }
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
        else if (config['handheld_device'] || ViewManager.isIE6 || (navigator.userAgent.match(/iPhone/i)) || (navigator.userAgent.match(/iPod/i)) || (navigator.userAgent.match(/iPad/i))) {
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

        $('#accordion').accordion({
            clearStyle: true,
            icons: false,
            autoHeight: false,
            collapsible: true,
            active: false,
            change: function(event, ui) {
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
                        if (config['handheld_device'] || ViewManager.isIE6 || (navigator.userAgent.match(/iPhone/i)) || (navigator.userAgent.match(/iPod/i)) || (navigator.userAgent.match(/iPad/i))) {
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

        // Rewrite the 'add column' buttons to be ajax
        rewriteAddColumnButtons();

        // Rewrite the 'remove column' buttons to be ajax
        rewriteRemoveColumnButtons();

        // Ensure the enabled/disabled state of the add/remove buttons is correct
        checkColumnButtonDisabledState();

        // Set equal column heights
        setTimeout(function() {
            //safari needs delay to load images
            setEqualColumnHeights('.row', 40);
        }, 150);

        showColumnBackgroundsOnSort();

        rewriteViewThemeSelector();

        if (!ViewManager.isIE6) {
            makeNewBlocksDraggable();
            makeExistingBlocksSortable();
        }

        $(viewsLoading).remove();

        $(bottomPane).show();
    } // init

    function checkEditAreaHeight() {
        // to make sure the 'floating' panel when opened is not longer than
        // the 'containing' div
        var editwrapper = $('#editcontent-sidebar-wrapper');
        var editwrapperheight = (parseInt(editwrapper.css('height')) + parseInt(editwrapper.css('padding-top')) + parseInt(editwrapper.css('padding-bottom')));
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
        document.cookie=cookieName+"="+ (isCollapsed ? '1': '0') +"; expires=Wednesday, 01-Aug-2040 08:00:00 GMT";
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
        });
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
                    //add new block
                    var pd = {
                            'id': $('#viewid').val(),
                            'change': 1,
                            'blocktype': ui.helper.find('input.blocktype-radio').val()
                        };

                    if (config.blockeditormaxwidth) {
                        pd['cfheight'] = getViewportDimensions().h - 100;
                    }
                    pd['action_addblocktype_row_' + whereTo['row'] + '_column_' + whereTo['column'] + '_order_' + whereTo['order']] = true;

                    sendjsonrequest(config['wwwroot'] + 'view/blocks.json.php', pd, 'POST', function(data) {
                        var div = $('<div>').html(data.data.display.html);
                        var blockinstance = div.find('div.blockinstance');
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
                    });
                    $('.block-placeholder').siblings('.blocktype').remove();
                }
                else {
                    //move existing block
                    var pd = {
                        'id': $('#viewid').val(),
                        'change': 1
                    };
                    if (config.blockeditormaxwidth) {
                        pd['cfheight'] = getViewportDimensions().h - 100;
                    }
                    var uihId = ui.helper.attr('id');
                    var blockinstanceId = uihId.substr(uihId.lastIndexOf('_') + 1);
                    pd['action_moveblockinstance_id_' + blockinstanceId + '_row_' + whereTo['row'] + '_column_' + whereTo['column'] + '_order_' + whereTo['order']] = true;
                    sendjsonrequest(config['wwwroot'] + 'view/blocks.json.php', pd, 'POST', function(data) {
                        hideColumnBackgrounds();
                    });
                }
            },

            update: function(event, ui) {
                if (!ViewManager.isIE6) {
                    $('.row .column-content').each(function() {
                        $(this).css('min-height', '');
                    });
                    setEqualColumnHeights('.row', 40);
                }
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

    function showColumnBackgrounds() {
        $('.row .column-content').each(function() {
            $(this).animate({backgroundColor:'#F5F5F5'}, 100);
        });
    }

    function hideColumnBackgrounds() {
        $('.row .column-content').each(function() {
            $(this).animate({backgroundColor:'#FFFFFF'}, 500);
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
            // for ie6, set height since min-height isn't supported
            if (ViewManager.isIE6) {
                $(this).find('.column-content').css({'height': currentTallest});
            }
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
    this.rewriteCategorySelectList = function() {
        forEach(getElementsByTagAndClassName('a', null, 'category-list'), function(i) {
            connect(i, 'onclick', function(e) {
                var queryString = parseQueryString(i.href.substr(i.href.indexOf('?')));
                removeElementClass(getFirstElementByTagAndClassName('li', 'current', 'category-list'), 'current');
                addElementClass(i.parentNode, 'current');
                sendjsonrequest(config['wwwroot'] + 'view/blocks.json.php', {'id': $('viewid').value, 'action': 'blocktype_list', 'c': queryString['c']}, 'POST', function(data) {
                    setNodeAttribute('category', 'value', queryString['c']);
                    $('blocktype-list').innerHTML = data.data;
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
                    if ($('#blockinstance_' + blockinstanceId + '_configure').length) {
                        removeConfigureBlocks();
                        showMediaPlayers();
                    }
                    //reset column heights
                    $('.column-content').each(function() {
                        $(this).css('min-height', '');
                    });
                    setEqualColumnHeights($('.row'), 50);
                    if (ViewManager.isIE6) {
                        // refresh the 'add block here' buttons
                        ViewManager.displayPage(config['wwwroot'] + 'view/blocks.php?id=' + $('#viewid').val());
                    }
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

    /**
     * Rewrites cancel button to remove a block
     */
    function rewriteCancelButton(button, blockinstanceId) {
        button.click(function(event) {
            var pd = {'id': $('#viewid').val(), 'change': 1};
            pd[button.attr('name')] = 1;
            sendjsonrequest(config['wwwroot'] + 'view/blocks.json.php', pd, 'POST', function(data) {
                $('#blockinstance_' + blockinstanceId).remove();
                if ($('#blockinstance_' + blockinstanceId + '_configure').length) {
                    removeConfigureBlocks();
                    showMediaPlayers();
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
            if (!self.isIE6 && self.topPane) {
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
        else if (config['handheld_device'] || (navigator.userAgent.match(/iPhone/i)) || (navigator.userAgent.match(/iPod/i)) || (navigator.userAgent.match(/iPad/i)) && self.topPane) {
            forEach(getElementsByTagAndClassName('input', 'blocktype-radio', 'top-pane'), function(i) {
                    setNodeAttribute(i, 'style', 'display:inline');
                });
            // Remove radio buttons for moving block types into place
            $('#top-pane input.blocktype-radio').each(function() {
                //$(this).attr('type', 'hidden'); // not allowed in jquery
                $(this).get(0).type = 'hidden'; // TODO need to test this across browsers
                if (ViewManager.isIE7 || ViewManager.isIE6) {
                    $(this).hide();
                }
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
        $('.row').each(function() {

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
        setEqualColumnHeights('.row', 40);
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
            // for ie6, set height since min-height isn't supported
            if (ViewManager.isIE6) {
                $(this).find('.column-content').css({'height': currentTallest});
            }
            $(this).find('.column-content').css({'min-height': currentTallest});
        });
        setEqualColumnHeights('.row', 40);
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

        $('body').prepend('<div/>', {
            id: 'overlay'
        });

    }

    function showMediaPlayers() {
        if (!config['handheld_device'] && tinyMCE && tinyMCE.activeEditor && tinyMCE.activeEditor.editorId) {
            tinyMCE.execCommand('mceRemoveControl', false, tinyMCE.activeEditor.editorId);
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
        var newblock = temp.find('div.blockinstance');
        newblock.hide();
        $('body').append(newblock);

        var style = {
            'position': 'absolute',
            'z-index': 1
        };

        var d = {
            'w': newblock.outerWidth(),
            'h': newblock.outerHeight()
        }
        var vpdim = {
            'w': $(window).width(),
            'h': $(window).height()
        }

        var h = Math.max(d.h, 200);
        var w = Math.max(d.w, 500);
        if (config.blockeditormaxwidth && newblock.find('textarea.wysiwyg').length) {
            w = vpdim.w - 80;
            style.height = h + 'px';
        }

        var tborder = parseFloat(newblock.css('border-top-width'));
        var tpadding = parseFloat(newblock.css('padding-top'));
        var newtop = getViewportPosition().y + Math.max((vpdim.h - h) / 2 - tborder - tpadding, 5);
        style.top = newtop + 'px';

        var lborder = parseFloat(newblock.css('border-left-width'));
        var lpadding = parseFloat(newblock.css('padding-left'));
        style.left = ((vpdim.w - w) / 2 - lborder - lpadding) + 'px';
        style.width = w + 'px';

        for (var prop in style) {
            newblock.css(prop, style[prop]);
        }

        var deletebutton = newblock.find('input.deletebutton');

        if (removeoncancel) {
            rewriteDeleteButton(deletebutton);

            var oldblockid = newblock.attr('id').substr(0, newblock.attr('id').length - '_configure'.length);
            var blockinstanceId = oldblockid.substr(oldblockid.lastIndexOf('_') + 1);
            var cancelbutton = $('#cancel_instconf_action_configureblockinstance_id_' + blockinstanceId);
            if (cancelbutton) {
                cancelbutton.attr('name', deletebutton.attr('name'));
                cancelbutton.unbind();
                rewriteCancelButton(cancelbutton, blockinstanceId);
            }
        }
        else {
            deletebutton.unbind();
            deletebutton.click(function(event) {
                event.stopPropagation();
                event.preventDefault();
                removeConfigureBlocks();
                showMediaPlayers();
            });
        }

        newblock.show();
        appendChildNodes(document.body, DIV({id: 'overlay'}));
        eval(configblock.javascript);
    } // end of addConfigureBlock()

    function removeConfigureBlocks() {
        // FF3 hangs unless you delay removal of the iframe inside the old configure block
        setTimeout(function() {
            $('div.configure').each( function() {
                $(this).remove();
            });
        }, 1);
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
        if (ViewManager.isIE6 && data.viewid) {
            document.location.href = config['wwwroot'] + 'view/blocks.php?id=' + data.viewid;
        }
        ViewManager.replaceConfigureBlock(data);
    }
    if (data.otherblocks) {
        $j.each(data.otherblocks, function( ind, val ) {
            ViewManager.replaceConfigureBlock(val);
        });
    }
}

function blockConfigError(form, data) {
    if (data.formelementerror) {
        eval(data.formelementerror + '(form, data)');
        return;
    }
}

function updateBlockConfigWidth(configblock, width) {
    var vpdim = getViewportDimensions();
    var w = Math.max(width, 500);
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

