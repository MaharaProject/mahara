function TableRenderer(target, source, columns, options) {
    // to use on the callbacks
    var self = this;
    this.source = source;
    this.columns = columns;
    this.paginate = true;
    this.statevars = ['offset','limit'];

    addLoadEvent(function() {
        self.table = target;

        self.tbody = getFirstElementByTagAndClassName('tbody', null, self.table);
        self.thead = getFirstElementByTagAndClassName('thead', null, self.table);
        self.tfoot = getFirstElementByTagAndClassName('tfoot', null, self.table);

        if (!self.thead) {
            self.thead = THEAD();
            appendChildNodes(self.table, self.thead);
        }
        if (!self.tfoot) {
            self.tfoot = TFOOT();
            appendChildNodes(self.table, self.tfoot);
        }

        if (self.paginate) {
            forEach([self.thead,self.tfoot], function(ref) {
                var nextPage = A({'href':''}, 'Next Page');
                var prevPage = A({'href':''}, 'Prev Page');

                connect(nextPage, 'onclick', function(e) { self.nextPage(); e.stop(); });
                connect(prevPage, 'onclick', function(e) { self.prevPage(); e.stop(); });

                var tr = TR(null, TD(null, prevPage, ' ', nextPage));

                // replaceChildNodes(ref, tr, ref.childNodes);
                appendChildNodes(ref, tr);
            });
        }
    });

    this.renderdata = function(data) {
        replaceChildNodes(self.tbody);

        forEach(data.data, function(row) {
            var tr = TR();
            if ( row._class ) { tr.className = row._class; }
            if ( row._id ) { tr.id = row._id; }
            
            forEach(self.columns, function (column) {
                if ( typeof(column) == 'string' ) {
                    appendChildNodes(tr, TD(null,row[column]));
                }
                else if ( typeof(column) == 'function' ) {
                    appendChildNodes(tr, column(row,data));
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
            if (typeof(request_args[key]) == 'undefined') {
                request_args[key] = self[key];
            }
        });

        self.d = loadJSONDoc(self.source, request_args);

        processingStart();

        self.d.addCallbacks(
            function (data) {
                processingStop();
                if ( data.error ) {
                    displayMessage(data.error);
                    return;
                }

                self.limit = data.limit;
                self.offset = data.offset;
                self.count = data.count;

                self.renderdata(data);
            },
            function (error) {
                processingStop();
                displayMessage('Error loading data (not valid JSON)');
            }
        );

        self.update = callLater(self.delay, partial(self.doupdate, {}));
    };

    this.prevPage = function() {
        if ( self.offset > 0 ) {
            if ( self.offset - self.limit < 0 ) {
                self.doupdate({'offset': 0});
            }
            else {
                self.doupdate({'offset': self.offset - self.limit});
            }
        }
        else {
            logDebug('Already on the first page (' + self.offset + ', ' + self.limit + ', ' + self.count + ')');
        }
    };

    this.nextPage = function() {
        if ( self.offset + self.limit < self.count ) {
            self.doupdate({'offset': self.offset + self.limit});
        }
        else {
            logDebug('Already on the last page (' + self.offset + ', ' + self.limit + ', ' + self.count + ')');
        }
    };

    this.updateOnLoad = function() {
        addLoadEvent(partial(self.doupdate, {}));
    }
}
