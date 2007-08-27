/**
 * Javascript for the new views interface
 *
 * Author: Nigel McNie
 */

function ViewManager() {
    var self = this;

    this.init = function () {
        // Hide 'new block here' buttons
        forEach(getElementsByTagAndClassName('input', 'newblockhere', 'bottom-pane'), function(i) {
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
        forEach(getElementsByTagAndClassName('a', null, 'category-list'), function(i) {
            connect(i, 'onclick', function(e) {
                var queryString = parseQueryString(i.href.substr(i.href.indexOf('?')));
                removeElementClass(getFirstElementByTagAndClassName('li', 'current', 'category-list'), 'current');
                addElementClass(i.parentNode, 'current');
                sendjsonrequest('viewrework.json.php', {'action': 'blocktype_list', 'category': queryString['category']}, 'POST', function(data) {
                    if (!data.error) {
                        $('blocktype-list').innerHTML = data.data;
                    }
                });
                e.stop();
            });
        });

        // Rewrite the delete buttons to be ajax
        forEach(getElementsByTagAndClassName('input', 'deletebutton', 'bottom-pane'), function(i) {
            connect(i, 'onclick', function(e) {
                sendjsonrequest('viewrework.json.php', {'action': 'delete_blockinstance', 'data': e.src().getAttribute('name')}, 'POST', function(data) {
                    if (!data.error) {
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


    addLoadEvent(self.init);
}

viewManager = new ViewManager();
