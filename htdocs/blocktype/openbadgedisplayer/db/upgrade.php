<?php
defined('INTERNAL') || die();

function xmldb_blocktype_openbadgedisplayer_upgrade($oldversion = 0) {

    if ($oldversion < 2015062301) {
        $blocks = get_records_array('block_instance', 'blocktype', 'openbadgedisplayer');

        if (is_array($blocks)) {
            foreach ($blocks as $block) {
                $configdata = unserialize($block->configdata);

                if (isset($configdata['badgegroup'])) {
                    // Append source to legacy values
                    if (is_string($configdata['badgegroup'])) {
                        $configdata['badgegroup'] = 'backpack:' . $configdata['badgegroup'];
                    }

                    else if (is_array($configdata['badgegroup'])) {
                        foreach ($configdata['badgegroup'] as &$group) {
                            $group = str_replace('https://openbadgepassport.com/', 'passport', $group);
                            $group = str_replace('https://backpack.openbadges.org/', 'backpack', $group);
                        }
                    }

                    $block->configdata = serialize($configdata);

                    update_record('block_instance', $block, 'id');
                }
            }
        }
    }

    if ($oldversion < 2016030200) {
        // Add a new table blocktype_openbadgedisplayer_data for storing prefetch badges
        $table = new XMLDBTable('blocktype_openbadgedisplayer_data');

        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, '10', XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addFieldInfo('host', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);
        $table->addFieldInfo('uid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->addFieldInfo('badgegroupid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->addFieldInfo('name', XMLDB_TYPE_TEXT);
        $table->addFieldInfo('html', XMLDB_TYPE_TEXT);
        $table->addFieldInfo('lastupdate', XMLDB_TYPE_DATETIME);

        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->addIndexInfo('hostuidix', XMLDB_INDEX_NOTUNIQUE, array('host', 'uid'));
        $table->addIndexInfo('hostuidbadgegroupidix', XMLDB_INDEX_UNIQUE, array('host', 'uid', 'badgegroupid'));

        create_table($table);
    }

    if ($oldversion < 2018081700) {
        // Alter table blocktype_openbadgedisplayer_data make uid and badgegroupid char string
        $table = new XMLDBTable('blocktype_openbadgedisplayer_data');
        $field = new XMLDBField('uid');
        $field->setAttributes(XMLDB_TYPE_CHAR, 100, null, XMLDB_NOTNULL);
        change_field_type($table, $field, true, true);
        $field = new XMLDBField('badgegroupid');
        $field->setAttributes(XMLDB_TYPE_CHAR, 100, null, XMLDB_NOTNULL);
        change_field_type($table, $field, true, true);
    }

    return true;
}