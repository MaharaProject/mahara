<?php

require_once(dirname(__FILE__) . '/../Media_base.php');

class Media_voki implements MediaBase {

    private static $base_url = 'http://www.voki.com/';
    private static $default_width  = 200;
    private static $default_height = 267;

    private static $scrape_sources = array(
        array(
            'match' => '#http://www\.voki\.com/pickup\.php\?(partnerid=symbaloo&)?scid=([0-9]+)#',
            'url' => 'http://voki.com/php/checksum/scid=$2'
        ),

        array(
            'match' => '#http://www\.voki\.com/pickup\.php\?(partnerid=symbaloo&)?scid=([0-9]+)&height=([0-9]+)&width=([0-9]+)#',
            'url' => 'http://voki.com/php/checksum/scid=$2/height=$3/width=$4'
        ),
    );

    private static $embed_sources = array(
        array(
            'match' => '#.*http://vhss(-d)?\.oddcast\.com/vhss_editors/voki_player\.swf\?(.+)(%26)?sc%3d([0-9]+).*#',
            'url' => 'http://vhss$1.oddcast.com/vhss_editors/voki_player.swf?$2$3sc%3D$4'
        ),
    );

    public function process_url($input, $width=0, $height=0) {
        $width  = $width  ? (int)$width  : self::$default_width;
        $height = $height ? (int)$height : self::$default_height;
        $input = strtolower($input);

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

        foreach (self::$scrape_sources as $source) {
            if (preg_match($source['match'], $input)) {
                $output = preg_replace($source['match'], $source['url'], $input);
                return self::process_url(self::scrape_url($output));
            }
        }
        return false;
    }

    public function validate_url($input) {
        $input = strtolower($input);
        foreach (self::$embed_sources as $source) {
            if (preg_match($source['match'], $input)) {
                return true;
            }
        }

        foreach (self::$scrape_sources as $source) {
            if (preg_match($source['match'], $input)) {
                return true;
            }
        }

        return false;
    }

    public function get_base_url() {
        return self::$base_url;
    }

    private function scrape_url($url) {
        $request = array(
            CURLOPT_URL => $url,
        );

        $return = mahara_http_request($request);

        $regex = '#<param name="movie" value="http://vhss(-d)?\.oddcast\.com/vhss_editors/voki_player\.swf\?doc=(.+)chsm%3d([0-9a-z]+)%26sc%3d([0-9]+)">#U';
        if (preg_match($regex, strtolower($return->data), $matches)) {
            return 'http://vhss' . $matches[1] . '.oddcast.com/vhss_editors/voki_player.swf?' . 'doc=' . $matches[2] . 'chsm%3D' . $matches[3] . '%26sc%3D' . $matches[4];
        }

        return false;
    }
}
