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


define('FORMAT_ARTEFACT_LISTSELF', 'listself');
define('FORMAT_ARTEFACT_LISTCHILDREN', 'listchildren');
define('FORMAT_ARTEFACT_RENDERFULL', 'renderfull');
define('FORMAT_ARTEFACT_RENDERMETADATA', 'rendermetadata');


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
    if (!$dirty = get_records_array('artefact_parent_cache', 'dirty', 1, '', 'DISTINCT(artefact)')) {
        return;
    }
    $blogsinstalled = get_field('artefact_installed', 'active', 'name', 'blog');
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
            // get any blog posts it may be attached to 
            if ($parent->artefacttype == 'file' && $blogsinstalled
                && $associated = get_column('artefact_blog_blogpost_file', 'blogpost', 'file', $parent->id)) {
                foreach ($associated as $a) {
                    if (!in_array($a, $parentids)) {
                        $parentids[] = $a;
                    }
                }
            }
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
    if ($artefacts = get_records_array('artefact')) {
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
                // get any blog posts it may be attached to 
                if ($parent->artefacttype == 'file' && $blogsinstalled
                    && $associated = get_column('artefact_blog_blogpost_file', 'blogpost', 'file', $parent->id)) {
                    foreach ($associated as $a) {
                        if (!in_array($a, $parentids)) {
                            $parentids[] = $a;
                        }
                    }
                }
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

/**
 * This function will return an instance of any "0 or 1" artefact. That is any
 * artefact that each user will have at most one instance of (e.g. profile
 * fields).
 *
 * @param string Is the type of artefact to return
 * @param string The user_id who owns the fetched artefact. (defaults to the
 * current user)
 *
 * @returns ArtefactType Instance of the artefact.
 */
function artefact_instance_from_type($artefact_type, $user_id=null) {
    global $USER;
    $prefix = get_config('dbprefix');

    if ($user_id === null) {
        $user_id = $USER->get('id');
    }

    safe_require('artefact', get_field('artefact_installed_type', 'plugin', 'name', $artefact_type));

    if (!call_static_method(generate_artefact_class_name($artefact_type), 'is_singular')) {
        throw new ArtefactNotFoundException("This artefact type is not a 'singular' artefact type");
    }

    // email is special (as in the user can have more than one of them, but
    // it's treated as a 0 or 1 artefact and the primary is returned
    if ($artefact_type == 'email') {
        $id = get_field('artefact_internal_profile_email', 'artefact', 'owner', $user_id, 'principal', 1);

        if (!$id) {
            throw new ArtefactNotFoundException("Artefact of type '${artefact_type}' doesn't exist");
        }

        $classname = generate_artefact_class_name($artefact_type);
        safe_require('artefact', 'internal');
        return new $classname($id);
    }
    else {
        $sql = 'SELECT a.*, i.plugin 
                FROM ' . $prefix . 'artefact a 
                JOIN ' . $prefix . 'artefact_installed_type i ON a.artefacttype = i.name
                WHERE a.artefacttype = ? AND a.owner = ?';
        if (!$data = get_record_sql($sql, array($artefact_type, $user_id))) {
            throw new ArtefactNotFoundException("Artefact of type '${artefact_type}' doesn't exist");
        }

        $classname = generate_artefact_class_name($artefact_type);
        safe_require('artefact', $data->plugin);
        return new $classname($data->id, $data);
    }

    throw new ArtefactNotFoundException("Artefact of type '${artefact_type}' doesn't exist");
}
        
?>
