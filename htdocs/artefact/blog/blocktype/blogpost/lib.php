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
 * @subpackage blocktype-blogpost
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeBlogpost extends PluginBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.blog/blogpost');
    }

    /**
     * Optional method. If exists, allows this class to decide the title for 
     * all blockinstances of this type
     */
    public static function get_instance_title(BlockInstance $bi) {
        $configdata = $bi->get('configdata');

        if (!empty($configdata['artefactid'])) {
            require_once(get_config('docroot') . 'lib/artefact.php');
            $blogpost = artefact_instance_from_id($configdata['artefactid']);
            return $blogpost->get('title');
        }
        return '';
    }

    public static function get_description() {
        return get_string('description', 'blocktype.blog/blogpost');
    }

    public static function get_categories() {
        return array('blog');
    }

    public static function render_instance(BlockInstance $instance) {
        $configdata = $instance->get('configdata');

        $result = '';
        if (!empty($configdata['artefactid'])) {
            require_once(get_config('docroot') . 'lib/artefact.php');
            $blogpost = artefact_instance_from_id($configdata['artefactid']);
            $configdata['hidetitle'] = true;
            $configdata['viewid'] = $instance->get('view');
            $result = $blogpost->render_self($configdata);
            $result = $result['html'];
        }

        return $result;
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        $configdata = $instance->get('configdata');
        return array(
            self::artefactchooser_element((isset($configdata['artefactid'])) ? $configdata['artefactid'] : null),
        );
    }

    public static function artefactchooser_element($default=null) {
        return array(
            'name'  => 'artefactid',
            'type'  => 'artefactchooser',
            'title' => get_string('blogpost', 'artefact.blog'),
            'defaultvalue' => $default,
            'rules' => array(
                'required' => true,
            ),
            'blocktype' => 'blogpost',
            'limit'     => 10,
            'selectone' => true,
            'artefacttypes' => array('blogpost'),
            'template'  => 'artefact:blog:artefactchooser-element.tpl',
        );
    }

    /**
     * Optional method. If specified, allows the blocktype class to munge the 
     * artefactchooser element data before it's templated
     */
    public static function artefactchooser_get_element_data($artefact) {
        static $blognames = array();

        if (!isset($blognames[$artefact->parent])) {
            $blognames[$artefact->parent] = get_field('artefact', 'title', 'id', $artefact->parent);
        }
        $artefact->blog = $blognames[$artefact->parent];

        $ellipsis = '';
        $artefact->description = trim(strip_tags($artefact->description));
        if (strlen($artefact->description) > 100) {
            $ellipsis = '…';
        }
        $artefact->description = substr($artefact->description, 0, 100) . $ellipsis;

        return $artefact;
    }

    /**
     * Optional method. If specified, changes the order in which the artefacts are sorted in the artefact chooser.
     *
     * This is a valid SQL string for the ORDER BY clause. Fields you can sort on are as per the artefact table
     */
    public static function artefactchooser_get_sort_order() {
        return 'parent, ctime DESC';
    }
}

?>
