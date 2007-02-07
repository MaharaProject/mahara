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
 * @author     Martyn Smith <martyn@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('JSON', 1);
require(dirname(dirname(__FILE__)) . '/init.php');

if ($USER->is_logged_in()) {
    $prefix = get_config('dbprefix');

    $result = get_records_sql_array("
        SELECT tag, SUM(count) AS count
        FROM (
            SELECT tag,COUNT(*) AS count FROM {$prefix}artefact_tag t INNER JOIN {$prefix}artefact a ON t.artefact=a.id WHERE a.owner=? GROUP BY 1
            UNION ALL
            SELECT tag,COUNT(*) AS count FROM {$prefix}view_tag t INNER JOIN {$prefix}view v ON t.view=v.id WHERE v.owner=? GROUP BY 1
        ) tags
        GROUP BY tag 
        ORDER BY LOWER(tag)
        ",
        array($USER->get('id'), $USER->get('id'))
    );
}

if (empty($result)) {
    $result = array();
}

json_headers();
print json_encode($result);

?>
