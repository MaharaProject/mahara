<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
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



/* 
 * Institutional admin queries:
 *
 * These are only used to populate user lists on the Institution
 * Members page.  They may return users who are not in the same
 * institution as the logged in institutional admin, so they should
 * return names only, not email addresses.
 */

function get_institutional_admin_search_results($search, $limit) {
    $institution = new StdClass;
    $institution->name = $search->institution;
    foreach (array('member', 'requested', 'invited') as $p) {
        $institution->{$p} = $search->{$p};
    }
    $results = institutional_admin_user_search($search->query, $institution, $limit);
    if ($results['count']) {
        foreach ($results['data'] as &$result) {
            $result['name'] = display_name($result);
        }
    }
    return $results;
}

function institutional_admin_user_search($query, $institution, $limit) {
    $plugin = get_config('searchplugin');
    safe_require('search', $plugin);
    return call_static_method(generate_class_name('search', $plugin), 'institutional_admin_search_user', 
                              $query, $institution, $limit);
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
        if (empty($search->institution) && empty($search->institution_requested)) {
            $search->institution_requested = 'all';
        }
        $allowed = $USER->get('admininstitutions');
        foreach (array('institution', 'institution_requested') as $p) {
            if (!empty($search->{$p})) {
                if ($search->{$p} == 'all' || !isset($allowed[$search->{$p}])) {
                    $constraints[] = array('field' => $p,
                                           'type' => 'in',
                                           'string' => $allowed);
                } else {
                    $constraints[] = array('field' => $p,
                                           'type' => 'equals',
                                           'string' => $search->{$p});
                }
            }
        }
    } else if (!empty($search->institution) && $search->institution != 'all') {
        $constraints[] = array('field' => 'institution',
                               'type' => 'equals',
                               'string' => $search->institution);
    }
    
    $results = admin_user_search($queries, $constraints, $offset, $limit, $sortby, $sortdir);
    if ($results['count']) {
        foreach ($results['data'] as &$result) {
            $result['name'] = display_name($result);
        }
    }
    return $results;
}


function build_admin_user_search_results($search, $offset, $limit, $sortby, $sortdir) {
    global $USER;

    $results = get_admin_user_search_results($search, $offset, $limit, $sortby, $sortdir);

    $params = array();
    foreach ($search as $k => $v) {
        if (!empty($v)) {
            $params[] = $k . '=' . $v;
        }
    }
    $searchurl = get_config('wwwroot') . 'admin/users/search.php?' . join('&amp;', $params)
        . '&amp;limit=' . $limit;

    $cols = array(
        'icon'        => array('name'     => '',
                               'template' => '<img src="' . get_config('wwwroot') . 'thumb.php?type=profileicon&size=40x40&id={$r.id}" alt="' . get_string('profileimage') . '" />'),
        'firstname'   => array('name'     => get_string('firstname')),
        'lastname'    => array('name'     => get_string('lastname')),
        'username'    => array('name'     => get_string('username'),
                               'template' => '{if $USER->get(\'admin\') || !empty($r.institutions)}<a href="' . get_config('wwwroot') . 'admin/users/edit.php?id={$r.id}">{$r.username|escape}</a>{else}{$r.username}{/if}'),
        'email'       => array('name'     => get_string('email')),
    );

    $institutions = get_records_assoc('institution', '', '', '', 'name,displayname');
    if (count($institutions) > 1) {
        $cols['institution'] = array('name'     => get_string('institution'),
                                     'template' => '{if empty($r.institutions)}{$institutions.mahara->displayname}{else}{foreach from=$r.institutions item=i}<div>{$institutions[$i]->displayname}</div>{/foreach}{/if}');
    }
    $cols['suspended'] = array('name'     => get_string('suspended', 'admin'),
                               'template' => '{if !$r.suspended || $r.suspended == \'f\'}<a class="suspend-user-link" href="' . get_config('wwwroot') . 'admin/users/suspend.php?id={$r.id}">' . get_string('suspenduser', 'admin') . '</a>{/if}');

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
