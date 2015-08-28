<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

$theme = new stdClass();

/* Give your new theme a name here */
$theme->displayname = 'Sub Theme Starter kit';

/* Set parent to boolean FALSE to specify the theme has no parent */
$theme->parent = 'raw';

/* If we are using normal CSS, this should be false. If we are using SASS, it should be true. */
$theme->overrideparentcss = true;

/**
 * The following themeconfig options are available. If you make new themeconfig
 * options please add them here and explain what they do.
 */

/* Allow skins to be used on this theme */
$theme->skins = true;

/* Limit this theme to certain institutions */
// $theme->institutions = array('institution_a', 'institution_b');
