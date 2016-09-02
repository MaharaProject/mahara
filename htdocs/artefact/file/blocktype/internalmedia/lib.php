<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-internalmedia
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

class PluginBlocktypeInternalmedia extends MaharaCoreBlocktype {

    /**
     * @DEPRECATED: Default width & height. Not currently used, because we
     * use CSS to make all videos scale to fill the width of their container
     * while maintaining their aspect ratio. Kept here so we don't have to
     * tear out all the width/height display code.
     */
    const DEFAULT_WIDTH = 900;
    const DEFAULT_HEIGHT = 600;

    public static function get_title() {
        return get_string('title', 'blocktype.file/internalmedia');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.file/internalmedia');
    }

    public static function get_categories() {
        return array('fileimagevideo' => 7000);
    }

    public static function has_config() {
        return true;
    }

    public static function postinst($oldversion) {
        if ($oldversion == 0) {
            set_config_plugin('blocktype', 'internalmedia', 'enabledtypes', serialize(array('flv', 'mp3', 'mp4')));
        }
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        list($artefact, $width, $height) = self::get_mediaplayer_details($instance);
        if (!$artefact) {
            return '';
        }

        $playerclass = self::get_player_class_for_artefact($artefact);
        if (!$playerclass) {
            return get_string('typeremoved', 'blocktype.file/internalmedia');
        }

        $result = '<div class="mediaplayer-container panel-body flush"><div class="mediaplayer">';
        $result .= call_static_method($playerclass, 'get_html', $artefact, $instance, $width, $height);

        // File download link
        $filesize = round($artefact->get('size') / 1000000, 2) . 'MB';
        $url = self::get_download_link($artefact, $instance);
        $result .= '<div class="media-download content-text">
            <span class="icon icon-download left" role="presentation" aria-hidden="true">
            </span><span class="sr-only">' . get_string('Download', 'artefact.internal') . '</span>
            <a class="media-link text-small" href="' . $url . '">' . hsc($artefact->get('title')) . '</a>
            <span class="text-midtone text-small"> [' . $filesize . '] </span>
        </div>';

        $result .= '</div></div>';

        require_once(get_config('docroot') . 'artefact/comment/lib.php');
        require_once(get_config('docroot') . 'lib/view.php');
        $view = new View($instance->get('view'));
        list($commentcount, $comments) = ArtefactTypeComment::get_artefact_comments_for_view($artefact, $view, $instance->get('id'), true, $editing);

        $smarty = smarty_core();

        $smarty->assign('commentcount', $commentcount);
        $smarty->assign('comments', $comments);
        $smarty->assign('html', $result);
        return $smarty->fetch('blocktype:internalmedia:internalmedia.tpl');
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
                'legend'       => get_string('media', 'blocktype.file/internalmedia'),
                'class'        => 'last select-file with-formgroup',
                'elements'     => array(
                    'artefactid' => $filebrowser
                )
            ),
        );
    }

    public static function filebrowser_element(&$instance, $default=array()) {
        $element = ArtefactTypeFileBase::blockconfig_filebrowser_element($instance, $default);
        $element['title'] = get_string('media', 'blocktype.file/internalmedia');
        $element['name'] = 'artefactid';
        $element['config']['selectone'] = true;
        $element['config']['selectmodal'] = true;
        $element['filters'] = array(
            'artefacttype'    => array('file', 'audio', 'video'),
            'filetype'        => self::get_allowed_mimetypes(),
        );
        $element['accept'] = implode(',', self::get_allowed_mimetypes());
        return $element;
    }

    public static function artefactchooser_element($default=null) {
        return array(
            'name' => 'artefactid',
            'type'  => 'artefactchooser',
            'title' => get_string('media', 'blocktype.file/internalmedia'),
            'defaultvalue' => $default,
            'blocktype' => 'internalmedia',
            'limit' => 5,
            'selectone' => true,
            'artefacttypes' => array('file', 'audio', 'video'),
            'template' => 'artefact:file:artefactchooser-element.tpl',
        );
    }

    public static function artefactchooser_get_element_data($artefact) {
        $artefact->icon = call_static_method(generate_artefact_class_name($artefact->artefacttype), 'get_icon', array('id' => $artefact->id));
        return $artefact;
    }

    public static function save_config_options(Pieform $form, $values) {
        $enabledtypes = array();
        foreach ($values as $type => $enabled) {
            if (!in_array($type, self::get_all_filetypes())) {
                continue;
            }
            if (!empty($enabled)) {
                $enabledtypes[] = $type;
            }
        }
        set_config_plugin('blocktype', 'internalmedia', 'enabledtypes', serialize($enabledtypes));
    }

    public static function get_config_options() {
        $elements = array();
        // Allowed file types
        $filetypes = array();
        $currenttypes = self::get_allowed_filetypes();

        foreach (self::get_all_filetypes() as $filetype) {
            $filetypes[$filetype] = array(
                'type'  => 'switchbox',
                'title' => get_string($filetype, 'artefact.file'),
                'defaultvalue' => in_array($filetype, $currenttypes),
            );
        }
        uasort($filetypes, create_function('$a, $b', 'return $a["title"] > $b["title"];'));
        $options = array_merge(
            array(
                'description' => array(
                    'value' => get_string('configdesc1', 'blocktype.file/internalmedia'),
                ),
            ),
            $filetypes
        );

        return array(
            'elements' => $options,
        );
    }


    private static function get_allowed_filetypes() {
        if ($data = get_config_plugin('blocktype', 'internalmedia', 'enabledtypes')) {
            return unserialize($data);
        }
        return array();
    }


    private static function get_allowed_mimetypes() {
        return array_keys(self::get_allowed_mimetype_filetypes());
    }


    private static function get_allowed_mimetype_filetypes() {
        if ($data = self::get_allowed_filetypes()) {
            if ($mimetypes = get_records_sql_assoc('
                SELECT mimetype, description
                FROM {artefact_file_mime_types}
                WHERE description IN (' . join(',', array_map('db_quote', $data)) . ')', array())) {
                foreach ($mimetypes as &$m) {
                    $m = $m->description;
                }
                // Hack to allow .wmv and .wma files to also use the .asf mimetype as well
                // See http://en.wikipedia.org/wiki/Advanced_Systems_Format
                if (in_array('wmv', $data)) {
                    $mimetypes['video/x-ms-asf'] = 'wmv';
                }
                if (in_array('wma', $data)) {
                    $mimetypes['video/x-ms-asf'] = 'wma';
                }
                return $mimetypes;
            }
        }
        return array();
    }


    /**
     * Get the MaharaMediaPlayer class appropriate for this artefact.
     * Or boolean false if there is none.
     *
     * @param ArtefactType $artefact
     * @return string|false
     */
    private static function get_player_class_for_artefact($artefact) {
        $mimetype = $artefact->get('filetype');
        $mimetypefiletypes = self::get_allowed_mimetype_filetypes();
        if (!isset($mimetypefiletypes[$mimetype])) {
            return false;
        }
        else {
            $callbacks = self::get_all_filetype_players();
            $classname = 'MaharaMediaPlayer_' . $callbacks[$mimetypefiletypes[$mimetype]];
            if (class_exists($classname)) {
                return $classname;
            }
            else {
                return false;
            }
        }
    }

    private static function get_all_filetypes() {
        return array_keys(self::get_all_filetype_players());
    }


    private static function get_all_filetype_players() {
        /* Keyed by the file type descriptions from the artefact_file_mime_types table */
        return array(
            'mp3'       => 'html5audio',
            'swf'       => 'flash',
            'flv'       => 'html5video',
            'quicktime' => 'qt',
            'wmv'       => 'wmp',
            'mpeg'      => 'qt',
            'avi'       => 'wmp',
            'mp4'       => 'html5video',
            'oga'       => 'html5audio',
            'ogg'       => 'html5audio',
            'ogv'       => 'html5video',
            'webm'      => 'html5video',
            '3gp'       => 'html5video',
            /* commenting out for now
            'ram'   => 'real_player',
            'rm'    => 'real_player',
            'rpm'   => 'real_player',
            */
        );
    }

    public static function get_download_link(ArtefactTypeFile $artefact, BlockInstance $instance) {
        return get_config('wwwroot') . 'artefact/file/download.php?file='
            . $artefact->get('id') . '&view=' . $instance->get('view');
    }

    public static function get_instance_javascript(BlockInstance $instance) {
        list($artefact, $width, $height) = self::get_mediaplayer_details($instance);
        if (!$artefact) {
            return array();
        }

        $playerclass = self::get_player_class_for_artefact($artefact);
        if (!$playerclass) {
            return array();
        }

        $jsfile = call_static_method($playerclass, 'get_js_library');
        $jsblock = call_static_method($playerclass, 'get_js_initjs', $artefact, $instance, $width, $height);

        $js = array();
        if ($jsfile) {
            $js['file'] = $jsfile;
        }
        if ($jsblock) {
            $js['initjs'] = $jsblock;
        }
        if ($js) {
            return array($js);
        }
        else {
            return array();
        }
    }

    public static function default_copy_type() {
        return 'full';
    }

    /**
     * Fetches the details needed by the mediaplayer renderers
     *
     * @param BlockInstance $instance
     * @return mixed array(ArtefactType $artefact, width, height) or false if no artefact on this block
     */
    public static function get_mediaplayer_details(BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        $viewid = $instance->get('view');
        $artefactid = isset($configdata['artefactid']) ? $configdata['artefactid'] : null;

        // If there is no artefact, then return false
        if (empty($artefactid)) {
            return array(false, false, false);
        }

        require_once(get_config('docroot') . 'artefact/lib.php');
        $artefact = $instance->get_artefact_instance($artefactid);

        /**
         * @DEPRECATED: Not currently using configurable heights; instead height & width
         * are dynamically scaled using CSS. Keeping these as placeholders so we don't
         * have to tear out all the width & height display code.
         */
        $width  = self::DEFAULT_WIDTH;
        $height = self::DEFAULT_HEIGHT;

        return array($artefact, $width, $height);
    }
}


