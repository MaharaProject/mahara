<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-pdf
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypePdf extends MaharaCoreBlocktype {

    public static function single_only() {
        return false;
    }

    public static function single_artefact_per_block() {
        return true;
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

    public static function get_viewtypes() {
        return array('dashboard', 'portfolio', 'profile', 'grouphomepage');
    }

    public static function render_instance_export(BlockInstance $instance, $editing=false, $versioning=false, $exporting=null) {
        if ($exporting != 'pdf' && $exporting != 'htmllite' && $exporting != 'pdflite') {
            return self::render_instance($instance, $editing, $versioning);
        }
        else {
            $filedir = 'export_info/';
            if ($exporting == 'pdf') {
                $filedir .= 'files/';
            }
        }
        // The exporting for PDF
        require_once(get_config('docroot') . 'lib/view.php');
        $configdata = $instance->get('configdata'); // this will make sure to unserialize it for us
        $configdata['viewid'] = $instance->get('view');
        $view = new View($configdata['viewid']);
        $artefactid = isset($configdata['artefactid']) ? $configdata['artefactid'] : null;
        $html = '';
        if ($artefactid) {
            $artefact = $instance->get_artefact_instance($configdata['artefactid']);
            if (!file_exists($artefact->get_path())) {
                return '';
            }
            $urlbase = get_config('wwwroot');
            $url = $urlbase . 'artefact/file/download.php?file=' . $artefactid . '&view=' . $view->get('id');
            $description = $artefact->get('description');
            if ($description) {
                $html .= '<div class="card-body">' . $description . '</div>';
            }
            $html .= '<div class="text-midtone text-small">' . get_string('notrendertopdf', 'artefact.file');
            $html .= '<br>' . get_string('notrendertopdffiles', 'artefact.file', 1);
            // We need to add an <a> link so that the HTML export() sub-task makes a copy of the artefct for the export 'files/' directory
            // We then override the link in the PDF pdf_view_export_data() function.
            $html .= '<a href="' . $url . '">' . $filedir . $artefact->get('id') . '_' . $artefact->get('title') . '</a>';
            $html .= '</div>';
        }
        return $html;
    }

    public static function render_instance(BlockInstance $instance, $editing=false, $versioning=false) {
        global $USER;
        require_once(get_config('docroot') . 'lib/view.php');
        $configdata = $instance->get('configdata'); // this will make sure to unserialize it for us
        $configdata['viewid'] = $instance->get('view');
        $view = new View($configdata['viewid']);
        $group = $view->get('group');

        $blockid = ($instance->get('id'));

        $result = '';
        $commentcount = 0;
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
                    // In case we fail to find a language of 5 chars, eg pt_BR (Portuguese, Brazil) we try the 'parent' pt (Portuguese)
                    $language = substr($language, 0, 2);
                    if ($language != 'en' && !file_exists(get_config('docroot') . 'artefact/file/blocktype/pdf/js/pdfjs/web/locale/' . $language . '/viewer.properties')) {
                        $language = 'en-GB';
                    }
                }
            }
            else if ($language == 'en') {
                $language = 'en-GB';
            }

            $viewid = $instance->get('view');
            $file = artefact_instance_from_id($artefactid);
            if (!($file instanceof ArtefactTypeFile)) {
                throw new NotFoundException();
            }
            $url = $urlbase . 'artefact/file/download.php?file=' . $artefactid . '&view=' . $viewid . '&title=' . urlencode($file->get('title'));
            $src = $urlbase . 'artefact/file/blocktype/pdf/viewer.php?';
            $src .= 'editing=' . $editing;
            $src .= '&ingroup=' . !empty($group);
            $src .= '&artefactid=' . $artefactid;
            $src .= '&lang=' . $language;
            $src .= '&view=' . $viewid;
            $src .= ($versioning ? '&versioning=true' : '');

            $result = '<iframe allow="fullscreen" src="' . $src .'" class="pdfiframe"></iframe>';

            require_once(get_config('docroot') . 'artefact/comment/lib.php');
            require_once(get_config('docroot') . 'lib/view.php');
            $view = new View($configdata['viewid']);
            list($commentcount, $comments) = ArtefactTypeComment::get_artefact_comments_for_view($artefact, $view, $instance->get('id'), true, $editing, $versioning);

        }
        $smarty = smarty_core();
        if ($artefactid) {
            $smarty->assign('artefactid', $artefactid);
            $artefact = $instance->get_artefact_instance($configdata['artefactid']);
            $smarty->assign('allowcomments', $artefact->get('allowcomments'));
            if ($commentcount) {
                $smarty->assign('commentcount', $commentcount);
            }
        }
        $smarty->assign('html', $result);
        $smarty->assign('blockid', $blockid);
        $smarty->assign('editing', $editing);
        $smarty->assign('blockid', $instance->get('id'));
        return $smarty->fetch('blocktype:pdf:pdfrender.tpl');
    }

    public static function has_instance_config(BlockInstance $instance) {
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
                'legend'       => get_string('File', 'artefact.file'),
                'class'        => 'last select-file with-formgroup',
                'elements'     => array(
                    'pdfwarning' => array(
                        'type' => 'html',
                        'value' => get_string('pdfwarning', 'blocktype.file/pdf'),
                        'class' => 'alert alert-info',
                    ),
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
        $element['title'] = get_string('File', 'artefact.file');
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
            'title' => get_string('File', 'artefact.file'),
            'defaultvalue' => $default,
            'blocktype' => 'html',
            'limit' => 10,
            'artefacttypes' => array('file'),
            'template' => 'artefact:file:artefactchooser-element.tpl',
        );
    }

    public static function default_copy_type(BlockInstance $instance, View $view) {
        return 'full';
    }

    public static function postinst($prevversion) {
        if ($prevversion < 2022051000) {
            // set the blocktype to have quickedit mode
            set_field('blocktype_installed', 'quickedit', 1, 'name', 'pdf');
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
