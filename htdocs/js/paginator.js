/**
 * Javascript side of the accessible pagination for Mahara.
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * Hooks into pagination built with the smarty function 'mahara_pagination',
 * and rewrites it to be javascript aware
 *
 * @param id The ID of the div that contains the pagination
 * @param list The ID of the table containing the paginated data
 * @param script    The URL (from wwwroot) of the script to hit to get new
 *                  pagination data
 * @param limit     Extra data to pass back in the ajax requests to the script
 */
var Paginator = (function($) {
return function(id, list, heading, script, extradata) {
    var self = this;

    this.init = function(id, list, heading, script, extradata) {
        self.id = id;

        if (script && script.length !== 0) {
            self.list = $('#' + list)[0];
            self.heading = $('#' + heading)[0];
            self.jsonScript = config['wwwroot'] + script;
            self.extraData = extradata;

            if (self.list && self.list.tagName == 'TABLE') {
                self.isTable = true;
            }

            var index = location.href.indexOf('?');
            if (index >= 0) {
                var querystring = parseQueryString(location.href.substr(index));
                self.params = querystring;
            }
            else if (typeof cleanurlid !== 'undefined') {
                // we need to get the user id of the profile we are viewing
                self.params = {"id": cleanurlid};
            }
            else if (Paginator.oldparams) {
                // Set if the page has been changed and we're setting up the new pagination controls
                self.params = Paginator.oldparams;
            }
            else {
                self.params = {};
            }

            if (self.heading) {
                $(self.heading).addClass('hidefocus').prop('tabIndex', -1);
            }

            self.rewritePaginatorLinks();
            self.rewritePaginatorSelectForm();
        }
        else {
            self.rewritePaginatorSelectFormWithoutJSON();
        }
    };

    this.rewritePaginatorLinks = function() {
        $('#' + self.id + ' li').each(function() {
            var a = $(this).find('a')[0];

            // If there is a link
            if (a) {
                self.rewritePaginatorLink(a);
            }
        });
    };

    this.rewritePaginatorSelectFormWithoutJSON = function() {
        var setlimitform = $('#' + self.id + ' form.js-pagination').first();
        // If there is a form for choosing page size(page limit)
        if (setlimitform.length) {
            var setlimitselect = setlimitform.find('select.js-pagination').first();
            var currentoffset = setlimitform.find('input.currentoffset').first();
            setlimitselect.on('change', function(e) {
                e.preventDefault();

                var url = setlimitform[0].action;
                if (url.indexOf('?') != -1) {
                    url += "&";
                }
                else {
                    url += "?";
                }
                url += setlimitselect[0].name + "=" + setlimitselect.val();
                var offsetvalue = currentoffset.val();
                if ((offsetvalue % setlimitselect.val()) !== 0) {
                    offsetvalue = Math.floor(offsetvalue / setlimitselect.val()) * setlimitselect.val();
                }
                url += "&" + currentoffset[0].name + "=" + offsetvalue;
                location.assign(url);
            });
        }
    };

    this.rewritePaginatorSelectForm = function() {
        var setlimitform = $('#' + self.id + ' form.js-pagination').first();
        // If there is a form for choosing page size(page limit)
        if (setlimitform.length) {
            var setlimitselect = setlimitform.find('select.js-pagination').first();
            var currentoffset = setlimitform.find('input.currentoffset').first();
            setlimitselect.on('change', function(e) {
                e.preventDefault();

                var url = setlimitform[0].action;
                var loc = url.indexOf('?');
                var queryData = [];
                if (loc != -1) {
                    queryData = parseQueryString(url.substring(loc + 1, url.length));
                    queryData.offset = currentoffset.val();
                    if ((queryData.offset % setlimitselect.val()) !== 0) {
                        queryData.offset = Math.floor(queryData.offset / setlimitselect.val()) * setlimitselect.val();
                    }
                }
                queryData.setlimit = "1";
                queryData.limit = setlimitselect.val();
                queryData.extradata = JSON.stringify(self.extraData);

                self.sendQuery(queryData);
            });
        }
    };

    this.updateResults = function (data, params, changedPage) {
        var container = self.isTable ? $(self.list).find('tbody').first()[0] : self.list,
            listdata = data.data.html ? data.data.html : data.data.tablerows,
            paginationdata = data.data.pagination;

        if (listdata === undefined || listdata.length === 0) {
            var noresults = get_string_ajax('noresultsfound', 'mahara');

            if (self.isTable) {
                var columns = $(self.list).find('th').length;
                listdata = '<tr class="no-results"><td colspan="' + columns + '">' + noresults + '</td></tr>';
            } else {
                listdata = '<p class="no-results">' + noresults + '</p>';
            }
        }

        if (container) {
            // You can't write to table nodes innerHTML in IE and
            // konqueror, so this workaround detects them and does
            // things differently
            if (self.isTable && ((document.all && !window.opera) || (/Konqueror|AppleWebKit|KHTML/.test(navigator.userAgent)))) {
                var temp = $('<div>', {'id':'ie-workaround'});
                if (container.tagName == 'TBODY') {
                    temp.html('<table><tbody>' + listdata + '</tbody></table>');
                    $(container).replaceWith(temp.find('table tbody:first'));
                }
                else {
                    temp.html(listdata);
                    $(container).empty().append(temp.children());
                }
            }
            else {
                $(container).html(listdata);
            }

            if (listdata.match(/data\-confirm/gm) !== null) {
                // need to re-animate the confirm option popup
                $('[data-confirm]').on('click', function() {
                    var content = $(this).attr('data-confirm');
                    return confirm(content);
                });
            }
            // In Chrome, tbody remains set to the value before tbody.innerHTML was modified
            //  to fix that, we re-initialize tbody using getFirstElementByTagAndClassName
            if (/chrome/.test(navigator.userAgent.toLowerCase()) && container.tagName == 'TBODY') {
                container = $(self.list).find('tbody').first()[0];
            }

            // Pieforms should separate its js from its html. For
            // now, be evil: scrape it out of the script elements and eval
            // it every time the page changes. :(
            $(container).find('script').each(function(id, s) {
                var m = $(s).text().match(new RegExp('^(new Pieform\\\(.*?\\\);)$'));
                if (m && m[1]) {
                    eval('var pf = ' + m[1] + ' pf.init();');
                }
            });
        }

        var results;

        // Update the pagination
        if ($('#' + self.id).length) {
            $('#' + self.id).replaceWith(paginationdata);

            // Run the pagination js to make it live
            Paginator.oldparams = params;
            eval(data['data']['pagination_js']);

            // Update the result count
            results = $('#' + self.id + ' div.results');
            if (results.length && data.data.results) {
                results.html(data.data.results);
            }
        }

        if (self.heading) {
            $(self.heading).removeClass('d-none');
        }

        // Focus management based on whether the user searched for something or just changed the page
        if (self.heading && !changedPage) {
            $(self.heading).trigger("focus");
        }
        else if (container) {
            var firstLink = $(container).find('a').first();
            if (firstLink.length) {
                firstLink.trigger("focus");
            }
            else if (results && results.length > 0) {
                results.prop('tabindex', -1)
                    .addClass('hidefocus')
                    .trigger("focus");
            }
        }

        // Fire event to let listseners know to reattach listeners
        $(document).trigger('pageupdated', [ data ]);

        self.params = params;
    };

    this.sendQuery = function(params, changedPage) {
        if (params) {
            params = $.extend({}, self.params, params);
        }
        else {
            params = self.params;
        }
        sendjsonrequest(self.jsonScript, params, 'GET', function(data) {
            self.updateResults(data, params, changedPage);
            var arg = data['data'];
            arg.params = params;
            arg.changedPage = changedPage;
            self.alertProxy('pagechanged', arg);
        });
    };

    this.rewritePaginatorLink = function(a) {
        $(a).on('click', function(e) {
            e.preventDefault();

            var loc = a.href.indexOf('?');
            var queryData = [];
            if (loc != -1) {
                queryData = parseQueryString(a.href.substring(loc + 1, a.href.length));
                queryData.extradata = JSON.stringify(self.extraData);
            }

            self.sendQuery(queryData, true);
        });
    };

    this.alertProxy = function(eventName, data) {
        if (typeof(paginatorProxy) == 'object') {
            paginatorProxy.alertObservers(eventName, data);
        }
    };

    this.init(id, list, heading, script, extradata);
};
}(jQuery));
/**
 * Any object can subscribe to the PaginatorProxy and thus be alerted when a
 * paginator changes page.
 *
 * This is done through a proxy object because generally a new Paginator object
 * is instantiated for each time the page is changed, and the old one thrown
 * away, so you can't really subscribe to events on the paginator itself.
 *
 * Generally, one paginator object should be created for an entire page.
 */
