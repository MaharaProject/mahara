<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-textbox
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeTextbox extends MaharaCoreBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.internal/textbox');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.internal/textbox');
    }

    public static function get_categories() {
        return array('general' => 24000);
    }

    public function can_have_attachments() {
        return true;
    }

    public static function render_instance(BlockInstance $instance, $editing=false, $versioning=false) {
        $configdata = $instance->get('configdata');
        if (!empty($configdata['artefactid'])) {
            safe_require('artefact', 'file');
            safe_require('artefact', 'comment');

            $artefact = $instance->get_artefact_instance($configdata['artefactid']);
            $viewid = $instance->get('view');
            if ($versioning) {
                $text = $configdata['text'];
            }
            else {
                $text = ArtefactTypeFolder::append_view_url($artefact->get('description'), $viewid);
            }
            $smarty = smarty_core();
            $smarty->assign('text', $text);

            $attachments = $artefact->get_attachments();
            if ($attachments) {
                require_once(get_config('docroot') . 'artefact/lib.php');
                foreach ($attachments as &$attachment) {
                    $f = artefact_instance_from_id($attachment->id);
                    $attachment->size = $f->describe_size();
                    $attachment->iconpath = $f->get_icon(array('id' => $attachment->id, 'viewid' => isset($options['viewid']) ? $options['viewid'] : 0));
                    $attachment->viewpath = get_config('wwwroot') . 'artefact/artefact.php?artefact=' . $attachment->id . '&view=' . (isset($viewid) ? $viewid : 0);
                    $attachment->downloadpath = get_config('wwwroot') . 'artefact/file/download.php?file=' . $attachment->id;
                    if (isset($viewid)) {
                        $attachment->downloadpath .= '&view=' . $viewid;
                    }
                }
                $smarty->assign('count', count($attachments));
            }
            $smarty->assign('attachments', $attachments);
            require_once(get_config('docroot') . 'lib/view.php');
            $view = new View($viewid);
            list($commentcount, $comments) = ArtefactTypeComment::get_artefact_comments_for_view($artefact, $view, $instance->get('id'), true, $editing, $versioning);
            $smarty->assign('commentcount', $commentcount);
            $smarty->assign('comments', $comments);
            $smarty->assign('blockid', $instance->get('id'));
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
            'class'            => 'd-none',
            'defaultvalue'     => $default,
            'blocktype'        => 'textbox',
            'limit'            => 5,
            'selectone'        => true,
            'selectjscallback' => 'updateTextContent',
            'getblocks'        => true,
            'ownerinfo'        => true,
            'returnfields'     => array('id', 'title', 'description', 'tags', 'license', 'licensor', 'licensorurl', 'safedescription', 'safetags', 'safelicense', 'editable', 'attachments'),
            'artefacttypes'    => array('html'),
            'template'         => 'artefact:internal:html-artefactchooser-element.tpl',
            'lazyload'         => true,
        );
    }

    public static function artefactchooser_get_element_data($artefact) {

        require_once('license.php');
        $artefactobj = artefact_instance_from_id($artefact->id);
        $artefact->safelicense = render_license($artefactobj);
        $artefact->tags = ArtefactType::artefact_get_tags($artefact->id);
        $artefact->safetags = is_array($artefact->tags) ? hsc(join(', ', $artefact->tags)) : '';

        return $artefact;
    }

    public static function get_instance_config_javascript(BlockInstance $instance) {
        // When an artefact is selected in the artefactchooser, update the
        // contents of the wysiwyg editor and the message about the number
        // of blocks containing the new artefact.
        $blockid = $instance->get('id');
        return <<<EOF
formchangemanager.setFormStateById('instconf', FORM_CHANGED);
function updateTextContent(a) {
    jQuery('#instconf_title').prop('value', a.title);
    if (typeof(tinyMCE) != 'undefined') {
        tinyMCE.activeEditor.setContent(a.description);
    }
    jQuery('#instconf_license').prop('value', a.license);
    jQuery('#instconf_licensor').prop('value', a.licensor);
    jQuery('#instconf_licensorurl').prop('value', a.licensorurl);
    jQuery('#instconf_textreadonly_display').html(a.safedescription);
    jQuery('#instconf_licensereadonly_display').html(a.safelicense);
    if (a.tags && a.tags.length > 0) {
        for (var i = 0; i < a.tags.length; i++) {
            // Set the value, creating a new option if necessary
            if (!$('#instconf_tags').find("option[value='" + a.tags[i] + "']").length) {
                // Create a DOM Option and pre-select by default
                var newOption = new Option(a.tags[i], a.tags[i], true, true);
                // Append it to the select
                $('#instconf_tags').append(newOption);
            }
        }
        $('#instconf_tags').val(a.tags).trigger('change');
    }
    jQuery('#instconf_textreadonly_display').html(a.safedescription);
    jQuery('#instconf_tagsreadonly_display').html(a.safetags);
    jQuery('#instconf_makecopy').prop('checked', false);
    if (a.editable == 1) {
        jQuery('#instconf_textreadonly_container').addClass('d-none');
        jQuery('#instconf_readonlymsg_container').addClass('d-none');
        jQuery('#instconf_licensereadonly_container').addClass('d-none');
        jQuery('#instconf_tagsreadonly_container').addClass('d-none');
        jQuery('#instconf_text_container').removeClass('d-none');
        if (jQuery('#instconf_license_container').length) {
            // only deal with these if the license metadata is enabled
            jQuery('#instconf_license_container').removeClass('d-none');
            jQuery('#instconf_license_description').removeClass('d-none');
            jQuery('#instconf_license_advanced_fieldset').removeClass('d-none');
        }
        jQuery('#instconf_tags_container').removeClass('d-none');
        jQuery('#instconf_tags_description').removeClass('d-none');
        var blockcountmsg = jQuery('#instconf_otherblocksmsg_container');
        if (blockcountmsg && jQuery('#textbox_blockcount')) {
            var otherblockcount = 0;
            if (a.blocks && a.blocks.length > 0) {
                for (var i = 0; i < a.blocks.length; i++) {
                    if (a.blocks[i] != {$blockid}) {
                        otherblockcount++;
                    }
                }
            }
            if (otherblockcount) {
                jQuery('#textbox_blockcount').empty().append(otherblockcount);
                jQuery(blockcountmsg).removeClass('d-none');
            }
            else {
                jQuery(blockcountmsg).addClass('d-none');
            }
        }

        if (typeof a.attachments != 'undefined') {
            // remove any attached files
            for (var key in instconf_artefactids.selecteddata) {
              jQuery('#instconf_artefactids_unselect_' + key).triggerHandler('click', instconf_artefactids.unselect);
            }
            // add in ones we need
            if (a.attachments.length > 0) {
                for (var i = 0; i < a.attachments.length; i++) {
                    instconf_artefactids.add_to_selected_list(a.attachments[i]);
                }
            }
        }
    }
    else {
        jQuery('#instconf_text_container').addClass('d-none');
        jQuery('#instconf_otherblocksmsg_container').addClass('d-none');
        if (jQuery('#instconf_license_container').length) {
            // only deal with these if the license metadata is enabled
            jQuery('#instconf_license_container').addClass('d-none');
            jQuery('#instconf_license_description').addClass('d-none');
            jQuery('#instconf_license_advanced_fieldset').addClass('d-none');
        }
        jQuery('#instconf_tags_container').addClass('d-none');
        jQuery('#instconf_tags_description').addClass('d-none');
        jQuery('#instconf_textreadonly_container').removeClass('d-none');
        jQuery('#instconf_readonlymsg_container').removeClass('d-none');
        jQuery('#instconf_licensereadonly_container').removeClass('d-none');
        jQuery('#instconf_tagsreadonly_container').removeClass('d-none');
    }
}
jQuery('#chooseartefactlink').on('click', function(e) {
    e.preventDefault();
    // if the artefact chooser is hidden, use paginator p to populate it, then toggle its visibility
    if (jQuery('#instconf_artefactid_container').hasClass('d-none')) {
        var queryData = [];
        queryData.extradata = JSON.stringify(p.extraData);
        p.sendQuery(queryData, true);
    }
    jQuery('#instconf_artefactid_container').toggleClass('d-none');
    jQuery('#instconf_managenotes_container').toggleClass('d-none');
});
jQuery('#instconf a.copytextboxnote').each(function() {
    jQuery(this).on('click', function(e) {
        e.preventDefault();
        jQuery('#artefactid_data input.radio').each(function() {
            if (jQuery(this).prop('checked')) {
                jQuery(this).prop('checked', false);
            }
        });
        jQuery('#instconf_makecopy').prop('checked', true);
        jQuery('#instconf_textreadonly_container').addClass('d-none');
        jQuery('#instconf_readonlymsg_container').addClass('d-none');
        jQuery('#instconf_otherblocksmsg_container').addClass('d-none');
        jQuery('#instconf_licensereadonly_container').addClass('d-none');
        jQuery('#instconf_tagsreadonly_container').addClass('d-none');
        jQuery('#instconf_text_container').removeClass('d-none');
        if (jQuery('#instconf_license_container').length) {
            // only deal with these if the license metadata is enabled
            jQuery('#instconf_license_container').removeClass('d-none');
            jQuery('#instconf_license_description').removeClass('d-none');
            jQuery('#instconf_license_advanced_fieldset').removeClass('d-none');
        }
        jQuery('#instconf_tags_container').removeClass('d-none');
        jQuery('#instconf_tags_description').removeClass('d-none');
    });
});
if (jQuery('#instconf_license').length) {
    jQuery('#instconf_license').removeClass('d-none');
}
if (jQuery('#instconf_license_advanced_container').length) {
    jQuery('#instconf_license_advanced_container div').first().removeClass('d-none');
}
jQuery(function() {
    jQuery('#instconf_tags').on('change', function() {
        updatetagbuttons();
    });
    updatetagbuttons();

    function updatetagbuttons() {
        jQuery('#instconf_tags_container ul button').on('click', function(e) {
            e.stopPropagation();
            e.preventDefault();
            var li = jQuery(this).parent();
            var data = jQuery('#instconf_tags').select2('data');
            var value = null;
            for (var x in data) {
                if (li[0].title == data[x].text) {
                    value = data[x].id;
                    break;
                }
            }
            var val = jQuery('#instconf_tags').select2('val');
            var index = val.indexOf(value);
            if (index > -1) {
                val.splice(index, 1);
            }
            jQuery('#instconf_tags').select2('val', val);
        });
    }
});
EOF;
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form(BlockInstance $instance) {
        global $USER;
        require_once('license.php');
        safe_require('artefact', 'file');
        $instance->set('artefactplugin', 'internal');
        $configdata = $instance->get('configdata');
        if (!$height = get_config('blockeditorheight')) {
            $cfheight = param_integer('cfheight', 0);
            $height = $cfheight ? $cfheight * 0.7 : 150;
        }

        $otherblockcount = 0;
        $readonly = false;
        $text = '';
        $tags = '';
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
                $tags = $artefact->get('tags');

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

        // Update the attached files in block configdata as
        // it may change when attached files have been deleted
        $attachmentids = isset($artefact) ? $artefact->attachment_id_list() : false;
        if ($attachmentids !== false && isset($configdata['artefactids']) && $configdata['artefactids'] != $attachmentids) {
            $configdata['artefactids'] = $attachmentids;
            $instance->set('configdata', $configdata);
            $instance->commit();
        }

        $elements = array(
            // Add a message whenever this text appears in some other block
            'otherblocksmsg' => array(
                'type' => 'html',
                'class' => 'message info' . (($otherblockcount && !$readonly) ? '' : ' d-none'),
                'value' => '<p class="alert alert-warning">' . $otherblocksmsg
                    . ' <a class="copytextboxnote nojs-hidden-inline" href="">' . get_string('makeacopy', 'blocktype.internal/textbox') . '</a></p>',
                'help' => true,
            ),
            // Add a message whenever this text cannot be edited here
            'readonlymsg' => array(
                'type' => 'html',
                'class' => 'message info' . ($readonly ? '' : ' d-none'),
                'value' => '<p class="alert alert-warning">' . get_string('readonlymessage', 'blocktype.internal/textbox')
                    . ' <a class="copytextboxnote nojs-hidden-inline" href="">' . get_string('makeacopy', 'blocktype.internal/textbox') . '</a></p>',
                'help' => true,
            ),
            'text' => array(
                'type' => 'wysiwyg',
                'class' => $readonly ? 'd-none' : '',
                'title' => get_string('blockcontent', 'blocktype.internal/textbox'),
                'width' => '100%',
                'height' => $height . 'px',
                'defaultvalue' => $text,
                'rules' => array('maxlength' => 1000000),
            ),
            'textreadonly' => array(
                'type' => 'html',
                'class' => $readonly ? '' : 'd-none',
                'title' => get_string('blockcontent', 'blocktype.internal/textbox'),
                'value' => '<div id="instconf_textreadonly_display" class="well text-midtone">' . $text . '</div>',
            ),
            'makecopy' => array(
                'type' => 'checkbox',
                'class' => 'd-none',
                'defaultvalue' => false,
            ),
            'chooseartefact' => array(
                'type'  => 'html',
                'value' => '<a id="chooseartefactlink" href="#" class="btn btn-secondary">'
                    . get_string('usecontentfromanothertextbox1', 'blocktype.internal/textbox') . '</a>',
            ),
            'managenotes' => array(
                'type'  => 'html',
                'class' => 'd-none text-right',
                'value' => '<a href="' . $manageurl . '" class="float-right">'
                    . get_string('managealltextboxcontent1', 'blocktype.internal/textbox') . ' <span class="icon icon-arrow-right right" role="presentation"></span></a>',
            ),
            'artefactid' => self::artefactchooser_element(isset($artefactid) ? $artefactid : null),
            'license' => license_form_el_basic(isset($artefact) ? $artefact : null),
            'license_advanced' => license_form_el_advanced(isset($artefact) ? $artefact : null),
            'licensereadonly' => array(
                'type' => 'html',
                'class' => $readonly ? '' : 'd-none',
                'title' => get_string('license'),
                'value' => '<div id="instconf_licensereadonly_display">' . (isset($artefact) ? render_license($artefact) : get_string('licensenone1')) . '</div>',
            ),
            'allowcomments' => array(
                'type'         => 'switchbox',
                'title'        => get_string('allowcomments', 'artefact.comment'),
                'defaultvalue' => (!empty($artefact) ? $artefact->get('allowcomments') : 1),
            ),
            'tags' => array(
                'type' => 'tags',
                'class' => $readonly ? 'd-none' : '',
                'title' => get_string('tags'),
                'description' => get_string('tagsdescprofile'),
                'defaultvalue' => $tags,
            ),
            'tagsreadonly' => array(
                'type' => 'html',
                'class' => $readonly ? '' : 'd-none',
                'title' => get_string('tags'),
                'value' => '<div id="instconf_tagsreadonly_display">' . (is_array($tags) ? hsc(join(', ', $tags)) : '') . '</div>',
            ),
            'artefactfieldset' => array(
                'type'         => 'fieldset',
                'collapsible'  => true,
                'collapsed'    => true,
                'legend'       => get_string('attachments', 'artefact.blog'),
                'class'        => 'last with-formgroup',
                'elements'     => array(
                    'artefactids' => self::filebrowser_element($instance, (isset($configdata['artefactids'])) ? $configdata['artefactids'] : null),
                )
            )
        );
        if ($readonly) {
            $elements['license']['class'] = 'd-none';
            $elements['license_advanced']['class'] = 'd-none';
        }
        return $elements;
    }

    public static function delete_instance(BlockInstance $instance) {
        require_once('embeddedimage.php');
        $configdata = $instance->get('configdata');
        if (!empty($configdata)) {
            $artefactid = $configdata['artefactid'];
            if (!empty($artefactid)) {
                EmbeddedImage::delete_embedded_images($instance->get('blocktype'), $artefactid);
            }
        }
    }

    public static function instance_config_save($values, $instance) {
        global $USER;
        require_once('embeddedimage.php');
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
            safe_require('artefact', 'internal');
            $artefact = new ArtefactTypeHtml(0, $data);
            $artefact->set('title', $title);
            $artefact->set('description', $values['text']);
            if (get_config('licensemetadata')) {
                $artefact->set('license', $values['license']);
                $artefact->set('licensor', $values['licensor']);
                $artefact->set('licensorurl', $values['licensorurl']);
            }
            $artefact->set('allowcomments', (!empty($values['allowcomments']) ? $values['allowcomments'] : 0));
            $artefact->set('tags', $values['tags']);
        }
        else {
            $artefact = new ArtefactTypeHtml((int)$values['artefactid']);

            if (!$USER->can_publish_artefact($artefact)) {
                throw new AccessDeniedException(get_string('nopublishpermissiononartefact', 'mahara', hsc($artefact->get('title'))));
            }

            // Stop users from editing textbox artefacts whose owner is not the same as the
            // view owner, even if they would normally be allowed to edit the artefact.
            // It's too confusing.  Textbox artefacts with other owners *can* be included in
            // the view read-only, provided the artefact has the correct republish
            // permission.
            if ($artefact->get('owner') === $data['owner']
                && $artefact->get('group') === $data['group']
                && $artefact->get('institution') === $data['institution']
                && !$artefact->get('locked')
                && $USER->can_edit_artefact($artefact)) {
                $newdescription = EmbeddedImage::prepare_embedded_images($values['text'], 'textbox', (int)$values['artefactid'], $view->get('group'));
                $artefact->set('description', $newdescription);
                if (get_config('licensemetadata')) {
                    $artefact->set('license', $values['license']);
                    $artefact->set('licensor', $values['licensor']);
                    $artefact->set('licensorurl', $values['licensorurl']);
                }
                $artefact->set('tags', $values['tags']);
                $artefact->set('allowcomments', !empty($values['allowcomments']) ? 1 : 0);
            }
        }

        $artefact->commit();

        $newdescription = EmbeddedImage::prepare_embedded_images($values['text'], 'textbox', $artefact->get('id'), $view->get('group'));

        if ($newdescription !== $values['text']) {
            $updatedartefact = new stdClass();
            $updatedartefact->id = $artefact->get('id');
            $updatedartefact->description = $newdescription;
            update_record('artefact', $updatedartefact, 'id');
        }

        // Add attachments, if there are any...
        update_attachments($artefact, $values['artefactids'], null, null, true);

        $values['artefactid'] = $artefact->get('id');
        $instance->save_artefact_instance($artefact);

        unset($values['text']);
        unset($values['otherblocksmsg']);
        unset($values['readonlymsg']);
        unset($values['textreadonly']);
        unset($values['makecopy']);
        unset($values['chooseartefact']);
        unset($values['managenotes']);
        unset($values['allowcomments']);
        unset($values['tags']); // We save the tags against the artefact so don't need to save again against the block

        // Pass back a list of any other blocks that need to be rendered
        // due to this change.
        $values['_redrawblocks'] = array_unique(get_column(
            'view_artefact', 'block',
            'artefact', $values['artefactid'],
            'view', $instance->get('view')
        ));

        return $values;
    }

    public static function filebrowser_element(&$instance, $default=array()) {
        $element = ArtefactTypeFileBase::blockconfig_filebrowser_element($instance, $default);
        $element['title'] = get_string('attachments', 'artefact.blog');
        $element['name'] = 'artefactids';
        $element['config']['select'] = true;
        $element['config']['selectone'] = false;
        $element['config']['selectmodal'] = true;
        $element['config']['alwaysopen'] = false;
        return $element;
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
                'license' => (!empty($configdata['license'])) ? $configdata['license'] : '',
                'licensor' => (!empty($configdata['licensor'])) ? $configdata['licensor'] : '',
                'licensorurl' => (!empty($configdata['licensorurl'])) ? $configdata['licensorurl'] : '',
                'tags'        => (!empty($configdata['tags'])) ? $configdata['tags'] : '',
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
        $license = '';
        $licensor = '';
        $licensorurl = '';
        $tags = '';

        if (!empty($configdata['artefactid'])) {
            $result['artefactid'] = json_encode(array($configdata['artefactid']));
            $note = $bi->get_artefact_instance($configdata['artefactid']);
            $text = $note->get('description');
            $tags = $note->get('tags');
            $license = $note->get('license');
            $licensor = $note->get('licensor');
            $licensorurl = $note->get('licensorurl');
        }

        $result['text'] = json_encode(array($text));
        $result['tags'] = json_encode(array($tags));
        $result['license '] = json_encode(array($license));
        $result['licensor'] = json_encode(array($licensor));
        $result['licensorurl'] = json_encode(array($licensorurl));

        return $result;
    }
}
