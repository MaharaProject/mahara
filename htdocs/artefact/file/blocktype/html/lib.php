<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-html
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeHtml extends MaharaCoreBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.file/html');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.file/html');
    }

    public static function get_categories() {
        return array('fileimagevideo' => 9000);
    }

    public static function render_instance(BlockInstance $instance, $editing=false, $versioning=false) {
        $configdata = $instance->get('configdata'); // this will make sure to unserialize it for us
        $configdata['viewid'] = $instance->get('view');

        $result = '';
        $artefactid = isset($configdata['artefactid']) ? $configdata['artefactid'] : null;
        if ($artefactid) {
            $artefact = $instance->get_artefact_instance($artefactid);

            if (!file_exists($artefact->get_path())) {
                return;
            }

            $result = clean_html(file_get_contents($artefact->get_path()));

            require_once(get_config('docroot') . 'artefact/comment/lib.php');
            require_once(get_config('docroot') . 'lib/view.php');
            $view = new View($configdata['viewid']);
            list($commentcount, $comments) = ArtefactTypeComment::get_artefact_comments_for_view($artefact, $view, $instance->get('id'), true, $editing, $versioning);
         }

        $smarty = smarty_core();
        if ($artefactid) {
            $smarty->assign('commentcount', $commentcount);
            $smarty->assign('comments', $comments);
        }
        $smarty->assign('html', $result);
        return $smarty->fetch('blocktype:html:html.tpl');
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
                'legend'       => get_string('file', 'artefact.file'),
                'class'        => 'last select-file with-formgroup',
                'elements'     => array(
                    'artefactid' => self::filebrowser_element($instance, (isset($configdata['artefactid'])) ? array($configdata['artefactid']) : null),
                )
            ),
        );
    }

    private static function get_allowed_mimetypes() {
        static $mimetypes = array();
        if (!$mimetypes) {
            $mimetypes = get_column('artefact_file_mime_types', 'mimetype', 'description', 'html');
        }
        return $mimetypes;
    }

    public static function filebrowser_element(&$instance, $default=array()) {
        $element = ArtefactTypeFileBase::blockconfig_filebrowser_element($instance, $default);
        $element['title'] = get_string('file', 'artefact.file');
        $element['name'] = 'artefactid';
        $element['config']['selectone'] = true;
        $element['config']['selectmodal'] = true;
        $element['filters'] = array(
            'artefacttype'    => array('file'),
            'filetype'        => self::get_allowed_mimetypes(),
        );
        $element['accept'] = implode(',', self::get_allowed_mimetypes());
        return $element;
    }

    public static function artefactchooser_element($default=null) {
        return array(
            'name'  => 'artefactid',
            'type'  => 'artefactchooser',
            'title' => get_string('file', 'artefact.file'),
            'defaultvalue' => $default,
            'blocktype' => 'html',
            'limit' => 10,
            'artefacttypes' => array('file'),
            'template' => 'artefact:file:artefactchooser-element.tpl',
        );
    }

    public static function default_copy_type() {
        return 'full';
    }

}
