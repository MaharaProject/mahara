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
var count = 0;

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
function renderAccessListItem(item) {
    var removeButton = BUTTON({'type': 'button'}, '{{str tag=remove}}');
    var dateDiv = DIV(null,
        makeCalendarInput(item, 'start'),
        makeCalendarLink(item, 'start'),
        makeCalendarInput(item, 'stop'),
        makeCalendarLink(item, 'stop')
    );
    var row = DIV(null,
        item.name,
        removeButton,
        dateDiv,
        INPUT({
            'type': 'hidden',
            'name': 'accesslist[' + count + '][type]',
            'value': item.type
        }),
        INPUT({
            'type': 'hidden',
            'name': 'accesslist[' + count + '][id]',
            'value': item.id})
    );

    connect(removeButton, 'onclick', function() {
        removeElement(row);
    });
    insertSiblingNodesAfter($('accesslistitems').lastChild, row);
    
    setupCalendar(item, 'start');
    setupCalendar(item, 'stop');
    count++;
}

function makeCalendarInput(item, type) {
    return INPUT({
        'type':'text',
        'name': 'accesslist[' + count + '][' + type + 'date]',
        'id'  :  type + 'date_' + count,
        'value': typeof(item[type + 'date']) != 'undefined' ? item[type + 'date'] : ''
    });
}

function makeCalendarLink(item, type) {
    var link = A({
        'href'   : '',
        'id'     : type + 'date_' + count + '_btn',
        'onclick': 'return false;', // @todo do with mochikit connect
        'class'  : 'pieform-calendar-toggle'},
        IMG({
            'src': '{{$THEMEURL}}calendar.gif',
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
        undefined, undefined
    ]
);
searchTable.statevars.push('type');
searchTable.statevars.push('query');
searchTable.type = 'user';
searchTable.query = '';
searchTable.rowfunction = function(rowdata, rownumber, globaldata) {
    var addButton = BUTTON({'type': 'button'}, '{{str tag=add}}');
    rowdata.type = searchTable.type;
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
