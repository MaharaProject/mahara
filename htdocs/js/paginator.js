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
                connect(a, 'onclick', function(e) {
                    e.stop();

                    var loc = a.href.indexOf('?');
                    var queryData = [];
                    if (loc != -1) {
                        queryData = parseQueryString(a.href.substring(loc + 1, a.href.length));
                        queryData.extradata = serializeJSON(self.extraData);
                    }

                    sendjsonrequest(self.jsonScript, queryData, 'GET', function(data) {
                        getFirstElementByTagAndClassName('tbody', null, self.datatable).innerHTML = data['data']['tablerows'];

                        // Update the pagination
                        var tmp = DIV();
                        tmp.innerHTML = data['data']['pagination'];
                        swapDOM(self.id, tmp.firstChild);

                        // Run the pagination js to make it live
                        eval(data['data']['pagination_js']);

                        // Update the result count
                        getFirstElementByTagAndClassName('div', 'results', self.id).innerHTML = data['data']['count'] + ' results'; // TODO i18n
                    });
                });
            }
        });
    }

    this.init(id, datatable, script, extradata);
}
