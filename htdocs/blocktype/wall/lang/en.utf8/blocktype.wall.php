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
 * @subpackage blocktype-wall
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006-2009 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

$string['title'] = 'Wall';
$string['otherusertitle'] = "%s's Wall";
$string['description'] = 'Display an area where people can leave you comments';
$string['noposts'] = 'No wall posts to display';
$string['makeyourpostprivate'] = 'Make your post private?';
$string['viewwall'] = 'View wall';
$string['backtoprofile'] = 'Back to profile';
$string['wall'] = 'Wall';
$string['wholewall'] = 'View whole wall';
$string['reply'] = 'reply';
$string['delete'] = 'delete post';
$string['deletepost'] = 'Delete post';
$string['Post'] = 'Post';
$string['deletepostsure'] = 'Are you sure you want to do this? It cannot be undone.';
$string['deletepostsuccess'] = 'Post deleted successfully';
$string['addpostsuccess'] = 'Post added successfully';
$string['maxcharacters'] = "Maximum %s characters per post.";
$string['sorrymaxcharacters'] = "Sorry, your post cannot be more than %s characters long.";
$string['posttextrequired'] = "This field is required.";

// Config strings
$string['postsizelimit'] = "Post Size Limit";
$string['postsizelimitdescription'] = "You can limit the size of wall posts here. Existing posts will not be changed";
$string['postsizelimitmaxcharacters'] = "Maximum number of characters";
$string['postsizelimitinvalid'] = "This is not a valid number.";
$string['postsizelimittoosmall'] = "This limit cannot be lower than zero.";
