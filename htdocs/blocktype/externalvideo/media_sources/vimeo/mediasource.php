<?php

require_once(dirname(__FILE__) . '/../Media_base.php');

class Media_vimeo implements MediaBase {

    private static $base_url = 'http://vimeo.com/';
    private static $default_width  = 400;
    private static $default_height = 225;

    private static $embed_sources = array(
        array(
                'match' => '#http://vimeo\.com/([0-9]+)#',
                'url'   => 'http://vimeo.com/moogaloop.swf?clip_id=$1&amp;server=vimeo.com&amp;show_title=0&amp;show_byline=0&amp;show_portrait=0&amp;color=00adef&amp;fullscreen=1&amp;autoplay=0&amp;loop=0'
        ),

        array(
                'match' => '#<iframe src="http://player\.vimeo\.com/video/(.+)" width="([0-9]+)" height="([0-9]+)" frameborder="([0-9]+)"></iframe>#',
                'url' => 'http://player.vimeo.com/video/$1'
        ),

        array(
                'match' => '#<embed src="http://(.+)" type="application/x-shockwave-flash" allowfullscreen="true" allowscriptaccess="always" width="([0-9]+)" height="([0-9]+)"></embed>#',
                'url' => 'http://$1'
        ),

        array(
                'match' => '#http://vimeo\.com/moogaloop\.swf\?(.+)#',
                'url' => 'http://vimeo.com/moogaloop.swf?$1'
        ),

        array(
                'match' => '#http://player\.vimeo\.com/video/([0-9]+)#',
                'url' => 'http://player.vimeo.com/video/$1'
        ),
    );


    public function process_url($input, $width=0, $height=0) {
        $width  = $width  ? (int)$width  : self::$default_width;
        $height = $height ? (int)$height : self::$default_height;
        $input = self::clean_input($input);

        foreach (self::$embed_sources as $source) {
            if (preg_match($source['match'], $input)) {
                $output = preg_replace($source['match'], $source['url'], $input);

                $result = array(
                    'videoid' => $output,
                    'type'    => 'embed',
                    'width'   => $width,
                    'height'  => $height,
                );

                if (preg_match(self::$embed_sources[4]['match'], $output)) {
                    $result['type'] = 'iframe';
                }
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

    private function clean_input($input) {
        //Strip out unnecessary tags.
        $replace = array(
            '#<p>(.+)</p>#',
            '#<object width="([0-9]+)" height="([0-9]+)">#',
            '#<param name="(.+)" value="(.+)" />#',
            '#</object>#',
        );

        $input = preg_replace($replace, '', $input);
        return $input;
    }
}
