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
        forEach(getElementsByTagAndClassName('span', 'pagination'), function(i) {
            var a = getFirstElementByTagAndClassName('a', null, i);

            // If there is a link
            if (a) {
                self.rewritePaginatorLink(a);
            }
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

            sendjsonrequest(self.jsonScript, queryData, 'GET', function(data) {
                var tbody = getFirstElementByTagAndClassName('tbody', null, self.datatable);
                if (tbody) {
                    // Currently the paginator is used for the artefact chooser
                    // alone. This block assumes there is a DOM node with an ID
                    // of 'ie-workaround', but could be improved somewhat to
                    // perhaps not need the DOM node to be in the DOM - at
                    // least not when the page loads.
                    //
                    // You can't write to table nodes innerHTML in IE and
                    // konqueror, so this workaround detects them and does
                    // things differently
                    if (
                        (document.all && document.documentElement && typeof(document.documentElement.style.maxHeight) != "undefined" && !window.opera)
                        ||
                        (/Konqueror|AppleWebKit|KHTML/.test(navigator.userAgent))) {
                        var temp = $('ie-workaround');
                        temp.innerHTML = '<table><tbody>' + data['data']['tablerows'];
                        swapDOM(tbody, temp.childNodes[0].childNodes[0]);
                        replaceChildNodes(temp);
                    }
                    else {
                        tbody.innerHTML = data['data']['tablerows'];
                    }
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
                    if (results) {
                        results.innerHTML = data['data']['results'];
                    }
                }

                self.alertProxy('pagechanged', data['data']);
            });
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
