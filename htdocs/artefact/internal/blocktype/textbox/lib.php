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

    public static function artefactchooser_element($default=null) {
        return array(
            'name'             => 'artefactid',
            'type'             => 'artefactchooser',
            'class'            => 'hidden',
            'defaultvalue'     => $default,
            'blocktype'        => 'textbox',
            'limit'            => 5,
            'selectone'        => true,
            'selectjscallback' => 'updateTextContent',
            'getblocks'        => true,
            'returnfields'     => array('id', 'title', 'description'),
            'artefacttypes'    => array('html'),
            'template'         => 'artefact:internal:html-artefactchooser-element.tpl',
        );
    }

    public static function get_instance_config_javascript($instance) {
        // When an artefact is selected in the artefactchooser, update the
        // contents of the wysiwyg editor and the message about the number
        // of blocks containing the new artefact.
        $blockid = $instance->get('id');
        return <<<EOF
function updateTextContent(a) {
    tinyMCE.activeEditor.setContent(a.description);
    setNodeAttribute('instconf_title', 'value', a.title);
    var blockcountmsg = $('instconf_otherblocksmsg_container');
    if (blockcountmsg && $('textbox_blockcount')) {
        var otherblockcount = 0;
        if (a.blocks && a.blocks.length > 0) {
            for (var i = 0; i < a.blocks.length; i++) {
                if (a.blocks[i] != {$blockid}) {
                    otherblockcount++;
                }
            }
        }
        if (otherblockcount) {
            replaceChildNodes('textbox_blockcount', otherblockcount);
            removeElementClass(blockcountmsg, 'hidden');
        }
        else {
            addElementClass(blockcountmsg, 'hidden');
        }
    }
}
connect('chooseartefactlink', 'onclick', function(e) {
    e.stop();
    toggleElementClass('hidden', 'instconf_artefactid_container');
    toggleElementClass('hidden', 'instconf_managenotes_container');
});
EOF;
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        $instance->set('artefactplugin', 'internal');
        $configdata = $instance->get('configdata');
        if (!$height = get_config('blockeditorheight')) {
            $cfheight = param_integer('cfheight', 0);
            $height = $cfheight ? $cfheight * 0.7 : 150;
        }
        $otherblockcount = 0;
        if (!empty($configdata['artefactid'])) {
            if ($blocks = get_column('view_artefact', 'block', 'artefact', $configdata['artefactid'])) {
                $blocks = array_unique($blocks);
                $otherblockcount = count($blocks) - 1;
            }
            $artefactid = $configdata['artefactid'];
            $text = $instance->get_artefact_instance($configdata['artefactid'])->get('description');
        }

        $otherblocksmsg = '<span id="textbox_blockcount">' . $otherblockcount . '</span>';
        $otherblocksmsg = get_string('textusedinotherblocks', 'blocktype.internal/textbox', $otherblocksmsg);

        $view = $instance->get_view();
        $manageurl = get_config('wwwroot') . 'artefact/internal/notes.php';
        if ($group = $view->get('group')) {
            $manageurl .= '?group=' . $group;
        }
        else if ($institution = $view->get('institution')) {
            $manageurl .= '?institution=' . $institution;
        }

        $elements = array(
            // Add a message whenever this text appears in some other block
            'otherblocksmsg' => array(
                'type' => 'html',
                'class' => $otherblockcount ? '' : 'hidden',
                'value' => '<div class="message info">' . $otherblocksmsg . '</div>',
            ),
            'text' => array(
                'type' => 'wysiwyg',
                'title' => get_string('blockcontent', 'blocktype.internal/textbox'),
                'width' => '100%',
                'height' => $height . 'px',
                'defaultvalue' => isset($text) ? $text : '',
                'rules' => array('maxlength' => 65536),
            ),
            'chooseartefact' => array(
                'type'  => 'html',
                'class' => 'nojs-hidden-block',
                'value' => '<a id="chooseartefactlink" href="">'
                    . get_string('usecontentfromanothertextbox', 'blocktype.internal/textbox') . '</a>',
            ),
            'artefactid' => self::artefactchooser_element(isset($artefactid) ? $artefactid : null),
            'managenotes' => array(
                'type'  => 'html',
                'class' => 'right hidden',
                'value' => '<a href="' . $manageurl . '" target="_blank">'
                    . get_string('managealltextboxcontent', 'blocktype.internal/textbox') . ' &raquo;</a>',
            ),
        );
        return $elements;
    }

    public static function instance_config_save($values, $instance) {
        global $USER;
        $data = array();

        if (empty($values['artefactid'])) {
            $view = $instance->get_view();
            foreach (array('owner', 'group', 'institution') as $f) {
                $data[$f] = $view->get($f);
            }
        }

        $artefact = new ArtefactTypeHtml((int)$values['artefactid'], $data);
        if (!$USER->can_edit_artefact($artefact)) {
            throw new AccessDeniedException(get_string('accessdenied', 'error'));
        }

        $artefact->set('title', $values['title']);
        $artefact->set('description', $values['text']);
        $artefact->commit();

        $values['artefactid'] = $artefact->get('id');
        $instance->save_artefact_instance($artefact);

        unset($values['text']);
        unset($values['otherblocksmsg']);
        unset($values['chooseartefact']);

        // Pass back a list of any other blocks that need to be rendered
        // due to this change.
        $values['_redrawblocks'] = array_unique(get_column(
            'view_artefact', 'block',
            'artefact', $values['artefactid'],
            'view', $instance->get('view')
        ));

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
