<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage core
 * @author     Richard Mansfield <richard.mansfield@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
require(dirname(dirname(__FILE__)) . '/init.php');

$wwwroot = get_config('wwwroot');

$getstring = quotestrings(array('accessstartdate', 'accessstopdate', 'artefacts', 'delete',
                                'deleteviewquestion', 'description', 'editaccess', 'editview', 
                                'editviewinformation', 'submitted', 'submitview', 
                                'submitviewquestion'));

/* This is a slightly dodgy use of the table renderer: I'm outputting
   five <tr> elements for each item of data coming from the database.
   Maybe I need a repeated-paginated-thing renderer? */

$javascript = <<<JAVASCRIPT
var viewlist = new TableRenderer(
    'viewlist',
    'myviews.json.php',
    [undefined,undefined]
);

viewlist.rowfunction = function(r, n, data) {
    return map(partial(TR,null),[title(r,data.communities), 
                                 [TD(null,{$getstring['accessstartdate']}),TD(r.startdate)],
                                 [TD(null,{$getstring['accessstopdate']}),TD(r.stopdate)],
                                 [TD(null,{$getstring['description']}),TD(r.description)],
                                 [TD(null,{$getstring['artefacts']}),
                                  TD(null,UL(null,map(renderartefact,r.artefacts)))]]);
}

function title(r, communities) {
    var editinfo = INPUT({'type':'button','value':{$getstring['editviewinformation']}});
    editinfo.onclick = function () { submitform(r.id, 'editinfo'); };
    var edit = INPUT({'type':'button','value':{$getstring['editview']}});
    edit.onclick = function () { submitform(r.id, 'edit'); };
    var editaccess = INPUT({'type':'button','value':{$getstring['editaccess']}});
    editaccess.onclick = function () { submitform(r.id, 'editaccess'); };
    var del = INPUT({'type':'button','value':{$getstring['delete']}});
    del.onclick = function () { submitform(r.id, 'delete'); };
    if (r.submittedto) {
        var buttons = [editaccess];
        var assess = get_string('viewsubmittedto', r.submittedto);
    }
    else {
        var buttons = [editinfo,edit,editaccess,del];
        var assess = assessselect(r.id,communities);
    }
    var f = FORM({'id':('form'+r.id),'method':'post','enctype':'multipart/form-data',
                  'encoding':'multipart/form-data','onsubmit':"return formsubmit('"+r.id+"');"},
                 DIV(null,buttons),
                 DIV(null,assess));
    return [TD(null,A({'href':'view.php?id='+r.id},r.title)),
            TD(null,f)];
}

function communityoption(community) {
    return OPTION({'value':community.id},community.name);
}

function assessselect(viewid, communitylist) {
    if (communitylist.length < 1) {
        return null;
    }
    var submitview = INPUT({'type':'button','value':{$getstring['submitview']}});
    submitview.onclick = function () { submitform(viewid, 'submitview'); };
    return [SELECT({'name':'community'},map(communityoption, communitylist)), submitview];
            
}

function renderartefact(a) {
    return LI(null,A({'href':'{$wwwroot}viewartefact?id='+a.id},a.title));
}

function deleteview(viewid) {
    if (!confirm({$getstring['deleteviewquestion']})) {
        return;
    }
    sendjsonrequest('delete.json.php', {'viewid':viewid}, viewlist.doupdate);
    return false;
}

function submitview(viewid, communityid) {
    if (!confirm({$getstring['submitviewquestion']})) {
        return;
    }
    sendjsonrequest('submit.json.php', {'viewid':viewid,'communityid':communityid}, viewlist.doupdate);
    return false;
}

function submitform(viewid, action) {
    if (action == 'delete') {
        return deleteview(viewid);
    }
    var form = $('form' + viewid);
    if (action == 'submitview') {
        return submitview(viewid, form.community.value);
    }
    var page = 'index.php';
    if (action == 'editinfo') {
        page = 'editmetadata.php';
    }
    if (action == 'edit') {
        page = 'edit.php';
    }
    if (action == 'editaccess') {
        page = 'editaccess.php';
    }
    setNodeAttribute(form, 'action', page);
    appendChildNodes(form, INPUT({'type':'hidden','name':'viewid','value':viewid}));
    form.submit();
    return false;
}

viewlist.limit = 5;
viewlist.updateOnLoad();

JAVASCRIPT;

$smarty = smarty(array('tablerenderer'), array(), array('viewsubmittedto'));
$smarty->assign('site_menu', site_menu());
$smarty->assign('searchform', searchform());
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->display('view/index.tpl');

?>
