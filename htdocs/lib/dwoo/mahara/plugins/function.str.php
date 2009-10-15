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
function Dwoo_Plugin_str(Dwoo $dwoo, $tag, $section = 'mahara', $args = null, $arg1 = null, $arg2 = null, $arg3 = null, $assign = null) {
    static $dictionary;

    $params = array($tag, $section);
    
    if ($args) {
        if (!is_array($args)) {
            $args = array($args);
        }
        $params = array_merge($params, $args);
    } else if (isset($arg1)) {
        foreach (array('arg1', 'arg2', 'arg3') as $k) {
            if ($$k) {
                $params[] = $$k;
            }
        }
    }

    $ret = call_user_func_array('get_string', $params);

    // If there is an 'assign' parameter, place it into that instead.
    if (!empty($assign)) {
        $dwoo->assignInScope($ret, $assign);
        return;
    }

    return $ret;
}

?>
