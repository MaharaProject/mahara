<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-resume
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('docroot') . 'artefact/lib.php');

$id = param_integer('id');
$artefact = param_integer('artefact');


$a = artefact_instance_from_id($artefact);

if ($a->get('owner') != $USER->get('id')) {
    throw new AccessDeniedException(get_string('notartefactowner', 'error'));
}

delete_records($a->get_other_table_name(), 'id', $id);
$count = count_records($a->get_other_table_name(), 'artefact', $artefact);
if (empty($count)) {
    $a->delete();
}
else {
    $a->set('mtime', time());
    $a->commit();
}

json_reply(null, get_string('compositedeleted', 'artefact.resume'));
