<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     resource
 * Name:     export
 * Author:   Catalyst IT Ltd
 * Purpose:  Look up export templates for Mahara
 *
 * Examples: $smarty->display ("export:leap:entry.tpl")
 *           $smarty->display ("export:leap/file:entry.tpl")
 * -------------------------------------------------------------
 */

function smarty_resource_export_source ($tpl_name, &$tpl_source, &$smarty_obj) {
    if ($filename = smarty_resource_export_get_filepath($tpl_name)) {
        $tpl_source = file_get_contents($filename);
        return true;
    }

    return false;
}

function smarty_resource_export_timestamp($tpl_name, &$tpl_timestamp, &$smarty_obj) {
    if ($filename = smarty_resource_export_get_filepath($tpl_name)) {
        $tpl_timestamp = filemtime($filename);
        return true;
    }

    return false;
}

function smarty_resource_export_secure($tpl_name, &$smarty_obj) {
  // assume all templates are secure
  return true;
}

function smarty_resource_export_trusted($tpl_name, &$smarty_obj)
{
  // not used for templates
}

function smarty_resource_export_get_filepath($tpl_name) {
    static $filepaths = array();
    if (isset($filepaths[$tpl_name])) {
        return $filepaths[$tpl_name];
    }

    $name = explode(':', $tpl_name);

    $plugin        = $name[0];
    $template_path = $name[1];

    $basedir = get_config('docroot');
    $pluginbits = explode('/', $plugin);
    if (count($pluginbits) == 2) {
        $basedir .= 'artefact/' . $pluginbits[1] . '/export/' . $pluginbits[0] . '/theme/';
    }
    else {
        $basedir .= 'export/' . $plugin . '/theme/';
    }

    foreach (theme_setup()->inheritance as $theme) {
        $filename = $basedir . $theme . '/' . $template_path;
        if (is_readable($filename)) {
            return $filepaths[$tpl_name] = $filename;
        }
    }

    return $filepaths[$tpl_name] = false;
}

// register the resource name "export"
$smarty->register_resource("export", array("smarty_resource_export_source",
                                              "smarty_resource_export_timestamp",
                                              "smarty_resource_export_secure",
                                              "smarty_resource_export_trusted"));
?>
