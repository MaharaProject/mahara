<?php

class HTMLPurifier_Filter_SlideShareIframe extends HTMLPurifier_Filter
{
    //defaults based on standard SlideShare iframe width and height
    private $default_width = 425;
    private $default_height = 355;

    //Max values set at double the defaults - seems like a sane limit
    //and it still looks fine at that size
    private $max_width = 850;
    private $max_height = 710;

    public $name = 'SlideShareIframe';

    public function preFilter($html, $config, $context) {
        if (preg_match_all('#(<iframe[^>]+?slideshare\.net/slideshow/embed_code/[^>]+></iframe>)#', $html, $matches)) {
            foreach ($matches[1] as $match) {
                $xml = simplexml_load_string($match);
                $width = $xml['width'] ? $xml['width']: $this->default_width;
                $height = $xml['height'] ? $xml['height'] : $this->default_height;
                $path = parse_url($xml['src'], PHP_URL_PATH);
                $id = '';
                if (preg_match('#/slideshow/embed_code/([0-9]+)#', $path, $code)) {
                    $id = $code[1];
                }

                if ((int)$width > $this->max_width) {
                    $width = $this->max_width;
                }

                if ((int)$height > $this->max_height) {
                    $height = $this->max_height;
                }

                $replace = '<span class="slideshare-iframe">'.$width.' '.$height.' '.$id.'</span>';
                $html = str_replace($match, $replace, $html);
            }
        }
        return $html;
    }

    public function postFilter($html, $config, $context) {
        $post_regex = '#<span class="slideshare-iframe">([0-9]+(%?|px)) ([0-9]+(%?|px)) ([0-9]+)</span>#';
        $post_replace = '<iframe width="$1" height="$3" src="http://www.slideshare.net/slideshow/embed_code/$5" frameborder="0" marginwidth="0" marginheight="0"></iframe>';
        return preg_replace($post_regex, $post_replace, $html);
    }
}
