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
define('JSON', 1);
require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'collection.php');

$id = param_integer('id');
if (!can_view_view($id)) {
    json_reply('local', get_string('accessdenied', 'error'));
}
$firstview = new View($id);
$collection = $firstview->get('collection');

$smarty = smarty_core();
$smarty->assign('viewid', $id);
$smarty->assign('collectiontitle', $collection->get('name'));
$smarty->assign('ownername', $firstview->formatted_owner());
$smarty->assign('collectiondescription', $collection->get('description'));
$smarty->assign('viewcontent', $firstview->build_rows(false, true));
$smarty->assign('tags', $firstview->get('tags'));

$shownav = $collection->get('navigation');
if ($shownav) {
    if ($views = $collection->get('views')) {
        if (count($views['views']) > 1) {
            $smarty->assign('collection', array_chunk($views['views'], 5));
        }
    }
}

$html = $smarty->fetch('collection/viewcontent.tpl');

json_reply(false, array(
    'message' => null,
    'html' => $html,
));
