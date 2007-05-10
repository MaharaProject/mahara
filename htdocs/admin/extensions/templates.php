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
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);

define('MENUITEM', 'configextensions');
define('SUBMENUITEM', 'templatesadmin');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
define('TITLE', get_string('templatesadmin', 'admin'));
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'templates');
require_once(get_config('libroot') . 'template.php');


$installedtemplates = get_column('template', 'name');
$alltemplates = array();
$templates = get_dir_contents(get_config('dataroot') . 'templates/');
foreach ($templates as $dir) {
    $alltemplates[$dir] = array();
}
$templates = get_dir_contents(get_config('libroot') . 'templates/');
foreach ($templates as $dir) {
    if (!array_key_exists($dir,$templates)) {
        $alltemplates[$dir] = array();
    }
}

foreach (array_keys($alltemplates) as $t) {
    try {
        $alltemplates[$t]['template'] = template_parse($t);
    }
    catch (TemplateParserException $e) {
        $alltemplates[$t]['error'] = $e->getMessage();
    }
    if (in_array($t, $installedtemplates)) {
        $alltemplates[$t]['installed'] = true;
    }
}
$loadingicon = theme_get_url('images/loading.gif');
$successicon = theme_get_url('images/success.gif');
$failureicon = theme_get_url('images/failure.gif');

$loadingstring = get_string('upgradeloading', 'admin');
$successstring = get_string('upgradesuccess', 'admin');
$failurestring = get_string('upgradefailure', 'admin');

$javascript = <<<JAVASCRIPT

function installtemplate(name) {
    $(name + '.message').innerHTML = '<img src="{$loadingicon}" alt="{$loadingstring}" />';

    sendjsonrequest('templateinstall.json.php', { 'name': name }, 'GET', function (data) {
        if (!data.error) {
            var message = '{$successstring}';
            $(name + '.message').innerHTML = '<img src="{$successicon}" alt=":)" />  ' + message;
            // move the whole thing into the list of installed plugins 
            // new parent node
            $(name + '.status').src = '$successicon';
        }
        else {
            var message = '';
            if (data.message) {
                message = data.message;
            } 
            else {
                message = '{$failurestring}';
            }
            $(name).innerHTML = '<img src="{$failureicon}" alt=":(" /> ' + message;
        }
    },
    function () {
        message = '{$failurestring}';
        $(name).innerHTML = message;
    },
    true);
}
JAVASCRIPT;


$smarty = smarty();
$smarty->assign('INLINEJAVASCRIPT', $javascript);
$smarty->assign('templates', $alltemplates);
$smarty->assign('installlink', 'installtemplate');
$smarty->display('admin/extensions/templates.tpl');

?>
