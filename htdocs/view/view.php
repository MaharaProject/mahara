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

$viewid     = param_integer('view');
$artefactid = param_integer('artefact', null);
$path       = param_variable('path', null);

$view = new View($viewid);
if (!can_view_view($viewid)) {
    throw new AccessDeniedException();
}

if ($artefactid) {

    if (!artefact_in_view($artefactid, $viewid)) {
        throw new AccessDeniedException("Artefact $artefactid not in View $viewid");
    }

    require_once('artefact.php');
    $artefact = artefact_instance_from_id($artefactid);

    $feedbackisprivate = !$artefact->public_feedback_allowed();
    $options = array('viewid' => $viewid,
                     'path' => $path);
    if (in_array(FORMAT_ARTEFACT_RENDERFULL, $artefact->get_render_list())) {
        $rendered = $artefact->render(FORMAT_ARTEFACT_RENDERFULL, $options);
    }
    else {
        $rendered = $artefact->render(FORMAT_ARTEFACT_RENDERMETADATA, $options);
    }
    $content = '';
    if (!empty($rendered['javascript'])) {
        $content = '<script type="text/javascript">' . $rendered['javascript'] . '</script>';
    }
    $content .= $rendered['html'];

    $viewhref = 'view.php?view=' . $viewid;
    $navlist = array('<a href="' . $viewhref .  '">' . $view->get('title') . '</a>');
    if (!empty($path)) {
        $titles = get_records_sql_assoc('
            SELECT id,title FROM ' . get_config('dbprefix') . 'artefact
            WHERE id IN (' . $path . ')','');
        $artefactids = split(',', $path);
        for ($i = 0; $i < count($artefactids); $i++) {
            if ($artefactid == $artefactid[$i]) {
                break;
            }
            array_push($navlist, '<a href="' . $viewhref . '&artefact=' . $artefactids[$i]
                       . ($i>0 ? '&path=' . join(',', array_slice($artefactids, 0, $i)) : '') . '">' 
                       . $titles[$artefactids[$i]]->title . '</a>');
        }
        array_push($navlist, $artefact->get('title'));
    }
    else {
        $hierarchy = $view->get_artefact_hierarchy();
        if (!empty($hierarchy['refs'][$artefactid])) {
            $artefact = $hierarchy['refs'][$artefactid];
            $ancestorid = $artefact->parent;
            while ($ancestorid && isset($hierarchy['refs'][$ancestorid])) {
                $ancestor = $hierarchy['refs'][$ancestorid];
                $link = '<a href="view.php?view=' . $viewid . '&amp;artefact=' . $ancestorid . '">' 
                    . $ancestor->title . "</a>\n";
                array_push($navlist, $link);
                $ancestorid = $ancestor->parent;
            }
        }
        array_push($navlist, $artefact->title);
    }

    $jsartefact = $artefactid;
}
else {
    $navlist = array($view->get('title'));
    define('TITLE', $view->get('title'));
    $jsartefact = 'undefined';
    $content = $view->render();
    global $USER;
    $submittedcommunity = $view->get('submittedto');
    if ($submittedcommunity 
        && record_exists('community_member', 
                         'community', $submittedcommunity,
                         'member', $USER->get('id'),
                         'tutor', 1)) {
        // The user is a tutor of the community that this view has
        // been submitted to, and is entitled to upload an additional
        // file when submitting feedback.
        $tutorfilefeedbackformrow = "TR(null, TH(null, LABEL(null, '" . get_string('attachfile') . "'))),"
            . "TR(null, TD(null, INPUT({'type':'file', 'name':'attachment'}))),";
    }
}
if (empty($tutorfilefeedbackformrow)) {
        $tutorfilefeedbackformrow = '';
}

$getstring = quotestrings(array('mahara' => array(
        'message', 'makepublic', 'placefeedback', 'cancel', 'complaint', 
        'feedbackonthisartefactwillbeprivate', 'notifysiteadministrator',
        'nopublicfeedback', 'reportobjectionablematerial', 'print',
)));

$thing = $artefactid ? 'artefact' : 'view';
$getstring['addtowatchlist'] = "'" . get_string('addtowatchlist', 'mahara', get_string($thing)) . "'";
$getstring['addtowatchlistwithchildren'] = "'" . get_string('addtowatchlistwithchildren', 'mahara', ucfirst(get_string($thing))) . "'";
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
    addtowatchlist = function (recurse) { 
        var data = {'view':view,'recurse':recurse};
        if (artefact) {
            data.artefact = artefact;
        }
        sendjsonrequest('addwatchlist.json.php', data);
        return false;
    }

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
    if (config.loggedin) {
        appendChildNodes('viewmenu', ' | ',
            A({'href':'', 'onclick':'return addtowatchlist(false);'},
                {$getstring['addtowatchlist']}), ' | ',
            A({'href':'', 'onclick':'return addtowatchlist(true);'},
               {$getstring['addtowatchlistwithchildren']})
        );
     }

}

addLoadEvent(view_menu);

// The list of existing feedback.
var feedbacklist = new TableRenderer(
    'feedbacktable',
    'getfeedback.json.php',
    [
        function (r) {
            if (r.attachid && r.ownedbythisuser) {
                return TD(null, r.message, DIV(null, {$getstring['feedbackattachmessage']}));
            }
            return TD(null, r.message);
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
    array(),
    array(
        'mahara' => array(
            'public',
            'private',
            'makeprivate',
        ),
    )
);
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->assign('VIEWNAV', $navlist);
if (isset($content)) {
    $smarty->assign('VIEWCONTENT', $content);
}
$smarty->display('view/view.tpl');

?>
