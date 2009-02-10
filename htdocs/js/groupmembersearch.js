/**
 * 'Speeds up' the group member search if the user has javascript enabled in their
 * browser
 *
 * Copyright: 2006-2008 Catalyst IT Ltd
 * This file is licensed under the same terms as Mahara itself
 */

function UserSearch() {
    var self = this;

    this.init = function () {
        self.rewriteQueryButton();
        self.rewritePaging();
        self.params = {};
    }

    this.searchByChildLink = function (element) {
        var children = getElementsByTagAndClassName('a', null, element);
        if (children.length == 1) {
            var href = getNodeAttribute(children[0], 'href');
            self.params = parseQueryString(href.substring(href.indexOf('?')+1, href.length));
            self.doSearch();
        }
    }

    this.changePage = function(e) {
        e.stop();
        self.searchByChildLink(this);
    }

    this.rewritePaging = function() {
        forEach(getElementsByTagAndClassName('span', 'pagination', 'pagination'), function(i) {
            connect(i, 'onclick', self.changePage);
        });
    }

    this.rewriteQueryButton = function() {
        connect($('query-button'), 'onclick', self.newQuery);
    }

    this.newQuery = function(e) {
        self.params = {};
        self.params.query = $('query').value;
        self.params.id = $('groupid').value;
        self.doSearch();
        e.stop();
    }

    this.doSearch = function() {
        self.params.action = 'search';
        sendjsonrequest('membersearchresults.php', self.params, 'POST', function(data) {
            var tbody = getElementsByTagAndClassName('tbody', null, 'results')[0];
            if (
                (document.all && document.documentElement && typeof(document.documentElement.style.maxHeight) != "undefined" && !window.opera)
                    ||
                    (/Konqueror|AppleWebKit|KHTML/.test(navigator.userAgent))) {
                var temp = DIV({'id':'ie-workaround'});
                temp.innerHTML = '<table><tbody>' + data.data.tablerows + '</tbody></table>';
                swapDOM(tbody, temp.childNodes[0].childNodes[0]);
                removeElement(temp);
            }
            else {
                tbody.innerHTML = data.data.tablerows;
            }
            $('pagination').innerHTML = data.data.pagination;
            if (data.data.count) {
                self.rewritePaging();
            }
        });
    }

    addLoadEvent(self.init);
}

userSearch = new UserSearch();
