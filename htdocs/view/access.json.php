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

// NOTE: this JSON script is used by the 'viewacl' element. It could probably
// be moved elsewhere without harm if necessary (e.g. if the 'viewacl' element
// was used in more places
define('INTERNAL', 1);
define('JSON', 1);

require(dirname(dirname(__FILE__)) . '/init.php');
require_once('searchlib.php');

$type   = param_variable('type');
$query  = param_variable('query', '');
$page = param_integer('page');
$limit  = 10;
if ($page < 1) {
    $page = 1;
}
$offset = ($page - 1) * $limit;
switch ($type) {
    case 'friend':
        $data = search_user($query, $limit, $offset,  array('exclude' => $USER->get('id'), 'friends' => true));
        if (!empty($data['data'])) {
            foreach ($data['data'] as $key => $value) {
                $info = array(
                    'id' => $value['id'],
                    'name' => display_name($value['id'])
                );
                $data['data'][$key] = $info;
            }
        }
        $count = $data['count'];
        break;
    case 'user':
        $data = search_user($query, $limit, $offset, array('exclude' => $USER->get('id')));
        $roles = get_records_array('usr_roles');
        $data['roles'] =  array();
        foreach ($roles as $r) {
            $data['roles'][] = array('name' => $r->role, 'display' => get_string($r->role, 'view'));
        }
        foreach ($data['data'] as $key => $value) {
            $info = array(
                'id' => $value['id'],
                'name' => display_name($value['id']),
            );
            $data['data'][$key] = $info;
        }
        break;
    case 'group':
        require_once('group.php');
        $data = search_group($query, $limit, $offset, '');
        $roles = get_records_array('grouptype_roles');
        $data['roles'] = array();
        foreach ($roles as $r) {
            $data['roles'][$r->grouptype][] = array('name' => $r->role, 'display' => get_string($r->role, 'grouptype.'.$r->grouptype));
        }
        if (!empty($data['data'])) {
            foreach ($data['data'] as $key => $value) {
                $info = array(
                    'id' => $value->id,
                    'url' => group_homepage_url($value),
                    'name' => $value->name,
                    'grouptype' => $value->grouptype
                );
                $data['data'][$key] = $info;
            }
        }
        $data['profilepic'] = false;
        break;
    default:
        $data = search_user($query, $limit, $offset,  array('exclude' => $USER->get('id'), 'friends' => true));
        foreach ($data as $key => $value) {
            $info = array(
                'id' => $value['id'],
                'name' => display_name($value['id'])
            );
            $data['data'][$key] = $info;
        }
        break;
}
$more = $data['count'] > $limit * $page;

$data['error'] = false;
$data['message'] = '';
$data['more'] = $more;
json_reply(false, $data);
