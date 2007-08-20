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

json_headers();

$limit = param_integer('limit', 10);
$offset = param_integer('offset', 0);
$category = param_variable('category', '');

if (empty($category)) {
    $count = get_field('template', 'COUNT(*)');
    $data = get_records_array('template', 'deleted', '0', 'title', 'name,title,description,category', $offset, $limit);
}
else {
    $count = get_field('template', 'COUNT(*)', 'category', $category);
    // $data = get_records_array('template', 'category', $category, 'title', 'name,title,description,category', $offset, $limit);
    // not using get_records_array here because we can't have more than one constraint :(
    $data = get_records_sql_array('SELECT name,title,description,category FROM {template} WHERE category=? AND deleted=0', array($category), $offset, $limit);

}

if (!$data) {
    $data = array();
}

print json_encode(array(
    'count'    => $count,
    'limit'    => $limit,
    'offset'   => $offset,
    'category' => $category,
    'data'     => $data,
));

?>
