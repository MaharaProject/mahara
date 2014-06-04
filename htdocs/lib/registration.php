<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @file Register a mahara site
 */
/**
 * @defgroup Registration Registration
 * Send site information to mahara.org
 *
 */

defined('INTERNAL') || die();

/**
 * @return string that is the registation form
 * @ingroup Registration
 */
function register_site()  {
    $strfield = get_string('Field', 'admin');
    $strvalue = get_string('Value', 'admin');
    $info = <<<EOF
<tr><td>
<table>
    <tr>
        <th>$strfield</th>
        <th>$strvalue</th>
    </tr>
EOF;
    $data = registration_data();
    foreach($data as $key => $val) {
        $info .= '<tr><td>'. hsc($key) . '</td><td>' . hsc($val) . "</td></tr>\n";
    }
    $info .= '</table></td></tr>';

    $form = array(
        'name' => 'register',
        'autofocus' => false,
        'elements' => array(
            'whatsent' => array(
                'type' => 'fieldset',
                'legend' => get_string('datathatwillbesent', 'admin'),
                'collapsible' => true,
                'collapsed' => true,
                'elements' => array(
                    'info' => array(
                        'type' => 'markup',
                        'value'=> $info,
                    ),
                )
            ),
            'sendweeklyupdates' => array(
                'type' => 'checkbox',
                'title' => get_string('sendweeklyupdates', 'admin'),
                'defaultvalue' => true,
            ),
            'register' => array(
                'type' => 'submit',
                'value' => get_string('Register', 'admin'),
            ),
        )
     );

     return pieform($form);
}
/**
 * Runs when registration form is submitted
 */
function register_submit(Pieform $form, $values) {
    global $SESSION;

    $result = registration_send_data();
    $data = json_decode($result->data);

    if ($data->status != 1) {
        log_info($result);
        $SESSION->add_error_msg(get_string('registrationfailedtrylater', 'admin', $result->info['http_code']));
    }
    else {
        set_config('registration_lastsent', time());
        set_config('registration_sendweeklyupdates', $values['sendweeklyupdates']);
        $SESSION->add_ok_msg(get_string('registrationsuccessfulthanksforregistering', 'admin'));
    }
    redirect('/admin/');
}


/**
 * Worker - performs sending of registration data to mahara.org
 */
function registration_send_data() {
    $registrationurl = 'https://mahara.org/api/registration.php';
    $data = registration_data();
    $request = array(
        CURLOPT_URL        => $registrationurl,
        CURLOPT_POST       => 1,
        CURLOPT_POSTFIELDS => $data,
    );
    return mahara_http_request($request);
}

function registration_store_data() {
    $data = registration_data();
    db_begin();
    $registration_id = insert_record('site_registration', (object)array(
        'time' => db_format_timestamp(time()),
    ), 'id', true);
    foreach ($data as $key => $value) {
        insert_record('site_registration_data', (object)array(
            'registration_id' => $registration_id,
            'field'           => $key,
            'value'           => ($value == null ? '' : $value)
        ));
    }
    db_commit();
}

/**
 * Builds the data that will be sent by the "register your site" feature
 */
