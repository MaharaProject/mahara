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
 * @subpackage core
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();


/**
 * Given an artefact plugin name, this function will test if 
 * it's installable or not.  If not, InstallationException will be thrown.
 */
function artefact_check_plugin_sanity($pluginname) {
    $classname = generate_class_name('artefact', $pluginname);
    safe_require('artefact', $pluginname);
    $types = call_static_method($classname, 'get_artefact_types');
    foreach ($types as $type) {
        $typeclassname = generate_artefact_class_name($type);
        if (get_config('installed')) {
            if ($taken = get_record_select('artefact_installed_type', 'name = ? AND plugin != ?', 
                                           array($type, $pluginname))) {
                throw new InstallationException("type $type is already taken by another plugin (" . $taken->plugin . ")");
            }
        }
        if (!class_exists($typeclassname)) {
            throw new InstallationException("class $typeclassname for type $type in plugin $pluginname was missing");
        }
    }
}

function rebuild_artefact_parent_cache_dirty() {
    // this will give us a list of artefacts, as the first returned column
    // is not unqiue, but that's ok, it's what we want.
    if (!$dirty = get_records_array('artefact_parent_cache', 'dirty', 1)) {
        return;
    }
    db_begin();
    delete_records('artefact_parent_cache', 'dirty', 1);
    foreach ($dirty as $d) {
        $parentids = array();
        $current = $d->artefact;
        while (true) {
            if (!$parent = get_record('artefact', 'id', $current)) {
                break;
            }
            if (!$parent->parent) {
                break;
            }
            $parentids[] = $parent->parent;
            $current = $parent->parent;
        }
        foreach ($parentids as $p) {
            $apc = new StdClass;
            $apc->artefact = $d->artefact;
            $apc->parent   = $p;
            $apc->dirty    = 0;
            insert_record('artefact_parent_cache', $apc);
        }
    }
    db_commit();
}

function rebuild_artefact_parent_cache_complete() {
    db_begin();
    delete_records('artefact_parent_cache');
    $artefacts = get_records_array('artefact');
    foreach ($artefacts as $a) {
        $parentids = array();
        $current = $a->id;
        while (true) {
            if (!$parent = get_record('artefact', 'id', $current)) {
                break;
            }
            if (!$parent->parent) {
                break;
            }
            $parentids[] = $parent->parent;
            $current = $parent->parent;
        }
        foreach ($parentids as $p) {
            $apc = new StdClass;
            $apc->artefact = $a->id;
            $apc->parent   = $p;
            $apc->dirty    = 0;
            insert_record('artefact_parent_cache', $apc);
        }
    }
    db_commit();
}

function artefact_can_render_to($type, $format) {
    return in_array($format, call_static_method(generate_artefact_class_name($type), 'get_render_list'));
}

function artefact_instance_from_id($id) {
    $prefix = get_config('dbprefix');
    $sql = 'SELECT a.*, i.plugin 
            FROM ' . $prefix . 'artefact a 
            JOIN ' . $prefix . 'artefact_installed_type i ON a.artefacttype = i.name
            WHERE a.id = ?';
    if (!$data = get_record_sql($sql, array($id))) {
        throw new ArtefactNotFoundException(get_string('artefactnotfound', 'mahara', $id));
    }
    $classname = generate_artefact_class_name($data->artefacttype);
    safe_require('artefact', $data->plugin);
    return new $classname($id, $data);
}
        


?>
