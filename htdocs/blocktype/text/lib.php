<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-text
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeText extends MaharaCoreBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.text');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.text');
    }

    public static function get_categories() {
        return array('shortcut' => 1000);
    }

    public static function get_artefacts(BlockInstance $instance) {
        safe_require('artefact', 'file');
        $configdata = $instance->get('configdata');
        // Add all artefacts found in the text
        if (empty($configdata['text'])) {
            $artefacts = array();
        }
        else {
            $artefacts = array_unique(artefact_get_references_in_html($configdata['text']));
        }
        return $artefacts;
    }

    public static function get_blocktype_type_content_types() {
        return array('text' => array('text'));
    }

    public static function render_instance(BlockInstance $instance, $editing=false, $versioning=false) {
        safe_require('artefact', 'file');
        $configdata = $instance->get('configdata');
        $smarty = smarty_core();
        if (array_key_exists('text', $configdata)) {
            $newtext = ArtefactTypeFolder::append_view_url($configdata['text'], $instance->get('view'));
            $smarty->assign('text', $newtext);
        }
        else {
            $smarty->assign('text', '');
        }
        return $smarty->fetch('blocktype:text:content.tpl');
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form(BlockInstance $instance) {
        require_once('license.php');
        $configdata = $instance->get('configdata');
        if (!$height = get_config('blockeditorheight')) {
            $cfheight = param_integer('cfheight', 0);
            $height = $cfheight ? $cfheight * 0.7 : 150;
        }

        $view = $instance->get_view();
        $text = '';
        if (array_key_exists('text', $configdata)) {
            $text = $configdata['text'];
        }

        $elements = array (
            'text' => array (
                'type' => 'wysiwyg',
                'title' => get_string('blockcontent', 'blocktype.text'),
                'width' => '100%',
                'height' => $height . 'px',
                'defaultvalue' => $text,
                'rules' => array('maxlength' => 1000000),
            ),
            'tags'  => array(
                'type'         => 'tags',
                'title'        => get_string('tags'),
                'description'  => get_string('tagsdescblock'),
                'defaultvalue' => $instance->get('tags'),
                'help'         => false,
            )
        );
        return $elements;
    }

    public static function instance_config_save($values, $instance) {
        require_once('embeddedimage.php');
        $newtext = EmbeddedImage::prepare_embedded_images($values['text'], 'text', $instance->get('id'));
        $values['text'] = $newtext;
        return $values;
    }

    public static function delete_instance(BlockInstance $instance) {
        require_once('embeddedimage.php');
        EmbeddedImage::delete_embedded_images('text', $instance->get('id'));
    }

    public static function default_copy_type() {
        return 'full';
    }

    /**
     *
     * @param array $biconfig   The block instance config
     * @param array $viewconfig The view config
     * @return BlockInstance    The newly made block instance
     */
    public static function import_create_blockinstance_leap(array $biconfig, array $viewconfig) {
        $configdata = $biconfig['config'];

        $bi = new BlockInstance(0,
            array(
                'blocktype'  => $biconfig['type'],
                'configdata' => $configdata,
            )
        );

        return $bi;
    }

    /**
     * Rewrite extra config data for a Text blockinstance
     *
     *      See more PluginBlocktype::import_rewrite_blockinstance_extra_config_leap()
     */
    public static function import_rewrite_blockinstance_extra_config_leap(array $artefactids, array $configdata) {
        // Rewrite embedded image urls in the configdata['text']
        if (!empty($configdata['text'])) {
            require_once('embeddedimage.php');
            $configdata['text'] = EmbeddedImage::rewrite_embedded_image_urls_from_import($configdata['text'], $artefactids);
        }
        else {
            $configdata['text'] = '';
        }
        return $configdata;
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
        $result = array('text' => json_encode(array($configdata['text'])));
        return $result;
    }

    public static function has_config() {
        return true;
    }

    public static function get_config_options() {
        $blocks = self::find_convertible_text_blocks();
        if (is_object($blocks)) {
            $convertibleblocksnumber = $blocks->NumRows();
        }
        else {
            $convertibleblocksnumber = 0;
        }
        return array(
            'elements' => array(
                'convertdescription' => array(
                    'value' => get_string('convertdescriptionfeatures', 'blocktype.text') . ' ' . get_string('convertdescription', 'blocktype.text',
                            $convertibleblocksnumber),
                ),
                'convertcheckbox' => array(
                    'type' => 'switchbox',
                    'defaultvalue' => false,
                    'title' => get_string('optionlegend', 'blocktype.text'),
                    'description' => get_string('switchdescription', 'blocktype.text'),
                ),
            )
        );
    }

    /**
     * Retrieves the text boxes that may be converted from Note to Textbox
     *
     * @param integer $limit Limit the number of records processed to this many.
     * @return ADORecordSet
     */
    private static function find_convertible_text_blocks($limit = null) {
        raise_memory_limit("512M");
        // find all note(textbox)-blocks that link to a note-artefact which is
        // not linked to any other note(textbox)-block, i.e. all textbox-blocks
        // whose artefact is used only once
        $query = "
            SELECT bi.id, bi.configdata, va.artefact, a.artefacttype
            FROM {block_instance} AS bi
            INNER JOIN {view_artefact} AS va ON va.block = bi.id AND bi.blocktype = 'textbox'
            INNER JOIN {artefact} AS a ON a.id = va.artefact
            LEFT JOIN {view_artefact} AS dummy ON va.artefact = dummy.artefact AND va.block != dummy.block
            LEFT JOIN {artefact_comment_comment} AS comment ON va.artefact = comment.onartefact
            WHERE dummy.block IS NULL AND comment.artefact IS NULL";
        if ($limit) {
            $query .= " LIMIT {$limit}";
        }

        return get_recordset_sql($query, array());
    }

    /**
     * Pieform success callback function for the config form. Converts the
     * text blocks, if the checkbox is ticked
     *
     * @param $form the pieform to send the ok-message to
     * @param array $values
     */
    public static function save_config_options(Pieform $form, $values) {
        global $SESSION;
        if (!array_key_exists('convertcheckbox', $values) || !$values['convertcheckbox']) {
            return;
        }
        $countconverted = self::convert_notes_to_text_blocks();
        $form->json_reply(PIEFORM_OK, get_string('convertibleokmessage', 'blocktype.text', $countconverted));
    }


    /**
     * This function is meant to be run (either via the "convertnotes.php" CLI script,
     * or from the blocktype/text plugin config page) shortly after upgrading to
     * Mahara 1.10. It will locate all the existing Note blocks & their underlying Note
     * artefacts, and convert them into simple Text blocks if they are not using
     * any of the Note artefact's advanced features.
     *
     * @param integer $limit Limit the number of records processed to this many.
     * @return integer The number of notes converted
     */
    public static function convert_notes_to_text_blocks($limit = null) {

        $rs = self::find_convertible_text_blocks($limit);
        if (!$rs) {
            log_info("No old-style Text Box blocks to process.");
            return 0;
        }

        $total = $rs->NumRows();
        $countprocessed = 0;
        $countconverted = 0;

        log_info("Preparing to process {$total} old-style Text Box blocks.");
        while ($record = $rs->FetchRow()) {

            $countprocessed++;
            if ($countprocessed % 1000 == 0) {
                log_info("{$countprocessed}/{$total} processed...");
            }

            $record = (object)$record;
            $oldconfigdata = unserialize($record->configdata);
            // don't convert textboxes with tags, because the text doesn't support tags
            if (array_key_exists('tags', $oldconfigdata) && count($oldconfigdata['tags']) > 0) {
                continue;
            }
            // don't convert textboxes with a license, because the text
            // doesn't support licenses
            if (array_key_exists('license', $oldconfigdata) && strlen($oldconfigdata['license']) > 0) {
                continue;
            }
            // don't convert textboxes with connected artefacts, because the text
            // doesn't support additional artefacts
            if (array_key_exists('artefactids', $oldconfigdata) && count($oldconfigdata['artefactids']) > 0) {
                continue;
            }
            // ignore if the artefacttype returned is not 'html' - seems to exist if a text box has a download link in the markup
            if ($record->artefacttype != 'html') {
                continue;
            }

            db_begin();
            $record = (object)array(
                'id' => $record->id,
                'configdata' => $oldconfigdata,
                'artefact' => $record->artefact,
            );

            $htmlartefact = new ArtefactTypeHtml($record->artefact);
            $newconfigdata = array(
                'text' => $htmlartefact->get('description'),
                'retractable' => false,
                'retractedonload' => false,
            );
            if (array_key_exists('retractable', $record->configdata)) {
                $newconfigdata['retractable'] = $record->configdata['retractable'];
            }
            if (array_key_exists('retractedonload', $record->configdata)) {
                $newconfigdata['retractedonload'] = $record->configdata['retractedonload'];
            }
            $whereobj = (object)(array('id' => $record->id,));
            $newobj = (object)(array(
                'blocktype' => 'text',
                'configdata' => serialize($newconfigdata),
            ));
            update_record('block_instance', $newobj, $whereobj);
            $htmlartefact->delete();
            $countconverted++;
            db_commit();
        }
        return $countconverted;
    }

    /**
     * Rewrites embedded image urls in the $configdata['text']
     *
     * See more in PluginBlocktype::rewrite_blockinstance_extra_config()
     */
    public static function rewrite_blockinstance_extra_config(View $view, BlockInstance $block, $configdata, $artefactcopies) {
        $regexp = array();
        $replacetext = array();
        foreach ($artefactcopies as $copyobj) {
            // Change the old image id to the new one
            $regexp[] = '#<img([^>]+)src="' . get_config('wwwroot') . 'artefact/file/download.php\?file=' . $copyobj->oldid . '([^0-9])#';
            $replacetext[] = '<img$1src="' . get_config('wwwroot') . 'artefact/file/download.php?file=' . $copyobj->newid . '$2';
        }
        $configdata['text'] = preg_replace($regexp, $replacetext, $configdata['text']);
        return $configdata;
    }
}
