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
 * @subpackage blocktype
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();


/**
 * Base blocktype plugin class
 * @abstract
 */
abstract class PluginBlocktype extends Plugin {

    public static function extra_xmldb_substitution($xml) {
        return str_replace(
        '<!-- PLUGINTYPE_INSTALLED_EXTRAFIELDS -->', 
        ' <FIELD NAME="artefactplugin" TYPE="char" LENGTH="255" NOTNULL="false" />',
        str_replace(
            '<!-- PLUGINTYPE_INSTALLED_EXTRAKEYS -->', 
            '<KEY NAME="artefactpluginfk" TYPE="foreign" FIELDS="artefactplugin" REFTABLE="artefact_installed" REFFIELDS="name" />',
            $xml
            )
        );
    }

    public static abstract function get_title();

    public static abstract function get_description();

    public static abstract function get_categories();

    public static abstract function render_instance(BlockInstance $instance);

    /**
    * subclasses can override this if they need to do something a bit special
    * eg more than just what the BlockInstance->delete function does.
    * 
    * @param BlockInstance $instance
    */
    public static function delete_instance(BlockInstance $instance) { }

    /**
    * This function must be implemented in the subclass if it has config
    */
    public static function config_form($id=0) {
        if ($this->has_config()) {
            throw new Exception(get_string('blocktypemissingconfigform', 'error', get_class($this)));
        }
        return false;
    }

    /**
    * This function must be implemented in the subclass if it has config
    * $values must contain a hidden 'id' field.
    */
    public function config_save(Pieform $form, $values) {
        if ($this->has_config()) {
            throw new Exception(get_string('blocktypemissingconfigsave', 'error', get_class($this)));
        }
        return false;
    }

    public static function has_config() {
        return false;
    }

    public static function category_title_from_name($name) {
        $title = get_string('blocktypecategory.'. $name);
        if (strpos($title, '[[') !== 0) {
            return $title;
        }
        // else we're an artefact
        return get_string('pluginname', 'artefact.' . $name);
    }

    public static function get_blocktypes_for_category($category) {

        $sql = 'SELECT bti.name,bti.artefactplugin 
            FROM {blocktype_installed} bti 
            JOIN {blocktype_installed_category} btic ON btic.blocktype = bti.name
            WHERE btic.category = ?';
        if (!$bts = get_records_sql_array($sql, array($category))) {
            return false;
        }

        $blocktypes = array();

        foreach ($bts as $bt) {
            $namespaced = blocktype_single_to_namespaced($bt->name, $bt->artefactplugin);
            safe_require('blocktype', $namespaced); 
            $temp = array(
                'name'           => $bt->name,
                'title'          => call_static_method(generate_class_name('blocktype', $namespaced), 'get_title'),
                'description'    => call_static_method(generate_class_name('blocktype', $namespaced), 'get_description'),
                'artefactplugin' => $bt->artefactplugin,
                'thumbnail_path' => get_config('wwwroot') . 'thumb.php?type=blocktype&bt=' . $bt->name . ((!empty($bt->artefactplugin)) ? '&ap=' . $bt->artefactplugin : ''),
            );
            $blocktypes[] = $temp;
        }
        return $blocktypes;
    }
}

class BlockInstance {

    private $id;
    private $blocktype;
    private $title;
    private $configdata;
    private $dirty;
    private $view;
    private $view_obj;
    private $column;
    private $order; 
    private $canmoveleft;
    private $canmoveright;
    private $canmoveup;
    private $canmovedown;
    private $maxorderincolumn; 

    public function __construct($id=0, $data=null) {
         if (!empty($id)) {
            if (empty($data)) {
                if (!$data = get_record('block_instance','id',$id)) {
                    // TODO: 1) doesn't need get string here if this is the 
                    // only place the exception is used - can be done in the 
                    // class itself. 2) String needs to be defined, or taken 
                    // from lang/*/view.php where there is already one for it
                    throw new BlockInstanceNotFoundException(get_string('blockinstancenotfound', 'error', $id));
                }
            }
            $this->id = $id;
        }
        else {
            $this->dirty = true;
        }
        if (empty($data)) {
            $data = array();
        }
        foreach ((array)$data as $field => $value) {
            if (property_exists($this, $field)) {
                $this->{$field} = $value;
            }
        }
    }

