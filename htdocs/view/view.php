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

$viewid = param_integer('id');
$view = get_record('view', 'id', $viewid);

if (can_view_view($viewid)) {
    $content = 'view template display here';
}

$getstring = quotestrings(array('message', 'makepublic', 'placefeedback',
                                'cancel', 'complaint', 'notifysiteadministrator',
                                'addtowatchlist', 'placefeedback',
                                'reportobjectionablematerial', 'print'));

$wwwroot = get_config('wwwroot');

$javascript = <<<JAVASCRIPT

var viewid = {$viewid};

function feedbackform() {
    var form = FORM({'id':'feedback','method':'post'});
    submitfeedback = function () {
        // @todo add support for attached files when user is a tutor.
        sendjsonrequest('addfeedback.json.php',
            {'view':viewid, 
             'message':form.message.value,
             'public':form.public.checked},
            function () { removeElement('feedback'); });
        return false;
    }
    appendChildNodes(form, 
        TABLE({'border':0, 'cellspacing':0},
        TBODY(null,
        TR(null, TH(null, LABEL(null, {$getstring['message']}))),
        TR(null, TD(null, TEXTAREA({'rows':5, 'cols':80, 'name':'message'}))),
        TR(null, TH(null, LABEL(null, {$getstring['makepublic']}), 
                    INPUT({'type':'checkbox', 'name':'public'}))),
        TR(null, TD(null,
                    INPUT({'type':'button', 'value':{$getstring['placefeedback']},
                               'onclick':'submitfeedback();'}),
                    INPUT({'type':'button', 'value':{$getstring['cancel']},
                               'onclick':"removeElement('feedback');"}))))));
    appendChildNodes('viewmenu', DIV(null, form));
    return false;
}

function objectionform() {
    var form = FORM({'id':'objection','method':'post'});
    submitobjection = function () {
        sendjsonrequest('objectionable.json.php',
            {'view':viewid, 'message':form.message.value},
            function () { removeElement('objection'); });
        return false;
    }
    appendChildNodes(form, 
        TABLE({'border':0, 'cellspacing':0},
        TBODY(null,
        TR(null, TH(null, LABEL(null, {$getstring['complaint']}))),
        TR(null, TD(null, TEXTAREA({'rows':5, 'cols':80, 'name':'message'}))),
        TR(null, TD(null,
                    INPUT({'type':'button', 'value':{$getstring['notifysiteadministrator']},
                               'onclick':'submitobjection();'}),
                    INPUT({'type':'button', 'value':{$getstring['cancel']},
                               'onclick':"removeElement('objection');"}))))));
    appendChildNodes('viewmenu', DIV(null, form));
    return false;
}

function view_menu(type, id) {
    var addwatchlist = A({'href':''}, {$getstring['addtowatchlist']});
    addwatchlist.onclick = function () { 
        sendjsonrequest('addwatchlist.json.php', {'viewid':id});
        return false;
    }

    appendChildNodes('viewmenu',
                     A({'href':'', 'onclick':'return feedbackform();'}, {$getstring['placefeedback']}), ' | ',
                     A({'href':'', 'onclick':'return objectionform();'},
                       {$getstring['reportobjectionablematerial']}), ' | ',
                     A({'href':'', 'onclick':'window.print();'}, {$getstring['print']}), ' | ',
                      addwatchlist);
}

addLoadEvent(function () {view_menu('view', {$viewid}); });

JAVASCRIPT;

$smarty = smarty();
//$smarty->clear_assign('MAINNAV');
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->assign('TITLE', $view->title);
if (isset($content)) {
    $smarty->assign('VIEWCONTENT', $content);
}
$smarty->display('view/view.tpl');

?>
