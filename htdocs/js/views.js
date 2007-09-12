/**
 * Javascript for the new views interface
 *
 * Author: Nigel McNie
 */

function ViewManager() {
    var self = this;

    this.init = function () {
        // Set up the column container reference, and make the container the
        // base for positioned elements inside it
        self.columnContainer = $('column-container');
        makePositioned(self.columnContainer);

        // Hide 'new block here' buttons
        forEach(getElementsByTagAndClassName('div', 'add-button', 'bottom-pane'), function(i) {
            removeElement(i);
        });

        // Hide controls in each block instance that are not needed
        forEach(getElementsByTagAndClassName('input', 'movebutton', 'bottom-pane'), function(i) {
            removeElement(i);
        });

        // Remove radio buttons for moving block types into place
        forEach(getElementsByTagAndClassName('input', 'blocktype-radio', 'top-pane'), function(i) {
            removeElement(i);
        });

        // Rewrite the links in the category select list to be ajax
        self.rewriteCategorySelectList();

        // Rewrite the delete buttons to be ajax
        self.rewriteDeleteButtons();

        // Rewrite the 'add column' buttons to be ajax
        self.rewriteAddColumnButtons();

        // Rewrite the 'remove column' buttons to be ajax
        self.rewriteRemoveColumnButtons();

        // Make the block instances draggable
        self.makeBlockInstancesDraggable();
    }

    /**
     * Adds a column to the view
     */
    this.addColumn = function(id, data) {
        // Get the existing number of columns
        var numColumns = parseInt(getFirstElementByTagAndClassName('div', 'column', 'bottom-pane').getAttribute('class').match(/columns([0-9]+)/)[1]);

        // Here we are doing two things:
        // 1) The existing columns that are higher than the one being inserted need to be renumbered
        // 2) All columns need their 'columnsN' class renumbered one higher
        log('addColumn: numColumns=' + numColumns);
        for (var oldID = numColumns; oldID >= 1; oldID--) {
            var column = $('column_' + oldID);
            log(column);
            var newID = oldID + 1;
            if (oldID >= id) {
                $('column_' + oldID).setAttribute('id', 'column_' + newID);

                // Renumber the add/remove column buttons
                getFirstElementByTagAndClassName('input', 'addcolumn', 'column_' + newID).setAttribute('name', 'action_addcolumn_before_' + (newID + 1));
                getFirstElementByTagAndClassName('input', 'removecolumn', 'column_' + newID).setAttribute('name', 'action_removecolumn_id_' + newID);
            }
            removeElementClass(column, 'columns' + numColumns);
            addElementClass(column, 'columns' + (numColumns + 1));
        }

        // If the column being added is the very first one, the 'left' add column button needs to be removed
        if (id == 1) {
            removeElement(getFirstElementByTagAndClassName('div', 'add-column-left', 'column_2'));
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

        // VERY TEMPORARY
        // Currently with our hard coded data, the adding of a column doesn't
        // really happen and so the new column is built thinking there are only
        // the same number of columns in total as there were before adding a
        // column. This munges the column class for us
        removeElementClass('column_' + id, 'columns' + numColumns);
        addElementClass('column_' + id, 'columns' + (numColumns + 1));

        // Wire up the new column buttons to be AJAX
        self.rewriteAddColumnButtons('column_' + id);
        self.rewriteRemoveColumnButtons('column_' + id);

        // Ensure the enabled/disabled state of the add/remove buttons is correct
        self.checkColumnButtonDisabledState();
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
        var numColumns = parseInt(getFirstElementByTagAndClassName('div', 'column', 'bottom-pane').getAttribute('class').match(/columns([0-9]+)/)[1]);

        forEach(getElementsByTagAndClassName('div', 'columns' + numColumns, 'bottom-pane'), function(i) {
            removeElementClass(i, 'columns' + numColumns);
            addElementClass(i, 'columns' + (numColumns - 1));
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

        // Ensure the enabled/disabled state of the add/remove buttons is correct
        self.checkColumnButtonDisabledState();
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
                sendjsonrequest('viewrework.json.php', {'view': $('viewid').value, 'action': 'blocktype_list', 'category': queryString['category']}, 'POST', function(data) {
                    if (!data.error) {
                        $('blocktype-list').innerHTML = data.data;
                    }
                });
                e.stop();
            });
        });
    }

    /**
     * Rewrites the blockinstance delete buttons to be AJAX
     */
    this.rewriteDeleteButtons = function() {
        forEach(getElementsByTagAndClassName('input', 'deletebutton', 'bottom-pane'), function(i) {
            connect(i, 'onclick', function(e) {
                var pd = {'view': $('viewid').value, 'change': 1};
                pd['action_' + e.src().getAttribute('name')] = 1;
                sendjsonrequest('viewrework.json.php', pd, 'POST', function(data) {
                    if (!data.error) {
                        // TODO: not happy with using .parentNode, it's fragile
                        removeElement(i.parentNode.parentNode);
                    }
                    else {
                        // ?
                    }
                });
                e.stop();
            });
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
            parentNode = 'bottom-pane';
        }

        forEach(getElementsByTagAndClassName('input', 'addcolumn', parentNode), function(i) {
            connect(i, 'onclick', function(e) {
                var name = e.src().getAttribute('name');
                var id   = parseInt(name.substr(-1));
                var pd   = {'view': $('viewid').value, 'change': 1}
                pd['action_addcolumn_before_' + id] = 1;
                sendjsonrequest('viewrework.json.php', pd, 'POST', function(data) {
                    if (!data.error) {
                        self.addColumn(id, data);
                    }
                    else {
                        // ?
                    }
                });
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
            parentNode = 'bottom-pane';
        }

        forEach(getElementsByTagAndClassName('input', 'removecolumn', parentNode), function(i) {
            connect(i, 'onclick', function(e) {
                var name = e.src().getAttribute('name');
                var id   = parseInt(name.substr(-1));
                var pd   = {'view': $('viewid').value, 'change': 1}
                pd['action_removecolumn_column_' + id] = 1;
                sendjsonrequest('viewrework.json.php', pd, 'POST', function(data) {
                    if (!data.error) {
                        self.removeColumn(id);
                    }
                    else {
                        // ?
                    }
                });
                e.stop();
            });
        });
    }
    
    /**
     * Disables the 'add column' buttons
     */
    this.checkColumnButtonDisabledState = function() {
        // Get the existing number of columns
        var numColumns = parseInt(getFirstElementByTagAndClassName('div', 'column', 'bottom-pane').getAttribute('class').match(/columns([0-9]+)/)[1]);

        var state = (numColumns == 5);
        forEach(getElementsByTagAndClassName('input', 'addcolumn', 'bottom-pane'), function(i) {
            if (state) {
                setNodeAttribute(i, 'disabled', 'disabled');
            }
            else {
                removeNodeAttribute(i, 'disabled');
            }
        });

        var state = (numColumns == 1);
        forEach(getElementsByTagAndClassName('input', 'removecolumn', 'bottom-pane'), function(i) {
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
    this.makeBlockInstancesDraggable = function() {
        forEach(getElementsByTagAndClassName('div', 'blockinstance', 'bottom-pane'), function(i) {
            new Draggable(i, {
                'handle': 'blockinstance-header',
                'starteffect': function () {
                    self.currentlyMovingBlockinstance = i;
                    self.createHotzones();

                    // Set the positioning of the blockinstance to 'absolute',
                    // so that it is taken out of the document flow (so the
                    // other blocks can collapse into its space if necessary if
                    // it's dragged around). This changes how the width is
                    // calculated, as the width is 'auto' by default, so we
                    // explicitly set it to have the width it needs.
                    var dimensions = elementDimensions(i);
                    setElementDimensions(i, dimensions);
                    i.style.position = 'absolute';

                    // Resize the placeholder div
                    // NOTE: negative offsets to account for borders. These might be removed
                    setElementDimensions(self.blockPlaceholder, {w: dimensions.w - 4, h: dimensions.h - 2});
                },
                'revert': true,
                'reverteffect': function (innerelement, top_offset, left_offset) {
                    self.destroyHotzones();

                    // We don't need the block placeholder anymore
                    removeElement(self.blockPlaceholder);

                    // Revert the 'absolute' positioning of the blockinstance being moved
                    self.currentlyMovingBlockinstance.style.top = 0;
                    self.currentlyMovingBlockinstance.style.left = 0;
                    self.currentlyMovingBlockinstance.style.position = 'relative';
                    self.currentlyMovingBlockinstance.style.width = 'auto';
                    self.currentlyMovingBlockinstance.style.height = 'auto';

                    // No longer is there a 'last hotzone' that was being dragged over
                    self.lastHotzone = null;

                    // Sadly we have to return an effect, because this requires
                    // something cancellable. Would be good to return nothing
                    return new MochiKit.Visual.Move(innerelement,
                        {x: 0, y: 0, duration: 0});

                },
                'snap': 5,
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
     *
     * Currently, drag and drop works, but the server is not informed. Also,
     * you cannot add blockinstances to empty columns.
     *
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
        forEach(getElementsByTagAndClassName('div', 'blockinstance', 'bottom-pane'), function(i) {
            var blockinstancePosition   = elementPosition(i, self.columnContainer);
            var blockinstanceDimensions = elementDimensions(i);
            // NOTE: added for the border
            blockinstanceDimensions.w += 4;

            // Work out whether the given blockinstance is at the top of the column
            if (getFirstElementByTagAndClassName('div', 'blockinstance', getFirstParentByTagAndClassName(i, 'div', 'column-content')) == i) {
                // Put a hotzone across the top half of the blockinstance
                var hotzone = self.createHotzone(i);
                setElementPosition(hotzone, blockinstancePosition);
                setElementDimensions(hotzone, {w: blockinstanceDimensions.w, h: blockinstanceDimensions.h / 2});

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
                if (self.currentlyMovingBlockinstance == i || afterCurrentlyMovingBlockinstance) {
                    element = nextBlockinstance;
                    afterCurrentlyMovingBlockinstance = true;
                }
                else {
                    element = i;
                    afterCurrentlyMovingBlockinstance = false;
                }
                var hotzone = self.createHotzone(element, true);

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
                var hotzone = self.createHotzone(i, true);
                var columnContainerPosition   = elementPosition(self.columnContainer);
                var columnContainerDimensions = elementDimensions(self.columnContainer);

                setElementPosition(hotzone, {x: blockinstancePosition.x, y: previousHotzonePosition.y + previousHotzoneDimensions.h});
                setElementDimensions(hotzone, {
                    w: blockinstanceDimensions.w,
                    h: columnContainerDimensions.h - (previousHotzonePosition.y + previousHotzoneDimensions.h)
                });
            }

            previousHotzone = hotzone;
        });
    }

    /**
     * Creates a new hotzone and puts it in the DOM, ready for use
     *
     * Hotzones are used for the drag and drop stuff, to detect where the
     * currently dragged block should land
     */
    this.createHotzone = function(blockinstance) {
        //var hotzone = DIV({'style': 'outline: 1px solid black; position: absolute;'});
        var hotzone = DIV({'style': 'position: absolute;'});

        var putPlaceholderAfter = false;
        if (typeof(arguments[1]) != 'undefined' && arguments[1] == true) {
            putPlaceholderAfter = true;
        }

        new Droppable(hotzone, {
            //'accept': ['blockinstance'],
            'onhover': function() {
                if (self.lastHotzone != hotzone) {
                    //log('hovering over hotzone for blockinstance', blockinstance.id);
                    //log('put the placeholder after?', putPlaceholderAfter);
                    //log((putPlaceholderAfter) ? 'after' : 'before', blockinstance.id);
                    self.lastHotzone = hotzone;

                    // Put the placeholder div in place.
                    if (putPlaceholderAfter) {
                        insertSiblingNodesAfter(blockinstance, self.blockPlaceholder);
                    }
                    else {
                        insertSiblingNodesBefore(blockinstance, self.blockPlaceholder);
                    }
                }
            },
            'ondrop': function(draggable, droppable, e) {
                e.stop();
                insertSiblingNodesAfter(self.blockPlaceholder, draggable);
                // TODO: ajax request to server, informing of the drop
            }
        });

        appendChildNodes(self.hotzoneContainer, hotzone);
        return hotzone;
    }

    /**
     * Removes hotzones from the document.
     *
     * This is trivially implemented as removing the div that contains them all
     */
    this.destroyHotzones = function() {
        removeElement(self.hotzoneContainer);
    }


    // The block instance that is currently being moved by drag and drop
    this.currentlyMovingBlockinstance = null;

    // The last hotzone that was hovered over
    this.lastHotzone = null;

    // The placeholder that shows where the blockinstance will be placed when
    // it is dropped. Needs a margin the same as the blockinstances
    this.blockPlaceholder = DIV({'style': 'border: 3px dashed #bbb; margin-top: 1em;'});

    // The column container - set in self.init
    this.columnContainer = null;

    addLoadEvent(self.init);
}

viewManager = new ViewManager();
