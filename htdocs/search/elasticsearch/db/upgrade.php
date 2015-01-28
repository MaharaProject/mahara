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

    return true;
}