function registration_data() {
    foreach (array(
        'wwwroot',
        'installation_key',
        'sitename',
        'dbtype',
        'lang',
        'theme',
        'enablenetworking',
        'allowpublicviews',
        'allowpublicprofiles',
        'version',
        'release') as $key) {
        $data_to_send[$key] = get_config($key);
    }

    foreach (array(
        'usr_friend',
        'usr_institution',
        'group_member',
        'block_instance',
        'institution',
        'blocktype_wall_post',
        'institution') as $key) {
        $data_to_send['count_' . $key] = count_records($key);
    }

    foreach (array(
        'usr',
        'group',
        'host') as $key) {
        $data_to_send['count_' . $key] = count_records_select($key, 'deleted = 0');
        }

    // Don't include the root user
    $data_to_send['count_usr']--;

    // Slightly more drilled down information
    if ($data = get_records_sql_array('SELECT artefacttype, COUNT(*) AS count
        FROM {artefact}
        GROUP BY artefacttype', array())) {
        foreach ($data as $artefacttypeinfo) {
            $data_to_send['artefact_type_' . $artefacttypeinfo->artefacttype] = $artefacttypeinfo->count;
        }
    }

    if ($data = get_records_sql_array('SELECT type, COUNT(*) AS count
        FROM {view}
        GROUP BY type', array())) {
        foreach ($data as $viewtypeinfo) {
            $data_to_send['view_type_' . $viewtypeinfo->type] = $viewtypeinfo->count;
        }
    }

    // Plugin versions
    foreach (plugin_types() as $type) {
        foreach (plugins_installed($type) as $plugin) {
            $data_to_send['plugin_' . $type . '_' . $plugin->name . '_version'] = $plugin->version;
        }
    }

    $data_to_send['newstats'] = 1;

    return $data_to_send;
}

function institution_registration_store_data() {
    $data = institution_registration_data();
    db_begin();
    foreach ($data as $institution => $inst_data) {
        $registration_id = insert_record('institution_registration', (object)array(
            'time'        => db_format_timestamp(time()),
            'institution' => $institution,
        ), 'id', true);
        foreach ($inst_data as $key => $value) {
            insert_record('institution_registration_data', (object)array(
                'registration_id' => $registration_id,
                'field'           => $key,
                'value'           => $value
            ));
        }
    }
    db_commit();
}

function institution_registration_data() {
    $data_to_store = array();
    foreach (get_column('institution', 'name') as $institution) {
        $inst_data = array();
        if ($institution == 'mahara') {
            $members = get_column_sql('SELECT id
                    FROM {usr}
                    WHERE deleted = 0 AND id > 0 AND id NOT IN
                        (SELECT usr FROM {usr_institution})
                    ', array());
        }
        else {
            $members = get_column_sql('SELECT usr
                    FROM {usr_institution} ui
                    JOIN {usr} u ON (u.id = ui.usr)
                    WHERE u.deleted = 0 AND ui.institution = ?
                    ', array($institution));
        }
        $inst_data['count_members'] =  count($members);
        if (!$members) {
            $inst_data['count_views'] = 0;
            $inst_data['count_blocks'] = 0;
            $inst_data['count_artefacts'] = 0;
            $inst_data['count_interaction_forum_post'] = 0;
            $inst_data['usersloggedin'] = 0;
            $data_to_store[$institution] = $inst_data;
            continue;
        }
        $inst_data['count_views'] = 0;
        if ($data = get_records_sql_array('SELECT tmp.type, SUM(tmp.count) AS count
                FROM (SELECT v.type, COUNT(*) AS count
                    FROM {view} v
                    WHERE v.owner IS NOT NULL AND v.owner IN (' . join(',', array_fill(0, count($members), '?')) . ')
                    GROUP BY v.type
                UNION ALL
                    SELECT v.type, COUNT(*) AS count
                    FROM {view} v
                    WHERE v.institution IS NOT NULL AND v.institution = ?
                    GROUP BY v.type
                ) tmp GROUP BY tmp.type', array_merge($members, array($institution)))) {
            foreach ($data as $viewtypeinfo) {
                $inst_data['view_type_' . $viewtypeinfo->type] = $viewtypeinfo->count;
                $inst_data['count_views'] += $viewtypeinfo->count;
            }
        }
        $inst_data['count_blocks'] = 0;
        if ($data = get_records_sql_array('SELECT tmp.type, SUM(tmp.count) AS count
                FROM (SELECT bi.blocktype AS type, COUNT(*) AS count
                    FROM {block_instance} bi
                    JOIN {view} v ON v.id = bi.view
                    WHERE v.owner IS NOT NULL AND v.owner IN (' . join(',', array_fill(0, count($members), '?')) . ')
                    GROUP BY bi.blocktype
                UNION ALL
                    SELECT bi.blocktype AS type, COUNT(*) AS count
                    FROM {block_instance} bi
                    JOIN {view} v ON v.id = bi.view
                    WHERE v.institution IS NOT NULL AND v.institution = ?
                    GROUP BY bi.blocktype
                ) tmp GROUP BY tmp.type', array_merge($members, array($institution)))) {
            foreach ($data as $blocktypeinfo) {
                $inst_data['blocktype_' . $blocktypeinfo->type] = $blocktypeinfo->count;
                $inst_data['count_blocks'] += $blocktypeinfo->count;
            }
        }
        $inst_data['count_artefacts'] = 0;
        if ($data = get_records_sql_array('SELECT a.artefacttype AS type, COUNT(*) AS count
                FROM {artefact} a
                WHERE a.author IN (' . join(',', array_fill(0, count($members), '?')) . ')
                GROUP BY a.artefacttype', $members)) {
            foreach ($data as $artefacttypeinfo) {
                $inst_data['artefact_type_' . $artefacttypeinfo->type] = $artefacttypeinfo->count;
                $inst_data['count_artefacts'] += $artefacttypeinfo->count;
            }
        }
        $inst_data['count_interaction_forum_post'] = count_records_select('interaction_forum_post',
                'poster IN (' . join(',', array_fill(0, count($members), '?')) . ')',
                $members);
        if (is_postgres()) {
            $weekago = "CURRENT_DATE - INTERVAL '1 week'";
            $thisweeksql = "(lastaccess > $weekago)::int";
        }
        else {
            $weekago = 'CURRENT_DATE - INTERVAL 1 WEEK';
            $thisweeksql = "lastaccess > $weekago";
        }
        if ($data = get_record_sql('SELECT SUM(' . $thisweeksql . ') AS sum
                FROM {usr} u
                WHERE u.id IN (' . join(',', array_fill(0, count($members), '?')) . ')',
                $members)) {
            $inst_data['usersloggedin'] = isset($data->sum) ? $data->sum : 0;
        }
        else {
            $inst_data['usersloggedin'] = 0;
        }

        $data_to_store[$institution] = $inst_data;
    }
    return $data_to_store;
}

function site_data_current() {
    return array(
        'users' => count_records_select('usr', 'id > 0 AND deleted = 0'),
        'groups' => count_records('group', 'deleted', 0),
        'views' => count_records_select('view', 'owner != 0 OR owner IS NULL'),
    );
}

function site_statistics($full=false) {
    $data = array();

    if ($full) {
        $data = site_data_current();
        $data['weekly'] = stats_graph_url('weekly');

        if (is_postgres()) {
            $weekago = "CURRENT_DATE - INTERVAL '1 week'";
            $thisweeksql = "(lastaccess > $weekago)::int";
            $todaysql = '(lastaccess > CURRENT_DATE)::int';
            $eversql = "(NOT lastaccess IS NULL)::int";
        }
        else {
            $weekago = 'CURRENT_DATE - INTERVAL 1 WEEK';
            $thisweeksql = "lastaccess > $weekago";
            $todaysql = 'lastaccess > CURRENT_DATE';
            $eversql = "NOT lastaccess IS NULL";
        }
        $sql = "SELECT SUM($todaysql) AS today, SUM($thisweeksql) AS thisweek, $weekago AS weekago, SUM($eversql) AS ever FROM {usr}";
        $active = get_record_sql($sql);
        $data['usersloggedin'] = get_string('loggedinsince', 'admin', $active->today, $active->thisweek, format_date(strtotime($active->weekago), 'strftimedateshort'), $active->ever);

        $memberships = count_records_sql("
            SELECT COUNT(*)
            FROM {group_member} m JOIN {group} g ON g.id = m.group
            WHERE g.deleted = 0
        ");
        $data['groupmemberaverage'] = round($memberships/$data['users'], 1);
        $data['strgroupmemberaverage'] = get_string('groupmemberaverage', 'admin', $data['groupmemberaverage']);
        $data['viewsperuser'] = get_field_sql("
            SELECT (0.0 + COUNT(id)) / NULLIF(COUNT(DISTINCT \"owner\"), 0)
            FROM {view}
            WHERE NOT \"owner\" IS NULL AND \"owner\" > 0
        ");
        $data['viewsperuser'] = round($data['viewsperuser'], 1);
        $data['strviewsperuser'] = get_string('viewsperuser', 'admin', $data['viewsperuser']);
    }

    $data['name']        = get_config('sitename');
    $data['release']     = get_config('release');
    $data['version']     = get_config('version');
    $data['installdate'] = format_date(strtotime(get_config('installation_time')), 'strftimedate');
    $data['dbsize']      = db_total_size();
    $data['diskusage']   = get_field('site_data', 'value', 'type', 'disk-usage');
    $data['cronrunning'] = !record_exists_select('cron', 'nextrun IS NULL OR nextrun < CURRENT_DATE');

    if ($latestversion = get_config('latest_version')) {
        $data['latest_version'] = $latestversion;
        if ($data['release'] == $latestversion) {
            $data['strlatestversion'] = get_string('uptodate', 'admin');
        }
        else {
            $download_page = 'https://launchpad.net/mahara/+download';
            $data['strlatestversion'] = get_string('latestversionis', 'admin', $download_page, $latestversion);
        }
    }

    return($data);
}

function institution_data_current($institution) {
    $data = array();
    if ($institution == 'mahara') {
        $data['members'] = get_column_sql('SELECT id
                FROM {usr}
                WHERE deleted = 0 AND id > 0 AND id NOT IN
                    (SELECT usr FROM {usr_institution})
                ', array());
    }
    else {
        $data['members'] = get_column_sql('SELECT usr
                FROM {usr_institution} ui
                JOIN {usr} u ON (u.id = ui.usr)
                WHERE u.deleted = 0 AND ui.institution = ?
                ', array($institution));
    }
    $data['users'] = count($data['members']);
    if (!$data['users']) {
        $data['views'] = 0;
    }
    else {
        $data['viewids'] = get_column_sql('
                SELECT id FROM {view}
                    WHERE owner IS NOT NULL AND owner IN (' . join(',', array_fill(0, $data['users'], '?')) . ')
                UNION
                    SELECT id FROM {view}
                    WHERE institution IS NOT NULL AND institution = ?'
                , array_merge($data['members'], array($institution)));
        $data['views'] = count($data['viewids']);
    }
    return $data;
}

function institution_statistics($institution, $full=false) {
    $data = array();

    if ($full) {
        $data = institution_data_current($institution);
        $data['weekly'] = stats_graph_url($institution . '_weekly');

        if (is_postgres()) {
            $weekago = "CURRENT_DATE - INTERVAL '1 week'";
            $thisweeksql = "COALESCE((lastaccess > $weekago)::int, 0)::int";
            $todaysql = 'COALESCE((lastaccess > CURRENT_DATE)::int, 0)::int';
            $eversql = "(NOT lastaccess IS NULL)::int";
        }
        else {
            $weekago = 'CURRENT_DATE - INTERVAL 1 week';
            $thisweeksql = "COALESCE(lastaccess > $weekago, 0)";
            $todaysql = 'COALESCE(lastaccess > CURRENT_DATE, 0)';
            $eversql = "NOT lastaccess IS NULL";
        }
        if (!$data['users']) {
            $active = get_record_sql("SELECT 0 AS today, 0 AS thisweek, $weekago AS weekago, 0 AS ever");
        }
        else {
            $sql = "SELECT SUM($todaysql) AS today, SUM($thisweeksql) AS thisweek, $weekago AS weekago, SUM($eversql) AS ever FROM {usr}
                    WHERE id IN (" . join(',', array_fill(0, $data['users'], '?')) . ")";
            $active = get_record_sql($sql, $data['members']);
        }
        $data['usersloggedin'] = get_string('loggedinsince', 'admin', $active->today, $active->thisweek, format_date(strtotime($active->weekago), 'strftimedateshort'), $active->ever);

        if (!$data['users']) {
            $data['groupmemberaverage'] = 0;
        }
        else {
            $memberships = count_records_sql("
                SELECT COUNT(*)
                FROM {group_member} m JOIN {group} g ON g.id = m.group
                WHERE g.deleted = 0 AND m.member IN (" . join(',', array_fill(0, $data['users'], '?')) . ")
            ", $data['members']);
            $data['groupmemberaverage'] = round($memberships/$data['users'], 1);
        }
        $data['strgroupmemberaverage'] = get_string('groupmemberaverage', 'admin', $data['groupmemberaverage']);
        if (!$data['views']) {
            $data['viewsperuser'] = 0;
        }
        else {
            $data['viewsperuser'] = get_field_sql("
                SELECT (0.0 + COUNT(id)) / NULLIF(COUNT(DISTINCT \"owner\"), 0)
                FROM {view}
                WHERE id IN (" . join(',', array_fill(0, $data['views'], '?')) . ")
            ", $data['viewids']);
            $data['viewsperuser'] = round($data['viewsperuser'], 1);
        }
        $data['strviewsperuser'] = get_string('viewsperuser', 'admin', $data['viewsperuser']);
    }

    $data['name']        = $institution;
    $data['release']     = get_config('release');
    $data['version']     = get_config('version');
    if ($institution == 'mahara') {
        $data['installdate'] = format_date(strtotime(get_config('installation_time')), 'strftimedate');
    }
    else {
        // *** FIXME: See if better way to get this
        $data['installdate'] = format_date(strtotime(get_field_sql('SELECT MIN(ui.ctime) FROM {usr_institution} ui WHERE ui.institution = ?', array($institution))), 'strftimedate');
    }
    if ($data['users']) {
        $data['diskusage']   = get_field_sql("
            SELECT SUM(quotaused)
            FROM {usr}
            WHERE deleted = 0 AND id IN (" . join(',', array_fill(0, $data['users'], '?')) . ")
            ", $data['members']);
    }

    return($data);
}

function user_statistics($limit, $offset, &$sitedata) {
    $data = array();
    $data['tableheadings'] = array(
        array('name' => get_string('date')),
        array('name' => get_string('Loggedin', 'admin'), 'class' => 'center'),
        array('name' => get_string('Created'), 'class' => 'center'),
        array('name' => get_string('Total'), 'class' => 'center'),
    );
    $data['table'] = user_stats_table($limit, $offset);
    $data['tabletitle'] = get_string('userstatstabletitle', 'admin');

    $maxfriends = get_records_sql_array("
        SELECT u.id, u.firstname, u.lastname, u.preferredname, u.urlid, SUM(f.friends) AS friends
        FROM {usr} u INNER JOIN (
            SELECT DISTINCT(usr1) AS id, COUNT(usr1) AS friends
            FROM {usr_friend}
            GROUP BY usr1
            UNION SELECT DISTINCT(usr2) AS id, COUNT(usr2) AS friends
            FROM {usr_friend}
            GROUP BY usr2
        ) f ON u.id = f.id
        GROUP BY u.id, u.firstname, u.lastname, u.preferredname, u.urlid
        ORDER BY friends DESC
        LIMIT 1", array());
    $maxfriends = $maxfriends[0];
    $meanfriends = 2 * count_records('usr_friend') / $sitedata['users'];
    if ($maxfriends) {
        $data['strmaxfriends'] = get_string(
            'statsmaxfriends',
            'admin',
            round($meanfriends, 1),
            profile_url($maxfriends),
            hsc(display_name($maxfriends, null, true)),
            $maxfriends->friends
        );
    }
    else {
        $data['strmaxfriends'] = get_string('statsnofriends', 'admin');
    }
    $maxviews = get_records_sql_array("
        SELECT u.id, u.firstname, u.lastname, u.preferredname, u.urlid, COUNT(v.id) AS views
        FROM {usr} u JOIN {view} v ON u.id = v.owner
        WHERE \"owner\" <> 0
        GROUP BY u.id, u.firstname, u.lastname, u.preferredname, u.urlid
        ORDER BY views DESC
        LIMIT 1", array());
    $maxviews = $maxviews[0];
    if ($maxviews) {
        $data['strmaxviews'] = get_string(
            'statsmaxviews',
            'admin',
            $sitedata['viewsperuser'],
            profile_url($maxviews),
            hsc(display_name($maxviews, null, true)),
            $maxviews->views
        );
    }
    else {
        $data['strmaxviews'] = get_string('statsnoviews', 'admin');
    }
    $maxgroups = get_records_sql_array("
        SELECT u.id, u.firstname, u.lastname, u.preferredname, u.urlid, COUNT(m.group) AS groups
        FROM {usr} u JOIN {group_member} m ON u.id = m.member JOIN {group} g ON m.group = g.id
        WHERE g.deleted = 0
        GROUP BY u.id, u.firstname, u.lastname, u.preferredname, u.urlid
        ORDER BY groups DESC
        LIMIT 1", array());
    $maxgroups = $maxgroups[0];
    if ($maxgroups) {
        $data['strmaxgroups'] = get_string(
            'statsmaxgroups',
            'admin',
            $sitedata['groupmemberaverage'],
            profile_url($maxgroups),
            hsc(display_name($maxgroups, null, true)),
            $maxgroups->groups
        );
    }
    else {
        $data['strmaxgroups'] = get_string('statsnogroups', 'admin');
    }
    $maxquotaused = get_records_sql_array("
        SELECT id, firstname, lastname, preferredname, urlid, quotaused
        FROM {usr}
        WHERE deleted = 0 AND id > 0
        ORDER BY quotaused DESC
        LIMIT 1", array());
    $maxquotaused = $maxquotaused[0];
    $data['strmaxquotaused'] = get_string(
        'statsmaxquotaused',
        'admin',
        display_size(get_field('usr', 'AVG(quotaused)', 'deleted', 0)),
        profile_url($maxquotaused),
        hsc(display_name($maxquotaused, null, true)),
        display_size($maxquotaused->quotaused)
    );

    $data['institutions'] = stats_graph_url('institutions');

    $smarty = smarty_core();
    $smarty->assign('data', $data);
    $data['summary'] = $smarty->fetch('admin/userstatssummary.tpl');

    return $data;
}

function user_stats_table($limit, $offset) {
    global $USER;

    $count = count_records('site_data', 'type', 'user-count-daily');

    $pagination = build_pagination(array(
        'id' => 'stats_pagination',
        'url' => get_config('wwwroot') . 'admin/statistics.php?type=users',
        'jsonscript' => 'admin/statistics.json.php',
        'datatable' => 'statistics_table',
        'count' => $count,
        'limit' => $limit,
        'offset' => $offset,
        'setlimit' => true,
    ));

    $result = array(
        'count'         => $count,
        'tablerows'     => '',
        'pagination'    => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
    );

    if ($count < 1) {
        return $result;
    }

    $day = is_postgres() ? "to_date(t.ctime::text, 'YYYY-MM-DD')" : 'DATE(t.ctime)'; // TODO: make work on other databases?

    $daterange = get_record_sql(
        "SELECT
            MIN($day) AS mindate,
            MAX($day) AS maxdate
        FROM (
            SELECT ctime
            FROM {site_data}
            WHERE type = ?
            ORDER BY ctime DESC
            LIMIT $limit
            OFFSET $offset
        ) t",
        array('user-count-daily')
    );

    $dayinterval = is_postgres() ? "'1 day'" : '1 day';

    $day = is_postgres() ? "to_date(ctime::text, 'YYYY-MM-DD')" : 'DATE(ctime)';

    $userdata = get_records_sql_array(
        "SELECT ctime, type, \"value\", $day AS date
        FROM {site_data}
        WHERE type IN (?,?) AND ctime >= ? AND ctime < (date(?) + INTERVAL $dayinterval)
        ORDER BY type = ? DESC, ctime DESC",
        array('user-count-daily', 'loggedin-users-daily', $daterange->mindate, $daterange->maxdate, 'user-count-daily')
    );

    $userscreated = get_records_sql_array(
        "SELECT $day AS cdate, COUNT(id) AS users
        FROM {usr}
        WHERE NOT ctime IS NULL AND ctime >= ? AND ctime < (date(?) + INTERVAL $dayinterval)
        GROUP BY cdate",
        array($daterange->mindate, $daterange->maxdate)
    );

    $data = array();

    if ($userdata) {
        foreach ($userdata as &$r) {
            if ($r->type == 'user-count-daily') {
                $data[$r->date] = array(
                    'date'  => $r->date,
                    'total' => $r->value,
                );
            }
            else if ($r->type == 'loggedin-users-daily' && isset($data[$r->date])) {
                $data[$r->date]['loggedin'] = $r->value;
            }
        }
        if ($userscreated) {
            foreach ($userscreated as &$r) {
                if (isset($data[$r->cdate])) {
                    $data[$r->cdate]['created'] = $r->users;
                }
            }
        }
    }

    $csvfields = array('date', 'loggedin', 'created', 'total');
    $USER->set_download_file(generate_csv($data, $csvfields), 'userstatistics.csv', 'text/csv');
    $result['csv'] = true;

    $smarty = smarty_core();
    $smarty->assign('data', $data);
    $result['tablerows'] = $smarty->fetch('admin/userstats.tpl');

    return $result;
}

function institution_user_statistics($limit, $offset, &$institutiondata) {
    $data = array();
    $data['tableheadings'] = array(
        array('name' => get_string('date')),
        array('name' => get_string('Loggedin', 'admin'), 'class' => 'center'),
        array('name' => get_string('Joined', 'group'), 'class' => 'center'),
        array('name' => get_string('Total'), 'class' => 'center'),
    );
    $data['table'] = institution_user_stats_table($limit, $offset, $institutiondata);
    $data['tabletitle'] = get_string('userstatstabletitle', 'admin');

    if (!$institutiondata['users']) {
        $data['strmaxfriends'] = get_string('statsnofriends', 'admin');
        $data['strmaxviews'] = get_string('statsnoviews', 'admin');
        $data['strmaxgroups'] = get_string('statsnogroups', 'admin');
        $data['strmaxquotaused'] = get_string('statsnoquota', 'admin');

        $smarty = smarty_core();
        $smarty->assign('data', $data);
        $data['summary'] = $smarty->fetch('admin/userstatssummary.tpl');

        return $data;
    }

    $maxfriends = get_records_sql_array("
        SELECT u.id, u.firstname, u.lastname, u.preferredname, u.urlid, SUM(f.friends) AS friends
        FROM {usr} u INNER JOIN (
            SELECT DISTINCT(usr1) AS id, COUNT(usr1) AS friends
            FROM {usr_friend}
            GROUP BY usr1
            UNION SELECT DISTINCT(usr2) AS id, COUNT(usr2) AS friends
            FROM {usr_friend}
            GROUP BY usr2
        ) f ON u.id = f.id
        WHERE u.id IN (" . join(',', array_fill(0, $institutiondata['users'], '?')) . ")
        GROUP BY u.id, u.firstname, u.lastname, u.preferredname, u.urlid
        ORDER BY friends DESC
        LIMIT 1", $institutiondata['members']);
    $maxfriends = $maxfriends[0];
    $meanfriends = count_records_sql('SELECT COUNT(*) FROM
                (SELECT * FROM {usr_friend}
                    WHERE usr1 IN (' . join(',', array_fill(0, $institutiondata['users'], '?')) . ')
                UNION ALL SELECT * FROM {usr_friend}
                    WHERE usr2 IN (' . join(',', array_fill(0, $institutiondata['users'], '?')) . ')
                ) tmp', array_merge($institutiondata['members'], $institutiondata['members'])) / $institutiondata['users'];
    if ($maxfriends) {
        $data['strmaxfriends'] = get_string(
            'statsmaxfriends',
            'admin',
            round($meanfriends, 1),
            profile_url($maxfriends),
            hsc(display_name($maxfriends, null, true)),
            $maxfriends->friends
        );
    }
    else {
        $data['strmaxfriends'] = get_string('statsnofriends', 'admin');
    }
    $maxviews = get_records_sql_array("
        SELECT u.id, u.firstname, u.lastname, u.preferredname, u.urlid, COUNT(v.id) AS views
        FROM {usr} u JOIN {view} v ON u.id = v.owner
        WHERE \"owner\" IN (" . join(',', array_fill(0, $institutiondata['users'], '?')) . ")
        GROUP BY u.id, u.firstname, u.lastname, u.preferredname, u.urlid
        ORDER BY views DESC
        LIMIT 1", $institutiondata['members']);
    $maxviews = $maxviews[0];
    if ($maxviews) {
        $data['strmaxviews'] = get_string(
            'statsmaxviews',
            'admin',
            $institutiondata['viewsperuser'],
            profile_url($maxviews),
            hsc(display_name($maxviews, null, true)),
            $maxviews->views
        );
    }
    else {
        $data['strmaxviews'] = get_string('statsnoviews', 'admin');
    }
    $maxgroups = get_records_sql_array("
        SELECT u.id, u.firstname, u.lastname, u.preferredname, u.urlid, COUNT(m.group) AS groups
        FROM {usr} u JOIN {group_member} m ON u.id = m.member JOIN {group} g ON m.group = g.id
        WHERE g.deleted = 0 AND u.id IN (" . join(',', array_fill(0, $institutiondata['users'], '?')) . ")
        GROUP BY u.id, u.firstname, u.lastname, u.preferredname, u.urlid
        ORDER BY groups DESC
        LIMIT 1", $institutiondata['members']);
    $maxgroups = $maxgroups[0];
    if ($maxgroups) {
        $data['strmaxgroups'] = get_string(
            'statsmaxgroups',
            'admin',
            $institutiondata['groupmemberaverage'],
            profile_url($maxgroups),
            hsc(display_name($maxgroups, null, true)),
            $maxgroups->groups
        );
    }
    else {
        $data['strmaxgroups'] = get_string('statsnogroups', 'admin');
    }
    $maxquotaused = get_records_sql_array("
        SELECT id, firstname, lastname, preferredname, urlid, quotaused
        FROM {usr}
        WHERE id IN (" . join(',', array_fill(0, $institutiondata['users'], '?')) . ")
        ORDER BY quotaused DESC
        LIMIT 1", $institutiondata['members']);
    $maxquotaused = $maxquotaused[0];
    $avgquota = get_field_sql("
        SELECT AVG(quotaused)
        FROM {usr}
        WHERE id IN (" . join(',', array_fill(0, $institutiondata['users'], '?')) . ")
        ", $institutiondata['members']);
    $data['strmaxquotaused'] = get_string(
        'statsmaxquotaused',
        'admin',
        display_size($avgquota),
        profile_url($maxquotaused),
        hsc(display_name($maxquotaused, null, true)),
        display_size($maxquotaused->quotaused)
    );

    $smarty = smarty_core();
    $smarty->assign('data', $data);
    $data['summary'] = $smarty->fetch('admin/userstatssummary.tpl');

    return $data;
}

function institution_user_stats_table($limit, $offset, &$institutiondata) {
    global $USER;

    $count = count_records('institution_data', 'type', 'user-count-daily', 'institution', $institutiondata['name']);

    $pagination = build_pagination(array(
        'id' => 'stats_pagination',
        'url' => get_config('wwwroot') . 'admin/users/statistics.php?institution=' . $institutiondata['name'] . '&type=users',
        'jsonscript' => 'admin/users/statistics.json.php',
        'datatable' => 'statistics_table',
        'count' => $count,
        'limit' => $limit,
        'offset' => $offset,
        'extradata' => array('institution' => $institutiondata['name']),
        'setlimit' => true,
    ));

    $result = array(
        'count'         => $count,
        'tablerows'     => '',
        'pagination'    => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
    );

    if ($count < 1) {
        return $result;
    }

    $day = is_postgres() ? "to_date(t.ctime::text, 'YYYY-MM-DD')" : 'DATE(t.ctime)'; // TODO: make work on other databases?

    $daterange = get_record_sql(
        "SELECT
            MIN($day) AS mindate,
            MAX($day) AS maxdate
        FROM (
            SELECT ctime
            FROM {institution_data}
            WHERE type = ? AND institution = ?
            ORDER BY ctime DESC
            LIMIT $limit
            OFFSET $offset
        ) t",
        array('user-count-daily', $institutiondata['name'])
    );

    $dayinterval = is_postgres() ? "'1 day'" : '1 day';

    $day = is_postgres() ? "to_date(ctime::text, 'YYYY-MM-DD')" : 'DATE(ctime)';

    $userdata = get_records_sql_array(
        "SELECT ctime, type, \"value\", $day AS date
        FROM {institution_data}
        WHERE type IN (?,?) AND institution = ? AND ctime >= ? AND ctime < (date(?) + INTERVAL $dayinterval)
        ORDER BY type = ? DESC, ctime DESC",
        array('user-count-daily', 'loggedin-users-daily', $institutiondata['name'], $daterange->mindate, $daterange->maxdate, 'user-count-daily')
    );

    $userscreated = get_records_sql_array(
        "SELECT $day as cdate, COUNT(usr) AS users
        FROM {usr_institution}
        WHERE institution = ?
        AND NOT ctime IS NULL AND ctime >= ? AND ctime < (date(?) + INTERVAL $dayinterval)
        GROUP BY cdate",
        array($institutiondata['name'], $daterange->mindate, $daterange->maxdate)
    );

    $data = array();

    if ($userdata) {
        foreach ($userdata as &$r) {
            if ($r->type == 'user-count-daily') {
                $data[$r->date] = array(
                    'date'  => $r->date,
                    'total' => $r->value,
                );
            }
            else if ($r->type == 'loggedin-users-daily' && isset($data[$r->date])) {
                $data[$r->date]['loggedin'] = $r->value;
            }
        }
        if ($userscreated) {
            foreach ($userscreated as &$r) {
                if (isset($data[$r->cdate])) {
                    $data[$r->cdate]['created'] = $r->users;
                }
            }
        }
    }

    $csvfields = array('date', 'loggedin', 'created', 'total');
    $USER->set_download_file(generate_csv($data, $csvfields), 'userstatistics.csv', 'text/csv');
    $result['csv'] = true;

    $smarty = smarty_core();
    $smarty->assign('data', $data);
    $result['tablerows'] = $smarty->fetch('admin/userstats.tpl');

    return $result;
}


function user_institution_graph() {
    // Draw a bar graph showing the number of users in each institution
    require_once(get_config('libroot') . 'institution.php');

    $institutions = Institution::count_members(false, true);
    if (count($institutions) > 1) {
        $dataarray = array();
        foreach ($institutions as &$i) {
            $dataarray[$i->displayname] = $i->members;
        }
        arsort($dataarray);
        // Truncate to avoid overlapping labels
        $dataarray = array_slice($dataarray, 0, 25, true);

        require_once(get_config('libroot') . "pear/Image/Graph.php");

        $Graph =& Image_Graph::factory('graph', array(300, 300));
        $Font =& $Graph->addNew('font', 'Vera');
        $Font->setSize(9);
        $Graph->setFont($Font);

        $Graph->add(
            Image_Graph::vertical(
                Image_Graph::vertical(
                    Image_Graph::factory('title', array(get_string('institutionmembers', 'admin'), 9)),
                    $Plotarea = Image_Graph::factory('plotarea'),
                    5
                ),
                $Date = Image_Graph::factory('title', array(format_date(time(), 'strftimew3cdate'), 7)),
                96
            )
        );

        $Date->setAlignment(IMAGE_GRAPH_ALIGN_RIGHT);
        $DateFont =& $Graph->addNew('font', 'Vera');
        $DateFont->setColor('gray@0.8');
        $Date->setFont($DateFont);

        $Dataset =& Image_Graph::factory('dataset', array($dataarray));
        $Plot =& $Plotarea->addNew('bar', array(&$Dataset));
        $Plot->setLineColor('gray');
        $Plot->setSpacing(2);

        $FillArray =& Image_Graph::factory('Image_Graph_Fill_Array');
        $Plot->setFillStyle($FillArray);
        $FillArray->addColor('blue@0.6');
        $FillArray->addColor('green@0.6');
        $FillArray->addColor('red@0.6');
        $FillArray->addColor('yellow@0.6');
        $FillArray->addColor('orange@0.6');

        $AxisX =& $Plotarea->getAxis(IMAGE_GRAPH_AXIS_X);
        if (count($dataarray) > 4) {
            $AxisX->setFontAngle('vertical');
        }
        $AxisX->setFontSize(8);

        $Graph->done(array('filename' => stats_graph_path('institutions')));
    }
}

function group_statistics($limit, $offset) {
    $data = array();
    $data['tableheadings'] = array(
        array('name' => '#'),
        array('name' => get_string('Group', 'group')),
        array('name' => get_string('Members', 'group'), 'class' => 'center'),
        array('name' => get_string('Views', 'view'), 'class' => 'center'),
        array('name' => get_string('nameplural', 'interaction.forum'), 'class' => 'center'),
        array('name' => get_string('Posts', 'interaction.forum'), 'class' => 'center'),
    );
    $data['table'] = group_stats_table($limit, $offset);
    $data['tabletitle'] = get_string('groupstatstabletitle', 'admin');

    $smarty = smarty_core();
    $smarty->assign('grouptypecounts', get_records_sql_array("
        SELECT grouptype, COUNT(id) AS groups
        FROM {group}
        WHERE deleted = 0
        GROUP BY grouptype
        ORDER BY groups DESC", array()
    ));
    $smarty->assign('jointypecounts', get_records_sql_array("
        SELECT jointype, COUNT(id) AS groups
        FROM {group}
        WHERE deleted = 0
        GROUP BY jointype
        ORDER BY groups DESC", array()
    ));
    $smarty->assign('groupgraph', stats_graph_url('grouptypes'));

    $data['summary'] = $smarty->fetch('admin/groupstatssummary.tpl');

    return $data;
}

function group_stats_table($limit, $offset) {
    global $USER;

    $count = count_records('group', 'deleted', 0);

    $pagination = build_pagination(array(
        'id' => 'stats_pagination',
        'url' => get_config('wwwroot') . 'admin/statistics.php?type=groups',
        'jsonscript' => 'admin/statistics.json.php',
        'datatable' => 'statistics_table',
        'count' => $count,
        'limit' => $limit,
        'offset' => $offset,
        'setlimit' => true,
    ));

    $result = array(
        'count'         => $count,
        'tablerows'     => '',
        'pagination'    => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
    );

    if ($count < 1) {
        return $result;
    }

    $groupdata = get_records_sql_array(
        "SELECT
            g.id, g.name, g.urlid, mc.members, vc.views, fc.forums, pc.posts
        FROM {group} g
            LEFT JOIN (
                SELECT gm.group, COUNT(gm.member) AS members
                FROM {group_member} gm
                GROUP BY gm.group
            ) mc ON g.id = mc.group
            LEFT JOIN (
                SELECT v.group, COUNT(v.id) AS views
                FROM {view} v
                WHERE NOT v.group IS NULL
                GROUP BY v.group
            ) vc ON g.id = vc.group
            LEFT JOIN (
                SELECT ii.group, COUNT(ii.id) AS forums
                FROM {interaction_instance} ii
                WHERE ii.plugin = 'forum' AND ii.deleted = 0
                GROUP BY ii.group
            ) fc ON g.id = fc.group
            LEFT JOIN (
                SELECT ii.group, COUNT(ifp.id) AS posts
                FROM {interaction_instance} ii
                    INNER JOIN {interaction_forum_topic} ift ON ii.id = ift.forum
                    INNER JOIN {interaction_forum_post} ifp ON ift.id = ifp.topic
                WHERE ii.deleted = 0 AND ift.deleted = 0 AND ifp.deleted = 0
                GROUP BY ii.group
            ) pc ON g.id = pc.group
        WHERE
            g.deleted = 0
        ORDER BY
            mc.members IS NULL, mc.members DESC, g.name, g.id",
        array(),
        $offset,
        $limit
    );

    $csvfields = array('id', 'name', 'members', 'views', 'forums', 'posts');
    $USER->set_download_file(generate_csv($groupdata, $csvfields), 'groupstatistics.csv', 'text/csv');
    $result['csv'] = true;

    require_once('group.php');

    $smarty = smarty_core();
    $smarty->assign('data', $groupdata);
    $smarty->assign('offset', $offset);
    $result['tablerows'] = $smarty->fetch('admin/groupstats.tpl');

    return $result;
}

function group_type_graph() {
    $grouptypes = get_records_sql_array("
        SELECT grouptype, jointype, COUNT(id) AS groups
        FROM {group}
        WHERE deleted = 0
        GROUP BY grouptype, jointype
        ORDER BY groups DESC", array()
    );

    if (count($grouptypes) > 1) {
        $dataarray = array();
        foreach ($grouptypes as &$t) {
            $strtype = get_string('name', 'grouptype.' . $t->grouptype);
            $strtype .= ' (' . get_string('membershiptype.abbrev.' . $t->jointype, 'group') . ')';
            $dataarray[$strtype] = $t->groups;
        }
        ksort($dataarray);
        arsort($dataarray);

        require_once(get_config('libroot') . "pear/Image/Graph.php");

        $Graph =& Image_Graph::factory('graph', array(300, 200));
        $Font =& $Graph->addNew('font', 'Vera');
        $Font->setSize(9);
        $Graph->setFont($Font);

        $Graph->add(
            Image_Graph::vertical(
                Image_Graph::horizontal(
                    $Plotarea = Image_Graph::factory('plotarea'),
                    $Legend = Image_Graph::factory('legend'),
                    60
                ),
                $Date = Image_Graph::factory('title', array(format_date(time(), 'strftimew3cdate'), 7)),
                96
            )
        );

        $Date->setAlignment(IMAGE_GRAPH_ALIGN_RIGHT);
        $DateFont =& $Graph->addNew('font', 'Vera');
        $DateFont->setColor('gray@0.8');
        $Date->setFont($DateFont);

        $Legend->setPlotArea($Plotarea);
        $Legend->setFontSize(6);
        $Plotarea->hideAxis();

        $Dataset =& Image_Graph::factory('dataset', array($dataarray));
        $Plot =& $Plotarea->addNew('pie', $Dataset);

        $Plot->setLineColor('black');

        $FillArray =& Image_Graph::factory('Image_Graph_Fill_Array');
        $Plot->setFillStyle($FillArray);
        $FillArray->addColor('blue@0.6');
        $FillArray->addColor('green@0.6');
        $FillArray->addColor('red@0.6');
        $FillArray->addColor('yellow@0.6');
        $FillArray->addColor('orange@0.6');
        $FillArray->addColor('black@0.6');

        $Marker =& $Plot->addNew('Image_Graph_Marker_Value', IMAGE_GRAPH_VALUE_Y);
        $Marker->setBorderColor('white');
        $Marker->setFontSize(7);

        $PointingMarker =& $Plot->addNew('Image_Graph_Marker_Pointing_Angular', array(20, &$Marker));
        $Plot->setMarker($PointingMarker);

        $Graph->done(array('filename' => stats_graph_path('grouptypes')));
    }
}

function view_statistics($limit, $offset) {
    $data = array();
    $data['tableheadings'] = array(
        array('name' => '#'),
        array('name' => get_string('view')),
        array('name' => get_string('Owner', 'view')),
        array('name' => get_string('Visits'), 'class' => 'center'),
        array('name' => get_string('Comments', 'artefact.comment'), 'class' => 'center'),
    );
    $data['table'] = view_stats_table($limit, $offset);
    $data['tabletitle'] = get_string('viewstatstabletitle', 'admin');

    $smarty = smarty_core();
    $maxblocktypes = 5;
    $smarty->assign('blocktypecounts', get_records_sql_array("
        SELECT
            b.blocktype,
            CASE WHEN bi.artefactplugin IS NULL THEN b.blocktype
                ELSE bi.artefactplugin || '/' || b.blocktype END AS langsection,
            COUNT(b.id) AS blocks
        FROM {block_instance} b
        JOIN {blocktype_installed} bi ON (b.blocktype = bi.name)
        JOIN {view} v ON (b.view = v.id AND v.type = 'portfolio')
        GROUP BY b.blocktype, langsection
        ORDER BY blocks DESC",
        array(), 0, $maxblocktypes
    ));
    $smarty->assign('viewtypes', stats_graph_url('viewtypes'));
    $smarty->assign('viewcount', $data['table']['count']);
    $data['summary'] = $smarty->fetch('admin/viewstatssummary.tpl');

    return $data;
}

function view_stats_table($limit, $offset) {
    global $USER;

    $count = count_records_select('view', '(owner != 0 OR owner IS NULL) AND type != ?', array('dashboard'));

    $pagination = build_pagination(array(
        'id' => 'stats_pagination',
        'url' => get_config('wwwroot') . 'admin/statistics.php?type=views',
        'jsonscript' => 'admin/statistics.json.php',
        'datatable' => 'statistics_table',
        'count' => $count,
        'limit' => $limit,
        'offset' => $offset,
        'setlimit' => true,
    ));

    $result = array(
        'count'         => $count,
        'tablerows'     => '',
        'pagination'    => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
    );

    if ($count < 1) {
        return $result;
    }

    $viewdata = get_records_sql_assoc(
        "SELECT
            v.id, v.title, v.owner, v.group, v.institution, v.visits, v.type, v.ownerformat, v.urlid
        FROM {view} v
        WHERE (v.owner != 0 OR \"owner\" IS NULL) AND v.type != ?
        ORDER BY v.visits DESC, v.title, v.id",
        array('dashboard'),
        $offset,
        $limit
    );

    require_once('view.php');
    require_once('group.php');
    View::get_extra_view_info($viewdata, false, false);

    safe_require('artefact', 'comment');
    $comments = ArtefactTypeComment::count_comments(array_keys($viewdata));

    foreach ($viewdata as &$v) {
        $v = (object) $v;
        if ($v->owner) {
            $v->ownername = display_name($v->user);
            $v->ownerurl  = profile_url($v->user);
        }
        else {
            $v->ownername = $v->sharedby;
            if ($v->group) {
                $v->ownerurl = group_homepage_url($v->groupdata);
            }
            else if ($v->institution && $v->institution != 'mahara') {
                $v->ownerurl = get_config('wwwroot') . 'institution/index.php?institution=' . $v->institution;
            }
        }
        $v->comments = isset($comments[$v->id]) ? (int) $comments[$v->id]->comments : 0;
    }

    $csvfields = array('title', 'displaytitle', 'fullurl', 'owner', 'group', 'institution', 'ownername', 'ownerurl', 'visits', 'type', 'comments');
    $USER->set_download_file(generate_csv($viewdata, $csvfields), 'viewstatistics.csv', 'text/csv');
    $result['csv'] = true;

    $smarty = smarty_core();
    $smarty->assign('data', $viewdata);
    $smarty->assign('offset', $offset);
    $result['tablerows'] = $smarty->fetch('admin/viewstats.tpl');

    return $result;
}

function view_type_graph() {
    // Draw a pie graph of views broken down by view type.
    $viewtypes = get_records_sql_array('
        SELECT type, COUNT(id) AS views
        FROM {view} WHERE type != ?
        GROUP BY type',
        array('dashboard')
    );

    if (count($viewtypes) > 1) {
        $dataarray = array();
        foreach ($viewtypes as &$t) {
            $dataarray[get_string($t->type, 'view')] = $t->views;
        }
        arsort($dataarray);

        require_once(get_config('libroot') . "pear/Image/Graph.php");

        $Graph =& Image_Graph::factory('graph', array(300, 200));
        $Font =& $Graph->addNew('font', 'Vera');
        $Font->setSize(9);
        $Graph->setFont($Font);

        $Graph->add(
            Image_Graph::vertical(
                Image_Graph::vertical(
                    Image_Graph::factory('title', array(get_string('viewsbytype', 'admin'), 9)),
                    $Plotarea = Image_Graph::factory('plotarea'),
                    5
                ),
                $Date = Image_Graph::factory('title', array(format_date(time(), 'strftimew3cdate'), 7)),
                96
            )
        );

        $Date->setAlignment(IMAGE_GRAPH_ALIGN_RIGHT);
        $DateFont =& $Graph->addNew('font', 'Vera');
        $DateFont->setColor('gray@0.8');
        $Date->setFont($DateFont);

        $Plotarea->hideAxis();
        $Dataset =& Image_Graph::factory('dataset', array($dataarray));
        $Plot =& $Plotarea->addNew('pie', array(&$Dataset));

        $Plot->setLineColor('black');

        $FillArray =& Image_Graph::factory('Image_Graph_Fill_Array');
        $Plot->setFillStyle($FillArray);
        $FillArray->addColor('blue@0.6');
        $FillArray->addColor('green@0.6');
        $FillArray->addColor('red@0.6');
        $FillArray->addColor('yellow@0.6');
        $FillArray->addColor('orange@0.6');

        $Marker =& $Plot->addNew('Image_Graph_Marker_Value', IMAGE_GRAPH_VALUE_X);
        $Marker->setBorderColor('white');
        $Marker->setFontSize(8);

        $PointingMarker =& $Plot->addNew('Image_Graph_Marker_Pointing_Angular', array(20, &$Marker));
        $Plot->setMarker($PointingMarker);

        $Graph->done(array('filename' => stats_graph_path('viewtypes')));
    }
}

function institution_view_statistics($limit, $offset, &$institutiondata) {
    $data = array();
    $data['tableheadings'] = array(
        array('name' => '#'),
        array('name' => get_string('view')),
        array('name' => get_string('Owner', 'view')),
        array('name' => get_string('Visits'), 'class' => 'center'),
        array('name' => get_string('Comments', 'artefact.comment'), 'class' => 'center'),
    );
    $data['table'] = institution_view_stats_table($limit, $offset, $institutiondata);
    $data['tabletitle'] = get_string('viewstatstabletitle', 'admin');

    $smarty = smarty_core();
    $maxblocktypes = 5;
    if ($institutiondata['views']) {
        $smarty->assign('blocktypecounts', get_records_sql_array("
            SELECT
                b.blocktype,
                CASE WHEN bi.artefactplugin IS NULL THEN b.blocktype
                    ELSE bi.artefactplugin || '/' || b.blocktype END AS langsection,
                COUNT(b.id) AS blocks
            FROM {block_instance} b
            JOIN {blocktype_installed} bi ON (b.blocktype = bi.name)
            JOIN {view} v ON (b.view = v.id AND v.type = 'portfolio')
            WHERE v.id IN (" . join(',', array_fill(0, $institutiondata['views'], '?')) . ")
            GROUP BY b.blocktype, langsection
            ORDER BY blocks DESC",
            $institutiondata['viewids'], 0, $maxblocktypes
        ));
    }
    $smarty->assign('viewtypes', stats_graph_url($institutiondata['name'] . '_viewtypes'));
    $smarty->assign('viewcount', $data['table']['count']);
    $data['summary'] = $smarty->fetch('admin/viewstatssummary.tpl');

    return $data;
}

function institution_view_stats_table($limit, $offset, &$institutiondata) {
    global $USER;

    if ($institutiondata['views'] != 0) {
        $count = count_records_select('view', 'id IN (' . join(',', array_fill(0, $institutiondata['views'], '?')) . ') AND type != ?',
                                        array_merge($institutiondata['viewids'], array('dashboard')));
    }
    else {
        $count = 0;
    }

    $pagination = build_pagination(array(
        'id' => 'stats_pagination',
        'url' => get_config('wwwroot') . 'admin/users/statistics.php?institution=' . $institutiondata['name'] . '&type=views',
        'jsonscript' => 'admin/users/statistics.json.php',
        'datatable' => 'statistics_table',
        'count' => $count,
        'limit' => $limit,
        'offset' => $offset,
        'extradata' => array('institution' => $institutiondata['name']),
        'setlimit' => true,
    ));

    $result = array(
        'count'         => $count,
        'tablerows'     => '',
        'pagination'    => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
    );

    if ($count < 1) {
        return $result;
    }

    $viewdata = get_records_sql_assoc(
        "SELECT
            v.id, v.title, v.owner, v.group, v.institution, v.visits, v.type, v.ownerformat, v.urlid
        FROM {view} v
        WHERE v.id IN (" . join(',', array_fill(0, $institutiondata['views'], '?')) . ") AND v.type != ?
        ORDER BY v.visits DESC, v.title, v.id",
        array_merge($institutiondata['viewids'], array('dashboard')),
        $offset,
        $limit
    );

    require_once('view.php');
    require_once('group.php');
    View::get_extra_view_info($viewdata, false, false);

    safe_require('artefact', 'comment');
    $comments = ArtefactTypeComment::count_comments(array_keys($viewdata));

    foreach ($viewdata as &$v) {
        $v = (object) $v;
        if ($v->owner) {
            $v->ownername = display_name($v->user);
            $v->ownerurl  = profile_url($v->user);
        }
        else {
            $v->ownername = $v->sharedby;
            if ($v->group) {
                $v->ownerurl = group_homepage_url($v->groupdata);
            }
            else if ($v->institution && $v->institution != 'mahara') {
                $v->ownerurl = get_config('wwwroot') . 'institution/index.php?institution=' . $v->institution;
            }
        }
        $v->comments = isset($comments[$v->id]) ? (int) $comments[$v->id]->comments : 0;
    }

    $csvfields = array('title', 'displaytitle', 'fullurl', 'owner', 'group', 'institution', 'ownername', 'ownerurl', 'visits', 'type', 'comments');
    $USER->set_download_file(generate_csv($viewdata, $csvfields), 'viewstatistics.csv', 'text/csv');
    $result['csv'] = true;

    $smarty = smarty_core();
    $smarty->assign('data', $viewdata);
    $smarty->assign('offset', $offset);
    $result['tablerows'] = $smarty->fetch('admin/viewstats.tpl');

    return $result;
}

function institution_view_type_graph(&$institutiondata) {
    if ($institutiondata['views'] == 0) {
        return;
    }
    $institution = $institutiondata['name'];
    $values = array();
    // Draw a pie graph of views broken down by view type.
    $values[] = 'dashboard';
    if ($institution == 'mahara') {
            $where = 'institution IS NULL';
    }
    else {
        $where = 'institution = ?';
        $values[] = $institution;
    }
    $values[] = $institution;
    $viewtypes = get_records_sql_array('
        SELECT type, COUNT(id) AS views
        FROM {view} WHERE type != ?
        AND id IN (
            SELECT id FROM {view} WHERE owner IS NOT NULL AND owner IN (
                SELECT u.id FROM {usr} u LEFT JOIN {usr_institution} ui ON u.id = ui.usr
                WHERE ' . $where . ' AND u.id != 0 AND deleted = 0
            ) UNION SELECT id FROM {view} WHERE institution IS NOT NULL AND institution = ?
        ) GROUP BY type', $values
    );

    if (count($viewtypes) > 1) {
        $dataarray = array();
        foreach ($viewtypes as &$t) {
            $dataarray[get_string($t->type, 'view')] = $t->views;
        }
        arsort($dataarray);

        require_once(get_config('libroot') . "pear/Image/Graph.php");

        $Graph =& Image_Graph::factory('graph', array(300, 200));
        $Font =& $Graph->addNew('font', 'Vera');
        $Font->setSize(9);
        $Graph->setFont($Font);

        $Graph->add(
            Image_Graph::vertical(
                Image_Graph::vertical(
                    Image_Graph::factory('title', array(get_string('viewsbytype', 'admin'), 9)),
                    $Plotarea = Image_Graph::factory('plotarea'),
                    5
                ),
                $Date = Image_Graph::factory('title', array(format_date(time(), 'strftimew3cdate'), 7)),
                96
            )
        );

        $Date->setAlignment(IMAGE_GRAPH_ALIGN_RIGHT);
        $DateFont =& $Graph->addNew('font', 'Vera');
        $DateFont->setColor('gray@0.8');
        $Date->setFont($DateFont);

        $Plotarea->hideAxis();
        $Dataset =& Image_Graph::factory('dataset', array($dataarray));
        $Plot =& $Plotarea->addNew('pie', array(&$Dataset));

        $Plot->setLineColor('black');

        $FillArray =& Image_Graph::factory('Image_Graph_Fill_Array');
        $Plot->setFillStyle($FillArray);
        $FillArray->addColor('blue@0.6');
        $FillArray->addColor('green@0.6');
        $FillArray->addColor('red@0.6');
        $FillArray->addColor('yellow@0.6');
        $FillArray->addColor('orange@0.6');

        $Marker =& $Plot->addNew('Image_Graph_Marker_Value', IMAGE_GRAPH_VALUE_X);
        $Marker->setBorderColor('white');
        $Marker->setFontSize(8);

        $PointingMarker =& $Plot->addNew('Image_Graph_Marker_Pointing_Angular', array(20, &$Marker));
        $Plot->setMarker($PointingMarker);

        $Graph->done(array('filename' => stats_graph_path($institutiondata['name'] . '_viewtypes')));
    }
}

function content_statistics($limit, $offset) {
    $data = array();
    $data['tableheadings'] = array(
        array('name' => '#'),
        array('name' => get_string('name')),
        array('name' => get_string('modified')),
        array('name' => get_string('Total'), 'class' => 'center'),
    );
    $data['table'] = content_stats_table($limit, $offset);
    $data['tabletitle'] = get_string('contentstatstabletitle', 'admin');

    $data['summary'] = $data['table']['count'] == 0 ? get_string('nostats', 'admin') : null;

    return $data;
}

function content_stats_table($limit, $offset) {
    global $USER;

    $dates = get_records_array('site_registration', '', '', 'time DESC', '*', 0, 2);

    if ($dates) {
        $count = count_records_select('site_registration_data', 'registration_id = ? AND value ' . (is_postgres() ? '~ E' : 'REGEXP ') . '\'^[0-9]+$\' AND field NOT LIKE \'%version\'', array($dates[0]->id));
    }
    else {
        $count = 0;
    }

    // Show all the stats, is a smallish number
    $limit = $count;
    $offset = 0;

    $pagination = build_pagination(array(
        'id' => 'stats_pagination',
        'url' => get_config('wwwroot') . 'admin/statistics.php?type=content',
        'jsonscript' => 'admin/statistics.json.php',
        'datatable' => 'statistics_table',
        'count' => $count,
        'limit' => $limit,
        'offset' => $offset,
    ));

    $result = array(
        'count'         => $count,
        'tablerows'     => '',
        'pagination'    => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
    );

    if ($count < 1) {
        return $result;
    }

    $contentdata = get_records_sql_assoc(
        "SELECT
            field, value
        FROM {site_registration_data}
        WHERE registration_id = ?
        AND value " . (is_postgres() ? "~ E" : "REGEXP ") . "'^[0-9]+$'
        AND field NOT LIKE '%version'
        ORDER BY field",
        array($dates[0]->id),
        $offset,
        $limit
    );

    if (count($dates) > 1) {
        $lastweeks = get_records_sql_assoc(
            "SELECT
                field, value
            FROM {site_registration_data}
            WHERE registration_id = ?
            ORDER BY field",
            array($dates[1]->id)
        );
        foreach ($contentdata as &$d) {
            $d->modified = $d->value - (isset($lastweeks[$d->field]->value) ? $lastweeks[$d->field]->value : 0);
        }
    }
    else {
        foreach ($contentdata as &$d) {
            $d->modified = $d->value;
        }
    }

    $csvfields = array('field', 'modified', 'value');
    $USER->set_download_file(generate_csv($contentdata, $csvfields), 'contentstatistics.csv', 'text/csv');
    $result['csv'] = true;

    $smarty = smarty_core();
    $smarty->assign('data', $contentdata);
    $smarty->assign('offset', $offset);
    $result['tablerows'] = $smarty->fetch('admin/contentstats.tpl');

    return $result;
}

function institution_content_statistics($limit, $offset, &$institutiondata) {
    $data = array();
    $data['tableheadings'] = array(
        array('name' => '#'),
        array('name' => get_string('name')),
        array('name' => get_string('modified')),
        array('name' => get_string('Total'), 'class' => 'center'),
    );
    $data['table'] = institution_content_stats_table($limit, $offset, $institutiondata);
    $data['tabletitle'] = get_string('contentstatstabletitle', 'admin');

    $data['summary'] = $data['table']['count'] == 0 ? get_string('nostats', 'admin') : null;

    return $data;
}

function institution_content_stats_table($limit, $offset, &$institutiondata) {
    global $USER;

    $dates = get_records_array('institution_registration', 'institution', $institutiondata['name'], 'time DESC', '*', 0, 2);

    if ($dates) {
        $count = count_records('institution_registration_data', 'registration_id', $dates[0]->id);
    }
    else {
        $count = 0;
    }

    if ($count > 1) {
        // remove one as it is userloggedin, which is a psuedostat
        $count --;
    }

    // Show all the stats, is a smallish number
    $limit = $count;
    $offset = 0;

    $pagination = build_pagination(array(
        'id' => 'stats_pagination',
        'url' => get_config('wwwroot') . 'admin/users/statistics.php?institution=' . $institutiondata['name'] . '&type=content',
        'jsonscript' => 'admin/users/statistics.json.php',
        'datatable' => 'statistics_table',
        'count' => $count,
        'limit' => $limit,
        'offset' => $offset,
        'extradata' => array('institution' => $institutiondata['name']),
    ));

    $result = array(
        'count'         => $count,
        'tablerows'     => '',
        'pagination'    => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
    );

    if ($count < 1) {
        return $result;
    }

    $contentdata = get_records_sql_assoc(
        "SELECT
            field, value
        FROM {institution_registration_data}
        WHERE registration_id = ? AND field != ?
        ORDER BY field",
        array($dates[0]->id, 'usersloggedin'),
        $offset,
        $limit
    );

    if (count($dates) > 1) {
        $lastweeks = get_records_sql_assoc(
            "SELECT
                field, value
            FROM {institution_registration_data}
            WHERE registration_id = ?
            ORDER BY field",
            array($dates[1]->id)
        );
        foreach ($contentdata as &$d) {
            $d->modified = $d->value - (isset($lastweeks[$d->field]->value) ? $lastweeks[$d->field]->value : 0);
        }
    }
    else {
        foreach ($contentdata as &$d) {
            $d->modified = $d->value;
        }
    }
    if (isset($contentdata['count_members'])) {
        $contentdata['count_members']->modified = get_field('institution_registration_data', 'value', 'registration_id', $dates[0]->id, 'field', 'usersloggedin');
    }

    $csvfields = array('field', 'modified', 'value');
    $USER->set_download_file(generate_csv($contentdata, $csvfields), 'contentstatistics.csv', 'text/csv');
    $result['csv'] = true;

    $smarty = smarty_core();
    $smarty->assign('data', $contentdata);
    $smarty->assign('offset', $offset);
    $smarty->assign('institution', $institutiondata['name']);
    $result['tablerows'] = $smarty->fetch('admin/contentstats.tpl');

    return $result;
}

function historical_statistics($limit, $offset, $field) {
    $data = array();
    $data['tableheadings'] = array(
        array('name' => get_string('date')),
        array('name' => get_string('modified'), 'class' => 'center'),
        array('name' => get_string('Total'), 'class' => 'center'),
    );
    $data['table'] = historical_stats_table($limit, $offset, $field);
    $data['tabletitle'] = get_string('historicalstatstabletitle', 'admin', get_string($field, 'statistics'));

    $data['summary'] = $data['table']['count'] == 0 ? get_string('nostats', 'admin') : null;

    return $data;
}

function historical_stats_table($limit, $offset, $field) {
    global $USER;

    $count = count_records_sql(
        "SELECT COUNT(*)
        FROM {site_registration} sr
        JOIN {site_registration_data} srd
            ON (srd.registration_id = sr.id)
        WHERE srd.field = ?",
        array($field)
    );

    $pagination = build_pagination(array(
        'id' => 'stats_pagination',
        'url' => get_config('wwwroot') . 'admin/statistics.php?type=historical',
        'jsonscript' => 'admin/statistics.json.php',
        'datatable' => 'statistics_table',
        'count' => $count,
        'limit' => $limit,
        'offset' => $offset,
        'extradata' => array('field' => $field),
        'setlimit' => true,
    ));

    $result = array(
        'count'         => $count,
        'tablerows'     => '',
        'pagination'    => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
    );

    if ($count < 1) {
        return $result;
    }

    $registrationdata = get_records_sql_array(
        "SELECT
            sr.time, srd.field, srd.value
        FROM {site_registration} sr
        JOIN {site_registration_data} srd
            ON (srd.registration_id = sr.id)
        WHERE srd.field = ?
        ORDER BY sr.time DESC",
        array($field),
        $offset,
        $limit
    );

    if ($registrationdata) {
        $registrationdata[count($registrationdata) - 1]->modified = $registrationdata[count($registrationdata) - 1]->value;
    }
    for ($i = count($registrationdata) - 2; $i >= 0; -- $i) {
        $registrationdata[$i]->modified = $registrationdata[$i]->value - $registrationdata[$i + 1]->value;
    }

    $csvfields = array('time', 'field', 'modified', 'value');
    $USER->set_download_file(generate_csv($registrationdata, $csvfields), 'registrationstatistics.csv', 'text/csv');
    $result['csv'] = true;

    $smarty = smarty_core();
    $smarty->assign('data', $registrationdata);
    $smarty->assign('offset', $offset);
    $result['tablerows'] = $smarty->fetch('admin/historicalstats.tpl');

    return $result;
}

function institution_historical_statistics($limit, $offset, $field, &$institutiondata) {
    $data = array();
    $data['tableheadings'] = array(
        array('name' => get_string('date')),
        array('name' => get_string('modified'), 'class' => 'center'),
        array('name' => get_string('Total'), 'class' => 'center'),
    );
    $data['table'] = institution_historical_stats_table($limit, $offset, $field, $institutiondata);
    $data['tabletitle'] = get_string('historicalstatstabletitle', 'admin', get_string($field, 'statistics'));

    $data['summary'] = $data['table']['count'] == 0 ? get_string('nostats', 'admin') : null;

    return $data;
}

function institution_historical_stats_table($limit, $offset, $field, &$institutiondata) {
    global $USER;

    $count = count_records_sql(
        "SELECT COUNT(*)
        FROM {institution_registration} ir
        JOIN {institution_registration_data} ird
            ON (ird.registration_id = ir.id)
        WHERE ir.institution = ? AND ird.field = ?",
        array($institutiondata['name'], $field)
    );

    $pagination = build_pagination(array(
        'id' => 'stats_pagination',
        'url' => get_config('wwwroot') . 'admin/users/statistics.php?institution=' . $institutiondata['name'] . '&type=historical',
        'jsonscript' => 'admin/users/statistics.json.php',
        'datatable' => 'statistics_table',
        'count' => $count,
        'limit' => $limit,
        'offset' => $offset,
        'extradata' => array('institution' => $institutiondata['name'], 'field' => $field),
        'setlimit' => true,
    ));

    $result = array(
        'count'         => $count,
        'tablerows'     => '',
        'pagination'    => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
    );

    if ($count < 1) {
        return $result;
    }

    $registrationdata = get_records_sql_array(
        "SELECT
            ir.id, ir.time, ird.field, ird.value
        FROM {institution_registration} ir
        JOIN {institution_registration_data} ird
            ON (ird.registration_id = ir.id)
        WHERE ir.institution = ? AND ird.field = ?
        ORDER BY ir.time DESC",
        array($institutiondata['name'], $field),
        $offset,
        $limit
    );

    if ($field == 'count_members') {
        foreach ($registrationdata as &$d) {
            $d->modified = get_field('institution_registration_data', 'value', 'registration_id', $d->id, 'field', 'usersloggedin');
        }
    }
    else {
        if ($registrationdata) {
            $registrationdata[count($registrationdata) - 1]->modified = $registrationdata[count($registrationdata) - 1]->value;
        }
        for ($i = count($registrationdata) - 2; $i >= 0; -- $i) {
            $registrationdata[$i]->modified = $registrationdata[$i]->value - $registrationdata[$i + 1]->value;
        }
    }

    $csvfields = array('time', 'field', 'modified', 'value');
    $USER->set_download_file(generate_csv($registrationdata, $csvfields), 'registrationstatistics.csv', 'text/csv');
    $result['csv'] = true;

    $smarty = smarty_core();
    $smarty->assign('data', $registrationdata);
    $smarty->assign('offset', $offset);
    $result['tablerows'] = $smarty->fetch('admin/historicalstats.tpl');

    return $result;
}

function institution_comparison_statistics($limit, $offset, $sort, $sortdesc) {
    $data = array();
    $data['tableheadings'] = array(
        array(
            'name' => get_string('institution'),
            'class' => 'search-results-sort-column' . ($sort == 'displayname' ? ' ' . ($sortdesc ? 'desc' : 'asc') : ''),
            'link' => get_config('wwwroot') . 'admin/statistics.php?type=institutions&sort=displayname&sortdesc=' . ($sort == 'displayname' ? !$sortdesc : false) . '&limit=' . $limit . '&offset=' . $offset
        ),
        array(
            'name' => get_string('members'),
            'class' => 'search-results-sort-column' . ($sort == 'count_members' ? ' ' . ($sortdesc ? 'desc' : 'asc') : ''),
            'link' => get_config('wwwroot') . 'admin/statistics.php?type=institutions&sort=count_members&sortdesc=' . ($sort == 'count_members' ? !$sortdesc : true) . '&limit=' . $limit . '&offset=' . $offset
        ),
        array(
            'name' => get_string('views'),
            'class' => 'search-results-sort-column' . ($sort == 'count_views' ? ' ' . ($sortdesc ? 'desc' : 'asc') : ''),
            'link' => get_config('wwwroot') . 'admin/statistics.php?type=institutions&sort=count_views&sortdesc=' . ($sort == 'count_views' ? !$sortdesc : true) . '&limit=' . $limit . '&offset=' . $offset
        ),
        array(
            'name' => get_string('blocks'),
            'class' => 'search-results-sort-column' . ($sort == 'count_blocks' ? ' ' . ($sortdesc ? 'desc' : 'asc') : ''),
            'link' => get_config('wwwroot') . 'admin/statistics.php?type=institutions&sort=count_blocks&sortdesc=' . ($sort == 'count_blocks' ? !$sortdesc : true) . '&limit=' . $limit . '&offset=' . $offset
        ),
        array(
            'name' => get_string('artefacts'),
            'class' => 'search-results-sort-column' . ($sort == 'count_artefacts' ? ' ' . ($sortdesc ? 'desc' : 'asc') : ''),
            'link' => get_config('wwwroot') . 'admin/statistics.php?type=institutions&sort=count_artefacts&sortdesc=' . ($sort == 'count_artefacts' ? !$sortdesc : true) . '&limit=' . $limit . '&offset=' . $offset
        ),
        array(
            'name' => get_string('posts'),
            'class' => 'search-results-sort-column' . ($sort == 'count_interaction_forum_post' ? ' ' . ($sortdesc ? 'desc' : 'asc') : ''),
            'link' => get_config('wwwroot') . 'admin/statistics.php?type=institutions&sort=count_interaction_forum_post&sortdesc=' . ($sort == 'count_interaction_forum_post' ? !$sortdesc : true) . '&limit=' . $limit . '&offset=' . $offset
        ),
    );
    $data['table'] = institution_comparison_stats_table($limit, $offset, $sort, $sortdesc);
    $data['tabletitle'] = get_string('institutionstatstabletitle', 'admin');

    $data['summary'] = $data['table']['count'] == 0 ? get_string('nostats', 'admin') : null;

    return $data;
}

function institution_comparison_stats_table($limit, $offset, $sort, $sortdesc) {
    global $USER;

    $count = count_records_sql(
            "SELECT COUNT(DISTINCT institution)
            FROM {institution_registration}"
    );

    $pagination = build_pagination(array(
        'id' => 'stats_pagination',
        'url' => get_config('wwwroot') . 'admin/statistics.php?type=institutions',
        'jsonscript' => 'admin/statistics.json.php',
        'datatable' => 'statistics_table',
        'count' => $count,
        'limit' => $limit,
        'offset' => $offset,
        'extradata' => array('sort' => $sort, 'sortdesc' => $sortdesc),
        'setlimit' => true,
    ));

    $result = array(
        'count'         => $count,
        'tablerows'     => '',
        'pagination'    => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
    );

    if ($count < 1) {
        return $result;
    }

    if ($sort == 'displayname') {
        if (is_postgres()) {
            $institutions = get_records_sql_array(
                    "SELECT tmp.id, tmp.institution AS name, i.displayname
                    FROM (SELECT DISTINCT ON (institution)
                        id, institution
                        FROM {institution_registration}
                        ORDER BY institution, time DESC
                    ) tmp
                    JOIN {institution} i ON (tmp.institution = i.name)
                    ORDER BY i.displayname " . ($sortdesc ? 'DESC' : 'ASC') . "
                    LIMIT ? OFFSET ?",
                    array($limit, $offset)
            );
        }
        else {
            $institutions = get_records_sql_array(
                    "SELECT tmp.id, tmp.institution AS name, i.displayname
                    FROM (SELECT ir.id, ir.institution
                        FROM {institution_registration} ir
                        JOIN (SELECT institution, MAX(time) AS time
                            FROM {institution_registration}
                            GROUP BY institution
                        ) inn ON (inn.institution = ir.institution AND inn.time = ir.time)
                    ) tmp
                    JOIN {institution} i ON (tmp.institution = i.name)
                    ORDER BY i.displayname " . ($sortdesc ? 'DESC' : 'ASC') . "
                    LIMIT ? OFFSET ?",
                    array($limit, $offset)
            );
        }
    }
    else {
        if (is_postgres()) {
            $institutions = get_records_sql_array(
                    "SELECT tmp.id, tmp.institution AS name, i.displayname
                    FROM (SELECT DISTINCT ON (ir.institution)
                        ir.id, ir.institution, ird.value
                        FROM {institution_registration} ir
                        JOIN {institution_registration_data} ird ON (ir.id = ird.registration_id)
                        WHERE ird.field = ?
                        ORDER BY ir.institution, ir.time DESC
                    ) tmp
                    JOIN {institution} i ON (tmp.institution = i.name)
                    ORDER BY tmp.value::int " . ($sortdesc ? 'DESC' : 'ASC') . "
                    LIMIT ? OFFSET ?",
                    array($sort, $limit, $offset)
            );
        }
        else {
            $institutions = get_records_sql_array(
                    "SELECT tmp.id, tmp.institution AS name, i.displayname
                    FROM (SELECT ir.id, ir.institution, ird.value
                        FROM {institution_registration} ir
                        JOIN (SELECT institution, MAX(time) AS time
                            FROM {institution_registration}
                            GROUP BY institution
                        ) inn ON (inn.institution = ir.institution AND inn.time = ir.time)
                        JOIN {institution_registration_data} ird ON (ir.id = ird.registration_id)
                        WHERE ird.field = ?
                    ) tmp
                    JOIN {institution} i ON (tmp.institution = i.name)
                    ORDER BY (tmp.value + 0) " . ($sortdesc ? 'DESC' : 'ASC') . "
                    LIMIT ? OFFSET ?",
                    array($sort, $limit, $offset)
            );
        }
    }

    $registrationdata = array();
    foreach ($institutions as $i) {
        $d = new StdClass;
        $d->name = $i->name;
        $d->displayname = $i->displayname;
        $current = get_records_select_array('institution_registration_data',
                'registration_id = ? AND field IN (?, ?, ?, ? ,?)',
                array($i->id, 'count_members', 'count_views', 'count_blocks', 'count_artefacts', 'count_interaction_forum_post'),
                '', 'field, value'
        );
        foreach ($current as $data) {
            $d->{$data->field} = $data->value;
        }
        $registrationdata[] = $d;
    }

    $csvfields = array('name', 'count_members', 'count_views', 'count_blocks', 'count_artefacts', 'count_interaction_forum_post');
    $USER->set_download_file(generate_csv($registrationdata, $csvfields), 'institutionstatistics.csv', 'text/csv');
    $result['csv'] = true;

    $smarty = smarty_core();
    $smarty->assign('data', $registrationdata);
    $smarty->assign('offset', $offset);
    $result['tablerows'] = $smarty->fetch('admin/comparisonstats.tpl');

    return $result;
}


function graph_site_data_weekly() {

    $lastyear = db_format_timestamp(time() - 60*60*12*365);
    $values = array($lastyear, 'view-count', 'user-count', 'group-count');
    $weekly = get_records_sql_array('
        SELECT ctime, type, "value", ' . db_format_tsfield('ctime', 'ts') . '
        FROM {site_data}
        WHERE ctime >= ? AND type IN (?,?,?)
        ORDER BY ctime, type', $values);

    if (!count($weekly) > 1) {
        return;
    }

    $dataarray = array();
    foreach ($weekly as &$r) {
        $dataarray[$r->type][strftime("%d %b", $r->ts)] = $r->value;
    }
    foreach ($dataarray as &$t) {
        // The graph will look nasty until we have 2 points to plot.
        if (count($t) < 2) {
            return;
        }
    }

    require_once(get_config('libroot') . "pear/Image/Graph.php");

    $Graph =& Image_Graph::factory('graph', array(350, 200));
    $Font =& $Graph->addNew('font', 'Vera');
    $Font->setSize(9);
    $Graph->setFont($Font);

    $Graph->add(
        Image_Graph::vertical(
            Image_Graph::vertical(
                $Plotarea = Image_Graph::factory('plotarea'),
                $Legend = Image_Graph::factory('legend'),
                88
            ),
            $Date = Image_Graph::factory('title', array(format_date(time(), 'strftimew3cdate'), 7)),
            96
        )
    );

    $Date->setAlignment(IMAGE_GRAPH_ALIGN_RIGHT);
    $DateFont =& $Graph->addNew('font', 'Vera');
    $DateFont->setColor('gray@0.8');
    $Date->setFont($DateFont);

    $Legend->setPlotarea($Plotarea);

    $datasetinfo = array(
        'user-count'  => array('color' => 'blue@0.6', 'name' => get_string('users')),
        'view-count'  => array('color' => 'green@0.6', 'name' => get_string('Views', 'view')),
        'group-count' => array('color' => 'red@0.6', 'name' => get_string('groups')),
    );

    $yaxis = array('min' => array(), 'max' => array());
    $points = 1;
    foreach (array_keys($datasetinfo) as $k) {
        $dataset =& Image_Graph::factory('dataset', array($dataarray[$k]));
        $dataset->setName($datasetinfo[$k]['name']);
        $plot =& $Plotarea->addNew('line', array(&$dataset));
        $linestyle =& Image_Graph::factory('Image_Graph_Line_Solid', array($datasetinfo[$k]['color']));
        $linestyle->setThickness(3);
        $plot->setLineStyle($linestyle);
        $yaxis['max'][$k] = max($dataarray[$k]);
        $yaxis['min'][$k] = min($dataarray[$k]);
        $points = max($points, count($dataarray[$k]));
    }

    $AxisX =& $Plotarea->getAxis(IMAGE_GRAPH_AXIS_X);
    $AxisX->setFontAngle('vertical');
    $AxisX->setFontSize(8);
    $AxisX->setLabelInterval(ceil($points/30)); // Avoid label crowding

    $AxisY =& $Plotarea->getAxis(IMAGE_GRAPH_AXIS_Y);
    $maxy = max($yaxis['max']);
    $AxisY->forceMaximum($maxy * 1.025);
    // $miny = min($yaxis['min']);
    // $padding = ($maxy - $miny) * 0.025;
    // $AxisY->forceMaximum($maxy + $padding);
    // $AxisY->forceMinimum($miny - $padding);

    $Graph->done(array('filename' => stats_graph_path('weekly')));
}

function graph_site_data_daily() {
    user_institution_graph();
    group_type_graph();
    view_type_graph();
}

function graph_institution_data_weekly(&$institutiondata) {

    $lastyear = db_format_timestamp(time() - 60*60*12*365);
    $values = array($lastyear, 'view-count', 'user-count', $institutiondata['name']);
    $weekly = get_records_sql_array('
        SELECT ctime, type, "value", ' . db_format_tsfield('ctime', 'ts') . '
        FROM {institution_data}
        WHERE ctime >= ? AND type IN (?,?) AND institution = ?
        ORDER BY ctime, type', $values);

    if (!count($weekly) > 1) {
        return;
    }

    $dataarray = array();
    foreach ($weekly as &$r) {
        $dataarray[$r->type][strftime("%d %b", $r->ts)] = $r->value;
    }
    foreach ($dataarray as &$t) {
        // The graph will look nasty until we have 2 points to plot.
        if (count($t) < 2) {
            return;
        }
    }

    require_once(get_config('libroot') . "pear/Image/Graph.php");

    $Graph =& Image_Graph::factory('graph', array(350, 200));
    $Font =& $Graph->addNew('font', 'Vera');
    $Font->setSize(9);
    $Graph->setFont($Font);

    $Graph->add(
        Image_Graph::vertical(
            Image_Graph::vertical(
                $Plotarea = Image_Graph::factory('plotarea'),
                $Legend = Image_Graph::factory('legend'),
                88
            ),
            $Date = Image_Graph::factory('title', array(format_date(time(), 'strftimew3cdate'), 7)),
            96
        )
    );

    $Date->setAlignment(IMAGE_GRAPH_ALIGN_RIGHT);
    $DateFont =& $Graph->addNew('font', 'Vera');
    $DateFont->setColor('gray@0.8');
    $Date->setFont($DateFont);

    $Legend->setPlotarea($Plotarea);

    $datasetinfo = array(
        'user-count'  => array('color' => 'blue@0.6', 'name' => get_string('users')),
        'view-count'  => array('color' => 'green@0.6', 'name' => get_string('Views', 'view')),
    );

    $yaxis = array('min' => array(), 'max' => array());
    $points = 1;
    foreach (array_keys($datasetinfo) as $k) {
        $dataset =& Image_Graph::factory('dataset', array($dataarray[$k]));
        $dataset->setName($datasetinfo[$k]['name']);
        $plot =& $Plotarea->addNew('line', array(&$dataset));
        $linestyle =& Image_Graph::factory('Image_Graph_Line_Solid', array($datasetinfo[$k]['color']));
        $linestyle->setThickness(3);
        $plot->setLineStyle($linestyle);
        $yaxis['max'][$k] = max($dataarray[$k]);
        $yaxis['min'][$k] = min($dataarray[$k]);
        $points = max($points, count($dataarray[$k]));
    }

    $AxisX =& $Plotarea->getAxis(IMAGE_GRAPH_AXIS_X);
    $AxisX->setFontAngle('vertical');
    $AxisX->setFontSize(8);
    $AxisX->setLabelInterval(ceil($points/30)); // Avoid label crowding

    $AxisY =& $Plotarea->getAxis(IMAGE_GRAPH_AXIS_Y);
    $maxy = max($yaxis['max']);
    $AxisY->forceMaximum($maxy * 1.025);
    // $miny = min($yaxis['min']);
    // $padding = ($maxy - $miny) * 0.025;
    // $AxisY->forceMaximum($maxy + $padding);
    // $AxisY->forceMinimum($miny - $padding);

    $Graph->done(array('filename' => stats_graph_path($institutiondata['name'] . '_weekly')));
}

function graph_institution_data_daily(&$institutiondata) {
    institution_view_type_graph($institutiondata);
}

function stats_graph_path($name) {
    return get_config('dataroot') . 'images/' . $name . '.png';
}

function stats_graph_url($name) {
    if (file_exists(stats_graph_path($name))) {
        return get_config('wwwroot') . 'admin/thumb.php?type=' . $name;
    }
    return '';
}
