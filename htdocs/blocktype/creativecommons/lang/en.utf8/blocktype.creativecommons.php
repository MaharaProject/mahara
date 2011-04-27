<?php
/**
 * Creative Commons License Block type for Mahara
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package    mahara
 * @subpackage blocktype-creativecommons
 * @author     Francois Marier <francois@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2009 Catalyst IT Ltd
 *
 */

defined('INTERNAL') || die();

$string['title'] = 'Creative Commons License';
$string['description'] = 'Attach a Creative Commons license to your page';
$string['blockcontent'] = 'Block Content';

$string['alttext'] = 'Creative Commons License';
$string['cclicensename'] = 'Creative Commons %s 3.0 Unported';
$string['cclicensestatement'] = "%s by %s is licensed under a %s license.";
$string['otherpermissions'] = 'Permissions beyond the scope of this license may be available from %s.';
$string['sealalttext'] = 'This license is acceptable for Free Cultural Works.';

$string['config:noncommercial'] = 'Allow commercial uses of your work?';
$string['config:noderivatives'] = 'Allow modifications of your work?';
$string['config:sharealike'] = 'Yes, as long as others share alike';

$string['by'] = 'Attribution';
$string['by-sa'] = 'Attribution-Share Alike';
$string['by-nd'] = 'Attribution-No Derivative Works';
$string['by-nc'] = 'Attribution-Noncommercial';
$string['by-nc-sa'] = 'Attribution-Noncommercial-Share Alike';
$string['by-nc-nd'] = 'Attribution-Noncommercial-No Derivative Works';
