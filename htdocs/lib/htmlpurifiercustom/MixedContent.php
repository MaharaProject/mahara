<?php
class HTMLPurifier_URIFilter_MixedContent extends HTMLPurifier_URIFilter {

    public $name = 'MixedContent';

    public function filter(&$uri, $config, $context) {
      // Make sure that if we're an HTTPS site, the iframe is also HTTPS
       if (is_https() && $uri->scheme == 'http') {
           // Convert it to a protocol-relative URL
           $uri->scheme = null;
       }
       return $uri;
    }
}
