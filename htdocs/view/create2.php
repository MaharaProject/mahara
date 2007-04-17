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
 * @author     Martyn Smith <martyn@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'create2');

require(dirname(dirname(__FILE__)) . '/init.php');
define('TITLE', get_string('createviewstep2', 'view'));
require_once('template.php');
require_once('pieforms/pieform.php');

$createid = param_integer('createid');
$template = param_variable('template', null);
$action   = param_variable('action', null);

if ($action == 'back' ) {
    redirect('/view/create1.php?createid=' . $createid);
}

if ($action == 'cancel' ) {
    $SESSION->clear('create_' . $createid);
    redirect('/view/');
}

if ( $template !== null ) {
    $data = $SESSION->get('create_' . $createid);

    $data['template'] = $template;

    $SESSION->set('create_' . $createid, $data);

    redirect('/view/create3.php?createid=' . $createid);
}

define('MENUITEM', 'myviews');

$selecttemplate = get_string('usethistemplate', 'view');
$wwwroot = get_config('wwwroot');

$javascript = <<<JAVASCRIPT
var templates = new TableRenderer(
    'templates',
    'create2.json.php', 
    [
        function(r) { return TD(null, H3(null, r.title), P(null, r.description)); },
        function(r) { return TD(null, IMG({'src': '{$wwwroot}thumb.php?type=template&name=' + r.name})); },
        function(r) {
            var button = BUTTON({'type': 'button'}, '{$selecttemplate}');
            connect(button, 'onclick', function () {
                $('template').value = r.name;
                logDebug(r.name);
                logDebug($('template').value);
                document.forms.template_selection.submit();
                return false;
            });
            return TD(null,button);
        }
    ]
);

templates.statevars.push('category');
templates.category = '';

templates.updateOnLoad();
JAVASCRIPT;

$smarty = smarty(array('tablerenderer'));
// $smarty->assign('createview2', $createview2);
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->assign('createid', $createid);
$smarty->assign('categories', get_column('template_category','name'));
$smarty->display('view/create2.tpl');

?>
