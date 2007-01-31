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

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'configsite');
define('SUBMENUITEM', 'sitepages');
require(dirname(dirname(dirname(__FILE__))).'/init.php');
require_once('pieforms/pieform.php');
define('TITLE', get_string('sitepages', 'admin'));

$sitepages = get_records_array('site_content');
$pageoptions = array();
foreach ($sitepages as $page) {
    $pageoptions[$page->name] = get_string($page->name,'admin');
}
asort($pageoptions);

$getstring = array('discardpageedits' => "'" . get_string('discardpageedits','admin') . "'");

$f = array(
    'name'                => 'editsitepage',
    'jsform'              => true,
    'jssuccesscallback'    => 'contentSaved',
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
$form = pieform($f);


function editsitepage_submit(Pieform $form, $values) {
    global $USER;
    $data = new StdClass;
    $data->name    = $values['pagename'];
    $data->content = $values['pagetext'];
    $data->mtime   = db_format_timestamp(time());
    $data->mauthor = $USER->get('id');
    try {
        update_record('site_content', $data, 'name');
    }
    catch (SQLException $e) {
        $form->json_reply(PIEFORM_ERR, get_string('savefailed','admin'));
    }
    $form->json_reply(PIEFORM_OK, get_string('pagesaved','admin'));
}

$ijs = <<< EOJS

// global stuff, set in onLoad().
var oldpagename = '';
var originalcontent = '';

function requestPageText() {
    // Allow the user to abort change if changes have been made in the editor.
    if (getEditorContent() != originalcontent) {
        if (!confirm({$getstring['discardpageedits']})) {
            $('editsitepage_pagename').value = oldpagename;
            return;
        }
    }

    editsitepage_remove_all_errors();
    sendjsonrequest('editchangepage.json.php', {'pagename':$('editsitepage_pagename').value}, 'POST',
                    function(data) {
                        if (!data.error) {
                            setEditorContent(data.content);
                            originalcontent = data.content;
                            oldpagename = $('editsitepage_pagename').value;
                        }
                    });
}

// Called from submitForm on successful page save.
function contentSaved (form, data) {  
    formSuccess(form, data);
    // @todo something might need to be done here
    //callLater(2, function() { removeElement('messages'); });
    originalcontent = getEditorContent();
}

function onLoad() {
    if (typeof(tinyMCE) != 'undefined') {
        setEditorContent = function (c) {
            if (navigator.userAgent.indexOf('Firefox/') != -1) {
                // Firefox won't let you use the delete key unless you
                // put this stuff in:
                tinyMCE.removeMCEControl('mce_editor_0');
                tinyMCE.idCounter = 0;
                $('editsitepage_pagetext').value = c;
                tinyMCE.execCommand('mceAddControl', true, 'editsitepage_pagetext');
            } else {
                tinyMCE.setContent(c);
            }
            tinyMCE.execCommand('mceFocus', false, 'mce_editor_0');
        }
        getEditorContent = tinyMCE.getContent;
    }
    else {
        setEditorContent = function (c) { $('editsitepage_pagetext').value = c; };
        getEditorContent = function () { return $('editsitepage_pagetext').value; };
    }
    // IE seems to need this but I don't know why.
    callLater(0.001,function() {
        originalcontent = getEditorContent();
        requestPageText();
    });
    connect('editsitepage_pagename', 'onchange', requestPageText);

    connect('editsitepage_pagename', 'onkeydown', function(e) {
        if (e.key().code == 9 && !e.modifier().shift) {
            tinyMCE.execCommand('mceFocus',false,'mce_editor_0');
            e.stop();
        }
    });
}

addLoadEvent(onLoad);
EOJS;

$smarty = smarty();
$smarty->assign('pageeditform', $form);
$smarty->assign('INLINEJAVASCRIPT', $ijs);
$smarty->display('admin/site/pages.tpl');

?>
