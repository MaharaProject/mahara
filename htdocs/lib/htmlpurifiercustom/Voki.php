<?php

class HTMLPurifier_Filter_Voki extends HTMLPurifier_Filter
{
    public $name = 'Voki';

    public function preFilter($html, $config, $context) {
        $pre_regex = '#<script\b[^>]+?\bsrc="(?:http:|https:)?\/\/vhss-d.oddcast.com\/voki_embed_functions.php"[^<>]*>\s*<\/script>\s*<script\b[^>]+?>'.
              '(AC_Voki_Embed\((?:[^<>,]*,){6}(?:[^<>,]*)(?:,\s)?([^<>]*)?\);)<\/script>#s';
        // span new_voki shows if it is a new voki embed code,
        // function will have  8 parameters and the last one will be = 1
        $pre_replace = '<span class="voki-embed"><span class="function">\1</span><span class="new_voki">\2</span></span>';
        return preg_replace($pre_regex, $pre_replace, $html);
    }

    public function postFilter($html, $config, $context) {
      // if its a new voki embed code, create normal code
      if ( strpos( $html, '<span class="new_voki">1</span>' ) !== false ) {
          $post_regex = '#<span class="voki-embed"><span class="function">(AC_Voki_Embed\([^<>]*;)</span><span class="new_voki">1</span></span>#';
          $post_replace = '<script language="JavaScript" type="text/javascript" src="//vhss-d.oddcast.com/voki_embed_functions.php"></script>'.
              '<script id="initvoki" language="JavaScript" type="text/javascript">'.
              '\1</script>';
      }
      else {
          // if its not new voki embed code, add a blank space before the function,
          // to deal with voki bug when there is new and old voki embed code in same page
          $post_regex = '#<span class="voki-embed"><span class="function">(AC_Voki_Embed\([^<>]*;)</span><span class="new_voki">(?:[^<>]*)?</span></span>#';
          $post_replace = '<script language="JavaScript" type="text/javascript" src="//vhss-d.oddcast.com/voki_embed_functions.php"></script>'.
              '<script language="JavaScript" type="text/javascript">'.
              'Old_\1</script>';
      }
        return preg_replace($post_regex, $post_replace, $html);
    }
}
