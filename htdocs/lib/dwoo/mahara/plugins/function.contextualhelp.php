<?php

/**
 * Dwoo {contextualhelp} function plugin
 *
 * Type:     function<br>
 * Date:     June 22, 2006<br>
 * Purpose:  Provide inline contextual help for arbitrary sections
 * @author   Catalyst IT Ltd
 * @version  1.0
 * @return HTML snippet for help icon
 */
function Dwoo_Plugin_contextualhelp(Dwoo $dwoo, $plugintype, $pluginname, $form = null, $element = null, $section = null, $assign = null) {
    $ret = call_user_func_array('get_help_icon', array(
        $plugintype, $pluginname, $form, $element, null, $section));

    // If there is an 'assign' parameter, place it into that instead.
    if ($assign) {
		$dwoo->assignInScope($ret, $assign);
        return;
    }

    return $ret;
}

?>
