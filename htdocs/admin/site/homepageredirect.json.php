<?php
/**
 *
 * @package    mahara
 * @subpackage admin
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('ADMIN', 1);
define('JSON', 1);

require(dirname(dirname(dirname(__FILE__))) . '/init.php');

global $USER;

$request = param_variable('q');
$page = param_integer('page');
if ($page < 1) {
    $page = 1;
}
$resultsperpage = 10;

$more = true;
$tmpresults = array();

while ($more && count($tmpresults) < $resultsperpage) {
    $results = get_homepage_redirect_results($request, $resultsperpage, $resultsperpage * ($page - 1));
    $more = $results['count'] > $resultsperpage * $page;

    if (!$results['data']) {
        $results['data'] = array();
    }

    foreach ($results['data'] as $result) {
        if (count($tmpresults) >= $resultsperpage) {
            $more = true;
            continue;
        }
        $title = $result->title;
        if ($result->institution && empty($result->group)) {
            if ($result->institution == 'mahara') {
                $title .= ' (' . get_string('Site') . ')';
            }
            else {
                $title .= ' (' . get_field('institution', 'displayname', 'name', $result->institution) . ')';
            }
        }
        else if ($result->group) {
            $title .= ' (' . get_field('group', 'name', 'id', $result->group) . ')';
        }
        else if ($result->owner) {
            $title .= ' (' . display_name($result->owner, null, true) . ')';
        }
        $tmpresults[] = (object) array('id' => $result->url,
            'text' => $title);
    }
    $page++;
}

echo json_encode(array(
    'more' => $more,
    'results' => $tmpresults,
));
