<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-pdf
 * @author     Son Nguyen
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
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

$fileid = param_integer('artefactid');
$viewid = param_integer('view');
$editing = param_boolean('editing', false);
$ingroup = param_boolean('ingroup', false);
$versioning = param_boolean('versioning', false);
$lang = param_variable('lang');

if (!can_view_view($viewid)) {
  throw new AccessDeniedException();
}

if (!$versioning && !artefact_in_view($fileid, $viewid)) {
    throw new AccessDeniedException();
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
$url = $urlbase . 'artefact/file/download.php?file=' . $fileid . '&view=' . $viewid . '&title=' . urlencode($file->get('title'));

// by default the default url inside of the wrong folder blocktype/pdf
$js =<<<EOF
    // load lang first to not get to en_US
   PDFViewerApplicationOptions.set('locale', '$lang');

    document.addEventListener('DOMContentLoaded', (event) => {
        PDFViewerApplicationOptions.set('defaultUrl', '$url');
    });
EOF;

$smarty = smarty();
$smarty->assign('url', $url);
$smarty->assign('js', $js);
$smarty->assign('title', $file->get('title'));
$smarty->assign('cacheversion', get_config('cacheversion'));
$smarty->display('blocktype:pdf:pdf.tpl');