/**
 * Hierarchy of classes that hold the code for the different media players
 * we use. See BlocktypeInternalMedia::get_all_filetype_players() for how
 * we map mimetypes to media players.
 */
abstract class MaharaMediaPlayer {

    /**
     * Returns a unique identifier to use in the "id" attributes of the media player. Should be
     * deterministic so that the HTML function can print it, and the script can then find it.
     *
     * @param ArtefactType $artefact
     * @param BlockInstance $block
     */
    protected static function get_unique_id(ArtefactType $artefact, BlockInstance $block) {
        return $block->get('id') . '_' . $artefact->get('id');
    }

    /**
     * Returns the HTML to display the a mediaplayer of this type
     *
     * @param ArtefactType $artefact
     * @param BlockInstance $block
     * @param int $width @DEPRECATED: Not currently used
     * @param int $height @DEPRECATED: Not currently used
     * @return string
     */
    abstract public static function get_html(ArtefactType $artefact, BlockInstance $block, $width, $height);

    /**
     * Returns JS library used to display a mediaplayer of this type.
     * Because Mahara currently ties one init block to one Javascript library loading, this can
     * only currently support one file per player type.
     *
     * @return array
     */
    public static function get_js_library() { return false; }

    /**
     * Returns a JS snippet needed to initialize a mediaplayer of this type (or boolean false if none)
     *
     * @param ArtefactType $artefact
     * @param BlockInstance $block
     * @param int $width @DEPRECATED: Not currently used
     * @param int $height @DEPRECATED: Not currently used
     * @return string|false
     */
    public static function get_js_initjs(ArtefactType $artefact, BlockInstance $block, $width, $height) { return false; }
}


