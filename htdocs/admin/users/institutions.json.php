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
 * @subpackage core
 * @author     Ruslan Kabalin <ruslan.kabalin@luns.net.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2010 Lancaster University Network Services Limited
 *                      http://www.luns.net.uk
 *
 */

define('INTERNAL', 1);
define('INSTITUTIONALADMIN', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

$query  = param_variable('query', '');
$offset = param_integer('offset', 0);
$limit = 20;

// Get a list of institutions
require_once(get_config('libroot') . 'institution.php');
if (!$USER->get('admin')) { // Filter the list for institutional admins
    $filter      = $USER->get('admininstitutions');
    $showdefault = false;
}
else {
    $filter      = false;
    $showdefault = true;
}
$data = build_institutions_html($filter, $showdefault, $query, $limit, $offset, $count);
$data['count'] = $count;
$data['limit'] = $limit;
$data['offset'] = $offset;
$data['query'] = $query;

json_reply(false, array('data' => $data));
