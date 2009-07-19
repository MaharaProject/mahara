<?php

class HTMLPurifier_Filter_SlideShare extends HTMLPurifier_Filter
{

    public $name = 'SlideShare';

    public function preFilter($html, $config, $context) {
        $pre_regex = '#<embed\b[^>]+\bsrc="http://static\.slideshare(\.net|cdn\.com)/swf/ssplayer2\.swf\?(doc=[a-z0-9-]+)[^>]+>\s*</embed>#s';
        $pre_replace = '<span class="slideshare-embed">\2</span>';
        return preg_replace($pre_regex, $pre_replace, $html);
    }

    public function postFilter($html, $config, $context) {
        $post_regex = '#<span class="slideshare-embed">(doc=[a-z0-9-]+)</span>#';
        $post_replace = '<object width="400" height="355" data="http://static.slidesharecdn.com/swf/ssplayer2.swf?\1">'.
            '<param name="movie" value="http://static.slidesharecdn.com/swf/ssplayer2.swf?\1" />'.
            '<!--[if IE]>'.
            '<embed style="width:400px; height:355px;" '.
            'id="VideoPlayback" '.
            'type="application/x-shockwave-flash" '.
            'src="http://static.slidesharecdn.com/swf/ssplayer2.swf?\1" '.
            'flashvars="" '.
            '</embed>'.
            '<![endif]-->'.
            '</object>';
        return preg_replace($post_regex, $post_replace, $html);
        return $html;
    }

}

