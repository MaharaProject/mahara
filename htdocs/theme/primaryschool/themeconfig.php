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

$theme->displayname = 'Primary School';

/* Set parent to boolean FALSE  to specify the theme has no parent */
$theme->parent      = 'raw';

/* This theme includes all css via sass, so we don't need raw's css. */
$theme->overrideparentcss = true;

$theme->themelinkcolor = '#0162a7'; // $view_link_normal_color
$theme->themefocusedlinkcolor = '#01528c'; // $view_link_hover_color


/**
 * The following themeconfig options are available. If you make new themeconfig
 * options please add them here and explain what they do.
 */

/* Allow skins to be used on this theme */
$theme->skins = true;

/* Limit this theme to certain institutions */
// $theme->institutions = array('institution_a', 'institution_b');
