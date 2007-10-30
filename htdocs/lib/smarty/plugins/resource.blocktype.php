<?php
/*
 * Smarty plugin
 * -------------------------------------------------------------
 * Type:     resource
 * Name:     blocktype
 * Author:   Nigel McNie <nigel@catalyst.net.nz>
 * Purpose:  Look up blocktype templates for Mahara
 *
 * Examples: $smarty->display ("blocktype:blocktypename:.tpl")
 * -------------------------------------------------------------
 */

function smarty_resource_blocktype_source ($tpl_name, &$tpl_source, &$smarty_obj) {
    if ($filename = smarty_resource_blocktype_get_filepath($tpl_name)) {
        $tpl_source = file_get_contents($filename);
        return true;
    }

    return false;
}

function smarty_resource_blocktype_timestamp($tpl_name, &$tpl_timestamp, &$smarty_obj) {
    if ($filename = smarty_resource_blocktype_get_filepath($tpl_name)) {
        $tpl_timestamp = filemtime($filename);
        return true;
    }

    return false;
}

function smarty_resource_blocktype_secure($tpl_name, &$smarty_obj) {
  // assume all templates are secure
  return true;
}

function smarty_resource_blocktype_trusted($tpl_name, &$smarty_obj)
{
  // not used for templates
}

function smarty_resource_blocktype_get_filepath($tpl_name) {
    static $filepaths = array();
    if (isset($filepaths[$tpl_name])) {
        return $filepaths[$tpl_name];
    }

    $name = explode(':', $tpl_name);

    $artefactplugin = get_field('blocktype_installed', 'artefactplugin', 'name', $name[0]);
    $template_path = $name[1];

    $basedir = get_config('docroot');
    if ($artefactplugin) {
         $basedir .= 'artefact/' . $artefactplugin . '/blocktype/' . $name[0] . '/theme/';
    }
    else {
        $basedir .= 'blocktype/' . $name[0];
    }

    foreach (theme_setup()->inheritance as $theme) {
        $filename = $basedir . $theme . '/' . $template_path;
        if (is_readable($filename)) {
            return $filepaths[$tpl_name] = $filename;
        }
    }

    return $filepaths[$tpl_name] = false;
}

// register the resource name "blocktype"
$smarty->register_resource("blocktype", array("smarty_resource_blocktype_source",
                                              "smarty_resource_blocktype_timestamp",
                                              "smarty_resource_blocktype_secure",
                                              "smarty_resource_blocktype_trusted"));
?>
