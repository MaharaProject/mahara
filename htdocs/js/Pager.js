/**
 * Pager module for MochiKit
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

try {
    if (
        typeof(MochiKit.Base)   == 'undefined' ||
        typeof(MochiKit.DOM)    == 'undefined' ||
        typeof(MochiKit.Signal) == 'undefined'
    ) {
        throw "";
    }
} catch (e) {
    throw "MochiKit.Pagination depends on MochiKit.Base, MochiKit.DOM and MochiKit.Signal!";
}

if (typeof(MochiKit.Pagination) == 'undefined') {
    MochiKit.Pagination = {};
}

MochiKit.Pagination.NAME = 'Pagination';
MochiKit.Pagination.VERSION = '0.1';
MochiKit.Pagination.__repr__ = function () { return '[' + this.NAME + ' ' + this.VERSION + ']'; };
MochiKit.Pagination.toString = function () { return this.__repr__(); };
MochiKit.Pagination.EXPORT = [
    'Pager'
];

MochiKit.Pagination.Pager = function (count, limit, options) {
    var cls = arguments.callee;
    if (!(this instanceof cls)) {
        return new cls(count, limit, options);
    }
    this.__init__(count, limit, options);
};

MochiKit.Pagination.Pager.prototype = {
    __class__: MochiKit.Pagination.Pager,
    __init__: function (count, limit, options) {
        var d = MochiKit.DOM;
        var b = MochiKit.Base;

        this.options = b.update({
            currentPage: 1,
            linkOptions: {
                'href': '',
                'style': 'padding-left: 1ex; padding-right: 1ex;'
            },
            currentPageOptions: {
                'style': 'padding-left: 1ex; padding-right: 1ex;'
            },
            displayFirstAndLast: true,
            firstPageString: '<<',
            previousPageString: '<',
            nextPageString: '>',
            lastPageString: '>>',
            morePagesString: '...', // can be null
            pageChangeCallback: null,
            contextPageCount: 2
        }, options || {});

        this.options.lastPage = Math.floor( ( count - 1 ) / limit ) + 1;
        if (this.options.lastPage < 1) {
            this.options.lastPage = 1;
        }
        this.instances = [];
    },
    repr: function () {
        return '[' + this.__class__.NAME + ", options:" + MochiKit.Base.repr(this.options) + "]";
    },
    dump: function() {
        logDebug(this.instances);
    },
    newDisplayInstance: function() {
        var instance = DIV({'style': 'text-align: center'});
        this.instances.push(instance);

        this.renderInstances();

        return instance;
    },
    goToPage: function(pageNumber, e) {
        this.options.currentPage = pageNumber;
        if (typeof(this.options.pageChangeCallback) == 'function') {
            this.options.pageChangeCallback(pageNumber);
        }
        this.renderInstances();
        e.stop();
    },
    renderNumber: function(n) {
        var page;

        if ( n == this.options.currentPage ) {
            page = SPAN(this.options.currentPageOptions, n);
        }
        else {
            page = A(this.options.linkOptions, n);
            connect(page, 'onclick', this, partial(this.goToPage, n));
        }
        return page;
    },
    removeAllInstances: function() {
        forEach(this.instances, function(instance) {
            removeElement(instance);
        });
    },
    renderInstances: function() {
        var options = this.options;
        var goToPage = this.goToPage;
        var self = this;
        forEach(this.instances, function (instance) {
            var childNodes = [];
            var morePagesAfter = false;
            var morePagesBefore = false;
            var i;

            if ( options.lastPage <= options.contextPageCount * 2 + 1 ) {
                // we just always display all numbers (there's not that many)
                for ( i = 1; i <= options.lastPage; i = i + 1 ) {
                    childNodes.push(self.renderNumber(i));
                }
            }
            else if ( options.currentPage <= options.contextPageCount + 1 ) {
                // the case where we display 1 to contextPageCount*2+1
                morePagesAfter = true;
                for ( i = 1; i <= options.contextPageCount * 2 + 1; i = i + 1 ) {
                    childNodes.push(self.renderNumber(i));
                }
            }
            else if ( options.currentPage >= options.lastPage - options.contextPageCount ) {
                // the case where we display lastPage - contextPageCount*2
                morePagesBefore = true;
                for ( i = options.lastPage - options.contextPageCount * 2; i <= options.lastPage; i = i + 1 ) {
                    childNodes.push(self.renderNumber(i));
                }
            }
            else {
                // we're somewhere in the middle
                morePagesAfter = true;
                morePagesBefore = true;
                for ( i = options.currentPage - options.contextPageCount; i <= options.currentPage + options.contextPageCount; i = i + 1 ) {
                    childNodes.push(self.renderNumber(i));
                }
            }

            if (options.morePagesString) {
                if (morePagesBefore) {
                    childNodes.unshift(SPAN(null, options.morePagesString));
                }
                else {
                    childNodes.unshift(SPAN({'style': 'visibility: hidden'}, options.morePagesString));
                }
                if (morePagesAfter) {
                    childNodes.push(SPAN(null, options.morePagesString));
                }
                else {
                    childNodes.push(SPAN({'style': 'visibility: hidden'}, options.morePagesString));
                }
            }

            var previousPage = A(options.linkOptions, options.previousPageString);
            if (options.currentPage == 1) {
                previousPage.style.visibility = 'hidden';
            }
            connect(previousPage, 'onclick', self, partial(goToPage, options.currentPage - 1));
            childNodes.unshift(previousPage);
            if (options.displayFirstAndLast) {
                var firstPage = A(options.linkOptions, options.firstPageString);
                if (options.currentPage == 1) {
                    firstPage.style.visibility = 'hidden';
                }
                connect(firstPage, 'onclick', self, partial(goToPage, 1));
                childNodes.unshift(firstPage);
            }

            var nextPage = A(options.linkOptions, options.nextPageString);
            if (options.currentPage == options.lastPage) {
                nextPage.style.visibility = 'hidden';
            }
            connect(nextPage, 'onclick', self, partial(goToPage, options.currentPage + 1));
            childNodes.push(nextPage);
            if (options.displayFirstAndLast) {
                var lastPage     = A(options.linkOptions, options.lastPageString);
                if (options.currentPage == options.lastPage) {
                    lastPage.style.visibility = 'hidden';
                }
                connect(lastPage, 'onclick', self, partial(goToPage, options.lastPage));
                childNodes.push(lastPage);
            }

            forEach(instance.childNodes, function(node) {
                disconnectAll(node);
            });
            replaceChildNodes(instance, childNodes);
        });
    }
};

MochiKit.Pagination.__new__ = function () {
    MochiKit.Base.nameFunctions(this);

    this.EXPORT_TAGS = {
        ":common": this.EXPORT,
        ":all": MochiKit.Base.concat(this.EXPORT, this.EXPORT_OK)
    };
};

MochiKit.Pagination.__new__();

MochiKit.Base._exportSymbols(this, MochiKit.Pagination);
