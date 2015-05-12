/**
 * 'Speeds up' search if the user has javascript enabled in their browser
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

function SearchTable(id) {
    var self = this;
    this.id = id;

    this.init = function () {
        self.rewriteQueryButtons();
        self.rewritePaging();
        self.params = {};
    };

    this.searchByChildLink = function (element) {
        var children = getElementsByTagAndClassName('a', null, element);
        if (children.length == 1) {
            var href = getNodeAttribute(children[0], 'href');
            self.params = parseQueryString(href.substring(href.indexOf('?')+1, href.length));
            self.doSearch();
        }
    };

    this.changePage = function(e) {
        e.stop();
        self.searchByChildLink(this);
    };

    this.rewritePaging = function() {
        forEach(getElementsByTagAndClassName('span', 'pagination', self.id), function(i) {
            connect(i, 'onclick', self.changePage);
        });
    };

    this.rewriteQueryButtons = function() {
        forEach(getElementsByTagAndClassName('button', 'query-button', self.id), function(i) {
            connect(i, 'onclick', self.newQuery);
        });
    };

    this.rewriteOther = function () {}; // Override

    this.newQuery = function(e) {
        self.params = {};
        forEach(getElementsByTagAndClassName('input', null, getFirstParentByTagAndClassName(this, 'form')), function(i) {
            self.params[i.name] = i.value;
        });
        self.doSearch();
        e.stop();
    };

    this.doSearch = function() {
        sendjsonrequest(self.id + '.json.php', self.params, 'POST', function(data) {
            $(self.id + '_table').innerHTML = data.data.table;
            $(self.id + '_pagination').innerHTML = data.data.pagination;
            if (data.data.count) {
                self.rewritePaging();
                self.rewriteOther();
            }
        });
    };

    addLoadEvent(self.init);
}

//searchTable = new SearchTable();
