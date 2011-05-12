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
 * @subpackage artefact-comment
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('PUBLIC', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('libroot') . 'view.php');
require_once(get_config('libroot') . 'pieforms/pieform.php');
safe_require('artefact', 'comment');

$extradata = json_decode(param_variable('extradata'));

if (!can_view_view($extradata->view)) {
    json_reply('local', get_string('noaccesstoview', 'view'));
}
if (!empty($extradata->artefact) && !artefact_in_view($extradata->artefact, $extradata->view)) {
    json_reply('local', get_string('accessdenied', 'error'));
}

$limit    = param_integer('limit', 10);
$offset   = param_integer('offset');

if (!empty($extradata->artefact)) {
    $artefact = artefact_instance_from_id($extradata->artefact);
}

$view = new View($extradata->view);
$data = ArtefactTypeComment::get_comments($limit, $offset, null, $view, $artefact);

json_reply(false, array('data' => $data));
