<?php
/**
 *
 * @package    mahara
 * @subpackage elasticsearch
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

function xmldb_search_elasticsearch_upgrade($oldversion=0) {
    if ($oldversion < 2015012800) {
        // Adding indices on the table search_elasticsearch_queue
        $table = new XMLDBTable('search_elasticsearch_queue');
        $index = new XMLDBIndex('itemidix');
        $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('itemid'));
        add_index($table, $index);
    }

    if ($oldversion < 2015060900) {
        log_debug('Add "status" and "lastprocessed" columns to search_elasticsearch_queue table');
        $table = new XMLDBTable('search_elasticsearch_queue');
        $field = new XMLDBField('status');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, null, null, 0);
        add_field($table, $field);

        $table = new XMLDBTable('search_elasticsearch_queue');
        $field = new XMLDBField('lastprocessed');
        $field->setAttributes(XMLDB_TYPE_DATETIME);
        add_field($table, $field);

        $table = new XMLDBTable('search_elasticsearch_queue');
        $index = new XMLDBIndex('statusix');
        $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, array('status'));
        add_index($table, $index);
    }

    if ($oldversion < 2015072700) {
        log_debug('Adding ability to search by "Text" blocks in elasticsearch');
        // Need to add the 'block_instance' to the default types to index for elasticsearch
        // Note: the $cfg->plugin_search_elasticsearch_types can be overriding this
        // We don't want to run the re-indexing now as that will take ages for large sites
        // It should be run from the  Extensions -> Elasticsearch -> Configuration page
        if ($types = get_field('search_config', 'value', 'plugin', 'elasticsearch', 'field', 'types')) {
            $types = explode(',', $types);
            if (!in_array('block_instance', $types)) {
                $types[] = 'block_instance';
            }
            $types = implode(',', $types);
            update_record('search_config', array('value' => $types), array('plugin' => 'elasticsearch', 'field' => 'types'));
            log_warn(get_string('newindextype', 'search.elasticsearch', 'block_instance'), true, false);
        }
    }

    if ($oldversion < 2015100800) {
        log_debug('Adding ability to search by collection in elasticsearch');
        // The code for this existed since the beginning but 'collection' was not
        // added to the $cfg->plugin_search_elasticsearch_types
        // We don't want to run the re-indexing now as that will take ages for large sites
        // It should be run from the  Extensions -> Elasticsearch -> Configuration page
        if ($types = get_field('search_config', 'value', 'plugin', 'elasticsearch', 'field', 'types')) {
            $types = explode(',', $types);
            if (!in_array('collection', $types)) {
                $types[] = 'collection';
            }
            $types = implode(',', $types);
            update_record('search_config', array('value' => $types), array('plugin' => 'elasticsearch', 'field' => 'types'));
            log_warn(get_string('newindextype', 'search.elasticsearch', 'collection'), true, false);
        }
    }
    return true;
}
