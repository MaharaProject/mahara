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
define('SECTION_PAGE', 'licenses');

require(dirname(dirname(dirname(__FILE__))).'/init.php');
require_once('license.php');
define('TITLE', get_string('sitelicenses', 'admin'));
define('DEFAULTPAGE', 'home');

if (param_exists('license_delete')) {
    $license_delete_array_keys = array_keys(param_variable('license_delete'));
    $del = array_shift($license_delete_array_keys);
    delete_records('artefact_license', 'name', $del);
    $SESSION->add_ok_msg(get_string('licensedeleted', 'admin'));
}

if (!isset($licenses)) {
    $licenses = get_records_assoc('artefact_license', null, null, 'displayname');
}
$extralicenses = get_column_sql("
    SELECT DISTINCT license
    FROM {artefact}
    WHERE license IS NOT NULL AND license <> ''
        AND license NOT IN (SELECT name FROM {artefact_license})
    ORDER BY license
");

$smarty = smarty();
setpageicon($smarty, 'icon-legal');

$smarty->assign('licenses', $licenses);
$smarty->assign('extralicenses', $extralicenses);
$smarty->assign('allowextralicenses', get_config('licenseallowcustom'));
$smarty->assign('enabled', get_config('licensemetadata'));
$smarty->display('admin/site/licenses.tpl');
