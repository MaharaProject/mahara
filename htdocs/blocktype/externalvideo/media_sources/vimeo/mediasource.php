<?php

require_once(dirname(__FILE__) . '/../Media_base.php');

class Media_vimeo implements MediaBase {

    private $httpstr;
    private static $base_url;
    private static $default_width  = 400;
    private static $default_height = 225;

    private static $iframe_sources;

    function __construct() {
        $this->httpstr = is_https() ? 'https' : 'http';

        self::$base_url = $this->httpstr . '://vimeo.com/';

        self::$iframe_sources = array(
            array(
                    'match' => '#^http://player\.vimeo\.com/video/([0-9]+).*#',
                    'url'   => $this->httpstr . '://player.vimeo.com/video/$1'
            ),
            array(
                    'match' => '#^https?://(www\.|secure\.)?vimeo\.com/([0-9]+)#',
                    'url'   => $this->httpstr . '://player.vimeo.com/video/$2'
            ),
        );
    }

    public function enabled() {
        // Check that the output iframe source will be allowed by htmlpurifier
        $outputsrc = $this->httpstr . '://player.vimeo.com/video/';
        return preg_match(get_config('iframeregexp'), $outputsrc);
    }

    public function process_url($input, $width=0, $height=0) {
        $width  = $width  ? (int)$width  : self::$default_width;
        $height = $height ? (int)$height : self::$default_height;

        foreach (self::$iframe_sources as $source) {
            if (preg_match($source['match'], $input)) {
                $output = preg_replace($source['match'], $source['url'], $input);

                $result = array(
                    'videoid' => $output,
                    'type'    => 'iframe',
                    'width'   => $width,
                    'height'  => $height,
                );

                return $result;
            }
        }
        return false;
    }

    public function validate_url($input) {
        foreach (self::$iframe_sources as $source) {
            if (preg_match($source['match'], $input)) {
                return true;
            }
        }
        return false;
    }

    public function get_base_url() {
        return self::$base_url;
    }
}