/**
 * Flash browser plugin (used only for playing actual Flash files, i.e. ".swf" files)
 */
class MaharaMediaPlayer_flash extends MaharaMediaPlayer {

    /**
     * Calculates some items shared by the JS and the HTML
     *
     * @param ArtefactType $artefact
     * @param BlockInstance $block
     * @return array
     */
    protected static function get_unique_id($artefact, $block) {
        return 'blocktype_internalmedia_flash_' . parent::get_unique_id($artefact, $block);
    }

    public static function get_html(ArtefactType $artefact, BlockInstance $block, $width, $height) {
        $id = self::get_unique_id($artefact, $block);
        $html =  '<span class="blocktype_internalmedia_mp3" id="' . $id . '">('
               . get_string('flashanimation', 'blocktype.file/internalmedia') . ')</span>';
        return $html;

    }

    public static function get_js_initjs(ArtefactType $artefact, BlockInstance $block, $width, $height) {
        $id = self::get_unique_id($artefact, $block);
        $url = PluginBlocktypeInternalmedia::get_download_link($artefact, $block);
        $swfheight = $height+20;

        return <<<JS
    var so = new SWFObject("{$url}&embedded=1","player","{$width}","{$swfheight}","7");
    so.addParam("allowfullscreen","false");
    so.addVariable("displayheight","{$height}");
    so.addVariable("type", "swf");
    so.addVariable("height", "{$height}");
    so.addVariable("width", "{$width}");
    so.addVariable("wmode", "transparent");
    so.addParam("play", "true");
    so.addParam("allowscriptaccess", "never");
    so.addParam("allownetworking", "never");
    so.write("{$id}");
JS;
    }

