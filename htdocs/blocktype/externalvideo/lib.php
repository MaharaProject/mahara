<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-externalvideo
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
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
            $mediasource = new $sourcename;
            // Any iframe output from these media sources must be
            // checked against the site-wide allowed iframe sources.
            // If a media source can only convert urls into iframes
            // that are going to be stripped, leave it out of the
            // list.
            if ($mediasource->enabled()) {
                $loaded_sources[$source] = $mediasource;
            }
        }
        return $loaded_sources;
    }

    public static function embed_code($url, $width, $height) {
        $width = (int) $width;
        $height = (int) $height;
        $url = hsc($url);
        return '<object width="' . $width . '" height="' . $height . '">'
            . '<param name="movie" value="' . $url . '"></param>'
            . '<param name="wmode" value="transparent"></param>'
            . '<param name="allowscriptaccess" value="never"></param>'
            . '<embed src="' . $url . '" '
            . 'type="application/x-shockwave-flash" wmode="transparent" width="' . $width . '" '
            . 'height="' . $height . '" allowscriptaccess="never"></embed></object>';
    }

    public static function iframe_code($url, $width, $height) {
        $width = (int) $width;
        $height = (int) $height;
        $url = hsc($url);
        return '<iframe width="' . $width . '" height="' . $height . '" src="' . $url . '" frameborder=0></iframe>';
    }

    public static function render_instance(BlockInstance $instance, $editing=false) {
        global $THEME;

        $configdata = $instance->get('configdata');
        $width  = (!empty($configdata['width'])) ? hsc($configdata['width']) : 0;
        $height = (!empty($configdata['height'])) ? hsc($configdata['height']) : 0;

        if (!isset($configdata['html'])) {
            if (!isset($configdata['videoid'])) {
                return '';
            }

            // This is a legacy block where videoid contains only a url, so generate embed/iframe code.
            $url = $configdata['videoid'];
            if (isset($configdata['type']) && $configdata['type'] == 'embed') {
                $configdata['html'] = $configdata['videoid'] = self::embed_code($url, $width, $height);
                unset($configdata['type']);
            }
            else if (isset($configdata['type']) && $configdata['type'] == 'iframe') {
                $configdata['html'] = $configdata['videoid'] = self::iframe_code($url, $width, $height);
                unset($configdata['type']);
            }
            else if ($urldata = self::process_url($url, $width, $height)) {
                $configdata = $urldata;
            }
            else {
                $configdata['html'] = ''; // We can't do anything with this url
            }
            $instance->set('configdata', $configdata);
            $instance->commit();
        }

        if (empty($configdata['html'])) {
            return '';
        }

        $smarty = smarty_core();
        // don't load html for auto retracted blocks to speed up page load time
        if (!empty($configdata['retractedonload']) && !$editing) {
            $smarty->assign('html', '<div id="block_' . $instance->get('id') . '_waiting">' . get_string('loading', 'mahara') . '</div>');
            $is_src = preg_match('/src=\"(.*?)\"/', $configdata['html'], $src);
            $is_width = preg_match('/ width=\"(.*?)\"/', $configdata['html'], $widthmatch);
            $is_height = preg_match('/ height=\"(.*?)\"/', $configdata['html'], $heightmatch);
            if ($is_src) {
                $smarty->assign('jsurl', $src[1]);
                // check if is embed rather than iframe
                $is_flashvars = preg_match('/flashvars=\"(.*?)\"/', $configdata['html'], $flashvars);
                if ($is_flashvars) {
                    $smarty->assign('jsflashvars', $flashvars[1]);
                }
                if (empty($width) && !empty($widthmatch)) {
                    $width = $widthmatch[1];
                }
                if (empty($height) && !empty($heightmatch)) {
                    $height = $heightmatch[1];
                }
            }
            else {
                // need to fall back to handling this normally
                $smarty->assign('html', $configdata['html']);
            }
        }
        else {
            $smarty->assign('html', $configdata['html']);
        }
        $smarty->assign('width', $width);
        $smarty->assign('height', $height);
        $smarty->assign('blockid', $instance->get('id'));

        return $smarty->fetch('blocktype:externalvideo:content.tpl');
    }

    public static function has_instance_config() {
        return true;
    }

    public static function instance_config_form($instance) {
        $configdata = $instance->get('configdata');

        return array(
            'videoid' => array(
                'type'  => 'textarea',
                'title' => get_string('urlorembedcode', 'blocktype.externalvideo'),
                'description' => get_string('videourldescription3', 'blocktype.externalvideo') .
                    '<br>' . get_string('validiframesites', 'blocktype.externalvideo') . self::get_valid_iframe_html() .
                    get_string('validurlsites', 'blocktype.externalvideo') . self::get_valid_url_html(),
                'cols' => '60',
                'rows' => '3',
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
                'description' => get_string('widthheightdescription', 'blocktype.externalvideo'),
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
        $content = trim($values['videoid']);

        if (!filter_var($content, FILTER_VALIDATE_URL)) {
            // Not a valid url, so assume it's embed code, and let it go through
            // to htmlpurifier.
            return;
        }

        // The user entered a valid url, so check whether any of the
        // media_sources want to try and generate embed/iframe code.
        $sources = self::load_media_sources();

        foreach ($sources as $name => $source) {
            if ($source->validate_url($content)) {
                return;
            }
        }

        // Nothing recognised this url.
        $form->set_error('videoid', get_string('invalidurl', 'blocktype.externalvideo'), false);
    }

    public static function instance_config_save($values) {
        $values['title']   = trim($values['title']);
        $values['videoid'] = trim($values['videoid']);

        if (!filter_var($values['videoid'], FILTER_VALIDATE_URL)) {
            // Not a url, treat the input as html to be sanitised when rendered.
            $httpstr = is_https() ? 'https' : 'http';
            $values['videoid'] = preg_replace('#https?://#', $httpstr . '://', $values['videoid']);
            $values['html'] = $values['videoid'];
            return $values;
        }

        // If it's an unrecognised url, do nothing.
        if (!$urldata = self::process_url($values['videoid'], $values['width'], $values['height'])) {
            return $values;
        }

        // $urldata should now contain html
        return array_merge($values, $urldata);
    }

    private static function process_url($url, $width=0, $height=0) {
        $sources = self::load_media_sources();

        foreach ($sources as $name => $source) {
            if ($result = $source->process_url($url, $width, $height)) {
                if ($result['type'] == 'embed') {
                    $result['html'] = self::embed_code($result['videoid'], $result['width'], $result['height']);
                }
                else if ($result['type'] == 'iframe') {
                    $result['html'] = self::iframe_code($result['videoid'], $result['width'], $result['height']);
                }
                else {
                    throw new SystemException('externalvideo block: invalid embed type for url');
                }

                // From now on, forget the url and just use the embed/iframe code as html content
                unset($result['type']);
                $result['videoid'] = $result['html'];

                return $result;
            }
        }
        return false;
    }

    /**
     * Returns a block of HTML that the external video block can use to show the
     * sites for which we will process URLs.
     */
    private static function get_valid_url_html() {
        $source_instances = self::load_media_sources();
        $wwwroot = get_config('wwwroot');

        $data = array();
        foreach ($source_instances as $name => $source) {
            $sourcestr = get_string($name, 'blocktype.externalvideo');
            $data[$sourcestr] = array(
                'name' => $sourcestr,
                'url'  => $source->get_base_url(),
                'icon' => $wwwroot . 'blocktype/externalvideo/media_sources/' . $name . '/favicon.png',
            );
        }

        ksort($data);

        $smarty = smarty_core();
        $smarty->assign('data', $data);
        return $smarty->fetch('blocktype:externalvideo:sitelist.tpl');
    }

    /**
     * Returns a block of HTML that the external video block can use to show the
     * sites for which iframes are allowed.
     */
    private static function get_valid_iframe_html() {
        $iframedomains = get_records_menu('iframe_source_icon', '', '', 'name');
        if (empty($iframedomains)) {
            return '';
        }

        $data = array();
        foreach ($iframedomains as $name => $host) {
            $data[$name] = array(
                'name' => $name,
                'url'  => 'http://' . $host,
                'icon' => favicon_display_url($host),
            );
        }

        $smarty = smarty_core();
        $smarty->assign('data', $data);
        return $smarty->fetch('blocktype:externalvideo:sitelist.tpl');
    }

    public static function default_copy_type() {
        return 'full';
    }

    public static function postinst($prevversion) {
        if ($prevversion == 0) {
            ensure_record_exists('iframe_source_icon', (object) array('name' => 'Prezi', 'domain' => 'prezi.com'), (object) array('name' => 'Prezi', 'domain' => 'prezi.com'));
            ensure_record_exists('iframe_source', (object) array('prefix' => 'prezi.com/embed/', 'name' => 'Prezi'), (object) array('prefix' => 'prezi.com/embed/', 'name' => 'Prezi'));
            update_safe_iframe_regex();
        }
    }

}
