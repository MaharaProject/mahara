<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {display_name} function plugin
 *
 * Type:     function<br>
 * Name:     str<br>
 * Date:     June 22, 2006<br>
 * Purpose:  Display a user's name according to mahara rules
 * @author   Penny <penny@catalyst.net.nz>
 * @version  1.0
 * @param array
 * @param Smarty
 * @return Internationalized string
 */
function smarty_function_display_name($params, &$smarty) {
    static $dictionary;
    
    if (!isset($params['user']) || !is_object($params['user'])) {
        return '';
    }

    return display_name($params['user']);
}

?>
