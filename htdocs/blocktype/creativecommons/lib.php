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
        global $THEME;
        $configdata = $instance->get('configdata');
        if (!isset($configdata['license'])) {
            return '';
        }

        $licensetype = reset(preg_grep('/^([a-z\-]+)$/', array($configdata['license'])));
        $licenseurl = "http://creativecommons.org/licenses/$licensetype/3.0/";

        $view = $instance->get_view();
        $workname = '<span rel="dc:type" href="http://purl.org/dc/dcmitype/Text" property="dc:title">'
            . $view->display_title(true, false, false) . '</span>';
        $authorurl = $view->owner_link();
        $authorname = hsc($view->formatted_owner());

        $licensename = get_string('cclicensename', 'blocktype.creativecommons', get_string($licensetype, 'blocktype.creativecommons'));
        $licenselink = '<a rel="license" href="' . $licenseurl . '">' . $licensename . '</a>';
        $attributionlink = '<a rel="cc:attributionURL" property="cc:attributionName" href="' . $authorurl . '">' . $authorname . '</a>';
        $licensestatement = get_string('cclicensestatement', 'blocktype.creativecommons', $workname, $attributionlink, $licenselink);

        $permissionlink = '<a rel="cc:morePermissions" href="'. $authorurl .'">' . $authorname . '</a>';
        $otherpermissions = get_string('otherpermissions', 'blocktype.creativecommons', $permissionlink);

        $smarty = smarty_core();
        $smarty->assign('licenseurl', $licenseurl);
        $smarty->assign('licenselogo', $THEME->get_url('images/' . $licensetype . '-3_0.png', false, 'blocktype/creativecommons'));
        $smarty->assign('licensestatement', $licensestatement);
        $smarty->assign('otherpermissions', $otherpermissions);
        return $smarty->fetch('blocktype:creativecommons:statement.tpl');
    }

    public static function has_instance_config() {
        return true;
    }

    public static function get_instance_config_javascript() {
        return array('js/creativecommons.js');
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
        global $THEME;
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

        $displayseal = ' hidden';
        if (0 == $noncommercial and $noderivatives < 2) {
            $displayseal = '';
        }

        // Dirty hack to display the seal just before the table
        // TODO: add a way to append stuff to the header in the maharatable pieform renderer
        $sealpositionhack = "insertSiblingNodesBefore('instconf', $('freecultureseal')); addElementClass('instconf', 'fl');";

        return array(
            'seal' => array(
                'type' => 'html',
                'value' => '<div id="freecultureseal" class="fr' .$displayseal.'"><a href="http://freedomdefined.org/">'.
                           '<img "alt="'.hsc(get_string('sealalttext', 'blocktype.creativecommons')).'" '.
                           'onload="'.hsc($sealpositionhack).'" '.
                           'style="border-width:0;" src="'.
                           $THEME->get_url('images/seal.png', false, 'blocktype/creativecommons') . '" /></a></div>',
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
