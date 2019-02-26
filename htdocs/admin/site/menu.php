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
define('MENUITEM', 'configsite/sitemenu');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'sitemenu');

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('menus', 'admin'));

$strings = array('edit','delete','update','cancel','add','name','unknownerror');
$adminstrings = array('confirmdeletemenuitem', 'deletefailed','deletingmenuitem','savingmenuitem',
                      'nositefiles','loggedinmenu','loggedoutmenu','linkedto','externallink','sitefile',
                      'loadingmenuitems','menuitemsloaded','failedloadingsitefiles',
                      'failedloadingmenuitems');
foreach ($strings as $string) {
    $getstring[$string] = json_encode(get_string($string));
}
foreach ($adminstrings as $string) {
    $getstring[$string] = json_encode(get_string($string, 'admin'));
}

$thead = array(json_encode(get_string('type', 'admin')), json_encode(get_string('name', 'admin')), json_encode(get_string('linkedto', 'admin')), '""');
$thead = implode($thead, ",");
$externallink = json_encode(get_string('externallink', 'admin'));
$sitefile = json_encode(get_string('sitefile','admin'));

$namelabel = json_encode(get_string('name', 'admin'));
$linkedtolabel = json_encode(get_string('linkedto', 'admin'));
$ijs = <<< EOJS
// Request a list of menu items from the server


