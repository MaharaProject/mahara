<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2011 Catalyst IT Ltd and others; see:
 *                    http://wiki.mahara.org/Contributors
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
 * @subpackage notification-internal
 * @author     Richard Mansfield
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 *
 */

defined('INTERNAL') || die();

function xmldb_notification_internal_upgrade($oldversion=0) {

    if ($oldversion < 2011112300) {
        execute_sql("
            UPDATE {notification_internal_activity}
            SET url = REPLACE(url, ?, '')
            WHERE url IS NOT NULL",
            array(get_config('wwwroot'))
        );
    }

    if ($oldversion < 2012021000) {
        // Populate the unread count on the usr table
        if (is_postgres()) {
            execute_sql('
                UPDATE {usr} SET unread = n.unread FROM (
                    SELECT usr, SUM(1 - read) AS unread FROM {notification_internal_activity} GROUP BY usr
                ) n WHERE {usr}.id = n.usr;'
            );
        }
        else if (is_mysql()) {
            execute_sql('
                UPDATE {usr} u, (SELECT usr, SUM(1 - "read") AS unread FROM {notification_internal_activity} GROUP BY usr) n
                SET u.unread = n.unread
                WHERE u.id = n.usr'
            );
        }

        // Create triggers to maintain the unread count
        db_create_trigger(
            'update_unread_insert',
            'AFTER', 'INSERT', 'notification_internal_activity', '
            IF NEW.read = 0 THEN
                UPDATE {usr} SET unread = unread + 1 WHERE id = NEW.usr;
            END IF;'
        );
        db_create_trigger(
            'update_unread_update',
            'AFTER', 'UPDATE', 'notification_internal_activity', '
            IF OLD.read = 0 AND NEW.read = 1 THEN
                UPDATE {usr} SET unread = unread - 1 WHERE id = NEW.usr;
            ELSEIF OLD.read = 1 AND NEW.read = 0 THEN
                UPDATE {usr} SET unread = unread + 1 WHERE id = NEW.usr;
            END IF;'
        );
        db_create_trigger(
            'update_unread_delete',
            'AFTER', 'DELETE', 'notification_internal_activity', '
            IF OLD.read = 0 THEN
                UPDATE {usr} SET unread = unread - 1 WHERE id = OLD.usr;
            END IF;'
        );
    }

    return true;
}
