<?php

require_once(dirname(__FILE__) . '/../Media_base.php');

class Media_slideshare implements MediaBase {

    private static $base_url = 'http://slideshare.net/';

    private static $default_width  = 425;
    private static $default_height = 355;

    private static $scrape_sources = array(
        array(
            'match' => '@^https?://(www\.)?slideshare\.net/(?!slideshow/)([^/]+)/([^/?&#]+).*@',
            'url'   => 'http://www.slideshare.net/$2/$3',
        ),
    );

    public function enabled() {
        // Check that the output iframe source will be allowed by htmlpurifier
        return preg_match(get_config('iframeregexp'), 'http://www.slideshare.net/slideshow/embed_code/');
    }

    public function process_url($input, $width=0, $height=0) {
        if (empty($input)) {
            return false;
        }

        $width  = $width  ? (int)$width  : self::$default_width;
        $height = $height ? (int)$height : self::$default_height;

        foreach (self::$scrape_sources as $source) {
            if (preg_match($source['match'], $input)) {
                $output = preg_replace($source['match'], $source['url'], $input);
                if ($newurl = self::scrape_url($output)) {
                    return array(
                        'videoid' => $newurl,
                        'type'    => 'iframe',
                        'width'   => $width,
                        'height'  => $height,
                    );
                }
            }
        }
        return false;
    }

    public function validate_url($input) {
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

    private static function scrape_url($url) {
        $config = array(
            CURLOPT_URL => $url,
        );

        static $scrape_regex = '#.*?https?://(www\.)?slideshare\.net/share/(tweet|facebook|linkedin)/([0-9]+)/.*#';

        $data = mahara_http_request($config);
        if (preg_match($scrape_regex, $data->data, $matches)) {
            $slideid = $matches[3];
            return 'http://www.slideshare.net/slideshow/embed_code/' . $slideid;
        }
        return false;
    }
}
