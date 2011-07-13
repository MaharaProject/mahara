<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
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
 * @subpackage blocktype-textbox
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeTextbox extends PluginBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.internal/textbox');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.internal/textbox');
    }

    public static function get_categories() {
        return array('general');
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        $configdata = $instance->get('configdata');
        $text = !empty($configdata['artefactid']) ? $instance->get_artefact_instance($configdata['artefactid'])->get('description') : '';
        safe_require('artefact', 'file');
        $text = ArtefactTypeFolder::append_view_url($text, $instance->get('view'));
        return clean_html($text);
    }

    /**
     * Returns a list of artefact IDs that are in this blockinstance.
     *
     * People may embed artefacts as images etc. They show up as links to the
     * download script, which isn't much to go on, but should be enough for us
     * to detect that the artefacts are therefore 'in' this blocktype.
     */
    public static function get_artefacts(BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        $artefacts = array();
        if (isset($configdata['artefactid'])) {
            $artefacts[] = $configdata['artefactid'];

            // Add all artefacts found in the text
            $text = $instance->get_artefact_instance($configdata['artefactid'])->get('description');
            $artefacts = array_unique(array_merge($artefacts, artefact_get_references_in_html($text)));
        }
        return $artefacts;
    }

    // Not used, but function definition required by base class
    public static function artefactchooser_element($default=null) {
        return array(
            'name'          => 'artefactid',
            'type'          => 'artefactchooser',
            'title'         => get_string('blockcontent', 'blocktype.internal/textbox'),
            'defaultvalue'  => $default,
            'blocktype'     => 'textbox',
            'limit'         => 10,
            'selectone'     => true,
        );
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        $configdata = $instance->get('configdata');
        if (!$height = get_config('blockeditorheight')) {
            $cfheight = param_integer('cfheight', 0);
            $height = $cfheight ? $cfheight * 0.7 : 150;
        }
        if (!empty($configdata['artefactid'])) {
            $artefactid = $configdata['artefactid'];
            $text = $instance->get_artefact_instance($configdata['artefactid'])->get('description');
        }
        $elements = array(
            'artefactid' => array(
                'type' => 'hidden', // @todo change to artefactchooser
                'value' => isset($artefactid) ? $artefactid : null,
            ),
            'text' => array(
                'type' => 'wysiwyg',
                'title' => get_string('blockcontent', 'blocktype.internal/textbox'),
                'width' => '100%',
                'height' => $height . 'px',
                'defaultvalue' => isset($text) ? $text : '',
                'rules' => array('maxlength' => 65536),
            ),
        );
        return $elements;
    }

    public static function instance_config_save($values, $instance) {
        $data = array();

        if (empty($values['artefactid'])) {
            $view = $instance->get_view();
            foreach (array('owner', 'group', 'institution') as $f) {
                $data[$f] = $view->get($f);
            }
        }

        $artefact = new ArtefactTypeHtml((int)$values['artefactid'], $data);
        $artefact->set('title', $values['title']);
        $artefact->set('description', $values['text']);
        $artefact->commit();

        $values['artefactid'] = $artefact->get('id');
        $instance->save_artefact_instance($artefact);

        unset($values['text']);
        return $values;
    }

    public static function default_copy_type() {
        return 'full';
    }

    /**
     * The content of this block is now stored as an html artefact, but older versions stored
     * the content directly in the 'text' property of the block config.  If this config has
     * 'text' but not 'artefactid', create an artefact.
     *
     * @param array $biconfig   The block instance config
     * @param array $viewconfig The view config
     * @return BlockInstance The newly made block instance
     */
    public static function import_create_blockinstance_leap(array $biconfig, array $viewconfig) {
        $configdata = $biconfig['config'];

        if (isset($configdata['text']) && !isset($configdata['artefactid'])) {
            $data = array(
                'title'       => $biconfig['title'],
                'description' => $configdata['text'],
                'owner'       => $viewconfig['owner'],
            );
            $artefact = new ArtefactTypeHtml(0, $data);
            $artefact->commit();
            $configdata['artefactid'] = $artefact->get('id');
            unset($configdata['text']);
        }

        $bi = new BlockInstance(0,
            array(
                'blocktype'  => $biconfig['type'],
                'configdata' => $configdata,
            )
        );

        return $bi;
    }

    /**
     * Set the text property of the block config so that exports can be imported
     * into older versions.
     *
     * @param BlockInstance $bi The blockinstance to export the config for.
     * @return array The config for the blockinstance
     */
    public static function export_blockinstance_config_leap(BlockInstance $bi) {
        $configdata = $bi->get('configdata');

        $result = array();

        $text = '';

        if (!empty($configdata['artefactid'])) {
            $result['artefactid'] = json_encode(array($configdata['artefactid']));
            $text = $bi->get_artefact_instance($configdata['artefactid'])->get('description');
        }

        $result['text'] = json_encode(array($text));

        return $result;
    }
}
