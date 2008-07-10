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
        forEach(getElementsByTagAndClassName('span', 'pagination', 'membersearchresults'), function(i) {
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
            $('results').innerHTML = data.data;
            if ($('searchresults')) {
                self.rewritePaging();
            }
        });
    }

    addLoadEvent(self.init);
}

userSearch = new UserSearch();
