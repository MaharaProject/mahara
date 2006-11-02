<?php
/**
 * This program is part of mahara
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
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL',1);
require(dirname(dirname(__FILE__)).'/init.php');
require_once('form.php');

$sitepages = get_records('site_content');
$pageoptions = array();
foreach ($sitepages as $page) {
    $pageoptions[$page->name] = get_string($page->name);
}
asort($pageoptions);

$f = array(
    'name' => 'editsitepage',
    'method' => 'post',
    'onsubmit' => 'return submitForm(\'editsitepage\',\'savesitepage.json.php\');',
    'action' => '',
    'elements' => array(
        'pagename' => array(
            'type' => 'select',
            'title' => get_string('pagename'),
            'value' => 'home',
            'options' => $pageoptions
        ),
        'pagetext' => array(
            'name' => 'pagetext',
            'type' => 'wysiwyg',
            'rows' => 25,
            'cols' => 85,
            'title' => get_string('pagecontent'),
            'description' => get_string('textdesc'),
            'value' => 'blah',
            'rules' => array(
                'required' => true
            )
        ),
        'submit' => array(
            'value' => get_string('savechanges'),
            'type'  => 'submit',
        )
    )
);

$form = form($f);
$js = array('mochikit','mahara');
if (use_html_editor()) {
    array_unshift($js,'tinymce');
}

$ijs = <<< EOJS
setEditorContent = function(content) { $('pagetext').innerHTML = content; };
function onLoad() {
    requestPageText();
    connect('pagename', 'onchange', requestPageText);
    if (typeof(tinyMCE) != 'undefined') {
        setEditorContent = tinyMCE.setContent;
    }
}
function requestPageText() {
    var d = loadJSONDoc('editchangepage.json.php',{'pagename':$('pagename').value});
    d.addCallback(function(data) {
        if (data.success) {
            setEditorContent(data.content);
        }
        else {
            displayMessage({'message':get_string('failedloadingpagecontent', $('pagename').value),
                                'type':'error'});
        }
    });
}
addLoadEvent(onLoad);
EOJS;

$jsstrings = array('requiredfieldempty','noresponse');

$smarty = smarty($js,array(),$jsstrings);
$smarty->assign('pageeditform', $form);
$smarty->assign('INLINEJAVASCRIPT', $ijs);
$smarty->display('admin/editsitepage.tpl');

?>