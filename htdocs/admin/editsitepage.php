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
 * @author     Richard Mansfield <richard.mansfield@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL',1);
define('ADMIN', 1);
define('MENUITEM','pageeditor');
require(dirname(dirname(__FILE__)).'/init.php');
require_once('form.php');

$sitepages = get_records('site_content');
$pageoptions = array();
foreach ($sitepages as $page) {
    $pageoptions[$page->name] = get_string($page->name,'admin');
}
asort($pageoptions);

$getstring = array('discardpageedits' => "'" . get_string('discardpageedits','admin') . "'");

$f = array(
    'name'                => 'editsitepage',
    'ajaxpost'            => true,
    'ajaxsuccessfunction' => 'contentSaved',
    'elements'            => array(
        'pagename' => array(
            'type'    => 'select',
            'title'   => get_string('pagename','admin'),
            'defaultvalue'   => 'home',
            'options' => $pageoptions
        ),
        'pagetext' => array(
            'name'        => 'pagetext',
            'type'        => 'wysiwyg',
            'rows'        => 20,
            'cols'        => 80,
            'title'       => get_string('pagetext','admin'),
            'description' => get_string('pagecontents','admin'),
            'rules'       => array(
                'required' => true
            )
        ),
        'submit' => array(
            'type'  => 'submit',
            'value' => get_string('savechanges','admin')
        ),
    )
);
$form = form($f);


function editsitepage_submit($values) {
    global $USER;
    $data = new StdClass;
    $data->name    = $values['pagename'];
    $data->content = $values['pagetext'];
    $data->mtime   = db_format_timestamp(time());
    $data->mauthor = $USER->id;
    try {
        update_record('site_content', $data, 'name');
    }
    catch (SQLException $e) {
        json_reply('local', get_string('savefailed','admin'));
    }
    json_reply(false, get_string('pagesaved','admin'));
}

if (use_html_editor()) {
    $js = array('tinymce');
}
else {
    $js = array();
}

$ijs = <<< EOJS

// global stuff, set in onLoad().
setEditorContent = function () {};
getEditorContent = function () {};
var oldpagename = '';
var originalcontent = '';

function requestPageText(removeMessage) {
    // Allow the user to abort change if changes have been made in the editor.
    if (getEditorContent() != originalcontent) {
        var answer = confirm({$getstring['discardpageedits']});
        if (!answer) {
            $('pagename').value = oldpagename;
            return;
        }
    }

    processingStart();
    if (removeMessage) {
        editsitepage_remove_message();
    }
    editsitepage_remove_error('pagetext');
    logDebug(get_string('loadingpagecontent', 'admin'));
    var d = loadJSONDoc('editchangepage.json.php',{'pagename':$('pagename').value});
    d.addCallback(function(data) {
        if (!data.error) {
            logDebug(get_string('sitepageloaded', 'admin'));
            setEditorContent(data.content);
            originalcontent = getEditorContent();
            oldpagename = $('pagename').value;
        }
        else {
            displayMessage(get_string('loadsitepagefailed', 'admin'));
        }
        processingStop();
    });
}

// Called from submitForm on successful page save.
function contentSaved () {  
    originalcontent = getEditorContent();
    callLater(2, editsitepage_remove_message);
    requestPageText(false);
}

function onLoad() {
    if (typeof(tinyMCE) != 'undefined') {
        setEditorContent = function (c) {
            tinyMCE.setContent(c);
            tinyMCE.execCommand('mceFocus',false,'mce_editor_0');
        }
        getEditorContent = tinyMCE.getContent;
    }
    else {
        setEditorContent = function (c) { $('pagetext').value = c; };
        getEditorContent = function () { return $('pagetext').value; };
    }
    originalcontent = getEditorContent();
    requestPageText();
    connect('pagename', 'onchange', requestPageText);
}

addLoadEvent(onLoad);
EOJS;

$smarty = smarty($js);
$smarty->assign('pageeditform', $form);
$smarty->assign('INLINEJAVASCRIPT', $ijs);
$smarty->display('admin/editsitepage.tpl');

?>
