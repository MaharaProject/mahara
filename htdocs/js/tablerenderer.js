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

    this.init = function() {
        self.table = getElement(target);
        self.loadingMessage = DIV({'class': 'tablerenderer-loading'}, IMG({'src': config.theme['images/loading.gif'], 'alt': ''}), ' Loading...');
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

        if (typeof(self.emptycontent) != 'undefined') {
            var newelement = DIV(null,self.emptycontent);
            hideElement(newelement);
            insertSiblingNodesBefore(self.table, newelement);
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
        self.doupdate({
            'offset': ( n - 1 ) * self.limit
        });
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
                if (self.count > 0) {
                    hideElement(self.table.previousSibling);
                    self.table.style.display = '';
                }
                else {
                    self.table.style.display = 'none';
                    showElement(self.table.previousSibling);
                }
            }

            if (self.loadingMessage) {
                removeElement(self.loadingMessage);
                self.loadingMessage = null;
            }

            self.renderdata(response);

            self.table.style.display = 'table';

        }, null, true);
    };

    this.goFirstPage = function() {
        self.doupdate({'offset': 0});
    };

    this.goPrevPage = function() {
        if ( self.offset > 0 ) {
            if ( self.offset - self.limit < 0 ) {
                self.doupdate({'offset': 0});
            }
            else {
                self.doupdate({'offset': self.offset - self.limit});
            }
        }
        else {
            logWarning('Already on the first page (' + self.offset + ', ' + self.limit + ', ' + self.count + ')');
        }
    };

    this.goNextPage = function() {
        if ( self.offset + self.limit < self.count ) {
            self.doupdate({'offset': self.offset + self.limit});
        }
        else {
            logWarning('Already on the last page (' + self.offset + ', ' + self.limit + ', ' + self.count + ')');
        }
    };

    this.goLastPage = function() {
        self.doupdate({'offset': Math.floor( ( self.count - 1 ) / self.limit) * self.limit});
    };

    this.updateOnLoad = function() {
        if ( TableRendererPageLoaded ) {
            self.doupdate();
        }
        else {
            addLoadEvent(partial(self.doupdate, {}));
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
