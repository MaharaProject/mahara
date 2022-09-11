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

require(dirname(dirname(__FILE__)) . '/init.php');

global $USER;
require_once(get_config('libroot') . 'view.php');

$viewid = param_integer('viewid', null);


$linklist= array();

list($collections, $views) = View::get_views_and_collections( $USER->get('id'));
if (!empty($views) || !empty($collections)) {
    foreach ($views as $view) {
        if ($viewid != $view['id']) { // don't list the current view
            $linklist[] = array(
                'title' => $view['name'],
                'value' => $view['url'],
            );
        }
    }
    foreach ($collections as $collection) {
        foreach ($collection['views'] as $view) {
            if ($viewid != $view['id']) {
                // tinymce dropped support for nested link lists. If it changes we should
                // use a nested way of displaying the collection titles and their pages
                $linklist[] = array(
                    'title' => $view['name'] . ' (' . $collection['name'] . ')',
                    'value' => $view['url'],
                );
            }
        }
    }
}
usort($linklist, function($a, $b) {
    return strnatcasecmp($a['title'], $b['title']);
});

json_reply(false, array('data' => json_encode($linklist), 'count' => count($linklist)));
