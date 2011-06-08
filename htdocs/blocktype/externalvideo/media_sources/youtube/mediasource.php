<?php

require_once(dirname(__FILE__) . '/../Media_base.php');

class Media_youtube implements MediaBase {

    private static $base_url = 'http://youtube.com/';

    private static $default_width  = 560;
    private static $default_height = 349;

    private static $iframe_sources = array(
        array(
            'match' => '#.*youtube\.com.*(v|(cp))(=|\/)([a-zA-Z0-9_=-]+).*#',
            'url'   => 'http://www.youtube.com/embed/$4'
        ),
        array(
            'match' => '#.*https?://(www\.)?youtube\.com/embed/([a-zA-Z0-9\-_+]*).*#',
            'url'   => 'http://www.youtube.com/embed/$2',
        ),
        array(
            'match' => '#https?://(www\.)?youtu\.be/([a-zA-Z0-9\-_+]*)#',
            'url'   => 'http://www.youtube.com/embed/$2',
        ),
    );

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
