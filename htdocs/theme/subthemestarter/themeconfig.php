<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 */

$theme = new stdClass();

/* Give your new theme a name here */
$theme->displayname = 'Sub Theme Starter kit';

/* Set parent to boolean FALSE to specify the theme has no parent */
/* If your parent theme is a subtheme, for example Modern, Default, Ocean, or Primaryschool,
you will need to uncomment the import lines with the path "../../<your parent theme name>/sass
in sass/style.scss and change the value <your parent theme name> to the folder name of the
subtheme that you use as basis. */
$theme->parent = 'raw';

/* If we are using normal CSS, this should be false. If we are using SASS, it should be true. */
$theme->overrideparentcss = true;

$theme->themeaddressbar = '#ffffff';

/**
 * The following themeconfig options are available. If you make new themeconfig
 * options please add them here and explain what they do.
 */

/* Allow skins to be used on this theme */
$theme->skins = true;

/* Limit this theme to certain institutions */
// $theme->institutions = array('institution_a', 'institution_b');
