<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

$theme = new StdClass;

$theme->displayname  = 'Ocean';
$theme->formrenderer = 'div';

/* Set parent to boolean FALSE  to specify the theme has no parent */
/* Currently Ocean cannot be a parent theme for subtheme */
$theme->parent      = 'raw';

/* This theme includes all css via sass, so we don't need raw's css. */
$theme->overrideparentcss = true;

/**
 * There are more themeconfig options available. For an explanation of
 * all the themeconfig options please look in theme/default/themeconfig.php
 */
