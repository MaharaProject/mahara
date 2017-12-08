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

global $USER;
require_once(get_config('libroot') . 'view.php');

$viewid = param_integer('viewid', null);


$linklist= array();

list($collections, $views) = View::get_views_and_collections( $USER->get('id'));
if (!empty($views) || !empty($collections)) {
    foreach ($views as $view) {
        if ($viewid != $view['id']) { // dont list the current view
            $linklist[] = array(
                'title' => $view['name'],
                'value' => $view['url'],
            );
        }
    }
    foreach ($collections as $collection) {
        $collectionitem = array();
        if (!isset($collection['views'][$viewid])) { // dont list the collection that contains the current view
            $collectionitem['title'] = $collection['name'];
            usort($collection['views'], function($a, $b) {return $a['displayorder'] > $b['displayorder'];});
            foreach ($collection['views'] as $view) {
                if ($viewid != $view['id']) {
                    $collectionitem['menu'][] = array(
                        'title' => $view['name'],
                        'value' => $view['url'],
                    );
                }
            }
            $linklist[] = $collectionitem;
        }
    }
}
usort($linklist, function($a, $b) {
    return strnatcasecmp($a['title'], $b['title']);
});

json_reply(false, array('data' => json_encode($linklist), 'count' => count($linklist)));
