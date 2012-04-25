<?php

require_once(dirname(__FILE__) . '/../Media_base.php');

class Media_voicethread implements MediaBase {

    private $httpstr;
    private static $base_url;

    private static $default_width  = 480;
    private static $default_height = 360;

    private static $embed_sources;

    function __construct() {
        $this->httpstr = is_https() ? 'https' : 'http';

        self::$base_url = $this->httpstr . '://www.voicethread.com/';

        self::$embed_sources = array(
            array(
                'match' => '#^https?://(www\.)?voicethread\.com/share/([0-9]+).*#',
                'url'   => $this->httpstr . '://voicethread.com/book.swf?b=$2',
            ),
            array(
                'match' => '@^https?://(www\.)?voicethread\.com/\??#q\.b([0-9]+).*@',
                'url'   => $this->httpstr . '://voicethread.com/book.swf?b=$2',
            ),
        );
    }

    public function enabled() {
        return true;
    }

    public function process_url($input, $width=0, $height=0) {
        $width  = $width  ? (int)$width  : self::$default_width;
        $height = $height ? (int)$height : self::$default_height;

        foreach (self::$embed_sources as $source) {
            if (preg_match($source['match'], $input)) {
                $output = preg_replace($source['match'], $source['url'], $input);
                $result = array(
                    'videoid' => $output,
                    'type'    => 'embed',
                    'width'   => $width,
                    'height'  => $height,
                );
                return $result;
            }
        }
        return false;
    }

    public function validate_url($input) {
        foreach (self::$embed_sources as $source) {
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
