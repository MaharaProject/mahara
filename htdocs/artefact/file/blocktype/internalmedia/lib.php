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

class PluginBlocktypeInternalmedia extends PluginBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.file/internalmedia');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.file/internalmedia');
    }

    public static function get_categories() {
        return array('fileimagevideo');
    }

    public static function has_config() {
        return true;
    }

    public static function postinst($oldversion) {
        if ($oldversion == 0) {
            set_config_plugin('blocktype', 'internalmedia', 'enabledtypes', serialize(array('flv', 'mp3')));
        }
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        $configdata = $instance->get('configdata');

        if (empty($configdata['artefactid'])) {
            return '';
        }
        $result = self::get_js_source();
        require_once(get_config('docroot') . 'artefact/lib.php');
        $artefact = $instance->get_artefact_instance($configdata['artefactid']);
        $defaultwidth = get_config_plugin('blocktype', 'internalmedia', 'width') ?
                get_config_plugin('blocktype', 'internalmedia', 'width') : 300;
        $defaultheight = get_config_plugin('blocktype', 'internalmedia', 'height') ?
                get_config_plugin('blocktype', 'internalmedia', 'height') : 300;
        $width  = (!empty($configdata['width'])) ? hsc($configdata['width']) : $defaultwidth;
        $height = (!empty($configdata['height'])) ? hsc($configdata['height']) : $defaultheight;
        $mimetype = $artefact->get('filetype');
        $mimetypefiletypes = self::get_allowed_mimetype_filetypes();
        if (!isset($mimetypefiletypes[$mimetype])) {
            return get_string('typeremoved', 'blocktype.file/internalmedia');
        }
        $callbacks = self::get_all_filetype_players();
        $result .= '<div class="mediaplayer-container center"><div class="mediaplayer">' . call_static_method('PluginBlocktypeInternalmedia', $callbacks[$mimetypefiletypes[$mimetype]], $artefact, $instance, $width, $height) . '</div></div>';
        return $result;
    }

    public static function has_instance_config() {
        return true;
    }

    public static function get_instance_config_javascript() {
        $result = self::get_js_source(true);
        if (!empty($result)) {
            return $result;
        }
    }

    public static function instance_config_form($instance) {
        $configdata = $instance->get('configdata');
        safe_require('artefact', 'file');
        $instance->set('artefactplugin', 'file');
        return array(
            'artefactid' => self::filebrowser_element($instance, (isset($configdata['artefactid'])) ? array($configdata['artefactid']) : null),
            'width' => array(
                'type' => 'text',
                'title' => get_string('width'),
                'size' => 3,
                'defaultvalue' => (isset($configdata['width'])) ? $configdata['width'] : '',
                'rules' => array('minvalue' => 1, 'maxvalue' => 2000),
            ),
            'height' => array(
                'type' => 'text',
                'title' => get_string('height'),
                'size' => 3,
                'defaultvalue' => (isset($configdata['height'])) ? $configdata['height'] : '',
                'rules' => array('minvalue' => 1, 'maxvalue' => 2000),
            ),
        );
    }

    public static function filebrowser_element(&$instance, $default=array()) {
        $element = ArtefactTypeFileBase::blockconfig_filebrowser_element($instance, $default);
        $element['title'] = get_string('media', 'blocktype.file/internalmedia');
        $element['name'] = 'artefactid';
        $element['config']['selectone'] = true;
        $element['filters'] = array(
            'artefacttype'    => array('file', 'audio', 'video'),
            'filetype'        => self::get_allowed_mimetypes(),
        );
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

    public static function save_config_options($form, $values) {
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
        set_config_plugin('blocktype', 'internalmedia', 'height', $values['height']);
        set_config_plugin('blocktype', 'internalmedia', 'width',  $values['width']);
    }

    public static function get_config_options() {
        $elements = array();
        // Allowed file types
        $filetypes = array();
        $currenttypes = self::get_allowed_filetypes();

        foreach (self::get_all_filetypes() as $filetype) {
            $filetypes[$filetype] = array(
                'type'  => 'checkbox',
                'title' => get_string($filetype, 'artefact.file'),
                'defaultvalue' => in_array($filetype, $currenttypes),
            );
        }
        uasort($filetypes, create_function('$a, $b', 'return $a["title"] > $b["title"];'));
        $options = array_merge(
            array(
                'description' => array(
                    'value' => get_string('configdesc', 'blocktype.file/internalmedia'),
                ),
            ),
            $filetypes
        );
        $options['height'] = array(
            'type'          => 'text',
            'title'         => get_string('height'),
            'rules'        => array('integer' => true, 'minvalue' => 120, 'maxvalue' => 3072),
            'defaultvalue'  => get_config_plugin('blocktype', 'internalmedia', 'height')
                ? get_config_plugin('blocktype', 'internalmedia', 'height') : 240,
        );
        $options['width'] = array(
            'type'          => 'text',
            'title'         => get_string('width'),
            'rules'        => array('integer' => true, 'minvalue' => 160, 'maxvalue' => 4096),
            'defaultvalue'  => get_config_plugin('blocktype', 'internalmedia', 'width')
                ? get_config_plugin('blocktype', 'internalmedia', 'width') : 320,
        );

        return array(
            'elements' => $options,
            'renderer' => 'table'
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
                return $mimetypes;
            }
        }
        return array();
    }


    private static function get_all_filetypes() {
        return array_keys(self::get_all_filetype_players());
    }


    private static function get_all_filetype_players() {
        /* Keyed by the file type descriptions from the artefact_file_mime_types table */
        return array(
            'mp3'       => 'flow_player', // tested
            'swf'       => 'flash_player', // tested
            'flv'       => 'flow_player', // tested
            'quicktime' => 'qt_player',  // tested
            'wmv'       => 'wmp_player', // tested
            'mpeg'      => 'qt_player',  // tested
            'avi'       => 'wmp_player', // tested
            'mp4_video' => 'flow_player', // tested
            /* commenting out for now
            'ram'   => 'real_player',
            'rm'    => 'real_player',
            'rpm'   => 'real_player',
            */
        );
    }

    public static function flash_player($artefact, $block, $width, $height) {
        static $count = 0;
        $count++;
        $id = 'blocktype_internalmedia_flash_' . time() . $count;
        $url = self::get_download_link($artefact, $block);
        $params = array('play' => 'true');
        $html =  '<a href="' . $url . '">' . hsc($artefact->get('title')) . '</a><br>
               <span class="blocktype_internalmedia_mp3" id="' . $id . '">('
               . get_string('flashanimation', 'blocktype.file/internalmedia') . ')</span>
                <script type="text/javascript">
                    var so = new SWFObject("' . $url . '","player","' . $width . '","' . ($height + 20). '","7");
                    so.addParam("allowfullscreen","false");
                    so.addVariable("displayheight"," ' . $height . '");
                    so.addVariable("type", "swf");
                    so.addVariable("height", "' . $height . '");
                    so.addVariable("width", "' . $width . '");
                    so.addVariable("wmode", "transparent");
                ';
        foreach ($params as $key => $value) {
            $html .= '      so.addParam("' . $key . '", "' . $value . '"); '. "\n";
        }

        $html .= '
                    so.write("' . $id . '");
                </script>
        ';
        return $html;

    }

    public static function flow_player($artefact, $block, $width, $height) {
        static $count = 0;
        $count++;
        $extn = $artefact->get('oldextension');

        $id = 'blocktype_internalmedia_flow_' . time() . $count;
        $url = self::get_download_link($artefact, $block);
        $url = parse_url($url, PHP_URL_PATH) . '?' . parse_url($url, PHP_URL_QUERY);
        $escapedurl = str_replace('&', '%26', $url); // Flash needs these escaped

        $baseurlpath = parse_url(get_config('wwwroot'), PHP_URL_PATH);
        $baseurl = $baseurlpath . 'artefact/file/blocktype/internalmedia/';

        $playerurl = $baseurl . 'mahara-flashplayer/mahara-flashplayer.swf';
        $autohide = 'true';
        $type = '';
        $audio = '';
        $buffering = 'true';
        if ($extn == 'mp3') {
            $height = 25; // only show the controls
            $autohide = 'false';
            $type = 'type: "audio",'; // force the player to use the audio plugin
            $buffering = 'false'; // without this autoPlay will also be set to true
            $audio = ', audio: {
		                  url: "' . $baseurl . 'flowplayer.audio/flowplayer.audio-3.2.11.swf"
		             }';
        }

        $html =  '<a href="' . $url . '">' . hsc($artefact->get('title')) . '</a><br>
               <span class="blocktype_internalmedia_mp3" id="' . $id . '" style="display:block;width:'.$width.'px;height:'.$height.'px;"></span>
               <span id="' . $id . '_h">' . get_string('flashanimation', 'blocktype.file/internalmedia') . '</span>
               <script type="text/javascript">
               flowplayer("'.$id.'", "'.$playerurl.'", {
                   clip:  {
                       url: "'.$escapedurl.'",
                       '.$type.'
                       autoPlay: false,
                       autoBuffering: '.$buffering.'
                   },
                   plugins: {
	                  controls: {
                          url: "mahara-flashplayer.controls.swf",
                          play:true,
                          volume:true,
                          mute:true,
                          time:false,
                          stop:false,
                          playlist:false,
                          fullscreen:true,
                          scrubber: true,
                          autoHide: '.$autohide.'
                      }'.$audio.'
                   }
               }).load();
               addElementClass("' . $id . '_h", "hidden");
               </script>
';
        return $html;

    }

    public static function real_player($artefact, $block, $width, $height) {

        $url = self::get_download_link($artefact, $block);

        require_once('file.php');
        $mimetype = $artefact->get('filetype');
        $autostart = 'false';

        return '<a href="' . $url . '">' . hsc($artefact->get('title')) . '</a><br>'
    . '<span class="blocktype_internalmedia_real">
    <script type="text/javascript">
    //<![CDATA[
    document.write(\'<object classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" width="240" height="180">\\
      <param name="src" value="' . $url . '">\\
      <param name="autostart" value="' . $autostart . '">\\
      <param name="controls" value="imagewindow">\\
      <param name="console" value="video">\\
      <param name="loop" value="true">\\
      <embed src="' . $url . '" width=240" height="180" loop="true" type="' . $mimetype . '" controls="imagewindow" console="video" autostart="' . $autostart . '">\\
      </object><br>\\
      <object classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" width="240" height="30">\\
      <param name="src" value="' . $url . '">\\
      <param name="autostart" value="' . $autostart . '">\\
      <param name="controls" value="ControlPanel">\\
      <param name="console" value="video">\\
      <embed src="' . $url . '" width="240" height="30" controls="ControlPanel" type="' . $mimetype . '" console="video" autostart="' . $autostart . '">\\
      </object>\');
    //]]>
    </script></span>';
    }

    public static function wmp_player($artefact, $block, $width, $height) {

        $url = hsc(self::get_download_link($artefact, $block, true));

        $size = 'width="' . $width . '" height="' . $height . '"';
        $autosize = 'false';

        $mimetype = 'video/x-ms-wmv'; // hardcode this
        $autostart = 'false';

        return '<a href="' . $url . '">' . hsc($artefact->get('title')) . '</a><br>'
    . '<span class="mediaplugin mediaplugin_wmp">
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

    public static function qt_player($artefact, $block, $width, $height) {

        $url = self::get_download_link($artefact, $block);

        $size = 'width="' . $width . '" height="' . $height . '"';

        require_once('file.php');
        $mimetype = $artefact->get('filetype');
        $autostart = 'false';

        return '<a href="' . $url . '">' . hsc($artefact->get('title')) . '</a><br>'
    . '<span class="mediaplugin mediaplugin_qt">
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

    private static function get_download_link(ArtefactTypeFile $artefact, BlockInstance $instance, $wmp=false) {
        return get_config('wwwroot') . 'artefact/file/download.php?file='
            . $artefact->get('id') . '&view=' . $instance->get('view')
            . ($wmp ? '&download=1' : '');
    }

    private static function get_js_source($asarray = false) {
        if (defined('BLOCKTYPE_INTERNALMEDIA_JS_INCLUDED')) {
            return '';
        }
        define('BLOCKTYPE_INTERNALMEDIA_JS_INCLUDED', true);
        if ($asarray) {
            return array(get_config('wwwroot').'artefact/file/blocktype/internalmedia/mahara-flashplayer/mahara-flashplayer.js',
                         get_config('wwwroot') . 'artefact/file/blocktype/internalmedia/swfobject.js',
                         );
        }
        return '<script src="'.get_config('wwwroot').'artefact/file/blocktype/internalmedia/mahara-flashplayer/mahara-flashplayer.js"></script>
             <script src="' . get_config('wwwroot') . 'artefact/file/blocktype/internalmedia/swfobject.js" type="text/javascript"></script>';
    }

    public static function default_copy_type() {
        return 'full';
    }

}
