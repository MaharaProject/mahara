<div id="viewacl_lhs">
    <div id="potentialpresetitems"></div>
    <div>
        {{str tag=search}} <input type="text" name="search" id="search">
        <select name="type" id="type">
            <option value="group">{{str tag=mygroups}}</option>
            <option value="community">{{str tag=communities}}</option>
            <option value="user">{{str tag=users}}</option>
            <option value="all" selected="selected">{{str tag=all}}</option>
        </select>
        <button id="dosearch" type="button">{{str tag=go}}</button>
        <table id="results">
            <thead>
                <tr>
                    <th>{{str tag=name}}</th>
                </tr>
            </thead>
            <tbody>
            </tbody>
        </table>
    </div>
</div>
<div id="accesslistitems">
</div>

<script type="text/javascript" src="/js/tablerenderer.js"></script>
<script type="text/javascript">
// Utility functions

// Given a row, render it on the left hand side
// The LHS looks like: [ name   ][ button ]
function renderPotentialPresetItem(item) {
    var addButton = BUTTON({'type': 'button'}, '{{str tag=add}}');
    var row = DIV(null, item.name, addButton);

    connect(addButton, 'onclick', function() {
        appendChildNodes('accesslist', renderAccessListItem(item));
    });
    appendChildNodes('potentialpresetitems', row);

    return row;
}

// Given a row, render it on the right hand side
// The RHS looks like:
//  [ name     ][ button ]
//  | from  v   v   v    |
//  | to    v   v   v    |
function renderAccessListItem(item) {
    var removeButton = BUTTON({'type': 'button'}, '{{str tag=remove}}');
    var dateDiv = DIV(null,
        makeCalendarInput(item, 'start'),
        makeCalendarLink(item, 'start'),
        makeCalendarInput(item, 'stop'),
        makeCalendarLink(item, 'stop')
    );
    var row = DIV(null, item.name, removeButton, dateDiv);

    connect(removeButton, 'onclick', function() {
        removeElement(row);
    });
    insertSiblingNodesBefore($('accesslistitems').firstChild, row);
    
    setupCalendar(item, 'start');
    setupCalendar(item, 'stop');
}

function makeCalendarInput(item, type) {
    return INPUT({
        'type':'text',
        'name': item.id + '_' + type + 'date',
        'id'  : item.id + '_' + type + 'date'
    });
}

function makeCalendarLink(item, type) {
    var link = A({
        'href'   : '',
        'id'     : item.id + '_' + type + 'date_btn',
        'onclick': 'return false;',
        'class'  : 'pieform-calendar-toggle'},
        IMG({
            'src': '{{$THEMEURL}}calendar.gif',
            'alt': ''})
    );
    return link;
}

function setupCalendar(item, type) {
    log(type);
    var dateStatusFunc, selectedFunc;
    if (type == 'start') {
        dateStatusFunc = function(date) {
            startDateDisallowed(date, $(item.id + '_stopdate'));
        };
        selectedFunc = function(calendar, date) {
            startSelected(calendar, date, $(item.id + '_startdate'), $(item.id + '_stopdate'));
        }
    }
    else {
        dateStatusFunc = function(date) {
            stopDateDisallowed(date, $(item.id + '_startdate'));
        };
        selectedFunc = function(calendar, date) {
            stopSelected(calendar, date, $(item.id + '_startdate'), $(item.id + '_stopdate'));
        }
    }
    Calendar.setup({
        "ifFormat"  :"%Y\/%m\/%d",
        "daFormat"  :"%Y\/%m\/%d",
        "inputField": item.id + '_' + type + 'date',
        "button"    : item.id + '_' + type + 'date_btn',
        "dateStatusFunc" : dateStatusFunc,
        "onSelect"       : selectedFunc
    });
}

// SETUP

// Left top: public, any, friends
var potentialPresets = {{$potentialpresets}};
forEach(potentialPresets, function(preset) {
    renderPotentialPresetItem(preset);
});

// Left hand side
var searchTable = new TableRenderer(
    'results',
    'create4.json.php',
    [
        undefined, undefined
    ]
);
searchTable.statevars.push('type');
searchTable.statevars.push('query');
searchTable.type = 'all';
searchTable.query = '';
searchTable.rowfunction = function(rowdata, rownumber, globaldata) {
    var addButton = BUTTON({'type': 'button'}, '{{str tag=add}}');
    connect(addButton, 'onclick', function() {
        appendChildNodes('accesslist', renderAccessListItem(rowdata));
    });
    return TR(null, TD(null, rowdata.name, addButton));
}
searchTable.updateOnLoad();

function search(e) {
    searchTable.query = $('search').value;
    searchTable.type  = $('type').options[$('type').selectedIndex].value;
    searchTable.doupdate();
    e.stop();
}


// Right hand side
var accesslist = {{$accesslist}};
if (accesslist) {
    forEach(accesslist, function(item) {
        renderAccessListItem(item);
    });
}

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
