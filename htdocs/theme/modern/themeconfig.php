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
$theme->displayname = 'Modern';

/* Set parent to boolean FALSE to specify the theme has no parent */
$theme->parent = 'raw';

/* If we are using normal CSS, this should be false. If we are using SASS, it should be true. */
$theme->overrideparentcss = true;

$theme->themeheadingcolor = '#545B5D'; // $view_text_heading_color
$theme->themetextcolor = '#545B5D'; // $view_text_font_color
$theme->themelinkcolor = '#1f6c88;'; // $view_link_normal_color
$theme->themefocusedlinkcolor = '#1d6781'; // $view_link_hover_color

/**
 * The following themeconfig options are available. If you make new themeconfig
 * options please add them here and explain what they do.
 */

/* Allow skins to be used on this theme */
$theme->skins = true;

/* Limit this theme to certain institutions */
// $theme->institutions = array('institution_a', 'institution_b');
