<?php

class HTMLPurifier_Filter_TeacherTube extends HTMLPurifier_Filter
{
    
    public $name = 'TeacherTube';
    
    public function preFilter($html, $config, $context) {
        $pre_regex = '#<embed src="http://www.teachertube.com/(player/search|skin-p)/mediaplayer.swf" [^>]+\bfile=http://www.teachertube.com/flvideo/(\d+).flv\b[^>]+></embed>#s';
        $pre_replace = '<span class="teachertube-embed">\2</span>';
        return preg_replace($pre_regex, $pre_replace, $html);
    }
    
    public function postFilter($html, $config, $context) {
        $post_regex = '#<span class="teachertube-embed">(\d+)</span>#';
        $post_replace = '<object width="425" height="350" data="http://www.teachertube.com/skin-p/mediaplayer.swf">'.
            '<param name="movie" value="http://www.teachertube.com/skin-p/mediaplayer.swf" />'.
            '<param name="flashvars" value="'.
            'height=350&width=425'.
            '&file=http://www.teachertube.com/flvideo/\1.flv'.
            '&image=http://www.teachertube.com/thumb/\1.jpg'.
            '&location=http://www.teachertube.com/skin-p/mediaplayer.swf'.
            '&logo=http://www.teachertube.com/images/greylogo.swf'.
            '&frontcolor=0xffffff&backcolor=0x000000&lightcolor=0xFF0000&screencolor=0xffffff'.
            '&autostart=false&volume=80&overstretch=fit'.
            '" />'.
            '<!--[if IE]>'.
            '<embed src="http://www.teachertube.com/skin-p/mediaplayer.swf" '.
            'width="425" height="350" type="application/x-shockwave-flash" allowfullscreen="true" '.
            'flashvars="'.
            'height=350&width=425'.
            '&file=http://www.teachertube.com/flvideo/\1.flv'.
            '&image=http://www.teachertube.com/thumb/\1.jpg'.
            '&location=http://www.teachertube.com/skin-p/mediaplayer.swf'.
            '&logo=http://www.teachertube.com/images/greylogo.swf'.
            '&frontcolor=0xffffff&backcolor=0x000000&lightcolor=0xFF0000&screencolor=0xffffff'.
            '&autostart=false&volume=80&overstretch=fit'.
            '">'.
            '</embed>'.
            '<![endif]-->'.
            '</object>';
        return preg_replace($post_regex, $post_replace, $html);
    }
    
}
