/**
 * Javascript side of the accessible pagination for Mahara.
 * @source: http://gitorious.org/mahara/mahara
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
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
var Paginator = function(id, datatable, heading, script, extradata) {
    var self = this;

    this.init = function(id, datatable, heading, script, extradata) {
        self.id = id;
        if (script && script.length !== 0) {
            self.datatable = $(datatable);
            self.heading = $(heading);
            self.jsonScript = config['wwwroot'] + script;
            self.extraData = extradata;

            var index = location.href.indexOf('?');
            if (index >= 0) {
                var querystring = parseQueryString(location.href.substr(index));
                self.params = querystring;
            }
            else if (typeof cleanurlid !== 'undefined') {
                // we need to get the user id of the profile we are viewing
                self.params = {"id": cleanurlid};
            }
            else if (Paginator.oldparams) {
                // Set if the page has been changed and we're setting up the new pagination controls
                self.params = Paginator.oldparams;
            }
            else {
                self.params = {};
            }

            if (self.heading) {
                addElementClass(self.heading, 'hidefocus');
                setNodeAttribute(self.heading, 'tabIndex', -1);
            }

            self.rewritePaginatorLinks();
            self.rewritePaginatorSelectForm();
        }
        else {
            self.rewritePaginatorSelectFormWithoutJSON();
        }
    };

    this.rewritePaginatorLinks = function() {
        forEach(getElementsByTagAndClassName('span', 'pagination', self.id), function(i) {
            var a = getFirstElementByTagAndClassName('a', null, i);

            // If there is a link
            if (a) {
                self.rewritePaginatorLink(a);
            }
        });
    };

    this.rewritePaginatorSelectFormWithoutJSON = function() {
        var setlimitform = getFirstElementByTagAndClassName('form', 'pagination', self.id);
        // If there is a form for choosing page size(page limit)
        if (setlimitform) {
            var setlimitselect = getFirstElementByTagAndClassName('select', 'pagination', setlimitform);
            var currentoffset = getFirstElementByTagAndClassName('input', 'currentoffset', setlimitform);
            connect (setlimitselect, 'onchange', function(e) {
                e.stop();

                var url = setlimitform.action;
                if (url.indexOf('?') != -1) {
                    url += "&";
                }
                else {
                    url += "?";
                }
                url += setlimitselect.name + "=" + setlimitselect.value;
                var offsetvalue = currentoffset.value;
                if ((offsetvalue % setlimitselect.value) !== 0) {
                    offsetvalue = Math.floor(offsetvalue / setlimitselect.value) * setlimitselect.value;
                }
                url += "&" + currentoffset.name + "=" + offsetvalue;
                location.assign(url);
            });
        }
    };

    this.rewritePaginatorSelectForm = function() {
        var setlimitform = getFirstElementByTagAndClassName('form', 'pagination', self.id);
        // If there is a form for choosing page size(page limit)
        if (setlimitform) {
            var setlimitselect = getFirstElementByTagAndClassName('select', 'pagination', setlimitform);
            var currentoffset = getFirstElementByTagAndClassName('input', 'currentoffset', setlimitform);
            connect (setlimitselect, 'onchange', function(e) {
                e.stop();

                var url = setlimitform.action
                var loc = url.indexOf('?');
                var queryData = [];
                if (loc != -1) {
                    queryData = parseQueryString(url.substring(loc + 1, url.length));
                    queryData.offset = currentoffset.value;
                    queryData.setlimit = "1";
                    queryData.limit = setlimitselect.value;
                    queryData.extradata = serializeJSON(self.extraData);
                }

                self.sendQuery(queryData);
            });
        }
    };

    this.updateResults = function (data, params, changedPage) {
        var container = self.datatable;
        if (self.datatable.tagName == 'TABLE') {
            container = getFirstElementByTagAndClassName('tbody', null, self.datatable);
        }
        if (container) {
            // You can't write to table nodes innerHTML in IE and
            // konqueror, so this workaround detects them and does
            // things differently
            if ((document.all && !window.opera) || (/Konqueror|AppleWebKit|KHTML/.test(navigator.userAgent))) {
                var temp = DIV({'id':'ie-workaround'});
                if (container.tagName == 'TBODY') {
                    temp.innerHTML = '<table><tbody>' + data.data.tablerows + '</tbody></table>';
                    swapDOM(container, temp.childNodes[0].childNodes[0]);
                }
                else {
                    temp.innerHTML = data.data.tablerows;
                    replaceChildNodes(container, temp.childNodes);
                }
            }
            else {
                container.innerHTML = data['data']['tablerows'];
            }

            // In Chrome, tbody remains set to the value before tbody.innerHTML was modified
            //  to fix that, we re-initialize tbody using getFirstElementByTagAndClassName
            if (/chrome/.test(navigator.userAgent.toLowerCase()) && container.tagName == 'TBODY') {
                container = getFirstElementByTagAndClassName('tbody', null, self.datatable);
            }

            // Pieforms should probably separate its js from its html. For
            // now, be evil: scrape it out of the script elements and eval
            // it every time the page changes.
            forEach(getElementsByTagAndClassName('script', null, container), function(s) {
                var m = scrapeText(s).match(new RegExp('^(new Pieform\\\(.*?\\\);)$'));
                if (m && m[1]) {
                    eval('var pf = ' + m[1] + ' pf.init();');
                }
            });
        }

        var results;

        // Update the pagination
        if ($(self.id)) {
            var tmp = DIV();
            tmp.innerHTML = data['data']['pagination'];
            swapDOM(self.id, tmp.firstChild);

            // Run the pagination js to make it live
            Paginator.oldparams = params;
            eval(data['data']['pagination_js']);

            // Update the result count
            results = getFirstElementByTagAndClassName('div', 'results', self.id);
            if (results && data.data.results) {
                results.innerHTML = data.data.results;
            }
        }

        if (self.heading) {
            removeElementClass(self.heading, 'hidden');
        }

        // Focus management based on whether the user searched for something or just changed the page
        if (self.heading && !changedPage) {
            self.heading.focus();
        }
        else if (container) {
            var firstLink = getFirstElementByTagAndClassName('a', null, container);
            if (firstLink) {
                firstLink.focus();
            }
            else if (results) {
                setNodeAttribute(results, 'tabindex', -1);
                addElementClass(results, 'hidefocus');
                results.focus();
            }
        }
        self.params = params;
    };

    this.sendQuery = function(params, changedPage) {
        if (params) {
            params = $j.extend({}, self.params, params);
        }
        else {
            params = self.params;
        }
        sendjsonrequest(self.jsonScript, params, 'GET', function(data) {
            self.updateResults(data, params, changedPage);
            var arg = data['data'];
            arg.params = params;
            arg.changedPage = changedPage;
            self.alertProxy('pagechanged', arg);
        });
    };

    this.rewritePaginatorLink = function(a) {
        connect(a, 'onclick', function(e) {
            e.stop();

            var loc = a.href.indexOf('?');
            var queryData = [];
            if (loc != -1) {
                queryData = parseQueryString(a.href.substring(loc + 1, a.href.length));
                queryData.extradata = serializeJSON(self.extraData);
            }

            self.sendQuery(queryData, true);
        });
    };

    this.alertProxy = function(eventName, data) {
        if (typeof(paginatorProxy) == 'object') {
            paginatorProxy.alertObservers(eventName, data);
        }
    };

    this.init(id, datatable, heading, script, extradata);
};

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
    };

    /**
     * Adds an observer to listen to paginator events
     */
    this.addObserver = function(o) {
        self.observers.push(o);
    };

    this.observers = [];
}

// Create the paginator proxy
var paginatorProxy = new PaginatorProxy();
