<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {loadquota} function plugin
 *
 * Type:     function<br>
 * Name:     loadquota<br>
 * Date:     June 22, 2006<br>
 * Purpose:  Set quota related variables for the quota template
 * @author   Martyn Smith <martyn@catalyst.net.nz>
 * @version  1.0
 * @param array
 * @param Smarty
 * @return Nothing
 */
function smarty_function_loadquota($params, &$smarty) {
    global $USER;

    if (!$USER->is_logged_in()) {
        return;
    }

    $quota     = $USER->get('quota');
    $quotaused = $USER->get('quotaused');

    if ($quota >= 1048576) {
        $quota_message = get_string('quotausage', 'mahara', sprintf('%0.1fMB', $USER->get('quotaused') / 1048576), sprintf('%0.1fMB', $quota / 1048567));
    }
    else if ($quota >= 1024) {
        $quota_message = get_string('quotausage', 'mahara', sprintf('%0.1fKB', $USER->get('quotaused') / 1024), sprintf('%0.1fKB', $quota / 1024));
    }
    else {
        $quota_message = get_string('quotausage', 'mahara', sprintf('%d bytes', $USER->get('quotaused')), sprintf('%d bytes', $quota));
    }

    $smarty->assign('QUOTA_MESSAGE', $quota_message);
    if ($quota == 0) {
        $smarty->assign('QUOTA_PERCENTAGE', 100);
    } else {
        $smarty->assign('QUOTA_PERCENTAGE', round($quotaused / $quota * 100));
    }

    return;
}

?>

