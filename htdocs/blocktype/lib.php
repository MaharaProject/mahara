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
                'thumbnail_path' => get_config('wwwroot') . 'thumb.php?type=blocktype&bt=' . $bt->name .'&ap=' . $bt->artefactplugin
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
            return true;
        }
        throw new InvalidArgumentException("Field $field wasn't found in class " . get_class($this));
    }

    public function __construct() {
        // @todo
    }

    public static function factory($id) {
        // @todo
    }

}


?>
