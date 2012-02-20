<?php

define('VIDEOID_REGEXP', '[A-Za-z0-9_\-.,@+^]+');

class HTMLPurifier_Filter_YouTubeIframe extends HTMLPurifier_Filter
{

    public $name = 'YouTubeIframe';

    public function preFilter($html, $config, $context) {
        $pre_regex = '#<iframe\b[a-zA-Z0-9/"=-\s]+?\bsrc="https?://www.youtube.com/embed/('. VIDEOID_REGEXP . ')(\?[^"]*)?"[^>]*></iframe>#';
        $pre_replace = '<span class="youtube-iframe">\1</span>';
        return preg_replace($pre_regex, $pre_replace, $html);
    }

    public function postFilter($html, $config, $context) {
        $post_regex = '#<span class="youtube-iframe">('. VIDEOID_REGEXP . ')</span>#';
        return preg_replace_callback($post_regex, array($this, 'postFilterCallback'), $html);
    }

    protected function postFilterCallback($matches) {
        return '<iframe title="YouTube video player" class="youtube-player" type="text/html"'.
            'width="480" height="390" src="http://www.youtube.com/embed/'.$matches[1].
            '" frameborder="0" allowFullScreen></iframe>';
    }

}