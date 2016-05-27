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
require_once(get_config('libroot') . 'view.php');

$group = param_integer('group', null);
$institution = param_alphanum('institution', null);

$views = new StdClass;
$views->query       = trim(param_variable('viewquery', ''));
$views->ownerquery  = trim(param_variable('ownerquery', ''));
$views->offset      = param_integer('viewoffset', 0);
$views->limit       = param_integer('limit', 10);
$views->group       = param_integer('group', null);
$views->institution = param_alphanum('institution', null);
$views->copyableby = (object) array('group' => $group, 'institution' => $institution);
if (!($group || $institution)) {
    $views->copyableby->owner = $USER->get('id');
}
$searchcollection = param_integer('searchcollection', null);
$sort[] = array('column' => 'title',
                'desc' => 0,
                );
if ($searchcollection) {
    array_unshift($sort, array('column' => 'collection',
                               'desc' => 0,
                               'tablealias' => 'cv'
                               ));
    $views->collection = $searchcollection;
}
$views->sort = (object) $sort;
View::get_templatesearch_data($views);

json_reply(false, array(
    'message' => null,
    'data' => array(
        'table'      => $views->html,
        'pagination' => $views->pagination['html'],
        'count'      => $views->count,
    )
));
