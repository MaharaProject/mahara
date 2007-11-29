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
 * @author     Martyn Smith <martyn@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 * @copyright  (C) portions from Moodle, (C) Martin Dougiamas http://dougiamas.com
 */

defined('INTERNAL') || die();

/**
 * Given a query string and limits, return an array of matching users using the
 * search plugin defined in config.php
 *
 * @param string  The query string
 * @param integer How many results to return
 * @param integer What result to start at (0 == first result)
 * @return array  A data structure containing results looking like ...
 *         $results = array(
 *               count   => integer, // total number of results
 *               limit   => integer, // how many results are returned
 *               offset  => integer, // starting from which result
 *               results => array(   // the result records
 *                   array(
 *                       id            => integer,
 *                       username      => string,
 *                       institution   => string,
 *                       firstname     => string,
 *                       lastname      => string,
 *                       preferredname => string,
 *                       email         => string,
 *                   ),
 *                   array(
 *                       id            => integer,
 *                       username      => string,
 *                       institution   => string,
 *                       firstname     => string,
 *                       lastname      => string,
 *                       preferredname => string,
 *                       email         => string,
 *                   ),
 *                   array(...),
 *               ),
 *           );
 */
function search_user($query_string, $limit, $offset = 0) {
    $plugin = get_config('searchplugin');
    safe_require('search', $plugin);
    safe_require('artefact', 'internal');

    $publicfields = array_keys(ArtefactTypeProfile::get_public_fields());
    if (empty($publicfields)) {
        $publicfields = array('preferredname');
    }
    $fieldlist = "('" . join("','", $publicfields) . "')";

    $results = call_static_method(generate_class_name('search', $plugin), 'search_user', $query_string, $limit, $offset);

    if ($results['data']) {
        $userlist = '('.join(',', array_map(create_function('$u','return $u[\'id\'];'), $results['data'])).')';

        $public_fields = get_records_sql_array('
            SELECT 
                u.id, a.artefacttype, a.title
            FROM
                {usr} u
                LEFT JOIN {artefact} a ON u.id=a.owner AND a.artefacttype IN ' . $fieldlist . '
            WHERE
                u.id IN ' . $userlist . '
            ORDER BY u.firstname, u.lastname, u.id, a.artefacttype',
            array()
        );

        $public_fields_byuser = array();
        if (!empty($public_fields)) {
            foreach ($public_fields as $field) {
                // This will be null if the user does not have a field marked public
                if ($field->artefacttype !== null) {
                    $public_fields_byuser[$field->id][$field->artefacttype] = $field->title;
                }
            }
        }
        
        foreach ($results['data'] as &$result) {
            $result['name'] = display_name($result);
            if (isset($public_fields_byuser[$result['id']])) {
                foreach ($public_fields_byuser[$result['id']] as $field => $value) {
                    $result[$field] = $value;
                }
            }
            if (isset($result['country'])) {
                $result['country'] = get_string('country.' . $result['country']);
            }
        }

    }

    return $results;
}


function get_admin_user_search_results($search, $offset, $limit, $sortby, $sortdir) {
    // In admin search, the search string is interpreted as either a
    // name search or an email search depending on its contents
    $queries = array();
    $constraints = array();
    if (!empty($search->query)) {
        if (strpos($search->query, '@') !== false) {
            $queries[] = array('field' => 'email',
                               'type' => 'contains',
                               'string' => $search->query);
        } else {
            $queries = array(array('field' => 'firstname',
                                   'type' => 'contains',
                                   'string' => $search->query),
                             array('field' => 'lastname',
                                   'type' => 'contains',
                                   'string' => $search->query));
        }
    }
    if (!empty($search->f)) {
        $constraints[] = array('field' => 'firstname',
                               'type' => 'starts',
                               'string' => $search->f);
    }
    if (!empty($search->l)) {
        $constraints[] = array('field' => 'lastname',
                               'type' => 'starts',
                               'string' => $search->l);
    }
    // Filter by viewable institutions:
    global $USER;
    if (!$USER->get('admin')) {
        $allowed = $USER->get('admininstitutions');
        if ($search->institution == 'all' || !isset($allowed[$search->institution])) {
            $constraints[] = array('field' => 'institution_requested',
                                   'type' => 'in',
                                   'list' => $allowed);
        } else {
            $constraints[] = array('field' => 'institution_requested',
                                   'type' => 'equals',
                                   'string' => $search->institution);
        }
    } else if ($search->institution != 'all') {
        $constraints[] = array('field' => 'institution',
                               'type' => 'equals',
                               'string' => $search->institution);
    }

    return admin_user_search($queries, $constraints, $offset, $limit, $sortby, $sortdir);
}


