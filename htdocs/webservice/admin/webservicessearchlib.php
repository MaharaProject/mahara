<?php
/**
 *
 * @package    mahara
 * @subpackage auth-webservice
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();

require_once('searchlib.php');
require_once('user.php');

function build_webservice_user_search_results($search, $offset, $limit, $sortby, $sortdir) {
    global $USER, $token, $suid, $ouid;

    $results = get_admin_user_search_results($search, $offset, $limit, $sortby, $sortdir);

    $params = array();
    foreach ($search as $k => $v) {
        if (!empty($v)) {
            $params[] = $k . '=' . $v;
        }
    }
    if ($suid) {
        $params[] = 'suid=' . $suid;
    }
    if ($ouid) {
        $params[] = 'ouid=' . $ouid;
    }

    $searchurl = get_config('wwwroot') . 'webservice/admin/search.php?' . join('&', $params) . '&limit=' . $limit;

    $pagination = $results['pagination'] = build_pagination(array(
            'id' => 'admin_usersearch_pagination',
            'class' => 'center',
            'url' => $searchurl,
            'count' => $results['count'],
            'limit' => $limit,
            'setlimit' => true,
            'jumplinks' => 8,
            'numbersincludeprevnext' => 2,
            'offset' => $offset,
            'datatable' => 'searchresults',
            'jsonscript' => 'webservice/admin/search.json.php',
    ));

    if ($ouid) {
        if ($ouid == 'add') {
            $url = get_config('wwwroot') . 'webservice/admin/oauthv1sregister.php?';
        }
        else {
            $url = get_config('wwwroot') . 'webservice/admin/oauthv1sregister.php?searchreturn=1&ouid=' . $ouid;
        }
    }
    else if ($suid) {
        if ($suid == 'add') {
            $url = get_config('wwwroot') . 'webservice/admin/index.php?';
        }
        else {
            $url = get_config('wwwroot') . 'webservice/admin/userconfig.php?searchreturn=1&suid=' . $suid;
        }
    }
    else {
        if ($token == 'add') {
            $url = get_config('wwwroot') . 'webservice/admin/index.php?';
        }
        else {
            $url = get_config('wwwroot') . 'webservice/admin/tokenconfig.php?searchreturn=1&token=' . $token;
        }
    }

    $cols = array(
        'icon'        => array('name'     => '',
                               'template'         =>  'auth:webservice:searchiconcolumn.tpl',
                               'class'    => 'center'),
        'firstname'   => array('name'     => get_string('firstname'), 'sort' => true),
        'lastname'    => array('name'     => get_string('lastname'), 'sort' => true),
        'username'    => array('name'     => get_string('username'), 'sort' => true,
                               'template' => 'auth:webservice:searchusernamecolumn.tpl'),
        'email'       => array('name'     => get_string('email'), 'sort' => true),
    );

    $institutions = get_records_assoc('institution', '', '', '', 'name,displayname');
    if (count($institutions) > 1) {
        $cols['institution'] = array('name'     => get_string('institution'),
                                     'template' => 'admin/users/searchinstitutioncolumn.tpl');
    }

    $smarty = smarty_core();
    $smarty->assign_by_ref('results', $results);
    $smarty->assign_by_ref('institutions', $institutions);
    $smarty->assign('USER', $USER);
    $smarty->assign('searchurl', $searchurl);
    $smarty->assign('returnurl', $url);
    $smarty->assign('sortby', $sortby);
    $smarty->assign('sortdir', $sortdir);
    $smarty->assign('token', $token);
    $smarty->assign('suid', $suid);
    $smarty->assign('ouid', $ouid);
    $smarty->assign('limitoptions', array(10, 50, 100, 200, 500));
    $smarty->assign('pagebaseurl', $searchurl . '&ouid=' . $ouid . '&suid=' . $suid  . '&token=' . $token . '&sortby=' . $sortby . '&sortdir=' . $sortdir);
    $smarty->assign('cols', $cols);
    $smarty->assign('ncols', count($cols));
    global $THEME;
    $THEME->templatedirs[]= get_config('docroot') . 'auth/webservice/theme/raw/';
    return array($smarty->fetch('searchresulttable.tpl'), $cols, array(
        'url' => $searchurl . '&ouid=' . $ouid . '&suid=' . $suid  . '&token=' . $token . '&sortby=' . $sortby . '&sortdir=' . $sortdir,
        'sortby' => $search->sortby,
        'sortdir' => $search->sortdir
    ), $pagination);
}

function build_webservice_log_search_results($search, $offset, $limit, $sortby, $sortdir) {
    global $USER;

    $results = get_log_search_results($search, $offset, $limit, $sortby, $sortdir);

    $params = array();
    foreach ($search as $k => $v) {
        if (!empty($v)) {
            $params[] = $k . '=' . $v;
        }
    }

    $searchurl = get_config('wwwroot') . 'webservice/admin/webservicelogs.php?' . join('&', $params) . '&limit=' . $limit;

    $pagination = $results['pagination'] = build_pagination(array(
            'id' => 'admin_usersearch_pagination',
            'class' => 'center',
            'url' => $searchurl,
            'count' => $results['count'],
            'limit' => $limit,
            'setlimit' => true,
            'jumplinks' => 8,
            'numbersincludeprevnext' => 2,
            'offset' => $offset,
            'datatable' => 'searchresults',
            'jsonscript' => 'webservice/admin/logsearch.json.php',
    ));

    $cols = array(
            'username'        => array('name'     => get_string('userauth', 'auth.webservice'),
                               'template'         =>  'auth:webservice:username.tpl',
                               'class'            => 'center',
                               'sort'             => true),
            'institution'   => array('name'     => get_string('institution'), 'sort' => true),
            'protocol'      => array('name'     => get_string('protocol', 'auth.webservice'), 'sort' => true),
            'auth'          => array('name'     => get_string('authtype', 'auth.webservice'), 'sort' => true),
            'functionname'  => array('name'     => get_string('function', 'auth.webservice'), 'sort' => true),
            'timetaken'     => array('name'     => get_string('timetaken', 'auth.webservice'), 'sort' => true),
            'timelogged'    => array('name'     => get_string('timelogged', 'auth.webservice'), 'sort' => true),
            'info'          => array('name'     => get_string('info', 'auth.webservice'), 'class' => 'webservicelogs-info'),
    );

    $institutions = get_records_assoc('institution', '', '', '', 'name,displayname');
    if (count($institutions) > 1) {
        $cols['institution'] = array('name'     => get_string('institution'),
                                     'template' => 'admin/users/searchinstitutioncolumn.tpl');
    }

    $smarty = smarty_core();
    $smarty->assign_by_ref('results', $results);
    $smarty->assign_by_ref('institutions', $institutions);
    $smarty->assign('USER', $USER);
    $smarty->assign('searchurl', $searchurl);
    $smarty->assign('sortby', $sortby);
    $smarty->assign('sortdir', $sortdir);
    $smarty->assign('limitoptions', array(10, 50, 100, 200, 500));
    $smarty->assign('pagebaseurl', $searchurl . '&sortby=' . $sortby . '&sortdir=' . $sortdir);
    $smarty->assign('cols', $cols);
    $smarty->assign('ncols', count($cols));
    global $THEME;
    $THEME->templatedirs[]= get_config('docroot') . 'auth/webservice/theme/raw/';
    return array($smarty->fetch('searchresulttable.tpl'), $cols, array(
        'url' => $searchurl . '&sortby=' . $sortby . '&sortdir=' . $sortdir,
        'sortby' => $search->sortby,
        'sortdir' => $search->sortdir
    ), $pagination);
}

/**
 * Split a query string into search terms.
 *
 * Contents of double-quoted strings are counted as a single term,
 * '"' can be entered as '\"', '\' as '\\'.
 */
