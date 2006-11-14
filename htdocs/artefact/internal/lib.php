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
        return array(
            'profile_firstname',
            'profile_lastname',
            'profile_studentid',
            'profile_preferredname',
            'profile_introduction',
            'profile_emailaddress',
            'profile_officialwebsite',
            'profile_personalwebsite',
            'profile_blogaddress',
            'profile_postaladdress',
            'profile_town',
            'profile_cityregion',
            'profile_country',
            'profile_homephone',
            'profile_businessphone',
            'profile_mobilephone',
            'profile_faxnumber',
            'profile_icqnumber',
            'profile_msnchat',
            'profile_aimscreenname',
            'profile_yahoochat',
            'profile_skypeusername',
            'profile_jabberusername',
            'profile_occupation',
            'profile_industry',
            'profile_icon',
            'file',
            'folder',
            'image',
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

// @todo write ArtefactType$name classes for each of the 
// types returned by get_artefact_types 

?>
