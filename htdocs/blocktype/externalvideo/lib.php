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
 * @subpackage blocktype-externalvideo
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/**
 * todos before this block type can be considered complete
 *  - document this class and methods
 *  - correct category
 *  - more video url sources, and good default behaviour
 *  - block title editable
 *  - i18n
 *  - minvalue/maxvalue rules
 */
class PluginBlocktypeExternalvideo extends SystemBlocktype {

    public static function get_title() {
        return get_string('title', 'blocktype.externalvideo');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.externalvideo');
    }

    public static function get_categories() {
        return array('file');
    }

    public static function render_instance(BlockInstance $instance) {
        $configdata = $instance->get('configdata');
        $result = '';
        $url    = hsc(self::make_video_url($configdata['videoid']));
        $width  = (!empty($configdata['width'])) ? hsc($configdata['width']) : '100%';
        $height = (!empty($configdata['height'])) ? hsc($configdata['height']) : '';

        if (isset($configdata['videoid'])) {
            $result  = '<div class="center">';
            $result .= '<object width="' . $width . '" height="' . $height . '">';
            $result .= '<param name="movie" value="' . $url . '"></param>';
            $result .= '<param name="wmode" value="transparent"></param>';
            $result .= '<param name="allowscriptaccess" value="never"></param>';
            $result .= '<embed src="' . $url . '" ';
            $result .= 'type="application/x-shockwave-flash" wmode="transparent" width="' . $width . '" ';
            $result .= 'height="' . $height . '" allowscriptaccess="never"></embed></object>';
            $result .= '</div>';
        }

        return $result;
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        $configdata = $instance->get('configdata');
        return array(
            'videoid' => array(
                'type'  => 'text',
                'title' => 'Video URL',
                'description' => 'Paste the URL of the page where the video is located',
                'width' => '90%',
                'defaultvalue' => $configdata['videoid'],
                'rules' => array(
                    'required' => true
                ),
            ),
            'width' => array(
                'type' => 'text',
                'title' => 'Width',
                'size' => 3,
                //'rules' => array(
                //    'minvalue' => 100,
                //    'maxvalue' => 800,
                //),
                'defaultvalue' => (isset($configdata['width'])) ? $configdata['width'] : '',
            ),
            'height' => array(
                'type' => 'text',
                'title' => 'Height',
                'size' => 3,
                //'rules' => array(
                //    'minvalue' => 100,
                //    'maxvalue' => 800,
                //),
                'defaultvalue' => (isset($configdata['height'])) ? $configdata['height'] : '',
            ),
        );
    }

    private static function make_video_url($url) {
        static $embedsources = array(
            array(
                'match' => '#.*youtube\.com.*v(=|\/)([a-zA-Z0-9-]+).*#',
                'url'   => 'http://www.youtube.com/v/$2'
            ),
            array(
                'match' => '#.*video.google.com.*docid=(\-?[0-9]+).*#',
                'url'   => 'http://video.google.com/googleplayer.swf?docId=$1',
            )
        );

        foreach ($embedsources as $source) {
            if (preg_match($source['match'], $url)) {
                return preg_replace($source['match'], $source['url'], $url);
            }
        }
        // TODO handle failure case
    }
}

?>
