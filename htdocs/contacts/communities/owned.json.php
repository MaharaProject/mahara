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
 * @author     Nigel McNie <nigel@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

json_headers();

$action = param_variable('action');
$dbprefix = get_config('dbprefix');

if ($action == 'delete') {
    $id = param_integer('id');
    // check owner
    $owner = get_field('community', 'owner', 'id', $id);
    if ($owner != $USER->get('id')) {
        json_reply('local', get_string('cantdeletecommunitydontown'));
    }
    db_begin();
    delete_records('view_access_community', 'community', $id);
    delete_records('community_member_invite', 'community', $id);
    delete_records('community_member_request', 'community', $id);
    delete_records('community_member', 'community', $id);
    delete_records('community', 'id', $id);
    db_commit();

    json_reply(null, get_string('deletecommunitysuccessful'));
}

json_reply('local', 'Unknown action');

?>
