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
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('JSON', 1);
define('PUBLIC', 1);

require(dirname(dirname(__FILE__)) . '/init.php');

json_headers();

$plugintype = param_alpha('plugintype');
$pluginname = param_alpha('pluginname');
$page       = param_alphanumext('page', null);
$section    = param_alphanumext('section', null);
$form       = param_alphanumext('form', null);
$element    = param_alphanumext('element', null);

$data = get_helpfile($plugintype, $pluginname, $form, $element, $page, $section);

if (empty($data)) {
    json_reply('local', get_string('nohelpfound'));
}

$json = array('error' => false, 'content' => $data);
echo json_encode($json);
exit;


?>
