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
 * Purpose:  Provide inline contextual help for arbitrary sections
 * @author   Penny Leach <penny@catalyst.net.nz>
 * @version  1.0
 * @param array
 * @param Smarty
 * @return HTML snippet for help icon
 */
function smarty_function_contextualhelp($params, &$smarty) {
    $form = (isset($params['form'])) ? $params['form'] : null; 
    $element = (isset($params['element'])) ? $params['element'] : null;
    $section = (isset($params['section'])) ? $params['section'] : null;

    $ret = call_user_func_array('get_help_icon', array(
        $params['plugintype'], $params['pluginname'], $form, $element, null, $section));

    // If there is an 'assign' parameter, place it into that instead.
    if (!empty($params['assign'])) {
        $smarty->assign($params['assign'], $ret);
        return;
    }

    return $ret;
}

?>
