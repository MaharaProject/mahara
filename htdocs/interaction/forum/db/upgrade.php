<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
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
 * @subpackage artefact-internal
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

function xmldb_interaction_forum_upgrade($oldversion=0) {
    if ($oldversion < 2009062300) {
        foreach (array('topic', 'forum') as $type) {
            log_debug("Subscription upgrade for {$type}s");
            // Add missing primary key to the subscription tables
            // Step 1: remove duplicates
            if ($dupes = get_records_sql_array('
                SELECT "user", ' . $type . ', COUNT(*)
                FROM {interaction_forum_subscription_' . $type . '}
                GROUP BY "user", ' . $type . '
                HAVING COUNT(*) > 1', array())) {
                // We found duplicate subscriptions to a topic/forum
                foreach ($dupes as $dupe) {
                    log_debug("interaction.forum: Removing duplicate $type subscription for {$dupe->user}");
                    delete_records('interaction_forum_subscription_' . $type, 'user', $dupe->user, $type, $dupe->$type);
                    insert_record('interaction_forum_subscription_' . $type, (object)array(
                        'user' => $dupe->user,
                        $type  => $dupe->$type,
                    ));
                }
            }
            // Step 2: add the actual key
            $table = new XMLDBTable('interaction_forum_subscription_' . $type);
            $key   = new XMLDBKey('primary');
            $key->setAttributes(XMLDB_KEY_PRIMARY, array('user', $type));
            add_key($table, $key);

            // Add a 'key' column, used for unsubscriptions
            $field = new XMLDBField('key');
            $field->setAttributes(XMLDB_TYPE_CHAR, 50, XMLDB_UNSIGNED, null);
            add_field($table, $field);

            $key = new XMLDBKey('keyuk');
            $key->setAttributes(XMLDB_KEY_UNIQUE, array('key'));
            add_key($table, $key);

            // Populate the key column
            if ($records = get_records_array('interaction_forum_subscription_' . $type, '', '', '', '"user", ' . $type)) {
                foreach ($records as $where) {
                    $new = (object)array(
                        'user' => $where->user,
                        $type  => $where->$type,
                        'key'  => dechex(mt_rand()),
                    );

                    update_record('interaction_forum_subscription_' . $type, $new, $where);
                }
            }

            // Now make the key column not null
            $field->setAttributes(XMLDB_TYPE_CHAR, 50, XMLDB_UNSIGNED, XMLDB_NOTNULL);
            change_field_notnull($table, $field);
        }
    }

    if ($oldversion < 2009081700) {
        if (!get_record('interaction_config', 'plugin', 'forum', 'field', 'postdelay')) {
            insert_record('interaction_config', (object) array('plugin' => 'forum', 'field' => 'postdelay', 'value' => 30));
        }
    }

    if ($oldversion < 2009081800) {
        $subscription = (object) array('plugin' => 'forum', 'event' => 'creategroup', 'callfunction' => 'create_default_forum');
        ensure_record_exists('interaction_event_subscription', $subscription, $subscription);
    }

    return true;
}