    public function get($field) {
        if (!property_exists($this, $field)) {
            throw new InvalidArgumentException("Field $field wasn't found in class " . get_class($this));
        }
        if ($field == 'configdata') {
            // make sure we unserialise it
            if (!is_array($this->configdata)) {
                $this->configdata = unserialize($this->configdata);
            }
        }
        if (strpos($field, 'canmove') === 0) {
            return $this->can_move(substr($field, strlen('canmove'))); // needs to be calculated.
        }
        if ($field == 'maxorderincolumn') {
            // only fetch this when we're asked, it's a db query.
            if (empty($this->maxorderincolumn)) {
                $this->maxorderincolumn = get_field(
                    'block_instance', 
                    'max("order")', 
                    'view', $this->view, 'column', $this->column);
            }
        }
        return $this->{$field};
    }

    public function set($field, $value) {
        if (property_exists($this, $field)) {
            if ($field == 'configdata') {
                throw new InvalidArgumentException(get_string('blockconfigdatacalledfromset', 'error'));
            }
            if ($this->{$field} != $value) {
                // only set it to dirty if it's changed
                $this->dirty = true;
            }
            $this->{$field} = $value;
            return true;
        }
        throw new ParamOutOfRangeException("Field $field wasn't found in class " . get_class($this));
    }

    /**
     * Builds the HTML for the block, inserting the blocktype content at the 
     * appropriate place
     */
    public function render($javascript=false) {
        safe_require('blocktype', $this->get('blocktype'));
        $content = call_static_method(generate_class_name('blocktype', $this->get('blocktype')), 'render_instance', $this);

        $movecontrols = array();
        if (empty($javascript)) {
            if ($this->get('canmoveleft')) {
                $movecontrols[] = array(
                    'column' => $this->get('column') - 1,
                    'order'  => $this->get('order'),
                    'arrow'  => '&larr;',
                );
            }
            if ($this->get('canmovedown')) {
                $movecontrols[] = array(
                    'column' => $this->get('column'),
                    'order'   => $this->get('order') +1,
                    'arrow'   => '&darr;',
                );
            }
            if ($this->get('canmoveup')) {
                $movecontrols[] = array(
                    'column' => $this->get('column'),
                    'order'  => $this->get('order') -1,
                    'arrow'  => '&uarr;',
                );
            }
            if ($this->get('canmoveright')) {
                $movecontrols[] = array(
                    'column' => $this->get('column') + 1,
                    'order'  => $this->get('order'),
                    'arrow'  => '&rarr;',
                );
            }
        }
        $smarty = smarty_core();

        $smarty->assign('id',     $this->get('id'));
        $smarty->assign('title',  $this->get('title'));
        $smarty->assign('column', $this->get('column'));
        $smarty->assign('order',  $this->get('order'));

        $smarty->assign('movecontrols', $movecontrols);
        $smarty->assign('content', $content);
        $smarty->assign('javascript', $javascript);

        return $smarty->fetch('view/blocktypecontainer.tpl');
    }

    public function commit() {
        if (empty($this->dirty)) {
            return;
        }
        $fordb = new StdClass;
        foreach (get_object_vars($this) as $k => $v) {
            $fordb->{$k} = $v;
        }
        if (empty($this->id)) {
            $this->id = insert_record('block_instance', $fordb, 'id', true);
        }
        else {
            update_record('block_instance', $fordb, 'id');
        }

        // @TODO maybe handle_event here.

        $this->dirty = false;
    }

    /**
     * @return View the view object this block instance is in
     */
    public function get_view() {
        if (empty($this->view_obj)) {
            $this->view_obj = new View($this->get('view'));
        }
        return $this->view_obj;
    }

    public function can_move($direction) {
        switch ($direction) {
            case 'left':
                return ($this->column > 1);
            case 'right':
                return ($this->column < $this->get_view()->get('numcolumns'));
            case 'up':
                return ($this->order > 1);
                break;
            case 'down':
                return ($this->order < $this->get('maxorderincolumn'));
            default:
                throw new InvalidArgumentException(get_string('invaliddirection', 'error', $direction));
        }
    }

    public function delete() {
        if (empty($this->id)) {
            $this->dirty = false;
            return;
        }
        
        delete_records('view_artefact', 'block', $this->id);
        delete_records('block_instance', 'id', $this->id);

        $this->dirty = false;
        safe_require('blocktype', $this->get('blocktype'));
        call_static_method(generate_class_name('blocktype', $this->get('blocktype')), 'delete_instance', $this);
    }

}


?>
