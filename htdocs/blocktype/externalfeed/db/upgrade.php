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
 * @subpackage blocktype-externalfeed
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

function xmldb_blocktype_externalfeed_upgrade($oldversion=0) {

    if ($oldversion < 2008042100) {
        // Add the 'image' column so that information about a feed's image can 
        // be stored
        $table = new XMLDBTable('blocktype_externalfeed_data');
        $field = new XMLDBField('image');
        $field->setAttributes(XMLDB_TYPE_TEXT);
        add_field($table, $field);
    }
    
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

    return true;
}
