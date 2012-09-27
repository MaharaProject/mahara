<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2012 Catalyst IT Ltd and others; see:
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
 * @author     Hugh Davenport <hugh@catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('INSTITUTIONALSTAFF', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require(get_config('libroot') . 'registration.php');

$limit  = param_integer('limit', 10);
$offset = param_integer('offset', 0);
$extradata = json_decode(param_variable('extradata'));
$institution = (isset($extradata->institution) ? $extradata->institution : 'mahara');

$type = param_alpha('type', 'users');
$subpages = array('users', 'views', 'content', 'historical');
if (!in_array($type, $subpages)) {
    $type = 'users';
}

if ($type == 'historical') {
    $field = (isset($extradata->field) ? $extradata->field : 'count_members');
}

$institutiondata = institution_statistics($institution, true);

switch ($type) {
case 'historical':
    $data = institution_historical_stats_table($limit, $offset, $field, $institutiondata);
    break;
case 'content':
    $data = institution_content_stats_table($limit, $offset, $institutiondata);
    break;
case 'views':
    $data = institution_view_stats_table($limit, $offset, $institutiondata);
    break;
case 'users':
default:
    $data = institution_user_stats_table($limit, $offset, $institutiondata);
}

json_reply(false, (object) array('message' => false, 'data' => $data));
