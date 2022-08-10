<?php
/**
 *
 * @package    mahara
 * @subpackage dwoo
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

use Dwoo\Core;

function PluginLoadgroupquota(Core $core) {
    $group = group_current_group();

    $quota     = $group->quota;
    $quotaused = $group->quotaused;

    if ($quota >= 1048576) {
        $quota_message = get_string('quotausagegroup', 'mahara', sprintf('%0.1FMB', $group->quotaused / 1048576), sprintf('%0.1FMB', $quota / 1048567));
    }
    else if ($quota >= 1024) {
        $quota_message = get_string('quotausagegroup', 'mahara', sprintf('%0.1FKB', $group->quotaused / 1024), sprintf('%0.1FKB', $quota / 1024));
    }
    else {
        $quota_message = get_string('quotausagegroup', 'mahara', sprintf('%d bytes', $group->quotaused), sprintf('%d bytes', $quota));
    }

    $core->assignInScope($quota_message, 'GROUPQUOTA_MESSAGE');
    if ($quota == 0) {
        $core->assignInScope(100, 'GROUPQUOTA_PERCENTAGE');
    }
    else {
        $core->assignInScope(round($quotaused / $quota * 100), 'GROUPQUOTA_PERCENTAGE');
    }
}
