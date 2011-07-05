<?php

class HTMLPurifier_Filter_VimeoIframe extends HTMLPurifier_Filter {

    public $name = 'VimeoIframe';
    private $max_width = 2000;

    public function preFilter($html, $config, $context) {
        $width = 400;
        $height = round(($width * 0.564), 0);
        $regex = '#<iframe src="http://player\.vimeo\.com/video/([^"]+)" width="([0-9]+)(px)?" height="([0-9]+)(px)?" .*></iframe>#';
        if (preg_match($regex, $html, $matches)) {
            if ($matches[2] <= $this->max_width) {
                $width = $matches[2];
                $height = round(($width * 0.564), 0);
            }

            $replace = '<span class="vimeo-iframe">' . $matches[1] . 'width=' . $width . $matches[3] . 'height=' . $height . $matches[3] . '</span>';
            return str_replace($matches[0], $replace, $html);
        }
        return $html;
    }

    public function postFilter($html, $config, $context) {
        $regex = '#<span class="vimeo-iframe">([^"]+)width=([0-9]+)(px)?height=([0-9]+)(px)?</span>#';
        if (preg_match($regex, $html, $matches)) {
            $iframe = '<iframe title="Vimeo video player" class="vimeo-player" type="text/html"';
            $iframe .= 'width="' . $matches[2] . $matches[3] . '" height="' . $matches[4] . $matches[3] . '" src="http://player.vimeo.com/video/' . $matches[1] . '"';
            $iframe .= 'frameborder="0" allowFullScreen></iframe>';
            return str_replace($matches[0], $iframe, $html);
        }
        return $html;
    }
}
