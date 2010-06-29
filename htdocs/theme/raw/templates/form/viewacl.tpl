{{auto_escape off}}
<input type="hidden" name="accesslist" value="">
<div id="viewacl_lhs">
    <div id="potentialpresetitems"></div>
    <div>
        <label>{{str tag=search}}</label> <input type="text" name="search" id="search">
        <select name="type" id="type">
            <option value="group">{{str tag=groups}}</option>
            <option value="user" selected="selected">{{str tag=users}}</option>
        </select>
        <button id="dosearch" type="button">{{str tag=go}}</button>
        <table id="results" class="fullwidth">
            <thead>
                <tr>
                    <th></th>
                    <th>{{str tag=name}}</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
<h3>{{str tag=Added section=view}}</h3>
<div id="accesslistitems">
</div>

<script type="text/javascript">
var count = 0;

// Utility functions

// Given a row, render it on the left hand side
function renderPotentialPresetItem(item) {
    var addButton = BUTTON({'type': 'button'}, '{{str tag=add}}');
    var row = DIV(null, addButton, ' ', item.name);
    item.preset = true;

    if (item.type == 'token') {
        connect(addButton, 'onclick', function() {
            sendjsonrequest('newviewtoken.json.php', {'view': {{$viewid}}}, 'POST', function(data) {
                item.id = data.data.token;
                appendChildNodes('accesslist', renderAccessListItem(item));
            });
        });
        appendChildNodes(row, contextualHelpIcon('{{$formname}}', 'secreturl', 'core', 'view', null, null));
    }
    else {
        connect(addButton, 'onclick', function() {
            appendChildNodes('accesslist', renderAccessListItem(item));
        });
    }
    appendChildNodes('potentialpresetitems', row);

    return row;
}

// Given a row, render it on the right hand side
function renderAccessListItem(item) {
    var removeButton = BUTTON({'type': 'button'}, '{{str tag=remove}}');
    var allowfdbk = INPUT({
                        'type': 'checkbox',
                        'name': 'accesslist[' + count + '][allowfeedback]',
                        'id'  :  'allowfeedback' + count,
                        'value':  1});
    var approvefdbk = INPUT({
                        'type': 'checkbox',
                        'name': 'accesslist[' + count + '][approvefeedback]',
                        'id'  :  'approvefeedback' + count,
                        'value':  1});
    var dateInfo = TABLE(null,
        TBODY(null,
            TR(null,
                TH(null, get_string('From') + ':'),
                TD(null, makeCalendarInput(item, 'start'), makeCalendarLink(item, 'start'))
            ),
            TR(null,
                TH(null, get_string('To') + ':'),
                TD(null, makeCalendarInput(item, 'stop'), makeCalendarLink(item, 'stop'))
            ),
            TR(null,
                TH(null, null),
                TD(null, get_string('datetimeformatguide'))
            ),
            TR(null,
                TH(null, get_string('allowfeedback') + ':'),
                TD(null, allowfdbk)
            ),
            TR(null,
                TH(null, get_string('approvefeedback') + ':'),
                TD(null, approvefdbk)
            )
        )
    );

    if (item['approvefeedback']==1) {
        setNodeAttribute(allowfdbk,'checked',true);
    }
    if (item['allowfeedback']==1) {
        setNodeAttribute(approvefdbk,'checked',true);
    }
    var cssClass = 'ai-container';
    if (item.preset) {
        cssClass += '  preset';
    }
    cssClass += ' ' + item.type + '-container';
    if (item.type == 'token') {
        item.name = config.wwwroot + 'view/view.php?t=' + item.id;
    }
    var name = item.name;
    if (item.type == 'user') {
        name = [IMG({'src': config.wwwroot + 'thumb.php?type=profileicon&id=' + item.id + '&maxwidth=20&maxheight=20'}), ' ', name];
    }
    var row = TABLE({'class': cssClass},
        TBODY(null, 
            TR(null,
                TH(null, name,  (item.role != null ? ' - ' + item.roledisplay : '')),
                TD({'class': 'right removebutton'}, removeButton)
            ),
            TR(null,
                TD({'colspan': 2},
                    dateInfo,
                    INPUT({
                        'type': 'hidden',
                        'name': 'accesslist[' + count + '][type]',
                        'value': item.type
                    }),
                    (item.id ?
                    INPUT({
                        'type': 'hidden',
                        'name': 'accesslist[' + count + '][id]',
                        'value': item.id
                    })
                    :
                    null
                    ),
                    (item.role != null ?
                    INPUT({
                        'type': 'hidden',
                        'name': 'accesslist[' + count + '][role]',
                        'value': item.role
                    })
                    :
                    null
                    )
                )
            )
        )
    );

    connect(removeButton, 'onclick', function() {
        removeElement(row);
    });
    appendChildNodes('accesslistitems', row);

    setupCalendar(item, 'start');
    setupCalendar(item, 'stop');
    count++;
}

