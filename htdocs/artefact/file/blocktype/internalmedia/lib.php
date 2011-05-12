<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage blocktype-internalmedia
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
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
        $width  = (!empty($configdata['width'])) ? hsc($configdata['width']) : '300';
        $height = (!empty($configdata['height'])) ? hsc($configdata['height']) : '300';
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
            'artefacttype'    => array('file'),
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
            'artefacttypes' => array('file'),
            'template' => 'artefact:file:artefactchooser-element.tpl',
        );
    }

    public static function artefactchooser_get_element_data($artefact) {
        $artefact->icon = call_static_method(generate_artefact_class_name($artefact->artefacttype), 'get_icon', array('id' => $artefact->id));
        return $artefact;
    }

    public static function save_config_options($values) {
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
                'type'  => 'checkbox',
                'title' => get_string($filetype, 'artefact.file'),
                'defaultvalue' => in_array($filetype, $currenttypes),
            );
        }
        uasort($filetypes, create_function('$a, $b', 'return $a["title"] > $b["title"];'));
        $filetypes = array_merge(
            array(
                'description' => array(
                    'value' => get_string('configdesc', 'blocktype.file/internalmedia'),
                ),
            ),
            $filetypes
        );

        return array(
            'elements' => $filetypes,
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
        $escapedurl = str_replace('&', '%26', $url); // Flash needs these escaped

        $baseurl = get_config('wwwroot') . 'artefact/file/blocktype/internalmedia/';

        $playerurl = $baseurl . 'flowplayer/flowplayer-3.2.4.swf';
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
		                  url: "' . $baseurl . 'flowplayer.audio/flowplayer.audio-3.2.1.swf"
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
                          url: "flowplayer.controls-3.2.2.swf",
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
    <!--[if !IE]>-->
      <object data="' . $url . '" type="' . $mimetype . '" ' . $size . '>
       <param name="src" value="' . $url . '">
       <param name="url" value="' . $url . '">
       <param name="controller" value="true">
       <param name="autoplay" value="' . $autostart . '">
       <param name="autostart" value="' . $autostart . '">
       <param name="resize" value="scale">
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
      </object>
    <!--<![endif]-->
    </object></span>';
    }

    private static function get_download_link(ArtefactTypeFile $artefact, BlockInstance $instance, $wmp=false) {
        return get_config('wwwroot') . 'artefact/file/download.php?file=' 
            . $artefact->get('id') . '&view=' . $instance->get('view')
            . ($wmp ? '&download=1' : '');
    }

    private static function get_js_source() {
        if (defined('BLOCKTYPE_INTERNALMEDIA_JS_INCLUDED')) {
            return '';
        }
        define('BLOCKTYPE_INTERNALMEDIA_JS_INCLUDED', true);
        return '<script src="'.get_config('wwwroot').'artefact/file/blocktype/internalmedia/flowplayer/flowplayer-3.2.4.js"></script>
             <script src="' . get_config('wwwroot') . 'artefact/file/blocktype/internalmedia/swfobject.js" type="text/javascript"></script>
             <script defer="defer" src="' . get_config('wwwroot') . 'artefact/file/blocktype/internalmedia/eolas_fix.js" type="text/javascript"></script>';
    }

    public static function default_copy_type() {
        return 'full';
    }

}
