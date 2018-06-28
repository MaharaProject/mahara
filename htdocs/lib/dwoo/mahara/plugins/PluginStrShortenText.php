<?php

/**
 * Core {str_shorten_text} function plugin
 *
 * Type:     function<br>
 * Name:     str_shorten_text<br>
 * Date:     July 10, 2007<br>
 * Purpose:  Fetch the mahara version number if in the admin section
 * @author   Catalyst IT Ltd
 * @version  1.0
 * @return html to display in the footer.
 */
use Dwoo\Core;

function PluginStrShortenText(Core $core, $str, $maxlen=100, $truncate=false) {
    return str_shorten_text($str, $maxlen=100, $truncate=false);
}

?>
