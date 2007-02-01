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
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(__FILE__)) . '/init.php');

json_headers();

$pluginname = param_variable('pluginname');
$parent     = param_integer('parent', null);
$userid     = param_integer('userid', $USER->get('id'));

if ($parent === null) {
    $parentcondition = 'IS NULL';
}
else {
    $parentcondition = ' = ' . $db->quote($parent);
}
$prefix = get_config('dbprefix');

// Get all artefacts we require
$data = get_records_sql_array("SELECT id, artefacttype, container, title
    FROM " . $prefix . "artefact
    WHERE artefacttype IN (
        SELECT name
            FROM " . $prefix . "artefact_installed_type
            WHERE plugin = ?
    )
    AND parent $parentcondition
    AND owner = ?
    ORDER BY title", array($pluginname, $userid));
if (!$data) {
    echo json_encode(array('error' => false,
                           'data' => false));
    exit;
}

// Format the data for return. Each artefact type has the opportunity to format
// the data how they see fit
safe_require('artefact', $pluginname);
$artefacts = array();
foreach ($data as $artefact) {
    $classname = 'ArtefactType' . ucfirst($artefact->artefacttype);
    $a = null;
    if (method_exists($classname, 'format_child_data')) {
        $a= call_static_method($classname, 'format_child_data', $artefact, $pluginname);
    }
    else {
        $a = new StdClass;
        $a->id         = $artefact->id;
        $a->isartefact = true;
        $a->title      = '';
        $a->text       = $artefact->title;
        $a->container  = (bool) $artefact->container;
        $a->parent     = $artefact->id;
    }
    $a->artefacttype = $artefact->artefacttype;
    $artefacts[]   = $a;
}

$classname = generate_class_name('artefact', $pluginname);
if (method_exists($classname, 'sort_child_data')) {
    usort($artefacts, array($classname, 'sort_child_data'));
}

// Build the JSON to return
$items = array();
foreach ($artefacts as $artefact) {
    $artefactclass = generate_artefact_class_name($artefact->artefacttype);
    $items[] = array(
        'id'         => $artefact->id,
        'isartefact' => $artefact->isartefact,
        'container'  => $artefact->container,
        'text'       => $artefact->text,
        'title'      => $artefact->title,
        'pluginname' => $pluginname,
        'parent'     => $artefact->parent,
        'type'       => $artefact->artefacttype,
        'rendersto'  => call_static_method($artefactclass, 'get_render_list')
    );
}
echo json_encode(array('error' => false,
                       'data' => $items));

?>
