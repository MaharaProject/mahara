<?php
/**
 *
 * @package    mahara
 * @subpackage blocktype-externalvideo
 * @author     Catalyst IT Ltd
 * @author     Gregor Anzelj
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
class PluginBlocktypeExternalvideo extends MaharaCoreBlocktype {

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

    private static $embed_services = array(
        'embedly',
    );

    public static function get_title() {
        return get_string('title', 'blocktype.externalvideo');
    }

    public static function get_description() {
        return get_string('description', 'blocktype.externalvideo');
    }

    public static function get_categories() {
        return array('external' => 35000);
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

    private static function load_embed_services() {
        static $loaded_services = array();

        if (!empty($loaded_services)) {
            return $loaded_services;
        }

        foreach (self::$embed_services as $service) {
            include_once('embed_services/' . $service . '/embedservice.php');
            $servicename = 'Embed_' . $service;
            $embedservice = new $servicename;
            if ($embedservice->enabled()) {
                $loaded_services[$service] = $embedservice;
            }
        }
        return $loaded_services;
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
        return '<iframe class="externalvideoiframe" width="' . $width . '" height="' . $height . '" src="' . $url . '" allowfullscreen="1"></iframe>';
    }

    public static function get_blocktype_type_content_types() {
        return array('externalvideo' => array('media'));
    }

    public static function render_instance(BlockInstance $instance, $editing=false, $versioning=false) {
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

        // This is block that contains embed/iframe code from embed_service
        if (isset($configdata['embed']) && !empty($configdata['embed'])) {
            $service = $configdata['embed']['service'];
            include_once('embed_services/' . $service . '/embedservice.php');
            $servicename = 'Embed_' . $service;
            $embedservice = new $servicename;
            return $embedservice->embed_content($configdata['embed']);
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

    public static function instance_config_form(BlockInstance $instance) {
        $configdata = $instance->get('configdata');

        return array(
            'videoid' => array(
                'type'  => 'textarea',
                'title' => get_string('urlorembedcode', 'blocktype.externalvideo'),
                'description' => get_string('videourldescription3', 'blocktype.externalvideo') .
                    '<br />' . get_string('validiframesites', 'blocktype.externalvideo') . ' ' . self::get_valid_iframe_html() .'<br />'.
                    get_string('validurlsites', 'blocktype.externalvideo') . ' ' . self::get_valid_url_html() .'<br />'.
                    get_string('validembedservices', 'blocktype.externalvideo') . ' ' . self::get_valid_services_html(),
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
            'tags'  => array(
                'type'         => 'tags',
                'title'        => get_string('tags'),
                'description'  => get_string('tagsdescblock'),
                'defaultvalue' => $instance->get('tags'),
                'help'         => false,
            )
        );
    }

    public static function instance_config_validate(Pieform $form, $values) {
        $content = trim($values['videoid']);

        if (!filter_var($content, FILTER_VALIDATE_URL)) {
            // Not a valid url, so assume it's embed code so check that it's within a tag
            if (!preg_match('/^\<.*\>$/sm', $content)) {
                $form->set_error('videoid', get_string('invalidurlorembed', 'blocktype.externalvideo'), false);
            }
            // And if so let it go through to htmlpurifier.
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

        // The user entered a valid url, so check whether any of the
        // embed_services want to try and generate embed/iframe code.
        $services = self::load_embed_services();

        foreach ($services as $name => $service) {
            if ($service->validate_url($content)) {
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

            // Process user entered embed/iframe code from embed_service.
            $services = self::load_embed_services();

            foreach ($services as $name => $service) {
                if ($data = $service->process_content($values['videoid'])) {
                    // Override width set in embed/iframe code
                    if ($values['width']) {
                        $data['width'] = $values['width'];
                    }
                    // Override height set in embed/iframe code
                    if ($values['height']) {
                        $data['height'] = $values['height'];
                    }
                    $values['embed'] = $data;
                    break;
                }
            }

            return $values;
        }
        // If it's an unrecognised url, do nothing.
        if (!$urldata = self::process_url($values['videoid'], $values['width'], $values['height'])) {
            return $values;
        }

        // $urldata should now contain html
        return array_merge($values, $urldata);
    }

    public static function process_url($url, $width=0, $height=0) {
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

        // Try with embed services
        $services = self::load_embed_services();

        foreach ($services as $name => $service) {
            if ($result = $service->process_url($url, $width, $height)) {
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

    /**
     * Returns a block of HTML that the external video block can use to show the
     * embed services (e.g. Embed.ly) which can be used to process URLs.
     */
    private static function get_valid_services_html() {
        global $USER;
        $service_instances = self::load_embed_services();
        $wwwroot = get_config('wwwroot');

        $data = array();
        $nodata = '';
        if (empty($service_instances)) {
            if ($USER->get('admin')) {
                $nodata = get_string('enableservices', 'blocktype.externalvideo', '<a href="' . $wwwroot . 'admin/extensions/pluginconfig.php?plugintype=blocktype&pluginname=externalvideo">', '</a>');
            }
            else {
                $nodata = get_string('none');
            }
        }
        else {
            foreach ($service_instances as $name => $service) {
                $servicestr = get_string($name, 'blocktype.externalvideo');
                $data[$servicestr] = array(
                    'name' => $servicestr,
                    'url'  => $service->get_base_url(),
                    'icon' => $wwwroot . 'blocktype/externalvideo/embed_services/' . $name . '/favicon.png',
                );
            }
        }

        $smarty = smarty_core();
        $smarty->assign('data', $data);
        $smarty->assign('nodata', $nodata);
        return $smarty->fetch('blocktype:externalvideo:servicelist.tpl');
    }

    public static function default_copy_type() {
        return 'full';
    }

    public static function postinst($prevversion) {
        if ($prevversion == 0) {
            ensure_record_exists('iframe_source_icon', (object) array('name' => 'Prezi', 'domain' => 'prezi.com'), (object) array('name' => 'Prezi', 'domain' => 'prezi.com'));
            ensure_record_exists('iframe_source', (object) array('prefix' => 'prezi.com/embed/', 'name' => 'Prezi'), (object) array('prefix' => 'prezi.com/embed/', 'name' => 'Prezi'));
            ensure_record_exists('iframe_source_icon', (object) array('name' => 'Youtube [privacy mode]', 'domain' => 'www.youtube.com'), (object) array('name' => 'Youtube [privacy mode]', 'domain' => 'www.youtube.com'));
            ensure_record_exists('iframe_source', (object) array('prefix' => 'www.youtube-nocookie.com/embed/', 'name' => 'Youtube [privacy mode]'), (object) array('prefix' => 'www.youtube-nocookie.com/embed/', 'name' => 'Youtube [privacy mode]'));
            update_safe_iframe_regex();
        }
    }

    /**
     * Shouldn't be linked to any artefacts via the view_artefacts table.
     *
     * @param BlockInstance $instance
     * @return multitype:
     */
    public static function get_artefacts(BlockInstance $instance) {
        return array();
    }

    public static function get_instance_javascript(BlockInstance $bi) {
        return array(
            array(
                'file'   => 'js/voki.js',
            )
        );
    }
}
