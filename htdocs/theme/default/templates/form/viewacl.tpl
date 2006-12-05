<div style="float:left;">
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
    var row = DIV(item.name, removeButton);

    connect(removeButton, 'onclick', function() {
        removeElement(row);
    });
    insertSiblingNodesBefore($('accesslistitems').firstChild, row);
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
