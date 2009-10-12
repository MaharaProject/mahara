<?php

class HTMLPurifier_Filter_Twitter extends HTMLPurifier_Filter
{
    
    public $name = 'Twitter';
    
    public function preFilter($html, $config, $context) {
        $pre_regex = array(
            '#<div id="twitter_div">\s*<h2 class="sidebar-title">([^<]*)</h2>\s*<ul id="twitter_update_list">\s*</ul>\s*</div>\s*(?:<p>)?\s*'.
            '<script\b[^>]+?\bsrc="http://twitter.com/javascripts/blogger.js"[^>]*></script>\s*'.
            '<script\b[^>]+?\bsrc="http://twitter.com/statuses/user_timeline/([a-zA-Z0-9_]+).json\?callback=twitterCallback2&(?:amp;)?count=(\d+)"[^>]*></script>(?:</p>)?#s',
            '#<embed\s+src="http://twitter.com/flash/twitter_badge.swf"[^>]+?\bflashvars="([^"]+)"[^>]*>\s*(</embed>)?#s',
            '#<embed\s+src="http://static.twitter.com/flash/twitter_timeline_badge.swf"[^>]+?\bflashvars="([^"]+)"[^>]*>\s*</embed>#s',
        );
        $pre_replace = array(
            '<span class="twitter-updates-js"><span class="title">\1</span><span class="username">\2</span><span class="count">\3</span></span>',
            '<span class="twitter-badge-flash">\1</span>',
            '<span class="twitter-timeline-badge-flash">\1</span>',
        );
        return preg_replace($pre_regex, $pre_replace, $html);
    }
    
    public function postFilter($html, $config, $context) {
        $post_regex = array(
            '#<span class="twitter-updates-js"><span class="title">([^<]+)</span><span class="username">([a-zA-Z0-9_]+)</span><span class="count">(\d+)</span></span>#',
            '#<span class="twitter-badge-flash">(([a-zA-Z0-9_=&\.,-]+|&amp;)+)</span>#',
            '#<span class="twitter-timeline-badge-flash">(([a-zA-Z0-9_=&\.,-]+|&amp;)+)</span>#',
        );
        $post_replace = array(
            '<div id="twitter_div"><h2 class="sidebar-title">\1</h2><ul id="twitter_update_list"></ul></div>'.
            '<script type="text/javascript" src="http://twitter.com/javascripts/blogger.js"></script>'.
            '<script type="text/javascript" src="http://twitter.com/statuses/user_timeline/\2.json?callback=twitterCallback2&amp;count=\3"></script>',
            '<object width="176" height="176" data="http://twitter.com/flash/twitter_badge.swf">'.
            '<param name="flashvars" value="\1">'.
            '<param name="name" value="twitter_badge">'.
            '<!--[if IE]>'.
            '<embed src="http://twitter.com/flash/twitter_badge.swf" '.
            'flashvars="\1" quality="high" width="176" height="176" name="twitter_badge" '.
            'align="middle" allowScriptAccess="always" wmode="transparent" type="application/x-shockwave-flash" '.
            'pluginspage="http://www.macromedia.com/go/getflashplayer" />'.
            '<![endif]-->'.
            '</object>',
            '<object width="200" height="400" data="http://static.twitter.com/flash/twitter_timeline_badge.swf">'.
            '<param name="flashvars" value="\1">'.
            '<param name="name" value="twitter_timeline_badge">'.
            '<!--[if IE]>'.
            '<embed src="http://static.twitter.com/flash/twitter_timeline_badge.swf" '.
            'flashvars="\1" width="200" height="400" quality="high" name="twitter_timeline_badge" '.
            'align="middle" allowScriptAccess="always" type="application/x-shockwave-flash" '.
            'pluginspage="http://www.adobe.com/go/getflashplayer" />'.
            '<![endif]-->'.
            '</object>',
        );
        return preg_replace($post_regex, $post_replace, $html);
    }
    
}
