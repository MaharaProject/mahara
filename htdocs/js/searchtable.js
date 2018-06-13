/**
 * 'Speeds up' search if the user has javascript enabled in their browser
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

var SearchTable = (function($) {
  return function (id) {
    var self = this;
    this.id = id;

    this.init = function () {
        self.rewriteQueryButtons();
        self.rewritePaging();
        self.params = {};
    };

    this.searchByChildLink = function (element) {
        var children = $(element).find('a');
        if (children.length == 1) {
            var href = children.first().prop('href');
            self.params = parseQueryString(href.substring(href.indexOf('?')+1, href.length));
            self.doSearch();
        }
    };

    this.changePage = function(e) {
        e.preventDefault();
        self.searchByChildLink(this);
    };

    this.rewritePaging = function() {
        $('#' + self.id + ' span.pagination').each(function() {
            $(this).on('click', self.changePage);
        });
    };

    this.rewriteQueryButtons = function() {
        $('#' + self.id + ' button.query-button').each(function() {
            $(this).on('click', self.newQuery);
        });
    };

    this.rewriteOther = function () {}; // Override

    this.newQuery = function(e) {
        self.params = {};
        $(this).closest('form').find('input').each(function() {
            self.params[this.name] = this.value;
        });
        self.doSearch();
        e.preventDefault();
    };

    this.doSearch = function() {
        sendjsonrequest(self.id + '.json.php', self.params, 'POST', function(data) {
            $('#' + self.id + '_table').html(data.data.table);
            $('#' + self.id + '_pagination').html(data.data.pagination);
            if (data.data.count) {
                self.rewritePaging();
                self.rewriteOther();
            }
        });
    };

    $(self.init);
  };
}(jQuery));

//searchTable = new SearchTable();
