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
 * @subpackage blocktype-image
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeImage extends PluginBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.file/image');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.file/image');
    }

    public static function get_categories() {
        return array('file');
    }

    public static function render_instance(BlockInstance $instance) {
        require_once(get_config('docroot') . 'lib/artefact.php');
        $configdata = $instance->get('configdata'); // this will make sure to unserialize it for us
//        $image = artefact_instance_from_id($configdata['artefactid']);
//        return $image->render_self($configdata); // in this case, config data contains width & height
        return 'whee!';
    }

}

?>
