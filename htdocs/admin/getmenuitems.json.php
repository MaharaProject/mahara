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
 * @subpackage admin
 * @author     Richard Mansfield <richard.mansfield@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
require(dirname(dirname(__FILE__)) . '/init.php');

$public = (int) param_boolean('public');

$result = array();

try { 
    $menuitems = get_records('site_menu','public',$public,'displayorder');
    // @todo: Get all the filenames of the files referred to in the $menuitems records.
    // (files table doesn't exist yet)
    $rows = array();
    foreach ($menuitems as $i) {
        $r = array();
        $r['id'] = $i->id;
        $r['name'] = $i->title;
        if (empty($i->url) && !empty($i->file)) {
            $r['type'] = 'adminfile';
            $r['linkedto'] = $i->file; // @todo: substitute the appropriate filename.
            // $r['link'] = ''; // @todo: provide a link to the file
        }
        else if (!empty($i->url) && empty($i->file)) {
            $r['type'] = 'externallink';
            $r['linkedto'] = $i->url;
            // $r['link'] = $i->url;
        }
        else {
            throw new Exception ('Exactly one of {file,url} should be set in site_menu table');
        }
        $rows[] = $r;
    }
    $result['menuitems'] = array_values($rows);
    $result['error'] = false;
    $result['message'] = get_string('sitemenuloaded');
}
catch (Exception $e) {
    $result['error'] = 'local';
    $result['message'] = get_string('failedloadingsitemenu');
}

echo json_encode($result);
?>
