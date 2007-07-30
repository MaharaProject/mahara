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

    private $dirty;
    private $deleted;
    private $id;
    private $owner;
    private $ownerformat;
    private $ctime;
    private $mtime;
    private $atime;
    private $submittedto;
    private $title;
    private $description;
    private $loggedin;
    private $friendsonly;
    private $template;
    private $artefact_instances;
    private $artefact_metadata;
    private $artefact_hierarchy;
    private $contents;
    private $ownerobj;

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

    public function get($field) {
        if (!property_exists($this, $field)) {
            throw new InvalidArgumentException("Field $field wasn't found in class " . get_class($this));
        }
        return $this->{$field};
    }

    public function set($field, $value) {
        if (property_exists($this, $field)) {
            if ($this->{$field} != $value) {
                // only set it to dirty if it's changed
                $this->dirty = true;
            }
            $this->{$field} = $value;
            $this->mtime = time();
            return true;
        }
        throw new InvalidArgumentException("Field $field wasn't found in class " . get_class($this));
    }

    /**
     * View destructor. Calls commit if necessary.
     *
     * A special case is when the object has just been deleted.  In this case,
     * we do nothing.
     */
    public function __destruct() {
        if ($this->deleted) {
            return;
        }
      
        if (!empty($this->dirty)) {
            return $this->commit();
        }
    }

    /** 
     * This method updates the contents of the view table only.
     */
    public function commit() {
        if (empty($this->dirty)) {
            return;
        }
        $fordb = new StdClass;
        foreach (get_object_vars($this) as $k => $v) {
            $fordb->{$k} = $v;
            if (in_array($k, array('mtime', 'ctime', 'atime')) && !empty($v)) {
                $fordb->{$k} = db_format_timestamp($v);
            }
        }
        if (empty($this->id)) {
            $this->id = insert_record('view', $fordb, 'id', true);
        }
        else {
            update_record('view', $fordb, 'id');
        }
        $this->dirty = false;
        $this->deleted = false;
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
            $sql = 'SELECT a.*, i.name, va.block, va.format
                    FROM ' . $prefix . 'view_artefact va
                    JOIN ' . $prefix . 'artefact a ON va.artefact = a.id
                    JOIN ' . $prefix . 'artefact_installed_type i ON a.artefacttype = i.name
                    WHERE va.view = ?';
            $this->artefact_metadata = get_records_sql_array($sql, array($this->id));
        }
        return $this->artefact_metadata;
    }

    public function get_artefact_hierarchy() {
        if (isset($this->artefact_hierarchy)) {
            return $this->artefact_hierarchy;
        }

        if (!$artefacts = $this->get_artefact_metadata()) {
            return array();
        }

        $this->artefact_hierarchy = array('data' => array(),
                                          'refs' => array());

        $prefix = get_config('dbprefix');

        $sql = 'SELECT a.*,a.parent,pc.parent,a.artefacttype 
                    FROM ' . $prefix . 'artefact a 
                    JOIN (
                        SELECT apc1.* 
                        FROM ' . $prefix . 'artefact_parent_cache apc1 
                        JOIN ' . $prefix . 'artefact_parent_cache apc2 ON apc1.artefact = apc2.artefact 
                        WHERE apc2.parent IN (
                            SELECT artefact FROM ' . $prefix . 'view_artefact where view = ?
                        )
                    ) pc ON pc.artefact = a.id 
                UNION SELECT a2.*,a2.parent,null,a2.artefacttype 
                    FROM ' . $prefix . 'artefact a2 
                    JOIN ' . $prefix . 'view_artefact va ON va.artefact = a2.id 
                    WHERE va.id = ?';

        $allchildren = get_records_sql_array($sql, array($this->id, $this->id));        

        foreach ($artefacts as $toplevel) {
            $a = array();
            $a['artefact'] = $toplevel;
            $a['children'] = $this->find_artefact_children($toplevel, 
                                  $allchildren, $this->artefact_hierarchy['refs']);
            $this->artefact_hierarchy['data'][$toplevel->id] = $a;
            $this->artefact_hierarchy['refs'][$toplevel->id] = $toplevel;
        }
        return $this->artefact_hierarchy;
    }

    public function find_artefact_children($artefact, $allchildren, &$refs) {

        $children = array();        
        if ($allchildren) {
            foreach ($allchildren as $child) {
                if ($child->parent != $artefact->id) {
                    continue;
                }
                $children[$child->id] = array();
                $children[$child->id]['artefact'] = $child;
                $refs[$child->id] = $child;
                $children[$child->id]['children'] = $this->find_artefact_children($child, 
                                                            $allchildren, $refs);
            }
        }

        return $children;
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

    public function get_contents() { // lazy setup.
        if (!isset($this->contents)) {
            $this->contents = get_records_array('view_content', 'view', $this->id);
        }
        return $this->contents;
    }
    
    public function has_artefacts() {
        if ($this->get_artefact_metadata()) {
            return true;
        }
        return false;
    }

    public function get_owner_object() {
        if (!isset($this->ownerobj)) {
            $this->ownerobj = get_record('usr', 'id', $this->get('owner'));
        }
        return $this->ownerobj;
    }

    public function render() {
        require_once('template.php');

        $artefacts = $this->get_artefact_metadata();
        $contents  = $this->get_contents();
        if (empty($contents)) {
            $contents = array();
        }
        if (empty($artefacts)) {
            $artefacts = array();
        }

        $data = array();
        foreach ($artefacts as $artefact) {
            $data[$artefact->block]['format'] = $artefact->format;
            $data[$artefact->block]['id'][] = $artefact->id;
        }
        foreach ($contents as $content) {
            $data[$content->block] = array(
                    'value'  => $content->content,
             );
        }
        $data['title']       = $this->get('title');
        $data['description'] = $this->get('description');
        $data['author']      = template_format_owner($this->get('ownerformat'), $this->get_owner_object());

        $template = template_locate($this->get('template'));
        
        return template_render($template, TEMPLATE_RENDER_READONLY, $data, $this->get('id'));
    }
    
    public function delete() {
        delete_records('artefact_feedback','view',$this->id);
        delete_records('view_feedback','view',$this->id);
        delete_records('view_artefact','view',$this->id);
        delete_records('view_content','view',$this->id);
        delete_records('view_access','view',$this->id);
        delete_records('view_access_community','view',$this->id);
        delete_records('view_access_usr','view',$this->id);
        delete_records('view_tag','view',$this->id);
        delete_records('usr_watchlist_artefact','view',$this->id);
        delete_records('usr_watchlist_view','view',$this->id);
        delete_records('view','id',$this->id);
        $this->deleted = true;
    }

    public function release($communityid, $releaseuser=null) {
        if ($this->get('submittedto') != $communityid) {
            throw new ParameterException("View with id " . $this->get('id') .
                                         " has not been submitted to community $communityid");
        }
        $releaseuser = optional_userobj($releaseuser);
        $this->set('submittedto', null);
        $this->commit();
        require_once('activity.php');
        activity_occurred('maharamessage', 
                  array('users'   => array($this->get('owner')),
                  'subject' => get_string('viewreleasedsubject'),
                  'message' => get_string('viewreleasedmessage', 'mahara', 
                       get_field('community', 'name', 'id', $communityid), 
                       display_name($releaseuser, $this->get_owner_object()))));
    }

}

?>
