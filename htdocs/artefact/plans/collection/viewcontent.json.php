<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-plans
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @author     Alexander Del Ponte <delponte@uni-bremen.de>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);
require(dirname(dirname(dirname(dirname(__FILE__)))) . '/init.php');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'collection.php');

$id = param_integer('id');
$collection = new \Collection($id);

$firstview = $collection->first_view();

if ($firstview === null) {
    json_reply(true, array(
        'message' => get_string('noviewsincollection', 'collection'),
    ));
}

if (!can_view_view($firstview)) {
    json_reply('local', get_string('accessdenied', 'error'));
}
$collection = $firstview->get('collection');

if ($firstview->uses_new_layout()) {
    $blocks = $firstview->get_blocks(false, true);
    $newlayout = true;
}
else {
    $blocks = $firstview->build_rows();
    $newlayout = false;
}

$smarty = smarty_core();
$smarty->assign('viewid', $id);
$smarty->assign('collectiontitle', $collection->get('name'));
$smarty->assign('ownername', $firstview->formatted_owner());
$smarty->assign('collectiondescription', $collection->get('description'));
$smarty->assign('newlayout', $newlayout);
$smarty->assign('blocks', $blocks);
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
