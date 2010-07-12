/**
 * 'Speeds up' search if the user has javascript enabled in their browser
 * @source: http://gitorious.org/mahara/mahara
 *
 * @licstart
 * Copyright (C) 2009-2010  Catalyst IT Ltd
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

function SearchTable(id) {
    var self = this;
    this.id = id;

    this.init = function () {
        self.rewriteQueryButtons();
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
        forEach(getElementsByTagAndClassName('span', 'pagination', self.id), function(i) {
            connect(i, 'onclick', self.changePage);
        });
    }

    this.rewriteQueryButtons = function() {
        forEach(getElementsByTagAndClassName('button', 'query-button', self.id), function(i) {
            connect(i, 'onclick', self.newQuery);
        });
    }

    this.rewriteOther = function () {} // Override

    this.newQuery = function(e) {
        self.params = {};
        forEach(getElementsByTagAndClassName('input', null, getFirstParentByTagAndClassName(this, 'form')), function(i) {
            self.params[i.name] = i.value;
        });
        self.doSearch();
        e.stop();
    }

    this.doSearch = function() {
        sendjsonrequest(self.id + '.json.php', self.params, 'POST', function(data) {
            $(self.id + '_table').innerHTML = data.data.table;
            $(self.id + '_pagination').innerHTML = data.data.pagination;
            if (data.data.count) {
                self.rewritePaging();
                self.rewriteOther();
            }
        });
    }

    addLoadEvent(self.init);
}

//searchTable = new SearchTable();
