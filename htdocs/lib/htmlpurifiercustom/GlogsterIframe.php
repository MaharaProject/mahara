<?php

class HTMLPurifier_Filter_GlogsterIframe extends HTMLPurifier_Filter
{
    //Defaults to half the standard iframe size
    private $default_width = '480';
    private $default_height = '650';
    private $default_scale = '50';

    private $max_scale = 100;
    private $max_width = 960;
    private $max_height = 1300;

    public $name = 'GlogsterIframe';

    public function preFilter($html, $config, $context) {
        if (preg_match_all('#(<iframe[^>]+?glogster\.com[^>]+></iframe>)#', $html, $matches)) {
            foreach ($matches[1] as $match) {
                $xml = simplexml_load_string($match);
                $width = $xml['width'] ? $xml['width']: $this->default_width;
                $height = $xml['height'] ? $xml['height'] : $this->default_height;
                $query = parse_url($xml['src'], PHP_URL_QUERY);
                parse_str($query, $parts);
                $id = $parts['glog_id'];
                $scale = $parts['scale'] ? $parts['scale'] : $this->default_scale;

                //These values all need to be set proportionally to their maximum values and to scale.
                if ($scale > $this->max_scale or $width > $this->max_width or $height > $this->max_height) {
                    $scale = $this->max_scale;
                    $height = $this->max_height;
                    $width = $this->max_width;
                }
                else {
                    //width and height need to be proportional to scale
                    $width_scale  = (int)$width/$this->max_width*100;
                    $height_scale = (int)$height/$this->max_height*100;

                    //Allowing for margin of error for calculating width/height based on scale.
                    $scale_range = range($scale-1,$scale+1);
                    $width_scale_range = range($width_scale-2, $width_scale+2);

                    //Ensure that width and height are scaled correctly,
                    //or that the scale is correctly set in the event
                    //that the width and height are within range of each other
                    if ((!in_array($width_scale, $scale_range) or !in_array($height_scale, $scale_range))
                    and !in_array($height_scale, $width_scale_range)) {
                        $width = $this->max_width*$scale/$this->max_scale;
                        $height = $this->max_height*$scale/$this->max_scale;
                    }
                    elseif (in_array($height_scale, $width_scale_range)) {
                        $scale = $width_scale;
                    }
                }

                $replace = '<span class="glogster-iframe">'.$scale.' '.$width.' '.$height.' '.$id.'</span>';
                $html = str_replace($match, $replace, $html);
            }
        }
        return $html;
    }

    public function postFilter($html, $config, $context) {
        $post_regex = '#<span class="glogster-iframe">([0-9]+) ([0-9]+(%?|px)) ([0-9]+(%?|px)) ([0-9]+)</span>#';
        $post_replace = '<iframe width="\2" height="\4" src="http://www.glogster.com/glog.php?glog_id=\6&scale=\1"></iframe>';
        return preg_replace($post_regex, $post_replace, $html);
    }
}
