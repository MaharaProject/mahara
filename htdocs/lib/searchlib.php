<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
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
    $results = call_static_method(generate_class_name('search', $plugin), 'search_user', $query_string, $limit, $offset, $data);

    if ($results['data']) {
        foreach ($results['data'] as &$result) {
            $result['name'] = display_name($result);
            $result['url']  = profile_url($result);
        }
    }

    return $results;
}

/*
*   The elastic search plug-in is for now only used in the "Universal Search" page.
*   Search is performed using the internal plug-in in all other case.
*   This might change in the future.
*/
function search_all($query_string, $limit, $offset = 0, $data = array(), $type = null) {
    if (record_exists('search_installed', 'name', 'elasticsearch', 'active', 1)) {
        safe_require('search', 'elasticsearch');
        $plugin = 'elasticsearch';
        $results = call_static_method(generate_class_name('search', $plugin), 'search_all', $query_string, $limit, $offset, $data, $type);
        return $results;
    }
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
    foreach (array('member', 'requested', 'invitedby', 'lastinstitution') as $p) {
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
      $phraselist = explode(' ', $phrase);
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

function get_admin_user_search_results($search, $offset, $limit) {
    $plugin = get_config('searchplugin');
    safe_require('search', $plugin);

    $constraints = array();

    if ($plugin == 'internal') {
        // For the internal plugin, just pass the raw query through as a string, it
        // is parsed in the plugin.
        $queries = $search->query;
    }
    else {
        // In admin search, the search string is interpreted as either a
        // name search or an email search depending on its contents
        $queries = array();
        if (!empty($search->query)) {
            list($words, $fullnames) = parse_name_query($search->query);
            foreach ($words as $word) {
                if (strpos($word, '@') !== false) {
                    $queries[] = array(
                        'field' => 'email',
                        'type' => 'contains',
                        'string' => $word
                    );
                }
                else {
                    $queries[] = array(
                        'field' => 'firstname',
                        'type' => 'contains',
                        'string' => $word
                    );
                    $queries[] = array(
                        'field' => 'lastname',
                        'type' => 'contains',
                        'string' => $word
                    );
                    $queries[] = array(
                        'field' => 'username',
                        'type' => 'contains',
                        'string' => $word
                    );
                    $queries[] = array(
                        'field' => 'preferredname',
                        'type' => 'contains',
                        'string' => $word
                    );
                }
            }
            foreach ($fullnames as $n) {
                $constraints[] = array(
                    'field' => 'firstname',
                    'type' => 'contains',
                    'string' => $n[0]
                );
                $constraints[] = array(
                    'field' => 'lastname',
                    'type' => 'contains',
                    'string' => $n[1]
                );
            }
        }
    }

    if (!empty($search->authname)) {
        $constraints[] = array(
            'field' => 'authname',
            'type' => 'equals',
            'string' => $search->authname
        );
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
    if (!empty($search->loggedin) && $search->loggedin !== 'any') {
        if ($search->loggedin == 'never') {
            $constraints[] = array('field'  => 'lastlogin',
                                   'type'   => 'equals',
                                   'string' => null);
        }
        else if ($search->loggedin == 'ever') {
            $constraints[] = array('field'  => 'lastlogin',
                                   'type'   => 'notequals',
                                   'string' => null);
        }
        else if ($search->loggedin == 'since') {
            $constraints[] = array('field'  => 'lastlogin',
                                   'type'   => 'greaterthan',
                                   'string' => $search->loggedindate);
        }
        else if ($search->loggedin == 'notsince') {
            $constraints[] = array('field'  => 'lastlogin',
                                   'type'   => 'lessthanequal',
                                   'string' => $search->loggedindate);
        }

    }
    // Filter by export queue items
    if (!empty($search->exportqueue)) {
        $exportqueueusers = get_column_sql('SELECT usr FROM {export_queue}');
        if (empty($exportqueueusers)) {
            // use a fake id number so that the query's in function will return no results
            $exportqueueusers = array(-1);
        }
        $constraints[] = array(
            'field'  => 'exportqueue',
            'type'   => 'in',
            'string' => array_unique($exportqueueusers),
        );
    }
    // Filter by archived submissions
    if (!empty($search->archivedsubmissions)) {
        $archivesubmissionsusers = get_column_sql('SELECT usr FROM {export_archive} e JOIN {archived_submissions} a ON a.archiveid = e.id');
        if (empty($archivesubmissionsusers)) {
            // use a fake id number so that the query's in function will return no results
            $archivesubmissionsusers = array(-1);
        }
        $constraints[] = array(
            'field'  => 'archivesubmissions',
            'type'   => 'in',
            'string' => array_unique($archivesubmissionsusers),
        );
    }
    // Filter by duplicate emails
    if (!empty($search->duplicateemail)) {
        $duplicateemailartefacts = get_column_sql('
            SELECT id
            FROM {artefact}
            WHERE
                artefacttype = \'email\'
                AND LOWER(title) IN (
                    SELECT LOWER(title)
                    FROM {artefact}
                    WHERE artefacttype = \'email\'
                    GROUP BY LOWER(title)
                    HAVING count(id) > 1
                )');
        if ($duplicateemailartefacts === false || !is_array($duplicateemailartefacts)) {
            $duplicateemailartefacts = array();
        }
        $constraints[] = array(
            'field'  => 'duplicateemail',
            'type'   => 'in',
            'string' => $duplicateemailartefacts
        );
    }

    // Filter by viewable institutions:
    global $USER;
    if (!$USER->get('admin') && !$USER->get('staff')) {
        $allowed = array_merge($USER->get('admininstitutions'), $USER->get('staffinstitutions'));
        if (empty($search->institution)) {
            $search->institution = 'all';
        }
        if ($search->institution == 'all' || !isset($allowed[$search->institution])) {
            $constraints[] = array(
                'field'  => 'institution',
                'type'   => 'in',
                'string' => $allowed,
            );
        }
        else {
            $constraints[] = array(
                'field'  => 'institution',
                'type'   => 'equals',
                'string' => $search->institution,
            );
        }
    } else if (!empty($search->institution) && $search->institution != 'all') {
        $constraints[] = array('field' => 'institution',
                               'type' => 'equals',
                               'string' => $search->institution);
    }

    $results = call_static_method(
        generate_class_name('search', $plugin), 'admin_search_user',
        $queries, $constraints, $offset, $limit, $search->sortby, $search->sortdir
    );

    if ($results['count']) {
        $isadmin = $USER->get('admin');
        $admininstitutions = $USER->get('admininstitutions');

        foreach ($results['data'] as &$result) {
            $result['name'] = display_name($result);
            if (!empty($result['institutions'])) {
                $result['institutions'] = array_combine($result['institutions'],$result['institutions']);
            }

            // Show all user's emails
            if (!empty($search->duplicateemail)) {
                $selectstr = 'title, (CASE WHEN id IN (' . join(',', array_map('db_quote', $duplicateemailartefacts)) . ') THEN 1 ELSE 0 END) AS duplicated';
            }
            else {
                $selectstr = 'title';
            }
            $emails = get_records_sql_array('
                SELECT ' . $selectstr . '
                FROM {artefact} a
                WHERE a.artefacttype = ?
                    AND a.owner = ?',
                array('email', $result['id']));
            if (is_array($emails)) {
                for ($i = 0; $i < count($emails); $i++) {
                    // Move primary email to the beginning of $emails
                    if ($emails[0]->title == $result['email']) {
                        break;
                    }
                    if ($emails[$i]->title == $result['email']) {
                        $e = $emails[0];
                        $emails[0] = $emails[$i];
                        $emails[$i] = $e;
                        break;
                    }
                }
                $emails[0]->primary = 1;
            }
            else {
                $emails = array();
            }
            $result['email'] = $emails;
            if ($isadmin) {
                continue;
            }

            // Remove email address when viewed by staff
            if (!$hideemail = (empty($admininstitutions) || empty($result['institutions']))) {
                $commoninstitutions = array_intersect($admininstitutions, $result['institutions']);
                $hideemail = $hideemail || empty($commoninstitutions);
            }
            if ($hideemail) {
                unset($result['email']);
            }
        }
    }

    return $results;
}


function build_admin_user_search_results($search, $offset, $limit) {
    global $USER, $THEME;

    $wantedparams = array('query', 'f', 'l', 'sortby', 'sortdir', 'loggedin', 'loggedindate', 'duplicateemail', 'institution', 'authname');
    $params = array();
    foreach ($search as $k => $v) {
        if (!in_array($k, $wantedparams)) {
            continue;
        }
        if (!empty($v)) {
            $params[] = $k . '=' . $v;
        }
    }
    $searchurl = get_config('wwwroot') . 'admin/users/search.php?' . join('&', $params) . '&limit=' . $limit;

    $results = get_admin_user_search_results($search, $offset, $limit);

    $pagination = build_pagination(array(
            'id' => 'admin_usersearch_pagination',
            'class' => 'center',
            'url' => $searchurl,
            'count' => $results['count'],
            'setlimit' => true,
            'limit' => $limit,
            'jumplinks' => 8,
            'numbersincludeprevnext' => 2,
            'offset' => $offset,
            'datatable' => 'searchresults',
            'searchresultsheading' => 'resultsheading',
            'jsonscript' => 'admin/users/search.json.php',
    ));



    $cols = array(
        'select' =>  array(
            'mergefirst' => true,
            'headhtml' => '<div class="btn-group" role="group"><a class="btn btn-xs btn-default" href="" id="selectall">' . get_string('All') . '</a>&nbsp;<a class="btn active btn-xs btn-default" href="" id="selectnone">' . get_string('none') . '</a></div>',
            'template' => 'admin/users/searchselectcolumn.tpl',
            'class'    => 'nojs-hidden with-checkbox',
            'accessible' => get_string('bulkselect'),
        ),
        'icon' => array(
            'mergelast' => true,
            'template' => 'admin/users/searchiconcolumn.tpl',
            'accessible' => get_string('profileicon'),
        ),
        'firstname' => array(
            'name'     => get_string('firstname'),
            'sort'     => true,
            'template' => 'admin/users/searchfirstnamecolumn.tpl',
        ),
        'lastname' => array(
            'name'     => get_string('lastname'),
            'sort'     => true,
            'template' => 'admin/users/searchlastnamecolumn.tpl',
        ),
        'preferredname' => array(
            'name'     => get_string('displayname'),
            'sort'     => true,
        ),
        'username' => array(
            'name'     => get_string('username'),
            'sort'     => true,
            'template' => 'admin/users/searchusernamecolumn.tpl',
        ),
        'email' => array(
            'name'     => get_string('emails'),
            'sort'     => true,
            'help'     => true,
            'class'    => 'form-inline-align-bottom',
            'helplink' => get_help_icon('core', 'admin', 'usersearch', 'email'),
            'template' => 'admin/users/searchemailcolumn.tpl',
        ),
    );

    $institutions = get_records_assoc('institution', '', '', '', 'name,displayname');
    if (count($institutions) > 1) {
        $cols['institution'] = array(
            'name'     => get_string('institution'),
            'sort'     => false,
            'template' => 'admin/users/searchinstitutioncolumn.tpl',
        );
    }

    $cols['authname'] = array(
            'name'     => get_string('authentication'),
            'sort'     => true,
    );

    $cols['lastlogin'] = array(
        'name'      => get_string('lastlogin', 'admin'),
        'sort'      => true,
        'template'  => 'strftimedatetime.tpl',
    );



    if (!$USER->get('admin') && !$USER->is_institutional_admin()) {
        unset($cols['email']);
        if (!get_config('staffreports')) {
            $cols['select']['headhtml'] = '';
            $cols['select']['template'] = null;
            $cols['select']['class'] = 'nojs-hidden';
            $cols['select']['accessible'] = '';
        }
    }
    else if (!$USER->get('admin') && $results['data']) {
        foreach ($results['data'] as &$r) {
            if (!isset($r['email'])) {
                $r['email'] = '- ' . get_string('emailaddresshidden', 'admin') . ' -';
            }
        }
    }

    $smarty = smarty_core();
    $smarty->assign('results', $results);
    $smarty->assign('institutions', $institutions);
    $smarty->assign('USER', $USER);
    $smarty->assign('limit', $limit);
    $smarty->assign('limitoptions', array(10, 50, 100, 200, 500));
    $smarty->assign('cols', $cols);
    $smarty->assign('ncols', count($cols));
    $html = $smarty->fetch('searchresulttable.tpl');
    return array($html, $cols, $pagination, array(
        'url' => $searchurl,
        'sortby' => $search->sortby,
        'sortdir' => $search->sortdir
    ));
}

/**
 * Returns the search results for the export queue
 *
 * @param array  $search            The parameters we want to search against
 * @param int    $offset            What result to start showing paginated results from
 * @param int    $limit             How many results to show
 *
 * @return array  A data structure containing results (see top of file).
 */

function build_admin_export_queue_results($search, $offset, $limit) {
    global $USER;

    $wantedparams = array('query', 'sortby', 'sortdir', 'institution');
    $params = array();
    foreach ($search as $k => $v) {
        if (!in_array($k, $wantedparams)) {
            continue;
        }
        if (!empty($v)) {
            $params[] = $k . '=' . $v;
        }
    }
    $searchurl = get_config('wwwroot') . 'admin/users/exportqueue.php?' . join('&', $params) . '&limit=' . $limit;

    // Use get_admin_user_search_results() as it hooks into the same
    // funky stuff the user search box query does on user/search.php page.
    $search->exportqueue = true;
    $results = get_admin_user_search_results($search, $offset, $limit);
    // Now that we have the users we need to match them up with their export_queue data
    if (!empty($results['count'])) {
        foreach ($results['data'] as $key => $data) {
            $used = array();
            $exportdata = get_records_sql_assoc('
                SELECT *, ' . db_format_tsfield('e.starttime', 'started') . ',
                          ' . db_format_tsfield('e.ctime', 'added') . '
                FROM {export_queue} e
                JOIN {export_queue_items} ei
                ON e.id = ei.exportqueueid
                AND e.usr = ?
                AND e.id = ?
                GROUP BY e.id, ei.id
                ORDER BY collection, view', array($data['id'], $data['eid']));
            $exportdataall = false;
            if (empty($exportdata)) {
                // Try checking if it an 'all' export
                $exportdataall = get_record_sql("SELECT *, " . db_format_tsfield('starttime', 'started') . ", " . db_format_tsfield('ctime', 'added') . "
                                                 FROM {export_queue} WHERE id = ? AND type = ?", array($data['eid'], 'all'));
            }
            if (empty($exportdataall) && empty($exportdata)) {
                // we have a problem with this row so will mark as failed
                $results['data'][$key]['status'] = get_string('exportfailed', 'admin', format_date($data['status']));
                $results['data'][$key]['statustype'] = $statustype = 'failed';
                continue;
            }
            // To get the main content title/url/type/id we look at the first row of the exportdata.
            if ($exportdataall) {
                $firstitem = $exportdataall;
                $contentdata = new stdClass();
                $contentdata->title = get_string('allmydata', 'export');
                $contentdata->type = 'all';
                $results['data'][$key]['contentdata'] = $contentdata;
            }
            else {
                $firstitem = reset($exportdata);
                if (!empty($firstitem->type)) {
                    $contentdata = new stdClass();
                    $contentdata->title = get_string('exporting' . $firstitem->type, 'export');
                    $contentdata->type = $firstitem->type;
                    $results['data'][$key]['contentdata'] = $contentdata;
                }
                else {
                    $results['data'][$key]['contentdata'] = get_export_contentdata($firstitem);
                }
            }
            // To get the status we check if the starttime is set
            if (empty($firstitem->starttime)) {
                $status = get_string('exportpending', 'admin', format_date($firstitem->added));
                $statustype = 'pending';
            }
            else if (!empty($firstitem->starttime)) {
                $status = get_string('exportfailed', 'admin', format_date($firstitem->started));
                $statustype = 'failed';
            }
            $results['data'][$key]['status'] = $status;
            $results['data'][$key]['statustype'] = $statustype;

            // Add on the raw exportdata allowing us to show the titles of all pages / artefacts
            // @todo allow all the titles to be displayed in an expanding box/area
            $results['data'][$key]['exportdata'] = $exportdata;
        }
    }

    $pagination = build_pagination(array(
            'id' => 'admin_exportqueue_pagination',
            'class' => 'center',
            'url' => $searchurl,
            'count' => $results['count'],
            'setlimit' => true,
            'limit' => $limit,
            'jumplinks' => 8,
            'numbersincludeprevnext' => 2,
            'offset' => $offset,
            'datatable' => 'searchresults',
            'searchresultsheading' => 'resultsheading',
            'jsonscript' => 'admin/users/exportqueue.json.php',
    ));

    $cols = array(
        'icon' => array(
            'template' => 'admin/users/searchiconcolumn.tpl',
            'class'    => 'center',
            'accessible' => get_string('profileicon'),
        ),
        'firstname' => array(
            'name'     => get_string('firstname'),
            'sort'     => true,
            'template' => 'admin/users/searchfirstnamecolumn.tpl',
        ),
        'lastname' => array(
            'name'     => get_string('lastname'),
            'sort'     => true,
            'template' => 'admin/users/searchlastnamecolumn.tpl',
        ),
        'preferredname' => array(
            'name'     => get_string('displayname'),
            'sort'     => true,
        ),
        'username' => array(
            'name'     => get_string('username'),
            'sort'     => true,
            'template' => 'admin/users/searchusernamecolumn.tpl',
        ),
        'contentname' => array(
            'name'     => get_string('exportcontentname', 'admin'),
            'sort'     => false,
            'template' => 'admin/users/searchexportcontentcolumn.tpl',
        ),
        'status' => array(
            'name'     => get_string('status'),
            'sort'     => true,
            'template' => 'admin/users/searchexportstatuscolumn.tpl',
        ),
        'exportselect' => array(
            'headhtml' => get_string('requeue', 'export') . '<br><div class="btn-group" role="group"><a class="btn btn-xs btn-default" href="" id="selectallexport">' . get_string('All') . '</a>&nbsp;<a class="btn btn-xs btn-default" href="" id="selectnoneexport">' . get_string('none') . '</a></div>',
            'template' => 'admin/users/searchselectcolumnexport.tpl',
            'class'    => 'nojs-hidden-table-cell with-checkbox',
            'accessible' => get_string('bulkselect'),
        ),
        'deleteselect' => array(
            'headhtml' => get_string('delete') . '<br><div class="btn-group" role="group"><a class="btn btn-xs btn-default" href="" id="selectalldelete">' . get_string('All') . '</a>&nbsp;<a class="btn btn-xs btn-default" href="" id="selectnonedelete">' . get_string('none') . '</a></div>',
            'template' => 'admin/users/searchselectcolumnexportdelete.tpl',
            'class'    => 'nojs-hidden-table-cell with-checkbox',
            'accessible' => get_string('bulkselect'),
        ),
    );

    $smarty = smarty_core();
    $smarty->assign('results', $results);
    $smarty->assign('USER', $USER);
    $smarty->assign('limit', $limit);
    $smarty->assign('limitoptions', array(10, 50, 100, 200, 500));
    $smarty->assign('cols', $cols);
    $smarty->assign('ncols', count($cols));
    $html = $smarty->fetch('searchresulttable.tpl');
    if ($html != '') {
        $html .= $smarty->fetch('searchresulttablebuttons.tpl');
    }

    return array($html, $cols, $pagination, array(
        'url' => $searchurl,
        'sortby' => $search->sortby,
        'sortdir' => $search->sortdir
    ));
}

/**
 * Returns the search results for the archived submissions
 *
 * @param array  $search            The parameters we want to search against
 * @param int    $offset            What result to start showing paginated results from
 * @param int    $limit             How many results to show
 *
 * @return array  A data structure containing results (see top of file).
 */

function build_admin_archived_submissions_results($search, $offset, $limit) {
    global $USER;

    $wantedparams = array('query', 'sortby', 'sortdir', 'institution');
    $params = array();
    foreach ($search as $k => $v) {
        if (!in_array($k, $wantedparams)) {
            continue;
        }
        if (!empty($v)) {
            $params[] = $k . '=' . $v;
        }
    }
    $searchurl = get_config('wwwroot') . 'admin/groups/archives.php?' . join('&', $params) . '&limit=' . $limit;

    // Use get_admin_user_search_results() as it hooks into the same
    // funky stuff the user search box query does on user/search.php page.
    $search->archivedsubmissions = true;

    $results = get_admin_user_search_results($search, $offset, $limit);
    // Now that we have the users we need to do some last minute alterations
    if (!empty($results['count'])) {
        foreach ($results['data'] as $key => $data) {
            // alter the archivectime to be human readable
            $results['data'][$key]['archivectime'] = format_date($data['archivectime']);
            // make sure the archive file is still on server at the path 'filepath' (not moved or deleted by server admin)
            $results['data'][$key]['filemissing'] = (!file_exists($data['filepath'] . $data['filename'])) ? true : false;
            // make the deleted group name more human readable
            $results['data'][$key]['groupdeleted'] = false;
            if (preg_match('/^(.*?)(\.deleted\.)(.*)$/', $data['submittedto'], $matches)) {
                $results['data'][$key]['groupdeleted'] = true;
                $results['data'][$key]['submittedto'] = $matches[1] . ' (' . get_string('deleted') . ' ' . format_date($matches[3]) . ')';
            }
        }
    }

    $pagination = build_pagination(array(
            'id' => 'admin_exportqueue_pagination',
            'class' => 'center',
            'url' => $searchurl,
            'count' => $results['count'],
            'setlimit' => true,
            'limit' => $limit,
            'jumplinks' => 8,
            'numbersincludeprevnext' => 2,
            'offset' => $offset,
            'datatable' => 'searchresults',
            'searchresultsheading' => 'resultsheading',
            'jsonscript' => 'admin/groups/archives.json.php',
    ));

    $cols = array(
        'submittedto' => array(
            'name'     => get_string('submittedto', 'admin'),
            'sort'     => true,
            'template' => 'admin/groups/submittedtocontentcolumn.tpl',
        ),
        'specialid' => array(
            'name'     => get_string('ID', 'admin'),
            'sort'     => true,
        ),
        'icon' => array(
            'template' => 'admin/users/searchiconcolumn.tpl',
            'class'    => 'center',
            'accessible' => get_string('profileicon'),
        ),
        'firstname' => array(
            'name'     => get_string('firstname'),
            'sort'     => true,
            'template' => 'admin/users/searchfirstnamecolumn.tpl',
        ),
        'lastname' => array(
            'name'     => get_string('lastname'),
            'sort'     => true,
            'template' => 'admin/users/searchlastnamecolumn.tpl',
        ),
        'preferredname' => array(
            'name'     => get_string('displayname'),
            'sort'     => true,
        ),
        'username' => array(
            'name'     => get_string('username'),
            'sort'     => true,
            'template' => 'admin/users/searchusernamecolumn.tpl',
        ),
        'filetitle' => array(
            'name'     => get_string('filenameleap', 'admin'),
            'sort'     => true,
            'template' => 'admin/groups/leap2acontentcolumn.tpl',
        ),
        'archivectime' => array(
            'name'     => get_string('archivedon', 'admin'),
            'sort'     => true,
        ),
    );

    $smarty = smarty_core();
    $smarty->assign('results', $results);
    $smarty->assign('USER', $USER);
    $smarty->assign('limit', $limit);
    $smarty->assign('limitoptions', array(10, 50, 100, 200, 500));
    $smarty->assign('cols', $cols);
    $smarty->assign('ncols', count($cols));
    $html = $smarty->fetch('searchresulttable.tpl');

    return array($html, $cols, $pagination, array(
        'url' => $searchurl,
        'sortby' => $search->sortby,
        'sortdir' => $search->sortdir
    ));
}

/**
 * Return the title, type and id of the item based on which is more important
 *
 * Takes an array containing ids on either or all of these items with ranking
 * preference in this order:
 * - collection
 * - view
 * and returns the title, type, and id of which ever one is present and is highest ranked
 *
 * @param array  $item An array containing any or all of 'collection', 'view' ids
 * @return array The title/url/type/id information on the most senior one found.
 */
function get_export_contentdata($item) {
    // first make sure we have an array
    if (is_object($item)) {
        $item = (array)$item;
    }

    $record = new stdClass();
    $record->title = '';
    $record->url = null;
    $record->type = null;
    $record->id = 0;
    if (!empty($item['collection'])) {
        require_once('collection.php');
        $collection = new Collection($item['collection']);
        $views = $collection->get('views');
        $record->title = $collection->get('name');
        $record->url = $views['views'][0]->fullurl;
        $record->type = 'collection';
        $record->id = $item['collection'];
    }
    else if (!empty($item['view'])) {
        require_once('view.php');
        $view = new View($item['view']);
        $record->title = $view->get('title');
        $record->url = get_config('wwwroot') . 'view/view.php?id=' . $item['view'];
        $record->type = 'view';
        $record->id = $item['view'];
    }
    return $record;
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
 * @param int    $friendof          Only return friends of this user
 *
 */
function get_group_user_search_results($group, $query, $offset, $limit, $membershiptype, $order=null, $friendof=null, $sortoptionidx=null) {
    $plugin = get_config('searchplugin');
    safe_require('search', $plugin);
    $searchclass = generate_class_name('search', $plugin);

    $constraints = array();
    if (call_static_method($searchclass, 'can_process_raw_group_search_user_queries')) {
        // Pass the raw query string through to group_search_user; parsing of the
        // query depends on the plugin configuration.
        $queries = $query;
    }
    else {
        $queries = array();
        if (!empty($query)) {
            list($words, $fullnames) = parse_name_query($query);
            foreach ($words as $word) {
                $queries[] = array(
                    'field'  => 'firstname',
                    'type'   => 'contains',
                    'string' => $word
                );
                $queries[] = array(
                    'field'  => 'lastname',
                    'type'   => 'contains',
                    'string' => $word
                );
            }
            foreach ($fullnames as $n) {
                $constraints[] = array(
                    'field'  => 'firstname',
                    'type'   => 'contains',
                    'string' => $n[0]
                );
                $constraints[] = array(
                    'field'  => 'lastname',
                    'type'   => 'contains',
                    'string' => $n[1]
                );
            }
        }
    }

    $results = call_static_method(
        $searchclass,
        'group_search_user',
        $group, $queries, $constraints, $offset, $limit, $membershiptype, $order, $friendof, $sortoptionidx
    );

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
                $result['jointime'] = format_date($result['jointime'], 'strftimedate');
            }
        }
    }
    return $results;
}


/**
 * Given a query string and limits, return an array of matching groups using the
 * search plugin defined in config.php
 *
 * @param string  The query string
 * @param integer How many results to return
 * @param integer What result to start at (0 == first result)
 * @param string  Category the group belongs to
 * @param string  The institution the group belongs
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
function search_group($query_string, $limit, $offset = 0, $type = 'member', $groupcategory = '', $institution='all') {
    $plugin = get_config('searchplugin');
    safe_require('search', $plugin);

    return call_static_method(generate_class_name('search', $plugin), 'search_group', $query_string, $limit, $offset, $type, $groupcategory, $institution);
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
        return array('view' => true, 'collection' => false, 'artefact' => false);
    }
    if ($filter == 'collection') {
        return array('view' => false, 'collection' => true, 'artefact' => false);
    }
    require_once(get_config('docroot') . 'artefact/lib.php');
    return array('view' => false, 'collection' => false, 'artefact' => artefact_get_types_from_filter($filter));
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
            safe_require_plugin('search', $plugin->name, 'lib.php');
            if (!call_static_method(generate_class_name('search', $plugin->name), 'is_available_for_site_setting')) {
                continue;
            }

            $searchpluginoptions[$plugin->name] = $plugin->name;

            $config_path = get_config('docroot') . 'search/' . $plugin->name . '/version.php';
            if (is_readable($config_path)) {
                $config = new stdClass();
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
