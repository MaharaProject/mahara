<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-pdf
 * @author     Son Nguyen, Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

/**
 * This displays a pdf in an <iframe>
 *
 */
define('INTERNAL', 1);
define('PUBLIC', 1);
require(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/init.php');
require_once(get_config('docroot') . '/artefact/lib.php');

$fileid = param_integer('file');
$viewid = param_integer('view');

if (!artefact_in_view($fileid, $viewid)) {
    throw new AccessDeniedException('');
}

if (!can_view_view($viewid)) {
    throw new AccessDeniedException('');
}

$file = artefact_instance_from_id($fileid);
if (!($file instanceof ArtefactTypeFile)) {
    throw new NotFoundException();
}

$smarty = smarty();
$smarty->assign('url', get_config('wwwroot') . 'artefact/file/download.php?file='.$fileid.'&view='.$viewid);
$smarty->assign('title', $file->get('title'));
$smarty->display('blocktype:pdf:pdf.tpl');
