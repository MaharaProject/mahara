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
define('PUBLIC', 1);
require(dirname(dirname(__FILE__)) . '/init.php');
require(get_config('libroot') . 'view.php');

$viewid     = param_integer('id');
$path       = param_variable('path', null);

$view = new View($viewid);
if (!can_view_view($viewid)) {
    throw new AccessDeniedException();
}

$viewbeingwatched = 0;

$title = get_string('titleformatted', 'view', $view->get('title'), $view->formatted_owner());
define('TITLE', $title);
$description = '';

$navlist = array($title);
$description = $view->get('description');
$jsartefact = 'undefined';

$content = $view->build_columns();

global $USER;
$submittedgroup = $view->get('submittedto');
if ($submittedgroup 
    && record_exists('group_member', 
                     'group', $submittedgroup,
                     'member', $USER->get('id'),
                     'tutor', 1)) {
    // The user is a tutor of the group that this view has
    // been submitted to, and is entitled to upload an additional
    // file when submitting feedback.
    $tutorfilefeedbackformrow = "TR(null, TH(null, LABEL(null, '" . get_string('attachfile') . "'))),"
        . "TR(null, TD(null, INPUT({'type':'file', 'name':'attachment'}))),";
}
$viewbeingwatched = (int)record_exists('usr_watchlist_view', 'usr', $USER->get('id'), 'view', $viewid);

if (empty($tutorfilefeedbackformrow)) {
        $tutorfilefeedbackformrow = '';
}

$getstring = quotestrings(array('mahara' => array(
        'message', 'makepublic', 'placefeedback', 'cancel', 'complaint', 
        'feedbackonthisartefactwillbeprivate', 'notifysiteadministrator',
        'nopublicfeedback', 'reportobjectionablematerial', 'print',
)));

$getstring['addtowatchlist'] = json_encode(get_string('addtowatchlist'));
$getstring['removefromwatchlist'] = json_encode(get_string('removefromwatchlist'));
$getstring['feedbackattachmessage'] = "'(" . get_string('feedbackattachmessage', 'mahara', get_string('feedbackattachdirname')) . ")'";

// Safari doesn't seem to like these inputs to be called 'public', so call them 'ispublic' instead.
if (!empty($feedbackisprivate)) {
    $makepublic = "TR(null, INPUT({'type':'hidden','name':'ispublic','value':'false'}), TD({'colspan':2}, " 
        . $getstring['feedbackonthisartefactwillbeprivate'] . ")),";
}
else {
    $makepublic = "TR(null, TH(null, LABEL(null, " . $getstring['makepublic'] . " ), " 
        . "INPUT({'type':'checkbox', 'class':'checkbox', 'name':'ispublic'}))),";
}

$javascript = <<<EOF

var view = {$viewid};
var artefact = {$jsartefact};

function feedbackform() {
    if ($('menuform')) {
        removeElement('menuform');
    }
    var form = FORM({'id':'menuform','method':'post'});
    submitfeedback = function () {
        if (form.attachment && form.attachment.value) {
            updateNodeAttributes(form, {'enctype':'multipart/form-data',
                                        'encoding':'multipart/form-data',
                                        'action':'feedbackattachment.php', 'target':''});
            appendChildNodes(form, INPUT({'type':'hidden', 'name':'view', 'value':view}));
            appendChildNodes(form, INPUT({'type':'hidden', 'name':'filename', 
                                          'value':basename(form.attachment.value)}));
            form.submit();
        }
        else {
            var data = {'view':view, 
                        'public':form.ispublic.checked,
                        'message':form.message.value};
            if (artefact) {
                data.artefact = artefact;
            }
            sendjsonrequest('addfeedback.json.php', data, 'POST', function () { 
                removeElement('menuform');
                feedbacklist.doupdate();
            });
            return false;
        }
    }
    appendChildNodes(form, 
        TABLE({'border':0, 'cellspacing':0, 'id':'feedback'},
        TBODY(null,
        TR(null, TH(null, LABEL(null, {$getstring['message']}))),
        TR(null, TD(null, TEXTAREA({'rows':5, 'cols':80, 'name':'message'}))),
        {$makepublic}
        {$tutorfilefeedbackformrow}
        TR(null, TD(null,
                    INPUT({'type':'button', 'class':'button', 
                               'value':{$getstring['placefeedback']},
                               'onclick':'submitfeedback();'}),
                    INPUT({'type':'button', 'class':'button', 'value':{$getstring['cancel']},
                               'onclick':"removeElement('menuform');"}))))));
    appendChildNodes('viewmenu', DIV(null, form));
    form.message.focus();
    return false;
}

