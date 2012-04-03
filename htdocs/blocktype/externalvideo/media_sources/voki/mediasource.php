<?php

require_once(dirname(__FILE__) . '/../Media_base.php');

class Media_voki implements MediaBase {

    private static $base_url = 'http://www.voki.com/';
    private static $default_width  = 200;
    private static $default_height = 267;

    private static $iframe_sources = array(
        array(
            'match' => '#^http://www\.voki\.com/pickup\.php\?(partnerid=symbaloo&)?scid=([0-9]+)#',
            'url' => 'http://voki.com/php/checksum/scid=$2'
        ),

        array(
            'match' => '#^http://www\.voki\.com/pickup\.php\?(partnerid=symbaloo&)?scid=([0-9]+)&height=([0-9]+)&width=([0-9]+)#',
            'url' => 'http://voki.com/php/checksum/scid=$2&height=$3&width=$4'
        ),

        array(
            'match' => '#^http://voki\.com/php/checksum/scid=([0-9]+)&height=([0-9]+)&width=([0-9]+)#',
            'url' => 'http://voki.com/php/checksum/scid=$1&height=$2&width=$3'
        ),
    );

    public function enabled() {
        // Check that the output iframe source will be allowed by htmlpurifier
        return preg_match(get_config('iframeregexp'), 'http://voki.com/php/checksum/');
    }

    public function process_url($input, $width=0, $height=0) {
        $width  = $width  ? (int)$width  : self::$default_width;
        $height = $height ? (int)$height : self::$default_height;
        $input = strtolower($input);

        foreach (self::$iframe_sources as $source) {
            if (preg_match($source['match'], $input)) {
                $output = preg_replace($source['match'], $source['url'], $input);
                $result = array(
                    'videoid' => $output,
                    'type'    => 'iframe',
                    'width'   => ($width + 20),
                    'height'  => ($height + 20),
                );
                return $result;
            }
        }

        return false;
    }

    public function validate_url($input) {
        $input = strtolower($input);

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
