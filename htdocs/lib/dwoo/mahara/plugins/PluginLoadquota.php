<?php

/**
 * Core {loadquota} function plugin
 *
 * Type:     function<br>
 * Name:     loadquota<br>
 * Date:     June 22, 2006<br>
 * Purpose:  Set quota related variables for the quota template
 * @author   Catalyst IT Ltd
 * @version  1.0
 * @return Nothing
 */

use Dwoo\Core;

function PluginLoadquota(Core $core) {
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

    $core->assignInScope($quota_message, 'QUOTA_MESSAGE');
    if ($quota == 0) {
        $core->assignInScope(100, 'QUOTA_PERCENTAGE');
    } else {
        $core->assignInScope(round($quotaused / $quota * 100), 'QUOTA_PERCENTAGE');
    }
}

?>
