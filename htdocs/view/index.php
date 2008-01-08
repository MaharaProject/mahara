<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @subpackage core
 * @author     Richard Mansfield <richard.mansfield@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'myportfolio/views');

define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'index');

require(dirname(dirname(__FILE__)) . '/init.php');
define('TITLE', get_string('myviews'));

$wwwroot = get_config('wwwroot');

$getstring = quotestrings(array('mahara' => array(
    'accessstartdate', 'accessstopdate', 'artefacts', 'delete', 'deleteviewquestion', 'description', 
    'editaccess', 'editview', 'editviewinformation', 'submitted', 'submitview', 'submitviewquestion'
)));

$editcontrolshelp = get_help_icon('core', 'views', null, null, null, 'vieweditcontrols');

$javascript = <<<JAVASCRIPT
var viewlist = new TableRenderer(
    'viewlist',
    'myviews.json.php',
    [undefined,undefined]
);

viewlist.rowfunction = function(r, n, data) {
    return map(partial(TR,null), [ title(r,data.groups), 
                                 [ TD(null,{$getstring['accessstartdate']}),TD(r.startdate)  ],
                                 [ TD(null,{$getstring['accessstopdate']}), TD(r.stopdate)   ],
                                 [ TD(null,{$getstring['description']}),    function () { var desc = TD(); desc.innerHTML=r.description; return desc }],
                                 [ TD(null,{$getstring['artefacts']}),
                                   TD(null,UL(null,map(partial(renderartefact,r.id),r.artefacts)))]]);
}

function title(r, groups) {
    var editinfo = INPUT({'type':'button','class':'button',
                              'value':{$getstring['editviewinformation']},
                              'onclick':"submitform(" + r.id + ", 'editinfo')"});
    var edit = INPUT({'type':'button','class':'button','value':{$getstring['editview']},
                          'onclick':"submitform(" + r.id + ", 'edit')"});
    var editaccess = INPUT({'type':'button','class':'button','value':{$getstring['editaccess']},
                                'onclick':"submitform(" + r.id + ", 'editaccess')"});
    var del = INPUT({'type':'button','class':'button','value':{$getstring['delete']},
                         'onclick':"return submitform(" + r.id + ", 'delete');"});
    if (r.submittedto) {
        var buttons = [editaccess];
        var assess = get_string('viewsubmittedto', r.submittedto);
    }
    else {
        var buttons = [editinfo,edit,editaccess,del];
        var assess = assessselect(r.id,groups);
    }
    var f = FORM({'id':('form'+r.id),'method':'post','enctype':'multipart/form-data',
                      'encoding':'multipart/form-data'},
                 DIV({'class': 'viewbuttons'}, buttons),
                 DIV({'class': 'viewbuttons'}, assess));
    var s = SPAN();
    if (r._rownumber == 1) {
        s.innerHTML = '{$editcontrolshelp}';
    }
    return [TD(null,A({'href':'view.php?id='+r.id},r.title)),
            TD(null,f, s)];
}

function groupoption(group) {
    return OPTION({'value':group.id},group.name);
}

function assessselect(viewid, grouplist) {
    if (grouplist.length < 1) {
        return null;
    }
    var submitview = INPUT({'type':'button','class':'button',
                            'value':{$getstring['submitview']}});
    submitview.onclick = function () { submitform(viewid, 'submitview'); };
    return [SELECT({'name':'group','class':'select'},
                   map(groupoption, grouplist)), submitview];
            
}

function renderartefact(viewid,a) {
    var link = A({'href':'{$wwwroot}view/artefact.php?artefact='+a.id+'&view='+viewid});
    link.innerHTML = a.title;
    return LI(null,link);
}

function submitform(viewid, action) {
    if (action == 'delete') {
        if (confirm({$getstring['deleteviewquestion']})) {
            sendjsonrequest('delete.json.php', {'viewid':viewid}, 'POST', viewlist.doupdate);
        }
        return false;
    }
    var form = $('form' + viewid);
    if (action == 'submitview') {
        if (confirm({$getstring['submitviewquestion']})) {
            sendjsonrequest('submit.json.php', {'viewid':viewid,'groupid':form.group.options[form.group.selectedIndex].value}, 'POST', viewlist.doupdate);
        }
        return false;
    }
    var page = 'index.php';
    if (action == 'editinfo') {
        page = 'edit.php';
    }
    if (action == 'edit') {
        page = 'blocks.php';
    }
    if (action == 'editaccess') {
        page = 'access.php';
    }
    setNodeAttribute(form, 'action', page);
    appendChildNodes(form, INPUT({'type':'hidden','name':'id','value':viewid}));
    form.submit();
    return false;
}

viewlist.limit = 5;
viewlist.updateOnLoad();

JAVASCRIPT;

$smarty = smarty(array('tablerenderer'), array(), array('viewsubmittedto' => 'mahara'));
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->display('view/index.tpl');

?>
