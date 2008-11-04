<?php

class HTMLPurifier_Filter_SciVee extends HTMLPurifier_Filter
{
    
    public $name = 'SciVee';
    
    public function preFilter($html, $config, $context) {
        $pre_regex = '#<object [^>]+>.*?<embed src="http://www.scivee.tv/flash/embedPlayer.swf"[^>]+\bflashvars="(id=\d+&(?:amp;)?type=\d+)"[^>]*>\s*</embed>\s*</object>#s';
        $pre_replace = '<span class="scivee-embed">\1</span>';
        return preg_replace($pre_regex, $pre_replace, $html);
    }
    
    public function postFilter($html, $config, $context) {
        $post_regex = '#<span class="scivee-embed">(id=\d+&(?:amp;)?type=\d+)</span>#';
        $post_replace = '<object width="480" height="400" '.
            'data="http://www.scivee.tv/flash/embedPlayer.swf">'.
            '<param name="movie" value="http://www.scivee.tv/flash/embedPlayer.swf" />'.
            '<param name="allowscriptaccess" value="always" />'.
            '<param name="flashvars" value="\1" />'.
            '<!--[if IE]>'.
            '<embed src="http://www.scivee.tv/flash/embedPlayer.swf" width="480" height="400" flashvars="\1"></embed>'.
            '<![endif]-->'.
            '</object>';
        return preg_replace($post_regex, $post_replace, $html);
    }
    
}
