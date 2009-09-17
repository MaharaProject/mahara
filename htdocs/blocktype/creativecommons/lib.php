<?php
/**
 * Creative Commons License Block type for Mahara
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage blocktype-creativecommons
 * @author     Francois Marier <francois@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2009 Catalyst IT Ltd
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeCreativecommons extends SystemBlocktype {

    // Standard Creative Commons naming scheme
    const noncommercial = 'nc';
    const noderivatives = 'nd';
    const sharealike    = 'sa';

    public static function single_only() {
        return true;
    }

    public static function get_title() {
        return get_string('title', 'blocktype.creativecommons');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.creativecommons');
    }

    public static function get_categories() {
        return array('general');
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        $configdata = $instance->get('configdata');
        if (!isset($configdata['license'])) {
            return '';
        }

        $licensetype = $configdata['license'];
        $licenseurl = "http://creativecommons.org/licenses/$licensetype/3.0/";
        $licensename = get_string($licensetype, 'blocktype.creativecommons');

        $html = '<a rel="license" href="http://creativecommons.org/licenses/'.$licensetype.'/3.0/"><img alt="'.
            get_string('alttext', 'blocktype.creativecommons').
            '" style="border-width:0" src="'.get_config('wwwroot').'blocktype/creativecommons/icons/'.
            $licensetype.'-3_0.png" /></a>';
        $html .= '<br>';
        $html .= get_string('licensestatement', 'blocktype.creativecommons', $licenseurl, $licensename);
        return $html;
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_save($values) {
        $license = 'by';
        if (1 == $values['noncommercial']) {
            $license .= '-' . self::noncommercial;
        }

        if (1 == $values['noderivatives']) {
            $license .= '-' . self::sharealike;
        }
        elseif (2 == $values['noderivatives']) {
            $license .= '-' . self::noderivatives;
        }

        $configdata = array('title' => $values['title'],
                            'license' => $license);
        return $configdata;
    }

    public static function instance_config_form($instance) {
        $configdata = $instance->get('configdata');

        $noncommercial = 0;
        $noderivatives = 1;

        if (isset($configdata['license'])) {
            $noncommercial = !(strpos($configdata['license'], self::noncommercial) === false);

            $noderivatives = 0;
            if (strpos($configdata['license'], self::noderivatives) !== false) {
                $noderivatives = 2;
            }
            elseif (strpos($configdata['license'], self::sharealike) !== false) {
                $noderivatives = 1;
            }
        }

        $displayseal = 'class = "hidden"';
        if (0 == $noncommercial and $noderivatives < 2) {
            $displayseal = '';
        }

        // Dirty hack to display the seal just before the table
        // TODO: add a way to append stuff to the header in the maharatable pieform renderer
        $sealpositionhack = "obj = this.parentNode; objparent = obj.parentNode; targetform = obj.parentNode.parentNode.parentNode.parentNode.parentNode; targetdiv = targetform.parentNode; targetdiv.insertBefore(obj, targetform);";

        return array(
            'toggleseal' => array(
                'type' => 'html',
                'value' => '<script type="text/javascript">
function toggle_seal() {
    freeculture = true;
    sealimage = $("freecultureseal");

    nc_checkboxes = getElementsByTagAndClassName("input", null, $("instconf_noncommercial_container"));
    if (!nc_checkboxes[0].checked) {
        freeculture = false;
    }

    nd_checkboxes = getElementsByTagAndClassName("input", null, $("instconf_noderivatives_container"));
    if (nd_checkboxes[2].checked) {
        freeculture = false;
    }

    if (freeculture) {
        removeElementClass(sealimage, "hidden");
    }
    else {
        addElementClass(sealimage, "hidden");
    }
}
</script>',
            ),

            'seal' => array(
                'type' => 'html',
                'value' => '<a id="freecultureseal" href="http://freedomdefined.org/" '.$displayseal.'>'.
                           '<img "alt="'.hsc(get_string('sealalttext', 'blocktype.creativecommons')).'" '.
                           'onload="'.hsc($sealpositionhack).'" '.
                           'style="border-width:0; float: right" src="'.
                           get_config('wwwroot').'blocktype/creativecommons/icons/seal.png" /></a>',
            ),

            'noncommercial' => array(
                'type' => 'radio',
                'title' => get_string('config:noncommercial', 'blocktype.creativecommons'),
                'options' => array(
                                   0 => get_string('yes'),
                                   1 => get_string('no'),
                                   ),
                'onclick' => 'toggle_seal();',
                'defaultvalue' => $noncommercial,
                'separator' => '<br>',
                'help' => true,
                'rules' => array('required'    => true),
            ),

            'noderivatives' => array(
                'type' => 'radio',
                'title' => get_string('config:noderivatives', 'blocktype.creativecommons'),
                'options' => array(
                                   0 => get_string('yes'),
                                   1 => get_string('config:sharealike', 'blocktype.creativecommons'),
                                   2 => get_string('no'),
                                   ),
                'onclick' => 'toggle_seal();',
                'defaultvalue' => $noderivatives,
                'separator' => '<br>',
                'help' => true,
                'rules' => array('required'    => true),
            ),
        );
    }

    public static function default_copy_type() {
        return 'full';
    }

}

?>
