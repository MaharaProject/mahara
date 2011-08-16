<?php

require_once(dirname(__FILE__) . '/../Media_base.php');

class Media_glogster implements MediaBase {

    private static $base_url = 'http://www.glogster.com/';

    private static $default_width  = 480;
    private static $default_height = 650;
    private static $default_scale  = 50;

    private static $embed_sources  = array(
        array(
            'match' => '#.*?glogster\.com/flash/flash_loader\.swf\?ver=(\d+).*?flashvars="([^"]+)".*#',
            'url'   => 'http://www.glogster.com/flash/flash_loader.swf?ver=$1&$2',
        ),
    );

    private static $iframe_sources = array(
        array(
            'match' => '#.*?https?://((www|edu)\.)?glogster\.com/glog\.php\?glog_id=([0-9]*).*#',
            'url'   => 'http://www.glogster.com/glog.php?glog_id=$3&scale=',
        ),
    );

    private static $scrape_sources = array(
        array(
            'match' => '#https?://([^.]*(\.edu)?)\.glogster\.com/([^/]*)/.*#',
            'url'   => 'http://$1.glogster.com/$3/',
        ),
    );

    public function process_url($input, $width=0, $height=0) {
        if (empty($input)) {
            return false;
        }

        $width  = $width  ? (int)$width  : self::$default_width;
        $height = $height ? (int)$height : self::$default_height;
        $scale  = (int) $width / self::$default_width * self::$default_scale;

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

        foreach (self::$iframe_sources as $source) {
            if (preg_match($source['match'], $input)) {
                $output = preg_replace($source['match'], $source['url'], $input);
                $result = array(
                    'videoid' => $output . $scale,
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
                return $this->process_url(self::scrape_url($output));
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

        foreach (self::$embed_sources as $source) {
            if (preg_match($source['match'], $input)) {
                return true;
            }
        }

        foreach (self::$iframe_sources as $source) {
            if (preg_match($source['match'], $input)) {
                return true;
            }
        }
        return false;
    }

    private static function scrape_url($url) {
        $config = array(
            CURLOPT_URL => $url,
        );

        $data = mahara_http_request($config);
        if (!empty($data->data)) {
            if (preg_match('#<textarea[^>]*?id="glogiframe"[^>]*>([^<]*)</textarea>#m',$data->data, $matches)) {
                $iframe = html_entity_decode($matches[1], ENT_QUOTES);
                return $iframe;
            }
        }
        return false;
    }

    public function get_base_url() {
        return self::$base_url;
    }
}
