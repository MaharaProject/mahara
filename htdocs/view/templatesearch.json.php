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
require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('libroot') . 'view.php');

$group = param_integer('group', null);
$institution = param_alphanum('institution', null);

$views = new StdClass;
$views->query       = trim(param_variable('viewquery', ''));
$views->ownerquery  = trim(param_variable('ownerquery', ''));
$views->offset      = param_integer('viewoffset', 0);
$views->limit       = param_integer('viewlimit', 10);
$views->group       = param_integer('group', null);
$views->institution = param_alphanum('institution', null);
$views->copyableby = (object) array('group' => $group, 'institution' => $institution);
if (!($group || $institution)) {
    $views->copyableby->owner = $USER->get('id');
}

View::get_templatesearch_data($views);

json_reply(false, array(
    'message' => null,
    'data' => array(
        'table'      => $views->html,
        'pagination' => $views->pagination['html'],
        'count'      => $views->count,
    )
));
