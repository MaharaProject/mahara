<?php

// loading all dependencies
require 'Dwoo_Template_Mahara.php';
require 'Dwoo_Template_Mahara_Artefact.php';
require 'Dwoo_Template_Mahara_Blocktype.php';
require 'Dwoo_Template_Mahara_Export.php';
require 'Dwoo_Template_Mahara_Interaction.php';

/**
 * implements some of the Smarty interface to support old code 
 * using Smarty and sets up the Dwoo object to work with Mahara
 * 
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class Dwoo_Mahara extends Dwoo {
    
    /**
     * stores the data in the dwoo object, smarty style
     * 
     * @var array
     */
    protected $_data;
    
    /**
     * stores the templates directories
     * 
     * @var array
     */
    protected $includePath;
    
    /**
     * stores the template delimiters since some code relies on that
     */
    public $left_delimiter = '{';
    public $right_delimiter = '}';
    
    public function __construct() {
        global $THEME;

        // make sure cache/compile paths exist
        check_dir_exists(get_config('dataroot') . 'dwoo/compile/' . $THEME->basename);
        check_dir_exists(get_config('dataroot') . 'dwoo/cache/' . $THEME->basename);

        // set paths
        $this->template_dir = $THEME->templatedirs;

        $compileDir = get_config('dataroot') . 'dwoo/compile/' . $THEME->basename;
        $cacheDir = get_config('dataroot') . 'dwoo/cache/' . $THEME->basename;
        parent::__construct($compileDir, $cacheDir);
        
        // add plugins dir to the loader
        $this->getLoader()->addDirectory(get_config('libroot') . 'dwoo/mahara/plugins/');
    
        // adds mahara resources and compiler factory
        $this->setDefaultCompilerFactory('file', array($this, 'compilerFactory'));
        $this->addResource('artefact', 'Dwoo_Template_Mahara_Artefact', array($this, 'compilerFactory'));
        $this->addResource('blocktype', 'Dwoo_Template_Mahara_Blocktype', array($this, 'compilerFactory'));
        $this->addResource('export', 'Dwoo_Template_Mahara_Export', array($this, 'compilerFactory'));
        $this->addResource('interaction', 'Dwoo_Template_Mahara_Interaction', array($this, 'compilerFactory'));
        
        // set base data
        $theme_list = array();
        $themepaths = themepaths();
        foreach ($themepaths['mahara'] as $themepath) {
            $theme_list[$themepath] = $THEME->get_url($themepath);
        }

        $this->_data = array(
            'THEME' => $THEME,
            'WWWROOT' => get_config('wwwroot'),
            'THEMELIST' => json_encode($theme_list),
        );
    }
    
    /**
     * implements smarty api to assign data
     */
    public function assign($key, $value) {
        $this->_data[$key] = $value;
    }
    
    /**
     * implements smarty api to assign data
     */
    public function assign_by_ref($key, &$value) {
        $this->_data[$key] =& $value;
    }
    
    /**
     * implements smarty api to read data
     */
    public function get_template_vars($name = null) {
        if (!$name) {
            return $this->_data;
        } elseif (isset($this->_data[$name])) {
            return $this->_data[$name];
        }
    }
    
    /**
     * implements smarty api to render and display a template
     */
    public function display($file) {
        echo $this->fetch($file);
    }
    
    /**
     * implements smarty api to render and return a template's ouptut
     */
    public function fetch($file) {
        $class = 'Dwoo_Template_File';
        if (strpos($file, ':') !== false) {
            $name = explode(':', $file, 2);
            $class = 'Dwoo_Template_Mahara_'.$name[0];
            $file = $name[1];
        }
        return $this->get(new $class($file, null, null, null, $this->template_dir), $this->_data);
    }
    
    /**
     * returns a compiler object when one is required
     * 
     * @return Dwoo_Compiler
     */
    public function compilerFactory() {
        $compiler = Dwoo_Compiler::compilerFactory();

        $compiler->setDelimiters($this->left_delimiter, $this->right_delimiter);
        $compiler->setAutoEscape(true);

        return $compiler;
    }
}

?>
