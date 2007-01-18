<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage artefact-file
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

function xmldb_artefact_file_upgrade($oldversion=0) {
    
    $status = true;

    if ($oldversion < 2007010900) {
        $table = new XMLDBTable('artefact_file_files');
        $field = new XMLDBField('adminfiles');
        $field->setAttributes(XMLDB_TYPE_INTEGER, 1, false, true, false, null, null, 0);
        add_field($table, $field);
        set_field('artefact_file_files', 'adminfiles', 0);

        // Put all folders into artefact_file_files
        $prefix = get_config('dbprefix');
        $folders = get_column_sql("
            SELECT a.id
            FROM " . $prefix . "artefact a
            LEFT OUTER JOIN " . $prefix . "artefact_file_files f ON a.id = f.artefact
            WHERE a.artefacttype = 'folder' AND f.artefact IS NULL");
        if ($folders) {
            foreach ($folders as $folderid) {
                $data = (object) array('artefact' => $folderid, 'adminfiles' => 0);
                insert_record('artefact_file_files', $data);
            }
        }
    }

    if ($oldversion < 2007011800) {
        // Make sure the default quota is set
        set_config_plugin('artefact', 'file', 'defaultquota', 10485760);
    }

    return $status;
}

?>
