<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);

define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'view');
define('SECTION_PAGE', 'download');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');

$viewid = param_integer('id');
$collection = param_integer('collection', null);

$view = new View($viewid);
if (!can_view_view($view)) {
    throw new AccessDeniedException(get_string('thisviewmaynotbecopied', 'view'));
}
if (!$view->is_copyable()) {
    throw new AccessDeniedException(get_string('thisviewmaynotbecopied', 'view'));
}

safe_require('export', 'leap');
$user = new User();
$user->find_by_id($view->get('owner'));

if (isset($collection)) {
    //get all views in collection
    require_once(get_config('libroot') . 'collection.php');
    $colltemplate = new Collection($collection);
    $views = $colltemplate->views();
    $views = array_column($views['views'], 'view');

    $artefacts = PluginExport::EXPORT_LIST_OF_COLLECTIONS;
}
else {
    $views = array($view->get('id'));
    $artefacts = PluginExport::EXPORT_ARTEFACTS_FOR_VIEWS;
}

$exporter = new PluginExportLeap($user, $views, $artefacts);

$exporter->includefeedback = false; // currently only doing leap2a exports and they can't handle feedback

try {
  $zipfile = $exporter->export();
}
catch (SystemException $e) {
  $errors[] = get_string('exportzipfileerror', 'export', $e->getMessage());
  log_warn($e->getMessage());
}

require_once('file.php');
serve_file($exporter->get('exportdir') . $zipfile, $zipfile, 'application/x-zip', array('lifetime' => 0, 'forcedownload' => true));