    public static function get_js_library() {
        return 'swfobject.js';
    }
}

// /**
//  * RealMedia browser plugin
//  */
// class MaharaMediaPlayer_real extends MaharaMediaPlayer {
//     public static function get_html($artefact, $block, $width, $height) {

//         $url = PluginBlocktypeInternalmedia::get_download_link($artefact, $block);

//         $mimetype = $artefact->get('filetype');
//         $autostart = 'false';

//         return '<span class="blocktype_internalmedia_real">
//     <object classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" width="240" height="180">
//         <param name="src" value="' . $url . '">
//         <param name="autostart" value="' . $autostart . '">
//         <param name="controls" value="imagewindow">
//         <param name="console" value="video">
//         <param name="loop" value="true">
//         <embed src="' . $url . '" width=240" height="180" loop="true" type="' . $mimetype . '" controls="imagewindow" console="video" autostart="' . $autostart . '">
//     </object><br>
//     <object classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" width="240" height="30">
//         <param name="src" value="' . $url . '">
//         <param name="autostart" value="' . $autostart . '">
//         <param name="controls" value="ControlPanel">
//         <param name="console" value="video">
//         <embed src="' . $url . '" width="240" height="30" controls="ControlPanel" type="' . $mimetype . '" console="video" autostart="' . $autostart . '">
//     </object>
// </span>';
//     }
// }

/**
 * Windows Media Player browser plugin
 */
class MaharaMediaPlayer_wmp extends MaharaMediaPlayer {
    public static function get_html(ArtefactType $artefact, BlockInstance $block, $width, $height) {

        $url = hsc(PluginBlocktypeInternalmedia::get_download_link($artefact, $block));

        $size = 'width="' . $width . '" height="' . $height . '"';
        $autosize = 'false';

        $mimetype = 'video/x-ms-wmv'; // hardcode this
        $autostart = 'false';

        return '<span class="mediaplugin mediaplugin_wmp">
    <object classid="CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6" ' . $size . '
      standby="Loading Microsoft(R) Windows(R) Media Player components..."
      type="application/x-oleobject">
     <param name="Filename" value="' . $url . '">' /* hsc($artefact->get('title')) . '"> */ . '
     <param name="src" value="' . $url . '">
     <param name="url" value="' . $url . '">
     <param name="ShowControls" value="true">
     <param name="AutoRewind" value="true">
     <param name="AutoStart" value="' . $autostart . '">
     <param name="Autosize" value="' . $autosize . '">
     <param name="EnableContextMenu" value="true">
     <param name="TransparentAtStart" value="false">
     <param name="AnimationAtStart" value="false">
     <param name="ShowGotoBar" value="false">
     <param name="EnableFullScreenControls" value="true">
     <param name="Wmode" value="opaque">
    <!--[if !IE]>-->
      <object data="' . $url . '" type="' . $mimetype . '" ' . $size . '>
       <param name="src" value="' . $url . '">
       <param name="url" value="' . $url . '">
       <param name="controller" value="true">
       <param name="autoplay" value="' . $autostart . '">
       <param name="autostart" value="' . $autostart . '">
       <param name="resize" value="scale">
       <param name="wmode" value="opaque">
      </object>
    <!--<![endif]-->
    </object></span>';
    }
}

/**
 * Quicktime browser plugin
 */
