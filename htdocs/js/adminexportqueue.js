/**
 * 'Speeds up' the user search if the user has javascript enabled in
 * their browser
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

function UserSearch(pager) {
    var self = this;

    this.init = function() {
        self.selectusersdelete = {};
        self.selectusersexport = {};

        self.rewriteHeaders();
        self.rewriteQueryButton();
        self.rewriteCheckboxes();

        paginatorProxy.addObserver(self);
        var oldparams = $j.extend({}, pager.params);
        connect(self, 'pagechanged', function(data) {
            if (!data.changedPage) {
                self.selectusersdelete = {};
                self.selectusersexport = {};
            }
            oldparams = $j.extend({}, pager.params);
            $j.each(['archive', 'exportdelete'], self.connectSelectedUsersForm);
            self.rewriteCheckboxes();
        });

        $j.each(['archive', 'exportdelete'], self.connectSelectedUsersForm);
    };

    this.rewriteHeaders = function() {
        $j('#searchresults th.search-results-sort-column a').each(function() {
            var href = $j(this).attr('href');
            var index = href.indexOf('?');
            var querystring = parseQueryString(href.substr(index));
            var sortby = querystring.sortby;
            var sortdir = querystring.sortdir;

            $j(this).click(function() {
                var header = $j(this).parent();
                if (!(header.hasClass('asc') || header.hasClass('desc'))) {
                    sortdir = 'asc';
                }

                pager.params.sortby = sortby;
                pager.params.sortdir = sortdir;

                $j(this).closest('thead').find('th').removeClass('asc').removeClass('desc');
                $j(this).parent().addClass(sortdir);
                var re1 = new RegExp(strings.descending);
                var re2 = new RegExp(strings.ascending);
                $j(this).closest('tr').find('span').each(function(i, el) {
                    el.innerHTML = el.innerHTML.replace(re1, strings.ascending);
                });
                if (sortdir == 'asc') {
                    $j(this).find('span').html($j(this).find('span').html().replace(re2, strings.descending));
                }
                sortdir = (sortdir == 'desc') ? 'asc' : 'desc';

                pager.sendQuery();
                return false;
            });
        });
    };

    this.rewriteQueryButton = function() {
        $j('#query-button').click(function() {
            pager.params.query = $j('#query').val();
            var institution = $j('#institution');
            if (institution) {
                pager.params.institution = institution.val();
            }
            var institution_requested = $j('#institution_requested');
            if (institution_requested) {
                pager.params.institution_requested = institution_requested.val();
            }
            pager.sendQuery();
            return false;
        });
    };

    this.rewriteCheckboxes = function() {
        // For the export checkboxes
        $j('#searchresults input.selectusersexport').each(function() {
            var value = $j(this).val();
            $j(this).change(function() {
                if ($j(this).prop('checked')) {
                    self.selectusersexport[value] = 1;
                }
                else {
                    delete self.selectusersexport[value];
                }
            });
            if (self.selectusersexport[value]) {
                $j(this).prop('checked', true);
            }
        });
        if ($j('#selectallexport')) {
            $j('#selectallexport').click(function() {
                $j('#searchresults input.selectusersexport').each(function() {
                    if (!$j(this).is(':disabled')) {
                        self.selectusersexport[$j(this).val()] = 1;
                        $j(this).prop('checked', true);
                    }
                });
                return false;
            });
            $j('#selectnoneexport').click(function() {
                $j('#searchresults input.selectusersexport').each(function() {
                    delete self.selectusersexport[$j(this).val()];
                    $j(this).prop('checked', false);
                });
                return false;
            });
        }

        // For the delete checkboxes
        $j('#searchresults input.selectusersexportdelete').each(function() {
            var value = $j(this).val();
            $j(this).change(function() {
                if ($j(this).prop('checked')) {
                    self.selectusersdelete[value] = 1;
                }
                else {
                    delete self.selectusersdelete[value];
                }
            });
            if (self.selectusersdelete[value]) {
                $j(this).prop('checked', true);
            }
        });
        if ($j('#selectalldelete')) {
            $j('#selectalldelete').click(function() {
                $j('#searchresults input.selectusersexportdelete').each(function() {
                    self.selectusersdelete[$j(this).val()] = 1;
                    $j(this).prop('checked', true);
                });
                return false;
            });
            $j('#selectnonedelete').click(function() {
                $j('#searchresults input.selectusersexportdelete').each(function() {
                    delete self.selectusersdelete[$j(this).val()];
                    $j(this).prop('checked', false);
                });
                return false;
            });
        }
    };

    this.connectSelectedUsersForm = function(i, formid) {
        $j('#' + formid + ' input.button').click(function() {
            // Some of the selected users aren't on the page, so just add them all to the
            // form now.
            var countdelete = 0;
            if (self.selectusersdelete) {
                for (var j in self.selectusersdelete) {
                    $j('#' + formid).append($j('<input>', {
                        'type': 'checkbox',
                        'name': 'deleterows[' + j + ']',
                        'value': j,
                        'class': 'hidden',
                        'checked': 'checked'
                    }));
                    countdelete++;
                }
            }

            var countarchive = 0;
            if (self.selectusersexport) {
                for (var j in self.selectusersexport) {
                    $j('#' + formid).append($j('<input>', {
                        'type': 'checkbox',
                        'name': 'exportrows[' + j + ']',
                        'value': j,
                        'class': 'hidden',
                        'checked': 'checked'
                    }));
                    countarchive++;
                }
            }

            if ((countdelete && $j(this).attr('name') == 'delete') || (countarchive && $j(this).attr('name') == 'export')) {
                $j('#nousersselected').addClass('hidden');
                $j('#' + formid).append($j('<input>', {
                    'type': 'hidden',
                    'name': 'action',
                    'value': $j(this).attr('name')
                }));
                $j('#' + formid).submit();
                return false;
            }
            $j('#nousersselected').removeClass('hidden');
            return false;
        });
    };

    this.init();
}
