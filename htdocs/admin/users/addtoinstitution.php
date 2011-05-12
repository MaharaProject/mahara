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
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('INSTITUTIONALADMIN', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once('institution.php');

$id          = param_integer('id');
$institution = new Institution(param_alpha('institution'));

if (!$USER->get('admin')) {
    if (!$USER->is_institutional_admin($institution->name)) {
        $SESSION->add_error_msg(get_string('notadminforinstitution', 'admin'));
        redirect(get_config('wwwroot').'admin/users/search.php');
    }
    else if (!get_field('usr_institution_request', 'confirmedusr', 'usr', $id, 'institution', $institution->name)) {
        $institution->inviteUser($id);
        $SESSION->add_ok_msg(get_string('invitationsent', 'admin'));
        redirect(get_config('wwwroot').'admin/users/search.php');
    }
}

$institution->addUserAsMember($id);
$SESSION->add_ok_msg(get_string('useradded', 'admin'));
redirect(get_config('wwwroot').'admin/users/edit.php?id='.$id);
