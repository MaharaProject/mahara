<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-internal
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
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

    if ($oldversion < 2012071100) {
        // Add new column 'path' to table interaction_forum_post used for diplaying posts by threads
        $table = new XMLDBTable('interaction_forum_post');
        $field = new XMLDBField('path');
        $field->setAttributes(XMLDB_TYPE_CHAR, 2048, null, null);
        add_field($table, $field);

        $index = new XMLDBIndex('pathix');
        $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('path'));
        add_index($table, $index);

        // Update the column 'path' for all posts in the old database
        $done = 0;
        $lastid = 0;
        $pwcount = count_records('interaction_forum_post');
        if (is_mysql()) {
            $mp = mysql_get_variable('max_allowed_packet');
            $limit = ($mp && is_numeric($mp) && $mp > 1048576) ? ($mp / 8192) : 100;
        }
        else {
            $limit = 2000;
        }
        while ($posts = get_records_select_array('interaction_forum_post', 'id > ?', array($lastid), 'id', 'id, parent', 0, $limit)) {
            foreach ($posts as $post) {
                // Update the column 'path'
                $path = sprintf('%010d', $post->id);
                $parentid = $post->parent;
                while (!empty($parentid)) {
                    if ($p = get_record_select('interaction_forum_post', 'id = ?', array($parentid), 'parent, path')) {
                        if (!empty($p->path)) {
                            $path = $p->path . '/' . $path;
                            break;
                        }
                        $path = sprintf('%010d', $parentid) . '/' . $path;
                        $parentid = $p->parent;
                    }
                    else {
                        throw new SQLException("Can't find the post with id = '$parentid'");
                    }
                }
                $post->path = $path;
                update_record('interaction_forum_post', $post);
                $lastid = $post->id;
            }
            $done += count($posts);
            log_debug("Updating posts' path: $done/$pwcount");
            set_time_limit(50);
        }
    }

    if ($oldversion < 2014050800) {

        // Subscribe admins to new activity.
        $adminusers = get_column('usr', 'id', 'admin', 1, 'deleted', 0);
        activity_add_admin_defaults($adminusers);
    }

    if ($oldversion < 2014060500) {
        // Drop unused fields.
        $table = new XMLDBTable('interaction_forum_post');
        $field = new XMLDBField('reported');
        if (field_exists($table, $field)) {
            drop_field($table, $field, true);
        }
        $field = new XMLDBField('reportedreason');
        if (field_exists($table, $field)) {
            drop_field($table, $field, true);
        }
    }

    return true;
}
