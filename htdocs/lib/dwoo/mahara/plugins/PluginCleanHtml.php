<?php

/**
 * Core {clean_html} function plugin
 *
 * Type:     function<br>
 * Name:     clean_html<br>
 * Date:     July 10, 2007<br>
 * Purpose:  Fetch the mahara version number if in the admin section
 * @author   Catalyst IT Ltd
 * @version  1.0
 * @return html to display in the footer.
 */
use Dwoo\Core;

function PluginCleanHtml(Core $core, $text, $xhtml=false) {
    return clean_html($text, $xhtml=false);
}

?>
