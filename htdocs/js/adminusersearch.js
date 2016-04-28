/**
 * 'Speeds up' the user search if the user has javascript enabled in
 * their browser
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
        self.selectusers = {};

        self.rewriteHeaders();
        self.rewriteInitials();
        self.rewriteQueryButton();
        self.rewriteCheckboxes();
        self.rewriteLoggedInFilter();
        self.rewriteDuplicateEmailFilter();

        paginatorProxy.addObserver(self);
        var oldparams = $j.extend({}, pager.params);
        connect(self, 'pagechanged', function(data) {
            if (!data.changedPage) {
                self.selectusers = {};
            }
            oldparams = $j.extend({}, pager.params);
            self.rewriteCheckboxes();
        });

        $j.each(['bulkactions', 'report'], self.connectSelectedUsersForm);
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

    this.rewriteInitials = function() {
        $j('#firstnamelist span.first-initial').each(function() {
            $j(this).click(function() {
                self.searchInitial('f', $j(this));
                return false;
            });
        });
        $j('#lastnamelist span.last-initial').each(function() {
            $j(this).click(function() {
                self.searchInitial('l', $j(this));
                return false;
            });
        });
    };

    this.searchInitial = function(initialtype, el) {
        // Clear all search params except for the other initial
        if (initialtype == 'f') {
            $j('#firstnamelist span.selected').removeClass('selected');
        }
        else if (initialtype == 'l') {
            $j('#lastnamelist span.selected').removeClass('selected');
        }
        el.addClass('selected');
        if (el.hasClass('all')) {
            delete pager.params[initialtype];
        }
        else {
            pager.params[initialtype] = el.text().replace(/\s+/g, '');
        }
        pager.params.offset = 0;
        pager.sendQuery();
    };

    this.rewriteQueryButton = function() {
        $j('#query-button').click(function() {
            pager.params.offset = 0;
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
        $j('#searchresults input.selectusers').each(function() {
            var value = $j(this).val();
            $j(this).change(function() {
                if ($j(this).prop('checked')) {
                    $j(this).closest('tr').addClass('warning'); // visual selected indicator
                    self.selectusers[value] = 1;
                }
                else {
                    $j(this).closest('tr').removeClass('warning'); // visual selected indicator
                    delete self.selectusers[value];
                }
                //update button state
                if($j('#searchresults input.selectusers:checked').length > 0){
                    $j('.withselectedusers button').removeClass('disabled');
                }
                else {
                    $j('.withselectedusers button').addClass('disabled');
                }
            });
            if (self.selectusers[value]) {
                $j(this).prop('checked', true);
            }
        });
        if ($j('#selectall')) {
            $j('#selectall').click(function() {
                $j(this).addClass('active');
                $j(this).siblings().removeClass('active');
                $j('.withselectedusers button').removeClass('disabled');
                $j('#searchresults input.selectusers').each(function() {
                    self.selectusers[$j(this).val()] = 1;
                    $j(this).prop('checked', true);
                    $j(this).closest('tr').addClass('warning'); // visual selected indicator
                });
                return false;
            });
            $j('#selectnone').click(function() {
                $j(this).addClass('active');
                $j(this).siblings().removeClass('active');
                $j('.withselectedusers button').addClass('disabled');
                $j('#searchresults input.selectusers').each(function() {
                    delete self.selectusers[$j(this).val()];
                    $j(this).prop('checked', false);
                    $j(this).closest('tr').removeClass('warning'); // visual selected indicator
                });
                return false;
            });
        }
    };

    this.rewriteLoggedInFilter = function() {
        $j('#loggedin').change(function() {
            var type = $j(this).val();
            pager.params.offset = 0;
            pager.params.loggedin = type;
            if (type === 'since' || type === 'notsince') {
                $j('#loggedindate_container').removeClass('js-hidden');
            }
            else {
                $j('#loggedindate_container').addClass('js-hidden');
            }
            pager.sendQuery();
            return false;
        });
        $j('#loggedinform_loggedindate').change(function() {
            // Set handler directly so that calendar works
            pager.params.offset = 0;
            pager.params.loggedindate = $j(this).val();
            pager.sendQuery();
        });
    };

    this.rewriteDuplicateEmailFilter = function() {
        $j('#duplicateemail').click(function() {
            pager.params.offset = 0;
            pager.params.duplicateemail = $j(this).prop('checked');
            pager.sendQuery();
        });
    };

    this.connectSelectedUsersForm = function(i, formid) {
        $j('#' + formid + ' button').click(function() {
            // Some of the selected users aren't on the page, so just add them all to the
            // form now.
            var count = 0;
            if (self.selectusers) {
                for (var j in self.selectusers) {
                    $j('#' + formid).append($j('<input>', {
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
