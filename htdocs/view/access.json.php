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
$showtitlein = param_boolean('showtitlein', false); // label for people in users' institutions
$showtitleout = param_boolean('showtitleout', false); // label for people not in users' institutions
$limit  = 10;
if ($page < 1) {
    $page = 1;
}
$offset = ($page - 1) * $limit;
$is_admin = $USER->get('admin') || $USER->get('staff');
$options = array();
if (is_isolated() && ($USER->get('institutions') || !$USER->get('institutions') && !$is_admin)) {
    $options['myinstitutions'] = true;
    $options['showadmins'] = false;
}

switch ($type) {
    case 'friend':
        $options['exclude'] = $USER->get('id');
        $options['friends'] = true;
        $data = search_user($query, $limit, $offset, $options);
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
        $options['exclude'] = $USER->get('id');
        $options['weightusers'] = true;
        $institutions = array_keys($USER->get('institutions'));
        $options['weightinstitutions'] = $institutions;
        $data = search_user($query, $limit, $offset, $options);
        $count = $data['count'];

        if (!empty($institutions) && !empty($data['data'])) {
            $sql = "
            SELECT usr, institution
            FROM {usr_institution}
            WHERE institution IN (" . join(',', array_map('db_quote', $institutions)) . ")
            AND usr != ?
            ";
            $peoplein = get_records_sql_assoc($sql, array($USER->get('id')));
            if ($peoplein) {
                $peoplein = array_keys($peoplein);
            }
            else {
                $peoplein = array();
            }
            // $sorted will be set up as a structure that select2 can handle for dropdowns with labels
            $sorted = array();
            foreach ($data['data'] as $key => $user) {
                if (in_array($user['id'], $peoplein)) {
                    if (!isset($sorted[0]['text'])) {
                        $sorted[0]['text'] = '';
                        if (!$showtitlein) {
                            if (count($institutions) == 1) {
                                $sorted[0]['text'] =  get_string('peopleinmyinstitution', 'view', ($USER->get('institutions')[$institutions[0]]->displayname));
                            }
                            else {
                                $sorted[0]['text'] = get_string('peopleinmyinstitutions', 'view');
                            }
                            $showtitlein = true; // we have shown the title, don't show again
                        }
                        $sorted[0]['children'] = array();
                    }
                    $info = array(
                        'id' => $user['id'],
                        'name' => display_name($user['id']),
                        'fancy' => true
                    );
                    $sorted[0]['children'][] = $info;
                }
                else {
                    if (!isset($sorted[1]['text'])) {
                        $sorted[1]['text'] = '';
                        if (!$showtitleout) {
                            $sorted[1]['text'] = get_string('otherpeople', 'view');
                            $showtitleout = true; // we have shown the title, don't show again
                        }
                        $sorted[1]['children'] = array();
                    }
                    $sorted[1]['children'][] = array(
                        'id' => $user['id'],
                        'name' => display_name($user['id']),
                        'fancy' => true
                    );
                }
            }

            // Make sure there are no null/empty values (select2 doesn't handle them)
            ksort($sorted);
            $data['data'] = array_values($sorted);
        }
        else if (empty($institutions) && !empty($data['data'])) {
            foreach ($data['data'] as $key => $value) {
                $info = array(
                    'id' => $value['id'],
                    'name' => display_name($value['id']),
                    'plain' => true
                );
                $data['data'][$key] = $info;
            }
        }

        $roles = get_records_array('usr_access_roles');
        $data['roles'] =  array();
        foreach ($roles as $r) {
            $data['roles'][] = array('name' => $r->role, 'display' => get_string($r->role, 'view'));
        }
        break;
    case 'group':
        require_once('group.php');
        $type = 'all';
        $groupcategory = '';
        $institutions = 'all';
        if (is_isolated() && !$is_admin) {
            $institutions = $USER->get('institutions');
            if (get_config('owngroupsonly')) {
                $type = 'member';
            }
        }
        else if (get_config('owngroupsonly') && !$is_admin) {
            $type = 'member';
            $institutions = array();
        }
        $data = search_group($query, $limit, $offset, $type, $groupcategory, $institutions);
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
        $options['exclude'] = $USER->get('id');
        $options['friends'] = true;
        $data = search_user($query, $limit, $offset, $options);
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
$data['showtitlein'] = (bool)$showtitlein;
$data['showtitleout'] = (bool)$showtitleout;
$data['error'] = false;
$data['message'] = '';
$data['more'] = $more;
json_reply(false, $data);
