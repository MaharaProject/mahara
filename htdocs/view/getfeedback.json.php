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
define('PUBLIC', 1);
define('JSON', 1);

require(dirname(dirname(__FILE__)) . '/init.php');

json_headers();

$view     = param_integer('view');
$artefact = param_integer('artefact', null);
$limit    = param_integer('limit', 5);
$offset   = param_integer('offset', 0);

if (!can_view_view($view)) {
    throw new AccessDeniedException();
}

$userid = $USER->get('id');

if ($artefact) {
    $owner = get_field('artefact', 'owner', 'id', $artefact);
    $public = (int) ($owner != $userid);
    $table = 'artefact_feedback';
    $count = count_records_sql('
        SELECT
            COUNT(*)
        FROM {artefact_feedback}
        WHERE view = ' . $view . ' AND artefact = ' . $artefact
            . ($public ? ' AND (public = 1 OR author = ' . $userid . ')' : ''));
    $feedback = get_records_sql_array('
        SELECT 
            id, author, ctime, message, public
        FROM {artefact_feedback}
        WHERE view = ' . $view . ' AND artefact = ' . $artefact
            . ($public ? ' AND (public = 1 OR author = ' . $userid . ')' : '') . '
        ORDER BY id DESC', '', $offset, $limit);

}
else {
    $owner = get_field('view', 'owner', 'id', $view);
    $public = ($owner != $userid);
    $table = 'view_feedback';
    $count = count_records_sql('
        SELECT
            COUNT(*)
        FROM {view_feedback}
        WHERE view = ' . $view 
            . ($public ? ' AND (public = 1 OR author = ' . $userid . ')' : ''));
    $feedback = get_records_sql_array('
        SELECT
            f.id, f.author, f.ctime, f.message, f.public, f.attachment, a.title
        FROM {view_feedback} f
        LEFT OUTER JOIN {artefact} a ON f.attachment = a.id
        WHERE view = ' . $view 
            . ($public ? ' AND (f.public = 1 OR f.author = ' . $userid . ')' : '') . '
        ORDER BY id DESC', '', $offset, $limit);
}

$data = array();
if ($feedback) {
    foreach ($feedback as $record) {
        $d = array(
            'id'              => $record->id,
            'table'           => $table,
            'ownedbythisuser' => ( $owner == $userid ? true : false ),
            'name'            => display_name($record->author),
            'date'            => format_date(strtotime($record->ctime), 'strftimedate'),
            'message'         => $record->message,
            'ispublic'        => $record->public
        );
        if (!empty($record->attachment)) {
            $d['attachid'] = $record->attachment;
            $d['attachtitle'] = $record->title;
        }
        $data[] = $d;
    }
}



$result = array(
    'count'       => $count,
    'limit'       => $limit,
    'offset'      => $offset,
    'data'        => $data,
);

json_headers();
print json_encode($result);


?>
