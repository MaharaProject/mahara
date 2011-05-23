<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2011 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage dwoo
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2011 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

function Dwoo_Plugin_loadgroupquota(Dwoo $dwoo) {
    $group = group_current_group();

    $quota     = $group->quota;
    $quotaused = $group->quotaused;

    if ($quota >= 1048576) {
        $quota_message = get_string('quotausage', 'mahara', sprintf('%0.1fMB', $group->quotaused / 1048576), sprintf('%0.1fMB', $quota / 1048567));
    }
    else if ($quota >= 1024) {
        $quota_message = get_string('quotausage', 'mahara', sprintf('%0.1fKB', $group->quotaused / 1024), sprintf('%0.1fKB', $quota / 1024));
    }
    else {
        $quota_message = get_string('quotausage', 'mahara', sprintf('%d bytes', $group->quotaused), sprintf('%d bytes', $quota));
    }

    $dwoo->assignInScope($quota_message, 'GROUPQUOTA_MESSAGE');
    if ($quota == 0) {
        $dwoo->assignInScope(100, 'GROUPQUOTA_PERCENTAGE');
    }
    else {
        $dwoo->assignInScope(round($quotaused / $quota * 100), 'GROUPQUOTA_PERCENTAGE');
    }
}
