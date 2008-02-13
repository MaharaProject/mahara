<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @author     Martyn Smith <martyn@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(__FILE__)) . '/init.php');

$feedback_id = param_integer('id');
$feedback_table = param_variable('table');

if ($feedback_table != 'view_feedback' && $feedback_table != 'artefact_feedback') {
    json_reply('local', 'Invalid table type');
}

$view_id = get_field($feedback_table, 'view', 'id', $feedback_id);
$owner = get_field('view', 'owner', 'id', $view_id);

if ($owner != $USER->get('id')) {
    json_reply('local', get_string('canteditdontownfeedback', 'view'));
}

update_record($feedback_table, (object)array('public' => 0, 'id' => $feedback_id));

json_headers();

json_reply(false,get_string('feedbackchangedtoprivate', 'view'));

?>
