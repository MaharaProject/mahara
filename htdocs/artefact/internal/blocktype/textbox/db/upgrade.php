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
        $sql = '
            SELECT b.id, b.title, b.configdata, b.view,
                v.owner, v.group, v.institution, v.ctime, v.mtime, v.atime
            FROM {block_instance} b JOIN {view} v ON b.view = v.id
            WHERE b.id > ? AND b.blocktype = ?
            ORDER BY b.id';
        $lastid = 0;
        $limit = 500;
        while ($records = get_records_sql_array($sql, array($lastid, 'textbox'), 0, $limit)) {
            foreach ($records as $r) {
                $configdata = unserialize($r->configdata);
                $artefact = (object) array(
                    'artefacttype' => 'html',
                    'ctime'        => $r->ctime,
                    'mtime'        => $r->mtime,
                    'atime'        => $r->atime,
                    'title'        => $r->title,
                    'description'  => isset($configdata['text']) ? $configdata['text'] : '',
                    'owner'        => $r->owner,
                    'group'        => $r->group,
                    'institution'  => $r->institution,
                );
                if ($r->owner > 0) {
                    $artefact->author = $r->owner;
                }
                else {
                    $artefact->authorname = '?';
                }
                $artefactid = insert_record('artefact', $artefact, 'id', true);
                unset($configdata['text']);
                $configdata['artefactid'] = $artefactid;
                set_field('block_instance', 'configdata', serialize($configdata), 'id', $r->id);
                insert_record('view_artefact', (object) array('view' => $r->view, 'block' => $r->id, 'artefact' => $artefactid));
            }
            $lastid = $r->id;
        }
    }

    return true;
}