class MaharaMediaPlayer_qt extends MaharaMediaPlayer {
    public static function get_html(ArtefactType $artefact, BlockInstance $block, $width, $height) {

        $url = PluginBlocktypeInternalmedia::get_download_link($artefact, $block);

        $size = 'width="' . $width . '" height="' . $height . '"';

        require_once('file.php');
        $mimetype = $artefact->get('filetype');
        $autostart = 'false';

        return '<span class="mediaplugin mediaplugin_qt">
    <object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B"
      codebase="http://www.apple.com/qtactivex/qtplugin.cab" ' . $size . '>
     <param name="pluginspage" value="http://www.apple.com/quicktime/download/">
     <param name="src" value="' . $url . '">
     <param name="controller" value="true">
     <param name="loop" value="false">
     <param name="autoplay" value="' . $autostart . '">
     <param name="autostart" value="' . $autostart . '">
     <param name="scale" value="aspect">
    <!--[if !IE]>-->
      <object data="' . $url . '" type="' . $mimetype . '" ' . $size . '>
       <param name="src" value="'.$url.'">
       <param name="pluginurl" value="http://www.apple.com/quicktime/download/">
       <param name="controller" value="true">
       <param name="loop" value="false">
       <param name="autoplay" value="' . $autostart . '">
       <param name="autostart" value="' . $autostart . '">
       <param name="scale" value="aspect">
       <param name="wmode" value="transparent">
      </object>
    <!--<![endif]-->
    </object></span>';
    }
}


/**
 * HTLM5 audio player
 */
class MaharaMediaPlayer_html5audio extends MaharaMediaPlayer {

    /**
     * The (current) height, in pixels, of the VideoJS controls.
     * HACK: This height was just found through observation. There's probably
     * a more reliable way to get or control this.
     */
    const VIDEOJS_CONTROL_HEIGHT = 30;

    protected static function get_unique_id($artefact, $block) {
        return 'audio_' . parent::get_unique_id($artefact, $block);
    }

    /**
     * Returns html5 code to play the artefact audio
     *
     * @param ArtefactTypeFile $artefact
     * @param BlockInstance $block
     * @param int $width
     * @param int $height
     * @return string
     */
    public static function get_html(ArtefactType $artefact, BlockInstance $block, $width, $height) {

        $url = PluginBlocktypeInternalMedia::get_download_link($artefact, $block);

        require_once('file.php');
        $mimetype = $artefact->get('filetype');
        $filesize = round($artefact->get('size') / 1000000, 2) . 'MB';

        return '
        <audio
            id="' . self::get_unique_id($artefact, $block) . '"
            class="video-js vjs-default-skin vjs-big-play-centered vjs-audio"
            width="' . $width . '"
            height="'.self::VIDEOJS_CONTROL_HEIGHT.'"
        >
            <source src="' . $url . '" type="' . $mimetype . '"/>
            ' . get_string('browsercannotplay1', 'blocktype.internalmedia') . '
        </audio>';
    }

    public static function get_js_library() {
        return 'videojs/video.js';
    }

    public static function get_js_initjs(ArtefactTypeFile $artefact, BlockInstance $block, $width, $height) {
        return 'videojs(
            "' . self::get_unique_id($artefact, $block) . '",
            {
                "controls": true,
                "preload": "auto",
                "width": "1000",
                "height": "'.self::VIDEOJS_CONTROL_HEIGHT.'"
            }
        );';
    }
}

/**
 * HTML5 video player
 */
class MaharaMediaPlayer_html5video extends MaharaMediaPlayer {

    protected static function get_unique_id($artefact, $block) {
        return 'video_' . parent::get_unique_id($artefact, $block);
    }

    /**
     * Returns html5 code to play the artefact video
     *
     * @param ArtefactTypeFile $artefact
     * @param BlockInstance $block
     * @param int $width
     * @param int $height
     * @return string
     */
    public static function get_html(ArtefactType $artefact, BlockInstance $block, $width, $height) {

        $url = PluginBlocktypeInternalMedia::get_download_link($artefact, $block);

        require_once('file.php');
        $mimetype = $artefact->get('filetype');

        return '
        <video
            id="' . self::get_unique_id($artefact, $block) . '"
            class="video-js vjs-default-skin vjs-big-play-centered"
            width="' . $width . '"
            height="' . $height . '"
        >
            <source src="' . $url . '" type="' . $mimetype . '"/>
            ' . get_string('browsercannotplay1', 'blocktype.internalmedia') . '
        </video>';
    }

    public static function get_js_library() {
        return 'videojs/video.js';
    }

    public static function get_js_initjs(ArtefactTypeFile $artefact, BlockInstance $block, $width, $height) {
        global $CFG;
        return 'videojs(
            "' . self::get_unique_id($artefact, $block) . '",
            {
                "controls": true,
                "preload": "auto",
                "fluid": true,
                "width": ' . $width . ',
                "height": ' . $height . ',
                "swf": "' . $CFG->wwwroot . '/artefact/file/blocktype/internalmedia/videojs/video-js.swf"
            }
        );';
    }
}
