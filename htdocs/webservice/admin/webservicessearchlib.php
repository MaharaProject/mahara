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

/**
 * Get results for log search with results containing markup/pagination
 *
 * @param object Contains:
 *               - userquery        string
 *               - functionquery    string
 *               - protocol         string
 *               - authtype         string
 *               - sortby           string
 *               - sortdir          string
 *               - offset           int
 *               - limit            int
 *               - onlyerrors               string  optional
 *               - institution              string  optional
 *               - institution_requested    string  optional
 *
 * @return array Contains search results markup/pagination
 */
function build_webservice_log_search_results($search) {
    global $THEME;
    $THEME->templatedirs[]= get_config('docroot') . 'auth/webservice/theme/raw/';

    $results = get_log_search_results($search);

    $params = array();
    foreach ($search as $k => $v) {
        if (!empty($v)) {
            $params[] = $k . '=' . $v;
        }
    }

    $searchurl = get_config('wwwroot') . 'webservice/admin/webservicelogs.php?action=search&' . join('&', $params);

    $pagination = $results['pagination'] = build_pagination(array(
            'id' => 'admin_usersearch_pagination',
            'class' => 'center',
            'url' => $searchurl,
            'count' => $results['count'],
            'limit' => $search->limit,
            'setlimit' => true,
            'jumplinks' => 8,
            'numbersincludeprevnext' => 2,
            'offset' => $search->offset,
            'datatable' => 'searchresults',
            'jsonscript' => 'webservice/admin/logsearch.json.php',
    ));

    $cols = array(
            'username'     => array('name'     => get_string('userauth', 'auth.webservice'),
                                    'template' => 'auth:webservice:username.tpl',
                                    'class'    => 'center',
                                    'sort'     => true),
            'institution'   => array('name'     => get_string('institution'), 'sort' => true),
            'protocol'      => array('name'     => get_string('protocol', 'auth.webservice'), 'sort' => true),
            'auth'          => array('name'     => get_string('authtype', 'auth.webservice'), 'sort' => true),
            'functionname'  => array('name'     => get_string('function', 'auth.webservice'), 'sort' => true),
            'timetaken'     => array('name'     => get_string('timetaken', 'auth.webservice'), 'sort' => true),
            'timelogged'    => array('name'     => get_string('timelogged', 'auth.webservice'), 'sort' => true),
            'info'          => array('name'     => get_string('info', 'auth.webservice'), 'class' => 'webservicelogs-info'),
    );

    $smarty = smarty_core();
    $smarty->assign('results', $results);
    $smarty->assign('institutions', $institutions);
    $smarty->assign('searchurl', $searchurl);
    $smarty->assign('sortby', $search->sortby);
    $smarty->assign('sortdir', $search->sortdir);
    $smarty->assign('limitoptions', array(10, 50, 100, 200, 500));
    $smarty->assign('pagebaseurl', $searchurl . '&sortby=' . $search->sortby . '&sortdir=' . $search->sortdir);
    $smarty->assign('cols', $cols);

    return array($smarty->fetch('searchresulttable.tpl'), $cols, array(
        'url' => $searchurl . '&sortby=' . $search->sortby . '&sortdir=' . $search->sortdir,
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

/**
 * Get raw results for webservices log search
 *
 * @param object $search - see build_webservice_log_search_results() for
 *                         list of variables
 */
function get_log_search_results($search) {
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
    $params = array();
    if ($search->protocol != 'all') {
        $wheres[]= ' el.protocol = ? ';
        $params[] = $search->protocol;
    }
    if ($search->authtype != 'all') {
        $wheres[]= ' el.auth = ? ';
        $params[] = $search->authtype;
    }
    if ($search->institution != 'all') {
        $wheres[]= ' el.institution = ? ';
        $params[] = $search->institution;
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

    $count = count_records_sql('
            SELECT  COUNT(*)
            FROM {external_services_logs} el
            JOIN {usr} u
                ON el.userid = u.id
            ' . $where, $params);
    $data = get_records_sql_array('
            SELECT  u.username,
                    u.firstname,
                    u.lastname,
                    u.email,
                    el.*
            FROM {external_services_logs} el
            JOIN {usr} u
                ON el.userid = u.id
            ' . $where . ' ORDER BY ' . $search->sortby, $params, $search->offset, $search->limit);

    $results = array(
            'count'   => $count,
            'limit'   => $search->limit,
            'offset'  => $search->offset,
            'data'    => array(),
        );
    if (!empty($data)) {
        foreach ($data as $row) {
            $row->timelogged = format_date($row->timelogged, 'strftimedatetime');
            $row->institution = institution_display_name($row->institution);
            $results['data'][] = (array) $row;
        }
    }
    return $results;
}
