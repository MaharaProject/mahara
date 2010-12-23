<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
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
 * @subpackage artefact-resume
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', true);
define('MENUITEM', 'content/resume');
define('SECTION_PLUGINTYPE', 'artefact');
define('SECTION_PLUGINNAME', 'resume');

require_once(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('pieforms/pieform.php');
require_once('pieforms/pieform/elements/calendar.php');
require_once(get_config('docroot') . 'artefact/lib.php');

define('TITLE', get_string('resume', 'artefact.resume'));

$id = param_integer('id');
$artefact = param_integer('artefact');

$a = artefact_instance_from_id($artefact);
$type = $a->get('artefacttype');

$tabs = PluginArtefactResume::composite_tabs();
define('RESUME_SUBPAGE', $tabs[$type]);

if ($a->get('owner') != $USER->get('id')) {
    throw new AccessDeniedException(get_string('notartefactowner', 'error'));
}

$elements = call_static_method(generate_artefact_class_name($type), 'get_addform_elements');
$elements['submit'] = array(
    'type' => 'submitcancel',
    'value' => array(get_string('save'), get_string('cancel')),
    'goto' => get_config('wwwroot') . '/artefact/resume/' . $tabs[$type] . '.php',
);
$elements['compositetype'] = array(
    'type' => 'hidden',
    'value' => $type,
);
$cform = array(
    'name' => $type,
    'plugintype' => 'artefact',
    'pluginname' => 'resume',
    'elements' => $elements,
    'successcallback' => 'compositeformedit_submit',
);

$a->populate_form($cform, $id, $type);
$compositeform = pieform($cform);

$smarty = smarty();
$smarty->assign('compositeform', $compositeform);
$smarty->assign('composite', $type);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('SUBPAGENAV', PluginArtefactResume::submenu_items());
$smarty->display('artefact:resume:editcomposite.tpl');