function PaginatorProxy() {
    var self = this;

    /**
     * Alerts any observers to a fired event. Called by paginator objects
     */
    this.alertObservers = function(eventName, data) {
        jQuery.each(self.observers, function(i, o) {
            jQuery(o).triggerHandler(eventName, data);
        });
    };

    /**
     * Adds an observer to listen to paginator events
     */
    this.addObserver = function(o) {
        self.observers.push(o);
    };

    this.observers = [];
}

// Create the paginator proxy
var paginatorProxy = new PaginatorProxy();

// 'Show more' pagination
function pagination_showmore(btn) {
    var params = {};
    params.offset = parseInt(btn.data('offset'), 10);
    params.orderby = btn.data('orderby');
    if (Number.isInteger(btn.data('group'))) {
        params.group = btn.data('group');
    }
    if (btn.data('institution').length) {
        params.institution = btn.data('institution');
    }
    sendjsonrequest(config['wwwroot'] + btn.data('jsonscript'), params, 'POST', function(data) {
        var btnid = btn.prop('id');
        btn.parent().replaceWith(data.data.tablerows);
        // we have a new 'showmore' button so wire it up
        jQuery('#' + btnid).on('click', function() {
            pagination_showmore(jQuery(this));
        });
        jQuery('#' + btnid).on('keydown', function(e) {
            if (e.keyCode == $j.ui.keyCode.SPACE || e.keyCode == $j.ui.keyCode.ENTER) {
                pagination_showmore(jQuery(this));
            }
        });
    });
}
