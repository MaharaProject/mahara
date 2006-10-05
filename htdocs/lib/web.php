<?php
/**
 * This program is part of mahara
 *
 *  This program is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program; if not, write to the Free Software
 *  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301 USA
 *
 * @package    mahara
 * @subpackage core
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 * @copyright  (C) portions from Moodle, (C) Martin Dougiamas http://dougiamas.com
 */

defined('INTERNAL') || die();


/**
 * This function creates a Smarty object and sets it up for use within our
 * podclass app, setting up some variables.
 *
 * The variables that it sets up are:
 *
 * - THEMEURL: The base url for static content
 * - WWWROOT: The base url for the podclass system
 * - USER: The user object
 * - JAVASCRIPT: A list of javascript files to include in the header.  This
 *   list is passed into this function (see below).
 * - HEADERS: An array of any further headers to set.  Each header is just
 *   straight HTML (see below).
 * - ONLOAD: String of onload javascript to add
 *
 * @param array A list of javascript includes.  Each include should be just
 *              the name of a file, and reside in {$THEMEURL}js/{filename}
 * @param array A list of additional headers.  These are to be specified as
 *              actual HTML.
 * @param string $onload javascript onload content
 * @return Smarty
 */
function &smarty($javascript = array(), $headers = array(), $onload=false) {
    global $USER;

    require_once(get_config('libroot') . 'smarty/Smarty.class.php');

    $smarty =& new Smarty();

    $smarty->template_dir = get_config('docroot').'themes/'.get_config('theme').'/templates/';
    $smarty->compile_dir  = get_config('dataroot').'smarty/compile';
    $smarty->cache_dir    = get_config('dataroot').'smarty/cache';

    $smarty->assign('THEMEURL', get_config('wwwroot').'themes/'.get_config('theme').'/static/');
    $smarty->assign('WWWROOT', get_config('wwwroot'));

    $smarty->assign_by_ref('USER', $USER);

    $smarty->assign_by_ref('JAVASCRIPT', $javascript);
    $smarty->assign_by_ref('HEADERS', $headers);
    
    $smarty->assign('ONLOAD',$onload);

    return $smarty;
}

?>