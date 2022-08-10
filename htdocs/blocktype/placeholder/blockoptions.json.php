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
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('libroot') . 'view.php');
safe_require('blocktype', 'placeholder');

$offset = param_integer('offset', 0);
$limit = param_integer('limit', 8);
$viewid = param_integer('viewid');
$blockid = param_integer('blockid');
if (!can_view_view($viewid)) {
    json_reply('local', get_string('accessdenied', 'error'));
}
$view = new View($viewid);
$setlimit = false;
$orderby = param_alphanum('orderby', null);

list($count, $types) = PluginBlocktypePlaceholder::get_content_types($view, $offset, $limit);
$pagination = build_showmore_pagination(array(
    'count'  => $count,
    'limit'  => $limit,
    'offset' => $offset,
    'orderby' => 'popular',
    'databutton' => 'showmorebtn',
    'jscall' => 'wire_blockoptions',
    'jsonscript' => 'blocktype/placeholder/blockoptions.json.php',
    'extra' => array('viewid' => $viewid,
                     'blockid' => $blockid),
));

$smarty = smarty_core();
$smarty->assign('blockid', $blockid);
$smarty->assign('types', $types);
$typeslist = $smarty->fetch('blocktype:placeholder:contenttypeslist.tpl');
$typeslist .= $pagination['html'];

json_reply(false, array(
    'message' => null,
    'data' => array(
        'tablerows' => $typeslist,
        'pagination_js' => $pagination['javascript'],
        'count' => $count,
        'results' => $count . ' ' . ($count == 1 ? get_string('result') : get_string('results')),
        'offset' => $offset,
        'setlimit' => $setlimit,
        'jscall' => 'wire_blockoptions',
    )
));
