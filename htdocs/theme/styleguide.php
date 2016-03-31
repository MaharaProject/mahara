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
    }

    [data-markdown] pre {
        margin-top: 20px;
        position: relative;
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
}
</style>
EOT;

$smarty = smarty();
$smarty->assign('description', get_string('styleguide_description'));
$smarty->assign('copy', get_string('copy'));
$smarty->assign('scrollup', get_string('scroll_to_top'));
$smarty->assign('SIDEBARS', false);
$smarty->assign('ADDITIONALHTMLHEAD', $inlinecss);
$smarty->display('styleguide.tpl');
