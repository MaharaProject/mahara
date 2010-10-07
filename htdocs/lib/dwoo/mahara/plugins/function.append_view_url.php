<?php

/**
 * Dwoo {append_view_url} function plugin
 *
 * Type:     function<br>
 * Date:     June 22, 2006<br>
 * Purpose:  See ArtefactTypeFolder::append_view_url
 * @author   Catalyst IT Ltd
 * @version  1.0
 */
function Dwoo_Plugin_append_view_url(Dwoo $dwoo, $html, $viewid=null) {
    return empty($viewid) ? $html : ArtefactTypeFolder::append_view_url($html, $viewid);
}
