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
require_once('view.php');

$view = new View(param_integer('id'));
$change = param_boolean('change', false);
$action = param_alphanumext('action', '');

// If the view has been submitted, disallow editing
if ($view->is_submitted()) {
    $submittedto = $view->submitted_to();
    throw new AccessDeniedException(get_string('canteditsubmitted', 'view', $submittedto['name']));
}

// we actually ned to process stuff
if ($change) {
    try {
        $returndata = $view->process_changes();
        json_reply(false, $returndata);
    }
    catch (Exception $e) {
        json_reply(true, $e->getMessage());
    }
}
// else we're just reading data...
switch ($action) {
case 'blocktype_list':
    $category = param_alpha('c');
    $data = $view->build_blocktype_list($category, true);
    json_reply(false, array('message' => false, 'data' => $data));
    break;
}

json_reply(true, get_string('noviewcontrolaction', 'error', $action));
