<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2012 Catalyst IT Ltd and others; see:
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
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('MENUITEM', 'configsite/sitelicenses');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'admin');
define('SECTION_PAGE', 'sitepages');

require(dirname(dirname(dirname(__FILE__))).'/init.php');
require_once('license.php');
require_once('pieforms/pieform.php');
define('TITLE', get_string('sitelicenses', 'admin'));
define('DEFAULTPAGE', 'home');

$extralicensessql = "
    SELECT license FROM artefact WHERE license IS NOT NULL and license <> ''
    EXCEPT
    SELECT name FROM artefact_license
";

$edit = param_variable('edit', null);

if ($edit !== null) {
    $edit = get_record('artefact_license', 'name', $edit);
}

$elements = array(
    'displayname' => array(
        'type' => 'text',
        'title' => get_string('licensedisplaynamelabel', 'admin'),
        'rules' => array('required' => true, 'maxlength' => 255),
    ),
    'name' => array(
        'type' => 'text',
        'title' => get_string('licensenamelabel', 'admin'),
        'rules' => array('required' => true, 'maxlength' => 255),
    ),
    'name2' => array(
        'type' => 'html',
        'title' => get_string('licensenamelabel', 'admin'),
        'ignore' => true,
    ),
    'shortname' => array(
        'type' => 'text',
        'title' => get_string('licenseshortnamelabel', 'admin'),
    ),
    'icon' => array(
        'type' => 'text',
        'title' => get_string('licenseiconlabel', 'admin'),
        'help' => true,
    ),
    'submit' => array(
        'type' => 'submit',
        'value' => get_string('licensesave', 'admin'),
    ),
);
if (empty($edit)) {
    $options = array();
    foreach (get_column_sql($extralicensessql) as $o) {
        $options[$o] = $o;
    }
    if (count($options)) {
        $elements['name']['type'] = 'select';
        $elements['name']['allowother'] = true;
        $elements['name']['options'] = $options;
    }
}
else {
    $elements['name']['value'] = $edit->name;
    $elements['name']['type'] = 'hidden';
    $elements['name2']['value'] = '<a href="' . hsc($edit->name) . '" target="_blank">' . hsc($edit->name) . '</a>';
    unset($elements['name2']['ignore']);
    foreach (array('displayname', 'shortname', 'icon') as $f) {
        $elements[$f]['defaultvalue'] = $edit->{$f};
    }
}

$form = pieform(array(
    'name' => 'license',
    'plugintype' => 'core',
    'pluginname' => 'core',
    'elements' => $elements,
));


function license_submit(Pieform $form, $values) {
    global $SESSION;
    $data = new StdClass;
    foreach (array('name', 'displayname', 'shortname', 'icon') as $f) {
        $data->{$f} = trim($values[$f]);
    }

    db_begin();
    delete_records('artefact_license', 'name', $data->name);
    insert_record('artefact_license', $data);
    db_commit();
    $SESSION->add_ok_msg(get_string('licensesaved', 'admin'));
    redirect('/admin/site/licenses.php');
}

$smarty = smarty();
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('form', $form);
$smarty->assign('enabled', get_config('licensemetadata'));
$smarty->display('admin/site/license-edit.tpl');
