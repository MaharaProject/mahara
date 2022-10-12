<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-gallery
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @author     Gregor Anzelj (External Galleries, e.g. Flickr, Picasa)
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) 2011 Gregor Anzelj <gregor.anzelj@gmail.com>
 *
 */

defined('INTERNAL') || die();

/**
 * Plugin for Gallery blocktype
 */
class PluginBlocktypeGallery extends MaharaCoreBlocktype {

    /**
     * {@inheritDoc}
     */
    public static function get_title() {
        return get_string('title', 'blocktype.file/gallery');
    }

    /**
     * single_artefact_per_block
     *
     * When the Image Gallery is displayed from a folder it will have a single
     * artefact and warrant a details block header. No header will display if
     * individual images (with multiple artefacts) were selected instead.
     *
     * @return boolean
     */
    public static function single_artefact_per_block() {
        return true;
    }

    public static function get_description() {
        return get_string('description1', 'blocktype.file/gallery');
    }

    public static function get_categories() {
        return array('fileimagevideo' => 5000);
    }

    public static function get_viewtypes() {
        return array('dashboard', 'portfolio', 'profile', 'grouphomepage');
    }

    public static function get_instance_javascript(BlockInstance $instance) {
        $blockid = $instance->get('id');
        // The initjs for the masonry will be applied to all galleries on the page
        return array(
            array(
                'file'   => get_config('wwwroot') . 'js/masonry/masonry.min.js',
                'initjs' => "$(function() {
                    $('.js-masonry.thumbnails').masonry({ itemSelector: '.thumb' });
                });"
            ),
            array(
                'file' => '',
                'initjs' => "$(function() {
                    $('#slideshow{$blockid}').on('slid.bs.carousel', function () {
                        $(window).trigger('colresize');
                    });
                });"
            )
        );
    }

    public static function get_instance_config_javascript(BlockInstance $instance) {
        return array(
            'js/configform.js',
        );
    }

    public static function render_instance_export(BlockInstance $instance, $editing=false, $versioning=false, $exporting=null) {
        if ($exporting != 'pdf') {
            return self::render_instance($instance, $editing, $versioning);
        }
        // The exporting for pdf
        $configdata = $instance->get('configdata'); // this will make sure to unserialize it for us
        $viewid = $instance->get('view');
        $style = isset($configdata['style']) ? intval($configdata['style']) : 2;

        $artefactids = array();
        if ($style !== 1) {
            // If not the slideshow then render in normal way
            return self::render_instance($instance, $editing, $versioning);
        }
        else {
            safe_require('artefact', 'file');
            // Slideshow piles all the images on top of each other in PDF so we need to avoid this
            if (isset($configdata['select']) && $configdata['select'] == 1 && is_array($configdata['artefactids'])) {
                $artefactids = $configdata['artefactids'];
            }
            else if ($versioning && !empty($configdata['existing_artefacts'])) {
                $artefactids = (array) $configdata['existing_artefacts'];
            }
            else if (!empty($configdata['artefactid'])) {
                // Get descendents of this folder.
                $artefactids = artefact_get_descendants(array(intval($configdata['artefactid'])));
            }

            $artefactids = $instance->order_artefacts_by_title($artefactids);
            $html = '';

            if ($artefactids) {
                $firstdone = false;
                foreach ($artefactids as $artefactid) {
                    $artefact = $instance->get_artefact_instance($artefactid);
                    if ($artefact->get('artefacttype') == 'folder') {
                        continue;
                    }
                    if (!file_exists($artefact->get_path())) {
                        continue;
                    }
                    $urlbase = get_config('wwwroot');
                    $url = $urlbase . 'artefact/file/download.php?file=' . $artefactid . '&view=' . $viewid;
                    $description = $artefact->get('description');
                    if (!$firstdone) {
                        $html .= '<div class="image"><img src="' . $url . '" alt="' . $artefact->get('title') . '"></div>';
                        if ($description) {
                            $html .= '<div class="card-body">' . $description . '</div>';
                        }
                        $html .= '<div class="text-midtone text-small">' . get_string('notrendertopdf', 'artefact.file');
                        $html .= '<br>' . get_string('notrendertopdffiles', 'artefact.file', count($artefactids));
                        $firstdone = true;
                    }
                    $html .= '<br><a href="' . $url . '">' . $artefact->get('title') . '</a>';
                }
                $html .= '</div>';
            }
            return $html;
        }
    }

    public static function render_instance(BlockInstance $instance, $editing=false, $versioning=false) {
        $configdata = $instance->get('configdata'); // this will make sure to unserialize it for us
        $configdata['viewid'] = $instance->get('view');
        $style = isset($configdata['style']) ? intval($configdata['style']) : 0;
        $showdescription = isset($configdata['showdescription']) ? intval($configdata['showdescription']) : 0;
        $copyright = null;
        $width = 75;
        $width = floor($width);
        switch ($style) {
            case 1: // slideshow
                $template = 'slideshow';
                $width = 400;
                break;
            case 0:
            case 2:
            default:
                $template = 'squarethumbs';
        }

        $images = array();
        $fancyboxattr = get_config_plugin('blocktype', 'gallery', 'usefancybox') ? 'lightbox_' . $instance->get('id') : null;

        safe_require('artefact', 'file');
        $artefactids = array();
        if (isset($configdata['select']) && $configdata['select'] == 1 && is_array($configdata['artefactids'])) {
            $artefactids = $configdata['artefactids'];
        }
        else if ($versioning && !empty($configdata['existing_artefacts'])) {
            $artefactids = (array) $configdata['existing_artefacts'];
        }
        else if (!empty($configdata['artefactid'])) {
            // Get descendents of this folder.
            $artefactids = artefact_get_descendants(array(intval($configdata['artefactid'])));
        }

        $artefactids = $instance->order_artefacts_by_title($artefactids);

        // This can be either an image or profileicon. They both implement
        // render_self
        foreach ($artefactids as $artefactid) {
            $image = $instance->get_artefact_instance($artefactid);

            if ($image instanceof ArtefactTypeProfileIcon) {
                $src = get_config('wwwroot') . 'thumb.php?type=profileiconbyid&id=' . $artefactid;
                $src .= '&view=' . $instance->get('view');
                $description = $image->get('title');
            }
            else if ($image instanceof ArtefactTypeImage) {
                $src = get_config('wwwroot') . 'artefact/file/download.php?file=' . $artefactid;
                $src .= '&view=' . $instance->get('view');
                $description = $image->get('description');
                $alttext = $image->get('alttext');
                $altiscaption = $image->get('altiscaption');
                $isdecorative = $image->get('isdecorative');
            }
            else {
                continue;
            }

            if ($fancyboxattr) {
                $link = $src . '&maxwidth=' . get_config_plugin('blocktype', 'gallery', 'previewwidth');
            }
            else {
                $link = get_config('wwwroot') . 'view/view.php?id=' . $instance->get('view') . '&modal=1&block=' . $instance->get('id') .'&artefact=' . $artefactid;
            }

            $images[] = array(
                'id' => $image->get('id'),
                'link' => $link,
                'source' => $src,
                'height' => null,
                'width' => null,
                'title' => $image->get('title'),
                'description' => $showdescription ? $description : '',
                'fancybox' => $fancyboxattr,
                'squaredimensions' => $width,
                'alttext' => $alttext,
                'altiscaption' => $altiscaption,
                'isdecorative' => $isdecorative,
                'bootstrapcaption' => $altiscaption ? $alttext : $description
            );
        }

        $smarty = smarty_core();
        $smarty->assign('count', count($images));
        $smarty->assign('instanceid', $instance->get('id'));
        $smarty->assign('images', $images);
        $smarty->assign('showdescription', $showdescription);
        $smarty->assign('width', $width);
        $smarty->assign('editing', $editing);
        $smarty->assign('copyright', $copyright);
        if (!empty($configdata['artefactid'])) {
            $artefact = $instance->get_artefact_instance($configdata['artefactid']);

            require_once(get_config('docroot') . 'artefact/comment/lib.php');
            require_once(get_config('docroot') . 'lib/view.php');
            $view = new View($configdata['viewid']);
            list($commentcount, $comments) = ArtefactTypeComment::get_artefact_comments_for_view($artefact, $view, $instance->get('id'), true, $editing, $versioning);
        }
        return $smarty->fetch('blocktype:gallery:' . $template . '.tpl');
    }

    public static function has_config() {
        return true;
    }

    public static function get_config_options() {
        $elements = array();
        $elements['gallerysettings'] = array(
            'type' => 'fieldset',
            'legend' => get_string('gallerysettings', 'blocktype.file/gallery'),
            'collapsible' => true,
            'elements' => array(
                'usefancybox' => array(
                    'type'         => 'switchbox',
                    'title'        => get_string('usefancybox1', 'blocktype.file/gallery'),
                    'description'  => get_string('usefancyboxdesc1', 'blocktype.file/gallery'),
                    'defaultvalue' => get_config_plugin('blocktype', 'gallery', 'usefancybox'),
                ),
                'previewwidth' => array(
                    'type'         => 'text',
                    'size'         => 4,
                    'title'        => get_string('previewwidth', 'blocktype.file/gallery'),
                    'description'  => get_string('previewwidthdesc1', 'blocktype.file/gallery'),
                    'defaultvalue' => get_config_plugin('blocktype', 'gallery', 'previewwidth'),
                    'rules'        => array('integer' => true, 'minvalue' => 16, 'maxvalue' => 1600),
                )
            ),
        );
        return array(
            'elements' => $elements,
            // Don't apply "panel panel-body" style to this form.
            'class' => null,
        );

    }

    public static function save_config_options(Pieform $form, $values) {
        set_config_plugin('blocktype', 'gallery', 'usefancybox', (int)$values['usefancybox']);
        set_config_plugin('blocktype', 'gallery', 'previewwidth', (int)$values['previewwidth']);
    }

    public static function postinst($prevversion) {
        if ($prevversion == 0) {
            $setfancybox = set_config_plugin('blocktype', 'gallery', 'usefancybox', 1); // Use Fancybox 3 by default
            $setpreviewwidth = set_config_plugin('blocktype', 'gallery', 'previewwidth', 1024); // Maximum photo width for fancybox preview
            return $setfancybox && $setpreviewwidth;
        }
        return true;
    }

    public static function has_instance_config(BlockInstance $instance) {
        return true;
    }

    public static function instance_config_form(BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        safe_require('artefact', 'file');
        $instance->set('artefactplugin', 'file');
        $user = $instance->get('view_obj')->get('owner');
        $select_options = array(
            0 => get_string('selectfolder', 'blocktype.file/gallery'),
            1 => get_string('selectimages', 'blocktype.file/gallery')
        );
        $style_options = array(
            0 => get_string('stylesquares', 'blocktype.file/gallery'),
            1 => get_string('styleslideshow', 'blocktype.file/gallery'),
        );
        if (isset($configdata['select']) && $configdata['select'] == 0) {
            $imageselector = self::imageselector($instance, null, 'd-none');
            if (!empty($configdata['artefactid'])) {
                $folderid = !empty($configdata['artefactid']) ? array(intval($configdata['artefactid'])) : null;
                $folderselector = self::folderselector($instance, $folderid);
            }
            else {
                $folderselector = self::folderselector($instance, null, 'd-none');
            }
        }
        else if (isset($configdata['select']) && $configdata['select'] == 1) {
            $imageids = isset($configdata['artefactids']) ? $configdata['artefactids'] : array();
            $imageselector = self::imageselector($instance, $imageids);
            $folderselector = self::folderselector($instance, null, 'd-none');
        }
        else {
            $imageselector = self::imageselector($instance, null, 'd-none');
            $folderid = !empty($configdata['artefactid']) ? array(intval($configdata['artefactid'])) : null;
            $folderselector = self::folderselector($instance, $folderid);
        }
        return array(
            'user' => array(
                'type' => 'hidden',
                'value' => $user,
            ),
            'select' => array(
                'type' => 'radio',
                'title' => get_string('select', 'blocktype.file/gallery'),
                'options' => $select_options,
                'defaultvalue' => (isset($configdata['select'])) ? $configdata['select'] : 0,
            ),
            'images' => $imageselector,
            'folder' => $folderselector,
            'style' => array(
                'type' => 'radio',
                'title' => get_string('style', 'blocktype.file/gallery'),
                'options' => $style_options,
                'defaultvalue' => (isset($configdata['style'])) && $configdata['style'] <= 1 ? $configdata['style'] : 0, // Square thumbnails should be default...
            ),
            'showdescription' => array(
                'type'  => 'switchbox',
                'title' => get_string('showdescriptions1', 'blocktype.file/gallery'),
                'description' => get_string('showdescriptionsdescription', 'blocktype.file/gallery'),
                'defaultvalue' => !empty($configdata['showdescription']) ? true : false,
            )
        );
    }

    public static function instance_config_validate(Pieform $form, $values) {
        global $USER;

        if (!empty($values['images'])) {
            foreach ($values['images'] as $id) {
                $image = artefact_instance_from_id($id);
                if (!($image instanceof ArtefactTypeImage) || !$USER->can_view_artefact($image)) {
                    $result['message'] = get_string('unrecoverableerror', 'error');
                    $form->set_error(null, $result['message']);
                    $form->reply(PIEFORM_ERR, $result);
                }
            }
        }

        if (!empty($values['folder'])) {
            $folder = artefact_instance_from_id($values['folder']);
            if (!($folder instanceof ArtefactTypeFolder) || !$USER->can_view_artefact($folder)) {
                $result['message'] = get_string('unrecoverableerror', 'error');
                $form->set_error(null, $result['message']);
                $form->reply(PIEFORM_ERR, $result);
            }
        }
    }

    public static function instance_config_save($values) {
        if ($values['select'] == 0) {
            $values['artefactid'] = $values['folder'];
            unset($values['artefactids']);
        }
        else if ($values['select'] == 1) {
            $values['artefactids'] = $values['images'];
            unset($values['artefactid']);
        }
        unset($values['folder']);
        unset($values['images']);
        switch ($values['style']) {
            case 0: // square thumbnails
                $values['width'] = 75;
                break;
            case 1: // slideshow
                $values['width'] = 400;
                break;
        }
        return $values;
    }

    public static function imageselector(&$instance, $default=array(), $class=null) {
        $element = ArtefactTypeFileBase::blockconfig_filebrowser_element($instance, $default);
        $element['title'] = get_string('Images', 'artefact.file');
        $element['name'] = 'images';
        $element['accept'] = 'image/*';
        if ($class) {
            $element['class'] = $class;
        }
        $element['config']['selectone'] = false;
        $element['config']['selectmodal'] = true;
        $element['filters'] = array(
            'artefacttype'    => array('image', 'profileicon'),
        );
        return $element;
    }

    public static function folderselector(&$instance, $default=array(), $class=null) {
        $element = ArtefactTypeFileBase::blockconfig_filebrowser_element($instance, $default);
        $element['title'] = get_string('folder', 'artefact.file');
        $element['name'] = 'folder';
        if ($class) {
            $element['class'] = $class;
        }
        $element['config']['upload'] = false;
        $element['config']['selectone'] = true;
        $element['config']['selectfolders'] = true;
        $element['config']['selectmodal'] = true;
        $element['filters'] = array(
            'artefacttype'    => array('folder'),
        );
        return $element;
    }

    public static function artefactchooser_element($default=null) {
    }

    public static function default_copy_type(BlockInstance $instance, View $view) {
        return 'full';
    }

    public static function get_current_artefacts(BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        $artefacts = array();
        if (isset($configdata['artefactid'])) {
            safe_require('artefact', 'file');
            $artefacts = artefact_get_descendants(array(intval($configdata['artefactid'])));
        }
        return $artefacts;
    }

    public static function shows_details_in_modal(BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        return isset($configdata['artefactids']);
    }

    public static function render_details_in_modal(BlockInstance $instance) {
        $artefacts = $instance->get('configdata')['artefactids'];
        $smarty = smarty_core();

        if ($artefacts) {
            safe_require('artefact', 'comment');
            $childrecords = array();
            foreach ($artefacts as $a) {
                $c = artefact_instance_from_id($a);
                $child = new StdClass();
                $child->id = $a;
                $child->description = $c->get('description');
                $child->size = $c->describe_size();
                $child->title = $child->hovertitle = $c->get('title');
                $child->artefacttype = $c->get('artefacttype');
                $child->iconsrc = call_static_method(generate_artefact_class_name($c->get('artefacttype')), 'get_icon', array('id' => $a, 'viewid' => $instance->get('view')));
                $count = ArtefactTypeComment::count_comments(null, array($child->id));
                if ($count) {
                    $child->commentcount = $count[$child->id]->comments;
                }
                else {
                    $child->commentcount = 0;
                }
                $childrecords[] = $child;
            }
            $smarty->assign('children', $childrecords);
        }
        $smarty->assign('blockid', $instance->get('id'));
        $smarty->assign('modal', true);
        $smarty->assign('viewid', $instance->get('view'));

        $template = 'artefact:file:folder_render_in_modal.tpl';

        return array(
            'html' => $smarty->fetch($template),
            'artefactids' =>  $instance->get('configdata')['artefactids'],
            'javascript'=>'',
        );
    }
}
