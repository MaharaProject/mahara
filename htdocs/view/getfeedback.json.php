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
define('PUBLIC', 1);
define('JSON', 1);

require(dirname(dirname(__FILE__)) . '/init.php');

$view     = param_integer('view');
$artefact = param_integer('artefact', null);
$limit    = param_integer('limit', 5);
$offset   = param_integer('offset', 0);

if (!can_view_view($view)) {
    json_reply('local', get_string('noaccesstoview', 'view'));
}

$userid = $USER->get('id');

if ($artefact) {
    require_once(get_config('docroot') . 'artefact/lib.php');
    $public = !$USER->can_edit_artefact(artefact_instance_from_id($artefact));
    $table = 'artefact_feedback';
    $count = count_records_sql('
        SELECT
            COUNT(*)
        FROM {artefact_feedback}
        WHERE view = ' . $view . ' AND artefact = ' . $artefact
            . ($public ? ' AND (public = 1 OR author = ' . $userid . ')' : ''));
    $feedback = get_records_sql_array('
        SELECT 
            id, author, authorname, ctime, message, public
        FROM {artefact_feedback}
        WHERE view = ' . $view . ' AND artefact = ' . $artefact
            . ($public ? ' AND (public = 1 OR author = ' . $userid . ')' : '') . '
        ORDER BY id', '', $offset, $limit);

}
else {
    require_once(get_config('libroot') . 'view.php');
    $public = !$USER->can_edit_view(new View($view));
    $table = 'view_feedback';
    $count = count_records_sql('
        SELECT
            COUNT(*)
        FROM {view_feedback}
        WHERE view = ' . $view 
            . ($public ? ' AND (public = 1 OR author = ' . $userid . ')' : ''));
    $feedback = get_records_sql_array('
        SELECT
            f.id, f.author, f.authorname, f.ctime, f.message, f.public, f.attachment, a.title, af.size
        FROM {view_feedback} f
        LEFT OUTER JOIN {artefact} a ON f.attachment = a.id
        LEFT OUTER JOIN {artefact_file_files} af ON af.artefact = a.id
        WHERE view = ' . $view 
            . ($public ? ' AND (f.public = 1 OR f.author = ' . $userid . ')' : '') . '
        ORDER BY id', '', $offset, $limit);
}

$data = array();
if ($feedback) {
    foreach ($feedback as $record) {
        $d = array(
            'id'              => $record->id,
            'table'           => $table,
            'ownedbythisuser' => !$public,
            'name'            => $record->author ? display_name($record->author) : $record->authorname,
            'date'            => format_date(strtotime($record->ctime), 'strftimedatetime'),
            'message'         => parse_bbcode($record->message),
            'ispublic'        => $record->public,
            'author'          => $record->author,
        );
        if (!empty($record->attachment)) {
            $d['attachid']    = $record->attachment;
            $d['attachtitle'] = $record->title;
            $d['attachsize']  = display_size($record->size);
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
