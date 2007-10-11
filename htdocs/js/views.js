/**
 * Javascript for the new views interface
 *
 * Author: Nigel McNie
 */

function ViewManager() {
    var self = this;

    this.init = function () {
        self.bottomPane = $('bottom-pane');

        if (!self.isIE6) {
            // Set up the column container reference, and make the container the
            // base for positioned elements inside it
            self.columnContainer = $('column-container');
            makePositioned(self.columnContainer);

            // Hide 'new block here' buttons
            forEach(getElementsByTagAndClassName('div', 'add-button', self.bottomPane), function(i) {
                removeElement(i);
            });

            // Hide controls in each block instance that are not needed
            forEach(getElementsByTagAndClassName('input', 'movebutton', self.bottomPane), function(i) {
                removeElement(i);
            });

            // Remove radio buttons for moving block types into place
            forEach(getElementsByTagAndClassName('input', 'blocktype-radio', 'top-pane'), function(i) {
                setNodeAttribute(i, 'type', 'hidden');
            });

            // Rewrite the links in the category select list to be ajax
            self.rewriteCategorySelectList();

            // Rewrite the configure buttons to be ajax
            self.rewriteConfigureButtons();

            // Rewrite the delete buttons to be ajax
            self.rewriteDeleteButtons();

            // Rewrite the 'add column' buttons to be ajax
            self.rewriteAddColumnButtons();

            // Rewrite the 'remove column' buttons to be ajax
            self.rewriteRemoveColumnButtons();

            // Ensure the enabled/disabled state of the add/remove buttons is correct
            self.checkColumnButtonDisabledState();

            // Make the block instances draggable
            self.makeBlockinstancesDraggable();

            // Make the block types draggable
            self.makeBlockTypesDraggable();

            // Make the top pane a dropzone for cancelling adding block types
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

        // Now we're done, remove the loading message and display the page
        removeElement('views-loading');
        showElement(self.bottomPane);
    }

    /**
     * Adds a column to the view
     */
    this.addColumn = function(id, data) {
        // Get the existing number of columns
        var numColumns = parseInt(getNodeAttribute(getFirstElementByTagAndClassName('div', 'column', self.bottomPane), 'class').match(/columns([0-9]+)/)[1]);

        // Here we are doing two things:
        // 1) The existing columns that are higher than the one being inserted need to be renumbered
        // 2) All columns need their 'columnsN' class renumbered one higher
        // 3) All columns need their 'width' style attribute removed, if they have one
        for (var oldID = numColumns; oldID >= 1; oldID--) {
            var column = $('column_' + oldID);
            var newID = oldID + 1;
            if (oldID >= id) {
                $('column_' + oldID).setAttribute('id', 'column_' + newID);

                // Renumber the add/remove column buttons
                getFirstElementByTagAndClassName('input', 'addcolumn', 'column_' + newID).setAttribute('name', 'action_addcolumn_before_' + (newID + 1));
                getFirstElementByTagAndClassName('input', 'removecolumn', 'column_' + newID).setAttribute('name', 'action_removecolumn_id_' + newID);
            }
            removeElementClass(column, 'columns' + numColumns);
            addElementClass(column, 'columns' + (numColumns + 1));
            removeNodeAttribute(column, 'style');
        }

        // If the column being added is the very first one, the 'left' add column button needs to be removed
        if (id == 1) {
            removeElement(getFirstElementByTagAndClassName('div', 'add-column-left', 'column_2'));
        }

        // If we're adding a column to the very right, move the add button between the columns
        if (id > numColumns) {
            var rightColumnDiv = getFirstElementByTagAndClassName('div', 'add-column-right', 'column_' + numColumns);
            removeElementClass(rightColumnDiv, 'add-column-right');
            addElementClass(rightColumnDiv, 'add-column-center');
        }

        // Now we insert the new column into the DOM. Inserting the HTML into a
        // new element and then into the DOM means we can add the new column
        // without changing any of the existing DOM tree (and thus destroying
        // events)
        var tempDiv = DIV();
        tempDiv.innerHTML = data.data;
        if (id == 1) {
            insertSiblingNodesBefore('column_2', tempDiv.firstChild);
        }
        else {
            insertSiblingNodesAfter('column_' + (id - 1), tempDiv.firstChild);
        }

        if (numColumns == 1) {
            removeElementClass('layout-link', 'disabled');
        }
        else if (numColumns == 4) {
            addElementClass('layout-link', 'disabled');
        }

        // Wire up the new column buttons to be AJAX
        self.rewriteAddColumnButtons('column_' + id);
        self.rewriteRemoveColumnButtons('column_' + id);
    }

    /**
     * Removes a column from the view, sizes the others to take its place and
     * moves the blockinstances in it to the other columns
     */
    this.removeColumn = function(id) {
        var addColumnLeftButtonContainer;
        if (id == 1) {
            // We are removing the first column, which has the button for adding a column to the left of itself. We want to keep this
            addColumnLeftButtonContainer = getFirstElementByTagAndClassName('div', 'add-column-left', 'column_1');
        }

        // Save the blockinstances that are in the column to remove
        var blockInstances = getElementsByTagAndClassName('div', 'blockinstance', 'column_' + id);

        // Remove the column itself
        removeElement('column_' + id);
        // Get the existing number of columns
        var numColumns = parseInt(getNodeAttribute(getFirstElementByTagAndClassName('div', 'column', self.bottomPane), 'class').match(/columns([0-9]+)/)[1]);

        // Renumber the columnsN classes of the remaining columns, and remove any set widths
        forEach(getElementsByTagAndClassName('div', 'columns' + numColumns, self.bottomPane), function(i) {
            removeElementClass(i, 'columns' + numColumns);
            addElementClass(i, 'columns' + (numColumns - 1));

            removeNodeAttribute(i, 'style');
        });


        // All columns above the one removed need to be renumbered
        if (id < numColumns) {
            for (var i = id; i < numColumns; i++) {
                var oldID = i + 1;
                var newID = i;
                $('column_' + oldID).setAttribute('id', 'column_' + newID);

                // Renumber the add/remove column buttons
                getFirstElementByTagAndClassName('input', 'addcolumn', 'column_' + newID).setAttribute('name', 'action_addcolumn_before_' + oldID);
                getFirstElementByTagAndClassName('input', 'removecolumn', 'column_' + newID).setAttribute('name', 'action_removecolumn_id_' + newID);
            }
        }

        if (numColumns == 2) {
            addElementClass('layout-link', 'disabled');
        }
        else if (numColumns == 5) {
            removeElementClass('layout-link', 'disabled');
        }

        // The last column needs the class of the header changed, the first column possibly too
        if (addColumnLeftButtonContainer) {
            insertSiblingNodesBefore(
                getFirstElementByTagAndClassName('div', 'remove-column', 'column_1'),
                addColumnLeftButtonContainer
            );
        }

        var lastColumn = $('column_' + (numColumns - 1));
        var addColumnRightButtonContainer = getFirstElementByTagAndClassName('div', 'add-column-right', lastColumn);
        if (!addColumnRightButtonContainer) {
            var addColumnRightButtonContainer = getFirstElementByTagAndClassName('div', 'add-column-center', lastColumn);
            removeElementClass(addColumnRightButtonContainer, 'add-column-center');
            addElementClass(addColumnRightButtonContainer, 'add-column-right');
        }

        // Put the block instances that were in the removed column into the other columns
        var i = 1;
        forEach(blockInstances, function(instance) {
            appendChildNodes(getFirstElementByTagAndClassName('div', 'column-content', 'column_' + i), instance);
            if (i < (numColumns - 1)) {
                i++;
            }
            else {
                i = 1;
            }
        });
    }

    /**
     * Rewrites the category select links to be AJAX
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
                });
                e.stop();
            });
        });
    }

    /**
     * Rewrites the blockinstance configure buttons to be AJAX
     */
    this.rewriteConfigureButtons = function() {
        forEach(getElementsByTagAndClassName('input', 'configurebutton', self.bottomPane), function(i) {
            self.rewriteConfigureButton(i);
        });
    }

    /**
     * Rewrites one configure button to be AJAX
     */
    this.rewriteConfigureButton = function(button) {
        connect(button, 'onclick', function(e) {
            e.stop();
            setNodeAttribute(button, 'disabled', 'disabled');
            var pd = {'id': $('viewid').value, 'change': 1};
            pd[getNodeAttribute(e.src(), 'name')] = 1;
            sendjsonrequest('blocks.json.php', pd, 'POST', function(data) {
                var blockinstance = getFirstParentByTagAndClassName(button, 'div', 'blockinstance');
                var blockinstanceId = blockinstance.id.substr(blockinstance.id.lastIndexOf('_') + 1);
                var content = getFirstElementByTagAndClassName('div', 'blockinstance-content', blockinstance);
                var oldContent = content.innerHTML;
                content.innerHTML = data.data['html'];
                eval(data.data['js']);

                // Make the cancel button be supersmart
                var cancelButton = $('cancel_cb_' + blockinstanceId + '_action_configureblockinstance_id_' + blockinstanceId);
                connect(cancelButton, 'onclick', function(e) {
                    e.stop();
                    content.innerHTML = oldContent;
                    removeNodeAttribute(button, 'disabled');
                });

            }, function() {
                removeNodeAttribute(button, 'disabled');
            });
        });
    }

    /**
     * Rewrites the blockinstance delete buttons to be AJAX
     */
    this.rewriteDeleteButtons = function() {
        forEach(getElementsByTagAndClassName('input', 'deletebutton', self.bottomPane), function(i) {
            self.rewriteDeleteButton(i);
        });
    }

    /**
     * Rewrites one delete button to be AJAX
     */
    this.rewriteDeleteButton = function(button) {
        connect(button, 'onclick', function(e) {
            setNodeAttribute(button, 'disabled', 'disabled');
            if (confirm(get_string('confirmdeleteblockinstance'))) {
                var pd = {'id': $('viewid').value, 'change': 1};
                pd[getNodeAttribute(e.src(), 'name')] = 1;
                sendjsonrequest(config['wwwroot'] + 'view/blocks.json.php', pd, 'POST', function(data) {
                    removeElement(getFirstParentByTagAndClassName(button, 'div', 'blockinstance'));
                    removeNodeAttribute(button, 'disabled');
                }, function() {
                    removeNodeAttribute(button, 'disabled');
                });
            }
            else {
                removeNodeAttribute(button, 'disabled');
            }
            e.stop();
        });
    }

    /**
     * Rewrites the add column buttons to be AJAX
     *
     * If the first parameter is a string/element, only the buttons below that
     * element will be rewritten
     */
    this.rewriteAddColumnButtons = function() {
        var parentNode;
        if (typeof(arguments[0]) != 'undefined') {
            parentNode = arguments[0];
        }
        else {
            parentNode = self.bottomPane;
        }

        forEach(getElementsByTagAndClassName('input', 'addcolumn', parentNode), function(i) {
            connect(i, 'onclick', function(e) {
                // Work around for a konqueror bug - konqueror passes onclick
                // events to disabled buttons
                if (!i.disabled) {
                    setNodeAttribute(i, 'disabled', 'disabled');
                    var name = getNodeAttribute(e.src(), 'name');
                    var id   = parseInt(name.substr(name.length - 1, 1));
                    var pd   = {'id': $('viewid').value, 'change': 1}
                    pd['action_addcolumn_before_' + id] = 1;
                    sendjsonrequest(config['wwwroot'] + 'view/blocks.json.php', pd, 'POST', function(data) {
                        self.addColumn(id, data);
                        self.checkColumnButtonDisabledState();
                    }, function() {
                        self.checkColumnButtonDisabledState();
                    });
                }
                e.stop();
            });
        });
    }

    /**
     * Rewrite the remove column buttons to be AJAX
     * 
     * If the first parameter is a string/element, only the buttons below that
     * element will be rewritten
     */
    this.rewriteRemoveColumnButtons = function() {
        var parentNode;
        if (typeof(arguments[0]) != 'undefined') {
            parentNode = arguments[0];
        }
        else {
            parentNode = self.bottomPane;
        }

        forEach(getElementsByTagAndClassName('input', 'removecolumn', parentNode), function(i) {
            connect(i, 'onclick', function(e) {
                // Work around for a konqueror bug - konqueror passes onclick
                // events to disabled buttons
                if (!i.disabled) {
                    setNodeAttribute(i, 'disabled', 'disabled');
                    var name = getNodeAttribute(e.src(), 'name');
                    var id   = parseInt(name.substr(name.length - 1, 1));
                    var pd   = {'id': $('viewid').value, 'change': 1}
                    pd['action_removecolumn_column_' + id] = 1;
                    sendjsonrequest(config['wwwroot'] + 'view/blocks.json.php', pd, 'POST', function(data) {
                        self.removeColumn(id);
                        self.checkColumnButtonDisabledState();
                    }, function() {
                        self.checkColumnButtonDisabledState();
                    });
                }
                e.stop();
            });
        });
    }
    
    /**
     * Disables the 'add column' buttons
     */
    this.checkColumnButtonDisabledState = function() {
        // Get the existing number of columns
        var numColumns = parseInt(getNodeAttribute(getFirstElementByTagAndClassName('div', 'column', self.bottomPane), 'class').match(/columns([0-9]+)/)[1]);

        var state = (numColumns == 5);
        forEach(getElementsByTagAndClassName('input', 'addcolumn', self.bottomPane), function(i) {
            if (state) {
                setNodeAttribute(i, 'disabled', 'disabled');
            }
            else {
                removeNodeAttribute(i, 'disabled');
            }
        });

        var state = (numColumns == 1);
        forEach(getElementsByTagAndClassName('input', 'removecolumn', self.bottomPane), function(i) {
            if (state) {
                setNodeAttribute(i, 'disabled', 'disabled');
            }
            else {
                removeNodeAttribute(i, 'disabled');
            }
        });
    }

    /**
     * Makes block instances draggable
     */
    this.makeBlockinstancesDraggable = function() {
        forEach(getElementsByTagAndClassName('div', 'blockinstance', self.bottomPane), function(i) {
            self.makeBlockinstanceDraggable(i);
        });
    }

    /**
     * Make a particular blockinstance draggable
     */
    this.makeBlockinstanceDraggable = function(blockinstance) {
        new Draggable(blockinstance, {
            'handle': 'blockinstance-header',
            'starteffect': function () {
                self.currentlyMovingObject = blockinstance;
                self.origCoordinates = self.getBlockinstanceCoordinates(blockinstance);
                self.createHotzones();

                // Set the positioning of the blockinstance to 'absolute',
                // so that it is taken out of the document flow (so the
                // other blocks can collapse into its space if necessary if
                // it's dragged around). This changes how the width is
                // calculated, as the width is 'auto' by default, so we
                // explicitly set it to have the width it needs.
                var dimensions = elementDimensions(blockinstance);
                setElementDimensions(blockinstance, dimensions);
                setStyle(blockinstance, {
                    'position': 'absolute'
                });

                // Resize the placeholder div
                // NOTE: negative offset to account for the border. This might be removed
                setElementDimensions(self.blockPlaceholder, {h: dimensions.h - 2});
                setStyle(self.blockPlaceholder, {'width': '100%'});

                setOpacity(blockinstance, 0.5);
            },
            'revert': true,
            'reverteffect': function (innerelement, top_offset, left_offset) {
                self.destroyHotzones();

                // Removing the block placeholder then reverting the position
                // of the dragged blockinstance results in a flash where the
                // height of the page reduces and then expands again. We can
                // fix this
                setElementDimensions(self.bottomPane, getElementDimensions(self.bottomPane));
                // LEAVE THIS LINE
                // Without it, firefox doesn't actually set the element
                // dimensions properly, it seems, resulting in the very flash
                // we are trying to get rid of
                getElementDimensions(self.bottomPane);

                // We don't need the block placeholder anymore
                removeElement(self.blockPlaceholder);

                // Revert the 'absolute' positioning of the blockinstance being moved
                setStyle(self.currentlyMovingObject, {
                    'top': 0,
                    'left': 0,
                    'position': 'relative',
                    'width': 'auto',
                    'height': 'auto'
                });

                // Revert the explicit size setting for the bottom pane
                setStyle(self.bottomPane, {
                    'width': 'auto',
                    'height': 'auto'
                });

                // No longer is there a 'last hotzone' that was being dragged over
                self.lastHotzone = null;

                setOpacity(blockinstance, 1);

                // Sadly we have to return an effect, because this requires
                // something cancellable. Would be good to return nothing
                return new MochiKit.Visual.Move(innerelement,
                    {x: 0, y: 0, duration: 0});
            }
        });
    }

    /**
     * Makes block types draggable
     */
    this.makeBlockTypesDraggable = function() {
        forEach(getElementsByTagAndClassName('div', 'blocktype', 'blocktype-list'), function(i) {
            // Overlay a div that is actually the thing that is dragged. This
            // is done because MochiKit's ghosting actually drags the element,
            // meaning revert ends up with a horrid flash when the element is
            // moved back into place
            makePositioned(i);
            var clone = DIV({'class': 'blocktype-clone'});

            // The border rule is required for IE7 to behave - and it needs the
            // semi-colon!
            setStyle(clone, {
                'border': '0px dotted transparent;',
                'position': 'absolute'
            });


            if (self.isIE7) {
                appendChildNodes(clone, i.cloneNode(true));
                hideElement(i);
            }
            else {
                setElementPosition(clone, getElementPosition(i, 'top-pane'));
                setOpacity(clone, 0.5);
            }
            insertSiblingNodesAfter(i, clone);
            setElementDimensions(clone, getElementDimensions(i));

            // Prevents the height of the blocktype list doubling when dragging
            if (self.isIE7) {
                i.style.position = 'absolute';
            }

            new Draggable(clone, {
                'starteffect': function () {
                    self.movingBlockType = true;

                    self.currentlyMovingObject = i;
                    self.createHotzones();

                    // Resize the placeholder div
                    setStyle(self.blockPlaceholder, {
                        'width' : '100%',
                        'height': '50px'
                    });

                    // Make it a ghost. Done when starting the drag because
                    // some browsers have trouble rendering things right on top
                    // of one another
                    if (self.isIE7) {
                        showElement(i);
                        setOpacity(clone, 0.5);
                    }
                    else {
                        appendChildNodes(clone, i.cloneNode(true));
                    }
                },
                'revert': true,
                'reverteffect': function (innerelement, top_offset, left_offset) {
                    if (self.isIE7) {
                        // Move the clone back and hide the filler used while
                        // dragging
                        setElementPosition(clone, {x: 0, y: 0});
                        hideElement(i);
                        setOpacity(clone, 1);
                    }
                    else {
                        // The actual draggable is the clone we were dragging
                        // around, we can put it back and remove the clone now
                        replaceChildNodes(clone);
                        setElementPosition(clone, getElementPosition(i, 'top-pane'));
                    }

                    self.destroyHotzones();

                    // No longer is there a 'last hotzone' that was being dragged over
                    self.lastHotzone = null;

                    // We don't need the block placeholder anymore, but it
                    // still might be needed in the dropfunction, as the
                    // reverteffect is called before the dropfunction. So we
                    // only hide it here
                    hideElement(self.blockPlaceholder);

                    self.movingBlockType = false;

                    // Sadly we have to return an effect, because this requires
                    // something cancellable. Would be good to return nothing
                    return new MochiKit.Visual.Move(innerelement,
                        {x: 0, y: 0, duration: 0});

                }
            });
        });
    }

    /**
     * Place hotzones over the blockinstances on the page, so that we can work
     * out where to drop the blockinstance.
     *
     * This gets called when a blockinstance starts moving.
     *
     * Hotzone stuff
     * =============
     *
     * When a blockinstance is being dragged, a number of 'hotzones' are
     * placed over the blocks, for detecting where the block should be
     * placed when it is dropped. These extend over the bottom half of a
     * block and the top half of the one below it. There is also one
     * covering the top half of the first block in each column, and one
     * covering the bottom of the column (including the bottom half of the
     * last blockinstance).
     *
     * The hotzones are placed in their own div in the column container,
     * and are absolutely positioned relative to the column container in
     * their correct locations.
     *
     * When the dragged block is over one of these hotzones, it triggers a
     * placeholder div to be put in place the size of the block being
     * dragged, in the correct location. This gives the appearance of space
     * opening up for the block where it will be dropped.
     *
     * When the block is dropped, it will be moved from its old position in
     * the DOM to the new one, and the hotzones removed. If the block was
     * not dropped over a hotzone, it reverts to where it was.
     */
    this.createHotzones = function() {
        // Make a container for all of the hotzone divs
        self.hotzoneContainer = DIV();
        appendChildNodes(self.columnContainer, self.hotzoneContainer);
        var previousHotzone = null;

        // Keeps track of whether we have seen the blockinstance that is being
        // dragged in this column yet
        var afterCurrentlyMovingBlockinstance = false;

        // We place the hotzones by looping through the blockinstances on the
        // page and adding the hotzones to over the top of them as appropriate
        forEach(getElementsByTagAndClassName('div', 'blockinstance', self.bottomPane), function(i) {
            var blockinstancePosition   = elementPosition(i, self.columnContainer);
            var blockinstanceDimensions = elementDimensions(i);
            // NOTE: added for the border
            blockinstanceDimensions.w += 4;

            // Work out whether the given blockinstance is at the top of the column
            if (getFirstElementByTagAndClassName('div', 'blockinstance', getFirstParentByTagAndClassName(i, 'div', 'column-content')) == i) {
                // Put a hotzone across the top half of the blockinstance
                var hotzone = self.createHotzone(i, insertSiblingNodesBefore);
                setElementPosition(hotzone, {x: blockinstancePosition.x, y:0});
                setElementDimensions(hotzone, {w: blockinstanceDimensions.w, h: blockinstanceDimensions.h / 2 + blockinstancePosition.y});

                previousHotzone = hotzone;

                afterCurrentlyMovingBlockinstance = false;
            }

            // Work out if there is a blockinstance below the current one
            var nextBlockinstance = i.nextSibling;
            var nextBlockinstancePosition = null;
            var nextBlockinstanceDimensions = null;
            while (nextBlockinstance != null) {
                if (hasElementClass(nextBlockinstance, 'blockinstance')) {
                    // If there is one, work out its position and dimensions for later
                    nextBlockinstancePosition   = elementPosition(nextBlockinstance, self.columnContainer);
                    nextBlockinstanceDimensions = elementDimensions(nextBlockinstance);
                    break;
                }

                nextBlockinstance = nextBlockinstance.nextSibling;
            }

            // Work out the position and size of the previous hotzone, for use
            // in placing the next hotzone
            var previousHotzonePosition = elementPosition(previousHotzone, self.columnContainer);
            var previousHotzoneDimensions = elementDimensions(previousHotzone);

            // If there is a blockinstance below this one, then we put another
            // hotzone covering half of the current blockinstance and half on
            // the one below we found. Otherwise, we just cover the rest of the
            // column.
            if (nextBlockinstance) {
                // The trickiest part about the hotzone implementation. Who
                // owns the hotzone, and whether the placeholder is inserted
                // before or after the owner, is important here - getting it
                // right means when the block switches hotzones, the
                // placeholder moves to the correct location.
                //
                // If the blockinstance being moved is not in the same column,
                // it's relatively simple - just make the owner the current
                // blockinstance.
                //
                // If it is in the same column, we make the owner the current
                // blockinstance, until we hit the blockinstance being moved,
                // when we switch to using the next block instance.
                var element;
                if (self.currentlyMovingObject == i || afterCurrentlyMovingBlockinstance) {
                    element = nextBlockinstance;
                    afterCurrentlyMovingBlockinstance = true;
                }
                else {
                    element = i;
                    afterCurrentlyMovingBlockinstance = false;
                }
                var hotzone = self.createHotzone(element, insertSiblingNodesAfter, true);

                // We need to place a hotzone over the bottom half of the
                // current block instance, and the top half of the next
                setElementPosition(hotzone, {x: blockinstancePosition.x, y: previousHotzonePosition.y + previousHotzoneDimensions.h});
                setElementDimensions(hotzone, {
                    w: blockinstanceDimensions.w,
                    h: (nextBlockinstancePosition.y + (nextBlockinstanceDimensions.h / 2)) - (blockinstancePosition.y + (blockinstanceDimensions.h / 2))
                });
            }
            else {
                // We've reached the end of the blockinstances, we place a
                // hotzone over the end of the column.
                // FIXME: place it from the bottom half of the last block to the bottom of the page
                var hotzone = self.createHotzone(i, insertSiblingNodesAfter, true);
                var columnContainerPosition   = elementPosition(self.columnContainer);
                var columnContainerDimensions = elementDimensions(self.columnContainer);

                var footerDimensions = getElementDimensions('footer', self.bottomPane);
                var footerPosition   = getElementPosition('footer', self.bottomPane);

                setElementPosition(hotzone, {x: blockinstancePosition.x, y: previousHotzonePosition.y + previousHotzoneDimensions.h});
                setElementDimensions(hotzone, {
                    w: blockinstanceDimensions.w,
                    h: (footerPosition.y + footerDimensions.h) - (previousHotzonePosition.y + previousHotzoneDimensions.h)
                });
            }

            previousHotzone = hotzone;
        });

        forEach(getElementsByTagAndClassName('div', 'column', 'column-container'), function(i) {
            if (!getFirstElementByTagAndClassName('div', 'blockinstance', i)) {
                // Column with no blockinstances
                var columnContent = getFirstElementByTagAndClassName('div', 'column-content', i);
                var hotzone = self.createHotzone(columnContent, appendChildNodes, true);

                var footerDimensions = getElementDimensions('footer', self.bottomPane);
                var footerPosition   = getElementPosition('footer', self.bottomPane);

                setElementPosition(hotzone, {x: getElementPosition(columnContent, self.columnContainer).x, y: 0});
                setElementDimensions(hotzone, {
                    w: getElementDimensions(columnContent).w,
                    h: footerPosition.y + footerDimensions.h
                });

                // The column content may have whitespace in it. This
                // whitespace causes the box to respect line-height, which
                // pushes down where the blocks appear when dragged into the
                // column. Removing all the children nodes fixes this
                replaceChildNodes(columnContent);

                previousHotzone = hotzone;
            }
        });
    }

    /**
     * Creates a new hotzone and puts it in the DOM, ready for use
     *
     * Hotzones are used for the drag and drop stuff, to detect where the
     * currently dragged block should land
     *
     * @param node The node to use as a reference for putting the placeholder in
     * @param function The DOM function to use to insert the placeholder relative to the node
     */
    this.createHotzone = function(node, placementFunction) {
        //var hotzone = DIV({'style': 'outline: 1px solid black; position: absolute;'});
        var hotzone = DIV({'style': 'position: absolute;'});

        // Whether to place a newly dropped block type after the element on
        // whose hotzone it was dropped. True always except at the top of
        // columns.
        //
        // For some reason, using the (supposedly equal) check
        // placementFunction != insertSiblingNodesBefore cause chaos when you
        // try and drop just after the start of a column (i.e. order 2) and
        // then at the very start - it gets dropped at order 2 twice.
        var placeAfter = arguments[2];

        placementFunction = partial(placementFunction, node, self.blockPlaceholder);
        if (self.movingBlockType) {
            dropFunction = function(draggable, node, placeAfter) {
                log('after?', placeAfter);
                var whereTo = self.getBlockinstanceCoordinates(node);
                if (placeAfter) {
                    whereTo['order'] += 1;
                }
                log(whereTo);

                var pd = {
                    'id': $('viewid').value,
                    'change': 1,
                    'blocktype': getFirstElementByTagAndClassName('input', 'blocktype-radio', self.currentlyMovingObject).value
                };
                pd['action_addblocktype_column_' + whereTo['column'] + '_order_' + whereTo['order']] = true;
                sendjsonrequest(config['wwwroot'] + 'view/blocks.json.php', pd, 'POST', function(data) {
                    var div = DIV();
                    div.innerHTML = data.data;
                    var blockinstance = getFirstElementByTagAndClassName('div', 'blockinstance', div);
                    self.rewriteDeleteButton(getFirstElementByTagAndClassName('input', 'deletebutton', blockinstance));
                    self.makeBlockinstanceDraggable(blockinstance);
                    insertSiblingNodesAfter(self.blockPlaceholder, blockinstance);
                    removeElement(self.blockPlaceholder);

                });

            };
        }
        else if (placementFunction == appendChildNodes) {
            dropFunction = partial(appendChildNodes, node);
        }
        else {
            dropFunction = partial(insertSiblingNodesAfter, self.blockPlaceholder);
        }

        self.makeHotzoneDroppable(hotzone, node, placementFunction, dropFunction, placeAfter);
        appendChildNodes(self.hotzoneContainer, hotzone);
        return hotzone;
    }

    /**
     * Makes a hotzone droppable. In a separate function for scoping purposes
     */
    this.makeHotzoneDroppable = function(hotzone, node, placementFunction, dropFunction, placeAfter) {
        var counter = 5;
        new Droppable(hotzone, {
            'onhover': function() {
                if (counter++ == 5) {
                    counter = 0;
                    if (self.lastHotzone != hotzone) {
                        self.lastHotzone = hotzone;
                        // Put the placeholder div in place.
                        placementFunction();
                        showElement(self.blockPlaceholder);
                    }
                }
            },
            'ondrop': function(draggable, droppable, e) {
                e.stop();
                // Only pass through the placeAfter parameter when appropriate,
                // as the dropfunction could be a simple DOM function
                if (self.movingBlockType) {
                    dropFunction(draggable, node, placeAfter);
                }
                else {
                    dropFunction(draggable);
                }

                if (!self.movingBlockType) {
                    // Work out where to send the block to
                    var whereTo = self.getBlockinstanceCoordinates(draggable);
                    if (self.origCoordinates.column != whereTo.column || self.origCoordinates.order != whereTo.order) {
                        var pd = {'id': $('viewid').value, 'change': 1};
                        pd['action_moveblockinstance_id_' + draggable.id.substr(draggable.id.lastIndexOf('_') + 1) + '_column_' + whereTo['column'] + '_order_' + whereTo['order']] = 1;
                        sendjsonrequest(config['wwwroot'] + 'view/blocks.json.php', pd, 'POST');
                    }
                }
            }
        });
    }

    /**
     * Removes hotzones from the document.
     *
     * This is trivially implemented as removing the div that contains them all
     */
    this.destroyHotzones = function() {
        removeElement(self.hotzoneContainer);
    }

    /**
     * Find the co-ordinates of a given block instance
     *
     * This returns a {column: x, order: y} hash
     */
    this.getBlockinstanceCoordinates = function(blockinstance) {
        // Work out where to send the block to
        var columnContainer = getFirstParentByTagAndClassName(blockinstance, 'div', 'column');
        var column = columnContainer.id.substr(columnContainer.id.lastIndexOf('_') + 1);
        var order  = 0;
        forEach(getFirstElementByTagAndClassName('div', 'column-content', columnContainer).childNodes, function(i) {
            if (hasElementClass(i, 'blockinstance')) {
                order++;
            }
            if (i == blockinstance) {
                throw MochiKit.Iter.StopIteration;
            }
        });

        return {'column': column, 'order': order};
    }

    /**
     * Adds CSS rules that should apply for the javascript only version
     */
    this.addCSSRules = function() {
        var styleNode = createDOM('link', {
            'rel' : 'stylesheet',
            'type': 'text/css',
            'href': config['wwwroot'] + 'theme/views-js.css'
        });
        appendChildNodes(getFirstElementByTagAndClassName('head'), styleNode);
    }


    // Whether it is a blocktype that is being added, rather than a
    // blockinstance being moved
    this.movingBlockType = false;

    // The object that is currently being moved by drag and drop - either a
    // block instance or block type
    this.currentlyMovingObject = null;

    // The original (column, order) coordinates of the currently moving
    // blockinstance
    this.origCoordinates = null;

    // The last hotzone that was hovered over
    this.lastHotzone = null;

    // The placeholder that shows where the blockinstance will be placed when
    // it is dropped. Needs a margin the same as the blockinstances
    this.blockPlaceholder = DIV({'id': 'block-placeholder'});

    // The column container - set in self.init
    this.columnContainer = null;

    // The bottom pane - set in self.init
    this.bottomPane = null;

    // Whether the browser is IE7 - needed for some hacks
    this.isIE7 = document.all && document.documentElement && typeof(document.documentElement.style.maxHeight) != "undefined" && !window.opera;

    // Whether the browser is IE6
    this.isIE6 = !this.isIE7 && document.all && !window.opera;

    addLoadEvent(self.init);
}

viewManager = new ViewManager();
viewManager.addCSSRules();
