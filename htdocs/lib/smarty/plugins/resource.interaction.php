<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     resource
 * Name:     interaction
 * Author:   Nigel McNie <nigel@catalyst.net.nz>
 * Purpose:  Look up interaction templates for Mahara
 *
 * Examples: $smarty->display ("interaction:forum:index.tpl")
 * -------------------------------------------------------------
 */

function smarty_resource_interaction_source ($tpl_name, &$tpl_source, &$smarty_obj) {
    $name = explode(':', $tpl_name);

    $plugin_name = $name[0];
    $plugin_path = $name[1];


    $basedir = get_config('docroot') . 'interaction/' . $plugin_name . '/theme/';

    foreach (theme_setup()->inheritance as $theme) {
        $filename = $basedir . $theme . '/' . $plugin_path;
        if (is_readable($filename)) {
            $tpl_source = file_get_contents($filename);
            return true;
        }
    }

    return false;
}

function smarty_resource_interaction_timestamp($tpl_name, &$tpl_timestamp, &$smarty_obj) {
    $name = explode(':', $tpl_name);

    $plugin_name = $name[0];
    $plugin_path = $name[1];

    $basedir = get_config('docroot') . 'interaction/' . $plugin_name . '/theme/';

    foreach (theme_setup()->inheritance as $theme) {
        $filename = $basedir . $theme . '/' . $plugin_path;
        if (is_readable($filename)) {
            $tpl_timestamp = filemtime($filename);
            return true;
        }
    }

    return false;
}

function smarty_resource_interaction_secure($tpl_name, &$smarty_obj) {
  // assume all templates are secure
  return true;
}

function smarty_resource_interaction_trusted($tpl_name, &$smarty_obj)
{
  // not used for templates
}

// register the resource name "interaction"
$smarty->register_resource("interaction", array("smarty_resource_interaction_source",
                                             "smarty_resource_interaction_timestamp",
                                             "smarty_resource_interaction_secure",
                                             "smarty_resource_interaction_trusted"));
?>
