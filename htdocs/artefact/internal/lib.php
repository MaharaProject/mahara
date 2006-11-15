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

class PluginArtefactInternal extends PluginArtefact {

    public static function get_artefact_types() {
        return array('file', 'folder', 'image',
                     'firstname', 'lastname', 'studentid', 
                     'preferredname', 'introduction', 
                     'email', 'officialwebsite', 'personalwebsite', 
                     'blog', 'address', 'town', 'city', 
                     'country', 'homenumber', 'businessnumber', 
                     'mobilenumber', 'faxnumber', 'icqnumber',
                     'msnnumber'
                     );
    }

    public static function get_plugin_name() {
        return 'internal';
    }

    public static function menu_items() {
        return array(
            array(
                'name' => 'myprofile',
                'link' => 'profile/',
            ),
            array(
                'name' => 'myfiles',
                'link' => 'files/',
            ),
        );
    }

    public static function postinst() {
        $types = self::get_artefact_types();
        $plugin = self::get_plugin_name();
        $ph = array();
        if (is_array($types)) {
            foreach ($types as $type) {
                $ph[] = '?';
                if (!record_exists('artefact_installed_type', 'plugin', $plugin, 'name', $type)) {
                    $t = new StdClass;
                    $t->name = $type;
                    $t->plugin = $plugin;
                    insert_record('artefact_installed_type',$t);
                }
            }
            $select = '(plugin = ? AND name NOT IN (' . implode(',', $ph) . '))';
            delete_records_select('artefact_installed_type', $select,
                                  array_merge(array($plugin),$types));
        }
    }
}

class ArtefactTypeProfile extends ArtefactType {

    public function commit() {
        $this->commit_basic();
    }
    
    public function delete() {
        $this->delete_basic();
    }

    public function render($format, $options) {

    }

    public function get_icon() {

    }

    public static function get_render_list() {

    }
    
    public static function can_render_to($format) {

    }

    public static function get_mandatory_fields() {
        return array('firstname', 'lastname', 'studentid', 'email');
    }
}

class ArtefactTypeCachedProfile extends ArtefactTypeProfile {
    
    public function commit() {
        $this->commit_basic();
        $field = $this->get_artefact_type();
        set_field('usr', $field, $this->title, 'id', $this->owner);
    }

    public function delete() {
        $this->delete_basic();
        $field = $this->get_artefact_type();
        set_field('usr', $field, null, 'id', $this->owner);
    }

}

class ArtefactTypeFirstname extends ArtefactTypeCachedProfile {}
class ArtefactTypeLastname extends ArtefactTypeCachedProfile {}
class ArtefactTypePreferredname extends ArtefactTypeCachedProfile {}
class ArtefactTypeEmail extends ArtefactTypeCachedProfile {}

class ArtefactTypeStudentid extends ArtefactTypeProfile {}
class ArtefactTypeIntroduction extends ArtefactTypeProfile {}
class ArtefactTypeOfficialwebsite extends ArtefactTypeProfile {}
class ArtefactTypePersonalwebsite extends ArtefactTypeProfile {}
class ArtefactTypeBlog extends ArtefactTypeProfile {}
class ArtefactTypeAddress extends ArtefactTypeProfile {}
class ArtefactTypeTown extends ArtefactTypeProfile {}
class ArtefactTypeCity extends ArtefactTypeProfile {}
class ArtefactTypeCountry extends ArtefactTypeProfile {}
class ArtefactTypeHomenumber extends ArtefactTypeProfile {}
class ArtefactTypeBusinessnumber extends ArtefactTypeProfile {}
class ArtefactTypeMobilenumber extends ArtefactTypeProfile {}
class ArtefactTypeFaxnumber extends ArtefactTypeProfile {}
class ArtefactTypeIcqnumber extends ArtefactTypeProfile {}
class ArtefactTypeMsnnumber extends ArtefactTypeProfile {}

class ArtefactTypeFolder extends ArtefactTypeProfile {}
class ArtefactTypeFile extends ArtefactTypeProfile {}
class ArtefactTypeImage extends ArtefactTypeFile {}

?>
