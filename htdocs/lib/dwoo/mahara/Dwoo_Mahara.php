<?php
/**
 *
 * @package    mahara
 * @subpackage dwoo
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */
namespace Mahara;

use Dwoo\Core as Core;
use Dwoo\Template\File as TemplateFile;
use Dwoo\ITemplate as ITemplate;
use Dwoo\Compiler as Compiler;

// loading all dependencies
require 'Dwoo_Template_Mahara.php';

/**
 * implements some of the Smarty interface to support old code
 * using Smarty and sets up the Dwoo object to work with Mahara
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 */
class Dwoo_Mahara extends Core {

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

        $dwoo_dir = get_dwoo_dir();
        // make sure cache/compile paths exist
        check_dir_exists($dwoo_dir . 'compile/' . $THEME->basename);
        check_dir_exists($dwoo_dir . 'cache/' . $THEME->basename);

        // set paths
        $this->template_dir = $THEME->templatedirs;

        $compileDir = $dwoo_dir . 'compile/' . $THEME->basename;
        $cacheDir = $dwoo_dir . 'cache/' . $THEME->basename;
        parent::__construct($compileDir, $cacheDir);

        // add plugins dir to the loader
        $this->getLoader()->addDirectory(get_config('libroot') . 'dwoo/mahara/plugins/');

        // adds mahara resources and compiler factory
        $this->setDefaultCompilerFactory('file', array($this, 'compilerFactory'));

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
        }
        else if (isset($this->_data[$name])) {
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
        $parts = explode(':', $file, 2);
        if (count($parts) == 2) {
            list($type, $file) = $parts;
        }
        else {
            $type = 'file';
        }

        $template = $this->templateFactory($type, $file);
        return $this->get($template, $this->_data);
    }

    /**
     * returns a compiler object when one is required
     *
     * @return Compiler
     */
    public function compilerFactory() {
        $compiler = Compiler::compilerFactory();

        $compiler->setDelimiters($this->left_delimiter, $this->right_delimiter);
        $compiler->setAutoEscape(true);

        return $compiler;
    }

    /**
     * [util function] fetches a template object of the given resource
     *
     * @param string $resourceName the resource name (i.e. file, string)
     * @param string $resourceId the resource identifier (i.e. file path)
     * @param int $cacheTime the cache time setting for this resource
     * @param string $cacheId the unique cache identifier
     * @param string $compileId the unique compiler identifier
     * @param ITemplate $parentTemplate the parent template
     * @return ITemplate
     */
    public function templateFactory($resourceName, $resourceId, $cacheTime = null, $cacheId = null, $compileId = null, ITemplate $parentTemplate = null) {
        if ($resourceName != 'file') {
            return new Dwoo_Template_Mahara("{$resourceName}:{$resourceId}", $cacheTime, $cacheId, $compileId, $this->template_dir);
        }
        else {
            return new TemplateFile($resourceId, $cacheTime, $cacheId, $compileId, $this->template_dir);
        }
    }
}
