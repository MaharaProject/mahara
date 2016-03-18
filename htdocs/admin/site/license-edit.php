<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
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
define('DEFAULTPAGE', 'home');

$extralicensessql = "
    SELECT DISTINCT license
    FROM {artefact}
    WHERE license IS NOT NULL AND license <> ''
        AND license NOT IN (SELECT name FROM {artefact_license})
    ORDER BY license
";

$edit = param_variable('edit', null);
$title = get_string('sitelicensesadd', 'admin');

if ($edit !== null) {
    $edit = get_record('artefact_license', 'name', $edit);
    $title = get_string('sitelicensesedit', 'admin');
}

define('TITLE', $title);

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
        'class' => 'btn-primary',
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
    $elements['name2']['value'] = '<a href="' . hsc($edit->name) . '">' . hsc($edit->name) . '</a>';
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
$smarty->assign('form', $form);
$smarty->assign('enabled', get_config('licensemetadata'));
$smarty->display('admin/site/license-edit.tpl');
