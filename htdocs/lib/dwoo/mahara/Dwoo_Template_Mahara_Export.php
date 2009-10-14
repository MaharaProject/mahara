<?php

/**
 * implements the Export resource type
 * 
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class Dwoo_Template_Mahara_Export extends Dwoo_Template_Mahara
{
    protected static $_filePaths = array();
    
    protected function resolveFileName(array $name, array $includePath)
    {
        global $THEME;
        
        if (!isset(self::$_filePaths[$name[0]][$name[1]])) {
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
        
            foreach ($THEME->inheritance as $theme) {
                $filename = $basedir . $theme . '/' . $template_path;
                if (is_readable($filename)) {
                    array_unshift($includePath, $basedir . $theme . '/');
                    return self::$_filePaths[$name[0]][$name[1]] = array($template_path, $includePath);
                }
            }

            self::$_filePaths[$name[0]][$name[1]] = false;
        }
        
        if (!self::$_filePaths[$name[0]][$name[1]]) {
            throw new MaharaException('Blocktype template could not be found : '.implode(':', $name));
        }

        return self::$_filePaths[$name[0]][$name[1]];
    }
}

?>