<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-resume
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

function xmldb_artefact_resume_upgrade($oldversion=0) {

    $status = true;

    if ($oldversion < 2009122100) {
        $table = new XMLDBTable('artefact_resume_employmenthistory');
        $field = new XMLDBField('employeraddress');
        $field->setAttributes(XMLDB_TYPE_TEXT);
        add_field($table, $field);

        $table = new XMLDBTable('artefact_resume_educationhistory');
        $field = new XMLDBField('institutionaddress');
        $field->setAttributes(XMLDB_TYPE_TEXT);
        add_field($table, $field);
    }

    if ($oldversion < 2010020300) {
        $table = new XMLDBTable('artefact_resume_educationhistory');
        $field = new XMLDBField('qualtype');
        $field->setAttributes(XMLDB_TYPE_TEXT);
        change_field_notnull($table, $field);

        $table = new XMLDBTable('artefact_resume_educationhistory');
        $field = new XMLDBField('qualname');
        $field->setAttributes(XMLDB_TYPE_TEXT);
        change_field_notnull($table, $field);
    }

    if ($oldversion < 2013071300) {
        $table = new XMLDBTable('artefact_resume_book');
        $field = new XMLDBField('url');
        $field->setAttributes(XMLDB_TYPE_TEXT);
        add_field($table, $field);
    }

    if ($oldversion < 2013072900) {
        execute_sql("UPDATE {blocktype_installed_category} SET category = 'internal' WHERE category = 'resume'");
    }

    return $status;
}
