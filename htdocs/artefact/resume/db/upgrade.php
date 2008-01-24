<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

function xmldb_artefact_resume_upgrade($oldversion=0) {
    
    $status = true;

    if ($oldversion < 2008012200) {
        foreach (array(
            'artefact_resume_employmenthistory',
            'artefact_resume_educationhistory',
            'artefact_resume_membership') as $table) {
            $records = get_records_array($table, '', '', 'startdate DESC', 'id,startdate,enddate');
            table_column($table, null, 'displayorder');
            table_column($table, 'startdate', 'startdate', 'text', null, null, '', 'not null');
            table_column($table, 'enddate', 'enddate', 'text', null, null, '', '');
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
        }
        foreach (array(
            'artefact_resume_certification',
            'artefact_resume_book') as $table) {
            $records = get_records_array($table, '', '', 'date DESC', 'id,date');
            table_column($table, null, 'displayorder');
            table_column($table, 'date', 'date', 'text', null, null, '', 'not null');
            if (!empty($records)) {
                foreach ($records as $k => $r) {
                    set_field($table, 'displayorder', $k, 'id', $r->id);
                    set_field($table, 'date', 
                              format_date(strtotime($r->date), 'strftimedate', 'current', 'artefact.resume'),
                              'id', $r->id);
                }
            }
        }
    }

    return $status;
}

?>
