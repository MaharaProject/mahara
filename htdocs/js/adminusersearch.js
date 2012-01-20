/**
 * 'Speeds up' the user search if the user has javascript enabled in
 * their browser
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

function UserSearch() {
    var self = this;

    this.init = function () {
        self.rewriteInitials();
        self.rewriteQueryButton();
        self.rewritePaging();
        self.rewriteSorting();
        self.rewriteSetLimit();
        self.selectusers = {};
        self.rewriteCheckboxes();
        self.params = {};
    };

    this.rewriteInitials = function() {
        forEach(getElementsByTagAndClassName('span', 'first-initial', 'firstnamelist'), function(i) {
            self.rewriteInitial('f', i);
        });
        forEach(getElementsByTagAndClassName('span', 'last-initial', 'lastnamelist'), function(i) {
            self.rewriteInitial('l', i);
        });
    };

    this.rewriteInitial = function(t, i) {
        connect(i, 'onclick', partial(self.searchInitial, t));
    };

    this.resetInitials = function() {
        forEach(getElementsByTagAndClassName('span', 'selected', 'initials'), function (i) {
            removeElementClass(i, 'selected');
        });
        forEach(getElementsByTagAndClassName('span', 'all', 'initials'), function (i) {
            addElementClass(i, 'selected');
        });
    };

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
            // Assume this is only changing the page or the order of results,
            // so pass true here to avoid clearing the selected users.
            self.doSearch(true);
        }
    };

    this.changePage = function(e) {
        e.stop();
        self.searchByChildLink(this);
    };

    this.rewritePaging = function() {
        forEach(getElementsByTagAndClassName('span', 'pagination', 'searchresults'), function(i) {
            connect(i, 'onclick', self.changePage);
        });
    };

    this.sortColumn = function(e) {
        e.stop();
        self.searchByChildLink(this);
    };

    this.rewriteSorting = function() {
        forEach(getElementsByTagAndClassName('th', 'search-results-sort-column', 'searchresults'), function(i) {
            connect(i, 'onclick', self.sortColumn);
        });
    };

    this.rewriteQueryButton = function() {
        connect($('query-button'), 'onclick', self.newQuery);
    };

    this.newQuery = function(e) {
        self.params = {};
        self.resetInitials();
        self.params.query = $('query').value;
        var institution = $('institution');
        if (institution) {
            self.params.institution = institution.value;
        }
        var institution_requested = $('institution_requested');
        if (institution_requested) {
            self.params.institution_requested = institution_requested.value;
        }
        self.doSearch();
        e.stop();
    };

    this.doSearch = function(saveselected) {
        self.params.action = 'search';
        sendjsonrequest('search.json.php', self.params, 'POST', function(data) {
            $('results').innerHTML = data.data;
            if (!saveselected) {
                self.selectusers = {};
            }
            if ($('searchresults')) {
                self.rewritePaging();
                self.rewriteSorting();
                self.rewriteCheckboxes();
                self.rewriteSetLimit();
            }
        });
    };

    this.rewriteCheckboxes = function() {
        forEach(getElementsByTagAndClassName('input', 'selectusers', 'searchresults'), function(i) {
            connect(i, 'onclick', function() {
                if (i.checked) {
                    self.selectusers[i.value] = 1;
                }
                else {
                    delete self.selectusers[i.value];
                }
            });
            if (self.selectusers[i.value]) {
                i.checked = true;
            }
        });
        if ($('selectall')) {
            connect('selectall', 'onclick', function(e) {
                e.stop();
                forEach(getElementsByTagAndClassName('input', 'selectusers', 'searchresults'), function(i) {
                    self.selectusers[i.value] = 1;
                    i.checked = true;
                });
            });
            connect('selectnone', 'onclick', function(e) {
                e.stop();
                forEach(getElementsByTagAndClassName('input', 'selectusers', 'searchresults'), function(i) {
                    delete self.selectusers[i.value];
                    i.checked = false;
                });
            });
        }
    };

    this.rewriteSetLimit = function() {
        if ($('setlimit')) {
            forEach(getElementsByTagAndClassName('a', null, 'setlimit'), function(i) {
                connect(i, 'onclick', function(e) {
                    e.stop();
                    if (!self.params.offset) {
                        self.params.offset = 0;
                    }
                    self.params.limit = scrapeText(i);
                    self.params.offset = Math.floor(self.params.offset / self.params.limit) * self.params.limit;
                    self.doSearch(true);
                });
            });
        }
    };

    addLoadEvent(self.init);
}

userSearch = new UserSearch();

function connectSelectedUsersForm(formid) {
    forEach(getElementsByTagAndClassName('input', 'button', formid), function(input) {
        connect(input, 'onclick', function() {
            // Some of the selected users aren't on the page, so just add them all to the
            // form now.
            var count = 0;
            if (userSearch.selectusers) {
                for (j in userSearch.selectusers) {
                    appendChildNodes(formid, INPUT({
                        'type': 'checkbox',
                        'name': 'users[' + j + ']',
                        'value': j,
                        'class': 'hidden',
                        'checked': 'checked'
                    }));
                    count++;
                }
            }
            if (count) {
                addElementClass('nousersselected', 'hidden');
                appendChildNodes(formid, INPUT({
                    'type': 'hidden',
                    'name': 'action',
                    'value': input.name
                }));
                $(formid).submit();
                return false;
            }
            removeElementClass('nousersselected', 'hidden');
            return false;
        });
    });
}

addLoadEvent(function() {
    forEach(['bulkactions', 'report'], connectSelectedUsersForm);
});
