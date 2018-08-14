<?php
/**
 *
 * @package    mahara
 * @subpackage dwoo
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 * This file incorporates portions of the Dwoo_Plugin_if class, covered by the
 * following copyright and permission notice:
 *
 * @author     Jordi Boggiano <j.boggiano@seld.be>
 * @copyright  Copyright (c) 2008, Jordi Boggiano
 * @license    http://dwoo.org/LICENSE   Modified BSD License
 * @link       http://dwoo.org/
 * @date       2008-10-23
 */

/**
 * A dwoo tag that displays the content in the block if the Mahara config setting matches a particular
 * value.
 *
 * See the init() function for an explanation of its parameters. It can be used in the following ways:
 * {ifconfig key=foo}bar{/ifconfig} : Same as if (get_config('foo')) { echo "bar"; }
 * {ifconfig key=foo plugintype=block pluginname=blog} : Uses get_config_plugin()
 * {ifconfig key=foo plugintype=block pluginid=12} : Uses get_config_plugin_instance()
 *
 * It can also take a compareTo tag. If present, the value of the config will be compared to it.
 *
 * Example: {ifconfig key=foo compareTo=false}
 * Same as: if (get_config('foo') == false) {
 *
 * This can be a string, integer, true, false, or null. It's compared with "==".
 *
 * This tag can also take an {else} tag, the same as {if}.
 */

namespace Dwoo\Plugins\Blocks;

use Dwoo\Compiler;
use Dwoo\IElseable;
use Dwoo\Block\Plugin as BlockPlugin;
use Dwoo\ICompilable\Block as ICompilableBlock;

class PluginIfconfig extends BlockPlugin implements ICompilableBlock, IElseable
{
    /**
     * This function does nothing. Dwoo uses it to determine by reflection what attributes the tag
     * can take.
     *
     * @param string $key The only required attribute.
     * @param string $plugintype (Optional) Supply if you want to get a plugin or plugin-instance config
     * @param string $pluginname (Optional) Supply if you want to get a plugin config
     * @param integer $pluginid (Optional) Supply if you want to get a plugin instance config
     * @param variable $compareTo (Opional) Value to compare the config to (with ==). Defaults to TRUE.
     */
    public function init($key, $plugintype=null, $pluginname=null, $pluginid=null, $compareTo=true) {
    }

    /**
     * Called when Dwoo hits the opening {ifconfig} tag.
     * @param Compiler $compiler
     * @param array $params
     * @param unknown_type $prepend
     * @param unknown_type $append
     * @param unknown_type $type
     */
    public static function preProcessing(Compiler $compiler, array $params, $prepend, $append, $type) {
        return '';
    }

    /**
     * Called when Dwoo hits the closing {ifconfig} tag. Returns a PHP string that will make the
     * appropriate get_config(), get_config_plugin() or get_config_plugin_instance() call when the
     * compiled template is executed.
     *
     * @param Compiler $compiler
     * @param array $params
     * @param unknown_type $prepend
     * @param unknown_type $append
     * @param string $content The contents between the opening and closing tags
     */
    public static function postProcessing(Compiler $compiler, array $params, $prepend, $append, $content) {
        $params = $compiler->getCompiledParams($params);
        $key = $params['key'];
        $plugintype = $params['plugintype'];
        $pluginname = $params['pluginname'];
        $pluginid = trim($params['pluginid'], '\'"');
        $compareTo = $params['compareTo'];

        if ($plugintype != 'null' && $pluginname != 'null') {
            $function = "get_config_plugin({$plugintype}, {$pluginname}, {$key})";
        }
        else if ($plugintype != 'null' && $pluginid != 'null') {
            $function = "get_config_plugin_instance({$plugintype}, {$pluginid}, {$key})";
        }
        else {
            $function = "get_config({$key})";
        }

        $pre = Compiler::PHP_OPEN."if ({$function} == {$compareTo}) {\n".Compiler::PHP_CLOSE;

        $post = Compiler::PHP_OPEN."\n}".Compiler::PHP_CLOSE;

        if (isset($params['hasElse'])) {
            $post .= $params['hasElse'];
        }

        return $pre . $content . $post;
    }
}
