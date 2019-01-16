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
$editing = param_boolean('editing', false);
$ingroup = param_boolean('ingroup', false);
$versioning = param_boolean('versioning', false);

if (!can_view_view($viewid)) {
  throw new AccessDeniedException('');
}

if (!$versioning && !artefact_in_view($fileid, $viewid)) {
    throw new AccessDeniedException('');
}

$file = artefact_instance_from_id($fileid);
if (!($file instanceof ArtefactTypeFile)) {
    throw new NotFoundException();
}

$urlbase = get_config('wwwroot');
if (get_config('cleanurls') && get_config('cleanurlusersubdomains') && !$editing && !$ingroup) {
    $view = new View($viewid);
    $viewauthor = new User();
    $viewauthor->find_by_id($view->get('owner'));
    $viewauthorurlid = $viewauthor->get('urlid');
    if ($urlallowed = !is_null($viewauthorurlid) && strlen($viewauthorurlid)) {
        $urlbase = profile_url($viewauthor) . '/';
    }
}

$smarty = smarty();
$smarty->assign('url', $urlbase . 'artefact/file/download.php?file='.$fileid.'&view='.$viewid.'&title='.urlencode($file->get('title')));
$smarty->assign('title', $file->get('title'));
$smarty->assign('cacheversion', get_config('cacheversion'));
$smarty->display('blocktype:pdf:pdf.tpl');