jQuery(function($) {
  var externallink = $externallink;
  var sitefile = $sitefile;

  function getitems() {
      sendjsonrequest('getmenuitems.json.php', {'public':selectedmenu == 'loggedoutmenu'}, 'GET',
                      function(data) { displaymenuitems(data.menuitems); });
  }
  // Get a list of the available admin files
  function getadminfiles() {
      sendjsonrequest('getadminfiles.json.php', {'public':selectedmenu == 'loggedoutmenu'},
                      'GET',
                      function (data) {
                          if (!data.error) {
                              adminfiles = data.adminfiles;
                          }
                          else {
                              adminfiles = null;
                          }
                      });
  }

  // Puts the list of menu items into the empty table.
  function displaymenuitems(itemlist) {
      var rows = $.map(itemlist, formatrow);

      var form  = $('<form>', { 'id': 'form', 'method': 'post',
        'enctype': 'multipart/form-data', 'encoding': 'multipart/form-data', 'name':'linksandresourcesform'});
      var tableResponsive = $('<div>', {'class':'table-responsive'});
      var table = $('<table>', { 'class': 'nohead table table-striped' });
      var tbody = $('<tbody>').append(rows, addform());
      var thead = $('<thead>').append($('<tr>').append($.map([$thead], function(header) { return $('<th>').text(header);})));

      $(table).append(thead, tbody);
      $(form).append(table);

      $('#menuitemlist').empty().append(form);
  }

  // Creates one table row
  function formatrow (item) {
      // item has id, type, name, link, linkedto
      var type = eval(item.type);
      var linkedto = $('<a>', {'href':item.linkedto, 'text': item.linktext});
      var edit = $('<button>', {'type':'button','class':'button btn btn-secondary btn-sm','title':{$getstring['edit']}})
        .append($('<span>', {'class':'icon icon-lg icon-pencil', 'role':'presentation'}), $('<span>', {'class':'sr-only','text': {$getstring['edit']}}));
      edit.on('click', function () { edititem(item); });
      var del = $('<button>', {'type':'button','class':'button btn btn-secondary btn-sm','title': {$getstring['delete']}})
      .append($('<span>', {'class':'icon icon-lg icon-trash text-danger', 'role':'presentation'}), $('<span>', {'class':'sr-only','text': {$getstring['delete']}}));
      del.on('click', function() { delitem(item.id); });
      var buttonGroup = $('<span>', {'class':'btn-group'}).append(edit, del);

      var cells = $.map([type, item.name, linkedto, [buttonGroup,contextualHelpIcon('linksandresourcesform', null, 'core', 'admin', null, 'adminmenuedit')] ], function(el) {
        return $('<td>').append(el);
      })
      return $('<tr>', {'id':'menuitem_'+item.id}).append(cells);
  }

  // Returns the form which adds a new menu item
  function addform(type) {
      var item = {'id':'new'};
      item.type = type ? type : 'externallist';
      return editform(item);
  }

  // Creates the contents of a menu item edit form
  // This is formatted as a table within the form (which is within a row of the table).
  function editform(item) {
      // item has id, type, name, link, linkedto
      // The form has two radio buttons to select the type, external link or admin file
      var elink = $('<input>', {'type':'radio','class':'radio with-label','name':'type'+item.id,'id':'type_'+item.id+'_externallink','value':'externallink'});
      var afile = $('<input>', {'type':'radio','class':'radio with-label','name':'type'+item.id,'id':'type_'+item.id+'_sitefile','value':'sitefile'});

      // Either a save, a cancel button, or both.
      var savecancel = [];
      var save = $('<button>', {'type':'button','class':'button btn btn-secondary btn-sm','title': {$getstring['update']}})
        .append($('<span>', {'class':'icon icon-plus icon-lg', 'role':'presentation'}), $('<span>', {'class':'sr-only','text': {$getstring['update']}}));
      save.on('click', function () { saveitem(item.id); });

      // The link field will be a text box or a select in the case of an admin file.
      var linkedto = null;

      var rowtype = 'add';
      if (!item) {
          // This is the 'add' form rather than the edit form
          // Set defaults.
          item = {'type':'externallist'};
      }
      if (!item.linkedto) {
          item.linkedto = '';
          item.name = '';
          elink.on('click', function () { changeaddform('externallink'); });
          afile.on('click', function () { changeaddform('sitefile'); });
          // The save button says 'add', and there's no cancel button.
          save.prop('value',{$getstring['add']});
          save.prop('title',{$getstring['add']});
          var savesr = $(save).find('span.sr-only').first();
          savesr.innerHTML = {$getstring['add']};
          savecancel = [save];
      }
      else { // Editing an existing menu item.
          // The save button says 'update' and there's a cancel button.
          var rowtype = 'edit';
          save.prop('value',{$getstring['update']});
          var cancel = $('<button>', {'type':'button','class':'button btn-sm btn btn-link','text': {$getstring['cancel']}});
          cancel.on('click', closeopenedits);
          savecancel = [save,cancel];
          elink.on('click', function () { changeeditform(item,'externallink'); });
          afile.on('click', function () { changeeditform(item,'sitefile'); });
      }

      // A text field for the name
      var name = $('<span>')
        .append($('<label>', {'for':'name'+item.id,'class':'sr-only', 'text': $namelabel}),
          $('<input>', {'type':'text','class':'text form-control input-sm','id':'name'+item.id,'value':item.name}));

      if (item.type == 'sitefile') {
          if (adminfiles == null) {
              // There are no admin files, we don't need the select or save button
              linkedto = {$getstring['nositefiles']};
              savecancel = [cancel];
          }
          else {
              // Select the currently selected file.
              linkedtoselect = $('<select>', {'id':'linkedto'+item.id});
              linkedto = $('<span>').append($('<label>', {'for':'linkedto'+item.id,'class':'sr-only', 'text': $linkedtolabel}), linkedtoselect);
              for (var i = 0; i < adminfiles.length; i++) {
                  if (item.file == adminfiles[i].id) {
                    $(linkedtoselect).append($('<option>', {'value':adminfiles[i].id, 'selected':true, 'text': adminfiles[i].name }));
                  }
                  else {
                    $(linkedtoselect).append($('<option>', {'value':adminfiles[i].id,  'text': adminfiles[i].name }));
                  }
              }
          }
          afile.prop('checked',true);
      }
      else { // type = externallist
          linkedto = $('<span>').append(
            $('<label>', {'for':'linkedto'+item.id,'class':'sr-only', 'text': $linkedtolabel}),
            $('<input>', {'type':'text','class':'text form-control input-sm','id':'linkedto'+item.id,'value':item.linkedto})
          );
          elink.prop('checked',true);
      }
      var radios = [$('<div>', {'class' : 'radio'}).append(elink,
        $('<label>', {'for':'type_'+item.id+'_externallink', 'text': {$getstring['externallink']}}),
        contextualHelpIcon('linksandresourcesform', 'elink', 'core', 'admin', null, 'adminexternallink')
      ),
      $('<div>', {'class' : 'radio'}).append(afile,
        $('<label>', {'for':'type_'+item.id+'_sitefile', 'text': {$getstring['sitefile']}}),
        contextualHelpIcon('linksandresourcesform', 'afile', 'core', 'admin', null, 'adminsitefile'))];
      var row = $('<tr>', {'id':'row'+item.id, 'class':rowtype}).append(
        $.map([radios, name, linkedto, savecancel], function (el) { return $('<td>').append(el); })
      );
      return row;
  }

  // Close all open edit forms
  function closeopenedits() {
      var rows = $('#menuitemlist tr');
      for (var i=0; i<rows.length; i++) {
        var row = $(rows[i])
          if (row.hasClass('edit')) {
              row.remove();
          }
          else if (row.hasClass('d-none')) {
            row.removeClass('d-none');
          }
      }
  }

  // Change the type of an edit form
  function changeeditform(item, type) {
      item.type = type;
      edititem(item);
  }

  // Change the type of the add form
  function changeaddform(type) {
      var newrow = addform(type);
      $('#rownew').replaceWith(newrow);
  }

  // Open a new edit form
  function edititem(item) {
      closeopenedits();
      var menuitem = $('#menuitem_'+item.id);
      menuitem.addClass('d-none');
      var newrow = editform(item);
      newrow.insertBefore(menuitem);
  }

  // Receive standard json error message
  // Request deletion of a menu item from the db
  function delitem(itemid) {
      if (confirm({$getstring['confirmdeletemenuitem']})) {
          sendjsonrequest('deletemenuitem.json.php',{'itemid':itemid}, 'POST', getitems);
      }
  }

  // Send the menu item in the form to the database.
  function saveitem(itemid) {
      var f = $('#form')[0];
      var name = $('#name'+itemid).val();
      var linkedto = $('#linkedto'+itemid).val();
      if (name == '') {
          displayMessage(get_string('namedfieldempty', 'mahara', {$getstring['name']}), 'error');
          return false;
      }
      if (linkedto == '') {
          displayMessage(get_string('namedfieldempty', ' mahara', {$getstring['linkedto']}), 'error');
          return false;
      }

      var data = {'type':eval('f.type'+itemid+'[0].checked') ? 'externallink' : 'sitefile',
                  'name':name,
                  'linkedto':linkedto,
                  'itemid':itemid,
                  'public':selectedmenu == 'loggedoutmenu'};
      sendjsonrequest('updatemenu.json.php', data, 'POST', getitems);
      return false;
  }

  function changemenu() {
      selectedmenu = $('#menuselect').val();
      getitems();
      getadminfiles();
  }

  var selectedmenu = 'loggedoutmenu';
  var adminfiles = null;


  $('#menuselect').val(selectedmenu);
  $('#menuselect').on('change', changemenu);
  changemenu();
});
EOJS;

