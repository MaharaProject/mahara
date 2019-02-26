/**
 * Javascript based display of tabular data.
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

TableRendererPageLoaded = false;
jQuery(function() { TableRendererPageLoaded = true; });

var TableRenderer = (function($) {
return function (target, source, columns, options) {
    // to use on the callbacks
    var self = this;
    this.source = source;
    this.columns = columns;
    this.options = options;
    this.offset = 0;
    this.limit = 10;
    this.statevars = ['offset','limit'];
    this.emptycontent = undefined;  // Something to display when no results are found
    this.rowfunction = function(rowdata, rownumber, data) { return $('<tr>', {'class': 'r' + (rownumber % 2)}); };
    this.updatecallback = function () {};
    this.postupdatecallback = function () {};
    this.updateOnLoadFlag = false;
    this.lastArgs = {};

    this.init = function() {
        self.table = $('#' + target);
        self.loadingMessage = $('<div>', {'class': 'loading-box'}).append(
                $('<div>', {'class':'loading-inner'}).append(
                    $('<span>', {'class':'icon-spinner icon-pulse icon icon-lg'}),
                    $('<span>',{'class':'loading-message'}).append(get_string('loading'))
                )
            );
        self.loadingMessage.insertAfter(self.table);

        self.tbody = self.table.find('tbody').first();
        self.thead = self.table.find('thead').first();
        self.tfoot = self.table.find('tfoot').first();

        if (!self.thead.length) {
            self.thead = $('<thead>');
            if (self.table[0].firstChild) {
                self.table.prepend(self.thead);
            }
            else {
                self.table.append(self.thead);
            }
        }
        if (!self.tbody.length) {
            self.tbody = $('<tbody>');
            self.table.append(self.tbody);
        }
        if (!self.tfoot.length) {
          self.tfoot = $('<tfoot>');
          self.table.append(self.tfoot);
        }

        if (TableRendererPageLoaded) {
            if (typeof(self.emptycontent) != 'undefined') {
                self.emptycontent = $('<div>').append(self.emptycontent);
                self.emptycontent.insertBefore(self.table);
            }
            if (!self.updateOnLoadFlag) {
                if (self.loadingMessage) {
                    self.loadingMessage.remove();
                    self.loadingMessage = null;
                }
            }
        }
    };

    this.renderdata = function(data, options) {
        self.tbody.empty();
        var rownumber = 0;

        if (data.count > 0) {
            $.each(data.data, function(id, row) {
                rownumber++;

                row._rownumber = rownumber;
                row._last = rownumber == data.data.length;
                var tr = self.rowfunction(row, rownumber, data);
                if ( row._class ) { tr.className = row._class; }
                if ( row._id ) { tr.id = row._id; }

                $.each(self.columns, function (i, column) {
                    if ( typeof(column) == 'string' ) {
                      tr.append($('<td>').append(row[column]));
                    }
                    else if ( typeof(column) == 'function' ) {
                        var columncontent = column(row,data);
                        if (columncontent.nodeName == 'TD') {
                            tr.append(columncontent);
                        }
                        else {
                            tr.append($('<td>').append(columncontent));
                        }
                    }
                    else if ( typeof(column) == 'undefined' ) {
                        return;
                    }
                    else {
                        console.error("Can't deal with column def of type: " + typeof(column));
                    }
                });

                self.tbody.append(tr);
                if (options && row.id == options.focusid && self.options.focusElement) {
                    if (tr.find(self.options.focusElement).length) {
                        tr.find(self.options.focusElement).trigger("focus");
                    }
                }
            });
        }
    };

    this.doupdate = function(request_args, options) {
        if (!request_args) {
            request_args = {};
        }
        self.lastArgs = request_args;

        $.each(self.statevars, function(id, key) {
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
                console.error('tablerenderer call updatecallback(', response, ') failed.');
            }

            if (typeof(self.emptycontent) != 'undefined') {
                // Make sure the emptycontent is in a div
                if (self.emptycontent[0].nodeName != 'DIV') {
                    self.emptycontent = $('<div>').append(self.emptycontent);
                    self.emptycontent.insertBefore(self.table);
                }

                if (self.count > 0) {
                    self.emptycontent.addClass('d-none');
                    self.table.css('display', '');
                }
                else {
                    self.table.css('display', 'none');
                    self.emptycontent.removeClass('d-none');
                }
            }

            if (self.loadingMessage) {
                self.loadingMessage.remove();
                self.loadingMessage = null;
            }

            self.renderdata(response, options);

            self.table.removeClass('d-none');

            try {
                self.postupdatecallback(response);
            }
            catch (e) {
                console.error('tablerenderer call postupdatecallback(', response, ') failed.');
            }

        }, null, true);
    };

    this.updateOnLoad = function(request_args) {
        self.updateOnLoadFlag = true;
        if ( TableRendererPageLoaded ) {
            self.doupdate();
        }
        else {
            $(self.doupdate.bind(null, request_args, null));
        }
    };

    if ( TableRendererPageLoaded ) {
        this.init();
    }
    else {
        $(this.init);
    }
};
}(jQuery));
