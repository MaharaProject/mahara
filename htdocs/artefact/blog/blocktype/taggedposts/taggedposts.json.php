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
$sql = "SELECT at.tag FROM {artefact_tag} at
        JOIN {artefact} a ON a.id = at.artefact
        WHERE a.owner = ?
        AND a.artefacttype = 'blogpost'";
if ($request !== '') {
    $sql .= " AND at.tag LIKE '%' || ? || '%'";
    $values[] = $request;
}
$sql .= " GROUP BY at.tag
          ORDER BY at.tag ASC";
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
                    'id' => hsc($tagexcluded . $tag->tag),
                    'text' => hsc($tag->tag),
            );
        }
    }
    $page++;
}

echo json_encode(array(
    'more' => $more,
    'results' => $tmptags,
));