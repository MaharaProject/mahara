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
require(dirname(dirname(__FILE__)) . '/init.php');

// We relocated this file to view/index.php now that we have merged the pages and collections list.
// Redirect to the new URL.
$groupid = param_integer('group', 0);
$institutionname = param_alphanum('institution', false);
if ($groupid) {
    redirect(get_config('wwwroot') . 'view/groupviews.php?' . $_SERVER['QUERY_STRING']);
}
else if ($institutionname) {
    if ($institutionname == 'mahara') {
        redirect(get_config('wwwroot') . 'admin/site/views.php?' . $_SERVER['QUERY_STRING']);
    }
    else {
        redirect(get_config('wwwroot') . 'view/institutionviews.php?' . $_SERVER['QUERY_STRING']);
    }
}
else {
    redirect(get_config('wwwroot') . 'view/index.php?' . $_SERVER['QUERY_STRING']);
}
