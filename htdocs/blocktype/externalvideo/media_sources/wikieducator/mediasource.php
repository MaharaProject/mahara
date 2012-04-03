<?php

require_once(dirname(__FILE__) . '/../Media_base.php');

class Media_wikieducator implements MediaBase {

    private static $base_url = 'http://wikieducator.org/';

    private static $default_width  = '100%';
    private static $default_height = 300;

    private static $max_percent_width = 100;

    private static $iframe_sources = array(
        array(
            'match' => '#^https?://(www\.)?wikieducator\.org/index\.php\?(old|cur)id=([0-9]+).*#',
            'url'   => 'http://wikieducator.org/index.php?$2id=$3',
        ),
    );

    private static $scrape_sources = array(
        array(
            'match' => '#^https?://(www\.)?wikieducator\.org/([a-zA-Z0-9_\-+:%/]+).*#',
            'url'   => 'http://wikieducator.org/$2',
        ),
    );

    public function enabled() {
        // Check that the output iframe source will be allowed by htmlpurifier
        return preg_match(get_config('iframeregexp'), 'http://wikieducator.org/index.php');
    }

    public function process_url($input, $width=0, $height=0) {
        $width  = $width  ? $width  : self::$default_width;
        $height = $height ? (int)$height : self::$default_height;

        if (strpos($width, '%') && !((int)$width <= self::$max_percent_width)) {
            $width = self::$max_percent_width . '%';
        }

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
        foreach (self::$iframe_sources as $source) {
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

    private static function scrape_url($url) {
        $config = array(
            CURLOPT_URL => $url,
        );

        $data = mahara_http_request($config);
        if (!empty($data->data)) {
            if (preg_match('#.*var *wgArticleId *= *"?([0-9]+)"?;.*#',$data->data, $matches)) {
                $newurl = self::$base_url . 'index.php?curid=' . $matches[1];
                return $newurl;
            }
        }
        return false;
    }
}
