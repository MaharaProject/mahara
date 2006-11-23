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


class View {

    protected $dirty;
    protected $id;
    protected $owner;
    protected $ctime;
    protected $mtime;
    protected $atime;
    protected $submitted;
    protected $title;
    protected $description;
    protected $loggedin;
    protected $friendsonly;
    protected $template;
    protected $artefact_instances;
    protected $artefact_metadata;

    public function __construct($id=0, $data=null) {
        if (!empty($id)) {
            if (empty($data)) {
                if (!$data = get_record('view','id',$id)) {
                    throw new ViewNotFoundException("View with id $id not found");
                }
            }    
            $this->id = $id;
        }
        else {
            $this->ctime = time();
        }

        if (empty($data)) {
            $data = array();
        }
        foreach ((array)$data as $field => $value) {
            if (property_exists($this, $field)) {
                $this->{$field} = $value;
            }
        }
        $this->atime = time();
    }

    public function get_artefact_instances() {
        if (!isset($this->artefact_instances)) {
            $this->artefact_instances = false;
            if ($instances = $this->get_artefact_metadata()) {
                foreach ($instances as $instance) {
                    safe_require('artefact', $instance->plugin);
                    $classname = generate_artefact_class_name($instance->artefacttype);
                    $i = new $classname($instance->id, $instance);
                    $this->childreninstances[] = $i;
                }
            }
        }
        return $this->artefact_instances;
    }

    public function get_artefact_metadata() {
        if (!isset($this->artefact_metadata)) {
            $prefix = get_config('dbprefix');
            $sql = 'SELECT a.*, i.name
                    FROM ' . $prefix . 'view_artefact va
                    JOIN ' . $prefix . 'artefact a ON va.artefact = a.id
                    JOIN ' . $prefix . 'artefact_installed_type i ON a.artefacttype = i.name
                    WHERE va.view = ?';
            $this->artefact_metadata = get_records_sql_array($sql, array($this->id));
        }
        return $this->artefact_metadata;
    }


    public function get_artefact_instances_watchlist($userid) {
        $instances = array();
        if ($artefacts = $this->get_artefact_metadata_watchlist($userid)) {
            foreach ($artefact as $instance) {
                safe_require('artefact', $instance->plugin);
                $classname = generate_artefact_class_name($instance->artefacttype);
                $i = new $classname($instance->id, $instance);
                $instances[] = $i;
            }
        }
        return $instances;
    }

    public function get_artefact_metadata_watchlist($userid) {
        $prefix = get_config('dbprefix');

        $sql = 'SELECT a.*, i.name
                    FROM ' . $prefix . 'view_artefact va
                    JOIN ' . $prefix . 'artefact a ON va.artefact = a.id
                    JOIN ' . $prefix . 'artefact_installed_type i ON a.artefacttype = i.name
                    JOIN ' . $prefix . 'usr_watchlist_artefact wa ON wa.artefact = a.id                    
                    WHERE va.view = ? AND wa.usr = ? AND a.parent IS NULL';
        return get_records_sql_array($sql,  array($this->id, $userid));
    }


    
    public function has_artefacts() {
        if ($this->get_artefact_metadata()) {
            return true;
        }
        return false;
    }
        
}

?>
