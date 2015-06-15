<?php
/**
 *
 * @package    mahara
 * @subpackage dwoo
 * @author     Catalyst IT Ltd
 * @author     Jordi Boggiano <j.boggiano@seld.be>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

/**
 * This class is a Dwoo ITemplate class. It acts as a pass-through to the standard
 * Dwoo_Template_File class, which reads template files. What this class does is take
 * a template specifier for a Mahara plugin's template file, like
 * "blocktype:creativecommons:statement.tpl", and translate that into the relative path
 * to the template (statement.tpl) and say which directories to search for this relative
 * path in.
 *
 * The actual code that translates the Dwoo identifier into a relative filesystem path, is in the
 * "get_theme_path()" method in the plugin type's class. Most plugin types will not need to customize
 * this and can simply inherit the implementation from Plugin. If the plugin can also have template
 * files that live outside of the {$plugintype}/{$pluginname} directory, then it will need to provide
 * its own implementation of get_theme_path().
 */
class Dwoo_Template_Mahara extends Dwoo_Template_File {
    /**
     * Convert a Mahara plugin template file path into a normal template file path with extra search paths.
     *
     * @param string $pluginfile The plugintype, name, and name of file, e.g. "blocktype:clippy:index.tpl"
     * @param int $cacheTime Not used.
     * @param int $cacheId Not used.
     * @param int $compileId Not used.
     * @param array $includePath The paths to look in.
     * @throws MaharaException
     */
    public function __construct($file, $cacheTime = null, $cacheId = null, $compileId = null, $includePath = null) {
        global $THEME;

        $parts = explode(':', $file, 3);

        if (count($parts) !== 3) {
            throw new SystemException("Invalid template path \"{$file}\"");
        }

        // Keep the original string for logging purposes
        $dwooref = $file;
        list($plugintype, $pluginname, $file) = $parts;

        // Since we use $plugintype as part of a file path, we should whitelist it
        $plugintype = strtolower($plugintype);
        if (!in_array($plugintype, plugin_types())) {
            throw new SystemException("Invalid plugintype in Dwoo template \"{$dwooref}\"");
        }

        // Get the relative path for this particular plugin
        require_once(get_config('docroot') . $plugintype . '/lib.php');
        $pluginpath = call_static_method(generate_class_name($plugintype), 'get_theme_path', $pluginname);

        // Because this is a plugin template file, we don't want to include any accidental matches against
        // core template files with the same name.
        $includePath = array();

        // First look for a local override.
        $includePath[] = get_config('docroot') . "local/theme/plugintype/{$pluginpath}/templates";

        // Then look for files in a custom theme
        foreach ($THEME->inheritance as $theme) {
            $includePath[] = get_config('docroot') . "theme/{$theme}/plugintype/{$pluginpath}/templates";
        }

        // Lastly look for files in the plugin itself
        foreach ($THEME->inheritance as $theme) {
            $includePath[] = get_config('docroot') . "{$pluginpath}/theme/{$theme}/templates";

            // For legacy purposes also look for the template file loose under the theme directory.
            $includePath[] = get_config('docroot') . "{$pluginpath}/theme/{$theme}";
        }

        // Now, we instantiate this as a standard Dwoo_Template_File class.
        // We're passing in $file, which is the relative path to the file, and
        // $includePath, which is an array of directories to search for $file in.
        // We let Dwoo figure out which one actually has it.
        parent::__construct($file, null, null, null, $includePath);
    }
}
