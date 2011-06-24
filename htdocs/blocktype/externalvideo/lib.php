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
 * @subpackage blocktype-externalvideo
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
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

    private static $media_sources = array(
        'youtube',
        'teachertube',
        'scivee',
        'googlevideo',
        'glogster',
        'slideshare',
        'voicethread',
        'wikieducator',
        'prezi',
        'vimeo',
        'voki',
    );

    public static function get_title() {
        return get_string('title', 'blocktype.externalvideo');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.externalvideo');
    }

    public static function get_categories() {
        return array('external');
    }

    private static function load_media_sources() {
        static $loaded_sources = array();

        if (!empty($loaded_sources)) {
            return $loaded_sources;
        }

        foreach (self::$media_sources as $source) {
            include_once('media_sources/' . $source . '/mediasource.php');
            $sourcename = 'Media_' . $source;
            $loaded_sources[$source] = new $sourcename;
        }
        return $loaded_sources;
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        $configdata = $instance->get('configdata');
        $result = '';
        $width  = (!empty($configdata['width'])) ? hsc($configdata['width']) : 0;
        $height = (!empty($configdata['height'])) ? hsc($configdata['height']) : 0;

        if (isset($configdata['videoid'])) {
            $url = hsc($configdata['videoid']);

            // IE seems to wait for all elements on the page to load
            // fully before the onload event goes off.  This means the
            // view editor isn't initialised until all videos have
            // finished loading, and an invalid video URL can stop the
            // editor from loading and result in an uneditable view.

            // Therefore, when this block appears on first load of the
            // view editing page, keep the embed code out of the page
            // initially and add it in after the page has loaded.

            $embed = '';

            if (isset($configdata['type']) && $configdata['type'] == 'embed') {
                $embed  = '<object width="' . $width . '" height="' . $height . '">';
                $embed .= '<param name="movie" value="' . $url . '"></param>';
                $embed .= '<param name="wmode" value="transparent"></param>';
                $embed .= '<param name="allowscriptaccess" value="never"></param>';
                $embed .= '<embed src="' . $url . '" ';
                $embed .= 'type="application/x-shockwave-flash" wmode="transparent" width="' . $width . '" ';
                $embed .= 'height="' . $height . '" allowscriptaccess="never"></embed></object>';
            }
            else if (isset($configdata['type']) && $configdata['type'] == 'iframe') {
                $embed  = '<iframe width="' . $width . '" height="' . $height . '" ';
                $embed .= 'src="' . $url . '" frameborder=0></iframe>';
            }

            $block = $instance->get('id');
            $configuring = $block == param_integer('blockconfig', 0);

            $result .= '<div class="mediaplayer-container center">';
            $result .= '<div id="vid_' . $block . '" class="mediaplayer" style="width: ' . $width . 'px; height: ' . $height . 'px; margin: 0 auto;">';

            if (!$editing || $configuring) {
                $result .= $embed;
            }

            $result .= '</div></div>';

            if ($editing && !$configuring) {
                $result .= '<script>';
                $result .= 'addLoadEvent(function() {$(\'vid_' . $block . "').innerHTML = " . json_encode($embed) . ';});';
                $result .= '</script>';
            }
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
                'title' => get_string('videourl','blocktype.externalvideo'),
                'description' => get_string('videourldescription2','blocktype.externalvideo') . self::get_html_of_supported_websites(),
                'width' => '90%',
                'defaultvalue' => isset($configdata['videoid']) ? $configdata['videoid'] : null,
                'rules' => array(
                    'required' => true,
                ),
            ),
            'width' => array(
                'type' => 'text',
                'title' => get_string('width','blocktype.externalvideo'),
                'size' => 3,
                'rules' => array(
                    'regex'  => '#\d+%?#',
                    'minvalue' => 0,
                    'maxvalue' => 2000,
                ),
                'defaultvalue' => (!empty($configdata['width'])) ? $configdata['width'] : 0,
            ),
            'height' => array(
                'type' => 'text',
                'title' => get_string('height','blocktype.externalvideo'),
                'size' => 3,
                'rules' => array(
                    'regex'  => '#\d+%?#',
                    'minvalue' => 0,
                    'maxvalue' => 2000,
                ),
                'defaultvalue' => (!empty($configdata['height'])) ? $configdata['height'] : 0,
            ),
        );
    }

    public static function instance_config_validate(Pieform $form, $values) {
        if ($values['videoid']) {
            $sources = self::load_media_sources();

            $valid = false;
            foreach ($sources as $name => $source) {
                if ($valid = $source->validate_url($values['videoid'])) {
                    break;
                }
            }
            if (!$valid) {
                $form->set_error('videoid', get_string('invalidurl', 'blocktype.externalvideo'));
            }
        }
    }

    public static function instance_config_save($values) {
        $title = $values['title'];
        $values = self::process_url($values['videoid'], $values['width'], $values['height']);
        $values['title'] = $title;
        return $values;
    }

    private static function process_url($url, $width=0, $height=0) {
        $sources = self::load_media_sources();

        foreach ($sources as $name => $source) {
            if ($result = $source->process_url($url, $width, $height)) {
                return $result;
            }
        }
        return false;
    }

    /**
     * Returns a block of HTML that the external video block can use to list
     * which video sites are supported.
     */
    private static function get_html_of_supported_websites() {
        $source_instances = self::load_media_sources();

        $wwwroot = get_config('wwwroot');
        $html    = '<ul style="list-style-type: none;" class="inlinelist">';

        foreach ($source_instances as $name => $source) {
            $sourcestr = get_string($name, 'blocktype.externalvideo');
            $html .= '<li><a href="' . $source->get_base_url() . '" target="_blank"><img src="' . $wwwroot . 'blocktype/externalvideo/media_sources/' . $name . '/favicon.png" alt="' . $sourcestr . '" title="' . $sourcestr . '"></a></li>';
        }
        $html .= '</ul>';

        return $html;
    }

    public static function default_copy_type() {
        return 'full';
    }

}
