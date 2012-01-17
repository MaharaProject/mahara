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
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('JSON', 1);
define('INSTITUTIONALSTAFF', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

$action = param_variable('action');

if ($action == 'search') {
    require_once('searchlib.php');

    $params = new StdClass;
    $params->query       = trim(param_variable('query', ''));
    $params->institution = param_alphanum('institution', null);
    $params->f           = param_alpha('f', null);
    $params->l           = param_alpha('l', null);
    $params->sortby      = param_alpha('sortby', 'firstname');
    $params->sortdir     = param_alpha('sortdir', 'asc');

    $offset  = param_integer('offset', 0);
    $limit   = param_integer('limit', 10);

    $data = array();
    $data['data'] = build_admin_user_search_results($params, $offset, $limit);
    $data['error'] = false;
    $data['message'] = null;
    json_reply(false, $data);
}
