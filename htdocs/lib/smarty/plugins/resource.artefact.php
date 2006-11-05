<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     resource
 * Name:     artefact
 * Author:   Martyn Smith <martyn@catalyst.net.nz>
 * Purpose:  Look up artefact templates for Mahara
 *
 * Examples: $smarty->display ("artefact:internal:profile/index.tpl")
 * -------------------------------------------------------------
 */

function smarty_resource_artefact_source ($tpl_name, &$tpl_source, &$smarty_obj) {
    log_debug("source($tpl_name, $tpl_source, $smarty_obj)");

    $name = explode(':', $tpl_name);

    $plugin_name = $name[0];
    $plugin_path = $name[1];


    $basedir = get_config('docroot') . 'artefact/' . $plugin_name . '/theme/';

    foreach (theme_setup()->inheritance as $theme) {
        $filename = $basedir . $theme . '/' . $plugin_path;
        if (is_readable($filename)) {
            $tpl_source = file_get_contents($filename);
            return true;
        }
    }

    return false;
}

function smarty_resource_artefact_timestamp($tpl_name, &$tpl_timestamp, &$smarty_obj) {
    log_debug("timestamp($tpl_name,$tpl_timestamp,$smarty_obj)");

    $name = explode(':', $tpl_name);

    $plugin_name = $name[0];
    $plugin_path = $name[1];

    $basedir = get_config('docroot') . 'artefact/' . $plugin_name . '/theme/';

    foreach (theme_setup()->inheritance as $theme) {
        $filename = $basedir . $theme . '/' . $plugin_path;
        if (is_readable($filename)) {
            $tpl_timestamp = filemtime($filename);
            return true;
        }
    }

    return false;
}

function smarty_resource_artefact_secure($tpl_name, &$smarty_obj) {
  log_debug("secure($tpl_name,$smarty_obj)");

  // assume all templates are secure
  return true;
}

function smarty_resource_artefact_trusted($tpl_name, &$smarty_obj)
{
  // not used for templates
}

// register the resource name "artefact"
$smarty->register_resource("artefact", array("smarty_resource_artefact_source",
                                             "smarty_resource_artefact_timestamp",
                                             "smarty_resource_artefact_secure",
                                             "smarty_resource_artefact_trusted"));
?>
