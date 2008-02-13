<?php
/**
 * Smarty plugin
 * @package Smarty
 * @subpackage plugins
 */


/**
 * Smarty {str} function plugin
 *
 * Type:     function<br>
 * Name:     str<br>
 * Date:     June 22, 2006<br>
 * Purpose:  Fetch internationalized strings
 * @author   Martyn Smith <martyn@catalyst.net.nz>
 * @version  1.0
 * @param array
 * @param Smarty
 * @return Internationalized string
 */
function smarty_function_str($params, &$smarty) {
    static $dictionary;
    
    if (!isset($params['section'])) {
        $params['section'] = 'mahara';
    }

    $args = array($params['tag'],$params['section']);
    if (isset($params['args'])) {
        if (!is_array($params['args'])) {
            $params['args'] = array($params['args']);
        }
        $args = array_merge($args,$params['args']);
    }

    $ret = call_user_func_array('get_string', $args);

    // If there is an 'assign' parameter, place it into that instead.
    if (!empty($params['assign'])) {
        $smarty->assign($params['assign'], $ret);
        return;
    }

    return $ret;
}

?>
