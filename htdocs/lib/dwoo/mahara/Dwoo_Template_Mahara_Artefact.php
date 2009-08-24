<?php

/**
 * implements the Artefact resource type for custom plugins
 * 
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class Dwoo_Template_Mahara_Artefact extends Dwoo_Template_Mahara
{
    protected function resolveFileName(array $name, array $includePath)
    {
        global $THEME;
        
        $plugin_name = $name[0];
        $plugin_path = $name[1];
    
        $basedir = get_config('docroot') . 'artefact/' . $plugin_name . '/theme/';

        foreach ($THEME->inheritance as $theme) {
            $filename = $basedir . $theme . '/' . $plugin_path;
            if (is_readable($filename)) {
                array_unshift($includePath, $basedir . $theme . '/');
                return array($plugin_path, $includePath);
            }
        }

        throw new MaharaException('Artefact template could not be found : '.implode(':', $name));
    }
}

?>