<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'managegroups/categories');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('groups', 'admin'));

$optionform = pieform(array(
    'name'       => 'groupcategories',
    'renderer'   => 'table',
    'plugintype' => 'core',
    'pluginname' => 'admin',
    'elements'   => array(
        'allowgroupcategories' => array(
            'type'         => 'checkbox',
            'title'        => get_string('enablegroupcategories', 'admin'),
            'defaultvalue' => get_config('allowgroupcategories'),
        ),
        'submit' => array(
            'type'         => 'submit',
            'value'        => get_string('submit'),
        ),
    )
));

function groupcategories_submit(Pieform $form, $values) {
    set_config('allowgroupcategories', (int) $values['allowgroupcategories']);
    redirect(get_config('wwwroot') . 'admin/groups/groupcategories.php');
}

$strings = array('edit','delete','update','cancel','add','name','unknownerror');
$adminstrings = array('confirmdeletecategory', 'deletefailed');
foreach ($strings as $string) {
    $getstring[$string] = json_encode(get_string($string));
}
foreach ($adminstrings as $string) {
    $getstring[$string] = json_encode(get_string($string, 'admin'));
}

$thead = array(json_encode(get_string('name', 'admin')), '""');
$ijs = "var thead = TR(null,map(partial(TH,null),[" . implode($thead,",") . "]));\n";

$ijs .= <<< EOJS
// Request a list of menu items from the server
function getitems() {
    sendjsonrequest('getgroupcategories.json.php', {}, 'GET',
                    function(data) { displaymenuitems(data.groupcategories); });
}


// Puts the list of menu items into the empty table.
function displaymenuitems(itemlist) {
    var rows = map(formatrow,itemlist);
    var form = FORM({'id':'form','method':'post','enctype':'multipart/form-data',
                         'encoding':'multipart/form-data'},
                    TABLE({'class':'nohead'},TBODY(null,[thead,rows,addform()])));
    replaceChildNodes($('menuitemlist'),form);
}

// Creates one table row
function formatrow (item) {
    // item has id, type, name, link, linkedto
    var edit = INPUT({'type':'image','src':config.theme['images/edit.gif'],'title':{$getstring['edit']}});
    connect(edit, 'onclick', function (e) { e.stop(); edititem(item); });
    var del = INPUT({'type':'image','src':config.theme['images/icon_close.gif'],'title':{$getstring['delete']}});
    connect(del, 'onclick', function (e) { e.stop(); delitem(item.id); });
    var cells = map(
        partial(TD,null),
        [
            item.name,
            [edit,' ',del]
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
    var save = INPUT({'type':'button','class':'button'});
    connect(save, 'onclick', function () { saveitem(item.id); });

    var rowtype = 'add';
    if (!item.name) {
        item.name = '';
        // The save button says 'add', and there's no cancel button.
        setNodeAttribute(save,'value',{$getstring['add']});
        savecancel = [save];
    }
    else { // Editing an existing menu item.
        // The save button says 'update' and there's a cancel button.
        var rowtype = 'edit';
        setNodeAttribute(save,'value',{$getstring['update']});
        var cancel = INPUT({'type':'button','class':'button','value':{$getstring['cancel']}});
        connect(cancel, 'onclick', closeopenedits);
        savecancel = [save,cancel];
    }

    // A text field for the name
    var name = INPUT({'type':'text','class':'text','id':'name'+item.id,'value':item.name});

    var row = TR({'id':'row'+item.id, 'class':rowtype},
                 map(partial(TD,null),[name,savecancel]));
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
        displayMessage(get_string('namedfieldempty',{$getstring['name']}),'error');
        return false;
    }

    var data = {'name':name,
                'itemid':itemid};
    sendjsonrequest('updategroup.json.php', data, 'POST', getitems);
    return false;
}

addLoadEvent(function () {
    getitems();
});
EOJS;

$smarty = smarty();
$smarty->assign('PAGEHEADING', hsc(get_string('groupcategories', 'admin')));
$smarty->assign('INLINEJAVASCRIPT', $ijs);
$smarty->assign('optionform', $optionform);
$smarty->display('admin/groups/groupcategories.tpl');
