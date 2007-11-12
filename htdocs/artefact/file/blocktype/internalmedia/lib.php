<?php
/**
 * This program is part of Mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage blocktype-internalmedia
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
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
        return array('multimedia', 'file');
    }

    public static function has_config() {
        return true;
    }

    public static function postinst($oldversion) {
        if ($oldversion == 0) {
            $enabledtypes = array_map(
                create_function('$a', 'return 1;'),
                self::get_all_extensions());
            unset($enabledtypes['swf']); // disable by default
            $enabledtypes = array_keys($enabledtypes);
            set_config_plugin('blocktype', 'internalmedia', 'enabledtypes', serialize($enabledtypes));
        }
    }

    public static function render_instance(BlockInstance $instance) {
        $configdata = $instance->get('configdata');

        if (empty($configdata['artefactid'])) {
            return '';
        }
        $result = self::get_js_source();
        require_once('artefact.php');
        $artefact = artefact_instance_from_id($configdata['artefactid']);
        $width  = (!empty($configdata['width'])) ? hsc($configdata['width']) : '300';
        $height = (!empty($configdata['height'])) ? hsc($configdata['height']) : '300';
        $extn = $artefact->get('oldextension');
        if (!in_array($extn, self::get_allowed_extensions())) {
            return 'This has been removed as an allowed type'; // TODO
        }
        $callbacks = self::get_all_extensions();
        $result .= call_static_method('PluginBlocktypeInternalmedia', $callbacks[$extn], $artefact, $instance, $width, $height);
        return $result;
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        $configdata = $instance->get('configdata');
        $form = array(
            self::artefactchooser_element((isset($configdata['artefactid'])) ? $configdata['artefactid'] : null),
        );
        $form['width'] = array(
                'type' => 'text',
                'title' => get_string('width'),
                'size' => 3,
                'defaultvalue' => (isset($configdata['width'])) ? $configdata['width'] : '',
        );
        $form['height'] = array(
                'type' => 'text',
                'title' => get_string('height'),
                'size' => 3,
                'defaultvalue' => (isset($configdata['height'])) ? $configdata['height'] : '',
        );
        return $form;
    }

    public static function get_artefacts(BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        if (isset($configdata['artefactid'])) {
            return array($configdata['artefactid']);
        }
        return false;
    }

    public static function artefactchooser_element($default=null) {
        $extraselect = '(' . implode(' OR ', array_map(
            create_function('$a', 'return "title LIKE \'%.$a\'";'),
            self::get_allowed_extensions())
        ) . ')';

        return array(
            'name' => 'artefactid',
            'type'  => 'artefactchooser',
            'title' => get_string('media', 'blocktype.file/internalmedia'),
            'defaultvalue' => $default,
            'rules' => array(
                'required' => true,
            ),
            'blocktype' => 'internalmedia',
            'limit' => 5,
            'selectone' => true,
            'artefacttypes' => array('file'),
            'extraselect' => $extraselect,
            'template' => 'artefact:file:artefactchooser-element.tpl',
        );
    }

    public static function save_config_options($values) {
        $enabledtypes = array();
        foreach ($values as $type => $enabled) {
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
        $currenttypes = self::get_allowed_extensions();

        foreach (array_keys(self::get_all_extensions()) as $filetype) {
            // TODO  add checks for types that have been disabled by the file plugin
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
                    'value' => 'Some stuff goes here' // TODO
                ),
            ),
            $filetypes
        );

        return array(
            'elements' => $filetypes,
            'renderer' => 'table'
        );
    }

    private static function get_allowed_extensions() {
        if ($data = get_config_plugin('blocktype', 'internalmedia', 'enabledtypes')) {
            return unserialize($data);
        }
        return array();
    }

    private static function get_all_extensions() {
        return array(
            'mp3'   => 'flash_player', // tested
            'swf'   => 'flash_player', // tested
            'flv'   => 'flash_player', // tested
            'mov'   => 'qt_player',  // tested
            'wmv'   => 'wmp_player', // tested
            'mpg'   => 'qt_player',  // tested
            'mpeg'  => 'qt_player',  // tested
            'avi'   => 'wmp_player', // tested
            'ram'   => 'real_player',
            'rm'    => 'real_player',
            'rpm'   => 'real_player',
        );
    }

    public static function flash_player($artefact, $block, $width, $height) {
        static $count = 0;
        $count++;
        $extn = $artefact->get('oldextension');
        if ($extn == 'mp3') {
            $height = 0; // one line
        }
        $id = 'blocktype_internalmedia_flash_' . time() . $count;
        $url = self::get_download_link($artefact, $block);
        $playerurl =  get_config('wwwroot') . "artefact/file/blocktype/internalmedia/mediaplayer.swf";
        $params = array();
        if ($extn == 'swf') {
            $playerurl = $url;
            $params['play'] = 'false';
        }
        $html =  '<a href="' . $url . '">' . hsc($artefact->get('title')) . '</a><br />
               <span class="blocktype_internalmedia_mp3" id="' . $id . '">(' 
               . get_string('flash', 'blocktype.file/internalmedia') . ')</span>
                <script type="text/javascript">
                    var so = new SWFObject(" ' . $playerurl . '","player","400","400","7");
                    so.addParam("allowfullscreen","false");
                    so.addVariable("file","' . urlencode($url) . '");
                    so.addVariable("displayheight"," ' . $height . '");
                    so.addVariable("type", "' . $artefact->get('oldextension') . '");
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

    public static function real_player($artefact, $block, $width, $height) {

        $url = self::get_download_link($artefact, $block);

        require_once('file.php');
        $mimetype = get_mime_type($artefact->get_path());
        $autostart = 'false';

        return '<a href="' . $url . '">' . hsc($artefact->get('title')) . '</a><br />'
    . '<span class="blocktype_internalmedia_real">
    <script type="text/javascript">
    //<![CDATA[
    document.write(\'<object classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" width="240" height="180">\\
      <param name="src" value="' . $url . '" />\\
      <param name="autostart" value="' . $autostart . '" />\\
      <param name="controls" value="imagewindow" />\\
      <param name="console" value="video" />\\
      <param name="loop" value="true" />\\
      <embed src="' . $url . '" width=240" height="180" loop="true" type="' . $mimetype . '" controls="imagewindow" console="video" autostart="' . $autostart . '" />\\
      </object><br />\\
      <object classid="clsid:CFCDAA03-8BE4-11cf-B84B-0020AFBBCCFA" width="240" height="30">\\
      <param name="src" value="' . $url . '" />\\
      <param name="autostart" value="' . $autostart . '" />\\
      <param name="controls" value="ControlPanel" />\\
      <param name="console" value="video" />\\
      <embed src="' . $url . '" width="240" height="30" controls="ControlPanel" type="' . $mimetype . '" console="video" autostart="' . $autostart . '" />\\
      </object>\');
    //]]>
    </script></span>';
    }

    public static function wmp_player($artefact, $block, $width, $height) {

        $url = self::get_download_link($artefact, $block, true);

        $size = 'width="' . $width . '" height="' . $height . '"';
        $autosize = 'false';

        $mimetype = 'video/x-ms-wmv'; // hardcode this
        $autostart = 'false';

        return '<a href="' . $url . '">' . hsc($artefact->get('title')) . '</a><br />'
    . '<span class="mediaplugin mediaplugin_wmp">
    <object classid="CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6" ' . $size . '
      standby="Loading Microsoft(R) Windows(R) Media Player components..."
      type="application/x-oleobject">
     <param name="Filename" value="' . $url . '" />' /* hsc($artefact->get('title')) . '" /> */ . '
     <param name="src" value="' . $url . '" />
     <param name="url" value="' . $url . '" />
     <param name="ShowControls" value="true" />
     <param name="AutoRewind" value="true" />
     <param name="AutoStart" value="' . $autostart . '" />
     <param name="Autosize" value="' . $autosize . '" />
     <param name="EnableContextMenu" value="true" />
     <param name="TransparentAtStart" value="false" />
     <param name="AnimationAtStart" value="false" />
     <param name="ShowGotoBar" value="false" />
     <param name="EnableFullScreenControls" value="true" />
    <!--[if !IE]>-->
      <object data="' . $url . '" type="' . $mimetype . '" ' . $size . '>
       <param name="src" value="' . $url . '" />
       <param name="controller" value="true" />
       <param name="autoplay" value="' . $autostart . '" />
       <param name="autostart" value="' . $autostart . '" />
       <param name="resize" value="scale" />
      </object>
    <!--<![endif]-->
    </object></span>';
    }

    public static function qt_player($artefact, $block, $width, $height) {

        $url = self::get_download_link($artefact, $block);

        $size = 'width="' . $width . '" height="' . $height . '"';

        require_once('file.php');
        $mimetype = get_mime_type($artefact->get_path());
        $autostart = 'false';

        return '<a href="' . $url . '">' . hsc($artefact->get('title')) . '</a><br />'
    . '<span class="mediaplugin mediaplugin_qt">
    <object classid="clsid:02BF25D5-8C17-4B23-BC80-D3488ABDDC6B"
      codebase="http://www.apple.com/qtactivex/qtplugin.cab" ' . $size . '>
     <param name="pluginspage" value="http://www.apple.com/quicktime/download/" />
     <param name="src" value="' . $url . '" />
     <param name="coltroller" value="true" />
     <param name="loop" value="false" />
     <param name="autoplay" value="' . $autostart . '" />
     <param name="autostart" value="' . $autostart . '" />
     <param name="scale" value="aspect" />
    <!--[if !IE]>-->
      <object data="' . $url . '" type="' . $mimetype . '" ' . $size . '>
       <param name="src" value="'.$url.'" />
       <param name="pluginurl" value="http://www.apple.com/quicktime/download/" />
       <param name="controller" value="true" />
       <param name="loop" value="false" />
       <param name="autoplay" value="' . $autostart . '" />
       <param name="autostart" value="' . $autostart . '" />
       <param name="scale" value="aspect" />
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
        return '<script src="' . get_config('wwwroot') . 'artefact/file/blocktype/internalmedia/swfobject.js"></script>
             <script defer="true" src="' . get_config('wwwroot') . 'artefact/file/blocktype/internalmedia/eolas_fix.js"></script>';
    }

}

?>
