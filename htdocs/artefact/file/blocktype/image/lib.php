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

class PluginBlocktypeImage extends MaharaCoreBlocktype {

    public static function should_ajaxify() {
        // Most of the load time for an image block is waiting for
        // the image file to get served up, which is already
        // a separate client HTTP request. So no need to ajaxify.
        return false;
    }

    public static function get_title() {
        return get_string('title', 'blocktype.file/image');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.file/image');
    }

    public static function get_categories() {
        return array('shortcut' => 2000);
    }

    public static function render_instance(BlockInstance $instance, $editing=false, $versioning=false) {
        $configdata = $instance->get('configdata'); // this will make sure to unserialize it for us

        if (!isset($configdata['artefactid'])) {
            return '';
        }

        $id = $configdata['artefactid'];
        $image = $instance->get_artefact_instance($id);
        $wwwroot = get_config('wwwroot');
        $viewid = $instance->get('view');
        $edittime = '&time=' . time();
        if ($image instanceof ArtefactTypeProfileIcon) {
            $src = $wwwroot . 'thumb.php?type=profileiconbyid&id=' . $id . '&view=' . $viewid . $edittime;
            $description = $image->get('title');
        }
        else {
            $src = $wwwroot . 'artefact/file/download.php?file=' . $id . '&view=' . $viewid . $edittime;
            $description = $image->get('description');
            $description = empty($description) ? $image->get('title') : $description;
        }

        if (!empty($configdata['width'])) {
            $src .= '&maxwidth=' . $configdata['width'];
        }

        require_once(get_config('docroot') . 'artefact/comment/lib.php');
        require_once(get_config('docroot') . 'lib/view.php');
        $view = new View($viewid);
        list($commentcount, $comments) = ArtefactTypeComment::get_artefact_comments_for_view($image, $view, $instance->get('id'), true, $editing, $versioning);
        $smarty = smarty_core();
        $smarty->assign('commentcount', $commentcount);
        $smarty->assign('comments', $comments);
        $smarty->assign('url', $wwwroot . 'artefact/artefact.php?artefact=' . $id . '&view=' . $viewid);
        $smarty->assign('src', $src);
        $smarty->assign('description', $description);
        $smarty->assign('showdescription', !empty($configdata['showdescription']) && !empty($description));
        return $smarty->fetch('blocktype:image:image.tpl');
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form(BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        safe_require('artefact', 'file');
        $instance->set('artefactplugin', 'file');
        $filebrowser = self::filebrowser_element($instance, (isset($configdata['artefactid'])) ? array($configdata['artefactid']) : null);

        return array(
            'artefactfieldset' => array(
                'type'         => 'fieldset',
                'collapsible'  => true,
                'collapsed'    => true,
                'legend'       => get_string('image'),
                'class'        => 'last select-file with-formgroup',
                'elements'     => array(
                    'artefactid' => $filebrowser
                )
            ),
            'showdescription' => array(
                'type'  => 'switchbox',
                'title' => get_string('showdescription', 'blocktype.file/image'),
                'defaultvalue' => !empty($configdata['showdescription']) ? true : false,
            ),
            'width' => array(
                'type' => 'text',
                'title' => get_string('width', 'blocktype.file/image'),
                'size' => 3,
                'description' => get_string('widthdescription1', 'blocktype.file/image'),
                'rules' => array(
                    'minvalue' => 16,
                    'maxvalue' => get_config('imagemaxwidth'),
                ),
                'defaultvalue' => (isset($configdata['width'])) ? $configdata['width'] : '',
            ),
        );
    }

    public static function instance_config_save($values, $instance) {
        if (isset($values['artefactid'])) {
            // Pass back a list of any other blocks that need to be rendered
            // due to this change.
            $values['_redrawblocks'] = array_unique(get_column(
                                                               'view_artefact', 'block',
                                                               'artefact', $values['artefactid'],
                                                               'view', $instance->get('view')
                                                               ));
        }
        return $values;
   }

    public static function filebrowser_element(&$instance, $default=array()) {
        $element = ArtefactTypeFileBase::blockconfig_filebrowser_element($instance, $default);
        $element['title'] = get_string('image');
        $element['name'] = 'artefactid';
        $element['accept'] = 'image/*';
        $element['config']['selectone'] = true;
        $element['config']['selectmodal'] = true;
        $element['filters'] = array(
            'artefacttype'    => array('image', 'profileicon'),
        );
        return $element;
    }

    public static function artefactchooser_element($default=null) {
        return array(
            'name'  => 'artefactid',
            'type'  => 'artefactchooser',
            'title' => get_string('image'),
            'defaultvalue' => $default,
            'blocktype' => 'image',
            'limit' => 10,
            'selectmodal' => true,
            'artefacttypes' => array('image', 'profileicon'),
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

    public static function default_copy_type() {
        return 'full';
    }
}
