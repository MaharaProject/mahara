<?php

/**
 * implements the Blocktype resource type for custom plugins
 * 
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class Dwoo_Template_Mahara_Blocktype extends Dwoo_Template_Mahara
{
    protected static $_filePaths = array();
    
    protected function resolveFileName(array $name, array $includePath)
    {
        global $THEME;
        
        if (!isset(self::$_filePaths[$name[0]][$name[1]])) {
            $artefactplugin = blocktype_artefactplugin($name[0]);
            $template_path = $name[1];
        
            $basedir = get_config('docroot');
            if ($artefactplugin) {
                 $basedir .= 'artefact/' . $artefactplugin . '/blocktype/' . $name[0] . '/theme/';
            }
            else {
                $basedir .= 'blocktype/' . $name[0] . '/theme/';
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