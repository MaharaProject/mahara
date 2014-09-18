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

class PluginBlocktypeFolder extends PluginBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.file/folder');
    }

    /**
     * Optional method. If exists, allows this class to decide the title for
     * all blockinstances of this type
     */
    public static function get_instance_title(BlockInstance $bi) {
        $configdata = $bi->get('configdata');

        if (!empty($configdata['artefactid'])) {
            return $bi->get_artefact_instance($configdata['artefactid'])->get('title');
        }
        return '';
    }

    public static function get_description() {
        return get_string('description', 'blocktype.file/folder');
    }

    public static function get_categories() {
        return array('fileimagevideo');
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        require_once(get_config('docroot') . 'artefact/lib.php');
        $configdata = $instance->get('configdata');
        $configdata['viewid'] = $instance->get('view');
        $configdata['simpledisplay'] = true;

        // This can be either an image or profileicon. They both implement
        // render_self
        $result = '';
        if (isset($configdata['artefactid'])) {
            $folder = $instance->get_artefact_instance($configdata['artefactid']);
            $result = $folder->render_self($configdata);
            $result = $result['html'];
        }

        return $result;
    }

    public static function has_config() {
        return true;
    }

    public static function get_config_options() {
        $elements = array();
        $elements['foldersettings'] = array(
            'type' => 'fieldset',
            'legend' => get_string('foldersettings', 'blocktype.file/folder'),
            'collapsible' => false,
            'elements' => array(
                'sortorder' => array(
                    'type'         => 'select',
                    'labelhtml'    => get_string('defaultsortorder', 'blocktype.file/folder'),
                    'defaultvalue' => get_config_plugin('blocktype', 'folder', 'sortorder'),
                    'options'      => array(
                        'asc' => get_string('ascending'),
                        'desc' => get_string('descending'),
                    )
                )
            ),
        );
        $elements['downloadfolderzip'] = array(
            'type' => 'fieldset',
            'legend' => get_string('zipdownloadheading', 'artefact.file'),
            'elements' => array(
                'folderdownloadzip' => array(
                    'type' => 'checkbox',
                    'title' => get_string('downloadfolderzip', 'artefact.file'),
                    'description' => get_string('downloadfolderzipdescription1', 'artefact.file'),
                    'defaultvalue' => get_config_plugin('blocktype', 'folder', 'folderdownloadzip'),
                ),
            ),
        );
        return array(
            'elements' => $elements,
        );
    }

    public static function save_config_options($form, $values) {
        set_config_plugin('blocktype', 'folder', 'sortorder', $values['sortorder']);
        set_config_plugin('blocktype', 'folder', 'folderdownloadzip', $values['folderdownloadzip']);
    }

    public static function postinst($prevversion) {
        if ($prevversion < 2013120900) {
            set_config_plugin('blocktype', 'folder', 'sortorder', 'asc');
        }
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        $configdata = $instance->get('configdata');
        safe_require('artefact', 'file');
        $instance->set('artefactplugin', 'file');
        $elements = array(
            'artefactid' => self::filebrowser_element($instance, (isset($configdata['artefactid'])) ? array($configdata['artefactid']) : null),
            'sortorder' => array(
                'type' => 'select',
                'labelhtml' => get_string('sortorder'),
                'defaultvalue' => isset($configdata['sortorder']) ? $configdata['sortorder'] : get_config_plugin('blocktype', 'folder', 'sortorder'),
                'options' => array('asc' => get_string('ascending'), 'desc' => get_string('descending')),
            ),
        );
        if (get_config_plugin('blocktype', 'folder', 'folderdownloadzip')) {
            $elements['folderdownloadzip'] = array(
                'type' => 'checkbox',
                'labelhtml' => get_string('downloadfolderzipblock', 'artefact.file'),
                'description' => get_string('downloadfolderzipdescriptionblock', 'artefact.file'),
                'defaultvalue' => (get_config_plugin('blocktype', 'folder', 'folderdownloadzip') ? (isset($configdata['folderdownloadzip']) ? $configdata['folderdownloadzip'] : 0) : 0),
            );
        }
        return $elements;
    }

    public static function artefactchooser_element($default=null) {
        return array(
            'name'  => 'artefactid',
            'type'  => 'artefactchooser',
            'title' => get_string('folder', 'artefact.file'),
            'defaultvalue' => $default,
            'blocktype' => 'folder',
            'limit' => 10,
            'artefacttypes' => array('folder'),
            'template' => 'artefact:file:artefactchooser-element.tpl',
        );
    }

    /**
     * Optional method. If specified, allows the blocktype class to munge the
     * artefactchooser element data before it's templated
     */
    public static function artefactchooser_get_element_data($artefact) {
        $folderdata = ArtefactTypeFileBase::artefactchooser_folder_data($artefact);

        $artefact->icon = call_static_method(generate_artefact_class_name($artefact->artefacttype), 'get_icon', array('id' => $artefact->id));
        $artefact->hovertitle = $artefact->description;

        $path = $artefact->parent ? ArtefactTypeFileBase::get_full_path($artefact->parent, $folderdata->data) : '';
        $artefact->description = str_shorten_text($folderdata->ownername . $path . $artefact->title, 30);

        return $artefact;
    }

    public static function artefactchooser_get_sort_order() {
        return array(array('fieldname' => 'parent'), array('fieldname' => 'title'));
    }

    public static function filebrowser_element(&$instance, $default=array()) {
        $element = ArtefactTypeFileBase::blockconfig_filebrowser_element($instance, $default);
        $element['title'] = get_string('file', 'artefact.file');
        $element['name'] = 'artefactid';
        $element['config']['upload'] = false;
        $element['config']['selectone'] = true;
        $element['config']['selectfolders'] = true;
        $element['filters'] = array(
            'artefacttype'    => array('folder'),
        );
        return $element;
    }

    public static function default_copy_type() {
        return 'full';
    }

}
