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
define('PUBLIC', 1);
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'site');
define('SECTION_PAGE', 'unsubscribe');

require(dirname(dirname(__FILE__)) . '/init.php');
require('view.php');

$unsubscribed = false;
$viewtitle = false;
if (param_exists('a') && param_exists('t')) {
    $type = param_alphanum('a');
    $token = param_alphanum('t');

    // Currently for watchlist
    $validtypes = array('watchlist' => 'usr_watchlist_view');
    $table = get_config('dbprefix') . $validtypes[$type];
    if ($results = get_records_sql_array("SELECT * FROM ". $table . " WHERE unsubscribetoken = ?", array($token))) {
        $viewid = $results[0]->view;
        $view = new View($viewid);
        $viewtitle = $view->get('title');
        delete_records_sql("DELETE FROM ". $table . " WHERE unsubscribetoken = ?", array($token));
        $unsubscribed = true;
    }
    else if ($USER->is_logged_in()) {
        // redirect to homepage
        redirect('/');
    }
}

define('TITLE', get_string('unsubscribetitle', 'notification.email'));

$smarty = smarty();
$smarty->assign('unsubscribed', $unsubscribed);
if ($viewtitle) {
    $smarty->assign('heading', get_string('unsubscribe_' . $type . '_heading', 'notification.email', $viewtitle));
}
$smarty->display('unsubscribe.tpl');
