/**
 * Javascript for the new views interface
 *
 * Author: Nigel McNie
 */

function ViewManager() {
    var self = this;

    this.init = function () {
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
    }


    this.addColumn = function(id) {
        alert('Adding a column before current column ' + id);
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
        var numColumns = getFirstElementByTagAndClassName('div', 'column', 'bottom-pane').getAttribute('class').match(/columns([0-9]+)/)[1];

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
                getFirstElementByTagAndClassName('input', 'addcolumn', 'column_' + newID).setAttribute('name', 'action_add_column_before_' + oldID);
                getFirstElementByTagAndClassName('input', 'removecolumn', 'column_' + newID).setAttribute('name', 'action_remove_column_' + newID);
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

        // If there used to be two columns (and therefore now there is one), remove the only 'remove column' button left, people can't remove the last column
        if (numColumns == 2) {
            removeElement(getFirstElementByTagAndClassName('div', 'remove-column', 'column_1'));
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
                sendjsonrequest('viewrework.json.php', {'view': $('viewid').value, 'action': 'delete_blockinstance', 'data': e.src().getAttribute('name')}, 'POST', function(data) {
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
     */
    this.rewriteAddColumnButtons = function() {
        forEach(getElementsByTagAndClassName('input', 'addcolumn', 'bottom-pane'), function(i) {
            connect(i, 'onclick', function(e) {
                var name = e.src().getAttribute('name');
                var id   = parseInt(name.substr(-1));
                sendjsonrequest('viewrework.json.php', {'view': $('viewid').value, 'action': 'add_column', 'column': id}, 'POST', function(data) {
                    if (!data.error) {
                        self.addColumn(id);
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
     */
    this.rewriteRemoveColumnButtons = function() {
        forEach(getElementsByTagAndClassName('input', 'removecolumn', 'bottom-pane'), function(i) {
            connect(i, 'onclick', function(e) {
                var name = e.src().getAttribute('name');
                var id   = parseInt(name.substr(-1));
                sendjsonrequest('viewrework.json.php', {'view': $('viewid').value, 'action': 'remove_column', 'column': id}, 'POST', function(data) {
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

    addLoadEvent(self.init);
}

viewManager = new ViewManager();
