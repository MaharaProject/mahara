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

class PluginBlocktypeText extends SystemBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.text');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.text');
    }

    public static function get_categories() {
        return array('general');
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        $configdata = $instance->get('configdata');
        $smarty = smarty_core();
        if (array_key_exists('text', $configdata)) {
            $smarty->assign('text', $configdata['text']);
        }
        else {
            $smarty->assign('text', '');
        }
        return $smarty->fetch('blocktype:text:content.tpl');
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
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
                'rules' => array('maxlength' => 65536),
            ),
        );
        return $elements;
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
        $convertibleblocksnumber = count(self::find_convertible_text_blocks());
        return array(
            'elements' => array(
                'convertdescription' => array(
                    'value' => get_string('convertdescriptionfeatures', 'blocktype.text') . ' ' . get_string('convertdescription', 'blocktype.text',
                            $convertibleblocksnumber),
                ),
                'convertcheckbox' => array(
                    'type' => 'checkbox',
                    'defaultvalue' => false,
                    'title' => get_string('optionlegend', 'blocktype.text'),
                    'description' => get_string('checkdescription', 'blocktype.text'),
                ),
            )
        );
    }

    /**
     * retrieves the number of text boxes that may be converted from Note to Textbox
     */
    private static function find_convertible_text_blocks() {
        // find all note(textbox)-blocks that link to a note-artefact which is
        // not linked to any other note(textbox)-block, i.e. all textbox-blocks
        // whose artefact is used only once
        $query = "
            SELECT bi.id, bi.configdata, va.artefact
            FROM {block_instance} AS bi
            INNER JOIN {view_artefact} AS va ON va.block = bi.id AND bi.blocktype = 'textbox'
            LEFT JOIN {view_artefact} AS dummy ON va.artefact = dummy.artefact AND va.block != dummy.block
            LEFT JOIN {artefact_comment_comment} AS comment ON va.artefact = comment.onartefact
            WHERE dummy.block IS NULL AND comment.artefact IS NULL";

        $records = get_records_sql_array($query, array());
        if (!$records) {
            $records = array();
        }
        $convertiblerecords = array();
        foreach ($records as $record) {
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
            $convertiblerecords[] = (object)array(
                'id' => $record->id,
                'configdata' => $oldconfigdata,
                'artefact' => $record->artefact,
            );
        }
        return $convertiblerecords;
    }

    /**
     * Pieform success callback function for the config form. Converts the
     * text blocks, if the checkbox is ticked
     *
     * @param $form the pieform to send the ok-message to
     * @param array $values
     */
    public static function save_config_options($form, $values) {
        global $SESSION;
        if (!array_key_exists('convertcheckbox', $values) || !$values['convertcheckbox']) {
            return;
        }
        $records = self::find_convertible_text_blocks();
        $countconverted = 0;

        db_begin();
        foreach ($records as $record) {
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
            $countconverted ++;
        }
        db_commit();
        $form->json_reply(PIEFORM_OK, get_string('convertibleokmessage', 'blocktype.text', $countconverted));
    }
}
