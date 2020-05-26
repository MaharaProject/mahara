/**
 * Provides functionality for pop-up GroupBoxes on Find Friend and My
 * Friends pages.
 *
 * @licstart
 * Copyright (C) 2009-2010 Lancaster University Network Services Ltd
 *                         http://www.luns.net.uk
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
jQuery(function($) {
"use strict";

    // array compare method
    Array.prototype.compare = function(testArr) {
        if (this.length != testArr.length) return false;
        for (var i = 0; i < testArr.length; i++) {
            if (this[i].compare) {
                if (!this[i].compare(testArr[i])) return false;
            }
            if (this[i] !== testArr[i]) return false;
        }
        return true;
    };

    var initialgroups = new Array();

    function getAllCheckedElemValues (groups) {
        var resultgroups = new Array();
        for (var i = 0; i < groups.length; i++ ) {
            if (groups[i].checked) {
                resultgroups.push(groups[i].value);
            }
        }
        return resultgroups;
    }

    // Initialise edit group modal
    function setupEditGroupMembership() {
        $('.js-edit-group').on('click', function(e){
            e.preventDefault();
            var userid = $(this).data('userid');
            if ($('.js-editgroup-' + userid).length) {
                $('.js-editgroup-' + userid).remove();
            }
            sendjsonrequest('../group/controlledgroups.json.php', {
            'userid':userid
            }, 'GET', function(data) {
                initialgroups = data.data.initialgroups;
                $(data.data.html).modal('show');
            });
        });
    }

    // Change membership
    $('body').on('click', '.js-editgroup-submit', function(e) {
        e.preventDefault();
        var userid = $(this).data('userid');
        var addtype = $(this).data('addtype');
        var groups = $('[name="' + addtype +'group_'+ userid + '"]');
        var resultgroups = getAllCheckedElemValues(groups);
        // apply changes only if something has been changed
        if (!initialgroups[addtype].compare(resultgroups)){
            sendjsonrequest('../group/changegroupsmembership.json.php',
                {
                    'addtype': addtype,
                    'userid': userid,
                    'resultgroups': resultgroups.join(','),
                    'initialgroups': initialgroups[addtype].join(',')
                }, 'POST',
            function() {
                $('.modal.show').modal('hide');
            });
        }
    });

    // reattach listeners when page has finished updating
    jQuery(window).on('pageupdated', {}, function() {
        setupEditGroupMembership();
    });

    setupEditGroupMembership();
});


