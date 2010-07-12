/**
 * Javascript based display of tabular data.
 * @source: http://gitorious.org/mahara/mahara
 *
 * NOTE: use of this is deprecated in Mahara - it's not accessible. See
 * paginator.js and talk to the developers for more information
 *
 * @licstart
 * Copyright (C) 2006-2010  Catalyst IT Ltd
 *
 * The JavaScript code in this page is free software: you can
 * redistribute it and/or modify it under the terms of the GNU
 * General Public License (GNU GPL) as published by the Free Software
 * Foundation, either version 3 of the License, or (at your option)
 * any later version.  The code is distributed WITHOUT ANY WARRANTY;
 * without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU GPL for more details.
 *
 * As additional permission under GNU GPL version 3 section 7, you
 * may distribute non-source (e.g., minimized or compacted) forms of
 * that code without the copy of the GNU GPL normally required by
 * section 4, provided you include this license notice and a URL
 * through which recipients can access the Corresponding Source.
 * @licend
 */

TableRendererPageLoaded = false;
addLoadEvent(function() { TableRendererPageLoaded = true });
document.write('<script type="text/javascript" src="' + config.wwwroot + 'js/Pager.js"></script>');

function TableRenderer(target, source, columns, options) {
    // to use on the callbacks
    var self = this;
    this.source = source;
    this.columns = columns;
    this.offset = 0;
    this.limit = 10;
    this.paginate = true;
    this.paginate_simple = true;
    this.paginate_firstlast = true;
    this.statevars = ['offset','limit'];
    this.emptycontent = undefined;  // Something to display when no results are found
    this.rowfunction = function(rowdata, rownumber, data) { return TR({'class': 'r' + (rownumber % 2)}); }
    this.updatecallback = function () {};
    this.postupdatecallback = function () {};
    this.updateOnLoadFlag = false;
    this.lastArgs = {};

    this.init = function() {
        self.table = getElement(target);
        self.loadingMessage = DIV({'class': 'tablerenderer-loading'}, IMG({'src': config.theme['images/loading.gif'], 'alt': ''}), ' ', get_string('loading'));
        insertSiblingNodesAfter(self.table, self.loadingMessage);

        self.tbody = getFirstElementByTagAndClassName('tbody', null, self.table);
        self.thead = getFirstElementByTagAndClassName('thead', null, self.table);
        self.tfoot = getFirstElementByTagAndClassName('tfoot', null, self.table);

        if (!self.thead) {
            self.thead = THEAD();
            if (self.table.firstChild) {
                insertSiblingNodesBefore(self.table.firstChild, self.thead);
            }
            else {
                appendChildNodes(self.table, self.thead);
            }
        }
        if (!self.tbody) {
            self.tbody = TBODY();
            appendChildNodes(self.table, self.tbody);
        }
        if (!self.tfoot) {
            self.tfoot = TFOOT();
            appendChildNodes(self.table, self.tfoot);
        }

        if (self.paginate) {
            self.linkspan = self.columns.length > 0 ? self.columns.length : 1;
            self.assertPager(self.offset, self.limit, self.count);
        }

        if (TableRendererPageLoaded) {
            if (typeof(self.emptycontent) != 'undefined') {
                self.emptycontent = DIV(null,self.emptycontent);
                insertSiblingNodesBefore(self.table, self.emptycontent);
            }
            if (!self.updateOnLoadFlag) {
                if (self.loadingMessage) {
                    removeElement(self.loadingMessage);
                    self.loadingMessage = null;
                }
            }
        }
    };

    this.assertPager = function (offset, limit, count) {
        if (!count) {
            return;
        }
        if(!self.pager || self.pager.options.lastPage != Math.floor( (count-1) / limit ) + 1 ) {
            if (self.pager) {
                if (self.headRow) {
                    removeElement(self.headRow);
                }
                if (self.footRow) {
                    removeElement(self.footRow);
                }
                self.pager.removeAllInstances();
            }
            self.pager = new Pager(count, limit,
                update(
                    null,
                    self.defaultPagerOptions,
                    self.pagerOptions,
                    { 'currentPage': Math.floor(offset / limit) + 1 }
                )
            );

            if (self.pager.options.lastPage == 1) {
                self.headRow = null;
                self.footRow = null;
                return;
            }

            self.headRow = TR(null, TD({'colspan': self.linkspan }, self.pager.newDisplayInstance()));
            self.footRow = TR(null, TD({'colspan': self.linkspan }, self.pager.newDisplayInstance()));

            if ( self.thead.firstChild ) {
                insertSiblingNodesBefore(self.thead.firstChild, self.headRow);
            }
            else {
                appendChildNodes(self.thead, self.headRow);
            }
            appendChildNodes(self.tfoot, self.footRow);
        }
    }

    this.pageChange = function(n) {
        self.lastArgs.offset = ( n - 1 ) * self.limit;
        self.doupdate(self.lastArgs);
    }

    this.onFirstPage = function () {
        if (self.offset == 0) {
            return true;
        }

        return false;
    }
    this.onLastPage = function () {
        // logDebug('offset=' + self.offset + ', limit=' + self.limit + ', count=' + self.count);
        if ( self.offset + self.limit >= self.count ) {
            return true;
        }

        return false;
    }

    this.renderdata = function(data) {
        replaceChildNodes(self.tbody);
        var rownumber = 0;

        if (data.count > 0) {
            forEach(data.data, function(row) {
                rownumber++;

                row._rownumber = rownumber;
                row._last = rownumber == data.data.length;
                var tr = self.rowfunction(row, rownumber, data);
                if ( row._class ) { tr.className = row._class; }
                if ( row._id ) { tr.id = row._id; }
                
                forEach(self.columns, function (column) {
                    if ( typeof(column) == 'string' ) {
                        appendChildNodes(tr, TD(null,row[column]));
                    }
                    else if ( typeof(column) == 'function' ) {
                        appendChildNodes(tr, column(row,data));
                    }
                    else if ( typeof(column) == 'undefined' ) {
                        return;
                    }
                    else {
                        logError("Can't deal with column def of type: " + typeof(column));
                    }
                });

                appendChildNodes(self.tbody, tr);
            });
        }
    }

    this.doupdate = function(request_args) {
        if (!request_args) {
            request_args = {};
        }
        self.lastArgs = request_args;

        forEach(self.statevars, function(key) {
            if (typeof(request_args[key]) == 'undefined' && typeof(self[key]) != 'undefined') {
                request_args[key] = self[key];
            }
        });

        sendjsonrequest(self.source, request_args, 'POST', function (response) {
            self.limit = response.limit;
            self.offset = response.offset;
            self.count = response.count;

            try {
                self.updatecallback(response);
            }
            catch (e) {
                logError('tablerenderer call updatecallback(', response, ') failed.');
            }

            if (self.paginate) {
                if (typeof(self.assertPager) == 'function') {
                    self.assertPager(self.offset, self.limit, self.count);
                }
            }

            if (typeof(self.emptycontent) != 'undefined') {
                // Make sure the emptycontent is in a div
                if (self.emptycontent.nodeName != 'DIV') {
                    self.emptycontent = DIV(null, self.emptycontent);
                    insertSiblingNodesBefore(self.table, self.emptycontent);
                }

                if (self.count > 0) {
                    addElementClass(self.emptycontent, 'hidden');
                    self.table.style.display = '';
                }
                else {
                    self.table.style.display = 'none';
                    removeElementClass(self.emptycontent, 'hidden');
                }
            }

            if (self.loadingMessage) {
                removeElement(self.loadingMessage);
                self.loadingMessage = null;
            }

            self.renderdata(response);

            removeElementClass(self.table, 'hidden');

            try {
                self.postupdatecallback(response);
            }
            catch (e) {
                logError('tablerenderer call postupdatecallback(', response, ') failed.');
            }

        }, null, true);
    };

    this.goFirstPage = function() {
        self.lastArgs.offset = 0;
        self.doupdate(self.lastArgs);
    };

    this.goPrevPage = function() {
        if ( self.offset > 0 ) {
            if ( self.offset - self.limit < 0 ) {
                self.lastArgs.offset = 0;
                self.doupdate(self.lastArgs);
            }
            else {
                self.lastArgs.offset = self.offset - self.limit;
                self.doupdate(self.lastArgs);
            }
        }
        else {
            logWarning('Already on the first page (' + self.offset + ', ' + self.limit + ', ' + self.count + ')');
        }
    };

    this.goNextPage = function() {
        if ( self.offset + self.limit < self.count ) {
            self.lastArgs.offset = self.offset + self.limit;
            self.doupdate(self.lastArgs);
        }
        else {
            logWarning('Already on the last page (' + self.offset + ', ' + self.limit + ', ' + self.count + ')');
        }
    };

    this.goLastPage = function() {
        self.lastArgs.offset = Math.floor( ( self.count - 1 ) / self.limit) * self.limit;
        self.doupdate(self.lastArgs);
    };

    this.updateOnLoad = function(request_args) {
        self.updateOnLoadFlag = true;
        if ( TableRendererPageLoaded ) {
            self.doupdate();
        }
        else {
            addLoadEvent(partial(self.doupdate, request_args));
        }
    }

    this.defaultPagerOptions = {
        'pageChangeCallback': self.pageChange,
        'previousPageString': get_string('prevpage'),
        'nextPageString': get_string('nextpage'),
        'lastPageString': get_string('lastpage'),
        'firstPageString': get_string('firstpage')
    };
    this.pagerOptions = {};

    if ( TableRendererPageLoaded ) {
        this.init();
    }
    else {
        addLoadEvent(this.init);
    }
}
