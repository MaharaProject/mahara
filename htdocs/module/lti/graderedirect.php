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
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('libroot') . 'collection.php');
require_once(get_config('libroot') . 'view.php');

// We relocated this file to view/index.php now that we have merged the pages and collections list.
// Redirect to the new URL.
$collectionid = param_integer('collectionid', 0);
$viewid = param_integer('viewid', 0);

if ($collectionid) {

    $collection = new Collection($collectionid);
    redirect($collection->get_url());
}
else if ($viewid) {
    $view = new View($viewid);
    redirect($view->get_url());
}
