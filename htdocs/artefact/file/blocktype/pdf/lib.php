<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-pdf
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypePdf extends MaharaCoreBlocktype {

    public static function single_only() {
        return false;
    }

    public static function get_title() {
        return get_string('title', 'blocktype.file/pdf');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.file/pdf');
    }

    public static function get_categories() {
        return array('fileimagevideo' => 8000);
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        global $USER;
        require_once(get_config('docroot') . 'lib/view.php');
        $configdata = $instance->get('configdata'); // this will make sure to unserialize it for us
        $configdata['viewid'] = $instance->get('view');
        $view = new View($configdata['viewid']);
        $group = $view->get('group');

        $result = '';
        $artefactid = isset($configdata['artefactid']) ? $configdata['artefactid'] : null;
        if ($artefactid) {
            $artefact = $instance->get_artefact_instance($configdata['artefactid']);

            if (!file_exists($artefact->get_path())) {
                return '';
            }

            $urlbase = get_config('wwwroot');
            // edit view doesn't use subdomains, neither do groups
            if (get_config('cleanurls') && get_config('cleanurlusersubdomains') && !$editing && empty($group)) {
                $viewauthor = new User();
                $viewauthor->find_by_id($view->get('owner'));
                $viewauthorurlid = $viewauthor->get('urlid');
                if ($urlallowed = !is_null($viewauthorurlid) && strlen($viewauthorurlid)) {
                    $urlbase = profile_url($viewauthor) . '/';
                }
            }
            // Send the current language to the pdf viewer
            $language = current_language();
            $language = str_replace('_', '-', substr($language, 0, ((substr_count($language, '_') > 0) ? 5 : 2)));
            if ($language != 'en' && !file_exists(get_config('docroot') . 'artefact/file/blocktype/pdf/js/pdfjs/web/locale/' . $language . '/viewer.properties')) {
                // In case the language file exists as a string with both lower and upper case, eg fr_FR we test for this
                $language = substr($language, 0, 2) . '-' . strtoupper(substr($language, 0, 2));
                if (!file_exists(get_config('docroot') . 'artefact/file/blocktype/pdf/js/pdfjs/web/locale/' . $language . '/viewer.properties')) {
                    // In case we fail to find a language of 5 chars, eg pt_BR (Portugese, Brazil) we try the 'parent' pt (Portugese)
                    $language = substr($language, 0, 2);
                    if ($language != 'en' && !file_exists(get_config('docroot') . 'artefact/file/blocktype/pdf/js/pdfjs/web/locale/' . $language . '/viewer.properties')) {
                        $language = 'en-GB';
                    }
                }
            }
            $result = '<iframe src="' . $urlbase . 'artefact/file/blocktype/pdf/viewer.php?editing=' . $editing . '&ingroup=' . !empty($group) . '&file=' . $artefactid . '&lang=' . $language . '&view=' . $instance->get('view')
                 . '" width="100%" height="500" frameborder="0"></iframe>';

            require_once(get_config('docroot') . 'artefact/comment/lib.php');
            require_once(get_config('docroot') . 'lib/view.php');
            $view = new View($configdata['viewid']);
            list($commentcount, $comments) = ArtefactTypeComment::get_artefact_comments_for_view($artefact, $view, $instance->get('id'), true, $editing);
        }
        $smarty = smarty_core();
        if ($artefactid) {
            $smarty->assign('commentcount', $commentcount);
            $smarty->assign('comments', $comments);
        }
        $smarty->assign('html', $result);
        return $smarty->fetch('blocktype:pdf:pdfrender.tpl');
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
                'legend'       => get_string('file', 'artefact.file'),
                'class'        => 'last select-file with-formgroup',
                'elements'     => array(
                    'artefactid' => $filebrowser
                )
            ),
        );
    }

    private static function get_allowed_mimetypes() {
        static $mimetypes = array();
        if (!$mimetypes) {
            $mimetypes = get_column('artefact_file_mime_types', 'mimetype', 'description', 'pdf');
        }
        return $mimetypes;
    }

    public static function filebrowser_element(&$instance, $default=array()) {
        $element = ArtefactTypeFileBase::blockconfig_filebrowser_element($instance, $default);
        $element['title'] = get_string('file', 'artefact.file');
        $element['name'] = 'artefactid';
        $element['accept'] = 'application/pdf';
        $element['config']['selectone'] = true;
        $element['config']['selectmodal'] = true;
        $element['filters'] = array(
            'artefacttype'    => array('file'),
            'filetype'        => self::get_allowed_mimetypes(),
        );
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
