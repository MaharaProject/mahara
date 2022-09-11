<?php
/**
 * Utility for the 'filedownload' blocktype
 *
 * @package    mahara
 * @subpackage blocktype-image
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

/**
 * Blocktype class for downloading files
 */
class PluginBlocktypeFiledownload extends MaharaCoreBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.file/filedownload');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.file/filedownload');
    }

    public static function get_categories() {
        return array('fileimagevideo' => 3000);
    }

    public static function get_viewtypes() {
        return array('dashboard', 'portfolio', 'profile', 'grouphomepage');
    }

    public static function render_instance_export(BlockInstance $instance, $editing=false, $versioning=false, $exporting=null) {
        if ($exporting != 'pdf' && $exporting != 'pdflite') {
            return self::render_instance($instance, $editing, $versioning);
        }
        // The exporting for pdf
        $files = self::render_instance_data($instance, $editing, $versioning);

        $smarty = smarty_core();
        $smarty->assign('viewid', $instance->get('view'));
        $smarty->assign('blockid', $instance->get('id'));
        $smarty->assign('files', $files);
        $smarty->assign('editing', $editing);
        $smarty->assign('exporttype', $exporting);
        return $smarty->fetch('blocktype:filedownload:filedownload_pdfexport.tpl');
    }

    public static function render_instance(BlockInstance $instance, $editing=false, $versioning=false) {
        $files = self::render_instance_data($instance, $editing, $versioning);

        $smarty = smarty_core();
        $smarty->assign('viewid', $instance->get('view'));
        $smarty->assign('blockid', $instance->get('id'));
        $smarty->assign('files', $files);
        $smarty->assign('editing', $editing);
        return $smarty->fetch('blocktype:filedownload:filedownload.tpl');
    }

    private static function render_instance_data(BlockInstance $instance, $editing=false, $versioning=false) {
        require_once(get_config('docroot') . 'artefact/lib.php');
        require_once(get_config('docroot') . 'artefact/comment/lib.php');

        $configdata = $instance->get('configdata');

        $viewid = $instance->get('view');
        $wwwroot = get_config('wwwroot');
        $files = array();

        if (isset($configdata['artefactids']) && is_array($configdata['artefactids'])) {
            foreach ($configdata['artefactids'] as $artefactid) {
                try {
                    $artefact = $instance->get_artefact_instance($artefactid);
                }
                catch (ArtefactNotFoundException $e) {
                    continue;
                }
                $view = new View($viewid);
                list($commentcount, $comments) = ArtefactTypeComment::get_artefact_comments_for_view($artefact, $view, $instance->get('id'), true, $editing, $versioning);

                $artefacttypeclass = generate_artefact_class_name($artefact->get('artefacttype'));
                $iconoptions = ['id' => $artefactid, 'viewid' => $viewid];
                $file = array(
                    'id' => $artefactid,
                    'title' => $artefact->get('title'),
                    'description' => $artefact->get('description'),
                    'size' => $artefact->get('size'),
                    'ctime' => $artefact->get('ctime'),
                    'artefacttype' => $artefact->get('artefacttype'),
                    'iconsrc' => $artefacttypeclass::get_icon($iconoptions),
                    'downloadurl' => $wwwroot,
                    'commentcount' => $commentcount,
                    'allowcomments' => $artefact->get('allowcomments'),
                );

                if ($artefact instanceof ArtefactTypeProfileIcon) {
                    $file['downloadurl'] .= 'thumb.php?type=profileiconbyid&id=' . $artefactid . '&view=' . $viewid;
                }
                else if ($artefact instanceof ArtefactTypeFile) {
                    $file['downloadurl'] .= 'artefact/file/download.php?file=' . $artefactid . '&view=' . $viewid;
                }
                $file['is_image'] = ($artefact instanceof ArtefactTypeImage) ? true : false;
                $files[] = $file;
            }
        }
        return $files;
    }

    public static function has_instance_config(BlockInstance $instance) {
        return true;
    }

    public static function instance_config_form(BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        safe_require('artefact', 'file');
        $instance->set('artefactplugin', 'file');
        return array(
            'artefactfieldset' => array(
                'type'         => 'fieldset',
                'collapsible'  => true,
                'collapsed'    => true,
                'legend'       => get_string('Files', 'blocktype.file/filedownload'),
                'class'        => 'first last with-formgroup',
                'elements'     => array(
                    'artefactid' => self::filebrowser_element($instance, (isset($configdata['artefactids'])) ? $configdata['artefactids'] : null)
                )
            )
        );
    }

    public static function artefactchooser_element($default=null) {
        return array(
            'name' => 'artefactids',
            'type'  => 'artefactchooser',
            'title' => get_string('Files', 'blocktype.file/filedownload'),
            'defaultvalue' => $default,
            'blocktype' => 'filedownload',
            'limit' => 10,
            'selectone' => false,
            'selectmodal' => true,
            'artefacttypes' => array('file', 'image', 'profileicon'),
            'template' => 'artefact:file:artefactchooser-element.tpl',
        );
    }

    /**
     * Optional method. If specified, allows the blocktype class to munge the
     * artefactchooser element data before it's templated
     */
    public static function artefactchooser_get_element_data($artefact) {
        return ArtefactTypeFileBase::artefactchooser_get_file_data($artefact);
    }

    public static function filebrowser_element(&$instance, $default=array()) {
        $element = ArtefactTypeFileBase::blockconfig_filebrowser_element($instance, $default);
        $element['title'] = get_string('Files', 'blocktype.file/filedownload');
        $element['name'] = 'artefactids';
        $element['config']['selectone'] = false;
        $element['config']['selectmodal'] = true;
        return $element;
    }

    public static function default_copy_type(BlockInstance $instance, View $view) {
        return 'full';
    }

    public static function postinst($prevversion) {
        if ($prevversion < 2022051000) {
            // set the blocktype to have quickedit mode
            set_field('blocktype_installed', 'quickedit', 1, 'name', 'filedownload');
        }
        return true;
    }

    public static function instance_quickedit_form(BlockInstance $instance) {
        $elements = self::instance_config_form($instance);
        $elements['quickedit'] = array(
            'type' => 'hidden',
            'value' => true
        );
        return $elements;
    }
}
