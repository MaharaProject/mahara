/**
 * Hooks into pagination built with the smarty function 'mahara_pagination',
 * and rewrites it to be javascript aware
 */
var Paginator = function(id, datatable, script) {
    var self = this;

    this.init = function(id, datatable, script) {
        self.id = id;
        self.datatable = $(datatable);
        self.jsonScript = config['wwwroot'] + script;

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
                        queryData = parseQueryString(a.href.substring(loc + 1, a.href.length)); // ie danger
                        log(queryData);
                    }

                    sendjsonrequest(self.jsonScript, queryData, 'GET', function(data) {
                        getFirstElementByTagAndClassName('tbody', null, self.datatable).innerHTML = data['data']['tablerows'];
                        log('id', self.id, 'self', self);
                        $(self.id).innerHTML = data['data']['pagination'];
                        eval(data['data']['pagination_js']);
                        //self.changePage();
                    });
                });
            }
        });
    }

    this.changePage = function() {
        
    }

    this.init(id, datatable, script);
}
