/**
 * collapsabletree.js
 *
 * Provides an API for building a collapsable tree structure
 *
 * Author: Nigel McNie
 */
function CollapsableTree(data, source) {
    var self = this;
    this.data         = data;
    this.source       = source;
    this.expandIcon   = '';
    this.collapseIcon = '';
    this.statevars    = new Array();
    this.formatcallback = null;

    // Returns the rendered tree
    this.render = function() {
        return this.convertDataToDOM(self.data);
    }

    // Sets the icons used to toggle expand/collapse status
    this.setToggleIcons = function(expandIcon, collapseIcon) {
        self.expandIcon   = expandIcon;
        self.collapseIcon = collapseIcon;
    }

    this.setFormatCallback = function(callback) {
        if (typeof(callback) != 'function') {
            alert('Callback must be a function');
        }
        self.formatcallback = callback;
    }

    // Expands a contracted node of the tree
    this.expand = function(p) {
        if (!p.loaded) {
            p.loaded = true;
            processingStart();

            var request_args = {};
            forEach(self.statevars, function(key) {
                if (p.getAttribute(key)) {
                    request_args[key] = p.getAttribute(key);
                }
            });

            var req = getXMLHttpRequest();
            req.open('post', self.source);
            req.setRequestHeader('Content-type','application/x-www-form-urlencoded'); 
            var d = sendXMLHttpRequest(req,queryString(request_args));
            d.addCallbacks(function (response) {
                var data = evalJSONRequest(response);
                
                if (!data.error) {
                    if (data.message) {
                        // Add the new children into the list item that was expanded
                        var ul = self.convertDataToDOM(data.message);
                        appendChildNodes(p, ul);
                        p.child = ul;
                    }
                    else {
                        var oops = P(null, 'omgwtfnothinghere');
                        appendChildNodes(p, oops);
                        p.child = oops;
                    }
                    // Replace the 'expand' link with a 'collapse' one 
                    replaceChildNodes(p.id + '_toggle', self.getCollapseLink(p));
                }
                else {
                    alert('did not get data :(');
                    // @todo insert sad face or something
                }

                processingStop();
            }, function () {
                alert('no data for you!');
                processingStop();
            });
        }
        else {
            // Replace the expand link with a collapse one and make all child UL's (all one of them) visible
            // Might not be available if the user clicked 'expand' twice before the document had finished loading
            if (p.child) {
                replaceChildNodes(p.id + '_toggle', self.getCollapseLink(p));
                p.child.style.display = 'block';
            }
        }
    }

    // Collapses a UL tree and replaces the "collapse" button with
    // an "expand" one 
    this.collapse = function(p) {
        replaceChildNodes(p.id + '_toggle', self.getExpandLink(p));
        p.child.style.display = 'none';
    }
    
    // Returns DOM representation of an 'expand tree' link
    this.getExpandLink = function(p) {
        var contents = '+';
        if (self.expandIcon) {
            contents = IMG({
                'src': self.expandIcon,
                'alt': '+',
                'border': 0
            });
        }
        var a = A({'href': ''}, contents);
        connect(a, 'onclick', function(e) { self.expand(p); e.stop(); });
        return a;
    }
    
    // Returns DOM representation of a 'collapse tree' link
    this.getCollapseLink = function(p) {
        var contents = '-';
        if (self.collapseIcon) {
            contents = IMG({
                'src': self.collapseIcon,
                'alt': '-',
                'border': 0
            });
        }
        var a = A({'href': ''}, contents);
        connect(a, 'onclick', function(e) { self.collapse(p); e.stop(); });
        return a;
    }
    
    // Given data in the correct form, convert it to a UL
    // for inserting into the DOM tree
    this.convertDataToDOM = function(data) {
        var ul = UL(null);
        var items = new Array();

        // Customised row support
        forEach(data, function(i) {
            var item;
            if (self.formatcallback) {
                item = self.formatcallback(i, self);
            }
            else {
                item = LI({'id': i.id});
                if (i.container) {
                    var toggleLink = SPAN({'id': i.id + '_toggle'}, self.getExpandLink(item));
                    appendChildNodes(item, toggleLink, ' ');
                }
                var title = SPAN(null, i.text);
                appendChildNodes(item, title);
                forEach(self.statevars, function(j) {
                    if (typeof(data[j]) != 'undefined') {
                        item.setAttribute(j, i[j]);
                    }
                });
            }

            items.push(item);
        });

        appendChildNodes(ul, items);
        return ul;
    }
}
