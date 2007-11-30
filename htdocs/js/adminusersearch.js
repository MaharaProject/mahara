function UserSearch() {
    var self = this;

    this.init = function () {
        self.rewriteInitials();
        self.rewriteQueryButton();
        self.rewritePaging();
        self.rewriteSorting();
        self.params = {};
    }

    this.rewriteInitials = function() {
        forEach(getElementsByTagAndClassName('span', 'first-initial', 'firstnamelist'), function(i) {
            self.rewriteInitial('f', i);
        });
        forEach(getElementsByTagAndClassName('span', 'last-initial', 'lastnamelist'), function(i) {
            self.rewriteInitial('l', i);
        });
    }

    this.rewriteInitial = function(t, i) {
        connect(i, 'onclick', partial(self.searchInitial, t));
    }

    this.resetInitials = function() {
        forEach(getElementsByTagAndClassName('span', 'selected', 'initials'), function (i) {
            removeElementClass(i, 'selected');
        });
        forEach(getElementsByTagAndClassName('span', 'all', 'initials'), function (i) {
            addElementClass(i, 'selected');
        });
    }

    this.searchInitial = function(initialtype, e) {
        // Clear all search params except for the other initial
        if (initialtype == 'f') {
            if (self.params.l) {
                self.params = {'l' : self.params.l};
            } else {
                self.params = {};
            }
            forEach(getElementsByTagAndClassName('span', 'selected', 'firstnamelist'), function (i) {
                removeElementClass(i, 'selected');
            });
        } else if (initialtype == 'l') {
            if (self.params.f) {
                self.params = {'f' : self.params.f};
            } else {
                self.params = {};
            }
            forEach(getElementsByTagAndClassName('span', 'selected', 'lastnamelist'), function (i) {
                removeElementClass(i, 'selected');
            });
        }
        addElementClass(this, 'selected');
        if (!hasElementClass(this, 'all')) {
            self.params[initialtype] = scrapeText(this).replace(/\s+/g, '');
        }
        self.doSearch();
        e.stop();
    };

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
        forEach(getElementsByTagAndClassName('span', 'pagination', 'searchresults'), function(i) {
            connect(i, 'onclick', self.changePage);
        });
    }

    this.sortColumn = function(e) {
        e.stop();
        self.searchByChildLink(this);
    }

    this.rewriteSorting = function() {
        forEach(getElementsByTagAndClassName('th', 'search-results-sort-column', 'searchresults'), function(i) {
            connect(i, 'onclick', self.sortColumn);
        });
    }

    this.rewriteQueryButton = function() {
        connect($('query-button'), 'onclick', self.newQuery);
    }

    this.newQuery = function(e) {
        self.params = {};
        self.resetInitials();
        self.params.query = $('query').value;
        self.params.institution = $('institution').value;
        self.params.institution_requested = $('institution_requested').value;
        self.doSearch();
        e.stop();
    }

    this.doSearch = function() {
        self.params.action = 'search';
        sendjsonrequest('search.json.php', self.params, 'POST', function(data) {
            $('results').innerHTML = data.data;
            self.rewritePaging();
            self.rewriteSorting();
        });
    }

    addLoadEvent(self.init);
}

userSearch = new UserSearch();
