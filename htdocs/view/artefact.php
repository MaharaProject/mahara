<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2008 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2008 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'artefact');

require(dirname(dirname(__FILE__)) . '/init.php');
require(get_config('libroot') . 'view.php');

$artefactid = param_integer('artefact');
$viewid     = param_integer('view');
$path       = param_variable('path', null);

$view = new View($viewid);
if (!can_view_view($viewid)) {
    throw new AccessDeniedException();
}

if (!artefact_in_view($artefactid, $viewid)) {
    throw new AccessDeniedException("Artefact $artefactid not in View $viewid");
}

require_once(get_config('docroot') . 'artefact/lib.php');
$artefact = artefact_instance_from_id($artefactid);

if (!$artefact->in_view_list()) {
    throw new AccessDeniedException("Artefacts of this type are only viewable within a View");
}

define('TITLE', $artefact->display_title() . ' ' . get_string('in', 'view') . ' ' . $view->get('title'));

// Render the artefact
$options = array('viewid' => $viewid,
                 'path' => $path);
$rendered = $artefact->render_self($options);
$content = '';
if (!empty($rendered['javascript'])) {
    $content = '<script type="text/javascript">' . $rendered['javascript'] . '</script>';
}
$content .= $rendered['html'];

// Build the path to the artefact, through its parents
$artefactpath = array();
$parent = $artefact->get('parent');
while ($parent !== null) {
    // This loop could get expensive when there are a lot of parents. But at least 
    // it works, unlike the old attempt
    $parentobj = artefact_instance_from_id($parent);
    if (artefact_in_view($parent, $viewid)) {
        array_unshift($artefactpath, array(
            'url'   => get_config('wwwroot') . 'view/artefact.php?artefact=' . $parent . '&view=' . $viewid,
            'title' => $parentobj->display_title(),
        ));
    }

    $parent = $parentobj->get('parent');
}

$artefactpath[] = array(
    'url' => '',
    'title' => $artefact->display_title(),
);

$heading = '<a href="' . get_config('wwwroot') . 'view/view.php?id=' . $view->get('id') .'">' . hsc($view->get('title')) . '</a> ' . get_string('by', 'view') . ' <a href="' . get_config('wwwroot') .'user/view.php?id=' . $view->get('owner'). '">' . $view->formatted_owner() . '</a>';
foreach ($artefactpath as $item) {
	if (empty($item['url'])) {
	    $heading .= ': ' . $item['title'];
	}
	else {
        $heading .= ': <a href="' . $item['url'] . '">' . $item['title'] . '</a>';
	}
}

$getstring = quotestrings(array(
    'mahara' => array('message', 'cancel'),
    'view' => array('makepublic', 'placefeedback', 'complaint',
        'feedbackonthisartefactwillbeprivate', 'notifysiteadministrator',
        'nopublicfeedback', 'reportobjectionablematerial', 'print',
        'thisfeedbackispublic', 'thisfeedbackisprivate', 'attachment')
));

$getstring['feedbackattachmessage'] = "'(" . get_string('feedbackattachmessage', 'view', get_string('feedbackattachdirname', 'view')) . ")'";

// Safari doesn't seem to like these inputs to be called 'public', so call them 'ispublic' instead.
$feedbackisprivate = !$artefact->public_feedback_allowed();
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
var artefact = {$artefactid};

function feedbackform() {
    if ($('menuform')) {
        removeElement('menuform');
    }
    var form = FORM({'id':'menuform','method':'post'});
    submitfeedback = function () {
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
    appendChildNodes(form, 
        TABLE({'border':0, 'cellspacing':0, 'id':'feedback'},
        TBODY(null,
        TR(null, TH(null, LABEL(null, {$getstring['message']}))),
        TR(null, TD(null, TEXTAREA({'rows':5, 'cols':80, 'name':'message'}))),
        {$makepublic}
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

}

addLoadEvent(view_menu);

// The list of existing feedback.
var feedbacklist = new TableRenderer(
    'feedbacktable',
    'getfeedback.json.php',
    [/*
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
    */]
);

feedbacklist.rowfunction = function(r, n, d) {
    var td = TD(null);
    td.innerHTML = r.message;
    if (r.attachid && r.ownedbythisuser) {
        appendChildNodes(td, DIV(null, {$getstring['feedbackattachmessage']}));
    }

    var publicPrivate = null;
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
                            replaceChildNodes(makePrivate.parentNode, '(' + {$getstring['private']} + ')');
                        }
                    }
                );

                e.stop();
            });
            makePrivate = [' - ', makePrivate];
        }
        publicPrivate = SPAN(null, {$getstring['thisfeedbackispublic']}, makePrivate);
    }
    else {
        publicPrivate = {$getstring['thisfeedbackisprivate']};
    }

    var icon = A({'href': config.wwwroot + 'user/view.php?id=' + r.author}, IMG({'src': config.wwwroot + 'thumb.php?type=profileicon&id=' + r.author + '&maxsize=20', 'valign': 'middle'}));
    appendChildNodes(td, DIV({'class': 'details'}, DIV({'class': 'icon'}, icon), A({'href': config.wwwroot + 'user/view.php?id=' + r.author}, r.name), ' | ', r.date, ' | ', publicPrivate));

    return TR({'class': 'r' + (n % 2)}, td);
};
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
        'view' => array(
            'public',
            'private',
            'makeprivate',
        ),
    ),
    array(
        'stylesheets' => array('style/views.css')
    )
);
$smarty->assign('heading', $heading);
$smarty->assign('noheadingescape', true);
$smarty->assign('artefact', $content);
$smarty->assign('artefactpath', $artefactpath);
$smarty->assign('INLINEJAVASCRIPT', $javascript);

$smarty->assign('viewid', $viewid);
$smarty->assign('viewowner', $view->get('owner'));
$smarty->assign('viewtitle', $view->get('title'));
$smarty->assign('formattedowner', $view->formatted_owner());

$smarty->display('view/artefact.tpl');

?>
