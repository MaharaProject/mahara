TableRendererPageLoaded = false;
addLoadEvent(function() { TableRendererPageLoaded = true });

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
    this.emptycontent = false;  // Something to display when no results are found
    this.rowfunction = function(rowdata, rownumber, data) { return TR(); }

    this.init = function() {
        self.table = getElement(target);

        self.tbody = getFirstElementByTagAndClassName('tbody', null, self.table);
        self.thead = getFirstElementByTagAndClassName('thead', null, self.table);
        self.tfoot = getFirstElementByTagAndClassName('tfoot', null, self.table);

        if (!self.thead) {
            self.thead = THEAD();
            insertSiblingNodesBefore(self.table.firstChild || null, self.thead);
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
            var page_state = new Object();
            self.page_state = page_state;
            page_state.firstButtons = new Array();
            page_state.prevButtons = new Array();
            page_state.nextButtons = new Array();
            page_state.lastButtons = new Array();
            self.linkspan = self.columns.length > 0 ? self.columns.length : 1;

            forEach([self.thead,self.tfoot], function(ref) {
                var firstPage = A({'href':''}, get_string('firstpage'));
                var prevPage  = A({'href':''}, get_string('prevpage'));
                var nextPage  = A({'href':''}, get_string('nextpage'));
                var lastPage  = A({'href':''}, get_string('lastpage'));
                firstPage.style.visibility = 'hidden';
                prevPage.style.visibility = 'hidden';
                nextPage.style.visibility = 'hidden';
                lastPage.style.visibility = 'hidden';
                page_state.nextButtons.push(nextPage);
                page_state.prevButtons.push(prevPage);
                page_state.firstButtons.push(firstPage);
                page_state.lastButtons.push(lastPage);

                connect(firstPage, 'onclick', function(e) { self.goFirstPage(); e.stop(); });
                connect(prevPage, 'onclick', function(e) { self.goPrevPage(); e.stop(); });
                connect(nextPage, 'onclick', function(e) { self.goNextPage(); e.stop(); });
                connect(lastPage, 'onclick', function(e) { self.goLastPage(); e.stop(); });

                var elements = new Array();

                if (self.paginate_firstlast) {
                    elements.push(firstPage);
                    elements.push(' ');
                }
                if (self.paginate_simple) {
                    elements.push(prevPage);
                    elements.push(' ');
                    elements.push(nextPage);
                }
                if (self.paginate_firstlast) {
                    elements.push(' ');
                    elements.push(lastPage);
                }

                var tr = TR(null, TD({'colspan':self.linkspan}, DIV({'style': 'width: 100%; margin: auto;'}, elements)));

                // replaceChildNodes(ref, tr, ref.childNodes);
                appendChildNodes(ref, tr);
            });
        }

        if (self.emptycontent) {
            var newelement = DIV(null,self.emptycontent);
            hideElement(newelement);
            insertSiblingNodesBefore(self.table, newelement);
        }
    };

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

    this.updatePagination = function() {
        if (self.onFirstPage()) {
            forEach(self.page_state.firstButtons, function(btn) { btn.style.visibility = 'hidden'; });
            forEach(self.page_state.prevButtons, function(btn) { btn.style.visibility = 'hidden'; });
        }
        else {
            forEach(self.page_state.firstButtons, function(btn) { btn.style.visibility = ''; });
            forEach(self.page_state.prevButtons, function(btn) { btn.style.visibility = ''; });
        }
        if (self.onLastPage()) {
            forEach(self.page_state.nextButtons, function(btn) { btn.style.visibility = 'hidden'; });
            forEach(self.page_state.lastButtons, function(btn) { btn.style.visibility = 'hidden'; });
        }
        else {
            forEach(self.page_state.nextButtons, function(btn) { btn.style.visibility = ''; });
            forEach(self.page_state.lastButtons, function(btn) { btn.style.visibility = ''; });
        }
    };

    this.renderdata = function(data) {
        replaceChildNodes(self.tbody);
        var rownumber = 1;

        forEach(data.data, function(row) {
            var tr = self.rowfunction(row, rownumber++, data);
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

    this.doupdate = function(request_args) {
        if (!request_args) {
            request_args = {};
        }

        forEach(self.statevars, function(key) {
            if (typeof(request_args[key]) == 'undefined' && typeof(self[key]) != 'undefined') {
                request_args[key] = self[key];
            }
        });

        var req = getXMLHttpRequest();
        req.open('post', self.source);
        req.setRequestHeader('Content-type','application/x-www-form-urlencoded'); 
        self.d = sendXMLHttpRequest(req,queryString(request_args));

        processingStart();

        self.d.addCallbacks(
            function (result) {
                var data = evalJSONRequest(result);
                processingStop();
                if ( data.error ) {
                    displayMessage(data.message || data.error);
                    return;
                }

                self.limit = data.limit;
                self.offset = data.offset;
                self.count = data.count;

                if (self.paginate) {
                    self.updatePagination();
                }

                if (self.emptycontent) {
                    if (self.count > 0) {
                        hideElement(self.table.previousSibling);
                        showElement(self.table);
                    }
                    else {
                        hideElement(self.table);
                        showElement(self.table.previousSibling);
                    }
                }
                self.renderdata(data);
            },
            function (error) {
                processingStop();
                displayMessage('Error loading data (not valid JSON)');
            }
        );
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

    if ( TableRendererPageLoaded ) {
        this.init();
    }
    else {
        addLoadEvent(this.init);
    }
}
