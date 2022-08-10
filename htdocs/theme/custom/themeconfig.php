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

$theme->displayname = 'Configurable Theme';
$theme->parent      = 'raw';

/* If we are using normal CSS, this should be false. If we are using SASS, it should be true. */
$theme->overrideparentcss = false;

$theme->themeaddressbar = '#ffffff';

/* Allow skins to be used on this theme */
$theme->skins = true;

 /* Limit this theme to certain institutions */
 // $theme->institutions = array('institution_a', 'institution_b');
