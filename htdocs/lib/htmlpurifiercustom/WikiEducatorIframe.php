<?php

class HTMLPurifier_Filter_WikiEducatorIframe extends HTMLPurifier_Filter
{
    private $default_width = '100%';
    private $default_height = '300';

    public $name = 'WikiEducatorIframe';

    public function preFilter($html, $config, $context) {
        if (preg_match_all('#(<iframe[^>]+?wikieducator\.org[^>]+></iframe>)#', $html, $matches)) {
            foreach ($matches[1] as $match) {
                $xml = simplexml_load_string($match);
                $width = $xml['width'] ? $xml['width']: $this->default_width;
                $height = $xml['height'] ? $xml['height'] : $this->default_height;
                $query = parse_url($xml['src'], PHP_URL_QUERY);
                parse_str($query, $parts);
                $id = '';
                $revision ='';
                if (array_key_exists('curid', $parts)) {
                    $revision = 'cur';
                    $id = $parts['curid'];
                }
                else if (array_key_exists('oldid', $parts)) {
                    $revision = 'old';
                    $id = $parts['oldid'];
                }
                else {
                    continue;
                }
                $replace = '<span class="wikieducator-iframe">'.$revision.' '.$width.' '.$height.' '.$id.'</span>';
                $html = str_replace($match, $replace, $html);
            }
        }
        return $html;
    }

    public function postFilter($html, $config, $context) {
        $post_regex = '#<span class="wikieducator-iframe">([a-z]{3}) ([0-9]+(%?|px)) ([0-9]+(%?|px)) ([0-9]+)</span>#';
        $post_replace = '<iframe width="\2" height="\4" src="http://wikieducator.org/index.php?\1id=\6"></iframe>';
        return preg_replace($post_regex, $post_replace, $html);
    }
}
