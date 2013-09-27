<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-textbox
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
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
                // Update view_artefact table
                execute_sql("
                    INSERT INTO {view_artefact} (view, block, artefact)
                    SELECT b.view, b.id, a.id
                    FROM {block_instance} b, {artefact} a
                    WHERE b.blocktype = 'textbox' AND a.artefacttype = 'html' AND a.note IS NOT NULL AND CAST(b.id AS TEXT) = a.note",
                    array()
                );
            }
            else if (is_mysql()) {
                execute_sql("
                    UPDATE {block_instance}, {artefact}
                    SET {block_instance}.configdata = CONCAT('a:1:{s:10:\"artefactid\";i:', {artefact}.id, ';}')
                    WHERE
                        {artefact}.artefacttype = 'html'
                        AND {artefact}.note IS NOT NULL
                        AND {block_instance}.blocktype = 'textbox'
                        AND {block_instance}.id = {artefact}.note"
                );
                // Update view_artefact table
                execute_sql("
                    INSERT INTO {view_artefact} (view, block, artefact)
                    SELECT b.view, b.id, a.id
                    FROM {block_instance} b, {artefact} a
                    WHERE b.blocktype = 'textbox' AND a.artefacttype = 'html' AND a.note IS NOT NULL AND b.id = a.note",
                    array()
                );
            }


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
