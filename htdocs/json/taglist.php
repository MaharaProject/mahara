<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('JSON', 1);
require(dirname(dirname(__FILE__)) . '/init.php');
require_once(get_config('docroot') . 'lib/form/elements/tags.php');

global $USER;

$request = param_variable('q');
$page = param_integer('page');
if ($page < 1) {
    $page = 1;
}
$tagsperpage = 10;

$more = true;
$tmptag = array();

while ($more && count($tmptag) < $tagsperpage) {
    $tags = get_all_tags_for_user($request, $tagsperpage, $tagsperpage * ($page - 1));
    $more = $tags['count'] > $tagsperpage * $page;

    if (!$tags['tags']) {
        $tags['tags'] = array();
    }

    foreach ($tags['tags'] as $tag) {
        if (count($tmptag) >= $tagsperpage) {
            $more = true;
            continue;
        }

        $tmptag[] = (object) array('id' => $tag->tag,
            'text' => display_tag($tag->tag, $tags['tags'])
        );
    }
    $page++;
}

echo json_encode(array(
    'more' => $more,
    'results' => $tmptag,
));
