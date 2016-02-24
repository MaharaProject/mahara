<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-externalfeed
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

function xmldb_blocktype_externalfeed_upgrade($oldversion=0) {

    if ($oldversion < 2008042101) {
        // We hit the 255 character limit for feed URLs
        if (is_postgres()) {
            execute_sql('ALTER TABLE {blocktype_externalfeed_data} ALTER COLUMN url TYPE TEXT');
        }
        else if (is_mysql()) {
            // If 2 URLs > 255 chars have the same first 255 characters then mahara will error - this is a MySQL issue though, their unique key length limit is to blame
            execute_sql('ALTER TABLE {blocktype_externalfeed_data} DROP KEY {blocextedata_url_uix}'); // We have to remove then add the constraint again else the change will make MySQL cry
            execute_sql('ALTER TABLE {blocktype_externalfeed_data} MODIFY COLUMN "url" text');
            execute_sql('ALTER TABLE {blocktype_externalfeed_data} add unique {blocextedata_url_uix} (url(255))');
        }
    }

    if ($oldversion < 2009121600) {
        if (is_mysql()) {
            // Make content column wider (TEXT is only 65kb in mysql)
            $table = new XMLDBTable('blocktype_externalfeed_data');
            $field = new XMLDBField('content');
            $field->setAttributes(XMLDB_TYPE_TEXT, "big", null, null);
            change_field_precision($table, $field);
        }
    }

    if ($oldversion < 2010073000) {
        execute_sql('
            UPDATE {blocktype_cron}
            SET minute = ?
            WHERE minute = ? AND hour = ? AND plugin = ? AND callfunction = ?',
            array('30', '0', '3', 'externalfeed', 'cleanup_feeds')
        );
    }

    if ($oldversion < 2011091400) {
        // Add columns for HTTP basic auth
        $table = new XMLDBTable('blocktype_externalfeed_data');
        $field1 = new XMLDBField('authuser');
        $field1->setAttributes(XMLDB_TYPE_TEXT);
        $field2 = new XMLDBField('authpassword');
        $field2->setAttributes(XMLDB_TYPE_TEXT);
        add_field($table, $field1);
        add_field($table, $field2);

        // Change unique constraint that's no longer valid
        $table = new XMLDBTable('blocktype_externalfeed_data');
        $index = new XMLDBIndex('url_uix');
        $index->setAttributes(XMLDB_INDEX_UNIQUE, array('url'));
        drop_index($table, $index);
        if (is_postgres()) {
            $index = new XMLDBIndex('urlautautix');
            $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('url', 'authuser', 'authpassword'));
            add_index($table, $index);
        }
        else if (is_mysql()) {
            // MySQL needs size limits when indexing text fields
            execute_sql('ALTER TABLE {blocktype_externalfeed_data} ADD INDEX
                           {blocextedata_urlautaut_ix} (url(255), authuser(255), authpassword(255))');
        }

    }

    if ($oldversion < 2011091401) {
        // Add columns for insecure SSL mode
        $table = new XMLDBTable('blocktype_externalfeed_data');
        $field = new XMLDBField('insecuresslmode');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 0);
        add_field($table, $field);
    }

    if ($oldversion < 2012090700) {
        // Reset all feeds to reset themselves
        set_field('blocktype_externalfeed_data', 'lastupdate', db_format_timestamp('0'));
        safe_require('blocktype', 'externalfeed');
        call_static_method('PluginBlocktypeExternalfeed', 'refresh_feeds');
    }

    if ($oldversion < 2014041500) {
        log_debug('Cleaning up duplicate feeds in the externalfeed blocktype');
        log_debug('1. Find the duplicate feed urls');
        // Setting these to be empty strings instead of NULL will make our SQL a lot simpler in the next section
        execute_sql("update {blocktype_externalfeed_data} set authuser='' where authuser is null");
        execute_sql("update {blocktype_externalfeed_data} set authpassword='' where authpassword is null");
        if ($duplicatefeeds = get_records_sql_array("SELECT COUNT(url), url, authuser, authpassword FROM {blocktype_externalfeed_data} GROUP BY url, authuser, authpassword HAVING COUNT(url) > 1 ORDER BY url, authuser, authpassword", array())) {
            log_debug('2. Get all feed ids for the duplicated feed urls');
            // Use the 1st one found to be the feed id for the block instances that need updating
            $feedstoupdate = array();
            foreach ($duplicatefeeds as $feed) {
                $feedids = get_column('blocktype_externalfeed_data', 'id', 'url', $feed->url, 'authuser', $feed->authuser, 'authpassword', $feed->authpassword);
                $feedstoupdate[$feed->url] = $feedids;
            }
            log_debug('3. Updating blocks to use correct feed id');
            // Find the block instances using external feeds. Check to see if they are not using the 'true' id and update them accordingly
            require_once(get_config('docroot') . 'blocktype/lib.php');
            $blockids = get_records_array('block_instance', 'blocktype', 'externalfeed', 'id ASC', 'id');
            foreach ($blockids as $blockid) {
                $blockinstance = new BlockInstance($blockid->id);
                $configdata = $blockinstance->get('configdata');
                if (!empty($configdata['feedid'])) {
                    foreach ($feedstoupdate as $url => $ids) {
                        foreach ($ids as $key => $id) {
                            if ($id == $configdata['feedid'] && $key != '0') {
                                $configdata['feedid'] = $ids[0];
                                $blockinstance->set('configdata', $configdata);
                                $blockinstance->set('dirty', true);
                                $blockinstance->commit();
                                break;
                            }
                        }
                    }
                }
            }
            log_debug('4. Removing orphaned feed rows');
            foreach ($feedstoupdate as $url => $ids) {
                foreach ($ids as $key => $id) {
                    if ($key != '0') {
                        execute_sql("DELETE FROM {blocktype_externalfeed_data} WHERE id = ?", array($id));
                    }
                }
            }
        }
    }

    if ($oldversion < 2016021200) {
        // Expanding the size of the 'blocktype_externalfeed_data.image' column
        // containing serialized data to avoid errors with MySQL/MariaDB.

        log_debug('Expanding the size of the blocktype_externalfeed_data.image');
        $table = new XMLDBTable('blocktype_externalfeed_data');
        $field = new XMLDBField('image');
        $field->setType(XMLDB_TYPE_TEXT);
        $field->setLength('big');
        change_field_precision($table, $field);
    }

    return true;
}
