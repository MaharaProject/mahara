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
 * @subpackage admin
 * @author     Martyn Smith <martyn@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'configusers/usersearch');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('usersearch', 'admin'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'usersearch');

$query = param_variable('query',null);

if (isset($query) && trim($query) == '') {
    $query = null;
}

$wwwroot = get_config('wwwroot');
$str_profileimage         = json_encode(get_string('profileimage'));
$str_institution          = json_encode(get_string('institution'));
$str_suspenduser          = json_encode(get_string('suspenduser', 'admin'));
$str_suspensionreason     = json_encode(get_string('suspensionreason', 'admin'));
$str_errorwhilesuspending = json_encode(get_string('errorwhilesuspending', 'admin'));
$str_noresults            = json_encode(get_string('noresultsfound'));
$str_cancel               = json_encode(get_string('cancel'));
$str_sesskey              = json_encode($USER->get('sesskey'));

$javascript = <<<EOF
var results = new TableRenderer(
    'searchresults',
    'search.json.php',
    [
        function(r) { return TD(null,IMG({'src': '{$wwwroot}thumb.php?type=profileicon&size=40x40&id=' + r.id, 'alt': {$str_profileimage}})); },
        function(r) {
            return TD({'style': 'vertical-align: top'},
                A({'href': '{$wwwroot}user/view.php?id=' + r.id}, r.name),
                BR(),
                STRONG(null, {$str_institution} + ': '),
                SPAN(null, r.institution)
            );
        },
        function(r) {
            return TD({'style': 'vertical-align: top'},
                A({'href': '', 'onclick': 'suspendDisplay(this); return false;'}, $str_suspenduser),BR()
                // A({'href': ''}, 'some other action'),BR()
            );
        }
    ]
);
results.rowfunction = function(r) { var row = TR(); row.data = r; return row; };
results.statevars.push('query');
results.statevars.push('action');
results.statevars.push('sesskey');
results.action = 'search';
results.sesskey = {$str_sesskey};
results.emptycontent = {$str_noresults};
results.query = '';
results.updateOnLoad();

function doSearch() {
    results.query = $('usersearch').value;
    results.offset = 0;
    results.doupdate();
}

addLoadEvent(function() {
    $('usersearch').focus();

    connect('usersearch', 'onkeypress', function (k) {
        if (k.key().code == 13) {
            doSearch();
        }
    });
});

function suspendDisplay(ref) {
    ref = ref.parentNode.parentNode; // get the TR
    var reason = INPUT({'type': 'text'});
    var cancelButton = BUTTON({'type': 'button'}, {$str_cancel});
    var saveButton = BUTTON({'type': 'button'}, {$str_suspenduser});

    insertSiblingNodesAfter(ref, TR(null, TD({'colSpan': 3},
        {$str_suspensionreason} + ': ',
        reason,
        cancelButton,
        saveButton
    )));

    reason.focus();

    connect(reason, 'onkeypress', function (k) {
        if (k.key().code == 13) {
            suspendSave(reason);
        }
        if (k.key().code == 27) {
            suspendCancel(reason);
        }
    });

    connect(cancelButton, 'onclick', partial(suspendCancel, reason));
    connect(saveButton, 'onclick', partial(suspendSave, reason));
}

function suspendSave(reason) {
    var susReason = reason.value;
    var data = reason.parentNode.parentNode.previousSibling.data;
    removeElement(reason.parentNode.parentNode);

    sendjsonrequest('search.json.php', {'action': 'suspend', 'reason': susReason, 'id': data.id}, 'GET',
                    function(response) {
                        if(!response.error) {
                            displayMessage('User "' + data.name + '" Suspended');
                        }
                    });
}

function suspendCancel(reason) {
    removeElement(reason.parentNode.parentNode);
}
EOF;

$smarty = smarty(array('tablerenderer'));
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->display('admin/users/search.tpl');

?>
