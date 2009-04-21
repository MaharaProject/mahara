<?php

define('INTERNAL', 1);
require_once(dirname(dirname(__FILE__)) . '/init.php');

safe_require('export', 'leap');

$userobj = new User();
$userobj->find_by_id(1);
$exporter = new PluginExportLeap($userobj, EXPORT_ALL_VIEWS, EXPORT_ALL_ARTEFACTS);

$exporter->export();

//header('Content-Type: text/xml');
header('Content-Disposition: inline; filename=leap.xml');

echo '<pre>';
echo hsc($exporter->get('xml'));
echo '</pre>';

?>