function split_query_string($query) {
    $terms = array();

    // Split string on unescaped double quotes
    $quotesplit = preg_split('/(?<!\\\)(\")/', $query, -1, PREG_SPLIT_DELIM_CAPTURE | PREG_SPLIT_NO_EMPTY);

    $inphrase = false;

    foreach ($quotesplit as $q) {
        if ($q == '"') {
            $inphrase = !$inphrase;
            continue;
        }

        // Remove escaping
        $q = preg_replace(array('/\x5C(?!\x5C)/u', '/\x5C\x5C/u'), array('','\\'), $q);
        if ($inphrase) {
            if ($trimmed = trim($q)) {
                $terms[] = $trimmed;
            }
        }
        else {
            // Split unquoted sequences on spaces
            foreach (preg_split('/\s+/', $q, -1, PREG_SPLIT_NO_EMPTY) as $word) {
                if ($word) {
                    $terms[] = $word;
                }
            }
        }
    }
    return $terms;
}

function get_log_search_results($search, $offset, $limit) {
    $sort = 'TRUE';
    if (preg_match('/^[a-zA-Z_0-9"]+$/', $search->sortby)) {
        $sort = $search->sortby;
        if (strtoupper($search->sortdir) != 'DESC') {
            $sort .= ' ASC';
        }
        else {
            $sort .= ' DESC';
        }
    }
    $where = '';
    $ilike = db_ilike();
    $wheres = array();
    if ($search->protocol != 'all') {
        $wheres[]= ' el.protocol = \'' . $search->protocol . '\' ';
    }
    if ($search->authtype != 'all') {
        $wheres[]= ' el.auth = \'' . $search->authtype . '\' ';
    }
    if ($search->institution != 'all') {
        $wheres[]= ' el.institution = \'' . $search->institution . '\' ';
    }
    if ($search->onlyerrors == 1) {
        $wheres[]= ' TRIM(el.info) > \' \' ';
    }
    if ($search->userquery) {
        $userwheres = array();
        $terms = split_query_string(strtolower(trim($search->userquery)));
        foreach ($terms as $term) {
            foreach (array('u.username', 'u.firstname', 'u.lastname') as $tests) {
                $userwheres[]= ' ' . $tests . ' ' . $ilike . ' \'%' . addslashes($term) . '%\'';
            }
        }
        if (!empty($userwheres)) {
            $wheres[]= ' ( ' . implode(' OR ', $userwheres) . ' ) ';
        }
    }
    if ($search->functionquery) {
        $functionwheres = array();
        $terms = split_query_string(strtolower(trim($search->functionquery)));
        foreach ($terms as $term) {
                $functionwheres[]= ' el.functionname ' . $ilike . ' \'%' . addslashes($term) . '%\'';
        }
        if (!empty($functionwheres)) {
            $wheres[]= ' ( ' . implode(' OR ', $functionwheres) . ' ) ';
        }
    }
    if (empty($wheres)) {
        $wheres[]= ' TRUE ';
    }
    $where = ' WHERE ' . implode(' AND ', $wheres);

    $options = array();
    $count = count_records_sql('
            SELECT  COUNT(*)
            FROM {external_services_logs} el
            JOIN {usr} u
            ON el.userid = u.id
            ' . $where, $options);
    $data = get_records_sql_array('
            SELECT  u.username,
                    u.firstname,
                    u.lastname,
                    u.email,
                    el.*
            FROM {external_services_logs} el
            JOIN {usr} u
            ON el.userid = u.id
            ' . $where . ' ORDER BY ' . $sort, $options, $offset);

    $results = array(
            'count'   => $count,
            'limit'   => $limit,
            'offset'  => $offset,
            'data'    => array(),
        );
    if (!empty($data)) {
        foreach ($data as $row) {
            $row->timelogged = date("H:i:s  -  d.m.Y", $row->timelogged);
            $results['data'][] = (array) $row;
        }
    }
    return $results;
}
