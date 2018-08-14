<?php

/**
 * Dwoo {str} function plugin
 *
 * Type:     function<br>
 * Name:     str<br>
 * Date:     June 22, 2006<br>
 * Purpose:  Fetch internationalized strings
 * @author   Catalyst IT Ltd
 * @version  1.0
 * @return Internationalized string
 */

use Dwoo\Core;

function PluginIntval(Core $dwoo, $value) {
    return intval($value);
}
