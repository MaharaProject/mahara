<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2009 Catalyst IT Ltd and others; see:
 *                         http://wiki.mahara.org/Contributors
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
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

$public = (int) param_boolean('public');

$result = array();

//$menuitems = get_records_array('site_menu','public',$public,'displayorder');
$menuitems = get_records_sql_array('
   SELECT
      s.*, a.title AS filename
   FROM {site_menu} s
      LEFT OUTER JOIN {artefact} a ON s.file = a.id
   WHERE
      s.public = ?
   ORDER BY s.displayorder', array($public));
$rows = array();
if ($menuitems) {
    foreach ($menuitems as $i) {
        $r = array();
        $r['id'] = $i->id;
        $r['name'] = $i->title;
        if (empty($i->url) && !empty($i->file)) {
            $r['type'] = 'sitefile';
            $r['linkedto'] = get_config('wwwroot') . 'artefact/file/download.php?file=' . $i->file; 
            $r['linktext'] = $i->filename; 
            $r['file'] = $i->file; 
        }
        else if (!empty($i->url) && empty($i->file)) {
            $r['type'] = 'externallink';
            $r['linkedto'] = $i->url;
            $r['linktext'] = $i->url; 
        }
        else {
            json_reply('local',get_string('loadmenuitemsfailed','admin'));
        }
        $rows[] = $r;
    }
}

$result['menuitems'] = array_values($rows);
$result['error'] = false;
$result['message'] = false;

json_headers();
echo json_encode($result);
