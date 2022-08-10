<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
define('MENUITEM', 'create/files');
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'core');
define('SECTION_PAGE', 'artefact');

require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'objectionable.php');
safe_require('artefact', 'comment');

$artefactid = param_integer('artefact');
$viewid     = param_integer('view');
$blockid    = param_integer('block', null);

$view = new View($viewid);
if (!can_view_view($view)) {
     throw new AccessDeniedException();
}

require_once(get_config('docroot') . 'artefact/lib.php');
$artefact = artefact_instance_from_id($artefactid);

if (!artefact_in_view($artefact, $viewid)) {
    throw new AccessDeniedException(get_string('artefactnotinview', 'error', $artefactid, $viewid));
}

$url = parse_url($_SERVER['QUERY_STRING']);
$query = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
parse_str($query, $params);

$viewtype = $view->get('type');
switch ($viewtype) {
    case 'portfolio':
    case 'grouphomepage':
        redirect(get_config('wwwroot') . 'view/view.php?id=' . $params['view'] . '&modal=1&artefact=' . $params['artefact']);
        break;
    default:
        redirect('/');
        $SESSION->add_error_msg(get_string('viewartefactdatavuamodal', 'mahara', $artefact->artefacttype, $artefact->title, $view->title));
}
