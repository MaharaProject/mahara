/**
 * Provides functionality for pop-up GroupBoxes on Find Friend and My
 * Friends pages.
 * @source: http://gitorious.org/mahara/mahara
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
}

var ul = null;
var initialgroups = new Array();

function showGroupBox(event, user_id) {
    replaceChildNodes('messages');

    if (event.preventDefault) {
        event.preventDefault();
    } else {
        event.returnValue = false;
    }

    if (!$('groupbox')) {
        appendChildNodes(
            getFirstElementByTagAndClassName('body'),
            DIV({'id':'groupbox','class':'groupbox hidden'})
        );
    }

    if (hasElementClass('groupbox', 'hidden')) {
        getitems(user_id, function() {
            removeElementClass('groupbox', 'hidden');
        });
    }
    else {
        addElementClass('groupbox', 'hidden');
    }
    return false;
}

function changemembership(event, user_id, type) {
    replaceChildNodes('messages');

    if (event.preventDefault) {
        event.preventDefault();
    } else {
        event.returnValue = false;
    }

    var groups = document.getElementsByName(type+'group_'+user_id);
    var resultgroups = new Array();

    forEach(groups, function(group) {
        if (group.checked == true ) {
            resultgroups.push(group.value);
        }
    });
    // apply changes only if something has been changed
    if (!initialgroups[type].compare(resultgroups)){
        sendjsonrequest('../group/changegroupsmembership.json.php',
        {
            'jointype':type,
            'userid':user_id,
            'resultgroups':resultgroups.join(','),
            'initialgroups':initialgroups[type].join(',')
            }, 'POST',
        function() {
            addElementClass('groupbox', 'hidden');
        });
    }
}

function getitems(user_id, successfunction) {
    sendjsonrequest('../group/controlledgroups.json.php', {
        'userid':user_id
    }, 'GET',
    function(data) {
        replaceChildNodes('groupbox');
        $('groupbox').innerHTML = data.data.html;

        var jt = getElementsByTagAndClassName('div', 'jointype', 'groupbox');
        var jtwidth = 300;
        forEach(jt, function(elem) { setStyle(elem, {'width': jtwidth + 'px'}); });

        var gbwidth = jt.length == 2 ? 640 : 315;
        var d = getElementDimensions('groupbox');
        var vpdim = getViewportDimensions();
        var newtop = getViewportPosition().y + Math.max((vpdim.h - d.h) / 2, 5);
        setStyle('groupbox', {
            'width': gbwidth + 'px',
            'left': (vpdim.w - d.w) / 2 + 'px',
            'top': newtop + 'px',
            'position': 'absolute'
        });
        initialgroups = data.data.initialgroups;
        successfunction();
    });
}


