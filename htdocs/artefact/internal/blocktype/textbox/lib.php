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

        if (!empty($configdata['artefactid'])) {
            safe_require('artefact', 'file');
            safe_require('artefact', 'comment');

            $artefact = $instance->get_artefact_instance($configdata['artefactid']);
            $viewid = $instance->get('view');
            $text = ArtefactTypeFolder::append_view_url($artefact->get('description'), $viewid);

            $smarty = smarty_core();
            $smarty->assign('text', $text);

            if ($artefact->get('allowcomments')) {
                $commentcount = ArtefactTypeComment::count_comments(null, array($configdata['artefactid']));
                $commentcount = isset($commentcount[$configdata['artefactid']]) ? $commentcount[$configdata['artefactid']]->comments : 0;
                $artefacturl = get_config('wwwroot') . 'view/artefact.php?view=' . $viewid . '&artefact=' . $configdata['artefactid'];
                $smarty->assign('artefacturl', $artefacturl);
                $smarty->assign('commentcount', $commentcount);
            }

            return $smarty->fetch('blocktype:textbox:content.tpl');
        }

        return '';
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
            'ownerinfo'        => true,
            'returnfields'     => array('id', 'title', 'description', 'safedescription', 'editable'),
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
    setNodeAttribute('instconf_title', 'value', a.title);
    tinyMCE.activeEditor.setContent(a.description);
    $('instconf_textreadonly_display').innerHTML = a.safedescription;
    $('instconf_makecopy').checked = false;
    if (a.editable == 1) {
        addElementClass('instconf_textreadonly_container', 'hidden');
        addElementClass('instconf_readonlymsg_container', 'hidden');
        removeElementClass('instconf_text_container', 'hidden');
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
    else {
        addElementClass('instconf_text_container', 'hidden');
        addElementClass('instconf_otherblocksmsg_container', 'hidden');
        removeElementClass('instconf_textreadonly_container', 'hidden');
        removeElementClass('instconf_readonlymsg_container', 'hidden');
    }
}
connect('chooseartefactlink', 'onclick', function(e) {
    e.stop();
    toggleElementClass('hidden', 'instconf_artefactid_container');
    toggleElementClass('hidden', 'instconf_managenotes_container');
});
forEach(getElementsByTagAndClassName('a', 'copytextboxnote', 'instconf'), function(link) {
    connect(link, 'onclick', function(e) {
        e.stop();
        forEach(getElementsByTagAndClassName('input', 'radio', 'artefactid_data'), function(i) {
            if (i.checked) {
                i.checked = false;
            }
        });
        $('instconf_makecopy').checked = true;
        addElementClass('instconf_textreadonly_container', 'hidden');
        addElementClass('instconf_readonlymsg_container', 'hidden');
        addElementClass('instconf_otherblocksmsg_container', 'hidden');
        removeElementClass('instconf_text_container', 'hidden');
    });
});
EOF;
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        global $USER;
        $instance->set('artefactplugin', 'internal');
        $configdata = $instance->get('configdata');
        if (!$height = get_config('blockeditorheight')) {
            $cfheight = param_integer('cfheight', 0);
            $height = $cfheight ? $cfheight * 0.7 : 150;
        }

        $otherblockcount = 0;
        $readonly = false;
        $text = '';
        $view = $instance->get_view();

        if (!empty($configdata['artefactid'])) {
            $artefactid = $configdata['artefactid'];
            try {
                $artefact = $instance->get_artefact_instance($artefactid);

                $readonly = $artefact->get('owner') !== $view->get('owner')
                    || $artefact->get('group') !== $view->get('group')
                    || $artefact->get('institution') !== $view->get('institution')
                    || $artefact->get('locked')
                    || !$USER->can_edit_artefact($artefact);

                $text = $artefact->get('description');

                if ($blocks = get_column('view_artefact', 'block', 'artefact', $artefactid)) {
                    $blocks = array_unique($blocks);
                    $otherblockcount = count($blocks) - 1;
                }
            }
            catch (ArtefactNotFoundException $e) {
                unset($artefactid);
            }
        }

        $otherblocksmsg = '<span id="textbox_blockcount">' . $otherblockcount . '</span>';
        $otherblocksmsg = get_string('textusedinotherblocks', 'blocktype.internal/textbox', $otherblocksmsg);

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
                'class' => 'message info' . (($otherblockcount && !$readonly) ? '' : ' hidden'),
                'value' => $otherblocksmsg
                    . ' <a class="copytextboxnote nojs-hidden-inline" href="">' . get_string('makeacopy', 'blocktype.internal/textbox') . '</a>',
                'help' => true,
            ),
            // Add a message whenever this text cannot be edited here
            'readonlymsg' => array(
                'type' => 'html',
                'class' => 'message info' . ($readonly ? '' : ' hidden'),
                'value' => get_string('readonlymessage', 'blocktype.internal/textbox')
                    . ' <a class="copytextboxnote nojs-hidden-inline" href="">' . get_string('makeacopy', 'blocktype.internal/textbox') . '</a>',
                'help' => true,
            ),
            'text' => array(
                'type' => 'wysiwyg',
                'class' => $readonly ? 'hidden' : '',
                'title' => get_string('blockcontent', 'blocktype.internal/textbox'),
                'width' => '100%',
                'height' => $height . 'px',
                'defaultvalue' => $text,
                'rules' => array('maxlength' => 65536),
            ),
            'textreadonly' => array(
                'type' => 'html',
                'class' => $readonly ? '' : 'hidden',
                'width' => '100%',
                'value' => '<div id="instconf_textreadonly_display">' . $text . '</div>',
            ),
            'makecopy' => array(
                'type' => 'checkbox',
                'class' => 'hidden',
                'defaultvalue' => false,
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
        $view = $instance->get_view();
        foreach (array('owner', 'group', 'institution') as $f) {
            $data[$f] = $view->get($f);
        }

        if (empty($values['artefactid']) || $values['makecopy']) {
            // The artefact title will be the same as the block title when the
            // artefact is first created, or, if there's no block title, generate
            // 'Note (1)', 'Note (2)', etc.  After that, the artefact title can't
            // be edited inside the block, but can be changed in the Notes area.
            if (empty($values['title'])) {
                $title = artefact_new_title(
                    get_string('Note', 'artefact.internal'), 'html',
                    $data['owner'], $data['group'], $data['institution']
                );
            }
            else {
                $title = $values['title'];
            }
            $artefact = new ArtefactTypeHtml(0, $data);
            $artefact->set('title', $title);
            $artefact->set('description', $values['text']);
        }
        else {
            $artefact = new ArtefactTypeHtml((int)$values['artefactid']);

            if (!$USER->can_publish_artefact($artefact)) {
                throw new AccessDeniedException(get_string('nopublishpermissiononartefact', 'mahara', hsc($artefact->get('title'))));
            }

            // Stop users from editing html artefacts whose owner is not the same as the
            // view owner, even if they would normally be allowed to edit the artefact.
            // It's too confusing.  Html artefacts with other owners *can* be included in
            // the view read-only, provided the artefact has the correct republish
            // permission.
            if ($artefact->get('owner') === $data['owner']
                && $artefact->get('group') === $data['group']
                && $artefact->get('institution') === $data['institution']
                && !$artefact->get('locked')
                && $USER->can_edit_artefact($artefact)) {
                $artefact->set('description', $values['text']);
            }
        }

        $artefact->commit();

        $values['artefactid'] = $artefact->get('id');
        $instance->save_artefact_instance($artefact);

        unset($values['text']);
        unset($values['otherblocksmsg']);
        unset($values['readonlymsg']);
        unset($values['textreadonly']);
        unset($values['makecopy']);
        unset($values['chooseartefact']);
        unset($values['managenotes']);

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
