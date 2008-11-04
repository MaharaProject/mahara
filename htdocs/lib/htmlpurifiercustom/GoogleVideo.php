<?php

class HTMLPurifier_Filter_GoogleVideo extends HTMLPurifier_Filter
{
    
    public $name = 'GoogleVideo';
    
    public function preFilter($html, $config, $context) {
        $pre_regex = '#<embed\b[^>]+\bsrc="http://video.google.com/googleplayer.swf\?(doc[iI]d=[0-9\-]+(?:&(?:amp;)?hl=[a-z][a-z])?)[^>]+>\s*</embed>#s';
        $pre_replace = '<span class="googlevideo-embed">\1</span>';
        return preg_replace($pre_regex, $pre_replace, $html);
    }
    
    public function postFilter($html, $config, $context) {
        $post_regex = '#<span class="googlevideo-embed">(doc[iI]d=[0-9\-]+(?:&(?:amp;)?hl=[a-z][a-z])?)</span>#';
        $post_replace = '<object width="400" height="326" data="http://video.google.com/googleplayer.swf?\1">'.
            '<param name="movie" value="http://video.google.com/googleplayer.swf?\1" />'.
            '<!--[if IE]>'.
            '<embed style="width:400px; height:326px;" '.
            'id="VideoPlayback" '.
            'type="application/x-shockwave-flash" '.
            'src="http://video.google.com/googleplayer.swf?\1" '.
            'flashvars="" '.
            '</embed>'.
            '<![endif]-->'.
            '</object>';
        return preg_replace($post_regex, $post_replace, $html);
    }
    
}

