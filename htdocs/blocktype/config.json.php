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
define('ADMIN', 1);
define('JSON', 1);
require(dirname(dirname(__FILE__)) . '/init.php');

$id = param_alphanum('id');
$direction = param_variable('direction', '');

$message = null;

if (!empty($direction)) {
    parse_str($direction, $direction_array);
    if (is_array($direction_array['row']) && !empty($direction_array['row'])) {
        foreach ($direction_array['row'] as $k => $v) {
            execute_sql("UPDATE {blocktype_installed_category} SET sortorder = ? WHERE blocktype = ?", array(($k + 1) * 1000, $v));
        }
    }
}
// return updated info
$types = array();
$blocks = get_records_sql_array("SELECT b.name, b.artefactplugin, bc.sortorder,
                                  (SELECT COUNT(*) FROM {block_instance} bi WHERE bi.blocktype = b.name) AS blockcount
                                  FROM {blocktype_installed} b
                                  JOIN {blocktype_installed_category} bc ON bc.blocktype = b.name
                                  WHERE b.active = 1
                                  AND b.name != ?
                                  ORDER BY bc.sortorder", array('placeholder'));
foreach ($blocks as $block) {
    $namespaced = blocktype_single_to_namespaced($block->name, $block->artefactplugin);
    safe_require('blocktype', $block->name);
    $classname = generate_class_name('blocktype', $namespaced);
    $types[] = array('name' => $block->name,
                     'title' => call_static_method($classname, 'get_title'),
                     'cssicon' => call_static_method($classname, 'get_css_icon', $block->name),
                     'cssicontype' => call_static_method($classname, 'get_css_icon_type', $block->name),
                     'count' => $block->blockcount,
                     );
}

$smarty = smarty_core();
$smarty->assign('types', $types);
$typeslist = $smarty->fetch('blocktype:placeholder:contenttypeslist.tpl');
$smarty->assign('typeslist', $typeslist);
$typeshtml = $smarty->fetch('blocktype:placeholder:contenttypes.tpl');
$message = get_string('blocktypeupdatedsuccess', 'admin');

json_reply(false, array(
    'message' => $message,
    'html' => $typeshtml,
));