function makeCalendarInput(item, type) {
    return INPUT({
        'type':'text',
        'name': 'accesslist[' + count + '][' + type + 'date]',
        'id'  :  type + 'date_' + count,
        'value': item[type + 'date'] ? item[type + 'date'] : '',
        'size': '15'
    });
}

function makeCalendarLink(item, type) {
    var link = A({
        'href'   : '',
        'id'     : type + 'date_' + count + '_btn',
        'onclick': 'return false;', // @todo do with mochikit connect
        'class'  : 'pieform-calendar-toggle'},
        IMG({
            'src': '{{theme_url filename='images/calendar.gif'}}',
            'alt': ''})
    );
    return link;
}

function setupCalendar(item, type) {
    //log(type);
    var dateStatusFunc, selectedFunc;
    //if (type == 'start') {
    //    dateStatusFunc = function(date) {
    //        startDateDisallowed(date, $(item.id + '_stopdate'));
    //    };
    //    selectedFunc = function(calendar, date) {
    //        startSelected(calendar, date, $(item.id + '_startdate'), $(item.id + '_stopdate'));
    //    }
    //}
    //else {
    //    dateStatusFunc = function(date) {
    //        stopDateDisallowed(date, $(item.id + '_startdate'));
    //    };
    //    selectedFunc = function(calendar, date) {
    //        stopSelected(calendar, date, $(item.id + '_startdate'), $(item.id + '_stopdate'));
    //    }
    //}
    if (!$(type + 'date_' + count)) {
        logWarn('Couldn\'t find element: ' + type + 'date_' + count);
        return;
    }
    Calendar.setup({
        "ifFormat"  :"{{str tag=strftimedatetimeshort}}",
        "daFormat"  :"{{str tag=strftimedatetimeshort}}",
        "inputField": type + 'date_' + count,
        "button"    : type + 'date_' + count + '_btn',
        //"dateStatusFunc" : dateStatusFunc,
        //"onSelect"       : selectedFunc
        "showsTime" : true
    });
}

// SETUP

// Left top: public, loggedin, friends
var potentialPresets = {{$potentialpresets}};
forEach(potentialPresets, function(preset) {
    renderPotentialPresetItem(preset);
});
var loggedinindex = {{$loggedinindex}};
function ensure_loggedin_access() {
    var oldaccess = getFirstElementByTagAndClassName(null, 'loggedin-container', 'accesslistitems');
    if (oldaccess) {
        forEach(getElementsByTagAndClassName(null, 'loggedin-container', 'accesslistitems'), function (elem) {
            if (oldaccess != elem) {
                removeElement(elem);
            }
        });
    }
    else {
        renderAccessListItem(potentialPresets[loggedinindex]);
    }
    var newaccess = getFirstElementByTagAndClassName(null, 'loggedin-container', 'accesslistitems');
    addElementClass(getFirstElementByTagAndClassName(null, 'removebutton', newaccess), 'hidden');
    forEach(getElementsByTagAndClassName(null, 'pieform-calendar-toggle', newaccess), function (elem) { addElementClass(elem, 'hidden'); });
    forEach(getElementsByTagAndClassName('input', null, newaccess), function (elem) {
        if (elem.name.match(/\[st(art|op)date\]$/)) {
            elem.value = '';
            elem.disabled = true;
        }
    });
}
function relax_loggedin_access() {
    forEach(getElementsByTagAndClassName(null, 'loggedin-container', $('accesslistitems')), function (elem) {
        removeElementClass(getElementsByTagAndClassName(null, 'removebutton', elem)[0], 'hidden');
        forEach(getElementsByTagAndClassName(null, 'pieform-calendar-toggle', elem), function (elem1) { removeElementClass(elem1, 'hidden'); });
        forEach(getElementsByTagAndClassName('input', null, elem), function (elem1) { elem1.disabled = false; });
    });
}

