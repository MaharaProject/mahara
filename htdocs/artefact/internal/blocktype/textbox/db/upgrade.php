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
 * @subpackage blocktype-textbox
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

function xmldb_blocktype_textbox_upgrade($oldversion=0) {

    if ($oldversion < 2011082200) {
        // Convert all textbox html content to artefacts
        $tbcount = count_records('block_instance', 'blocktype', 'textbox');
        $sql = '
            SELECT b.id, b.title, b.configdata, b.view,
                v.owner, v.group, v.institution, v.ctime, v.mtime, v.atime
            FROM {block_instance} b JOIN {view} v ON b.view = v.id
            WHERE b.id > ? AND b.blocktype = ?
            ORDER BY b.id';
        $done = 0;
        $lastid = 0;

        if (is_mysql()) {
            $mp = mysql_get_variable('max_allowed_packet');
            $limit = ($mp && is_numeric($mp) && $mp > 1048576) ? ($mp / 8192) : 100;
        }
        else {
            $limit = 5000;
        }

        while ($records = get_records_sql_array($sql, array($lastid, 'textbox'), 0, $limit)) {
            // Create the new artefacts
            $values = array();
            foreach ($records as $r) {
                $configdata = unserialize($r->configdata);
                array_push(
                    $values,
                    'html',                                                       // artefacttype
                    $r->ctime, $r->mtime, $r->atime,                              // ctime, mtime, atime
                    $r->title,                                                    // title
                    isset($configdata['text']) ? $configdata['text'] : '',        // description
                    $r->owner, $r->group, $r->institution,                        // owner, group, institution
                    $r->owner > 0 ? $r->owner : null, $r->owner > 0 ? null : '?', // author, authorname
                    $r->id                                                        // note
                );
                // Dumping the block id in the note column makes it easier to update block_instance later
            }

            $insertsql = "
                INSERT INTO {artefact}
                    (artefacttype, ctime, mtime, atime, title, description, owner, \"group\", institution, author, authorname, note)
                VALUES ";
            $insertsql .= join(',', array_fill(0, count($records), '(?,?,?,?,?,?,?,?,?,?,?,?)'));
            execute_sql($insertsql, $values);

            // Update block_instance configdata to point at the new artefacts
            if (is_postgres()) {
                execute_sql("
                    UPDATE {block_instance}
                    SET configdata = 'a:1:{s:10:\"artefactid\";i:' || a.id::text || ';}'
                    FROM (
                        SELECT id, note FROM {artefact} WHERE artefacttype = 'html' AND note IS NOT NULL
                    ) a
                    WHERE blocktype = 'textbox' AND {block_instance}.id::text = a.note"
                );
            }
            else if (is_mysql()) {
                execute_sql("
                    UPDATE {block_instance}, {artefact}
                    SET {block_instance}.configdata = CONCAT('a:1:{s:10:\"artefactid\";i:', CAST({artefact}.id AS CHAR), ';}')
                    WHERE
                        {artefact}.artefacttype = 'html'
                        AND {artefact}.note IS NOT NULL
                        AND {block_instance}.blocktype = 'textbox'
                        AND CAST({block_instance}.id AS CHAR) = {artefact}.note"
                );
            }

            // Update view_artefact table
            $casttype = is_postgres() ? 'TEXT' : 'CHAR';
            execute_sql("
                INSERT INTO {view_artefact} (view, block, artefact)
                SELECT b.view, b.id, a.id
                FROM {block_instance} b, {artefact} a
                WHERE b.blocktype = 'textbox' AND a.artefacttype = 'html' AND a.note IS NOT NULL AND CAST(b.id AS $casttype) = a.note",
                array()
            );

            // Remove the dodgy block id in the note column
            execute_sql("UPDATE {artefact} SET note = NULL WHERE artefacttype = 'html' AND note IS NOT NULL");

            $done += count($records);
            log_debug("Upgrading textbox blocks: $done/$tbcount");
            $last = end($records);
            $lastid = $last->id;
        }
    }

    return true;
}
