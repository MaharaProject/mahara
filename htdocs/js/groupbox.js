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
var reversetypes = {'invite':'controlled','controlled':'invite'};

function showGroupBox(event, user_id, type) {
    replaceChildNodes('messages');

    if (event.preventDefault) {
        event.preventDefault();
    } else {
        event.returnValue = false;
    }

    ul = $(type + 'groupbox_' + user_id).getElementsByTagName('ul')[0];

    if (getStyle($(type + 'groupbox_' + user_id), 'display') == 'block'){
        hideElement($(type + 'groupbox_' + user_id));
    }
    else {
        hideElement($(reversetypes[type] + 'groupbox_' + user_id));
        getitems(user_id, type, function() {
            showElement($(type + 'groupbox_' + user_id));
        });
    }
}

function changemembership(event, user_id, type) {
    replaceChildNodes('messages');

    if (event.preventDefault) {
        event.preventDefault();
    } else {
        event.returnValue = false;
    }

    ul = $(type + 'groupbox_' + user_id).getElementsByTagName('ul')[0];

    var groups = document.getElementsByName(type+'group_'+user_id);
    var resultgroups = new Array();

    forEach(groups, function(group) {
        if (group.checked == true ) {
            resultgroups.push(group.value);
        }
    });
    // apply changes only if something has been changed
    if (!initialgroups[user_id].compare(resultgroups)){
        sendjsonrequest('../group/changegroupsmembership.json.php',
        {
            'jointype':type,
            'userid':user_id,
            'resultgroups':resultgroups.join(','),
            'initialgroups':initialgroups[user_id].join(',')
            }, 'POST',
        function() {
            getitems(user_id, type, function() {});
        });
    }
}

function getitems(user_id, type, successfunction) {
    sendjsonrequest('../group/controlledgroups.json.php', {
        'userid':user_id,
        'jointype':type
    }, 'GET',
    function(groups) {
        var results = new Array();
        initialgroups[user_id] = [];

        if (groups.data == false) {
            results.push(LI(get_string('nogroups')));
        }
        else {
            forEach(groups.data, function(group) {
                var li = LI('');
                var input = INPUT({
                    'type':'checkbox',
                    'class':'checkbox',
                    'name':type+'group_'+user_id,
                    'value':group.id
                    });
                if (group.member || group.invited) {
                    input.checked = true;
                    initialgroups[user_id].push(group.id);
                }
                appendChildNodes(li, input, '\u00A0\u00A0', group.name);
                if (group.invited || (type == 'invite' && group.member) || (group.role == 'tutor' && ((group.memberrole == 'member' && input.checked) || (group.member && group.memberrole != 'member')))) {
                    li.setAttribute('class', 'disabled');
                    input.disabled = true;
                }
                results.push(li);
            });
            var a = A({
                'href':'#'
            }, '\u00A0\u00A0', get_string('applychanges'));
            a.setAttribute('onclick', 'changemembership(event, '+user_id+', \''+type+'\');');
            results.push(LI({}, a));
        }

        replaceChildNodes(ul, results);
        if (ul.childNodes.length) ul.lastChild.className = 'last';
        successfunction();
    });
}


