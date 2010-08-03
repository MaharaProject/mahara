/**
 * Javascript side of the accessible pagination for Mahara.
 * @source: http://gitorious.org/mahara/mahara
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

/**
 * Hooks into pagination built with the smarty function 'mahara_pagination',
 * and rewrites it to be javascript aware
 *
 * @param id The ID of the div that contains the pagination
 * @param datatable The ID of the table containing the paginated data
 * @param script    The URL (from wwwroot) of the script to hit to get new
 *                  pagination data
 * @param limit     Extra data to pass back in the ajax requests to the script
 */
var Paginator = function(id, datatable, script, extradata) {
    var self = this;

    this.init = function(id, datatable, script, extradata) {
        self.id = id;
        self.datatable = $(datatable);
        self.jsonScript = config['wwwroot'] + script;
        self.extraData = extradata;

        self.rewritePaginatorLinks();
    }

    this.rewritePaginatorLinks = function() {
        forEach(getElementsByTagAndClassName('span', 'pagination', self.id), function(i) {
            var a = getFirstElementByTagAndClassName('a', null, i);

            // If there is a link
            if (a) {
                self.rewritePaginatorLink(a);
            }
        });
    }

    this.updateResults = function (data) {
        var tbody = getFirstElementByTagAndClassName('tbody', null, self.datatable);
        if (tbody) {
            // You can't write to table nodes innerHTML in IE and
            // konqueror, so this workaround detects them and does
            // things differently
            if ((document.all && !window.opera) || (/Konqueror|AppleWebKit|KHTML/.test(navigator.userAgent))) {
                var temp = DIV({'id':'ie-workaround'});
                temp.innerHTML = '<table><tbody>' + data.data.tablerows + '</tbody></table>';
                swapDOM(tbody, temp.childNodes[0].childNodes[0]);
            }
            else {
                tbody.innerHTML = data['data']['tablerows'];
            }

            // Pieforms should probably separate its js from its html. For
            // now, be evil: scrape it out of the script elements and eval
            // it every time the page changes.
            forEach(getElementsByTagAndClassName('script', null, tbody), function(s) {
                var m = scrapeText(s).match(new RegExp('^(new Pieform\\\(.*?\\\);)$'));
                if (m && m[1]) {
                    eval('var pf = ' + m[1] + ' pf.init();');
                }
            });
        }

        // Update the pagination
        if ($(self.id)) {
            var tmp = DIV();
            tmp.innerHTML = data['data']['pagination'];
            swapDOM(self.id, tmp.firstChild);

            // Run the pagination js to make it live
            eval(data['data']['pagination_js']);

            // Update the result count
            var results = getFirstElementByTagAndClassName('div', 'results', self.id);
            if (results && data.data.results) {
                results.innerHTML = data.data.results;
            }
        }
    }

    this.sendQuery = function(params) {
        sendjsonrequest(self.jsonScript, params, 'GET', function(data) {
            self.updateResults(data);
            self.alertProxy('pagechanged', data['data']);
        });
    }

    this.rewritePaginatorLink = function(a) {
        connect(a, 'onclick', function(e) {
            e.stop();

            var loc = a.href.indexOf('?');
            var queryData = [];
            if (loc != -1) {
                queryData = parseQueryString(a.href.substring(loc + 1, a.href.length));
                queryData.extradata = serializeJSON(self.extraData);
            }

            self.sendQuery(queryData);
        });
    }

    this.alertProxy = function(eventName, data) {
        if (typeof(paginatorProxy) == 'object') {
            paginatorProxy.alertObservers(eventName, data);
        }
    }

    this.init(id, datatable, script, extradata);
}

/**
 * Any object can subscribe to the PaginatorProxy and thus be alerted when a
 * paginator changes page.
 *
 * This is done through a proxy object because generally a new Paginator object
 * is instantiated for each time the page is changed, and the old one thrown
 * away, so you can't really subscribe to events on the paginator itself.
 *
 * Generally, one paginator object should be created for an entire page.
 */
function PaginatorProxy() {
    var self = this;

    /**
     * Alerts any observers to a fired event. Called by paginator objects
     */
    this.alertObservers = function(eventName, data) {
        forEach(self.observers, function(o) {
            signal(o, eventName, data);
        });
    }

    /**
     * Adds an observer to listen to paginator events
     */
    this.addObserver = function(o) {
        self.observers.push(o);
    }

    this.observers = [];
}

// Create the paginator proxy
var paginatorProxy = new PaginatorProxy();
