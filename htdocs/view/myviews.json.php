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
require(dirname(dirname(__FILE__)) . '/init.php');

$limit = param_integer('limit', 5);
$offset = param_integer('offset', 0);

$userid = $USER->get('id');

$count = count_records('view', 'owner', $userid);

/* Get $limit views from the view table, then get all these views'
   associated artefacts */

/* Do this in one query sometime */

$viewdata = get_records_array('view', 'owner', $userid, '', 
                              'id,title,startdate,enddate,description,submitted', $offset, $limit);

$viewidlist = implode(', ', array_map(create_function('$a', 'return $a->id;'), $viewdata));

$prefix = get_config('dbprefix');
$artefacts = get_records_sql_array('SELECT va.view, va.artefact, a.title
        FROM ' . $prefix . 'view_artefact va
        JOIN ' . $prefix . 'artefact a ON va.artefact = a.id
        WHERE va.view IN (' . $viewidlist . ')', '');

$data = array();
if ($viewdata) {
    // The table renderer seems to expect array indices to be
    // contiguous starting at 0, so we cannot use the view id as the
    // array index and need to remember which id belongs to which
    // index.
    for ($i = 0; $i < count($viewdata); $i++) {
        $index[$viewdata[$i]->id] = $i;
        $data[$i]['id'] = $viewdata[$i]->id;
        $data[$i]['title'] = $viewdata[$i]->title;
        $data[$i]['startdate'] = strftime(get_string('strftimedate'),strtotime($viewdata[$i]->startdate));
        $data[$i]['enddate'] = strftime(get_string('strftimedate'),strtotime($viewdata[$i]->enddate));
        $data[$i]['description'] = $viewdata[$i]->description;
        $data[$i]['submitted'] = $viewdata[$i]->submitted;
        $data[$i]['artefacts'] = array();
    }
    // Go through all the artefact records and put them in with the
    // views they belong to.
    if ($artefacts) {
        foreach ($artefacts as $artefact) {
            $data[$index[$artefact->view]]['artefacts'][] = array('id'    => $artefact->artefact,
                                                                  'title' => $artefact->title);
        }
    }
}

/* Get a list of communities that the user belongs to which also have
   a tutor member.  This is the list of communities that the user is
   able to submit views to. */

$communitydata = get_column('community_member', 'community', 'member', $userid);
$communityidlist = implode(', ', $communitydata);
$tutorcommunitydata = get_records_sql_array('SELECT c.id, c.name
       FROM ' . $prefix . 'community c
       JOIN ' . $prefix . 'community_member cm ON c.id = cm.community
       WHERE cm.community IN (' . $communityidlist . ')
       AND cm.tutor = 1', '');

$result = array(
    'count'       => $count,
    'limit'       => $limit,
    'offset'      => $offset,
    'data'        => $data,
    'communities' => $tutorcommunitydata,
);

json_headers();
print json_encode($result);

?>
