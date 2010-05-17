<?php

/** 
 * Upgrades for local customisations.
 */

defined('INTERNAL') || die();

function xmldb_local_upgrade($oldversion=0) {
     if ($oldversion < 1) {
         $table = new XMLDBTable('group_category');
         $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
         $table->addFieldInfo('title', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
         $table->addFieldInfo('displayorder', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
         $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
         create_table($table);

         $table = new XMLDBTable('group');
         $field = new XMLDBField('groupcategory');
         $field->setAttributes(XMLDB_TYPE_INTEGER, 10);
         add_field($table, $field);
     }
}

?>