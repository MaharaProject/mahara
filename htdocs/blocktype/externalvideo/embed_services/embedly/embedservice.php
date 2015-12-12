<?php

require_once(dirname(__FILE__) . '/../Embed_base.php');

class Embed_embedly implements EmbedBase {

    private $httpstr;
    private static $base_url;
    private static $signup_url;

    private static $default_width  = 640;
    private static $default_height = 480;

    function __construct() {
        $this->httpstr = is_https() ? 'https' : 'http';

        self::$base_url   = $this->httpstr . '://embed.ly/';
        //self::$signup_url = $this->httpstr . '://app.embed.ly/';
    }

    private static $embed_sources  = array(
        array(
            'match' => '#<a class="embedly-card".*href="(.*?)".*>(.*?)<\/a>.*#s',
            'url'   => '$1',
            'title' => '$2',
            'type'  => 'link',
        ),
        array(
            'match' => '#<blockquote class="embedly-card" (.*?)>.*href="(.*?)".*>(.*?)<\/a>.*<p>(.*?)<\/p><\/blockquote>.*#s',
            'data'  => '$1',
            'url'   => '$2',
            'title' => '$3',
            'desc'  => '$4',
            'type'  => 'card',
        ),
        array(
            'match'  => '#<div class="embedly-responsive".*style="(.*?)".*src="(.*?)".*style="(.*?)".*<\/div>.*#s',
            'style1' => '$1',
            'src'    => '$2',
            'style2' => '$3',
            'type'   => 'div', // responsive iframe
        ),
        array(
            'match'  => '#<iframe class="embedly-embed".*src="(.*?)".*<\/iframe>.*#s',
            'src'    => '$1',
            'type'   => 'iframe',
        ),
    );

    /*
     *  Returns if this embed service is enabled or not.
     */
    public function enabled() {
        return true;
    }

    /*
     *  Function that process the URL and generates HTML
     *  needed for embedding the URL.
     */
    public function process_url($input, $width=0, $height=0) {
        $width  = $width  ? (int)$width  : self::$default_width;
        $height = $height ? (int)$height : self::$default_height;
        $output = '<a class="embedly-card" href="' . $input . '"></a>';

        $result = array(
            'videoid' => $output,
            'width'   => $width,
            'height'  => $height,
            'html'    => $output,
        );
        return $result;
    }

    /*
     *  Function that checks if the URL is valid, meaning that
     *  embed service is able to generate embed code for this URL.
     */
    public function validate_url($input) {
        if (filter_var($input, FILTER_VALIDATE_URL)) {
            return true;
        }

        /*foreach (self::$embed_sources as $source) {
            if (preg_match($source['match'], $input)) {
                return true;
            }
        }*/
        return false;
    }

    /*
     *  Function that process entered embed/iframe code and
     *  prepares it for embedding into Mahara page.
     */
    public function process_content($input) {
        foreach (self::$embed_sources as $source) {
            if (preg_match($source['match'], $input)) {
                $type = $source['type'];

                $result = array();
                $result['service'] = 'embedly';
                $result['type'] = $type;

                if ($type == 'link' || $type == 'card') {
                    $result['url'] = preg_replace($source['match'], $source['url'], $input);
                    $result['title'] = preg_replace($source['match'], $source['title'], $input);
                }
                if ($type == 'card') {
                    $result['data'] = preg_replace($source['match'], $source['data'], $input);
                    $result['desc'] = preg_replace($source['match'], $source['desc'], $input);
                }
                if ($type == 'div' || $type == 'iframe') {
                    $result['src'] = preg_replace($source['match'], $source['src'], $input);
                    if (preg_match('#width="(\d+)"#', $input, $m)) {
                        $result['width'] = $m[1];
                    }
                    if (preg_match('#height="(\d+)"#', $input, $m)) {
                        $result['height'] = $m[1];
                    }
                }
                if ($type == 'div') {
                    $result['style1'] = preg_replace($source['match'], $source['style1'], $input);
                    $result['style2'] = preg_replace($source['match'], $source['style2'], $input);
                }

                return $result;
            }
        }
        return false;
    }

    /*
     *  Function that builds embed/iframe code to be
     *  embedded into Mahara page.
     */
    public function embed_content($input) {
        $width  = isset($input['width'])  ? (int)$input['width']  : self::$default_width;
        $height = isset($input['height']) ? (int)$input['height'] : self::$default_height;

        $type   = isset($input['type'])   ? $input['type']   : 'link';
        $url    = isset($input['url'])    ? $input['url']    : null;
        $title  = isset($input['title'])  ? $input['title']  : null;
        $data   = isset($input['data'])   ? $input['data']   : null;
        if ($data) {
            // We want to strip out the quotes otherwise they get escaped as
            // we can't use '|safe' in template for potentially unsafe input
            $data = str_replace('"', '', str_replace("'", "", $data));
        }
        $desc   = isset($input['desc'])   ? $input['desc']   : null;
        $src    = isset($input['src'])    ? $input['src']    : null;
        $style1 = isset($input['style1']) ? $input['style1'] : null;
        $style2 = isset($input['style2']) ? $input['style2'] : null;

        $smarty = smarty_core();

        $smarty->assign('width', $width);
        $smarty->assign('height', $height);

        $smarty->assign('type', $type);
        $smarty->assign('url', $url);
        $smarty->assign('title', $title);
        $smarty->assign('data', $data);
        $smarty->assign('desc', $desc);
        $smarty->assign('src', $src);
        $smarty->assign('style1', $style1);
        $smarty->assign('style2', $style2);
        $smarty->assign('key', get_random_key());
        return $smarty->fetch('blocktype:externalvideo:embedly.tpl');
    }

    /*
     *  Function that returns embed service base URL.
     */
    public function get_base_url() {
        return self::$base_url;
    }
}
