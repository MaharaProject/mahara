<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'managegroups/categories');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('groupcategories', 'admin'));

$optionform = pieform(array(
    'name'       => 'groupcategories',
    'renderer'   => 'div',
    'class'      => '',
    'plugintype' => 'core',
    'pluginname' => 'admin',
    'elements'   => array(
        'allowgroupcategories' => array(
            'class'        => 'with-label-widthauto',
            'type'         => 'switchbox',
            'title'        => get_string('enablegroupcategories', 'admin'),
            'defaultvalue' => get_config('allowgroupcategories'),
        ),
        'submit' => array(
            'class'        => 'btn-primary',
            'type'         => 'submit',
            'value'        => get_string('submit'),
        ),
    )
));

function groupcategories_submit(Pieform $form, $values) {
    set_config('allowgroupcategories', (int) $values['allowgroupcategories']);
    redirect(get_config('wwwroot') . 'admin/groups/groupcategories.php');
}

$strings = array('edit', 'delete', 'update', 'cancel', 'add', 'name', 'unknownerror');
$adminstrings = array('confirmdeletecategory', 'deletefailed', 'addnewgroupcategory');
$argumentstrings = array('editspecific', 'deletespecific');
foreach ($strings as $string) {
    $getstring[$string] = json_encode(get_string($string));
}
foreach ($adminstrings as $string) {
    $getstring[$string] = json_encode(get_string($string, 'admin'));
}
foreach ($argumentstrings as $string) {
    $getstring[$string] = json_encode(get_string($string, 'mahara', '%s'));
}

$thead = array(json_encode(get_string('name', 'admin')), '""');
$ijs = "var thead = TR(null,map(partial(TH,null),[" . implode($thead,",") . "]));\n";

$ijs .= <<< EOJS
// Request a list of menu items from the server
function getitems(r) {
    sendjsonrequest('getgroupcategories.json.php', {}, 'GET', function(data) {
        data.focusid = (typeof r != 'undefined') ? r.id : false;
        displaymenuitems(data);
    });
}


// Puts the list of menu items into the empty table.
function displaymenuitems(data) {
    var itemlist = data.groupcategories;
    var rows = map(formatrow,itemlist);
    var form = FORM({'id':'form','method':'post','enctype':'multipart/form-data',
                         'encoding':'multipart/form-data'},
                    TABLE({'class':'nohead table table-short'},TBODY(null,[thead,rows,addform()])));
    replaceChildNodes($('menuitemlist'),form);
    if (data.focusid) {
        $('item' + data.focusid).focus();
    }
}

// Creates one table row
function formatrow (item) {
    // item has class, id, type, name, link, linkedto

    var edit = BUTTON({
        'class':'btn btn-default btn-sm',
        'id':'item' + item.id,
        'type':'button',
        'title':{$getstring['edit']},
        'alt':{$getstring['editspecific']}.replace('%s', item.name)},
            SPAN({'class':'icon icon-cog icon-lg', 'role':'presentation'}),
            SPAN({'class':'sr-only'}, {$getstring['editspecific']}.replace('%s', item.name))
        );

    connect(edit, 'onclick', function (e) { e.stop(); edititem(item); });


    var del = BUTTON({
        'class':'btn btn-default btn-sm',
        'id':'item' + item.id,
        'type':'button',
        'title':{$getstring['delete']},
        'alt':{$getstring['deletespecific']}.replace('%s', item.name)},
            SPAN({'class':'icon icon-trash text-danger icon-lg','role':'presentation'}),
            SPAN({'class':'sr-only'}, {$getstring['deletespecific']}.replace('%s', item.name))
        );

    connect(del, 'onclick', function (e) { e.stop(); delitem(item.id); });

    var buttongroup = SPAN({'class': 'btn-group'}, edit, del);

    var cells = map(
        partial(TD,null),
        [
            item.name,
            [buttongroup]
        ]
    );
    return TR({'id':'menuitem_'+item.id},cells);
}

// Returns the form which adds a new menu item
function addform(type) {
    var item = {'id':'new'};
    return editform(item);
}

// Creates the contents of a menu item edit form
// This is formatted as a table within the form (which is within a row of the table).
function editform(item) {
    // item has id, name

    // Either a save, a cancel button, or both.
    var savecancel = [];
    var save = INPUT({'type':'button','class':'button btn btn-default btn-add-group'});
    connect(save, 'onclick', function () { saveitem(item.id); });

    var rowtype = 'add';
    if (!item.name) {
        item.name = '';
        item.label = {$getstring['addnewgroupcategory']};
        // The save button says 'add', and there's no cancel button.
        setNodeAttribute(save,'value',{$getstring['add']});
        savecancel = [save];
    }
    else { // Editing an existing menu item.
        // The save button says 'update' and there's a cancel button.
        var rowtype = 'edit';
        setNodeAttribute(save,'value',{$getstring['update']});
        var cancel = INPUT({'type':'button','class':'button btn btn-sm btn-default','value':{$getstring['cancel']}});
        connect(cancel, 'onclick', closeopenedits);
        savecancel = [save,cancel];
        item.label = {$getstring['edit']};
    }

    // A text field for the name
    var label = LABEL({'for':'name'+item.id,'class':'accessible-hidden'}, null, item.label);
    var name = INPUT({'type':'text','class':'text form-control input-sm','id':'name'+item.id,'value':item.name});
    jQuery(name).keydown(function(e) {
        if (e.keyCode == 13) {
            signal(save, 'onclick');
            e.preventDefault();
        }
    });
    var parentspan = createDOM('span',null,label,name);
    var row = TR({'id':'row'+item.id, 'class':rowtype},
                 map(partial(TD,null),[parentspan,savecancel]));
    return row;
}

// Close all open edit forms
function closeopenedits() {
    var rows = getElementsByTagAndClassName('tr',null,$('menuitemlist'));
    for (var i=0; i<rows.length; i++) {
        if (hasElementClass(rows[i],'edit')) {
            removeElement(rows[i]);
        }
        else if (hasElementClass(rows[i],'hidden')) {
            removeElementClass(rows[i],'hidden');
        }
    }
}

// Open a new edit form
function edititem(item) {
    closeopenedits();
    var menuitem = $('menuitem_'+item.id);
    addElementClass(menuitem,'hidden');
    var newrow = editform(item);
    insertSiblingNodesBefore(menuitem, newrow);
    $('name' + item.id).focus();
}

// Receive standard json error message
// Request deletion of a menu item from the db
function delitem(itemid) {
    if (confirm({$getstring['confirmdeletecategory']})) {
        sendjsonrequest('deletegroupcategory.json.php',{'itemid':itemid}, 'POST', getitems);
    }
}

// Send the menu item in the form to the database.
function saveitem(itemid) {
    var f = $('form');
    var name = $('name'+itemid).value;
    if (name == '') {
        displayMessage(get_string('namedfieldempty', 'mahara', {$getstring['name']}), 'error');
        return false;
    }

    var data = {'name':name,
                'itemid':itemid};
    sendjsonrequest('updategroup.json.php', data, 'POST', function(r) {
        getitems(r);
    });
    return false;
}

addLoadEvent(function () {
    getitems();
});
EOJS;

$smarty = smarty();
setpageicon($smarty, 'icon-users');

$smarty->assign('PAGEHEADING', hsc(get_string('groupcategories', 'admin')));
$smarty->assign('INLINEJAVASCRIPT', $ijs);
$smarty->assign('optionform', $optionform);
$smarty->display('admin/groups/groupcategories.tpl');
