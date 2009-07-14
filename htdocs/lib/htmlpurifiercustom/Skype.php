<?php

class HTMLPurifier_Filter_Skype extends HTMLPurifier_Filter
{
    
    public $name = 'Skype';
    
    public function preFilter($html, $config, $context) {
        $pre_regex = '#<script\b[^>]+?\bsrc="http://download.skype.com/share/skypebuttons/js/skypeCheck.js"[^<>]*>\s*</script>'.
            '\s*<a\b[^<>]+?\bhref="skype:([^?"<>]+)\?call"[^>]*>\s*<img\b[^<>]+?\b(src="http://(download|mystatus)\.skype\.com/[^<>]+>)\s*</a>#s';
        $pre_replace = '<span class="skype-button"><span class="skype-name">\1</span><img \2</span>';
        return preg_replace($pre_regex, $pre_replace, $html);
    }
    
    public function postFilter($html, $config, $context) {
        $post_regex = '#<span class="skype-button"><span class="skype-name">([a-zA-Z0-9_,\.-]+)</span>'.
          '(<img\s+src="http://(download|mystatus)\.skype\.com/[\s/a-zA-Z0-9"=_.:;!™-]+>)</span>#';
        $post_replace = '<script type="text/javascript" src="http://download.skype.com/share/skypebuttons/js/skypeCheck.js"></script><a href="skype:\1?call">\2</a>';
        return preg_replace($post_regex, $post_replace, $html);
    }
    
}