function build_admin_user_search_results($search, $offset, $limit, $sortby, $sortdir) {
    global $USER;

    if (empty($search->institution)) {
        $search->institution = 'all';
    }

    $results = get_admin_user_search_results($search, $offset, $limit, $sortby, $sortdir);

    $params = array();
    foreach ($search as $k => $v) {
        if (!empty($v)) {
            $params[] = $k . '=' . $v;
        }
    }
    $searchurl = get_config('wwwroot') . 'admin/users/search.php?' . join('&amp;', $params)
        . '&amp;limit=' . $limit;

    $templatedir = get_config('docroot') . 'theme/' . get_config('theme') . '/templates/admin/users/';

    $cols = array(
        'icon'        => array('name'     => '',
                               'template' => file_get_contents($templatedir . 'icon.tpl')),
        'firstname'   => array('name'     => get_string('firstname')),
        'lastname'    => array('name'     => get_string('lastname')),
        'username'    => array('name'     => get_string('username'),
                               'template' => file_get_contents($templatedir . 'username.tpl')),
        'email'       => array('name'     => get_string('email')),
    );

    $institutions = get_records_assoc('institution', '', '', '', 'name,displayname');
    if (count($institutions) > 1) {
        $cols['institution'] = array('name'     => get_string('institution'),
                                     'template' => file_get_contents($templatedir . 'institution.tpl'));
    }
    $cols['suspended'] = array('name'     => get_string('suspended', 'admin'),
                               'template' => file_get_contents($templatedir . 'suspendlink.tpl'));

    $smarty = smarty_core();
    $smarty->assign_by_ref('results', $results);
    $smarty->assign_by_ref('institutions', $institutions);
    $smarty->assign('USER', $USER);
    $smarty->assign('searchurl', $searchurl);
    $smarty->assign('sortby', $sortby);
    $smarty->assign('sortdir', $sortdir);
    $smarty->assign('pagebaseurl', $searchurl . '&sortby=' . $sortby . '&sortdir=' . $sortdir);
    $smarty->assign('cols', $cols);
    $smarty->assign('ncols', count($cols));
    return $smarty->fetch('searchresulttable.tpl');
}


function admin_user_search($queries, $constraints, $offset, $limit, $sortfield, $sortdir) {
    $plugin = get_config('searchplugin');
    safe_require('search', $plugin);
    return call_static_method(generate_class_name('search', $plugin), 'admin_search_user', 
                              $queries, $constraints, $offset, $limit, $sortfield, $sortdir);
}


/**
 * Given a query string and limits, return an array of matching groups using the
 * search plugin defined in config.php
 *
 * @param string  The query string
 * @param integer How many results to return
 * @param integer What result to start at (0 == first result)
 * @return array  A data structure containing results looking like ...
 *         $results = array(
 *               count   => integer, // total number of results
 *               limit   => integer, // how many results are returned
 *               offset  => integer, // starting from which result
 *               results => array(   // the result records
 *                   array(
 *                       id            => integer,
 *                       name          => string,
 *                       owner         => integer,
 *                       description   => string,
 *                       ctime         => string,
 *                       mtime         => string,
 *                   ),
 *                   array(
 *                       id            => integer,
 *                       name          => string,
 *                       owner         => integer,
 *                       description   => string,
 *                       ctime         => string,
 *                       mtime         => string,
 *                   ),
 *                   array(...),
 *               ),
 *           );
 */
function search_group($query_string, $limit, $offset = 0, $all = false) {
    $plugin = get_config('searchplugin');
    safe_require('search', $plugin);

    return call_static_method(generate_class_name('search', $plugin), 'search_group', $query_string, $limit, $offset, $all);
}

function search_selfsearch($query_string, $limit, $offset, $type = 'all') {
    $plugin = get_config('searchplugin');
    safe_require('search', $plugin);

    return call_static_method(generate_class_name('search', $plugin), 'self_search', $query_string, $limit, $offset, $type);
}

function get_search_plugins() {
    $searchpluginoptions = array();

    if ($searchplugins = get_records_array('search_installed')) {
        foreach ($searchplugins as $plugin) {
            $searchpluginoptions[$plugin->name] = $plugin->name;

            $config_path = get_config('docroot') . 'search/' . $plugin->name . '/version.php';
            if (is_readable($config_path)) {
                $config = new StdClass;
                require_once($config_path);
                if (isset($config->name)) {
                    $searchpluginoptions[$plugin->name] = $config->name;
                }
            }
        }
    }

    return $searchpluginoptions;
}
?>
