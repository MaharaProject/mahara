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
define('MENUITEM', 'myviews');
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
    return map(partial(TR,null), [ title(r,data.communities), 
                                 [ TD(null,{$getstring['accessstartdate']}),TD(r.startdate)  ],
                                 [ TD(null,{$getstring['accessstopdate']}), TD(r.stopdate)   ],
                                 [ TD(null,{$getstring['description']}),    function () { var desc = TD(); desc.innerHTML=r.description; return desc }],
                                 [ TD(null,{$getstring['artefacts']}),
                                   TD(null,UL(null,map(partial(renderartefact,r.id),r.artefacts)))]]);
}

function title(r, communities) {
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
        var assess = assessselect(r.id,communities);
    }
    var f = FORM({'id':('form'+r.id),'method':'post','enctype':'multipart/form-data',
                      'encoding':'multipart/form-data'},
                 DIV({'class': 'viewbuttons'}, buttons),
                 DIV({'class': 'viewbuttons'}, assess));
    var s = SPAN();
    if (r._rownumber == 1) {
        s.innerHTML = '{$editcontrolshelp}';
    }
    return [TD(null,A({'href':'view.php?view='+r.id},r.title)),
            TD(null,f, s)];
}

function communityoption(community) {
    return OPTION({'value':community.id},community.name);
}

function assessselect(viewid, communitylist) {
    if (communitylist.length < 1) {
        return null;
    }
    var submitview = INPUT({'type':'button','class':'button',
                            'value':{$getstring['submitview']}});
    submitview.onclick = function () { submitform(viewid, 'submitview'); };
    return [SELECT({'name':'community','class':'select'},
                   map(communityoption, communitylist)), submitview];
            
}

function renderartefact(viewid,a) {
    var link = A({'href':'{$wwwroot}view/view.php?view='+viewid+'&artefact='+a.id});
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
            sendjsonrequest('submit.json.php', {'viewid':viewid,'communityid':form.community.options[form.community.selectedIndex].value}, 'POST', viewlist.doupdate);
        }
        return false;
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

$smarty = smarty(array('tablerenderer'), array(), array('viewsubmittedto' => 'mahara'));
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->display('view/index.tpl');

?>
