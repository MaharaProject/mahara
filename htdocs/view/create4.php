<?php
/**
 * This program is part of Mahara
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
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'view');
require(dirname(dirname(__FILE__)) . '/init.php');
require_once('pieforms/pieform.php');
require_once('pieforms/pieform/elements/calendar.php');
$smarty = smarty(array(), pieform_get_headdata_calendar(pieform_configure_calendar(array())));

$form = array(
    'name' => 'createview4',
    'elements' => array(
        'acl' => array(
            'type' => 'viewacl',
            //'defaultvalue' => array(
            //    // make something up
            //    array(
            //        'type'  => 'public',
            //        'id'    => null,
            //        'start' => null,
            //        'end'   => null
            //    ),
            //    array(
            //        'type' => 'user',
            //        'id' => 1,
            //        'start' => null,
            //        'end' => null
            //    )
            //)
        ),
        'submit' => array(
            'type' => 'submitcancel',
            'value' => array(get_string('createview'), get_string('cancel'))
        )
    )
);


$smarty->assign('createview4form', pieform($form));
$smarty->display('view/create4.tpl');

?>