// Left hand side
var searchTable = new TableRenderer(
    'results',
    'access.json.php',
    [
        undefined, undefined, undefined
    ]
);
searchTable.statevars.push('type');
searchTable.statevars.push('query');
searchTable.type = 'user';
searchTable.pagerOptions = {
    'firstPageString': '\u00AB',
    'previousPageString': '<',
    'nextPageString': '>',
    'lastPageString': '\u00BB',
    'linkOptions': {
        'href': '',
        'style': 'padding-left: 0.5ex; padding-right: 0.5ex;'
    }
}
searchTable.query = '';
searchTable.rowfunction = function(rowdata, rownumber, globaldata) {
    rowdata.type = searchTable.type;
    var buttonTD = TD({'style': 'white-space:nowrap;'});

    var addButton = BUTTON({'type': 'button', 'class': 'button'}, '{{str tag=add}}');
    connect(addButton, 'onclick', function() {
        appendChildNodes('accesslist', renderAccessListItem(rowdata));
    });
    appendChildNodes(buttonTD, addButton);

    var identityNodes = [], profileIcon = null, roleSelector = null;
    if (rowdata.type == 'user') {
        profileIcon = IMG({'src': config.wwwroot + 'thumb.php?type=profileicon&maxwidth=20&maxheight=20&id=' + rowdata.id});
        identityNodes.push(A({'href': config.wwwroot + 'user/view.php?id=' + rowdata.id, 'target': '_blank'}, rowdata.name));
    }
    else if (rowdata.type == 'group') {
        rowdata.role = null;
        var options = [OPTION({'value':null, 'selected':true}, '{{str tag=everyoneingroup section=view}}')];
        for (r in globaldata.roles[rowdata.grouptype]) {
            options.push(OPTION({'value':globaldata.roles[rowdata.grouptype][r].name}, globaldata.roles[rowdata.grouptype][r].display));
        }
        roleSelector = SELECT({'name':'role'}, options);
        connect(roleSelector, 'onchange', function() {
            rowdata.role = this.value;
            if (this.value) {
                rowdata.roledisplay = scrapeText(this.childNodes[this.selectedIndex]);
            }
        });
        identityNodes.push(A({'href': config.wwwroot + 'group/view.php?id=' + rowdata.id, 'target': '_blank'}, rowdata.name));
        identityNodes.push(" - ");
        identityNodes.push(roleSelector);
    }

    return TR({'class': 'r' + (rownumber % 2)},
        buttonTD,
        TD({'style': 'vertical-align: middle;'}, identityNodes),
        TD({'class': 'center', 'style': 'vertical-align: top;width:20px;'}, profileIcon)
    );
}
searchTable.updateOnLoad();

function search(e) {
    searchTable.query = $('search').value;
    searchTable.type  = $('type').options[$('type').selectedIndex].value;
    searchTable.doupdate();
    e.stop();
}


// Right hand side
addLoadEvent(function () {
    var accesslist = {{$accesslist}};
    if (accesslist) {
        forEach(accesslist, function(item) {
            renderAccessListItem(item);
        });
    }
    update_loggedin_access();
});

addLoadEvent(function() {
    // Populate the "potential access" things (public|loggedin|allfreidns)

    connect($('search'), 'onkeydown', function(e) {
        if (e.key().string == 'KEY_ENTER') {
            search(e);
        }
    });
    connect($('dosearch'), 'onclick', search);
});

</script>
{{/auto_escape}}
