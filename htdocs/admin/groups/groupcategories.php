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
$thead = implode($thead, ",");

$ijs = <<< EOJS
jQuery(function($) {
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
      var rows = $.map(itemlist, formatrow);

      var form  = $('<form>', { 'id': 'form', 'method': 'post',
        'enctype': 'multipart/form-data', 'encoding': 'multipart/form-data' });
      var table = $('<table>', { 'class': 'nohead table table-short' });
      var tbody = $('<tbody>');
      var thead = $('<tr>').append(createNodesFromList('<th>', [$thead]));

      $(tbody).append(thead, rows, addform());
      $(table).append(tbody);
      $(form).append(table);

      $('#menuitemlist').empty().append(form);

      if (data.focusid) {
          $('#item' + data.focusid).trigger("focus");
      }
  }

  // Creates one table row
  function formatrow (item) {
      // item has class, id, type, name, link, linkedto

      var edit = $('<button>', {
        'class':'btn btn-secondary btn-sm',
        'id':'item' + item.id,
        'type':'button',
        'title':{$getstring['edit']},
        'alt':{$getstring['editspecific']}.replace('%s', item.name)
      });
      edit.append($('<span>', {'class':'icon icon-cog icon-lg', 'role':'presentation'}));
      edit.append($('<span class="sr-only">' + {$getstring['editspecific']}.replace('%s', item.name) + '</span>'));

      edit.on('click', function (e) { e.preventDefault(); edititem(item); });


      var del = $('<button>', {
        'class':'btn btn-secondary btn-sm',
        'id':'item' + item.id,
        'type':'button',
        'title':{$getstring['delete']},
        'alt':{$getstring['deletespecific']}.replace('%s', item.name)
      });
      del.append($('<span>', {'class':'icon icon-trash text-danger icon-lg', 'role':'presentation'}));
      del.append($('<span class="sr-only">' + {$getstring['deletespecific']}.replace('%s', item.name) + '</span>'));

      del.on('click', function (e) { e.preventDefault(); delitem(item.id); });

      var buttongroup = $('<span>', {'class': 'btn-group'});
      buttongroup.append(edit, del);

      var row = $('<tr></tr>', {'id':'menuitem_'+item.id});
      row.append('<td>' + item.name + '</td>');
      row.append(buttongroup.wrap('<td>').parent());
      return row;
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
      var save = $('<input>', {'type':'button','class':'button btn btn-secondary btn-add-group'});
      save.on('click', function () { saveitem(item.id); });

      var rowtype = 'add';
      if (!item.name) {
          item.name = '';
          item.label = {$getstring['addnewgroupcategory']};
          // The save button says 'add', and there's no cancel button.
          save.prop('value',{$getstring['add']});
          savecancel = [save];
      }
      else { // Editing an existing menu item.
          // The save button says 'update' and there's a cancel button.
          var rowtype = 'edit';
          save.prop('value',{$getstring['update']});
          var cancel = $('<input>', {'type':'button','class':'button btn btn-sm btn-secondary','value':{$getstring['cancel']}});
          cancel.on('click', closeopenedits);
          savecancel = [save,cancel];
          item.label = {$getstring['edit']};
      }

      // A text field for the name
      var label = $('<label>', {'for':'name'+item.id,'class':'accessible-hidden'}).text(item.label);
      var name = $('<input>', {'type':'text','class':'text form-control input-sm','id':'name'+item.id,'value':item.name});
      name.on('keydown', function(e) {
          if (e.keyCode == 13) {
            save.trigger('click');
              e.preventDefault();
          }
      });
      var parentspan = $('<span>').append(label,name);
      var row = $('<tr>', {'id':'row'+item.id, 'class':rowtype});
      row.append($('<td>').append(parentspan), $('<td>').append(savecancel));
      return row;
  }

  // Close all open edit forms
  function closeopenedits() {
    var rows = $('#menuitemlist tr')
    for (var i=0; i<rows.length; i++) {
     var row = $(rows[i]);
        if (row.hasClass('edit')) {
            row.remove();
        }
        else if (row.hasClass('d-none')) {
            row.removeClass('d-none');
        }
    }
  }

  // Open a new edit form
  function edititem(item) {
      closeopenedits();
      var menuitem = $('#menuitem_'+item.id);
      menuitem.addClass('d-none');
      var newrow = editform(item);
      newrow.insertBefore(menuitem)
      $('#name' + item.id).trigger("focus");
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
    var f = $('#form');
    var name = $('#name'+itemid).val();
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

  getitems();
});
EOJS;

$smarty = smarty();
setpageicon($smarty, 'icon-users');

$smarty->assign('PAGEHEADING', hsc(get_string('groupcategories', 'admin')));
$smarty->assign('INLINEJAVASCRIPT', $ijs);
$smarty->assign('optionform', $optionform);
$smarty->display('admin/groups/groupcategories.tpl');