function objectionform() {
    if ($('menuform')) {
        removeElement('menuform');
    }
    var form = FORM({'id':'menuform','method':'post'});
    submitobjection = function () {
        var data = {'view':view, 'message':form.message.value};
        if (artefact) {
            data.artefact = artefact;
        }
        sendjsonrequest('objectionable.json.php', data, 'POST', function () { removeElement('menuform'); });
        return false;
    }
    appendChildNodes(form, 
        TABLE({'border':0, 'cellspacing':0, 'id':'objection'},
        TBODY(null,
        TR(null, TH(null, LABEL(null, {$getstring['complaint']}))),
        TR(null, TD(null, TEXTAREA({'rows':5, 'cols':80, 'name':'message'}))),
        TR(null, TD(null,
                    INPUT({'type':'button', 'class':'button', 
                               'value':{$getstring['notifysiteadministrator']},
                               'onclick':'submitobjection();'}),
                    INPUT({'type':'button', 'class':'button', 'value':{$getstring['cancel']},
                               'onclick':"removeElement('menuform');"}))))));
    appendChildNodes('viewmenu', DIV(null, form));
    form.message.focus();
    return false;
}

function view_menu() {
    if (config.loggedin) {
        appendChildNodes('viewmenu',
            A({'href':'', 'onclick':"return feedbackform();"}, 
                {$getstring['placefeedback']}), ' | ',
            A({'href':'', 'onclick':'return objectionform();'},
               {$getstring['reportobjectionablematerial']}), ' | '
        );
    }
    appendChildNodes('viewmenu',
        A({'href':'', 'onclick':'window.print();return false;'}, 
            {$getstring['print']})
    );
    if (config.loggedin && !artefact) {
        var linkTextFlag = {$viewbeingwatched};
        var linkText = [{$getstring['addtowatchlist']}, {$getstring['removefromwatchlist']}];
        link = A({'href': ''}, linkText[linkTextFlag]);
        connect(link, 'onclick', function(e) {
            var data = {'view': view};
            sendjsonrequest('togglewatchlist.json.php', data, 'POST', function() {
                link.innerHTML = linkText[++linkTextFlag % 2];
            });
            e.stop();
        });
        appendChildNodes('viewmenu', ' | ', link);
     }

}

addLoadEvent(view_menu);

// The list of existing feedback.
var feedbacklist = new TableRenderer(
    'feedbacktable',
    'getfeedback.json.php',
    [
        function (r) {
            var td = TD(null);
            td.innerHTML = r.message;
            if (r.attachid && r.ownedbythisuser) {
                appendChildNodes(td, DIV(null, {$getstring['feedbackattachmessage']}));
                return td;
            }
            return td;
        },
        'name',
        'date', 
        function (r) {
            if (r.ispublic == 1) {
                var makePrivate = null;
                if (r.ownedbythisuser) {
                    makePrivate = A({'href': ''}, get_string('makeprivate'));
                    connect(makePrivate, 'onclick', function (e) {
                        sendjsonrequest(
                            'changefeedback.json.php',
                            r,
                            'POST',
                            function (data) {
                                if (!data.error) {
                                    replaceChildNodes(makePrivate.parentNode, '(' + get_string('private') + ')');
                                }
                            }
                        );

                        e.stop();
                    });
                }
                return TD(null, '(' + get_string('public') + ') ', makePrivate);
            }
            return TD(null, '(' + get_string('private') + ')');
        },
        function (r) {
            if (r.attachid) {
                return TD(null, A({'href':config.wwwroot + 'artefact/file/download.php?file=' + r.attachid},
                                  r.attachtitle));
            }
            return TD(null);
        }
    ]
);

feedbacklist.limit = 10;
feedbacklist.view = view;
feedbacklist.artefact = artefact;
feedbacklist.statevars.push('view','artefact');
feedbacklist.emptycontent = {$getstring['nopublicfeedback']};
feedbacklist.updateOnLoad();


EOF;

$smarty = smarty(
    array('tablerenderer'),
    array('<link rel="stylesheet" type="text/css" href="' . get_config('wwwroot') . 'theme/views.css">'),
    array(
        'mahara' => array(
            'public',
            'private',
            'makeprivate',
        ),
    ),
    array(
        'stylesheets' => array('style/views.css'),
    )
);
$smarty->assign('DESCRIPTION', $description);
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->assign('VIEWNAV', $navlist);
if (isset($content)) {
    $smarty->assign('VIEWCONTENT', $content);
}
if ($USER->get('id') == $view->get('owner')) {
    $smarty->assign('can_edit', true);
    $smarty->assign('viewid', $viewid);
}
$smarty->display('view/view.tpl');

?>
