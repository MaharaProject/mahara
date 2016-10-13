<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

function xmldb_module_framework_upgrade($oldversion=0) {

    if ($oldversion < 2016081900) {
        log_debug('Adding "active" column to "framework" table');
        $table = new XMLDBTable('framework');
        $field = new XMLDBField('active');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, null, null, 1);
        add_field($table, $field);
    }

    if ($oldversion < 2016082200) {
        log_debug('Adding "framework_evidence_statuses" table');
        $table = new XMLDBTable('framework_evidence_statuses');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addFieldInfo('framework', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('name', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL);
        $table->addFieldInfo('type', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        create_table($table);

        $key = new XMLDBKey('frameworkfk');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('framework'), 'framework', array('id'));
        add_key($table, $key);
    }

    if ($oldversion < 2016101400) {
        log_debug('Adding "framework_assessment_feedback" table');
        $table = new XMLDBTable('framework_assessment_feedback');
        $table->addFieldInfo('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addFieldInfo('framework', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('artefact', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addFieldInfo('oldstatus', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL);
        $table->addFieldInfo('newstatus', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL);
        $table->addFieldInfo('usr', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, array('id'));
        create_table($table);

        $key = new XMLDBKey('artefactfk');
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('framework'), 'framework', array('id'));
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('artefact'), 'artefact', array('id'));
        $key->setAttributes(XMLDB_KEY_FOREIGN, array('usr'), 'usr', array('id'));
        add_key($table, $key);
    }

    return true;
}
