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
function search_user($query_string, $limit, $offset = 0, $data = array()) {
    $plugin = get_config('searchplugin');
    safe_require('search', $plugin);
    safe_require('artefact', 'internal');

    $publicfields = array_keys(ArtefactTypeProfile::get_public_fields());
    if (empty($publicfields)) {
        $publicfields = array('preferredname');
    }
    $fieldlist = "('" . join("','", $publicfields) . "')";

    $results = call_static_method(generate_class_name('search', $plugin), 'search_user', $query_string, $limit, $offset, $data);

    if ($results['data']) {
        $userlist = '('.join(',', array_map(create_function('$u','return (int)$u[\'id\'];'), $results['data'])).')';

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
    foreach (array('member', 'requested', 'invitedby') as $p) {
        $institution->{$p} = $search->{$p};
    }
    $results = institutional_admin_user_search($search->query, $institution, $limit);
    if ($results['count']) {
        foreach ($results['data'] as &$r) {
            $r['name'] = $r['firstname'] . ' ' . $r['lastname'] . ' (' . $r['username'] . ')';
            if (!empty($r['studentid'])) {
                $r['name'] .= ' (' . $r['studentid'] . ')';
            }
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


/**
 * Pull two-word phrases out of a query for matching against first,last names.
 *
 * This function comes from Drupal's search module, with some small changes.
 */

function parse_name_query($text) {
  $words = array();
  $fullnames = array();

  // Tokenize query string
  preg_match_all('/ ("[^"]+"|[^" ]+)/i', ' '. $text, $matches, PREG_SET_ORDER);

  if (count($matches) < 1) {
    return NULL;
  }

  // Classify tokens
  foreach ($matches as $match) {
    // Strip off phrase quotes
    if ($match[1]{0} == '"') {
      $phrase = preg_replace('/\s\s+/', ' ', strtolower(substr($match[1], 1, -1)));
      $phraselist = split(' ', $phrase);
      if (count($phraselist) == 2) {
        $fullnames[] = $phraselist;
      } else {
        $words = array_merge($words, array($phrase));
      }
    } else {
      $words = array_merge($words, array(strtolower($match[1])));
    }
  }
  return array($words, $fullnames);

}

function get_admin_user_search_results($search, $offset, $limit, $sortby, $sortdir) {
    // In admin search, the search string is interpreted as either a
    // name search or an email search depending on its contents
    $queries = array();
    $constraints = array();
    if (!empty($search->query)) {
        list($words, $fullnames) = parse_name_query($search->query);
        foreach ($words as $word) {
            if (strpos($word, '@') !== false) {
                $queries[] = array('field' => 'email',
                                   'type' => 'contains',
                                   'string' => $word);
            } else {
                $queries[] = array('field' => 'firstname',
                                   'type' => 'contains',
                                   'string' => $word);
                $queries[] = array('field' => 'lastname',
                                   'type' => 'contains',
                                   'string' => $word);
                $queries[] = array('field' => 'username',
                                   'type' => 'contains',
                                   'string' => $word);
            }
        }
        foreach ($fullnames as $n) {
            $constraints[] = array('field' => 'firstname',
                                   'type' => 'contains',
                                   'string' => $n[0]);
            $constraints[] = array('field' => 'lastname',
                                   'type' => 'contains',
                                   'string' => $n[1]);
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
            if (!empty($result['institutions'])) {
                $result['institutions'] = array_combine($result['institutions'],$result['institutions']);
            }
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
    $searchurl = get_config('wwwroot') . 'admin/users/search.php?' . join('&', $params) . '&limit=' . $limit;

    $usernametemplate = '<a href="' . get_config('wwwroot')
        . '{if $USER->is_admin_for_user($r.id)}admin/users/edit.php?id={$r.id}{else}user/view.php?id={$r.id}{/if}">{$r.username}</a>';

    $cols = array(
        'icon'        => array('name'     => '',
                               'template' => '<img src="{profile_icon_url user=$r maxwidth=40 maxheight=40}" alt="' . get_string('profileimage') . '" />',
                               'class'    => 'center'),
        'firstname'   => array('name'     => get_string('firstname')),
        'lastname'    => array('name'     => get_string('lastname')),
        'username'    => array('name'     => get_string('username'),
                               'template' => $usernametemplate),
        'email'       => array('name'     => get_string('email')),
    );

    $institutions = get_records_assoc('institution', '', '', '', 'name,displayname');
    if (count($institutions) > 1) {
        $cols['institution'] = array('name'     => get_string('institution'),
                                     'template' => '{if !$r.institutions}{$institutions.mahara->displayname}{else}{foreach from=$r.institutions item=i}<div>{$institutions[$i]->displayname}</div>{/foreach}{/if}{if !$r.requested}{foreach from=$r.requested item=i}<div class="pending">{str tag=requestto section=admin} {$institutions[$i]->displayname}{if $USER->is_institutional_admin("$i")} (<a href="{$WWWROOT}admin/users/addtoinstitution.php?id={$r.id}&institution={$i}">{str tag=confirm section=admin}</a>){/if}</div>{/foreach}{/if}{if !$r.invitedby}{foreach from=$r.invitedby item=i}<div class="pending">{str tag=invitedby section=admin} {$institutions[$i]->displayname}</div>{/foreach}{/if}');
    }

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
 * Returns search results for users in a particular group
 *
 * The search term is applied against first and last names of the users in the group
 *
 * @param int    $group             The group to build results for
 * @param string $query             A search string to filter by
 * @param int    $offset            What result to start showing paginated results from
 * @param int    $limit             How many results to show
 * @param array  $membershiptype    User membershiptype
 * @param bool   $random            Set to true if you want the result to be ordered by random, default false
 *
 */
function get_group_user_search_results($group, $query, $offset, $limit, $membershiptype, $order=null) {
    $queries = array();
    $constraints = array();
    if (!empty($query)) {
        list($words, $fullnames) = parse_name_query($query);
        foreach ($words as $word) {
            $queries[] = array('field' => 'firstname',
                               'type' => 'contains',
                               'string' => $word);
            $queries[] = array('field' => 'lastname',
                               'type' => 'contains',
                               'string' => $word);
        }
        foreach ($fullnames as $n) {
            $constraints[] = array('field' => 'firstname',
                                   'type' => 'contains',
                                   'string' => $n[0]);
            $constraints[] = array('field' => 'lastname',
                                   'type' => 'contains',
                                   'string' => $n[1]);
        }
    }

    $results = group_user_search($group, $queries, $constraints, $offset, $limit, $membershiptype, $order);
    if ($results['count']) {
        $userids = array_map(create_function('$a', 'return $a["id"];'), $results['data']);
        $introductions = get_records_sql_assoc("SELECT \"owner\", title
            FROM {artefact}
            WHERE artefacttype = 'introduction'
            AND \"owner\" IN (" . implode(',', db_array_to_ph($userids)) . ')',
            $userids);
        foreach ($results['data'] as &$result) {
            $result['name'] = display_name($result);
            $result['introduction'] = isset($introductions[$result['id']]) ? $introductions[$result['id']]->title : '';
            if (isset($result['jointime'])) {
                $result['jointime'] = strftime(get_string('strftimedate'), $result['jointime']);
            }
        }
    }
    return $results;
}


function group_user_search($group, $queries, $constraints, $offset, $limit, $membershiptype, $order=null) {
    $plugin = get_config('searchplugin');
    safe_require('search', $plugin);
    return call_static_method(generate_class_name('search', $plugin), 'group_search_user', 
                              $group, $queries, $constraints, $offset, $limit, $membershiptype, $order);
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
function search_group($query_string, $limit, $offset = 0, $type = 'member', $groupcategory = '') {
    $plugin = get_config('searchplugin');
    safe_require('search', $plugin);

    return call_static_method(generate_class_name('search', $plugin), 'search_group', $query_string, $limit, $offset, $type, $groupcategory);
}

function search_selfsearch($query_string, $limit, $offset, $type = 'all') {
    $plugin = get_config('searchplugin');
    safe_require('search', $plugin);

    return call_static_method(generate_class_name('search', $plugin), 'self_search', $query_string, $limit, $offset, $type);
}

function get_portfolio_types_from_param($filter) {
    if (is_null($filter) || $filter == 'all') {
        return null;
    }
    if ($filter == 'view') {
        return array('view' => true, 'artefact' => false);
    }
    require_once(get_config('docroot') . 'artefact/lib.php');
    return array('view' => false, 'artefact' => artefact_get_types_from_filter($filter));
}

function get_portfolio_items_by_tag($tag, $owner, $limit, $offset, $sort='name', $type=null, $returntags=true) {
    // For now, can only be used to search a user's portfolio
    if (empty($owner->id) || empty($owner->type)) {
        throw new SystemException('get_views_and_artefacts_by_tag: invalid owner');
    }
    if ($owner->type != 'user') {
        throw new SystemException('get_views_and_artefacts_by_tag only implemented for users');
    }

    $types = get_portfolio_types_from_param($type);

    $plugin = 'internal';
    safe_require('search', $plugin);

    $result = call_static_method(generate_class_name('search', $plugin), 'portfolio_search_by_tag', $tag, $owner, $limit, $offset, $sort, $types, $returntags);
    $result->filter = $result->type = $type ? $type : 'all';
    return $result;
}

function get_search_plugins() {
    $searchpluginoptions = array();

    if ($searchplugins = plugins_installed('search')) {
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

/**
 * Given a filter string and limits, return an array of matching friends.
 *
 * @param string  The filter string
 * @param integer How many results to return
 * @param integer What result to start at (0 == first result)
 * @return array  A data structure containing results looking like ...
 *         $results = array(
 *               count   => integer, // total number of results
 *               limit   => integer, // how many results are returned
 *               offset  => integer, // starting from which result
 *               results => array(   // the result records
 *                   array(
 *                       id            => integer, //user id
 *                   ),
 *                   array(...),
 *               ),
 *           );
 */
function search_friend($filter, $limit, $offset) {
    global $USER;
    $userid = $USER->get('id');

    if (!in_array($filter, array('all','current','pending'))) {
        throw new SystemException('Invalid search filter');
    }

    $sql = array();
    $count = 0;

    if (in_array($filter, array('all', 'current'))) {
        $count += count_records_sql('SELECT COUNT(usr1) FROM {usr_friend}
            JOIN {usr} u1 ON (u1.id = usr1 AND u1.deleted = 0)
            JOIN {usr} u2 ON (u2.id = usr2 AND u2.deleted = 0)
            WHERE usr1 = ? OR usr2 = ?',
            array($userid, $userid)
        );

        array_push($sql, 'SELECT usr2 AS id, 2 AS status FROM {usr_friend} WHERE usr1 = ?
        ');
        array_push($sql, 'SELECT usr1 AS id, 2 AS status FROM {usr_friend} WHERE usr2 = ?
        ');
    }

    if (in_array($filter, array('all', 'pending'))) {
        $count += count_records_sql('SELECT COUNT("owner") FROM {usr_friend_request}
            JOIN {usr} u ON (u.id = requester AND u.deleted = 0)
            WHERE "owner" = ?',
            array($userid)
        );

        array_push($sql, 'SELECT requester AS id, 1 AS status FROM {usr_friend_request} WHERE "owner" = ?
        ');
    }

    $data = get_column_sql('SELECT f.id FROM (' . join('UNION ', $sql) . ') f
        JOIN {usr} u ON (f.id = u.id AND u.deleted = 0)
        ORDER BY status, firstname, lastname, u.id
        LIMIT ?
        OFFSET ?', array_merge(array_pad($values=array(), count($sql), $userid), array($limit, $offset)));

    foreach ($data as &$result) {
        $result = array('id' => $result);
    }

    return array(
    'count'   => $count,
    'limit'   => $limit,
    'offset'  => $offset,
    'data'    => $data,
    );
}
