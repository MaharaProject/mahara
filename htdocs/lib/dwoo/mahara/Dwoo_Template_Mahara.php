<?php

/**
 * base resource class for all mahara types, it calls for file name resolution if required
 * 
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class Dwoo_Template_Mahara extends Dwoo_Template_File
{
    public function __construct($file, $cacheTime = null, $cacheId = null, $compileId = null, $includePath = null)
    {
        global $THEME;

        $name = explode(':', $file, 2);

        // this is a mahara special resource, resolving path
        if (count($name) == 2) {
            list($file, $includePath) = $this->resolveFileName($name, $includePath);
        }
        
        parent::__construct($file, null, null, null, $includePath);
    }
}

?>