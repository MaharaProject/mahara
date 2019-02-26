<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'site');
define('SECTION_PAGE', 'styleguide');

require('../init.php');

define('TITLE', get_string('styleguide_title'));

// Inline CSS to avoid cluttering up the actual themes and making sure we don't
// rely on having a certain theme enabled. We shouldn't do this normally
// but the style guide is a special case. The amount of CSS here should be minimal.

$inlinecss = <<<EOT
<style>
    section[data-markdown] {
        border: 1px solid #ccc;
        padding: 20px;
        margin-bottom: 20px;
        border-radius: 5px;
        overflow: hidden; /* create block formatting context */
    }

    [data-markdown] pre {
        margin-top: 20px;
        position: relative;
        clear: left;
    }

    section[data-markdown] h3:first-child {
        margin-top: 0;
    }

    .copy {
        position: absolute;
        top: 0;
        right: 0;
        font-size: 150%;
        border-radius: 2px;
        border-width: 0px;
        box-shadow: none;
        line-height: 1em;
        background-color: transparent;
        outline: 1px solid #ccc;
        padding-top: 5px;
        padding-bottom: 3px;
    }

    .nav-tabs {
        margin-bottom: 20px !important;
    }

    .nav-tabs a {
        text-transform: capitalize;
    }

    #scroll-to-top {
        text-align: right;
        padding-right: 40px;
    }

    #scroll-to-top.fixed {
        position: fixed;
        bottom: 40px;
    }
    .dot {
        color: #9d9d9d;
        font-size: 0.8em;
    }
    .begun {
        color: #5b9aa9;
    }
    .incomplete {
        color: #d9534f;
    }
    .partial {
        color: #f0ad4e;
    }
    .completed {
        color: #426600;
    }
}
</style>
EOT;

$smarty = smarty();

// Add a custom preprocessor to this page, to copy each sample code section, so we can have
// a Dwoo-rendered example of it followed by the unrendered sample of the code. We have to do this
// with a preprocessor rather than a custom Dwoo block like {codesample}...{/codesample},
// because the Dwoo Block API doesn't expose the uncompiled content of the block.
//
// Dwoo requires us to do add a preprocessor in a rather roundabout way, not by modifying the
// compiler directly, but by overriding the compilerFactory; the function that provides the compiler.
$smarty->setDefaultCompilerFactory(
    'file',
    // The "use" keyword is a PHP closure, which says to use the $smarty variable from
    // the page's scope, inside this anonymous function.
    function () use ($smarty) {

        // To inherit the normal Mahara Dwoo behavior, we'll retrieve the normal compiler,
        // and just add the preprocessor to it.
        $compiler = $smarty->compilerFactory();
        $compiler->addPreProcessor(
            function ($compiler, $input) {
                // Sample code is surrounded in markdown ``` delimiters.
                return preg_replace(
                    '/```(.*?)```/s',
                    '$1' . "\n"
                    . '```{literal}$1{/literal}```',
                    $input
                );
            },
            false
        );
        return $compiler;
    }
);

$smarty->assign('description', get_string('styleguide_description'));
$smarty->assign('copy', get_string('copy'));
$smarty->assign('scrollup', get_string('scroll_to_top'));
$smarty->assign('SIDEBARS', false);
$smarty->assign('ADDITIONALHTMLHEAD', $inlinecss);
$smarty->assign('wwwroot', get_config('wwwroot'));
$smarty->display('styleguide.tpl');
