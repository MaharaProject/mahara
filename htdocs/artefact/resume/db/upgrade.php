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
 * @subpackage artefact-resume
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

function xmldb_artefact_resume_upgrade($oldversion=0) {
    
    $status = true;

    if ($oldversion < 2008012200) {
        if (is_mysql()) {
            $inttype = 'BIGINT(10)';
        }
        else {
            $inttype = 'INTEGER';
        }
        foreach (array(
            'artefact_resume_employmenthistory',
            'artefact_resume_educationhistory',
            'artefact_resume_membership') as $table) {
            $records = get_records_array($table, '', '', 'startdate DESC', 'id,startdate,enddate');
            // Sigh. table_column is screwed beyond belief. We let it do its 
            // work (in the case of start and stopdate at least because it does 
            // cast the columns OK), then fix its bugs
            execute_sql('ALTER TABLE {' . $table . '} ADD displayorder ' . $inttype);
            table_column($table, 'startdate', 'startdate', 'text', null, null, '', 'not null');
            table_column($table, 'enddate', 'enddate', 'text', null, null, '', '');

            // MySQL docs say:
            //  * BLOB and TEXT columns cannot have DEFAULT values.
            // It turns out they do - a default of ''. And dropping this results in:
            // mysql> ALTER TABLE "artefact_resume_employmenthistory" ALTER COLUMN startdate DROP DEFAULT;
            // ERROR 1101 (42000): BLOB/TEXT column 'startdate' can't have a default value
            //
            if (is_postgres()) {
                execute_sql('ALTER TABLE {' . $table . '} ALTER COLUMN startdate DROP DEFAULT');
                execute_sql('ALTER TABLE {' . $table . '} ALTER COLUMN enddate DROP DEFAULT');
            }

            if (!empty($records)) {
                foreach ($records as $k => $r) {
                    set_field($table, 'displayorder', $k, 'id', $r->id);
                    set_field($table, 'startdate', 
                              format_date(strtotime($r->startdate), 'strftimedate', 'current', 'artefact.resume'),
                              'id', $r->id);
                    set_field($table, 'enddate', 
                              format_date(strtotime($r->enddate), 'strftimedate', 'current', 'artefact.resume'),
                              'id', $r->id);
                }
            }
            if (is_mysql()) {
                execute_sql('ALTER TABLE {' . $table .'} MODIFY displayorder ' . $inttype . ' NOT NULL');
                execute_sql('ALTER TABLE {' . $table .'} MODIFY startdate TEXT NOT NULL');
            }
            else {
                execute_sql('ALTER TABLE {' . $table . '} ALTER displayorder SET NOT NULL');
                execute_sql('ALTER TABLE {' . $table . '} ALTER COLUMN startdate SET NOT NULL');
            }
        }
        foreach (array(
            'artefact_resume_certification',
            'artefact_resume_book') as $table) {
            $records = get_records_array($table, '', '', 'date DESC', 'id,date');
            execute_sql('ALTER TABLE {' . $table . '} ADD displayorder ' . $inttype);
            table_column($table, 'date', 'date', 'text', null, null, '', 'not null');
            if (is_postgres()) {
                execute_sql('ALTER TABLE {' . $table . '} ALTER COLUMN date DROP DEFAULT');
            }
            if (!empty($records)) {
                foreach ($records as $k => $r) {
                    set_field($table, 'displayorder', $k, 'id', $r->id);
                    set_field($table, 'date', 
                              format_date(strtotime($r->date), 'strftimedate', 'current', 'artefact.resume'),
                              'id', $r->id);
                }
            }
            if (is_mysql()) {
                execute_sql('ALTER TABLE {' . $table . '} MODIFY displayorder ' . $inttype . ' NOT NULL');
            }
            else {
                execute_sql('ALTER TABLE {' . $table . '} ALTER displayorder SET NOT NULL');
                execute_sql('ALTER TABLE {' . $table . '} ALTER COLUMN date SET NOT NULL');
            }
        }
    }

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

    return $status;
}
