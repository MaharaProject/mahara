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
 * @subpackage artefact-internal
 * @author     Alastair Pharo <alastair@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

function xmldb_artefact_blog_upgrade($oldversion=0) {
    
    $status = true;

    // There was no database prior to this version.
    if ($status && $oldversion < 2006120501) {
        $status = $status && install_from_xmldb_file(
            get_config('docroot') .
            'artefact/blog/db/install.xml'
        );
    }

    if ($status && $oldversion < 2006121501) {
        $table = new XMLDBTable('artefact_blog_blogpost_file_pending');

        $table->addFieldInfo('file', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->addFieldInfo('when', XMLDB_TYPE_DATETIME, null, null, XMLDB_NOTNULL);
        
        $table->addKeyInfo('blogpost_file_pending_pk', XMLDB_KEY_PRIMARY, array('file'));
        $table->addKeyInfo('filefk', XMLDB_KEY_FOREIGN, array('file'), 'artefact', array('id'));

        $status = $status && create_table($table);
    }

    return $status;
}

?>