$menulist = array('loggedinmenu', 'loggedoutmenu');
foreach ($menulist as &$menu) {
    $menu = array('value' => $menu,
                  'name' => get_string($menu,'admin'));
}


// Edit form for footer links
$all = footer_menu(true);
$active = get_config('footerlinks');
$active = $active ? unserialize($active) : array_keys($all);
$activeurls = get_config('footercustomlinks');
$activeurls = $activeurls ? unserialize($activeurls) : null;
$footerelements = array();
foreach ($all as $k => $v) {
    if ($k == 'legal') {
        $footerelements[$k] = array(
            'type' => 'switchbox',
            'class' => 'last with-text-input',
            'title' => $v['title'],
            'defaultvalue' => in_array($k, $active),
        );
    }
    else {
        $footerelements[$k] = array(
            'type' => 'switchbox',
            'class' => 'last with-text-input',
            'title' => $v['title'],
            'defaultvalue' => in_array($k, $active),
            'onclick' => "jQuery('#footerlinks_{$k}_link')[0].disabled = !this.checked;",
        );
        $footerelements[$k . '_link'] = array(
            'type' => 'text',
            'size' => 60,
            'description' => get_string('footercustomlink', 'admin', $v['url']),
            'defaultvalue' => isset($activeurls[$k]) ? $activeurls[$k] : '',
            'disabled' => !in_array($k, $active),
        );
    }
}
$footerelements['submit'] = array(
    'class' => 'btn-primary',
    'type'  => 'submit',
    'value' => get_string('savechanges', 'admin')
);
$footerform = pieform(array(
    'name'              => 'footerlinks',
    'elements'          => $footerelements,
));

function footerlinks_submit(Pieform $form, $values) {
    global $active, $activeurls, $all, $SESSION;
    $new = array();
    $newurls = array();
    foreach (array_keys($all) as $k) {
        if (!empty($values[$k])) {
            $new[] = $k;
        }
        if (!empty($values[$k.'_link']) &&
            ($values[$k.'_link'] != $all[$k]['url'])) {
                $newurls[$k] = $values[$k.'_link'];
        }
    }
    if ($new != $active) {
        set_config('footerlinks', serialize($new));
        $SESSION->add_ok_msg(get_string('footerupdated', 'admin'));
    }
    if ($newurls != $activeurls) {
        set_config('footercustomlinks', serialize($newurls));
        if ($new == $active) {
            // record message in session only if we haven't done so yet.
            $SESSION->add_ok_msg(get_string('footerupdated', 'admin'));
        }
    }
    redirect(get_config('wwwroot') . 'admin/site/menu.php');
}


$smarty = smarty();
setpageicon($smarty, 'icon-bars');

$smarty->assign('INLINEJAVASCRIPT', $ijs);
$smarty->assign('MENUS', $menulist);
$smarty->assign('descriptionstrargs', array('<a href="' . get_config('wwwroot') . 'artefact/file/sitefiles.php">', '</a>'));
$smarty->assign('footerform', $footerform);
$smarty->display('admin/site/menu.tpl');
