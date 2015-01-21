<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-taggedposts
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

function xmldb_blocktype_taggedposts_upgrade($oldversion=0) {

    if ($oldversion < 2015011500) {
        $table = new XMLDBTable('blocktype_taggedposts_tags');

        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addFieldInfo('block_instance', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('tag', XMLDB_TYPE_CHAR, 128, null, XMLDB_NOTNULL);
        $table->addFieldInfo('tagtype', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);

        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));

        $table->addIndexInfo('tagtagtypeix', XMLDB_INDEX_NOTUNIQUE, array('tag', 'tagtype'));

        if (!table_exists($table)) {
            create_table($table);

            $rs = get_recordset('block_instance', 'blocktype', 'taggedposts', 'id', 'id, configdata');
            while ($bi = $rs->FetchRow()) {
                // Each block will have only one tag (because we combined this upgrade block
                // with the upgrade block for the "multiple tags" enhancement.
                $configdata = unserialize($bi['configdata']);
                if (!empty($configdata['tagselect'])) {
                    $todb = new stdClass();
                    $todb->block_instance = $bi['id'];
                    $todb->tag = $configdata['tagselect'];
                    $todb->tagtype = PluginBlocktypeTaggedposts::TAGTYPE_INCLUDE;
                    insert_record('blocktype_taggedposts_tags', $todb);
                }
            }
        }
    }

    return true;
}
