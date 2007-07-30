<input type="hidden" name="accesslist" value="">
<div id="viewacl_lhs">
    <div id="potentialpresetitems"></div>
    <div>
        {{str tag=search}} <input type="text" name="search" id="search">
        <select name="type" id="type">
            <option value="group">{{str tag=mygroups}}</option>
            <option value="community">{{str tag=communities}}</option>
            <option value="user" selected="selected">{{str tag=users}}</option>
        </select>
        <button id="dosearch" type="button">{{str tag=go}}</button>
        <table id="results">
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
<div id="accesslistitems">
</div>

<script type="text/javascript">
var count = 0;

// Utility functions

// Given a row, render it on the left hand side
function renderPotentialPresetItem(item) {
    var addButton = BUTTON({'type': 'button'}, '{{str tag=add}}');
    var row = DIV(null, addButton, ' ', item.name);

    connect(addButton, 'onclick', function() {
        appendChildNodes('accesslist', renderAccessListItem(item));
    });
    appendChildNodes('potentialpresetitems', row);

    return row;
}

// Given a row, render it on the right hand side
function renderAccessListItem(item) {
    var removeButton = BUTTON({'type': 'button'}, '{{str tag=remove}}');
    var dateDiv = DIV(null,
        makeCalendarInput(item, 'start'),
        makeCalendarLink(item, 'start'),
        makeCalendarInput(item, 'stop'),
        makeCalendarLink(item, 'stop')
    );
    var row = TABLE(null,
        TBODY(null, 
            TR(null,
                TH(null, item.name + (item.tutoronly ? ' ' + '{{str tag=tutors}}' : '')),
                TD({'class': 'right'}, removeButton)
            ),
            TR(null,
                TD({'colspan': 2},
                    dateDiv,
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
                    (typeof(item.tutoronly) != 'undefined' ?
                    INPUT({
                        'type': 'hidden',
                        'name': 'accesslist[' + count + '][tutoronly]',
                        'value': item.tutoronly
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
        'value': item[type + 'date'] ? item[type + 'date'] : ''
    });
}

function makeCalendarLink(item, type) {
    var link = A({
        'href'   : '',
        'id'     : type + 'date_' + count + '_btn',
        'onclick': 'return false;', // @todo do with mochikit connect
        'class'  : 'pieform-calendar-toggle'},
        IMG({
            'src': '{{theme_path location='images/calendar.gif'}}',
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
        "ifFormat"  :"%Y\/%m\/%d %H:%M",
        "daFormat"  :"%Y\/%m\/%d %H:%M",
        "inputField": type + 'date_' + count,
        "button"    : type + 'date_' + count + '_btn',
        //"dateStatusFunc" : dateStatusFunc,
        //"onSelect"       : selectedFunc
        "showTimes" : true
    });
}

// SETUP

// Left top: public, loggedin, friends
var potentialPresets = {{$potentialpresets}};
forEach(potentialPresets, function(preset) {
    renderPotentialPresetItem(preset);
});

// Left hand side
var searchTable = new TableRenderer(
    'results',
    'create4.json.php',
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
        rowdata.tutoronly = 0;
        appendChildNodes('accesslist', renderAccessListItem(rowdata));
    });
    appendChildNodes(buttonTD, addButton);

    var profileIcon, tutorAddButton = null;
    if (rowdata.type == 'user') {
        profileIcon = IMG({'src': config.wwwroot + 'thumb.php?type=profileicon&size=40x40&id=' + rowdata.id});
    }
    else if (rowdata.type == 'community') {
        tutorAddButton = BUTTON({'type': 'button', 'class': 'button'}, '{{str tag=addtutors section=view}}');
        connect(tutorAddButton, 'onclick', function() {
            rowdata.tutoronly = 1;
            appendChildNodes('accesslist', renderAccessListItem(rowdata));
        });
        appendChildNodes(buttonTD, tutorAddButton);
    }

    return TR({'class': 'r' + (rownumber % 2)},
        buttonTD,
        TD({'class': 'fullwidth'}, rowdata.name),
        TD({'class': 'center', 'style': 'width:40px'}, profileIcon)
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
            log(item);
            renderAccessListItem(item);
        });
    }
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
