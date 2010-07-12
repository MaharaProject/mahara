/**
 * Provides functionality for pop-up GroupBoxes on Find Friend and My Friends pages.
 *
 * (C) 2009 Lancaster University Network Services Limited
 *                      http://www.luns.net.uk
 * This file is licensed under the same terms as Mahara itself
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


