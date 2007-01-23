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
 * @author     Richard Mansfield <richard.mansfield@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(__FILE__)) . '/init.php');

json_headers();

$data = new StdClass;

$data->view       = param_integer('view');
$data->artefact   = param_integer('artefact', null);
$data->message    = param_variable('message');
$data->public     = param_boolean('public') ? 1 : 0;
$data->attachment = param_integer('attachment', null);
$data->author     = $USER->get('id');
$data->ctime      = db_format_timestamp(time());

if ($data->artefact) {
    $table = 'artefact_feedback';
}
else {
    $table = 'view_feedback';
}

if (!insert_record($table, $data, 'id', true)) {
    json_reply('local', get_string('addfeedbackfailed'));
}

require_once('activity.php');
activity_occurred('feedback', $data);

json_reply(false,get_string('feedbacksubmitted'));

?>
