<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-blog-taggedposts
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(dirname(dirname(dirname(__FILE__))))) . '/init.php');

global $USER;

$request = param_variable('q', '');
$tagexcluded = '';
if (substr($request, 0, 1) == '-') {
    $request = substr($request, 1);
    $tagexcluded = '-';
}
$page = param_integer('page');
if ($page < 1) {
    $page = 1;
}
$tagsperpage = 5;
$values = array($USER->id);
$typecast = is_postgres() ? '::varchar' : '';
$sql = "SELECT
            (CASE
                WHEN at.tag LIKE 'tagid_%' THEN CONCAT(i.displayname, ': ', t2.tag)
                ELSE at.tag
            END) AS tag
        FROM {tag} at
        LEFT JOIN {tag} t2 ON t2.id" . $typecast . " = SUBSTRING(at.tag, 7)
        LEFT JOIN {institution} i ON i.name = t2.ownerid
        JOIN {artefact} a ON (at.resourcetype = 'artefact' AND at.resourceid = a.id" . $typecast . ")
        WHERE a.owner = ?
        AND at.resourcetype = 'artefact'
        AND at.resourceid = a.id" . $typecast . "
        AND a.artefacttype = 'blogpost'";
if ($request !== '') {
    $sql .= " AND (at.tag LIKE '%' || ? || '%' OR t2.tag LIKE '%' || ? || '%')";
    $values[] = $request;
    $values[] = $request;
}
// We need to do group/order by alias of first column (tag) so we use column positioning
$sql .= " GROUP BY 1, i.displayname
          ORDER BY 1 ASC";
$more = true;
$tmptags = array();
$alltags = get_records_sql_array($sql, $values);
while ($alltags !== false && $more && count($tmptags) < $tagsperpage) {
    $tags = array_slice($alltags, $tagsperpage * ($page - 1), $tagsperpage);
    $more = sizeof($alltags) > $tagsperpage * $page;

    foreach ($tags as $tag) {
        if (count($tmptags) >= $tagsperpage) {
            $more = true;
            continue;
        }
        if (stripos($tag->tag, $request) !== false || $request === '') {
            $tmptags[] = (object) array(
                    'id' => $tagexcluded . $tag->tag,
                    'text' => $tag->tag,
            );
        }
    }
    $page++;
}

echo json_encode(array(
    'more' => $more,
    'results' => $tmptags,
));