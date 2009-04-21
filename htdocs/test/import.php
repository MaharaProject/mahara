<?php
define('INTERNAL', 1);
define('PUBLIC', 1);
require_once(dirname(dirname(__FILE__)) . '/init.php');
require_once(dirname(dirname(__FILE__)) . '/import/lib.php');

$userid = 1;
// NOTE: artefact.file can't import files that aren't in dataroot, the export file has to be in dataroot too.
$filename = /*get_config('dataroot') .*/ 'nigeltest/leap2a.xml';



//$userobj = new User();
//$userobj->find_by_id($userid);

$importer = PluginImport::create_importer(null, (object)array(
    'token'      => '',
    //'host'       => '',
    'usr'        => $userid,
    'queue'      => (int)!(PluginImport::import_immediately_allowed()), // import allowed straight away? Then don't queue
    'ready'      => 0, // maybe 1?
    'expirytime' => db_format_timestamp(time()+(60*60*24)),
    'format'     => 'leap',
    'data'       => array('filename' => $filename)
));
$importer->process();

?>
