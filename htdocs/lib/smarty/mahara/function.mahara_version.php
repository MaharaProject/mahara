<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {mahara_version} function plugin
 *
 * Type:     function<br>
 * Name:     mahara_version<br>
 * Date:     July 10, 2007<br>
 * Purpose:  Fetch the mahara version number if in the admin section
 * @author   Catalyst IT Ltd
 * @version  1.0
 * @param array
 * @param Smarty
 * @return html to display in the footer.
 */
function smarty_function_mahara_version($params, &$smarty) {
    global $USER;
    if (!defined('ADMIN') || !$USER->get('admin')) {
        return '';
    }
    return '<div id="version">Mahara version ' . get_config('release') . ' (' . get_config('version') . ')</div>';
}

?>
