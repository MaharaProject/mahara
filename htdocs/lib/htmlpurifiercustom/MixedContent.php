<?php
class HTMLPurifier_URIFilter_MixedContent extends HTMLPurifier_URIFilter {

    public $name = 'MixedContent';

    public function filter(&$uri, $config, $context) {
        $currenttoken = $context->get('CurrentToken');
        // Make sure that if we're an HTTPS site, then the URI link is also HTTPS
        // Unless the link is a static <A HREF> link
        $fixscheme = true;
        if ($currenttoken->name == 'a') {
            if (isset($currenttoken->attr) &&
                isset($currenttoken->attr['class']) &&
                preg_match('/embedly-card/', $currenttoken->attr['class'])) {
                $fixscheme = true;
            }
            else {
                $fixscheme = false;
            }
        }

        if (is_https() && $uri->scheme == 'http' && $fixscheme) {
            // Convert it to a protocol-relative URL
            $uri->scheme = null;
        }
        return $uri;
    }
}
