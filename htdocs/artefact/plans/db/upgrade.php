<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-plans
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @author     Alexander Del Ponte <delponte@uni-bremen.de>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

function xmldb_artefact_plans_upgrade($oldversion=0) {

    if ($oldversion < 2010072302) {
        set_field('artefact', 'container', 1, 'artefacttype', 'plan');
    }

    if ($oldversion < 2019071700) {
        $table = new XMLDBTable('artefact_plans_plan');

        $table->addFieldInfo('artefact', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->addFieldInfo('template', XMLDB_TYPE_INTEGER, 2, null, XMLDB_NOTNULL, null, null, null, 0);
        $table->addFieldInfo('rootgroupplan', XMLDB_TYPE_INTEGER, 10);
        $table->addFieldInfo('selectionplan', XMLDB_TYPE_INTEGER, 2, null, XMLDB_NOTNULL, null, null, null, 0);

        $table->addKeyInfo('primary', XMLDB_KEY_PRIMARY, ['artefact']);
        $table->addKeyInfo('artefactfk', XMLDB_KEY_FOREIGN, ['artefact'], 'artefact', ['id']);

        //ToDo: Create unique Index using rootgroupplan and artefact
        $table->addIndexInfo('rootgroupplanix', XMLDB_INDEX_NOTUNIQUE, ['rootgroupplan']);

        create_table($table);
        execute_sql('INSERT INTO {artefact_plans_plan} (artefact)
                      SELECT id FROM {artefact} WHERE artefacttype = ?', ['plan']);


        $table = new XMLDBTable('artefact_plans_task');

        $table->addFieldInfo('startdate', XMLDB_TYPE_DATETIME, null, null, null, null, null, null, null, 'artefact');
        $table->addFieldInfo('reminder', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED);
        $table->addFieldInfo('remindermailsent', XMLDB_TYPE_INTEGER, 2, null, XMLDB_NOTNULL, null, null, null, 0);
        $table->addFieldInfo('taskview', XMLDB_TYPE_INTEGER, 10);
        $table->addFieldInfo('outcome', XMLDB_TYPE_INTEGER, 10);
        $table->addFieldInfo('outcometype', XMLDB_TYPE_CHAR, 20);
        $table->addFieldInfo('template', XMLDB_TYPE_INTEGER, 2, null, XMLDB_NOTNULL, null, null, null, 0);
        $table->addFieldInfo('mandatory', XMLDB_TYPE_INTEGER, 2, null, XMLDB_NOTNULL, null, null, null, 0);
        $table->addFieldInfo('rootgrouptask', XMLDB_TYPE_INTEGER, 10);

        //Greyed out to ensure, that a usertask will not be deleted, when the root group task is.
        //$table->addKeyInfo('rootgrouptaskfk', XMLDB_KEY_FOREIGN, ['rootgrouptask'], 'artefact', ['id']);

        $table->addIndexInfo('rootgrouptaskix', XMLDB_INDEX_NOTUNIQUE, ['rootgrouptask']);
        $table->addIndexInfo('taskviewix', XMLDB_INDEX_NOTUNIQUE, ['taskview']);
        $table->addIndexInfo('outcomeix', XMLDB_INDEX_NOTUNIQUE, ['outcome']);
        $table->addIndexInfo('outcometypeix', XMLDB_INDEX_NOTUNIQUE, ['outcometype']);
        //Combined index to speed up cron_reminder_check
        $table->addIndexInfo('completedremindermailsentix', XMLDB_INDEX_NOTUNIQUE, ['completed,remindermailsent']);

        foreach ($table->getFields() as $field) {
            add_field($table, $field);
        }
        foreach ($table->getKeys() as $key) {
            add_key($table, $key);
        }
        foreach ($table->getIndexes() as $index) {
            add_index($table, $index);
        }
    }

    if ($oldversion < 2019091300) {
        $table = new XMLDBTable('artefact_plans_plan');

        $field = new XMLDBField('roottemplate');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10, null, null, null, null, null, null, 'template');
        add_field($table, $field);

        $index = new XMLDBIndex('roottemplateix');
        $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, ['roottemplate']);
        add_index($table, $index);
    }

    if ($oldversion < 2019091600) {
        $table = new XMLDBTable('artefact_plans_task');

        $field = new XMLDBField('roottemplatetask');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 10, null, null, null, null, null, null, 'template');
        add_field($table, $field);

        // We have to drop and recreate this index to make the system determine consistent index names
        $index = new XMLDBIndex('rootgrouptaskix');
        $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, ['rootgrouptask']);
        drop_index($table, $index);
        add_index($table, $index);

        $index = new XMLDBIndex('roottemplatetaskix');
        $index->setAttributes(XMLDB_INDEX_NOTUNIQUE, ['roottemplatetask']);
        add_index($table, $index);
    }

    return true;
}
