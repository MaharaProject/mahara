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
 * @subpackage artefact-internal
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

function xmldb_artefact_internal_upgrade($oldversion=0) {
    
    $status = true;

    if ($oldversion < 2007042500) {
        // migrate everything we had to change to  make mysql happy
        $prefix = get_config('dbprefix');
        execute_sql("ALTER TABLE {$prefix}artefact_internal_profile_email ALTER COLUMN email TYPE varchar(255)");
        execute_sql("ALTER TABLE {$prefix}artefact_internal_profile_icon ALTER COLUMN filename TYPE varchar(255)");

    }

    // everything up to here we pre mysql support.
    return $status;
}

?>
