<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-image
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

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

    public static function render_instance(BlockInstance $instance, $editing=false) {
        require_once(get_config('docroot') . 'artefact/lib.php');
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

                $file = array(
                    'id' => $artefactid,
                    'title' => $artefact->get('title'),
                    'description' => $artefact->get('description'),
                    'size' => $artefact->get('size'),
                    'ctime' => $artefact->get('ctime'),
                    'artefacttype' => $artefact->get('artefacttype'),
                    'iconsrc' => call_static_method(
                        generate_artefact_class_name($artefact->get('artefacttype')),
                        'get_icon',
                        array('id' => $artefactid, 'viewid' => $viewid)
                    ),
                    'downloadurl' => $wwwroot,
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

        $smarty = smarty_core();
        $smarty->assign('viewid', $instance->get('view'));
        $smarty->assign('files', $files);
        return $smarty->fetch('blocktype:filedownload:filedownload.tpl');
    }

    public static function has_instance_config() {
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
                'class'        => 'last with-formgroup',
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

    public static function default_copy_type() {
        return 'full';
    }

}
