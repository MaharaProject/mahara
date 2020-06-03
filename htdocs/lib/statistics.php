<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @file Statistics for a mahara site
 */

defined('INTERNAL') || die();

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
        $data['weekly'] = true;

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
        $data['usersloggedin'] = get_string('loggedinsince', 'admin', $active->today, $active->thisweek, format_date(strtotime($active->weekago), 'strftimedate'), $active->ever);

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

    $data['displayname'] = get_config('sitename');
    $data['name']        = 'all';
    $data['release']     = get_config('release');
    $data['version']     = get_config('version');
    $data['installdate'] = format_date(strtotime(get_config('installation_time')), 'strftimedate');
    $data['dbsize']      = db_total_size();
    $data['diskusage']   = get_field('site_data', 'value', 'type', 'disk-usage');
    $data['cronrunning'] = !record_exists_select('cron', 'nextrun IS NULL OR nextrun < CURRENT_DATE');
    $data['siteclosedbyadmin'] = get_config('siteclosedbyadmin');
    $data['institution'] = 'all';

    if ($latestversion = get_config('latest_version')) {
        $data['latest_version'] = $latestversion;
        if ($data['release'] == $latestversion) {
            $data['uptodate'] = get_string('uptodate', 'admin');
        }
        else {
            $download_page = 'https://launchpad.net/mahara/+download';
            $data['strlatestversion'] = get_string('latestversionis', 'admin', $download_page, $latestversion);
        }
    }
    if ($branchlatest = get_config('latest_branch_version')) {
        if ($data['release'] != $branchlatest) {
            $download_page = 'https://launchpad.net/mahara/+milestone/' . $branchlatest;
            $data['strlatestbranchversion'] = get_string('latestbranchversionis', 'admin', $download_page, $branchlatest);
        }
    }
    if ($insupport = get_config('supported_versions')) {
        $insupport = explode(',', $insupport);
        if (!in_array(get_config('series'), $insupport)) {
            if (preg_match('/dev$/', $data['release'])) {
                $data['strnotinsupport'] = get_string('versionnotinsupportdev', 'admin');
            }
            else {
                $data['strnotinsupport'] = get_string('versionnotinsupport', 'admin', get_config('series'));
            }
        }
    }

    if ($full) {
        // Add the other overall graphs here
        // Group graph
        $smarty = smarty_core();
        $smarty->assign('grouptypecounts', get_records_sql_array("
            SELECT grouptype, COUNT(id) AS groupcount
            FROM {group}
            WHERE deleted = 0
            GROUP BY grouptype
            ORDER BY groupcount DESC", array()
        ));
        $smarty->assign('jointypecounts', get_records_sql_array("
            SELECT jointype, COUNT(id) AS groupcount
            FROM {group}
            WHERE deleted = 0
            GROUP BY jointype
            ORDER BY groupcount DESC", array()
        ));
        $smarty->assign('groupgraph', true);
        $data['groupinfo'] = $smarty->fetch('admin/groupstatssummary.tpl');

        // Users graph
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
        $meanfriends = 2 * count_records('usr_friend') / $data['users'];
        if ($maxfriends) {
            $data['strmaxfriends'] = get_string(
                'statsmaxfriends1',
                'admin',
                $maxfriends->friends,
                round($meanfriends, 1),
                profile_url($maxfriends),
                hsc(display_name($maxfriends, null, true))
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
                'statsmaxviews1',
                'admin',
                $maxviews->views,
                $data['viewsperuser'],
                profile_url($maxviews),
                hsc(display_name($maxviews, null, true))
            );
        }
        else {
            $data['strmaxviews'] = get_string('statsnoviews', 'admin');
        }
        $maxgroups = get_records_sql_array("
            SELECT u.id, u.firstname, u.lastname, u.preferredname, u.urlid, COUNT(m.group) AS groupcount
            FROM {usr} u JOIN {group_member} m ON u.id = m.member JOIN {group} g ON m.group = g.id
            WHERE g.deleted = 0
            GROUP BY u.id, u.firstname, u.lastname, u.preferredname, u.urlid
            ORDER BY groupcount DESC
            LIMIT 1", array());
        $maxgroups = $maxgroups[0];
        if ($maxgroups) {
            $data['strmaxgroups'] = get_string(
                'statsmaxgroups1',
                'admin',
                $maxgroups->groupcount,
                $data['groupmemberaverage'],
                profile_url($maxgroups),
                hsc(display_name($maxgroups, null, true))
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
            'statsmaxquotaused1',
            'admin',
            display_size(get_field('usr', 'AVG(quotaused)', 'deleted', 0)),
            profile_url($maxquotaused),
            hsc(display_name($maxquotaused, null, true)),
            display_size($maxquotaused->quotaused)
        );

        $smarty = smarty_core();
        $smarty->assign('data', $data);
        $data['usersinfo'] = $smarty->fetch('admin/userstatssummary.tpl');

        // Views graph
        $smarty = smarty_core();
        $maxblocktypes = 5;
        $blocktypecounts = get_records_sql_array("
            SELECT
                b.blocktype,
                COUNT(b.id) AS blocks
            FROM {block_instance} b
            JOIN {blocktype_installed} bi ON (b.blocktype = bi.name)
            JOIN {view} v ON (b.view = v.id AND v.type = 'portfolio')
            GROUP BY b.blocktype
            ORDER BY blocks DESC",
            array(), 0, $maxblocktypes
        );
        if (is_array($blocktypecounts)) {
            foreach ($blocktypecounts as $blocktype) {
                safe_require('blocktype', $blocktype->blocktype);
                $classname = generate_class_name('blocktype', $blocktype->blocktype);
                $blocktype->title = $classname::get_title();
            }
        }
        $smarty->assign('blocktypecounts', $blocktypecounts);

        $smarty->assign('viewtypes', true);
        $smarty->assign('viewcount', $data['views']);
        $data['viewsinfo'] = $smarty->fetch('admin/viewstatssummary.tpl');
    }
    return($data);
}

function institution_data_current($institution) {
    $data = array();
    if ($institution == 'mahara') {
        $membersquery = 'SELECT id FROM {usr}
                WHERE deleted = 0 AND id > 0 AND id NOT IN
                (SELECT usr FROM {usr_institution})';
        $membersqueryparams = array();
    }
    else {
        $membersquery = 'SELECT usr FROM {usr_institution} ui
                JOIN {usr} u ON (u.id = ui.usr)
                WHERE u.deleted = 0 AND ui.institution = ?';
        $membersqueryparams = array($institution);
    }
    $data['memberssql'] = $membersquery;
    $data['memberssqlparams'] = $membersqueryparams;
    $data['users'] = get_field_sql('SELECT COUNT(*) FROM (' . $membersquery . ') AS members', $membersqueryparams);
    if (!$data['users']) {
        $data['views'] = 0;
    }
    else {
        $data['viewssql'] = '
                SELECT id FROM {view}
                    WHERE owner IS NOT NULL AND owner IN (' . $membersquery . ')
                UNION
                    SELECT id FROM {view}
                    WHERE institution IS NOT NULL AND institution = ?';
        $data['viewssqlparam'] = array_merge($membersqueryparams, array($institution));
        $data['views'] = count_records_sql('SELECT COUNT(*) FROM (' . $data['viewssql'] . ') AS members',   $data['viewssqlparam']);
    }
    return $data;
}

function institution_statistics($institution, $full=false) {
    $data = array();

    if ($full) {
        $data = institution_data_current($institution);
        $data['weekly'] = true;
        $data['institution'] = $institution;

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
                    WHERE id IN (" . $data['memberssql'] . ")";
            $active = get_record_sql($sql, $data['memberssqlparams']);
        }
        $data['usersloggedin'] = get_string('loggedinsince', 'admin', $active->today, $active->thisweek, format_date(strtotime($active->weekago), 'strftimedate'), $active->ever);

        if (!$data['users']) {
            $data['groupmemberaverage'] = 0;
        }
        else {
            $memberships = count_records_sql("
                SELECT COUNT(*)
                FROM {group_member} m JOIN {group} g ON g.id = m.group
                WHERE g.deleted = 0 AND m.member IN (" . $data['memberssql'] . ")
            ", $data['memberssqlparams']);
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
                        WHERE id IN (" . $data['viewssql'] . ")
                    ", $data['viewssqlparam']);
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
            WHERE deleted = 0 AND id IN (" . $data['memberssql'] . ")
            ", $data['memberssqlparams']);
    }

    if ($full) {
        // Add the other overall graphs here
        // Users for institution graph
        if (!$data['users']) {
            $data['strmaxfriends'] = get_string('statsnofriends', 'admin');
            $data['strmaxviews'] = get_string('statsnoviews', 'admin');
            $data['strmaxgroups'] = get_string('statsnogroups', 'admin');
            $data['strmaxquotaused'] = get_string('statsnoquota', 'admin');

            $smarty = smarty_core();
            $smarty->assign('data', $data);
            $data['summary'] = $smarty->fetch('admin/institutionuserstatssummary.tpl');

        }
        else {
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
                WHERE u.id IN (" . $data['memberssql'] . ")
                GROUP BY u.id, u.firstname, u.lastname, u.preferredname, u.urlid
                ORDER BY friends DESC
                LIMIT 1", $data['memberssqlparams']);
            $maxfriends = $maxfriends[0];
            $meanfriends = count_records_sql('SELECT COUNT(*) FROM
                        (SELECT * FROM {usr_friend}
                            WHERE usr1 IN (' . $data['memberssql'] . ')
                        UNION ALL SELECT * FROM {usr_friend}
                            WHERE usr2 IN (' . $data['memberssql'] . ')
                        ) tmp', array_merge($data['memberssqlparams'], $data['memberssqlparams'])) /
                        $data['users'];
            if ($maxfriends) {
                $data['strmaxfriends'] = get_string(
                    'statsmaxfriends1',
                    'admin',
                    $maxfriends->friends,
                    round($meanfriends, 1),
                    profile_url($maxfriends),
                    hsc(display_name($maxfriends, null, true))
                );
            }
            else {
                $data['strmaxfriends'] = get_string('statsnofriends', 'admin');
            }
            $maxviews = get_records_sql_array("
                SELECT u.id, u.firstname, u.lastname, u.preferredname, u.urlid, COUNT(v.id) AS views
                FROM {usr} u JOIN {view} v ON u.id = v.owner
                WHERE \"owner\" IN (" . $data['memberssql'] . ")
                GROUP BY u.id, u.firstname, u.lastname, u.preferredname, u.urlid
                ORDER BY views DESC
                LIMIT 1", $data['memberssqlparams']);
            $maxviews = $maxviews[0];
            if ($maxviews) {
                $data['strmaxviews'] = get_string(
                    'statsmaxviews1',
                    'admin',
                    $maxviews->views,
                    $data['viewsperuser'],
                    profile_url($maxviews),
                    hsc(display_name($maxviews, null, true))
                );
            }
            else {
                $data['strmaxviews'] = get_string('statsnoviews', 'admin');
            }
            $maxgroups = get_records_sql_array("
                SELECT u.id, u.firstname, u.lastname, u.preferredname, u.urlid, COUNT(m.group) AS groupcount
                FROM {usr} u JOIN {group_member} m ON u.id = m.member JOIN {group} g ON m.group = g.id
                WHERE g.deleted = 0 AND u.id IN (" . $data['memberssql'] . ")
                GROUP BY u.id, u.firstname, u.lastname, u.preferredname, u.urlid
                ORDER BY groupcount DESC
                LIMIT 1", $data['memberssqlparams']);
            $maxgroups = $maxgroups[0];
            if ($maxgroups) {
                $data['strmaxgroups'] = get_string(
                    'statsmaxgroups1',
                    'admin',
                    $maxgroups->groupcount,
                    $data['groupmemberaverage'],
                    profile_url($maxgroups),
                    hsc(display_name($maxgroups, null, true))
                );
            }
            else {
                $data['strmaxgroups'] = get_string('statsnogroups', 'admin');
            }
            $maxquotaused = get_records_sql_array("
                SELECT id, firstname, lastname, preferredname, urlid, quotaused
                FROM {usr}
                WHERE id IN (" . $data['memberssql'] . ")
                ORDER BY quotaused DESC
                LIMIT 1", $data['memberssqlparams']);
            $maxquotaused = $maxquotaused[0];
            $avgquota = get_field_sql("
                SELECT AVG(quotaused)
                FROM {usr}
                WHERE id IN (" . $data['memberssql'] . ")
                ", $data['memberssqlparams']);
            $data['strmaxquotaused'] = get_string(
                'statsmaxquotaused1',
                'admin',
                display_size($avgquota),
                profile_url($maxquotaused),
                hsc(display_name($maxquotaused, null, true)),
                display_size($maxquotaused->quotaused)
            );

            $smarty = smarty_core();
            $smarty->assign('data', $data);
            $data['usersinfo'] = $smarty->fetch('admin/institutionuserstatssummary.tpl');
        }
        // Views for institution graph
        $smarty = smarty_core();
        $maxblocktypes = 5;
        if ($data['views']) {
            $smarty->assign('blocktypecounts', get_records_sql_array("
                SELECT
                    b.blocktype,
                    CASE WHEN bi.artefactplugin IS NULL THEN b.blocktype
                        ELSE bi.artefactplugin || '/' || b.blocktype END AS langsection,
                    COUNT(b.id) AS blocks
                FROM {block_instance} b
                JOIN {blocktype_installed} bi ON (b.blocktype = bi.name)
                JOIN {view} v ON (b.view = v.id AND v.type = 'portfolio')
                WHERE v.id IN (" . $data['viewssql'] . ")
                GROUP BY b.blocktype, langsection
                ORDER BY blocks DESC",
                $data['viewssqlparam'], 0, $maxblocktypes
            ));
        }
        $smarty->assign('viewtypes', true);
        $smarty->assign('institution', $data['name']);
        $smarty->assign('viewcount', $data['views']);
        $data['viewsinfo'] = $smarty->fetch('admin/institutionviewstatssummary.tpl');
    }

    return($data);
}

function get_heading_html($heading) {
    $smarty = smarty_core();
    $smarty->assign('heading', $heading);
    return $smarty->fetch('admin/users/statsheading.tpl');
}

function get_active_columns(&$data, $extra) {
    $activeheadings = array();
    foreach ($data['tableheadings'] as $key => $heading) {
        $data['tableheadings'][$key]['selected'] = in_array($heading['id'], $extra['columns']);
        // make sure the required ones are always selected
        if (!empty($data['tableheadings'][$key]['required'])) {
            $data['tableheadings'][$key]['selected'] = true;
        }
        if ($data['tableheadings'][$key]['selected']) {
            $activeheadings[$data['tableheadings'][$key]['id']] = $data['tableheadings'][$key]['name'];
            // To add the accessibility info
            $data['tableheadings'][$key]['sr'] = get_string('sortby') . ' ' . get_string('ascending');
            if (!empty($extra['sortdesc']) && $extra['sort'] == $heading['id']) {
                $data['tableheadings'][$key]['sr'] = get_string('sortby') . ' ' . get_string('descending');
            }
            $data['tableheadings'][$key]['html'] = get_heading_html($data['tableheadings'][$key]);
        }
    }
    // Make sure the all active columns are selected
    return $activeheadings;
}

function userdetails_statistics_headers($extra, $urllink) {
    return array(
        array('id' => 'rownum', 'name' => '#'),
        array(
              'id' => 'firstname', 'required' => true,
              'name' => get_string('firstname'),
              'class' => format_class($extra, 'firstname'),
              'link' => format_goto($urllink . '&sort=firstname', $extra, array('sort'), 'firstname')
        ),
        array(
              'id' => 'lastname', 'required' => true,
              'name' => get_string('lastname'),
              'class' => format_class($extra, 'lastname'),
              'link' => format_goto($urllink . '&sort=lastname', $extra, array('sort'), 'lastname')
        ),
        array(
              'id' => 'email', 'required' => true,
              'name' => get_string('email'),
              'class' => format_class($extra, 'email'),
              'link' => format_goto($urllink . '&sort=email', $extra, array('sort'), 'email')
        ),
        array(
              'id' => 'studentid',
              'name' => get_string('studentid'),
              'class' => format_class($extra, 'studentid'),
              'link' => format_goto($urllink . '&sort=studentid', $extra, array('sort'), 'studentid')
        ),
        array(
              'id' => 'displayname', 'required' => true,
              'name' => get_string('displayname'),
              'class' => format_class($extra, 'displayname'),
              'link' => format_goto($urllink . '&sort=displayname', $extra, array('sort'), 'displayname')
        ),
        array(
              'id' => 'username', 'required' => true,
              'name' => get_string('username'),
              'class' => format_class($extra, 'username'),
              'link' => format_goto($urllink . '&sort=username', $extra, array('sort'), 'username')
        ),
        array(
              'id' => 'remotename',
              'name' => get_string('remoteuser', 'admin'),
              'class' => format_class($extra, 'remotename'),
              'link' => format_goto($urllink . '&sort=remotename', $extra, array('sort'), 'remotename')
        ),
        array(
              'id' => 'quotapercent',
              'name' => get_string('quotapercent', 'admin'),
              'class' => format_class($extra, 'quotapercent'),
              'link' => format_goto($urllink . '&sort=quotapercent', $extra, array('sort'), 'quotapercent')
        ),
        array(
              'id' => 'lastlogin',
              'name' => get_string('lastlogin', 'admin'),
              'class' => format_class($extra, 'lastlogin'),
              'link' => format_goto($urllink . '&sort=lastlogin', $extra, array('sort'), 'lastlogin')
        ),
        array(
              'id' => 'probation',
              'name' => get_string('probationreportcolumn', 'admin'),
              'class' => format_class($extra, 'probation'),
              'link' => format_goto($urllink . '&sort=probation', $extra, array('sort'), 'probation'),
              'disabled' => empty(get_config('probationenabled'))
        ),
    );
}

function userdetails_statistics($limit, $offset, $extra, $institution = null) {
    userhasaccess($institution, 'userdetails');
    $data = array();
    $urllink = get_config('wwwroot') . 'admin/users/statistics.php?type=users&subtype=userdetails';
    if ($institution) {
        $urllink .= '&institution=' . $institution;
    }
    $data['tableheadings'] = userdetails_statistics_headers($extra, $urllink);

    $activeheadings = get_active_columns($data, $extra);
    $extra['columns'] = array_keys($activeheadings);

    $data['table'] = userdetails_stats_table($limit, $offset, $extra, $institution, $urllink);
    $data['table']['activeheadings'] = $activeheadings;

    $data['summary'] = $data['table']['count'] == 0 ? get_string('nostats', 'admin') : null;

    return $data;
}

function userdetails_stats_table($limit, $offset, $extra, $institution, $urllink) {
    global $USER, $SESSION;

    $start = !empty($extra['start']) ? $extra['start'] : null;
    $end = !empty($extra['end']) ? $extra['end'] : date('Y-m-d', strtotime('now'));
    $users = $SESSION->get('usersforstats');

    $fromsql = " FROM {usr} u";
    $wheresql = " WHERE u.deleted = 0 AND id != 0";
    $where = array();
    if ($institution) {
        $fromsql .= " JOIN {usr_institution} ui ON (ui.usr = u.id AND ui.institution = ?)";
        $where = array($institution);
    }
    if ($users) {
        $wheresql .= " AND u.id IN (" . join(',', array_map('db_quote', array_values((array)$users))) . ")";
    }
    if ($start) {
        $wheresql .= " AND u.ctime >= DATE(?) AND u.ctime <= DATE(?)";
        $where[] = $start;
        $where[] = $end;
    }
    $count = count_records_sql("SELECT COUNT(*) " . $fromsql . $wheresql, $where);

    $pagination = build_pagination(array(
        'id' => 'stats_pagination',
        'url' => $urllink,
        'jsonscript' => 'admin/users/statistics.json.php',
        'datatable' => 'statistics_table',
        'count' => $count,
        'limit' => $limit,
        'offset' => $offset,
        'setlimit' => true,
        'extradata' => $extra,
    ));

    $result = array(
        'count'         => $count,
        'tablerows'     => '',
        'pagination'    => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
    );

    $result['settings']['start'] = ($start) ? $start : null;
    $result['settings']['end'] = $end;
    $result['settings']['users'] = !empty($users) ? count($users) : 0;

    if ($count < 1) {
        return $result;
    }

    $sorttype = !empty($extra['sort']) ? $extra['sort'] : '';
    switch ($sorttype) {
        case "lastlogin":
        case "displayname":
        case "username":
        case "email":
        case "remotename":
        case "quotapercent":
        case "studentid":
        case "probation":
            $orderby = " " . $sorttype . " " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC') . ", CONCAT (u.firstname, ' ', u.lastname)";
            break;
        case "lastname":
            $orderby = " u.lastname " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC') . ", u.firstname " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "firstname":
        default:
            $orderby = " u.firstname " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC') . ", u.lastname " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
    }

    $sql = "SELECT u.id, u.firstname, u.lastname, u.username, u.preferredname AS displayname,
            u.lastlogin, u.email, u.studentid, u.ctime,
            (SELECT remoteusername FROM {auth_remote_user} aru WHERE aru.localusr = u.id LIMIT 1) AS remotename,
            ((u.quotaused * 1.0)/ u.quota) AS quotapercent, u.quota, u.quotaused, u.probation
            " . $fromsql . $wheresql . "
            ORDER BY " . $orderby;
    if (empty($extra['csvdownload'])) {
        $sql .= " LIMIT $limit OFFSET $offset";
    }
    $data = get_records_sql_array($sql, $where);
    $daterange = array_map(function ($obj) { return $obj->ctime; }, $data);
    $result['settings']['start'] = ($start) ? $start : min($daterange);

    foreach ($data as $item) {
        $item->profileurl = profile_url($item->id);
        $item->lastlogin = $item->lastlogin ? format_date(strtotime($item->lastlogin)) : ' ';
        $item->quotapercent_format = round($item->quotapercent * 100);
        $item->quota_format = display_size($item->quota);
        $item->quotaused_format = !empty($item->quotaused) ? display_size($item->quotaused) : 0;
        // Map statistics page column headers to CSV column headers to allow for easier user update CSV import
        $item->preferredname = $item->displayname;
        $item->remoteuser = $item->remotename;
    }
    if (!empty($extra['csvdownload'])) {
        $csvfields = array('firstname', 'lastname', 'email', 'studentid',
                           'preferredname', 'username', 'remoteuser', 'quotapercent_format', 'lastlogin', 'probation');
        $USER->set_download_file(generate_csv($data, $csvfields), $institution . 'userdetailsstatistics.csv', 'text/csv');
    }
    $result['csv'] = true;
    $columnkeys = array();
    foreach ($extra['columns'] as $column) {
        $columnkeys[$column] = 1;
    }

    $smarty = smarty_core();
    $smarty->assign('data', $data);
    $smarty->assign('columns', $columnkeys);
    $smarty->assign('offset', $offset);

    $result['tablerows'] = $smarty->fetch('admin/users/userdetailsstats.tpl');

    return $result;
}

function useragreement_statistics_headers($extra, $urllink) {
    return array(
        array('id' => 'rownum', 'name' => '#'),
        array(
              'id' => 'firstname', 'required' => true,
              'name' => get_string('firstname'),
              'class' => format_class($extra, 'firstname'),
              'link' => format_goto($urllink . '&sort=firstname', $extra, array('sort'), 'firstname')
        ),
        array(
              'id' => 'lastname', 'required' => true,
              'name' => get_string('lastname'),
              'class' => format_class($extra, 'lastname'),
              'link' => format_goto($urllink . '&sort=lastname', $extra, array('sort'), 'lastname')
        ),
        array(
              'id' => 'displayname',
              'name' => get_string('displayname'),
              'class' => format_class($extra, 'displayname'),
              'link' => format_goto($urllink . '&sort=displayname', $extra, array('sort'), 'displayname')
        ),
        array(
              'id' => 'email',
              'name' => get_string('email'),
              'class' => format_class($extra, 'email'),
              'link' => format_goto($urllink . '&sort=email', $extra, array('sort'), 'email')
        ),
        array(
              'id' => 'username',
              'name' => get_string('username'),
              'class' => format_class($extra, 'username'),
              'link' => format_goto($urllink . '&sort=username', $extra, array('sort'), 'username')
        ),
        array(
              'id' => 'siteprivacy', 'required' => true,
              'name' => get_string('siteprivacy', 'admin'),
              'class' => format_class($extra, 'siteprivacy'),
              'link' => format_goto($urllink . '&sort=siteprivacy', $extra, array('sort'), 'siteprivacy')
        ),
        array(
              'id' => 'siteprivacyconsentdate',
              'name' => get_string('siteprivacyconsentdate', 'admin'),
              'class' => format_class($extra, 'siteprivacyconsentdate'),
              'link' => format_goto($urllink . '&sort=siteprivacyconsentdate', $extra, array('sort'), 'siteprivacyconsentdate')
        ),
        array(
              'id' => 'siteterms', 'required' => true,
              'name' => get_string('sitetermsandconditions', 'admin'),
              'class' => format_class($extra, 'siteterms'),
              'link' => format_goto($urllink . '&sort=siteterms', $extra, array('sort'), 'siteterms')
        ),
        array(
              'id' => 'sitetermsconsentdate',
              'name' => get_string('sitetermsconsentdate', 'admin'),
              'class' => format_class($extra, 'sitetermsconsentdate'),
              'link' => format_goto($urllink . '&sort=sitetermsconsentdate', $extra, array('sort'), 'sitetermsconsentdate')
        ),
        array(
              'id' => 'institutionprivacy', 'required' => true,
              'name' => get_string('institutionprivacystatement', 'admin'),
              'class' => format_class($extra, 'institutionprivacy'),
              'link' => format_goto($urllink . '&sort=institutionprivacy', $extra, array('sort'), 'institutionprivacy')
        ),
        array(
              'id' => 'institutionprivacyconsentdate',
              'name' => get_string('institutionprivacyconsentdate', 'admin'),
              'class' => format_class($extra, 'institutionprivacyconsentdate'),
              'link' => format_goto($urllink . '&sort=institutionprivacyconsentdate', $extra, array('sort'), 'institutionprivacyconsentdate')
        ),
        array(
              'id' => 'institutionterms', 'required' => true,
              'name' => get_string('institutiontermsandconditions', 'admin'),
              'class' => format_class($extra, 'institutionterms'),
              'link' => format_goto($urllink . '&sort=institutionterms', $extra, array('sort'), 'institutionterms')
        ),
        array(
              'id' => 'institutiontermsconsentdate',
              'name' => get_string('institutiontermsconsentdate', 'admin'),
              'class' => format_class($extra, 'institutiontermsconsentdate'),
              'link' => format_goto($urllink . '&sort=institutiontermsconsentdate', $extra, array('sort'), 'institutiontermsconsentdate')
        ),
        array(
              'id' => 'institution',
              'name' => get_string('institution', 'admin'),
              'class' => format_class($extra, 'institution'),
              'link' => format_goto($urllink . '&sort=institution', $extra, array('sort'), 'institution')
        ),
    );
}

function useragreement_statistics($limit, $offset, $extra, $institution = null) {
    userhasaccess($institution, 'useragreement');
    $data = array();
    $urllink = get_config('wwwroot') . 'admin/users/statistics.php?type=users&subtype=useragreement';
    if ($institution) {
        $urllink .= '&institution=' . $institution;
    }
    $data['tableheadings'] = useragreement_statistics_headers($extra, $urllink);

    $activeheadings = get_active_columns($data, $extra);
    $extra['columns'] = array_keys($activeheadings);

    $data['table'] = useragreement_stats_table($limit, $offset, $extra, $institution, $urllink);
    $data['table']['activeheadings'] = $activeheadings;

    $data['summary'] = $data['table']['count'] == 0 ? get_string('nostats', 'admin') : null;

    return $data;
}

function useragreement_stats_table($limit, $offset, $extra, $institution, $urllink) {
    global $USER, $SESSION;

    $start = !empty($extra['start']) ? $extra['start'] : null;
    $end = !empty($extra['end']) ? $extra['end'] : date('Y-m-d', strtotime('now'));
    $users = $SESSION->get('usersforstats');

    $fromsql = "
        FROM {usr_agreement} ua
        JOIN {usr} u ON u.id = ua.usr
        LEFT JOIN {usr_institution} ui ON ui.usr = ua.usr
        LEFT JOIN {institution} i ON i.name = ui.institution
        LEFT JOIN (
            SELECT MAX(ua.sitecontentid) AS siteprivacyid, MAX(ua.ctime) AS ctime, ua.usr
            FROM {usr_agreement} ua
            JOIN {site_content_version} s ON (s.id = ua.sitecontentid AND s.type = 'privacy' AND s.institution = 'mahara')
            GROUP BY ua.usr
        ) sp ON ua.usr = sp.usr
        LEFT JOIN (
            SELECT MAX(ua.sitecontentid) AS sitetermsid, MAX(ua.ctime) AS ctime, ua.usr
            FROM {usr_agreement} ua
            JOIN {site_content_version} s ON (s.id = ua.sitecontentid AND s.type = 'termsandconditions' AND s.institution = 'mahara')
            GROUP BY ua.usr
        ) st ON ua.usr = st.usr
        LEFT JOIN (
            SELECT MAX(ua.sitecontentid) AS institutionprivacyid, MAX(ua.ctime) AS ctime, ua.usr
            FROM {usr_agreement} ua
            JOIN {site_content_version} s ON (s.id = ua.sitecontentid AND s.type = 'privacy' AND s.institution != 'mahara')
            JOIN {usr_institution} ui ON (ui.institution = s.institution AND ui.usr = ua.usr)
            GROUP BY ua.usr
        ) ip ON ua.usr = ip.usr
        LEFT JOIN (
            SELECT MAX(ua.sitecontentid) AS institutiontermsid, MAX(ua.ctime) AS ctime, ua.usr
            FROM {usr_agreement} ua
            JOIN {site_content_version} s ON (s.id = ua.sitecontentid AND s.type = 'termsandconditions' AND s.institution != 'mahara')
            JOIN {usr_institution} ui ON (ui.institution = s.institution AND ui.usr = ua.usr)
            GROUP BY ua.usr
        ) it ON ua.usr = it.usr";
    $wheresql = " WHERE u.deleted = 0 AND u.id != 0";
    $where = array();
    if ($institution) {
        if ($institution == 'mahara') {
            $wheresql .= " AND ui.institution IS NULL";
        }
        else {
            $wheresql .= " AND ui.institution = ?";
            $where = array($institution);
        }
    }
    if ($users) {
        $wheresql .= " AND u.id IN (" . join(',', array_map('db_quote', array_values((array)$users))) . ")";
    }
    if ($start) {
        $wheresql .= " AND COALESCE(it.ctime, ip.ctime, st.ctime, sp.ctime) >= DATE(?) AND COALESCE(it.ctime, ip.ctime, st.ctime, sp.ctime) <= DATE(?)";
        $where[] = $start;
        $where[] = $end;
    }

    $count = count_records_sql("SELECT COUNT(DISTINCT(ua.usr)) " . $fromsql . $wheresql, $where);

    $pagination = build_pagination(array(
        'id' => 'stats_pagination',
        'url' => $urllink,
        'jsonscript' => 'admin/users/statistics.json.php',
        'datatable' => 'statistics_table',
        'count' => $count,
        'limit' => $limit,
        'offset' => $offset,
        'setlimit' => true,
        'extradata' => $extra,
    ));

    $result = array(
        'count'         => $count,
        'tablerows'     => '',
        'pagination'    => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
    );

    $result['settings']['start'] = ($start) ? $start : null;
    $result['settings']['end'] = $end;
    $result['settings']['users'] = !empty($users) ? count($users) : 0;

    if ($count < 1) {
        return $result;
    }

    $sorttype = !empty($extra['sort']) ? $extra['sort'] : '';
    switch ($sorttype) {
        case "siteprivacy":
            $orderby = " (SELECT version FROM {site_content_version} WHERE id = sp.siteprivacyid) " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "siteterms":
            $orderby = " (SELECT version FROM {site_content_version} WHERE id = st.sitetermsid) " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "institutionprivacy":
            $orderby = " (SELECT version FROM {site_content_version} WHERE id = ip.institutionprivacyid) " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "institutionterms":
            $orderby = " (SELECT version FROM {site_content_version} WHERE id = it.institutiontermsid) " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "siteprivacyconsentdate":
            $orderby = " sp.ctime " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "sitetermsconsentdate":
            $orderby = " st.ctime " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "institutionprivacyconsentdate":
            $orderby = " ip.ctime " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "institutiontermsconsentdate":
            $orderby = " it.ctime " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "username":
        case "displayname":
        case "email":
            $orderby = " " . $sorttype . " " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC') . ", CONCAT (u.firstname, ' ', u.lastname)";
            break;
        case "lastname":
            $orderby = " u.lastname " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC') . ", u.firstname " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "firstname":
        default:
            $orderby = " u.firstname " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC') . ", u.lastname " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
    }

    $sql = "SELECT DISTINCT ua.usr, sp.siteprivacyid, sp.ctime AS siteprivacyconsentdate, (SELECT version FROM {site_content_version} WHERE id = sp.siteprivacyid) AS siteprivacy,
            st.sitetermsid, st.ctime AS sitetermsconsentdate, (SELECT version FROM {site_content_version} WHERE id = st.sitetermsid) AS siteterms,
            ip.institutionprivacyid, ip.ctime AS institutionprivacyconsentdate, (SELECT version FROM {site_content_version} WHERE id = ip.institutionprivacyid) AS institutionprivacy,
            it.institutiontermsid, it.ctime AS institutiontermsconsentdate, (SELECT version FROM {site_content_version} WHERE id = it.institutiontermsid) AS institutionterms,
            u.id, u.username, u.firstname, u.lastname, u.preferredname AS displayname, u.email,
            i.name, i.displayname AS instname, COALESCE(it.ctime, ip.ctime, st.ctime, sp.ctime) AS defaulttime
            " . $fromsql . $wheresql . "
            ORDER BY " . $orderby;

    if (empty($extra['csvdownload'])) {
        $sql .= " LIMIT $limit OFFSET $offset";
    }
    $data = get_records_sql_array($sql, $where);

    foreach ($data as $item) {
        $item->profileurl = profile_url($item->id);
        $item->siteprivacyconsentdate = $item->siteprivacyconsentdate ? format_date(strtotime($item->siteprivacyconsentdate)) : ' ';
        $item->sitetermsconsentdate = $item->sitetermsconsentdate ? format_date(strtotime($item->sitetermsconsentdate)) : ' ';
        $item->institutionprivacyconsentdate = $item->institutionprivacyconsentdate ? format_date(strtotime($item->institutionprivacyconsentdate)) : ' ';
        $item->institutiontermsconsentdate = $item->institutiontermsconsentdate ? format_date(strtotime($item->institutiontermsconsentdate)) : ' ';
    }

    if (!empty($extra['csvdownload'])) {
        $csvfields = array('username', 'firstname', 'lastname', 'displayname', 'email',
                           'siteprivacy', 'siteprivacyconsentdate', 'siteterms', 'sitetermsconsentdate',
                           'institutionprivacy', 'institutionprivacyconsentdate', 'institutionterms', 'institutiontermsconsentdate',
                           'instname');
        $USER->set_download_file(generate_csv($data, $csvfields), $institution . 'useragreementsstatistics.csv', 'text/csv');
    }
    $result['csv'] = true;
    $columnkeys = array();
    foreach ($extra['columns'] as $column) {
        $columnkeys[$column] = 1;
    }

    $smarty = smarty_core();
    $smarty->assign('data', $data);
    $smarty->assign('columns', $columnkeys);
    $smarty->assign('offset', $offset);

    $result['tablerows'] = $smarty->fetch('admin/users/useragreementstats.tpl');

    return $result;
}

function useractivity_statistics_headers($extra, $urllink) {
    return array(
        array('id' => 'rownum', 'name' => '#'),
        array(
              'id' => 'firstname', 'required' => true,
              'name' => get_string('firstname'),
              'class' => format_class($extra, 'firstname'),
              'link' => format_goto($urllink . '&sort=firstname', $extra, array('sort'), 'firstname')
        ),
        array(
              'id' => 'lastname', 'required' => true,
              'name' => get_string('lastname'),
              'class' => format_class($extra, 'lastname'),
              'link' => format_goto($urllink . '&sort=lastname', $extra, array('sort'), 'lastname')
        ),
        array(
              'id' => 'displayname',
              'name' => get_string('displayname'),
              'class' => format_class($extra, 'displayname'),
              'link' => format_goto($urllink . '&sort=displayname', $extra, array('sort'), 'displayname')
        ),
        array(
              'id' => 'username',
              'name' => get_string('username'),
              'class' => format_class($extra, 'username'),
              'link' => format_goto($urllink . '&sort=username', $extra, array('sort'), 'username')
        ),
        array(
              'id' => 'artefacts', 'required' => true,
              'name' => get_string('artefacts'),
              'class' => format_class($extra, 'artefacts'),
              'link' => format_goto($urllink . '&sort=artefacts', $extra, array('sort'), 'artefacts'),
              'helplink' => get_help_icon('core', 'reports', 'useractivity', 'artefacts')
        ),
        array(
              'id' => 'pages', 'required' => true,
              'name' => get_string('Views', 'view'),
              'class' => format_class($extra, 'pages'),
              'link' => format_goto($urllink . '&sort=pages', $extra, array('sort'), 'pages'),
              'helplink' => get_help_icon('core', 'reports', 'useractivity', 'pages')
        ),
        array(
              'id' => 'collections', 'required' => true,
              'name' => get_string('Collections', 'collection'),
              'class' => format_class($extra, 'collections'),
              'link' => format_goto($urllink . '&sort=collections', $extra, array('sort'), 'collections'),
              'helplink' => get_help_icon('core', 'reports', 'useractivity', 'collections')
        ),
        array(
              'id' => 'groups', 'required' => true,
              'name' => get_string('groups'),
              'class' => format_class($extra, 'groups'),
              'link' => format_goto($urllink . '&sort=groups', $extra, array('sort'), 'groups'),
              'helplink' => get_help_icon('core', 'reports', 'useractivity', 'groups')
        ),
        array(
              'id' => 'logins', 'required' => true,
              'name' => get_string('logins'),
              'class' => format_class($extra, 'logins'),
              'link' => format_goto($urllink . '&sort=logins', $extra, array('sort'), 'logins'),
              'helplink' => get_help_icon('core', 'reports', 'useractivity', 'logins')
        ),
        array(
              'id' => 'actions',
              'name' => get_string('actions', 'statistics'),
              'class' => format_class($extra, 'actions'),
              'link' => format_goto($urllink . '&sort=actions', $extra, array('sort'), 'actions'),
              'helplink' => get_help_icon('core', 'reports', 'useractivity', 'actions')
        ),
        array(
              'id' => 'lastlogin', 'required' => true,
              'name' => get_string('lastactiontime', 'statistics'),
              'class' => format_class($extra, 'lastlogin'),
              'link' => format_goto($urllink . '&sort=lastlogin', $extra, array('sort'), 'lastlogin'),
              'helplink' => get_help_icon('core', 'reports', 'useractivity', 'lastlogin')
        ),
        array(
              'id' => 'lastactivity',
              'name' => get_string('lastaction', 'statistics'),
              'class' => format_class($extra, 'lastactivity'),
              'helplink' => get_help_icon('core', 'reports', 'useractivity', 'lastactivity'),
//              'link' => format_goto($urllink . '&sort=lastactivity', $extra, array('sort'), 'lastactivity')
        ),
    );
}

function useractivity_statistics($limit, $offset, $extra, $institution = null) {
    userhasaccess($institution, 'useractivity');
    if (!get_config('eventlogenhancedsearch')) {
        return array('notvalid_errorstring' => get_string('needadvancedanalytics', 'statistics'));
    }
    $data = array();
    $urllink = get_config('wwwroot') . 'admin/users/statistics.php?type=users&subtype=useractivity';
    if ($institution) {
        $urllink .= '&institution=' . $institution;
    }
    $data['tableheadings'] = useractivity_statistics_headers($extra, $urllink);

    $activeheadings = get_active_columns($data, $extra);
    $extra['columns'] = array_keys($activeheadings);

    $data['table'] = useractivity_stats_table($limit, $offset, $extra, $institution, $urllink);
    $data['table']['activeheadings'] = $activeheadings;

    $data['summary'] = $data['table']['count'] == 0 ? get_string('nostats', 'admin') : null;

    return $data;
}

function useractivity_stats_table($limit, $offset, $extra, $institution, $urllink) {
    global $USER, $SESSION;

    $start = !empty($extra['start']) ? $extra['start'] : null;
    $end = !empty($extra['end']) ? $extra['end'] : date('Y-m-d', strtotime('now'));
    $users = $SESSION->get('usersforstats');

    $fromsql = " FROM {usr} u";
    $wheresql = " WHERE id != 0 AND u.lastlogin IS NOT NULL";
    $where = array();
    if ($institution) {
        if ($institution == 'mahara') {
            $fromsql .= " LEFT JOIN {usr_institution} ui ON (ui.usr = u.id AND ui.institution IS NULL)";
        }
        else {
            $fromsql .= " JOIN {usr_institution} ui ON (ui.usr = u.id AND ui.institution = ?)";
            $where = array($institution);
        }
    }
    if ($users) {
        $wheresql .= " AND u.id IN (" . join(',', array_map('db_quote', array_values((array)$users))) . ")";
    }
    if ($start) {
        $wheresql .= " AND EXISTS(SELECT usr FROM {event_log} el
                                  WHERE el.usr = u.id
                                  AND el.event = 'login'
                                  AND el.ctime >= DATE(?) AND el.ctime <= DATE(?)
                                  LIMIT 1)";
        $where[] = $start;
        $where[] = $end;
    }
    $count = 0;
    $usrids = get_records_sql_assoc("SELECT id, username " . $fromsql . $wheresql, $where);
    if (!empty($usrids)) {
        $usrids = array_keys($usrids);
        $count = count($usrids);
    }

    $pagination = build_pagination(array(
        'id' => 'stats_pagination',
        'url' => $urllink,
        'jsonscript' => 'admin/users/statistics.json.php',
        'datatable' => 'statistics_table',
        'count' => $count,
        'limit' => $limit,
        'offset' => $offset,
        'setlimit' => true,
        'extradata' => $extra,
    ));

    $result = array(
        'count'         => $count,
        'tablerows'     => '',
        'pagination'    => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
    );

    $result['settings']['start'] = ($start) ? $start : null;
    $result['settings']['end'] = $end;
    $result['settings']['users'] = !empty($users) ? count($users) : 0;

    if ($count < 1) {
        return $result;
    }

    $sorttype = !empty($extra['sort']) ? $extra['sort'] : '';
    $sortorder = "1";
    $sortdesc = !empty($extra['sortdesc']) ? 'desc' : 'asc';
    $sortdirection = '';
    $sortname = null;

    switch ($sorttype) {
        case "lastlogin":
            $sortdirection = array('LastActivity' => $sortdesc);
            break;
        case "lastactivity":
            $sortdirection = array('LastLogin' => $sortdesc);
            break;
        case "actions":
            $sortdirection = array('EventTypeCount' => $sortdesc);
            break;
        case "logins":
            $sortorder = "doc.event.value == 'login' ? 1 : 0";
            $sortdirection = array('EventTypeCount' => $sortdesc);
            break;
        case "groups":
            $sortorder = "doc.event.value == 'creategroup' ? 1 : 0";
            $sortdirection = array('EventTypeCount' => $sortdesc);
            break;
        case "collections":
            $sortorder = "doc.event.value == 'createcollection' ? 1 : 0";
            $sortdirection = array('EventTypeCount' => $sortdesc);
            break;
        case "pages":
            $sortorder = "doc.event.value == 'createview' ? 1 : 0";
            $sortdirection = array('EventTypeCount' => $sortdesc);
            break;
        case "artefacts":
            $sortorder = "doc.event.value == 'saveartefact' ? 1 : 0";
            $sortdirection = array('EventTypeCount' => $sortdesc);
            break;
        case "displayname":
            $sortname = 'preferredname';
            break;
        case "username":
            $sortname = 'username';
            break;
        case "lastname":
            $sortname = 'lastname';
            break;
        case "firstname":
        default:
            $sortname = 'firstname';
    }

    $result['settings']['start'] = $start;

    // Add in the elasticsearch data if needed
    $aggmap = array();
    if (get_config('searchplugin') == 'elasticsearch') {
        safe_require('search', 'elasticsearch');
        $options = array(
            'query' => array(
                'terms' => array(
                    'usr' => $usrids
                ),
            ),
            'range' => array(
                'range' => array(
                    'ctime' => array(
                        'gte' => $result['settings']['start'] . ' 00:00:00',
                        'lte' => $result['settings']['end'] . ' 23:59:59'
                    )
                )
            ),
            'aggs' => array(
                'UsrId' => array(
                    'terms' => array(
                        'field' => 'usr',
                        'order' => $sortdirection,
                        'size' => $count,
                     ),
                     'aggs' => array(
                        'EventType' => array(
                            'terms' => array(
                                'field' => 'event',
                                'min_doc_count' => 0,
                            ),
                        ),
                        'EventTypeCount' => array(
                            'sum' => array(
                                'script' => array(
                                    'inline' => $sortorder,
                                ),
                            ),
                        ),
                        'LastLogin' => array(
                            'max' => array(
                                'script' => array(
                                    'inline' => "doc.ctime.value",
                                ),
                            ),
                        ),
                        'LastActivity' => array(
                            'max' => array(
                                'script' => array(
                                    'inline' => "doc.id.value",
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );
        if (empty($sortdirection)) { unset($options['aggs']['UsrId']['terms']['order']); }
        $aggregates = PluginSearchElasticsearch::search_events($options, 0, 0);
        if ($aggregates['totalresults'] > 0) {
            foreach ($aggregates['aggregations']['UsrId']['buckets'] as $k => $usr) {
                $user = new User();
                $user->find_by_id($usr['key']);
                $aggregates['aggregations']['UsrId']['buckets'][$k]['firstname'] = $user->get('firstname');
                $aggregates['aggregations']['UsrId']['buckets'][$k]['lastname'] = $user->get('lastname');
                $aggregates['aggregations']['UsrId']['buckets'][$k]['username'] = $user->get('username');
                $aggregates['aggregations']['UsrId']['buckets'][$k]['preferredname'] = $user->get('preferredname');
            }
            if (!empty($sortname)) {
                usort($aggregates['aggregations']['UsrId']['buckets'], function ($a, $b) use ($sortname) {
                    return strnatcasecmp($a[$sortname], $b[$sortname]);
                });
                if ($sortdesc == 'desc') {
                    $aggregates['aggregations']['UsrId']['buckets'] = array_reverse($aggregates['aggregations']['UsrId']['buckets']);
                }
            }
            ElasticsearchType_event_log::process_aggregations($aggmap, $aggregates['aggregations'], true, array('UsrId', 'EventType'));
        }
    }

    $data = array();
    $timezone = new DateTimeZone(date_default_timezone_get()); // get timezone we are in
    $offsettime = $timezone->getOffset(new DateTime("now")); // work out offset in seconds
    if ($aggregates['totalresults'] > 0) {
        foreach ($aggregates['aggregations']['UsrId']['buckets'] as $item) {
            $obj = new stdClass();
            $obj->id = $item['key'];
            $obj->firstname = $item['firstname'];
            $obj->lastname = $item['lastname'];
            $obj->username = $item['username'];
            $obj->displayname = $item['preferredname'];
            $obj->ctime = null;
            $obj->artefacts = !empty($aggmap[$item['key'] . '|saveartefact']) ? $aggmap[$item['key'] . '|saveartefact'] : 0;
            $obj->pages = !empty($aggmap[$item['key'] . '|createview']) ? $aggmap[$item['key'] . '|createview'] : 0;
            $obj->collections = !empty($aggmap[$item['key'] . '|createcollection']) ? $aggmap[$item['key'] . '|createcollection'] : 0;
            $obj->groups = !empty($aggmap[$item['key'] . '|creategroup']) ? $aggmap[$item['key'] . '|creategroup'] : 0;
            $obj->logins = !empty($aggmap[$item['key'] . '|login']) ? $aggmap[$item['key'] . '|login'] : 0;
            $lastactivity = get_field('event_log', 'event', 'id', $item['LastActivity']['value']);
            $obj->lastactivity = ($lastactivity) ? get_string($lastactivity, 'statistics') : '';
            $obj->profileurl = profile_url($item['key']);
            $date = $item['LastLogin']['value'] / 1000; // convert from UTC milliseconds
            if ($offsettime < 0) {
                $date += $offsettime;
            }
            if ($offsettime > 0) {
                $date -= $offsettime;
            }
            $obj->lastlogin = $item['LastLogin']['value'] ? date('d F Y, H:i a', $date) : '';
            $obj->actions = $item['doc_count'];
            $data[] = $obj;
        }
    }
    if (empty($extra['csvdownload'])) {
        $data = array_slice($data, $offset, $limit, true);
    }

    if (!empty($extra['csvdownload'])) {
        $csvfields = array('firstname', 'lastname', 'displayname', 'username',
                           'artefacts', 'pages', 'collections', 'groups', 'logins',
                          'actions', 'lastlogin', 'lastactivity');
        $USER->set_download_file(generate_csv($data, $csvfields), $institution . 'useractivitystatistics.csv', 'text/csv');
    }
    $result['csv'] = true;
    $columnkeys = array();
    foreach ($extra['columns'] as $column) {
        $columnkeys[$column] = 1;
    }

    $smarty = smarty_core();
    $smarty->assign('data', $data);
    $smarty->assign('columns', $columnkeys);
    $smarty->assign('offset', $offset);

    $result['tablerows'] = $smarty->fetch('admin/users/useractivitystats.tpl');

    return $result;
}

function collaboration_statistics_headers($extra, $urllink) {
    return array(
        array('id' => 'rownum', 'name' => '#'),
        array(
              'id' => 'date', 'required' => true,
              'name' => get_string('date'),
              'class' => format_class($extra, 'date'),
              'link' => format_goto($urllink . '&sort=date', $extra, array('sort'), 'date')
        ),
        array(
              'id' => 'comments', 'required' => true,
              'name' => get_string('Comments', 'artefact.comment'),
              'class' => format_class($extra, 'comments'),
              'link' => format_goto($urllink . '&sort=comments', $extra, array('sort'), 'comments'),
              'helplink' => get_help_icon('core', 'reports', 'collaboration', 'comments')
        ),
        array(
              'id' => 'annotations',
              'name' => get_string('Annotations', 'artefact.annotation'),
              'class' => format_class($extra, 'annotations'),
              'link' => format_goto($urllink . '&sort=annotations', $extra, array('sort'), 'annotations'),
              'helplink' => get_help_icon('core', 'reports', 'collaboration', 'annotations')
        ),
        array(
              'id' => 'usershare', 'required' => true,
              'name' => get_string('usershare', 'statistics'),
              'class' => format_class($extra, 'usershare'),
              'link' => format_goto($urllink . '&sort=usershare', $extra, array('sort'), 'usershare'),
              'helplink' => get_help_icon('core', 'reports', 'collaboration', 'users')
        ),
        array(
              'id' => 'groupshare', 'required' => true,
              'name' => get_string('groupshare', 'statistics'),
              'class' => format_class($extra, 'groupshare'),
              'link' => format_goto($urllink . '&sort=groupshare', $extra, array('sort'), 'groupshare'),
              'helplink' => get_help_icon('core', 'reports', 'collaboration', 'groups')
        ),
        array(
              'id' => 'institutionshare', 'required' => true,
              'name' => get_string('institutionshare', 'statistics'),
              'class' => format_class($extra, 'institutionshare'),
              'link' => format_goto($urllink . '&sort=institutionshare', $extra, array('sort'), 'institutionshare'),
              'helplink' => get_help_icon('core', 'reports', 'collaboration', 'institutions')
        ),
        array(
              'id' => 'loggedinshare', 'required' => true,
              'name' => get_string('loggedinshare', 'statistics'),
              'class' => format_class($extra, 'loggedinshare'),
              'link' => format_goto($urllink . '&sort=loggedinshare', $extra, array('sort'), 'loggedinshare'),
              'helplink' => get_help_icon('core', 'reports', 'collaboration', 'registered')
        ),
        array(
              'id' => 'publicshare', 'required' => true,
              'name' => get_string('publicshare', 'statistics'),
              'class' => format_class($extra, 'publicshare'),
              'link' => format_goto($urllink . '&sort=publicshare', $extra, array('sort'), 'publicshare'),
              'helplink' => get_help_icon('core', 'reports', 'collaboration', 'public')
        ),
        array(
              'id' => 'secretshare', 'required' => true,
              'name' => get_string('secretshare', 'statistics'),
              'class' => format_class($extra, 'secretshare'),
              'link' => format_goto($urllink . '&sort=secretshare', $extra, array('sort'), 'secretshare'),
              'helplink' => get_help_icon('core', 'reports', 'collaboration', 'secreturls')
        ),
        array(
              'id' => 'friendshare',
              'name' => get_string('friendshare', 'statistics'),
              'class' => format_class($extra, 'friendshare'),
              'link' => format_goto($urllink . '&sort=friendshare', $extra, array('sort'), 'friendshare'),
              'helplink' => get_help_icon('core', 'reports', 'collaboration', 'friends')
        ),
    );
}

function collaboration_statistics($limit, $offset, $extra, $institution = null) {
    userhasaccess($institution, 'collaboration');
    if (!get_config('eventlogenhancedsearch')) {
        return array('notvalid_errorstring' => get_string('needadvancedanalytics', 'statistics'));
    }
    $data = array();
    $urllink = get_config('wwwroot') . 'admin/users/statistics.php?type=users&subtype=collaboration';
    if ($institution) {
        $urllink .= '&institution=' . $institution;
    }
    $data['tableheadings'] = collaboration_statistics_headers($extra, $urllink);

    $activeheadings = get_active_columns($data, $extra);
    $extra['columns'] = array_keys($activeheadings);

    $data['table'] = collaboration_stats_table($limit, $offset, $extra, $institution, $urllink);
    $data['table']['activeheadings'] = $activeheadings;

    $data['summary'] = $data['table']['count'] == 0 ? get_string('nostats', 'admin') : null;

    return $data;
}

function collaboration_stats_table($limit, $offset, $extra, $institution, $urllink) {
    global $USER;

    $start = !empty($extra['start']) ? $extra['start'] : date('Y-m-d', strtotime("-1 months"));
    $end = !empty($extra['end']) ? $extra['end'] : date('Y-m-d', strtotime('now'));
    $from = strtotime($start);
    $to = strtotime($end);
    $daterange = array();
    while ($from < $to) {
        $daterange[date("Y_W", $from)] = date('Y-m-d', $from);
        $from = $from + (7 * 24 * 60 * 60); // Break down the range by weeks
    }
    $daterange[date("Y_W", $to)] = date('Y-m-d', $to);

    $count = count($daterange);

    $pagination = build_pagination(array(
        'id' => 'stats_pagination',
        'url' => $urllink,
        'jsonscript' => 'admin/users/statistics.json.php',
        'datatable' => 'statistics_table',
        'count' => $count,
        'limit' => $limit,
        'offset' => $offset,
        'setlimit' => true,
        'extradata' => $extra,
    ));

    $result = array(
        'count'         => $count,
        'tablerows'     => '',
        'pagination'    => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
    );

    $result['settings']['start'] = ($start) ? $start : null;
    $result['settings']['end'] = $end;
    if ($count < 1) {
        return $result;
    }

    $sorttype = !empty($extra['sort']) ? $extra['sort'] : '';
    $aggmap = array();
    if (get_config('searchplugin') == 'elasticsearch') {
        safe_require('search', 'elasticsearch');
        $options = array(
            'range' => array(
                'range' => array(
                    'ctime' => array(
                        'gte' => $start . ' 00:00:00',
                        'lt' => $end . ' 00:00:00'
                    )
                )
            ),
            'sort' => array(
                'ctime' => 'desc'
            ),
            'aggs' => array(
                'YearWeek' => array(
                    'terms' => array(
                        'field' => 'yearweek',
                    ),
                    'aggs' => array(
                        'EventType' => array(
                            'terms' => array(
                                'field' => 'event',
                            ),
                            'aggs' => array(
                                'ResourceType' => array(
                                    'terms' => array(
                                        'field' => 'resourcetype',
                                    ),
                                    'aggs' => array(
                                        'ParentResourceType' => array(
                                            'terms' => array(
                                                'field' => 'parentresourcetype',
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );
        if ($institution) {
            // restrict results to users from the institution
            if ($institution == 'mahara') {
                $usrids = get_records_sql_assoc("SELECT u.id, u.username FROM {usr} u
                                                 LEFT JOIN {usr_institution} ui ON ui.usr = u.id
                                                 JOIN {event_log} el ON el.usr = u.id
                                                 WHERE ui.institution IS NULL
                                                 AND el.event = 'login'
                                                 AND el.ctime >= DATE(?) AND el.ctime <= DATE(?)
                                                 GROUP BY u.id", array($start, $end));
            }
            else {
                $usrids = get_records_sql_assoc("SELECT u.id, u.username FROM {usr} u
                                                 JOIN {usr_institution} ui ON ui.usr = u.id
                                                 JOIN {event_log} el ON el.usr = u.id
                                                 WHERE ui.institution = ?
                                                 AND el.event = 'login'
                                                 AND el.ctime >= DATE(?) AND el.ctime <= DATE(?)
                                                 GROUP BY u.id", array($institution, $start, $end));
            }
            if (!empty($usrids)) {
                $usrids = array_keys($usrids);
                $options['query'] = array(
                    'terms' => array(
                        'usr' => $usrids
                    ),
                );
            }
            else {
                $result['pagination'] = null;
                return $result;
            }
        }
        $aggregates = PluginSearchElasticsearch::search_events($options, 0, 0);
        if ($aggregates['totalresults'] > 0) {
            ElasticsearchType_event_log::process_aggregations($aggmap, $aggregates['aggregations'], true, array('YearWeek', 'EventType', 'ResourceType', 'ParentResourceType'));
        }
    }
    $result['settings']['start'] = ($start) ? $start : min($daterange);

    $rawdata = array();
    // if sorting by date
    if ($sorttype == 'date' && !empty($extra['sortdesc'])) {
        $daterange = array_reverse($daterange);
    }

    foreach ($daterange as $k => $v) {
        list ($year, $week) = explode('_', $k);
        $obj = new stdClass();
        $obj->date = get_string('collaborationdate', 'statistics', format_date(strtotime($year . "W" . $week . '1'), 'strfdaymonthyearshort'));
        $obj->yearweek = $k;
        $obj->comments = !empty($aggmap[$k . '|saveartefact|comment']) ? $aggmap[$k . '|saveartefact|comment'] : 0;
        $obj->annotations = !empty($aggmap[$k . '|saveartefact|annotation']) ? $aggmap[$k . '|saveartefact|annotation'] : 0;
        $obj->usershare = !empty($aggmap[$k . '|updateviewaccess|user']) ? $aggmap[$k . '|updateviewaccess|user'] : 0;
        $obj->groupshare = !empty($aggmap[$k . '|updateviewaccess|group']) ? $aggmap[$k . '|updateviewaccess|group'] : 0;
        $obj->institutionshare = !empty($aggmap[$k . '|updateviewaccess|institution']) ? $aggmap[$k . '|updateviewaccess|institution'] : 0;
        $obj->loggedinshare = !empty($aggmap[$k . '|updateviewaccess|loggedin']) ? $aggmap[$k . '|updateviewaccess|loggedin'] : 0;
        $obj->publicshare = !empty($aggmap[$k . '|updateviewaccess|public']) ? $aggmap[$k . '|updateviewaccess|public'] : 0;
        $obj->secretshare = !empty($aggmap[$k . '|updateviewaccess|token']) ? $aggmap[$k . '|updateviewaccess|token'] : 0;
        $obj->friendshare = !empty($aggmap[$k . '|updateviewaccess|friends']) ? $aggmap[$k . '|updateviewaccess|friends'] : 0;

        $rawdata[$k] = $obj;
    }

    // if sorting by something other than date
    if (!empty($sorttype) && $sorttype != 'date') {
        uasort ($rawdata, function ($i, $j) use ($sorttype) {
            $a = $i->{$sorttype};
            $b = $j->{$sorttype};
            if ($a == $b) {
                return 0;
            }
            else if ($a > $b) {
                return 1;
            }
            else {
                return -1;
            }
        });
        if (!empty($extra['sortdesc'])) {
           $rawdata = array_reverse($rawdata);
        }
    }

    // Now do the limit / offset for pagination
    if (empty($extra['csvdownload'])) {
        $data = array_slice($rawdata, $offset, $limit, true);
    }
    else {
        $data = $rawdata;
        $csvfields = array('date', 'comments', 'annotations', 'usershare', 'groupshare',
                           'institutionshare', 'loggedinshare', 'publicshare', 'secretshare',
                           'friendshare');
        $USER->set_download_file(generate_csv($data, $csvfields), $institution . 'collaborationstatistics.csv', 'text/csv');
    }
    $result['csv'] = true;
    $columnkeys = array();
    foreach ($extra['columns'] as $column) {
        $columnkeys[$column] = 1;
    }

    $smarty = smarty_core();
    $smarty->assign('data', $data);
    $smarty->assign('columns', $columnkeys);
    $smarty->assign('offset', $offset);

    $result['tablerows'] = $smarty->fetch('admin/users/collaborationstats.tpl');

    return $result;
}

function users_statistics_headers($extra, $urllink) {
    return array(
        array('id' => 'rownum', 'name' => '#'),
        array(
              'id' => 'date', 'required' => true,
              'name' => get_string('date'),
              'class' => format_class($extra, 'date'),
              'link' => format_goto($urllink . '&sort=date', $extra, array('sort'), 'date')
        ),
        array(
              'id' => 'loggedin', 'required' => true,
              'name' => get_string('Loggedin', 'admin'),
              'class' => format_class($extra, 'loggedin'),
              'link' => format_goto($urllink . '&sort=loggedin', $extra, array('sort'), 'loggedin'),
              'helplink' => get_help_icon('core', 'reports', 'users', 'loggedin')
        ),
        array(
              'id' => 'created', 'required' => true,
              'name' => get_string('Created'),
              'class' => format_class($extra, 'created'),
              'helplink' => get_help_icon('core', 'reports', 'users', 'created'),
//              'link' => format_goto($urllink . '&sort=created', $extra, array('sort'), 'created')
        ),
        array(
              'id' => 'total', 'required' => true,
              'name' => get_string('Total'),
              'class' => format_class($extra, 'total'),
              'link' => format_goto($urllink . '&sort=total', $extra, array('sort'), 'total'),
              'helplink' => get_help_icon('core', 'reports', 'users', 'total')
        ),
    );
}

function user_statistics($limit, $offset, $extra) {
    $data = array();
    $urllink = get_config('wwwroot') . 'admin/users/statistics.php?type=users&subtype=users';
    $data['tableheadings'] = users_statistics_headers($extra, $urllink);

    $activeheadings = get_active_columns($data, $extra);
    $extra['columns'] = array_keys($activeheadings);

    $data['table'] = user_stats_table($limit, $offset, $extra);
    $data['table']['activeheadings'] = $activeheadings;

    $data['summary'] = $data['table']['count'] == 0 ? get_string('nostats', 'admin') : null;

    return $data;
}

function user_stats_table($limit, $offset, $extra) {
    global $USER;

    $start = !empty($extra['start']) ? $extra['start'] : date('Y-m-d', strtotime("-1 months"));
    $end = !empty($extra['end']) ? $extra['end'] : date('Y-m-d', strtotime('now'));

    if ($start) {
        $where = "type = ? AND ctime >= DATE(?) AND ctime <= DATE(?)";
        $count = count_records_select('site_data', $where, array('user-count-daily', $start, $end));
    }
    else {
        $count = count_records('site_data', 'type', 'user-count-daily');
    }

    $pagination = build_pagination(array(
        'id' => 'stats_pagination',
        'url' => get_config('wwwroot') . 'admin/users/statistics.php?type=users',
        'jsonscript' => 'admin/users/statistics.json.php',
        'datatable' => 'statistics_table',
        'count' => $count,
        'limit' => $limit,
        'offset' => $offset,
        'setlimit' => true,
        'extradata' => $extra,
    ));

    $result = array(
        'count'         => $count,
        'tablerows'     => '',
        'pagination'    => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
    );

    $result['settings']['start'] = ($start) ? $start : null;
    $result['settings']['end'] = $end;
    if ($count < 1) {
        return $result;
    }

    $day = is_postgres() ? "to_date(t.ctime::text, 'YYYY-MM-DD')" : 'DATE(t.ctime)'; // TODO: make work on other databases?

    $sorttype = !empty($extra['sort']) ? $extra['sort'] : '';
    if (is_mysql()) {
        $cast = " (value + 0) ";
    }
    else {
        $cast = " CAST(value AS INTEGER) ";
    }
    switch ($sorttype) {
        case "total":
            $rangesql = " type = 'user-count-daily'";
            $ordersql = " ORDER BY type = 'loggedin-users-daily' DESC";
            $ordersql .= ", " . $cast . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC');
            $ordersql .= ", ctime " . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC');
            break;
        case "loggedin":
            $rangesql = " type = 'loggedin-users-daily'";
            $ordersql = " ORDER BY type = 'loggedin-users-daily' DESC";
            $ordersql .= ", " . $cast . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC');
            $ordersql .= ", ctime " . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC');
            break;
        case "date":
        default:
            $rangesql = " type = 'user-count-daily'";
            $ordersql = " ORDER BY type = 'user-count-daily' " . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC');
            $ordersql .= ", ctime " . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC');
    }

    // Get the min/max daterange within supplied date range
    $rangewhere = array();
    if ($start) {
        $rangesql .= " AND ctime >= DATE(?) AND ctime <= DATE(?)";
        $rangewhere[] = $start;
        $rangewhere[] = $end;
    }

    $sql = "SELECT $day AS date
            FROM {site_data} t
            WHERE " . $rangesql . $ordersql;
    if (empty($extra['csvdownload'])) {
            $sql .= " LIMIT $limit OFFSET $offset";
    }
    $dateranges = get_records_sql_array($sql, $rangewhere);
    $daterange = array();
    foreach ($dateranges as $d) {
        $daterange[] = $d->date;
    }
    $result['settings']['start'] = ($start) ? $start : min($daterange);

    $dayinterval = is_postgres() ? "'1 day'" : '1 day';

    $day = is_postgres() ? "to_date(ctime::text, 'YYYY-MM-DD')" : 'DATE(ctime)';

    $usersql = "SELECT ctime, type, \"value\", $day AS date
        FROM {site_data}
        WHERE type IN (?,?) AND $day IN (" . join(',', array_map('db_quote', $daterange)) . ")" . $ordersql;
    $userdata = get_records_sql_array($usersql, array('user-count-daily', 'loggedin-users-daily'));

    $userscreatedsql = "SELECT $day AS cdate, COUNT(id) AS users
        FROM {usr}
        WHERE ctime IS NOT NULL AND $day IN (" . join(',', array_map('db_quote', $daterange)) . ")
        GROUP BY cdate";

    $userscreated = get_records_sql_array($userscreatedsql, array());

    $data = array_fill_keys($daterange, array());

    if ($userdata) {
        foreach ($userdata as &$r) {
            if ($r->type == 'user-count-daily') {
                $data[$r->date]['date'] = $r->date;
                $data[$r->date]['total'] = $r->value;
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
    if (!empty($extra['csvdownload'])) {
        $csvfields = array('date', 'loggedin', 'created', 'total');
        $USER->set_download_file(generate_csv($data, $csvfields), 'userstatistics.csv', 'text/csv');
    }
    $result['csv'] = true;
    $columnkeys = array();
    foreach ($extra['columns'] as $column) {
        $columnkeys[$column] = 1;
    }

    $smarty = smarty_core();
    $smarty->assign('data', $data);
    $smarty->assign('columns', $columnkeys);
    $smarty->assign('offset', $offset);
    $result['tablerows'] = $smarty->fetch('admin/userstats.tpl');

    return $result;
}

function institution_user_statistics($limit, $offset, &$institutiondata, $extra) {
    userhasaccess($institutiondata['institution'], 'users');
    $data = array();
    $data['institution'] = $institutiondata['institution'];
    $urllink = get_config('wwwroot') . 'admin/users/statistics.php?institution=' . $data['institution'] . '&type=users&subtype=users';

    $data['tableheadings'] = users_statistics_headers($extra, $urllink);

    $activeheadings = get_active_columns($data, $extra);
    $extra['columns'] = array_keys($activeheadings);

    $data['table'] = institution_user_stats_table($limit, $offset, $institutiondata, $extra);
    $data['table']['activeheadings'] = $activeheadings;

    $data['summary'] = $data['table']['count'] == 0 ? get_string('nostats', 'admin') : null;

    return $data;
}

function institution_user_stats_table($limit, $offset, &$institutiondata, $extra) {
    global $USER;

    $start = !empty($extra['start']) ? $extra['start'] : null;
    $end = !empty($extra['end']) ? $extra['end'] : date('Y-m-d', strtotime('now'));

    if ($start) {
        $where = "type = ? AND institution = ? AND ctime >= DATE(?) AND ctime <= DATE(?)";
        $count = count_records_select('institution_data', $where, array('user-count-daily', $institutiondata['name'], $start, $end));
    }
    else {
        $count = count_records('institution_data', 'type', 'user-count-daily', 'institution', $institutiondata['name']);
    }

    $pagination = build_pagination(array(
        'id' => 'stats_pagination',
        'url' => get_config('wwwroot') . 'admin/users/statistics.php?institution=' . $institutiondata['name'] . '&type=users',
        'jsonscript' => 'admin/users/statistics.json.php',
        'datatable' => 'statistics_table',
        'count' => $count,
        'limit' => $limit,
        'offset' => $offset,
        'setlimit' => true,
        'extradata' => $extra,
    ));

    $result = array(
        'count'         => $count,
        'tablerows'     => '',
        'pagination'    => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
    );
    $result['settings']['start'] = ($start) ? $start : null;
    $result['settings']['end'] = $end;
    if ($count < 1) {
        return $result;
    }

    $day = is_postgres() ? "to_date(t.ctime::text, 'YYYY-MM-DD')" : 'DATE(t.ctime)'; // TODO: make work on other databases?

    $sorttype = !empty($extra['sort']) ? $extra['sort'] : '';
    if (is_mysql()) {
        $cast = " (value + 0) ";
    }
    else {
        $cast = " CAST(value AS INTEGER) ";
    }
    switch ($sorttype) {
        case "total":
            $rangesql = " type = 'user-count-daily'";
            $ordersql = " ORDER BY type = 'loggedin-users-daily' DESC";
            $ordersql .= ", " . $cast . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC');
            $ordersql .= ", ctime " . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC');
            break;
        case "loggedin":
            $rangesql = " type = 'loggedin-users-daily'";
            $ordersql = " ORDER BY type = 'loggedin-users-daily' DESC";
            $ordersql .= ", " . $cast . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC');
            $ordersql .= ", ctime " . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC');
            break;
        case "date":
        default:
            $rangesql = " type = 'user-count-daily'";
            $ordersql = " ORDER BY type = 'user-count-daily' " . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC');
            $ordersql .= ", ctime " . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC');
    }

    // Get the min/max daterange within supplied date range
    $rangesql .= " AND institution = ?";
    $rangewhere = array($institutiondata['name']);
    if ($start) {
        $rangesql .= " AND ctime >= DATE(?) AND ctime <= DATE(?)";
        $rangewhere[] = $start;
        $rangewhere[] = $end;
    }

    $sql = "SELECT $day AS date
            FROM {institution_data} t
            WHERE " . $rangesql . $ordersql;
    if (empty($extra['csvdownload'])) {
        $sql .= " LIMIT $limit OFFSET $offset";
    }
    $dateranges = get_records_sql_array($sql, $rangewhere);
    $daterange = array();
    foreach ($dateranges as $d) {
        $daterange[] = $d->date;
    }
    $result['settings']['start'] = ($start) ? $start : min($daterange);

    $dayinterval = is_postgres() ? "'1 day'" : '1 day';

    $day = is_postgres() ? "to_date(ctime::text, 'YYYY-MM-DD')" : 'DATE(ctime)';

    $usersql = "SELECT ctime, type, \"value\", $day AS date
        FROM {institution_data}
        WHERE type IN (?,?) AND institution = ? AND $day IN (" . join(',', array_map('db_quote', $daterange)) . ")" . $ordersql;
    $userdata = get_records_sql_array($usersql, array('user-count-daily', 'loggedin-users-daily', $institutiondata['name']));

    $userscreatedsql = "SELECT $day as cdate, COUNT(usr) AS users
        FROM {usr_institution}
        WHERE institution = ?
        AND ctime IS NOT NULL AND $day IN (" . join(',', array_map('db_quote', $daterange)) . ")
        GROUP BY cdate";

    $userscreated = get_records_sql_array($userscreatedsql, array($institutiondata['name']));

    $data = array_fill_keys($daterange, array());

    if ($userdata) {
        foreach ($userdata as &$r) {
            if ($r->type == 'user-count-daily') {
                $data[$r->date]['date'] = $r->date;
                $data[$r->date]['total'] = $r->value;
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
    if (!empty($extra['csvdownload'])) {
        $csvfields = array('date', 'loggedin', 'created', 'total');
        $USER->set_download_file(generate_csv($data, $csvfields), $institutiondata['name'] . 'userstatistics.csv', 'text/csv');
    }
    $result['csv'] = true;

    $columnkeys = array();
    foreach ($extra['columns'] as $column) {
        $columnkeys[$column] = 1;
    }

    $smarty = smarty_core();
    $smarty->assign('data', $data);
    $smarty->assign('columns', $columnkeys);
    $smarty->assign('offset', $offset);
    $result['tablerows'] = $smarty->fetch('admin/userstats.tpl');

    return $result;
}


function user_institution_graph($type = null) {
    // Draw a pie graph showing the number of users in each institution
    require_once(get_config('libroot') . 'institution.php');

    $institutions = Institution::count_members(false, true);
    if (is_array($institutions) && count($institutions) > 1) {
        $dataarray = array();
        foreach ($institutions as &$i) {
            if ($i->members) {
                $dataarray[$i->displayname] = $i->members;
            }
        }
        arsort($dataarray);
        // Truncate to avoid trying to fit too many results onto graph
        $newdataarray = array_slice($dataarray, 0, 9, true);
        // And place the rest as an 'All Other' piece
        $others = array_diff_assoc($dataarray, $newdataarray);
        if (!empty($others)) {
            $newdataarray[get_string('allothers', 'admin')] = 0;
            foreach ($others as $o) {
                $newdataarray[get_string('allothers', 'admin')] += $o;
            }
        }
        $data['graph'] = ($type) ? $type : 'pie';
        $data['graph_function_name'] = 'user_institution_graph';
        $data['title'] = get_string('institutionmembers','admin');
        $data['labels'] = array_keys($newdataarray);
        $data['data'] = $newdataarray;

        return $data;
    }
}

function groups_statistics_headers($extra, $urllink) {
    return array(
        array('id' => 'rownum', 'name' => '#'),
        array('id' => 'id',
              'name' => get_string('ID', 'admin'),
              'class' => format_class($extra, 'id'),
              'link' => format_goto($urllink . '&sort=id', $extra, array('sort'), 'id')
        ),
        array('id' => 'group', 'required' => true,
              'name' => get_string('Group', 'group'),
              'class' => format_class($extra, 'group'),
              'link' => format_goto($urllink . '&sort=group', $extra, array('sort'), 'group'),
              'helplink' => get_help_icon('core', 'reports', 'groups', 'group')
        ),
        array('id' => 'members', 'required' => true,
              'name' => get_string('Members', 'group'),
              'class' => format_class($extra, 'members'),
              'link' => format_goto($urllink . '&sort=members', $extra, array('sort'), 'members'),
              'helplink' => get_help_icon('core', 'reports', 'groups', 'members')
        ),
        array('id' => 'views', 'required' => true,
              'name' => get_string('Views', 'view'),
              'class' => format_class($extra, 'views'),
              'link' => format_goto($urllink . '&sort=views', $extra, array('sort'), 'views'),
              'helplink' => get_help_icon('core', 'reports', 'groups', 'views')
        ),
        array('id' => 'groupcomments',
              'name' => get_string('groupcomments', 'statistics'),
              'class' => format_class($extra, 'groupcomments'),
              'link' => format_goto($urllink . '&sort=groupcomments', $extra, array('sort'), 'groupcomments'),
              'helplink' => get_help_icon('core', 'reports', 'groups', 'groupcomments')
        ),
        array('id' => 'sharedviews',
              'name' => get_string('sharedviews', 'view'),
              'class' => format_class($extra, 'sharedviews'),
              'link' => format_goto($urllink . '&sort=sharedviews', $extra, array('sort'), 'sharedviews'),
              'helplink' => get_help_icon('core', 'reports', 'groups', 'sharedviews')
        ),
        array('id' => 'sharedcomments',
              'name' => get_string('sharedcomments', 'statistics'),
              'class' => format_class($extra, 'sharedcomments'),
              'link' => format_goto($urllink . '&sort=sharedcomments', $extra, array('sort'), 'sharedcomments'),
              'helplink' => get_help_icon('core', 'reports', 'groups', 'sharedcomments')
        ),
        array('id' => 'forums', 'required' => true,
              'name' => get_string('nameplural', 'interaction.forum'),
              'class' => format_class($extra, 'forums'),
              'link' => format_goto($urllink . '&sort=forums', $extra, array('sort'), 'forums'),
              'helplink' => get_help_icon('core', 'reports', 'groups', 'forums')
        ),
        array('id' => 'posts', 'required' => true,
              'name' => get_string('Posts', 'interaction.forum'),
              'class' => format_class($extra, 'posts'),
              'link' => format_goto($urllink . '&sort=posts', $extra, array('sort'), 'posts'),
              'helplink' => get_help_icon('core', 'reports', 'groups', 'posts')
        ),
    );
}

function group_statistics($limit, $offset, $extra) {
    $data = array();
    $urllink = get_config('wwwroot') . 'admin/users/statistics.php?type=groups&subtype=groups';
    $data['tableheadings'] = groups_statistics_headers($extra, $urllink);

    $activeheadings = get_active_columns($data, $extra);
    $extra['columns'] = array_keys($activeheadings);

    $data['table'] = group_stats_table($limit, $offset, $extra);
    $data['table']['activeheadings'] = $activeheadings;

    $data['summary'] = $data['table']['count'] == 0 ? get_string('nostats', 'admin') : null;

    return $data;
}

function group_stats_table($limit, $offset, $extra) {
    global $USER;

    $start = !empty($extra['start']) ? $extra['start'] : date('Y-m-d', strtotime("-1 months"));
    $end = !empty($extra['end']) ? $extra['end'] : date('Y-m-d', strtotime('now'));
    if ($start) {
        $where = "ctime >= DATE(?) AND ctime <= DATE(?)";
        $count = count_records_select('group', $where . " AND deleted = 0", array($start, $end));
    }
    else {
        $count = count_records('group', 'deleted', 0);
    }

    $pagination = build_pagination(array(
        'id' => 'stats_pagination',
        'url' => get_config('wwwroot') . 'admin/users/statistics.php?type=groups',
        'jsonscript' => 'admin/users/statistics.json.php',
        'datatable' => 'statistics_table',
        'count' => $count,
        'limit' => $limit,
        'offset' => $offset,
        'setlimit' => true,
        'extradata' => $extra,
    ));

    $result = array(
        'count'         => $count,
        'tablerows'     => '',
        'pagination'    => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
    );

    $result['settings']['start'] = ($start) ? $start : null;
    $result['settings']['end'] = $end;
    if ($count < 1) {
        return $result;
    }

    $sorttype = !empty($extra['sort']) ? $extra['sort'] : '';
    $sortdesc = !empty($extra['sortdesc']) ? 'desc' : 'asc';
    $sorttypeaggmap = '';
    switch ($sorttype) {
        case "groupcomments":
            $sortdirection = array('EventTypeCount' => $sortdesc);
            $sortorder = "(doc.event.value == 'saveartefact' && doc.resourcetype.value == 'comment' && doc.ownertype.value == 'group') ? 1 : 0";
            $sorttypeaggmap = '|saveartefact|comment|group';
            break;
        case "sharedviews":
            $sortdirection = array('EventTypeCount' => $sortdesc);
            $sortorder = "(doc.event.value == 'updateviewaccess' && doc.resourcetype.value == 'group' && doc.ownertype.value == 'user') ? 1 : 0";
            $sorttypeaggmap = '|updateviewaccess|group|user';
            break;
        case "sharedcomments":
            $sortdirection = array('EventTypeCount' => $sortdesc);
            $sortorder = "(doc.event.value == 'sharedcommenttogroup' && doc.resourcetype.value == 'comment' && doc.ownertype.value == 'group') ? 1 : 0";
            $sorttypeaggmap = '|sharedcommenttogroup|comment|group';
            break;
        default:
            $sortdirection = '';
            $sortorder = "1";
    }

    $aggmap = array();
    // Add in the elasticsearch data if needed
    if (get_config('searchplugin') == 'elasticsearch' && get_config('eventlogenhancedsearch')) {
        safe_require('search', 'elasticsearch');
        $options = array(
            'query' => array(
                'multi_match' => array(
                    'query' => 'group',
                    'fields' => array(
                        'ownertype', 'resourcetype'
                    )
                ),
            ),
            'range' => array(
                'range' => array(
                    'ctime' => array(
                        'gte' => $start . ' 00:00:00',
                        'lte' => $end . ' 23:59:59'
                    )
                )
            ),
            'aggs' => array(
                'GroupId' => array(
                    'terms' => array(
                        'field' => 'ownerid',
                        'order' => $sortdirection,
                        'size' => $count,
                    ),
                    'aggs' => array(
                        'EventTypeCount' => array(
                            'sum' => array(
                                'script' => array(
                                    'inline' => $sortorder,
                                ),
                            ),
                        ),
                        'EventType' => array(
                            'terms' => array(
                                'field' => 'event',
                                'min_doc_count' => 0,
                            ),
                            'aggs' => array(
                                'ResourceType' => array(
                                    'terms' => array(
                                        'field' => 'resourcetype',
                                    ),
                                    'aggs' => array(
                                        'OwnerType' => array(
                                            'terms' => array(
                                                'field' => 'ownertype',
                                            ),
                                        ),
                                    ),
                                ),
                            ),
                        ),
                    ),
                ),
            ),
        );
        if (empty($sortdirection)) {
            unset($options['aggs']['GroupId']['terms']['order']);
        }
        $aggregates = PluginSearchElasticsearch::search_events($options, 0, 0);
        $groupids = array();
        if ($aggregates['totalresults'] > 0) {
            ElasticsearchType_event_log::process_aggregations($aggmap, $aggregates['aggregations'], true, array('GroupId', 'EventType', 'ResourceType', 'OwnerType'));
            $groups = array_slice($aggregates['aggregations']['GroupId']['buckets'], $offset, $limit, true);
            foreach($groups as $k => $g) {
                if (isset($aggmap[$g['key'] . $sorttypeaggmap]) && $aggmap[$g['key'] . $sorttypeaggmap] > 0) {
                    $groupids[$k] = $g['key'];
                }
            }
        }
    }

    switch ($sorttype) {
        case "members":
            $ordersql = " mc.members " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "views":
            $ordersql = " vc.views " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "forums":
            $ordersql = " fc.forums " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "posts":
            $ordersql = " posts " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "id":
            $ordersql = " g.id " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "group":
        default:
            $ordersql = " g.name " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');


    }
    $rangesql = '';
    $rangewhere = array();
    if ($start) {
        $rangesql = " AND ctime >= DATE(?) AND ctime <= DATE(?)";
        $rangewhere[] = $start;
        $rangewhere[] = $end;
    }

    $elasticselect = $elasticfrom = '';
    if (!empty($sortdirection) && !empty($groupids)) {
        $elasticselect = ", CASE WHEN ggc.elastic IS NULL THEN 0 ELSE ggc.elastic END AS elastic";
        $elasticfrom = " LEFT JOIN (
                            SELECT g.id, 1 AS elastic
                            FROM {group} g
                            WHERE g.id IN (" . implode(',', $groupids) . ")
                        ) ggc on g.id = ggc.id";
        $ordersql = " elastic  " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC') . ", " . $ordersql;
    }

    $sql = "SELECT
            g.id, g.name, g.urlid, g.ctime, mc.members, vc.views, fc.forums,
            CASE WHEN pc.posts IS NULL THEN 0 ELSE pc.posts END AS posts
            " . $elasticselect . "
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
            " . $elasticfrom . "
        WHERE
            g.deleted = 0 " . $rangesql . "
        ORDER BY
            " . $ordersql . ", g.name, g.id";

    if (!empty($extra['csvdownload'])) {
        $groupdata = get_records_sql_array($sql, $rangewhere);
    }
    else {
        $groupdata = get_records_sql_array($sql, $rangewhere, $offset, $limit);
    }

    if (!empty($sortdirection) && !empty($groupids)) {
        $groupidkeys = array_flip($groupids);
        usort($groupdata, function ($a, $b) use ($groupidkeys) {
            if (!isset($groupidkeys[$a->id]) && !isset($groupidkeys[$b->id])) {
                return 0;
            }
            else if (!isset($groupidkeys[$a->id]) || !isset($groupidkeys[$b->id])) {
                return empty($extra['sortdesc']) ? -1 : 1;
            }
            $posA = $groupidkeys[$a->id];
            $posB = $groupidkeys[$b->id];
            if ($posA == $posB) {
                return 0;
            }
            return ($posA < $posB) ? -1 : 1;
        });
    }
    foreach ($groupdata as $key => $group) {
        if (!empty($aggmap)) {
            $group->groupcomments = !empty($aggmap[$group->id . '|saveartefact|comment|group']) ? $aggmap[$group->id . '|saveartefact|comment|group'] : 0;
            $group->sharedviews = !empty($aggmap[$group->id . '|updateviewaccess|group|user']) ? $aggmap[$group->id . '|updateviewaccess|group|user'] : 0;
            $group->sharedcomments = !empty($aggmap[$group->id . '|sharedcommenttogroup|comment|group']) ? $aggmap[$group->id . '|sharedcommenttogroup|comment|group'] : 0;
        }
        else {
            $group->groupcomments = get_string('notdisplayed', 'statistics');
            $group->sharedviews = get_string('notdisplayed', 'statistics');
            $group->sharedcomments = get_string('notdisplayed', 'statistics');
        }
    }

    $daterange = array_map(function ($obj) { return $obj->ctime; }, $groupdata);
    $result['settings']['start'] = ($start) ? $start : min($daterange);
    if (!empty($extra['csvdownload'])) {
        $csvfields = array('id', 'name', 'members', 'views', 'groupcomments', 'sharedviews',
                           'sharedcomments', 'forums', 'posts');
        $csvheaders = array('sharedviews' => 'shared_pages',
                            'views' => 'pages',
                            'groupcomments' => 'group_comments',
                            'sharedcomments' => 'shared_page_comments');
        $USER->set_download_file(generate_csv($groupdata, $csvfields, $csvheaders), 'groupstatistics.csv', 'text/csv');
    }
    $result['csv'] = true;

    require_once('group.php');
    if ($groupdata) {
        foreach ($groupdata as $group) {
            $group->homeurl = group_homepage_url($group);
        }
    }
    $columnkeys = array();
    foreach ($extra['columns'] as $column) {
        $columnkeys[$column] = 1;
    }

    $smarty = smarty_core();
    $smarty->assign('data', $groupdata);
    $smarty->assign('columns', $columnkeys);
    $smarty->assign('offset', $offset);
    $result['tablerows'] = $smarty->fetch('admin/groupstats.tpl');

    return $result;
}

function group_type_graph($type = false) {
    $grouptypes = get_records_sql_array("
        SELECT grouptype, jointype, COUNT(id) AS groupcount
        FROM {group}
        WHERE deleted = 0
        GROUP BY grouptype, jointype
        ORDER BY groupcount DESC", array()
    );

    if (is_array($grouptypes) && count($grouptypes) > 1) {
        $dataarray = array();
        foreach ($grouptypes as &$t) {
            $strtype = get_string('name', 'grouptype.' . $t->grouptype);
            $strtype .= ' (' . get_string('membershiptype.abbrev.' . $t->jointype, 'group') . ')';
            $dataarray[$strtype] = $t->groupcount;
        }
        ksort($dataarray);
        arsort($dataarray);
        $data['graph'] = ($type) ? $type : 'pie';
        $data['graph_function_name'] = 'group_type_graph';
        $data['title'] = get_string('grouptypes','statistics');
        $data['labels'] = array_keys($dataarray);
        $data['data'] = $dataarray;
        require_once(get_config('libroot') . 'graph.php');
        $graphdata = get_circular_graph_json($data);

        $dbdata['type'] = 'group-type-graph';
        $dbwhere['type'] = 'group-type-graph';
        $dbdata['value'] = json_encode($graphdata);
        $dbdata['ctime'] = db_format_timestamp(time());
        ensure_record_exists('site_data', (object)$dbwhere, (object)$dbdata);
    }
}

function group_type_graph_render($type = null) {
    $data['graph'] = ($type) ? $type : 'pie';
    if ($jsondata = json_decode(get_field('site_data','value','type','group-type-graph'))) {
        $data['jsondata'] = json_encode($jsondata[0]);
        return $data;
    }
}

function pageactivity_statistics_headers($extra, $urllink) {
    return array(
        array('id' => 'rownum', 'name' => '#'),
        array('id' => 'view', 'required' => true,
              'name' => get_string('view'),
              'class' => format_class($extra, 'view'),
              'link' => format_goto($urllink . '&sort=view', $extra, array('sort'), 'view'),
              'helplink' => get_help_icon('core', 'reports', 'pageactivity', 'view')
        ),
        array('id' => 'collection', 'required' => true,
              'name' => get_string('Collection', 'collection'),
              'class' => format_class($extra, 'collection'),
              'link' => format_goto($urllink . '&sort=collection', $extra, array('sort'), 'collection'),
              'helplink' => get_help_icon('core', 'reports', 'pageactivity', 'collection')
        ),
        array('id' => 'owner', 'required' => true,
              'name' => get_string('Owner', 'view'),
              'class' => format_class($extra, 'owner'),
              'link' => format_goto($urllink . '&sort=owner', $extra, array('sort'), 'owner'),
              'helplink' => get_help_icon('core', 'reports', 'pageactivity', 'owner')
        ),
        array('id' => 'created',
              'name' => get_string('Created'),
              'class' => format_class($extra, 'created'),
              'link' => format_goto($urllink . '&sort=created', $extra, array('sort'), 'created'),
              'helplink' => get_help_icon('core', 'reports', 'pageactivity', 'created')
        ),
        array('id' => 'modified',
              'name' => get_string('lastmodified', 'statistics'),
              'class' => format_class($extra, 'modified'),
              'link' => format_goto($urllink . '&sort=modified', $extra, array('sort'), 'modified'),
              'helplink' => get_help_icon('core', 'reports', 'pageactivity', 'modified')
        ),
        array('id' => 'visited',
              'name' => get_string('lastvisited', 'statistics'),
              'class' => format_class($extra, 'visited'),
              'link' => format_goto($urllink . '&sort=visited', $extra, array('sort'), 'visited'),
              'helplink' => get_help_icon('core', 'reports', 'pageactivity', 'visited')
        ),
        array('id' => 'blocks',
              'name' => get_string('blocks'),
              'class' => format_class($extra, 'blocks'),
              'link' => format_goto($urllink . '&sort=blocks', $extra, array('sort'), 'blocks'),
              'helplink' => get_help_icon('core', 'reports', 'pageactivity', 'blocks')
        ),
        array('id' => 'visits', 'required' => true,
              'name' => get_string('Visits'),
              'class' => format_class($extra, 'visits'),
              'link' => format_goto($urllink . '&sort=visits', $extra, array('sort'), 'visits'),
              'helplink' => get_help_icon('core', 'reports', 'pageactivity', 'visits')
        ),
        array('id' => 'comments', 'required' => true,
              'name' => get_string('Comments', 'artefact.comment'),
              'class' => format_class($extra, 'comments'),
              'link' => format_goto($urllink . '&sort=comments', $extra, array('sort'), 'comments'),
              'helplink' => get_help_icon('core', 'reports', 'pageactivity', 'comments')
        ),
    );
}

function view_statistics($limit, $offset, $extra) {
    $data = array();
    $urllink = get_config('wwwroot') . 'admin/users/statistics.php?type=users&subtype=pageactivity';
    $data['tableheadings'] = pageactivity_statistics_headers($extra, $urllink);

    $activeheadings = get_active_columns($data, $extra);
    $extra['columns'] = array_keys($activeheadings);

    $data['table'] = view_stats_table($limit, $offset, $extra);
    $data['table']['activeheadings'] = $activeheadings;

    $data['summary'] = $data['table']['count'] == 0 ? get_string('nostats', 'admin') : null;

    return $data;
}

function view_stats_table($limit, $offset, $extra) {
    global $USER, $SESSION;

    $start = !empty($extra['start']) ? $extra['start'] : null;
    $end = !empty($extra['end']) ? $extra['end'] : date('Y-m-d', strtotime('now'));
    $users = $SESSION->get('usersforstats');
    require_once('view.php');
    $where = "(v.owner != 0 OR v.owner IS NULL) AND v.type != ? AND v.template != ?";
    $values = array('dashboard', View::SITE_TEMPLATE);
    if ($users) {
      $where .= " AND v.owner IN (" . join(',', array_map('db_quote', array_values((array)$users))) . ")";
    }
    if (get_config('eventloglevel') == 'all') {
        if ($start) {
            $where .= " AND e.ctime >= DATE(?) AND e.ctime <= DATE(?)
                        AND ((e.resourcetype = 'view' AND e.resourceid = v.id)
                             OR (e.parentresourcetype = 'view' AND e.parentresourceid = v.id))";
            $sqlwhere = " v.id IN (SELECT DISTINCT v.id FROM {view} v, {event_log} e WHERE " . $where . ")";
            $values[] = $start;
            $values[] = $end;
        }
        $count = count_records_sql("SELECT COUNT(DISTINCT v.id) FROM {view} v, {event_log} e WHERE " . $where, $values);
    }
    else {
        if ($start) {
            $where .= " AND v.mtime >= DATE(?) AND v.mtime <= DATE(?)";
            $sqlwhere = $where;
            $values[] = $start;
            $values[] = $end;
        }
        $count = count_records_sql("SELECT COUNT(*) FROM {view} v WHERE " . $where, $values);
    }

    $pagination = build_pagination(array(
        'id' => 'stats_pagination',
        'url' => get_config('wwwroot') . 'admin/users/statistics.php?type=views',
        'jsonscript' => 'admin/users/statistics.json.php',
        'datatable' => 'statistics_table',
        'count' => $count,
        'limit' => $limit,
        'offset' => $offset,
        'setlimit' => true,
        'extradata' => $extra,
    ));

    $result = array(
        'count'         => $count,
        'tablerows'     => '',
        'pagination'    => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
    );
    $result['settings']['start'] = ($start) ? $start : null;
    $result['settings']['end'] = $end;
    $result['settings']['users'] = !empty($users) ? count($users) : 0;
    if ($count < 1) {
        return $result;
    }

    $sorttype = !empty($extra['sort']) ? $extra['sort'] : '';
    switch ($sorttype) {
        case "view":
            $orderby = " v.title " . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC') . ", v.id";
            break;
        case "collection":
            $orderby = " c.name " . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC') . ", v.id";
            break;
        case "owner":
            $orderby = " CASE
                  WHEN v.owner IS NOT NULL
                  THEN (SELECT CONCAT(firstname, ' ', lastname) FROM {usr} WHERE id = v.owner)
                  WHEN v.group IS NOT NULL
                  THEN (SELECT name FROM {group} WHERE id = v.group)
                  ELSE (SELECT displayname FROM {institution} WHERE name = v.institution)
                  END " . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC') . ", v.id";
            break;
        case "comments":
        case "blocks":
            $orderby = " " . $sorttype . " " . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC') . ", v.title, v.id";
            break;
        case "created":
            $orderby = " v.ctime " . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC') . ", v.title, v.id";
            break;
        case "modified":
            $orderby = " v.mtime " . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC') . ", v.title, v.id";
            break;
        case "visited":
            $orderby = " v.atime " . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC') . ", v.title, v.id";
            break;
        case "visits":
        default:
            $orderby = " v.visits " . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC') . ", v.title, v.id";
    }

    $sql = "SELECT v.id, v.title, v.owner, v.group, v.institution, c.name,
            CASE
                WHEN v.owner IS NOT NULL
                THEN (SELECT CONCAT(firstname, ' ', lastname) FROM {usr} WHERE id = v.owner)
                WHEN v.group IS NOT NULL
                THEN (SELECT name FROM {group} WHERE id = v.group)
                ELSE (SELECT displayname FROM {institution} WHERE name = v.institution)
            END AS displayname,
            v.visits, v.type, v.ownerformat, v.urlid, v.template,
            v.ctime, v.mtime, v.atime,
            (SELECT COUNT(*) FROM {block_instance} WHERE view = v.id) AS blocks,
            (SELECT COUNT(*) FROM {artefact_comment_comment} WHERE onview = v.id) AS comments
        FROM {view} v
        LEFT JOIN {collection_view} cv ON cv.view = v.id
        LEFT JOIN {collection} c ON c.id = cv.collection
        WHERE " . $sqlwhere . "
        ORDER BY " . $orderby;
    if (empty($extra['csvdownload'])) {
        $viewdata = get_records_sql_assoc($sql, $values, $offset, $limit);
    }
    else {
        $viewdata = get_records_sql_assoc($sql, $values);
    }
    require_once('view.php');
    require_once('group.php');
    View::get_extra_view_info($viewdata, false, false);

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
        if ($v->collection) {
            $v->collectiontitle = $v->collection->get('name');
        }
    }

    $daterange = array_map(function ($obj) { return $obj->ctime; }, $viewdata);
    $result['settings']['start'] = ($start) ? $start : min($daterange);
    if (!empty($extra['csvdownload'])) {
        $csvfields = array('displaytitle', 'fullurl', 'collectiontitle','ownername', 'ownerurl',
                           'ctime', 'mtime', 'atime', 'blocks', 'visits', 'comments');
        $USER->set_download_file(generate_csv($viewdata, $csvfields), 'viewstatistics.csv', 'text/csv');
    }
    $result['csv'] = true;
    $columnkeys = array();
    foreach ($extra['columns'] as $column) {
        $columnkeys[$column] = 1;
    }

    $smarty = smarty_core();
    $smarty->assign('data', $viewdata);
    $smarty->assign('columns', $columnkeys);
    $smarty->assign('offset', $offset);
    $result['tablerows'] = $smarty->fetch('admin/viewstats.tpl');

    return $result;
}

function view_type_graph($type = null) {
    // Draw a pie graph of views broken down by view type.
    $viewtypes = get_records_sql_array('
        SELECT type, COUNT(id) AS views
        FROM {view} WHERE type != ?
        GROUP BY type',
        array('dashboard')
    );

    if (is_array($viewtypes) && count($viewtypes) > 1) {
        $dataarray = array();
        foreach ($viewtypes as &$t) {
            $dataarray[get_string(ucfirst($t->type), 'view')] = $t->views;
        }
        arsort($dataarray);

        $data['graph'] = ($type) ? $type : 'pie';
        $data['graph_function_name'] = 'view_type_graph';
        $data['title'] = get_string('viewsbytype', 'admin');
        $data['labels'] = array_keys($dataarray);
        $data['data'] = $dataarray;
        require_once(get_config('libroot') . 'graph.php');
        $graphdata = get_circular_graph_json($data);

        $dbdata['type'] = 'view-type-graph';
        $dbwhere['type'] = 'view-type-graph';
        $dbdata['value'] = json_encode($graphdata);
        $dbdata['ctime'] = db_format_timestamp(time());
        ensure_record_exists('site_data', (object)$dbwhere, (object)$dbdata);
    }
}

function view_type_graph_render($type = null) {
    $data['graph'] = ($type) ? $type : 'pie';
    if ($jsondata = json_decode(get_field('site_data','value','type','view-type-graph'))) {
        $data['jsondata'] = json_encode($jsondata[0]);
        return $data;
    }
}

function institution_view_statistics($limit, $offset, &$institutiondata, $extra) {
    userhasaccess($institutiondata['institution'], 'pageactivity');
    $data = array();
    $urllink = get_config('wwwroot') . 'admin/users/statistics.php?institution=' . $institutiondata['institution'] . '&type=users&subtype=pageactivity';
    $data['tableheadings'] = pageactivity_statistics_headers($extra, $urllink);

    $activeheadings = get_active_columns($data, $extra);
    $extra['columns'] = array_keys($activeheadings);

    $data['table'] = institution_view_stats_table($limit, $offset, $institutiondata, $extra);
    $data['table']['activeheadings'] = $activeheadings;

    $data['summary'] = $data['table']['count'] == 0 ? get_string('nostats', 'admin') : null;

    return $data;
}

function institution_view_stats_table($limit, $offset, &$institutiondata, $extra) {
    global $USER, $SESSION;

    $start = !empty($extra['start']) ? $extra['start'] : null;
    $end = !empty($extra['end']) ? $extra['end'] : date('Y-m-d', strtotime('now'));
    $users = $SESSION->get('usersforstats');
    if ($institutiondata['views'] != 0) {
        $start = !empty($extra['start']) ? $extra['start'] : null;
        $end = !empty($extra['end']) ? $extra['end'] : date('Y-m-d', strtotime('now'));
        $where = 'v.id IN (' . $institutiondata['viewssql'] . ') AND v.type != ?';
        $values = array_merge($institutiondata['viewssqlparam'], array('dashboard'));
        if ($users) {
            $where .= " AND v.owner IN (" . join(',', array_map('db_quote', array_values((array)$users))) . ")";
        }
        if (get_config('eventloglevel') == 'all') {
            if ($start) {
                $where .= " AND e.ctime >= DATE(?) AND e.ctime <= DATE(?)
                            AND ((e.resourcetype = 'view' AND e.resourceid = v.id)
                            OR (e.parentresourcetype = 'view' AND e.parentresourceid = v.id))";
                $sqlwhere = " v.id IN (SELECT DISTINCT v.id FROM {view} v, {event_log} e WHERE " . $where . ")";
                $values[] = $start;
                $values[] = $end;
            }
            $count = count_records_sql("SELECT COUNT(DISTINCT v.id) FROM {view} v, {event_log} e WHERE " . $where, $values);
        }
        else {
            if ($start) {
                $where .= " AND v.mtime >= DATE(?) AND v.mtime <= DATE(?)";
                $sqlwhere = $where;
                $values[] = $start;
                $values[] = $end;
            }
            $count = count_records_sql('SELECT COUNT(*) FROM {view} v WHERE ' . $where, $values);
        }
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
        'extradata' => $extra,
        'setlimit' => true,
    ));

    $result = array(
        'count'         => $count,
        'tablerows'     => '',
        'pagination'    => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
    );
    $result['settings']['start'] = ($start) ? $start : null;
    $result['settings']['end'] = $end;
    $result['settings']['users'] = !empty($users) ? count($users) : 0;
    if ($count < 1) {
        return $result;
    }

    $sorttype = !empty($extra['sort']) ? $extra['sort'] : '';
    switch ($sorttype) {
        case "view":
            $orderby = " v.title " . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC') . ", v.id";
            break;
        case "collection":
            $orderby = " c.name " . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC') . ", v.id";
            break;
        case "owner":
            $orderby = " CASE
                  WHEN v.owner IS NOT NULL
                  THEN (SELECT CONCAT(firstname, ' ', lastname) FROM {usr} WHERE id = v.owner)
                  WHEN v.group IS NOT NULL
                  THEN (SELECT name FROM {group} WHERE id = v.group)
                  ELSE (SELECT displayname FROM {institution} WHERE name = v.institution)
                  END " . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC') . ", v.id";
            break;
        case "comments":
        case "blocks":
            $orderby = " " . $sorttype . " " . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC') . ", v.title, v.id";
            break;
        case "created":
            $orderby = " v.ctime " . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC') . ", v.title, v.id";
            break;
        case "modified":
            $orderby = " v.mtime " . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC') . ", v.title, v.id";
            break;
        case "visited":
            $orderby = " v.atime " . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC') . ", v.title, v.id";
            break;
        case "visits":
        default:
            $orderby = " v.visits " . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC') . ", v.title, v.id";
    }

    $sql = "SELECT v.id, v.title, v.owner, v.group, v.institution,
            CASE
                WHEN v.owner IS NOT NULL
                THEN (SELECT CONCAT(firstname, ' ', lastname) FROM {usr} WHERE id = v.owner)
                WHEN v.group IS NOT NULL
                THEN (SELECT name FROM {group} WHERE id = v.group)
                ELSE (SELECT displayname FROM {institution} WHERE name = v.institution)
            END AS displayname,
            v.visits, v.type, v.ownerformat, v.urlid, v.template,
            v.ctime, v.mtime, v.atime,
            (SELECT COUNT(*) FROM {block_instance} WHERE view = v.id) AS blocks,
            (SELECT COUNT(*) FROM {artefact_comment_comment} WHERE onview = v.id) AS comments
        FROM {view} v
        LEFT JOIN {collection_view} cv ON cv.view = v.id
        LEFT JOIN {collection} c ON c.id = cv.collection
        WHERE " . $sqlwhere . "
        ORDER BY " . $orderby;

    if (!empty($extra['csvdownload'])) {
        $viewdata = get_records_sql_assoc($sql, $values);
    }
    else {
        $viewdata = get_records_sql_assoc($sql, $values, $offset, $limit);
    }

    require_once('view.php');
    require_once('group.php');
    View::get_extra_view_info($viewdata, false, false);

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
        $v->name = ($v->collection) ? $v->collection->get('name') : null;
        $v->collectiontitle = $v->name;
    }

    $daterange = array_map(function ($obj) { return $obj->ctime; }, $viewdata);
    $result['settings']['start'] = ($start) ? $start : min($daterange);
    if (!empty($extra['csvdownload'])) {
        $csvfields = array('displaytitle', 'fullurl', 'collectiontitle', 'ownername', 'ownerurl',
                           'ctime', 'mtime', 'atime', 'blocks', 'visits', 'comments');
        $USER->set_download_file(generate_csv($viewdata, $csvfields), $institutiondata['name'] . 'viewstatistics.csv', 'text/csv');
    }
    $result['csv'] = true;
    $columnkeys = array();
    foreach ($extra['columns'] as $column) {
        $columnkeys[$column] = 1;
    }

    $smarty = smarty_core();
    $smarty->assign('data', $viewdata);
    $smarty->assign('columns', $columnkeys);
    $smarty->assign('offset', $offset);
    $result['tablerows'] = $smarty->fetch('admin/viewstats.tpl');

    return $result;
}

function institution_view_type_graph($type = null, $institutiondata) {

    $institution = is_object($institutiondata) ? $institutiondata->institution : $institutiondata['name'];
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

    if (is_array($viewtypes) && count($viewtypes) > 1) {
        $dataarray = array();
        foreach ($viewtypes as &$t) {
            $dataarray[get_string($t->type, 'view')] = $t->views;
        }
        arsort($dataarray);

        $data['graph'] = ($type) ? $type : 'pie';
        $data['graph_function_name'] = 'institution_view_type_graph';
        $data['title'] = get_string('viewsbytype', 'admin');
        $data['labels'] = array_keys($dataarray);
        $data['data'] = $dataarray;

        require_once(get_config('libroot') . 'graph.php');
        $graphdata = get_circular_graph_json($data, null, true);

        $dbdata['type'] = 'view-type-graph';
        $dbdata['institution'] = $institution;
        $dbwhere = $dbdata;
        $dbdata['value'] = json_encode($graphdata);
        $dbdata['ctime'] = db_format_timestamp(time());

        ensure_record_exists('institution_data', (object)$dbwhere, (object)$dbdata);
    }
}

function institution_view_type_graph_render($type = null, $extradata) {

    $data['graph'] = ($type) ? $type : 'pie';
    if ($jsondata = json_decode(get_field('institution_data','value','type','view-type-graph','institution', $extradata->institution))) {
        $data['jsondata'] = json_encode($jsondata[0]);
        return $data;
    }
}

function institution_user_type_graph($type = null, $institutiondata) {

    $institution = is_object($institutiondata) ? $institutiondata->institution : $institutiondata['name'];
    $usertypes = array();
    // Draw a pie graph of users broken down by admin / staff / members.
    // Each user gets counted by their highest privilege.
    if ($institution == 'mahara') {
        $usertypes = get_records_sql_array('
            SELECT COUNT(CASE WHEN u.admin = 0 AND u.staff = 0 THEN 1 ELSE NULL END) AS numusers,
                   COUNT(CASE WHEN u.admin > 0 THEN 1 ELSE NULL END) AS numadmins,
                   COUNT(CASE WHEN u.staff > 0 AND u.admin = 0 THEN 1 ELSE NULL END) AS numstaff
            FROM {usr} u LEFT JOIN {usr_institution} ui ON u.id = ui.usr
            WHERE ui.usr IS NULL AND u.deleted = 0 AND u.id != 0', array()
        );
    }
    else {
        $usertypes = get_records_sql_array('
            SELECT COUNT(CASE WHEN ui.admin = 0 AND ui.staff = 0 THEN 1 ELSE NULL END) AS numusers,
                   COUNT(CASE WHEN ui.admin > 0 THEN 1 ELSE NULL END) AS numadmins,
                   COUNT(CASE WHEN ui.staff > 0 AND ui.admin = 0 THEN 1 ELSE NULL END) AS numstaff
            FROM {usr} u, {usr_institution} ui
            WHERE ui.usr = u.id AND u.deleted = 0 AND ui.institution = ?', array($institution)
        );
    }

    $dataarray = array();
    $totalusers = 0;
    foreach ($usertypes as $t) {
        $dataarray[get_string('members')] = $t->numusers;
        $dataarray[get_string('staff', 'statistics')] = $t->numstaff;
        $dataarray[get_string('admins', 'statistics')] = $t->numadmins;
        $totalusers = $t->numusers + $t->numstaff + $t->numadmins;
    }
    if (empty($totalusers)) {
        $dataarray = array();
    }

    $data['graph'] = ($type) ? $type : 'pie';
    $data['graph_function_name'] = 'institution_user_type_graph';
    $data['title'] = get_string('usersbytype', 'statistics');
    $data['labels'] = array_keys($dataarray);
    $data['data'] = $dataarray;
    return $data;
}

function content_statistics_headers($extra, $urllink) {
    return array(
        array('id' => 'rownum', 'name' => '#'),
        array('id' => 'name', 'required' => true,
              'name' => get_string('name'),
              'class' => format_class($extra, 'name'),
              'link' => format_goto($urllink . '&sort=name', $extra, array('sort'), 'name'),
              'helplink' => get_help_icon('core', 'reports', 'content', 'name')
        ),
        array('id' => 'modified', 'required' => true,
              'name' => get_string('modified'),
              'class' => format_class($extra, 'modified'),
              'helplink' => get_help_icon('core', 'reports', 'content', 'modified'),
//              'link' => format_goto($urllink . '&sort=modified', $extra, array('sort'), 'modified')
        ),
        array('id' => 'total', 'required' => true,
              'name' => get_string('Total'),
              'class' => format_class($extra, 'total'),
              'link' => format_goto($urllink . '&sort=total', $extra, array('sort'), 'total'),
              'helplink' => get_help_icon('core', 'reports', 'content', 'total')
        ),
    );
}

function content_statistics($limit, $offset, $extra) {
    $data = array();
    $urllink = get_config('wwwroot') . 'admin/users/statistics.php?type=content&subtype=content';
    $data['tableheadings'] = content_statistics_headers($extra, $urllink);

    $activeheadings = get_active_columns($data, $extra);
    $extra['columns'] = array_keys($activeheadings);

    $data['table'] = content_stats_table($limit, $offset, $extra);
    $data['table']['activeheadings'] = $activeheadings;

    $data['summary'] = $data['table']['count'] == 0 ? get_string('nostats', 'admin') : null;

    return $data;
}

function content_stats_table($limit, $offset, $extra) {
    global $USER;

    $start = !empty($extra['start']) ? $extra['start'] : date('Y-m-d', strtotime("-1 months"));
    $end = !empty($extra['end']) ? $extra['end'] : date('Y-m-d', strtotime('now'));
    $values = array();
    $fromsql = "FROM {site_registration} sr
                JOIN {site_registration_data} sd ON sd.registration_id = sr.id";
    if ($start) {
        $fromsql .= " WHERE sr.time >= DATE(?) AND sr.time <= DATE(?)";
        $values[] = $start;
        $values[] = $end;
    }
    else {
        $fromsql .= " WHERE sr.id IN (SELECT id FROM {site_registration} ORDER BY time DESC LIMIT 2)";
    }
    $fromsql .= " AND sd.value " . (is_postgres() ? '~ E' : 'REGEXP ') . "'^[0-9]+$'
                  AND sd.field NOT LIKE '%version'
                  AND sd.field NOT IN ('allowpublicviews', 'allowpublicprofiles', 'newstats')";
    $regdata = get_records_sql_array("SELECT DISTINCT sr.id, sr.time " . $fromsql . " ORDER BY sr.time DESC", $values);

    $count = ($regdata) ? count_records_sql("SELECT COUNT(*) " . $fromsql . " AND sr.id = " . $regdata[0]->id, $values) : 0;

    $pagination = build_pagination(array(
        'id' => 'stats_pagination',
        'url' => get_config('wwwroot') . 'admin/users/statistics.php?type=content&subtype=content',
        'jsonscript' => 'admin/users/statistics.json.php',
        'datatable' => 'statistics_table',
        'count' => $count,
        'limit' => $limit,
        'offset' => $offset,
        'extradata' => $extra,
        'setlimit' => true,
    ));

    $result = array(
        'count'         => $count,
        'tablerows'     => '',
        'pagination'    => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
    );
    $result['settings']['start'] = ($start) ? $start : null;
    $result['settings']['end'] = $end;
    if ($count < 1) {
        return $result;
    }

    $sorttype = !empty($extra['sort']) ? $extra['sort'] : '';
    switch ($sorttype) {
        case "total":
            if (is_mysql()) {
                $cast = " (sd.value + 0) ";
            }
            else {
                $cast = " CAST(sd.value AS INTEGER) ";
            }
            $ordersql = $cast . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "name":
        default:
            $ordersql = " sd.field " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
    }
    $sql = "SELECT sd.field, sd.value " . $fromsql . "
        AND sr.id = " . $regdata[0]->id . "
        ORDER BY " . $ordersql;
    if (!empty($extra['csvdownload'])) {
        $contentdata = get_records_sql_assoc($sql, $values);
    }
    else {
        $contentdata = get_records_sql_assoc($sql, $values, $offset, $limit);
    }
    $daterange = array_map(function ($obj) { return $obj->time; }, $regdata);
    $result['settings']['start'] = ($start) ? $start : min($daterange);

    if (is_array($regdata) && count($regdata) > 1) {
        $firstweeks = get_records_sql_assoc(
            "SELECT sd.field, sd.value " . $fromsql . "
            AND sr.id = " . end($regdata)->id . "
            ORDER BY sd.field",
            $values
        );
        foreach ($contentdata as &$d) {
            $d->modified = $d->value - (isset($firstweeks[$d->field]->value) ? $firstweeks[$d->field]->value : 0);
        }
    }
    else {
        foreach ($contentdata as &$d) {
            $d->modified = $d->value;
        }
    }

    if (!empty($extra['csvdownload'])) {
        $csvfields = array('field', 'modified', 'value');
        $USER->set_download_file(generate_csv($contentdata, $csvfields), 'contentstatistics.csv', 'text/csv');
    }
    $result['csv'] = true;
    $columnkeys = array();
    foreach ($extra['columns'] as $column) {
        $columnkeys[$column] = 1;
    }

    $smarty = smarty_core();
    $smarty->assign('data', $contentdata);
    $smarty->assign('columns', $columnkeys);
    $smarty->assign('offset', $offset);
    $result['tablerows'] = $smarty->fetch('admin/contentstats.tpl');

    return $result;
}

function objectionable_statistics_headers($extra, $urllink) {
    return array(
        array('id' => 'rownum', 'name' => '#'),
        array('id' => 'viewname', 'required' => true,
              'name' => get_string('view'),
              'class' => format_class($extra, 'viewname'),
        ),
        array('id' => 'artefactname', 'required' => true,
              'name' => get_string('Artefact'),
              'class' => format_class($extra, 'artefactname'),
        ),
        array('id' => 'reporter', 'required' => true,
              'name' => get_string('reporter', 'statistics'),
              'class' => format_class($extra, 'reporter'),
        ),
        array('id' => 'report', 'required' => true,
              'name' => get_string('report', 'group'),
              'class' => format_class($extra, 'report'),
        ),
        array('id' => 'date', 'required' => true,
              'name' => get_string('date'),
              'class' => format_class($extra, 'date'),
        ),
        array('id' => 'reviewer', 'required' => false,
              'name' => get_string('reviewer', 'statistics'),
              'class' => format_class($extra, 'reviewer'),
        ),
        array('id' => 'review', 'required' => false,
              'name' => get_string('review', 'statistics'),
              'class' => format_class($extra, 'review'),
        ),
        array('id' => 'reviewdate', 'required' => false,
              'name' => get_string('date'),
              'class' => format_class($extra, 'reviewdate'),
        ),
        array('id' => 'status', 'required' => true,
              'name' => get_string('status'),
              'class' => format_class($extra, 'status'),
        ),
    );
}

function objectionable_statistics($limit, $offset, $extra, $institution = null) {
    userhasaccess($institution, 'objectionable');
    $data = array();
    $urllink = get_config('wwwroot') . 'admin/users/statistics.php?type=content&subtype=objectionable';
    if ($institution) {
        $urllink .= '&institution=' . $institution;
    }
    $data['tableheadings'] = objectionable_statistics_headers($extra, $urllink);

    $activeheadings = get_active_columns($data, $extra);
    $extra['columns'] = array_keys($activeheadings);

    $data['table'] = objectionable_stats_table($limit, $offset, $extra, $institution, $urllink);
    $data['table']['activeheadings'] = $activeheadings;

    $data['summary'] = $data['table']['count'] == 0 ? get_string('nostats', 'admin') : null;

    return $data;
}

function objectionable_stats_table($limit, $offset, $extra, $institution, $urllink) {
    global $USER, $SESSION;

    $start = !empty($extra['start']) ? $extra['start'] : date('Y-m-d', strtotime("-1 months"));
    $end = !empty($extra['end']) ? $extra['end'] : date('Y-m-d', strtotime('now'));
    $users = $SESSION->get('usersforstats');

    $fromsql = " FROM (SELECT objectid AS viewid, report, NULL AS artefactid, reportedby, reportedtime, reviewedby, review, reviewedtime, resolvedtime, status
                       FROM {objectionable}
                       WHERE objecttype = 'view'
                       UNION
                       SELECT va.view AS viewid, o.report, o.objectid AS artefactid, reportedby, reportedtime, reviewedby, review, reviewedtime, resolvedtime, status
                       FROM {objectionable} o
                       JOIN {view_artefact} va ON va.artefact = o.objectid
                       WHERE o.objecttype = 'artefact'
                      ) AS obj
                 JOIN {view} v ON v.id = obj.viewid
                 JOIN {usr} u ON u.id = v.owner";
    $wheresql = " WHERE obj.resolvedtime IS NULL";
    $where = array();
    if ($institution) {
        if ($institution == 'mahara') {
            $wheresql .= " AND u.id NOT IN (SELECT usr FROM {usr_institution})";
        }
        else {
            $fromsql .= " JOIN {usr_institution} ui ON (ui.usr = u.id AND ui.institution = ?)";
            $where = array($institution);
        }
    }
    if ($users) {
        $wheresql .= " AND u.id IN (" . join(',', array_map('db_quote', array_values((array)$users))) . ")";
    }
    if ($start) {
        $wheresql .= " AND obj.reportedtime >= DATE(?) AND obj.reportedtime <= DATE(?)";
        $where[] = $start;
        $where[] = $end;
    }

    $count = count_records_sql("SELECT COUNT(*) " . $fromsql . $wheresql, $where);

    $pagination = build_pagination(array(
        'id' => 'stats_pagination',
        'url' => $urllink,
        'jsonscript' => 'admin/users/statistics.json.php',
        'datatable' => 'statistics_table',
        'count' => $count,
        'limit' => $limit,
        'offset' => $offset,
        'extradata' => $extra,
        'setlimit' => true,
    ));

    $result = array(
        'count'         => $count,
        'tablerows'     => '',
        'pagination'    => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
    );
    $result['settings']['start'] = ($start) ? $start : null;
    $result['settings']['end'] = $end;
    if ($count < 1) {
        return $result;
    }

    $sql = "SELECT viewid, report, artefactid, reportedby, reportedtime, reviewedby, review, reviewedtime, resolvedtime, status,
                v.title, u.id AS ownerid " . $fromsql . $wheresql . " ORDER BY v.title, reportedtime";
    if (empty($extra['csvdownload'])) {
        $sql .= " LIMIT $limit OFFSET $offset";
    }

    $data = get_records_sql_array($sql, $where);

    if ($data) {
        foreach ($data as &$item) {
            $item->artefactname = ($item->artefactid) ? get_field('artefact', 'title', 'id', $item->artefactid) : null;
            $item->viewname = $item->title;
            $item->reporter = display_name($item->reportedby);
            $item->reviewer = display_name($item->reviewedby);
        }
    }

    if (!empty($extra['csvdownload'])) {
        $csvfields = array('objectname', 'reporter', 'reportedtime', 'report', 'status');
        $USER->set_download_file(generate_csv($data, $csvfields), $institution . 'objectionablestatistics.csv', 'text/csv');
    }
    $result['csv'] = true;
    $columnkeys = array();
    foreach ($extra['columns'] as $column) {
        $columnkeys[$column] = 1;
    }

    $smarty = smarty_core();
    $smarty->assign('data', $data);
    $smarty->assign('columns', $columnkeys);
    $smarty->assign('offset', $offset);
    $result['tablerows'] = $smarty->fetch('admin/objectionablestats.tpl');

    return $result;
}

function institution_content_statistics($limit, $offset, &$institutiondata, $extra) {
    userhasaccess($institutiondata['name'], 'pageactivity');
    $data = array();

    $urllink = get_config('wwwroot') . 'admin/users/statistics.php?institution=' . $institutiondata['name'] . '&type=content&subtype=content';
    $data['tableheadings'] = content_statistics_headers($extra, $urllink);

    $activeheadings = get_active_columns($data, $extra);
    $extra['columns'] = array_keys($activeheadings);

    $data['table'] = institution_content_stats_table($limit, $offset, $institutiondata, $extra);
    $data['table']['activeheadings'] = $activeheadings;

    $data['summary'] = $data['table']['count'] == 0 ? get_string('nostats', 'admin') : null;

    return $data;
}

function institution_content_stats_table($limit, $offset, &$institutiondata, $extra) {
    global $USER;

    $start = !empty($extra['start']) ? $extra['start'] : null;
    $end = !empty($extra['end']) ? $extra['end'] : date('Y-m-d', strtotime('now'));
    $values = array();
    $fromsql = "FROM {institution_registration} sr
                JOIN {institution_registration_data} sd ON sd.registration_id = sr.id";
    if ($start) {
        $fromsql .= " WHERE sr.time >= DATE(?) AND sr.time <= DATE(?)";
        $values[] = $start;
        $values[] = $end;
    }
    else {
        $fromsql .= " WHERE sr.id IN (SELECT id FROM {institution_registration} WHERE institution = ? ORDER BY time DESC LIMIT 2)";
        $values[] = $institutiondata['name'];
    }
    $fromsql .= " AND sr.institution = ?";
    $values[] = $institutiondata['name'];
    $regdata = get_records_sql_array("SELECT DISTINCT sr.id, sr.time " . $fromsql . " ORDER BY sr.time DESC", $values);

    if ($regdata === false) {
        return array('count' => 0);
    }

    $count = count_records_sql("SELECT COUNT(*) " . $fromsql . " AND sr.id = " . $regdata[0]->id . " AND sd.field != 'usersloggedin'", $values);

    $pagination = build_pagination(array(
        'id' => 'stats_pagination',
        'url' => get_config('wwwroot') . 'admin/users/statistics.php?institution=' . $institutiondata['name'] . '&type=content&subtype=content',
        'jsonscript' => 'admin/users/statistics.json.php',
        'datatable' => 'statistics_table',
        'count' => $count,
        'limit' => $limit,
        'offset' => $offset,
        'extradata' => $extra,
        'setlimit' => true,
    ));

    $result = array(
        'count'         => $count,
        'tablerows'     => '',
        'pagination'    => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
    );
    $result['settings']['start'] = ($start) ? $start : null;
    $result['settings']['end'] = $end;
    if ($count < 1) {
        return $result;
    }

    $sorttype = !empty($extra['sort']) ? $extra['sort'] : '';
    switch ($sorttype) {
        case "total":
            if (is_mysql()) {
                $cast = " sd.value ";
            }
            else {
                $cast = " CAST(sd.value AS INTEGER) ";
            }
            $ordersql = $cast . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "name":
        default:
            $ordersql = " sd.field " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
    }
    $sql = "SELECT sd.field, sd.value " . $fromsql . "
          AND sr.id = " . $regdata[0]->id . "
          AND sd.field != 'usersloggedin'
          ORDER BY " . $ordersql;
    if (!empty($extra['csvdownload'])) {
        $contentdata = get_records_sql_assoc($sql, $values);
    }
    else {
        $contentdata = get_records_sql_assoc($sql, $values, $offset, $limit);
    }

    $daterange = array_map(function ($obj) { return $obj->time; }, $regdata);
    $result['settings']['start'] = ($start) ? $start : min($daterange);

    if (is_array($regdata) && count($regdata) > 1) {
        $firstweeks = get_records_sql_assoc(
            "SELECT sd.field, sd.value " . $fromsql . "
            AND sr.id = " . end($regdata)->id . "
            AND sd.field != 'usersloggedin'
            ORDER BY " . $ordersql,
            $values
        );
        foreach ($contentdata as &$d) {
            $d->modified = $d->value - (isset($firstweeks[$d->field]->value) ? $firstweeks[$d->field]->value : 0);
        }
    }
    else {
        foreach ($contentdata as &$d) {
            $d->modified = $d->value;
        }
    }
    if (isset($contentdata['count_members'])) {
        $contentdata['count_members']->modified = get_field('institution_registration_data', 'value', 'registration_id', $regdata[0]->id, 'field', 'usersloggedin');
    }

    if (!empty($extra['csvdownload'])) {
        $csvfields = array('field', 'modified', 'value');
        $USER->set_download_file(generate_csv($contentdata, $csvfields), $institutiondata['name'] . 'contentstatistics.csv', 'text/csv');
    }
    $result['csv'] = true;

    $columnkeys = array();
    foreach ($extra['columns'] as $column) {
        $columnkeys[$column] = 1;
    }

    $smarty = smarty_core();
    $smarty->assign('data', $contentdata);
    $smarty->assign('offset', $offset);
    $smarty->assign('columns', $columnkeys);
    $smarty->assign('institution', $institutiondata['name']);
    $result['tablerows'] = $smarty->fetch('admin/contentstats.tpl');

    return $result;
}

function masquerading_statistics_headers($extra, $urllink) {
    return array(
        array('id' => 'rownum', 'name' => '#'),
        array(
              'id' => 'user', 'required' => true,
              'name' => get_string('user', 'statistics'),
              'class' => format_class($extra, 'user'),
              'link' => format_goto($urllink . '&sort=user', $extra, array('sort'), 'user'),
              'helplink' => get_help_icon('core', 'reports', 'masquerading', 'user')
        ),
        array(
              'id' => 'reason',
              'name' => get_string('masqueradereason', 'admin'),
              'class' => format_class($extra, 'reason'),
              'helplink' => get_help_icon('core', 'reports', 'masquerading', 'reason')
        ),
        array(
              'id' => 'masquerader',
              'name' => get_string('masquerader', 'admin'),
              'class' => format_class($extra, 'masquerader'),
              'link' => format_goto($urllink . '&sort=masquerader', $extra, array('sort'), 'masquerader'),
              'helplink' => get_help_icon('core', 'reports', 'masquerading', 'masquerader')
        ),
        array(
              'id' => 'date', 'required' => true,
              'name' => get_string('masqueradetime', 'admin'),
              'class' => format_class($extra, 'date'),
              'link' => format_goto($urllink . '&sort=date', $extra, array('sort'), 'date'),
              'helplink' => get_help_icon('core', 'reports', 'masquerading', 'date')
        ),
    );
}

function masquerading_statistics($limit, $offset, $extra, $institution = null) {
    userhasaccess($institution, 'masquerading');
    $urllink = get_config('wwwroot') . 'admin/users/statistics.php?type=users&subtype=masquerading';
    if ($institution) {
        $urllink .= '&institution=' . $institution;
    }
    $data = array();
    $data['tableheadings'] = masquerading_statistics_headers($extra, $urllink);

    $activeheadings = get_active_columns($data, $extra);
    $extra['columns'] = array_keys($activeheadings);

    $data['table'] = masquerading_stats_table($limit, $offset, $extra, $institution, $urllink);
    $data['table']['activeheadings'] = $activeheadings;

    $data['summary'] = $data['table']['count'] == 0 ? get_string('nostats', 'admin') : null;

    return $data;
}

function masquerading_stats_table($limit, $offset, $extra, $institution, $urllink) {
    global $USER, $SESSION;

    $start = !empty($extra['start']) ? $extra['start'] : null;
    $end = !empty($extra['end']) ? $extra['end'] : date('Y-m-d', strtotime('now'));
    $users = $SESSION->get('usersforstats');

    $fromsql = " FROM {usr} u JOIN {event_log} e ON e.usr = u.id ";
    $wheresql = " WHERE u.id != 0 AND u.deleted = 0 AND e.event = 'loginas'";
    $where = array();
    if ($institution) {
        $fromsql .= " JOIN {usr_institution} ui ON (ui.usr = u.id AND ui.institution = ?)";
        $where = array($institution);
    }
    if ($users) {
        $wheresql .= " AND (e.usr IN (" . join(',', array_map('db_quote', array_values((array)$users))) . ")";
        $wheresql .= "   OR e.realusr IN (" . join(',', array_map('db_quote', array_values($users))) . "))";
    }
    if ($start) {
        $wheresql .= " AND e.ctime >= DATE(?) AND e.ctime <= DATE(?)";
        $where[] = $start;
        $where[] = $end;
    }
    $count = count_records_sql("SELECT COUNT(*) " . $fromsql . $wheresql, $where);

    $pagination = build_pagination(array(
        'id' => 'stats_pagination',
        'url' => $urllink,
        'jsonscript' => 'admin/users/statistics.json.php',
        'datatable' => 'statistics_table',
        'count' => $count,
        'limit' => $limit,
        'offset' => $offset,
        'setlimit' => true,
        'extradata' => $extra,
    ));

    $result = array(
        'count'         => $count,
        'tablerows'     => '',
        'pagination'    => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
    );
    $result['settings']['start'] = ($start) ? $start : null;
    $result['settings']['end'] = $end;
    $result['settings']['users'] = !empty($users) ? count($users) : 0;
    if ($count < 1) {
        return $result;
    }

    $sorttype = !empty($extra['sort']) ? $extra['sort'] : '';
    switch ($sorttype) {
        case "masquerader":
        case "user":
            $orderby = " " . $sorttype . " " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC') . ", date";
            break;
        case "date":
        default:
            $orderby = " date " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
    }

    $sql = "SELECT u.id AS user, e.data, e.ctime AS date, e.realusr AS masquerader
            " . $fromsql . $wheresql . "
            ORDER BY " . $orderby;
    if (empty($extra['csvdownload'])) {
        $sql .= " LIMIT $limit OFFSET $offset";
    }
    $data = get_records_sql_array($sql, $where);
    $daterange = array_map(function ($obj) { return $obj->date; }, $data);
    $result['settings']['start'] = ($start) ? $start : min($daterange);

    foreach ($data as $item) {
        $jsondata = json_decode($item->data);
        $item->reason = $jsondata->reason;
        $item->userurl = profile_url($item->user);
        $item->user = display_name($item->user);
        $item->masqueraderurl = profile_url($item->masquerader);
        $item->masquerader = display_name($item->masquerader);
        $item->date = format_date(strtotime($item->date));
    }

    if (!empty($extra['csvdownload'])) {
        $csvfields = array('user', 'reason', 'masquerader', 'masqueraderurl', 'date');
        $USER->set_download_file(generate_csv($data, $csvfields), $institution . 'masqueradingstatistics.csv', 'text/csv');
    }
    $result['csv'] = true;
    $columnkeys = array();
    foreach ($extra['columns'] as $column) {
        $columnkeys[$column] = 1;
    }

    $smarty = smarty_core();
    $smarty->assign('data', $data);
    $smarty->assign('columns', $columnkeys);
    $smarty->assign('offset', $offset);
    $result['tablerows'] = $smarty->fetch('admin/users/loginaslog.tpl');

    return $result;
}

function accesslist_statistics_headers($extra, $urllink) {
    return array(
        array('id' => 'rownum', 'name' => '#'),
        array(
              'id' => 'owner', 'required' => true,
              'name' => get_string('owner', 'view'),
              'class' => format_class($extra, 'owner'),
              'link' => format_goto($urllink . '&sort=owner', $extra, array('sort'), 'owner'),
              'helplink' => get_help_icon('core', 'reports', 'accesslist', 'owner')
        ),
        array(
              'id' => 'views', 'required' => true,
              'name' => get_string('View', 'view') . '/' . get_string('Collection', 'collection'),
              'class' => format_class($extra, 'views'),
              'link' => format_goto($urllink . '&sort=views', $extra, array('sort'), 'views'),
              'helplink' => get_help_icon('core', 'reports', 'accesslist', 'views')
        ),
        array(
              'id' => 'numviews', 'required' => true,
              'name' => get_string('Views', 'view'),
              'class' => format_class($extra, 'numviews'),
              'link' => format_goto($urllink . '&sort=numviews', $extra, array('sort'), 'numviews'),
              'helplink' => get_help_icon('core', 'reports', 'accesslist', 'numviews')
        ),
        array(
              'id' => 'accessrules', 'required' => true,
              'name' => get_string('accesslist', 'view'),
              'class' => format_class($extra, 'accessrules'),
              'helplink' => get_help_icon('core', 'reports', 'accesslist', 'accessrules')
        ),
    );
}

function accesslist_statistics($limit, $offset, $extra, $institution = null) {
    userhasaccess($institution, 'accesslist');
    $data = array();
    $urllink = get_config('wwwroot') . 'admin/users/statistics.php?type=users&subtype=accesslist';
    if ($institution) {
        $urllink .= '&institution=' . $institution;
    }
    $data['tableheadings'] = accesslist_statistics_headers($extra, $urllink);

    $activeheadings = get_active_columns($data, $extra);
    $extra['columns'] = array_keys($activeheadings);

    $data['table'] = accesslist_stats_table($limit, $offset, $extra, $institution, $urllink);
    $data['table']['activeheadings'] = $activeheadings;

    $data['summary'] = $data['table']['count'] == 0 ? get_string('nostats', 'admin') : null;

    return $data;
}

function accesslist_stats_table($limit, $offset, $extra, $institution, $urllink) {
    global $USER, $SESSION;

    $start = !empty($extra['start']) ? $extra['start'] : null;
    $end = !empty($extra['end']) ? $extra['end'] : date('Y-m-d', strtotime('now'));
    $users = $SESSION->get('usersforstats');

    $fromsql = " FROM (
        SELECT u.id AS userid, u.deleted AS udeleted, CONCAT(u.firstname, ' ', u.lastname) AS displayname, cv.view AS viewid, c.id AS collectionid,
            (SELECT COUNT(*) FROM {collection_view} WHERE collection = c.id) AS views,
            c.name AS title, c.ctime AS vctime
        FROM {usr} u JOIN {collection} c ON c.owner = u.id
        JOIN {collection_view} cv ON cv.collection = c.id
        WHERE cv.displayorder = 0
        UNION
        SELECT u.id AS userid, u.deleted AS udeleted, CONCAT(u.firstname, ' ', u.lastname) AS displayname, v.id AS viewid, NULL AS collectionid,
            1 AS views, v.title, v.ctime AS vctime
        FROM {usr} u JOIN {view} v ON v.owner = u.id
        LEFT JOIN {collection_view} cv ON cv.view = v.id
        WHERE cv.collection IS NULL AND v.type !='dashboard'
        UNION
        SELECT u.id AS userid, u.deleted AS udeleted, CONCAT(u.firstname, ' ', u.lastname) AS displayname, NULL AS viewid, NULL AS collectionid,
            0 AS views, NULL as title, u.ctime AS vctime
        FROM {usr} u LEFT JOIN {view} v ON v.owner = u.id
        WHERE v.id IS NULL
    ) AS t";
    $wheresql = " WHERE userid != 0 AND udeleted = 0";
    $where = array();
    if ($institution) {
        $fromsql .= " JOIN {usr_institution} ui ON (ui.usr = userid AND ui.institution = ?)";
        $where = array($institution);
    }
    if ($users) {
        $wheresql .= " AND userid IN (" . join(',', array_map('db_quote', array_values((array)$users))) . ")";
    }
    if ($start) {
        $wheresql .= " AND vctime >= DATE(?) AND vctime <= DATE(?)";
        $where[] = $start;
        $where[] = $end;
    }
    $count = count_records_sql("SELECT COUNT(*) " . $fromsql . $wheresql, $where);

    $pagination = build_pagination(array(
        'id' => 'stats_pagination',
        'url' => $urllink,
        'jsonscript' => 'admin/users/statistics.json.php',
        'datatable' => 'statistics_table',
        'count' => $count,
        'limit' => $limit,
        'offset' => $offset,
        'setlimit' => true,
        'extradata' => $extra,
    ));

    $result = array(
        'count'         => $count,
        'tablerows'     => '',
        'pagination'    => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
    );
    $result['settings']['start'] = ($start) ? $start : null;
    $result['settings']['end'] = $end;
    $result['settings']['users'] = !empty($users) ? count($users) : 0;
    if ($count < 1) {
        return $result;
    }

    $sorttype = !empty($extra['sort']) ? $extra['sort'] : '';
    switch ($sorttype) {
        case "numviews":
            $orderby = " views " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "views":
            $orderby = " title " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC') . ", displayname";
            break;
        case "owner":
        default:
            $orderby = " displayname " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC') . ", title, viewid";
    }
    $sql = "SELECT userid, displayname, viewid, collectionid, views, title, vctime
            " . $fromsql . $wheresql . "
            ORDER BY " . $orderby;
    if (empty($extra['csvdownload'])) {
        $sql .= " LIMIT $limit OFFSET $offset";
    }
    $data = get_records_sql_array($sql, $where);
    $daterange = array_map(function ($obj) { return $obj->vctime; }, $data);
    $result['settings']['start'] = ($start) ? $start : min($daterange);

    foreach ($data as $item) {
        $item->userurl = profile_url($item->userid);
        if ($item->views < 1) {
            $item->title = get_string('noviews1', 'view');
        }
        $item->access = get_records_sql_array("
            SELECT *, 0 AS secreturls
            FROM {view_access} WHERE view = ? AND token IS NULL
            UNION
            (SELECT *, (SELECT COUNT(*) FROM {view_access} va2 WHERE token IS NOT NULL AND va2.view = va.view) AS secreturls
            FROM {view_access} va WHERE va.view = ? AND va.token IS NOT NULL LIMIT 1)",
            array($item->viewid, $item->viewid));
        $item->hasaccessrules = !empty($item->access);
        $item->pending = is_view_suspended($item->viewid);
    }

    if (!empty($extra['csvdownload'])) {
        $csvfields = array('displayname', 'userurl', 'title', 'views', 'hasaccessrules');
        $USER->set_download_file(generate_csv($data, $csvfields), $institution . 'accessstatistics.csv', 'text/csv');
    }
    $result['csv'] = true;
    $columnkeys = array();
    foreach ($extra['columns'] as $column) {
        $columnkeys[$column] = 1;
    }

    $smarty = smarty_core();
    $smarty->assign('data', $data);
    $smarty->assign('columns', $columnkeys);
    $smarty->assign('offset', $offset);
    $result['tablerows'] = $smarty->fetch('admin/users/accesslists.tpl');

    return $result;
}

function comparisons_statistics_headers($extra, $urllink) {
    return array(
        array('id' => 'rownum', 'name' => '#'),
        array(
            'id' => 'institution', 'required' => true,
            'name' => get_string('institution'),
            'class' => format_class($extra, 'institution'),
            'link' => format_goto($urllink . '&sort=institution', $extra, array('sort'), 'institution')
        ),
        array(
            'id' => 'members', 'required' => true,
            'name' => get_string('members'),
            'class' => format_class($extra, 'members'),
            'link' => format_goto($urllink . '&sort=members', $extra, array('sort'), 'members'),
            'helplink' => get_help_icon('core', 'reports', 'comparisons', 'members')
        ),
        array(
            'id' => 'views', 'required' => true,
            'name' => get_string('views'),
            'class' => format_class($extra, 'views'),
            'link' => format_goto($urllink . '&sort=views', $extra, array('sort'), 'views'),
            'helplink' => get_help_icon('core', 'reports', 'comparisons', 'views')
        ),
        array(
            'id' => 'blocks', 'required' => true,
            'name' => get_string('blocks'),
            'class' => format_class($extra, 'blocks'),
            'link' => format_goto($urllink . '&sort=blocks', $extra, array('sort'), 'blocks'),
            'helplink' => get_help_icon('core', 'reports', 'comparisons', 'blocks')
        ),
        array(
            'id' => 'artefacts', 'required' => true,
            'name' => get_string('artefacts'),
            'class' => format_class($extra, 'artefacts'),
            'link' => format_goto($urllink . '&sort=artefacts', $extra, array('sort'), 'artefacts'),
            'helplink' => get_help_icon('core', 'reports', 'comparisons', 'artefacts')
        ),
        array(
            'id' => 'posts', 'required' => true,
            'name' => get_string('posts'),
            'class' => format_class($extra, 'posts'),
            'link' => format_goto($urllink . '&sort=posts', $extra, array('sort'), 'posts'),
            'helplink' => get_help_icon('core', 'reports', 'comparisons', 'posts')
        ),
    );
}

function institution_comparison_statistics($limit, $offset, $extra) {
    $data = array();
    $urllink = get_config('wwwroot') . 'admin/users/statistics.php?type=information&subtype=comparisons';
    $data['tableheadings'] = comparisons_statistics_headers($extra, $urllink);

    $activeheadings = get_active_columns($data, $extra);
    $extra['columns'] = array_keys($activeheadings);

    $data['table'] = institution_comparison_stats_table($limit, $offset, $extra, $urllink);
    $data['table']['activeheadings'] = $activeheadings;

    $data['summary'] = $data['table']['count'] == 0 ? get_string('nostats', 'admin') : null;

    return $data;
}

function institution_comparison_stats_table($limit, $offset, $extra, $urllink) {
    global $USER;

    $count = count_records_sql(
        "SELECT COUNT(DISTINCT institution)
        FROM {institution_registration}"
    );

    $pagination = build_pagination(array(
        'id' => 'stats_pagination',
        'url' => $urllink,
        'jsonscript' => 'admin/users/statistics.json.php',
        'datatable' => 'statistics_table',
        'count' => $count,
        'limit' => $limit,
        'offset' => $offset,
        'setlimit' => true,
        'extradata' => $extra,
    ));

    $result = array(
        'count'         => $count,
        'tablerows'     => '',
        'pagination'    => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
    );
    $result['settings']['start'] = get_field_sql("SELECT MIN(ctime) FROM {usr}");
    $result['settings']['end'] = date('Y-m-d', strtotime('now'));
    if ($count < 1) {
        return $result;
    }

    $sorttype = !empty($extra['sort']) ? $extra['sort'] : '';
    switch ($sorttype) {
        case "members":
        case "views":
        case "artefacts":
        case "blocks":
            $sortby = 'count_' . $sorttype;
            break;
        case "posts":
            $sortby = 'count_interaction_forum_post';
            break;
        case "institution":
        default:
            $sortby = 'displayname';
    }

    if ($sortby == 'displayname') {
        $orderby = " i.displayname ";
        $wheresql = '';
        $where = array();
    }
    else {
        if (is_postgres()) {
            $orderby = " CAST(tmp.value AS INTEGER) ";
        }
        else {
            $orderby = " (tmp.value + 0) ";
        }
        $wheresql = " JOIN {institution_registration_data} ird ON (ir.id = ird.registration_id)
                      WHERE ird.field = ?";
        $where = array($sortby);
    }

    $sql = "SELECT tmp.id, tmp.institution AS name, i.displayname
         FROM (SELECT ir.id, ir.institution " . ($sortby != 'displayname' ? ",ird.value" : '') . "
               FROM {institution_registration} ir
               JOIN (SELECT institution, MAX(time) AS time
                     FROM {institution_registration}
                     GROUP BY institution
                    ) inn ON (inn.institution = ir.institution AND inn.time = ir.time)
                    " . $wheresql . "
               ) tmp
         JOIN {institution} i ON (tmp.institution = i.name)
         ORDER BY " . $orderby . ($extra['sortdesc'] ? 'DESC' : 'ASC');
    if (!empty($extra['csvdownload'])) {
        $institutions = get_records_sql_array($sql, $where);
    }
    else {
        $institutions = get_records_sql_array($sql, $where, $offset, $limit);
    }

    $registrationdata = array();
    foreach ($institutions as $i) {
        $d = new stdClass();
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
    if (!empty($extra['csvdownload'])) {
        $csvfields = array('name', 'count_members', 'count_views', 'count_blocks', 'count_artefacts', 'count_interaction_forum_post');
        $USER->set_download_file(generate_csv($registrationdata, $csvfields), 'institutionstatistics.csv', 'text/csv');
    }
    $result['csv'] = true;
    $columnkeys = array();
    foreach ($extra['columns'] as $column) {
        $columnkeys[$column] = 1;
    }

    $smarty = smarty_core();
    $smarty->assign('data', $registrationdata);
    $smarty->assign('columns', $columnkeys);
    $smarty->assign('offset', $offset);
    $result['tablerows'] = $smarty->fetch('admin/comparisonstats.tpl');

    return $result;
}


function graph_site_data_weekly($type = null) {

    $lastsixmonths = db_format_timestamp(time() - 60*60*12*172);
    $values = array($lastsixmonths, 'view-count', 'user-count', 'group-count');
    $weekly = get_records_sql_array('
        SELECT ctime, type, "value", ' . db_format_tsfield('ctime', 'ts') . '
        FROM {site_data}
        WHERE ctime >= ? AND type IN (?,?,?)
        ORDER BY ctime, type', $values);

    if ($weekly === false || !count($weekly) > 1) {
        return false;
    }

    $dataarray = array();
    foreach ($weekly as &$r) {
        $dataarray[$r->type][strftime("%d %b", $r->ts)] = $r->value;
    }
    foreach ($dataarray as &$t) {
        // The graph will look nasty until we have 2 points to plot.
        if (count($t) < 2) {
            return false;
        }
    }

    $data['graph'] = ($type) ? $type : 'bar';
    $data['graph_function_name'] = 'graph_site_data_weekly';
    $data['title'] = get_string('sitedataweekly', 'statistics');
    $data['labels'] = array_keys($dataarray['user-count']);
    $data['labellang'] = 'statistics';
    $data['data'] = $dataarray;
    return $data;
}

function graph_site_data_daily() {
    group_type_graph();
    view_type_graph();
}

function graph_institution_data_weekly($type = null, $institutiondata) {
    $name = is_object($institutiondata) ? $institutiondata->institution : $institutiondata['name'];

    if ($name == 'all') {
        return graph_site_data_weekly($type);
    }
    $lastyear = db_format_timestamp(time() - 60*60*12*172);
    $values = array($lastyear, 'view-count', 'user-count', $name);
    $weekly = get_records_sql_array('
        SELECT ctime, type, "value", ' . db_format_tsfield('ctime', 'ts') . '
        FROM {institution_data}
        WHERE ctime >= ? AND type IN (?,?) AND institution = ?
        ORDER BY ctime, type', $values);

    if ($weekly === false || !count($weekly) > 1) {
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

    $data['graph'] = ($type) ? $type : 'bar';
    $data['graph_function_name'] = 'graph_site_data_weekly';
    $data['title'] = get_string('institutiondataweekly', 'statistics');
    $data['labels'] = array_keys($dataarray['user-count']);
    $data['labellang'] = 'statistics';
    $data['data'] = $dataarray;
    return $data;
}

function graph_institution_data_daily(&$institutiondata) {
    institution_view_type_graph(null, $institutiondata);
}

function logins_statistics_headers($extra, $urllink) {
    return array(
        array('id' => 'rownum', 'name' => '#'),
        array(
            'id' => 'institution', 'required' => true,
            'name' => get_string('institution'),
            'class' => format_class($extra, 'displayname'),
            'link' => format_goto($urllink . '&sort=displayname', $extra, array('sort'), 'displayname')
        ),
        array(
            'id' => 'logins', 'required' => true,
            'name' => get_string('logins', 'statistics'),
            'class' => format_class($extra, 'count_logins'),
            'link' => format_goto($urllink . '&sort=count_logins', $extra, array('sort'), 'count_logins'),
            'helplink' => get_help_icon('core', 'reports', 'logins', 'count_logins')
        ),
        array(
            'id' => 'activeusers', 'required' => true,
            'name' => get_string('activeusers', 'statistics'),
            'class' => format_class($extra, 'count_active'),
            'link' => format_goto($urllink . '&sort=count_active', $extra, array('sort'), 'count_active'),
            'helplink' => get_help_icon('core', 'reports', 'logins', 'count_active')
        ),
    );
}

/**
 * Create logins by institution layout for the site statistics page
 *
 * @param int $limit     Limit results
 * @param int $offset    Starting offset
 * @param array $extra   Array can contain keys for:
 *                       $sort : Database column to sort by
 *                       $sortdesc : The direction to sort the $sort column by
 *                       $start : The start date to filter results by - format 'YYYY-MM-DD'
 *                       $end : The end date to filter results by - format 'YYYY-MM-DD'
 *                       $institution : The name of institution
 *
 * @results array Results containing the html / pagination data
 */
function institution_logins_statistics($limit, $offset, $extra) {
    $data = array();
    $urllink = get_config('wwwroot') . 'admin/users/statistics.php?type=information&subtype=logins';
    $data['tableheadings'] = logins_statistics_headers($extra, $urllink);

    $activeheadings = get_active_columns($data, $extra);
    $extra['columns'] = array_keys($activeheadings);

    $data['table'] = institution_logins_stats_table($limit, $offset, $extra);
    $data['table']['activeheadings'] = $activeheadings;

    $data['help'] = get_help_icon('core','statistics',null,null,null,'statisticslogins');

    $data['summary'] = $data['table']['count'] == 0 ? get_string('nostats', 'admin') : null;

    return $data;
}

/**
 * Create logins by institution table for the site statistics page
 *
 * @param int $limit     Limit results
 * @param int $offset    Starting offset
 * @param array $extra  - See institution_logins_statistics() for parameters
 *
 * @results array Results containing the html / pagination data
 */
function institution_logins_stats_table($limit, $offset, $extra) {
    global $USER;

    $start = !empty($extra['start']) ? $extra['start'] : null;
    $end = !empty($extra['end']) ? $extra['end'] : date('Y-m-d', strtotime('now'));

    $rawdata = users_active_data($limit, $offset, $extra);
    $count = ($rawdata) ? count($rawdata) : 0;

    $pagination = build_pagination(array(
        'id' => 'stats_pagination',
        'url' => get_config('wwwroot') . 'admin/users/statistics.php?type=information&subtype=logins',
        'jsonscript' => 'admin/users/statistics.json.php',
        'datatable' => 'statistics_table',
        'count' => $count,
        'limit' => $limit,
        'offset' => $offset,
        'setlimit' => true,
        'extradata' => $extra,
    ));

    $result = array(
        'count'         => $count,
        'tablerows'     => '',
        'pagination'    => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
    );
    $result['settings']['start'] = ($start) ? $start : date('Y-m-d', strtotime("-1 months"));
    $result['settings']['end'] = $end;
    if ($count < 1) {
        return $result;
    }

    if (!empty($extra['csvdownload'])) {
        $csvfields = array('name', 'displayname', 'count_logins', 'count_active');
        $USER->set_download_file(generate_csv($rawdata, $csvfields), 'userloginstatistics.csv', 'text/csv');
    }
    $result['csv'] = true;
    $columnkeys = array();
        foreach ($extra['columns'] as $column) {
        $columnkeys[$column] = 1;
    }

    $data = array_slice($rawdata, $offset, $limit);
    $smarty = smarty_core();
    $smarty->assign('data', $data);
    $smarty->assign('columns', $columnkeys);
    $smarty->assign('offset', $offset);
    $result['tablerows'] = $smarty->fetch('admin/userloginsummary.tpl');

    return $result;
}

/**
 * Get records of how many users have their last login fall within a certain time period.
 * Group the results by institution.
 *
 * @param int $limit     Limit results
 * @param int $offset    Starting offset
 * @param array $extra  - See institution_logins_statistics() for parameters
 *
 * @result int $count The total count of 'users per institution' rows
 * @result array $results The count of users per institution
 */
function users_active_data($limit=0, $offset=0, $extra) {
    if (empty($extra['start'])) {
        $extra['start'] = db_format_timestamp(strtotime("-1 months"));
    }
    if (empty($extra['end'])) {
        $extra['end'] = db_format_timestamp(time());
    }

    $sql = "SELECT CASE WHEN i.name IS NOT NULL THEN i.name ELSE 'mahara' END AS name,
            CASE WHEN i.displayname IS NOT NULL THEN i.displayname ELSE 'No institution' END AS displayname,
            COUNT(u.ctime) AS count_logins, COUNT(DISTINCT u.usr) AS count_active
            FROM {usr_login_data} u
            LEFT JOIN {usr_institution} ui ON ui.usr = u.usr
            LEFT JOIN {institution} i ON i.name = ui.institution
            WHERE (u.ctime >= DATE(?) AND u.ctime <= DATE(?))";
    $where = array($extra['start'], $extra['end']);
    if (!empty($extra['institution'])) {
        $sql .= " AND i.name = ?";
        $where[] = $extra['institution'];
    }
    $sql .= " GROUP BY i.name, i.displayname";
    if (!empty($extra['sort'])) {
        $sql .= " ORDER BY " . $extra['sort'] . " " . ($extra['sortdesc'] ? 'DESC' : 'ASC');
    }

    if (!empty($extra['csvdownload'])) {
        $results = get_records_sql_array($sql, $where);
    }
    else {
        $results = get_records_sql_array($sql, $where, $offset, $limit);
    }
    return $results;
}

/**
 * Fetch the site/institution statistics to display in Admin -> Institutions -> Statistics
 * Consolidate code here rather than have it repeated for site vs institution stats
 *
 * @param string $institution  Name of the institution or 'all' for all of them (site stats)
 * @param string $type         Type of report needs to match one of the $allowedtypes within function
 * @param object $extra        Object containing extra parameters needed to return statisics
 *
 * @result array ($allowedtypes, $data) Return - allowedtypes to be used as subpages,
 *                                                               - the data for the subpage from type chosen
 */
function display_statistics($institution, $type, $extra = null) {
    global $USER;

    $subtype = isset($extra->subtype) ? $extra->subtype : null;
    $allowedtypes = array('users', 'groups', 'content', 'information');
    if ($institution == 'all') {
        if (!$USER->get('admin') && !$USER->get('staff')) {
            throw new AccessDeniedException("Institution::statistics | " . get_string('accessdenied', 'auth.webservice'));
        }
        $showall = true;
    }
    else {
        if (!$USER->get('admin') && !$USER->get('staff') && !$USER->is_institutional_admin($institution) && !$USER->is_institutional_staff($institution)) {
            throw new AccessDeniedException("Institution::statistics | " . get_string('accessdenied', 'auth.webservice'));
        }
        $showall = false;
    }

    if (!in_array($type, $allowedtypes)) {
        $type = 'users';
    }

    if ($showall) {
        switch ($type) {
         case 'information':
            if ($subtype == 'comparisons') {
                $data = institution_comparison_statistics($extra->limit, $extra->offset, $extra->extra);
            }
            else if ($subtype == 'logins') {
                $data = institution_logins_statistics($extra->limit, $extra->offset, $extra->extra);
            }
            break;
         case 'content':
            if ($subtype == 'content') {
                $data = content_statistics($extra->limit, $extra->offset, $extra->extra);
            }
            else if ($subtype == 'objectionable') {
                $data = objectionable_statistics($extra->limit, $extra->offset, $extra->extra);
            }
            break;
         case 'groups':
            $data = group_statistics($extra->limit, $extra->offset, $extra->extra);
            break;
         case 'users':
         default:
            if ($subtype == 'accesslist') {
                $data = accesslist_statistics($extra->limit, $extra->offset, $extra->extra, null);
            }
            else if ($subtype == 'masquerading') {
                if (!in_array(get_config('eventloglevel'), array('masq', 'all'))) {
                    $data = array('notvalid_errorstring' => get_string('masqueradingnotloggedwarning', 'admin', get_config('wwwroot')));
                }
                else {
                    $data = masquerading_statistics($extra->limit, $extra->offset, $extra->extra, null);
                }
            }
            else if ($subtype == 'pageactivity') {
                $data = view_statistics($extra->limit, $extra->offset, $extra->extra);
            }
            else if ($subtype == 'useractivity') {
                $data = useractivity_statistics($extra->limit, $extra->offset, $extra->extra, null);
            }
            else if ($subtype == 'userdetails') {
                $data = userdetails_statistics($extra->limit, $extra->offset, $extra->extra, null);
            }
            else if ($subtype == 'useragreement') {
                $data = useragreement_statistics($extra->limit, $extra->offset, $extra->extra, null);
            }
            else if ($subtype == 'collaboration') {
                $data = collaboration_statistics($extra->limit, $extra->offset, $extra->extra, null);
            }
            else {
                $data = user_statistics($extra->limit, $extra->offset, $extra->extra);
            }
        }
    }
    else {
        $institutiondata = institution_statistics($institution, true);
        switch ($type) {
         case 'information':
            if ($subtype == 'comparisons') {
                $data = array('notvalid_errorstring' => get_string('nocomparisondataperinstitution', 'statistics'));
            }
            else if ($subtype == 'logins') {
                $data = array('notvalid_errorstring' => get_string('nologinsdataperinstitution', 'statistics'));
            }
            break;
         case 'content':
            if ($subtype == 'content') {
                $data = institution_content_statistics($extra->limit, $extra->offset, $institutiondata, $extra->extra);
            }
            else if ($subtype == 'objectionable') {
                $data = objectionable_statistics($extra->limit, $extra->offset, $extra->extra, $institution);
            }
            break;
         case 'groups':
                $data = array('notvalid_errorstring' => get_string('nogroupdataperinstitution', 'statistics'));
            break;
         case 'users':
         default:
            if ($subtype == 'accesslist') {
                $data = accesslist_statistics($extra->limit, $extra->offset, $extra->extra, $institution);
            }
            else if ($subtype == 'masquerading') {
                if (!in_array(get_config('eventloglevel'), array('masq', 'all'))) {
                    $data = array('notvalid_errorstring' => get_string('masqueradingnotloggedwarning', 'admin', get_config('wwwroot')));
                }
                else {
                    $data = masquerading_statistics($extra->limit, $extra->offset, $extra->extra, $institution);
                }
            }
            else if ($subtype == 'pageactivity') {
                $data = institution_view_statistics($extra->limit, $extra->offset, $institutiondata, $extra->extra);
            }
            else if ($subtype == 'useractivity') {
                $data = useractivity_statistics($extra->limit, $extra->offset, $extra->extra, $institution);
            }
            else if ($subtype == 'userdetails') {
                $data = userdetails_statistics($extra->limit, $extra->offset, $extra->extra, $institution);
            }
            else if ($subtype == 'useragreement') {
                $data = useragreement_statistics($extra->limit, $extra->offset, $extra->extra, $institution);
            }
            else if ($subtype == 'collaboration') {
                $data = collaboration_statistics($extra->limit, $extra->offset, $extra->extra, $institution);
            }
            else {
                $data = institution_user_statistics($extra->limit, $extra->offset, $institutiondata, $extra->extra);
            }
        }
    }

    return array($allowedtypes, $data);
}

/**
 * Return the form array for the config report modal
 * That we can turn into a pieform object
 *
 * @param object $extra  The parameters in play with the reports
 * @param array  $institutionelement  The pieform ready array for the institution element
 *
 * @return $form        A pieform structured form array
 */
function report_config_form($extra, $institutionelement) {
    global $USER;

    $type = isset($extra->type) ? $extra->type : null;
    $subtype = isset($extra->subtype) ? $extra->subtype : $type;
    $institution = isset($extra->institution) ? $extra->institution : null;

    if (!$institution || !$USER->can_edit_institution($institution, true)) {
        $institution = empty($institutionelement['value']) ? $institutionelement['defaultvalue'] : $institutionelement['value'];
    }
    else if (!empty($institution)) {
        $institutionelement['defaultvalue'] = $institution;
    }
    // make it a select2 element
    $institutionelement['isSelect2'] = true;

    $form = array(
        'name'            => 'reportconfigform',
        'method'          => 'post',
        'plugintype'      => 'core',
        'pluginname'      => 'admin',
        'renderer'        => 'div',
        'class'           => 'form-as-button float-left',
        'elements'   => array(
            'type' => array(
                'type' => 'hidden',
                'value' => $type,
            ),
            'subtype' => array(
                'type' => 'hidden',
                'value' => $subtype,
            ),
            'institution' => $institutionelement,
        )
    );

    if (!empty($extra->extra) && isset($extra->extra['users'])) {
        $form['elements']['users'] = array(
            'type'     => 'hidden',
            'value'    => (array)$extra->extra['users'],
        );
    }

    $typesubtypes = get_report_types($institution);
    if (!isset($typesubtypes[$type]['options'][$type . '_' . $subtype])) {
        // This can happen when switching from 'all institutions' to a particular institution
        // where the allowed report options are different. So default back to overview page.
        $type = 'information';
        $subtype = 'information';
    }
    $form['elements']['typesubtype'] = array(
        'type' => 'select',
        'title' => get_string('reporttype', 'statistics'),
        'defaultvalue' => ($type . '_' . $subtype),
        'optgroups' => $typesubtypes,
    );
    $form['elements']['start'] = array(
        'type' => 'calendar',
        'title' => get_string('From') . ':',
        'class' => 'form-inline in-modal',
        'defaultvalue' => !empty($extra->extra) && isset($extra->extra['start']) ? strtotime($extra->extra['start']) : strtotime('-1 month'),
        'caloptions' => array(
            'showsTime' => false,
        ),
    );
    $form['elements']['end'] = array(
        'type' => 'calendar',
        'title' => get_string('To') . ':',
        'class' => 'form-inline in-modal',
        'defaultvalue' => !empty($extra->extra) && isset($extra->extra['end']) ? strtotime($extra->extra['end']) : strtotime('now'),
        'caloptions' => array(
            'showsTime' => false,
        ),
    );

    $data = array();
    $function = $subtype . '_statistics_headers';
    if (function_exists($function)) {
        $data['tableheadings'] = $function($extra->extra, null);
        $activeheadings = get_active_columns($data, $extra->extra);
        $headerelements = array();
        foreach ($data['tableheadings'] as $heading) {
            $disabled = isset($heading['disabled']) && !empty($heading['disabled']) ? true : false;
            $disabled = $disabled ? true : (!empty($heading['required']) ? true : false);
            $headerelements['report_column_' . $heading['id']] = array(
                'type' => 'checkbox',
                'title' => $heading['name'],
                'readonly' => $disabled,
                'defaultvalue' => (!empty($heading['required']) || !empty($heading['selected']) ? $heading['id'] : null),
            );
        }
        if (!empty($headerelements)) {
            $form['elements']['inputgroup'] = array (
                'type' => 'fieldset',
                'class' => 'last',
                'elements' => $headerelements,
                'legend' => get_string('Columns', 'admin'),
                'collapsible' => true,
                'collapsed'   => true,
            );
        }
    }

    $form['elements']['submit'] = array(
        'type'  => 'submitcancel',
        'class' => 'btn-primary',
        'value' => array(get_string('submit'), get_string('cancel')),
        'goto'  => format_goto(get_config('wwwroot') . 'admin/users/statistics.php?institution=' . $institution, $extra->extra, array('sort', 'sortdesc')),
    );

    return $form;
}

function format_goto($url, $data, $ignore=array(), $currentsort=null) {
    static $allowed_keys = array('id', 'start', 'end', 'users', 'sort', 'sortdesc');

    if (strpos($url, '?') === false) {
        $firstjoin = '?';
    }
    else {
        $firstjoin = '&';
    }

    if (is_array($data)) {
        $count = 0;
        foreach ($data as $key => $value) {
            // To allow the resorting of the columns
            if ($key == 'sortdesc') {
               if (isset($data['sort']) && $data['sort'] == $currentsort) {
                   $value = !$value;
               }
               else {
                   $value = true;
               }
            }

            if (in_array($key, $allowed_keys) && !empty($value) && !in_array($key, $ignore)) {
                if (is_object($value)) {
                    $value = (array)$value;
                }
                if (is_array($value)) {
                    foreach ($value as $k => $v) {
                        if ($count < 1) {
                            $url .= $firstjoin . hsc($key) . '=' . hsc($v);
                        }
                        else {
                            $url .= '&' . hsc($key) . '=' . hsc($v);
                        }
                        $count++;
                    }
                }
                else {
                    if ($count < 1) {
                        $url .= $firstjoin . hsc($key) . '=' . hsc($value);
                    }
                    else {
                        $url .= '&' . hsc($key) . '=' . hsc($value);
                    }
                    $count++;
                }
            }
        }
    }

    return $url;
}

/**
 * To generate the correct class for the sortable column heading
 *
 * @param   array   $extra        Array containing current sort and sortdesc information
 * @param   string  $column       The column the class info is to generated for
 * @param   string  $class        Pass in any extra classes needed
 *
 * @return  string  $class        Containing the CSS classes
 */
function format_class($extra, $column, $class = 'search-results-sort-column') {
    if (isset($extra['sort']) && $extra['sort'] == $column) {
        if (isset($extra['sortdesc']) && !empty($extra['sortdesc'])) {
            $class .= ' desc';
        }
        else {
            $class .= ' asc';
        }
    }

    return $class;
}

function reportconfigform_cancel_submit(Pieform $form) {
    $submitelement = $form->get_element('submit');
    redirect($submitelement['goto']);
}

function reportconfigform_submit(Pieform $form, $values) {
    global $SESSION;

    $submitelement = $form->get_element('submit');
    // Get the type/subtype values from select field
    list($type, $subtype) = explode('_', $values['typesubtype']);
    $submitelement['goto'] .= '&type=' . $type . '&subtype=' . $subtype;

    $SESSION->set('columnsforstats', null);
    $extra = array();
    foreach ($values as $k => $v) {
        if (preg_match('/report_column_(.*)/', $k, $matches) && !empty($v)) {
            $extra['columns'][] = $matches[1];
        }
    }

    $data = array();
    $function = $subtype . '_statistics_headers';
    if (function_exists($function)) {
        $data['tableheadings'] = $function(null, null);
        $activeheadings = get_active_columns($data, $extra);
        $SESSION->set('columnsforstats', array_keys($activeheadings));
    }

    $form->reply(PIEFORM_OK, array(
        'message' => get_string('applyingfilters', 'statistics'),
        'goto' => $submitelement['goto'],
        )
    );
}

/**
 * Get report types/subtypes array for select field
 *
 * @param   string  $institution  The institution for the report, can be 'all'
 * @return  array   $optgroups    The select options grouped by report type
 */
function get_report_types($institution = null) {
    global $USER;

    // Get correct subtypes for 'information' type
    if (!empty($institution) && $institution != 'all') {
        $infooptions = array('information_information' => get_string('Overview', 'statistics'));
    }
    else {
        $infooptions = array('information_information' => get_string('Overview', 'statistics'),
                             'information_comparisons' => get_string('reportinstitutioncomparison', 'statistics'),
                             'information_logins' => get_string('logins', 'statistics'));
    }
    asort($infooptions);

    // Get correct subtypes for 'users' type
    $usersoptions = array(
        'users_users' => get_string('peoplereports', 'statistics'),
        'users_pageactivity' => get_string('reportpageactivity', 'statistics'),
        'users_accesslist' => get_string('reportaccesslist', 'statistics'),
        'users_masquerading' => get_string('reportmasquerading', 'statistics'),
        'users_userdetails' => get_string('reportuserdetails', 'statistics'),
        'users_useragreement' => get_string('reportuseragreement', 'statistics'),
    );
    if (get_config('eventlogenhancedsearch')) {
        $advancedoptions = array(
            'users_collaboration' => get_string('reportcollaboration', 'statistics'),
            'users_useractivity' => get_string('reportuseractivity', 'statistics'),
        );
        $usersoptions = array_merge($usersoptions, $advancedoptions);
    }
    asort($usersoptions);

    $optgroups = array(
        'content' => array(
            'label' => get_string('Content', 'admin'),
            'options' => array(
                'content_content' => get_string('Content', 'admin'),
                'content_objectionable' => get_string('objectionable', 'admin')
            ),
        ),
        'information' => array(
            'label' => get_string('Institution', 'admin'),
            'options' => $infooptions,
        ),
        'users' => array(
            'label' => get_string('People', 'admin'),
            'options' => $usersoptions,
        ),
    );

    if (empty($institution) || $institution == 'all') {
        $optgroups['groups'] = array(
            'label' => get_string('Groups', 'admin'),
            'options' => array('groups_groups' => get_string('Groups', 'admin')),
        );
    }

    // But ignore $optgroups above if $USER is only institution staff and only allowed to see old user related reports
    if (!empty($institution)) {
        if (!$USER->get('admin') && !$USER->is_institutional_admin($institution) &&
            $USER->is_institutional_staff($institution) && empty(get_config('staffstats')) && !empty(get_config('staffreports'))) {
            $usersoptions = array(
                'users_accesslist' => get_string('reportaccesslist', 'statistics'),
                'users_masquerading' => get_string('reportmasquerading', 'statistics'),
                'users_userdetails' => get_string('reportuserdetails', 'statistics'),
                'users_useragreement' => get_string('reportuseragreement', 'statistics'),
            );
            asort($usersoptions);
            $optgroups = array(
                'users' => array(
                    'label' => get_string('People', 'admin'),
                    'options' => $usersoptions,
                ),
            );
        }
    }

    asort($optgroups);
    return $optgroups;
}

/**
 * Get report settings string to display what reportform settings are in play
 */
function get_report_settings($settings) {
    $str = '';
    if (!empty($settings['start'])) {
        $str .= "<div>";
        $str .= get_string('timeperiod', 'statistics') . format_date(strtotime($settings['start']), 'strftimedate');
        if (!empty($settings['end'])) {
            $str .= ' - ';
            $str .= format_date(strtotime($settings['end']), 'strftimedate');
        }
        $str .= "</div>\n";
    }
    if (!empty($settings['users'])) {
        $str .= "<div>";
        $str .= get_string('selectednusers', 'admin', $settings['users']);
        $str .= ' <button class="btn btn-secondary filter" id="removeuserfilter" title="' . get_string('removefilter', 'statistics') . '">
                     <span class="times">×</span>
                     <span class="sr-only">' . get_string('removefilter', 'statistics') . '</span>
                 </button>';
        $str .= "</div>\n";
    }
    return $str;
}

function userhasaccess($institution, $report) {
    global $USER;
    if ($USER->get('admin') || $USER->is_institutional_admin($institution)) {
        return true;
    }
    if ($USER->is_institutional_staff($institution) && !empty(get_config('staffstats'))) {
        return true;
    }

    if ($USER->is_institutional_staff($institution) && empty(get_config('staffstats')) && !empty(get_config('staffreports'))) {
        if (in_array($report, array('accesslist', 'masquerading', 'userdetails'))) {
            return true;
        }
    }

    $smarty = smarty();
    $smarty->assign('CANCREATEINST', $USER->get('admin'));
    $smarty->display('admin/users/noinstitutionsstats.tpl');
    exit;
}

function report_earliest_date($subtype, $institution = 'mahara') {
    // A quick way to find possible earliest dates for things

    // This check accepts the fact that 'mahara' institution must exist first
    // therefore the earliest for 'mahara' must be the earliest for 'all' for many of the reports
    $all = false;
    if ($institution == 'all') {
        $institution = 'mahara';
        $all = true;
    }
    switch ($subtype) {
        case "content":
            $date = get_field_sql("SELECT MIN(i.time) FROM {institution_registration} i WHERE i.institution = ?", array($institution));
            break;
        case "objectionable":
            $date = get_field_sql("SELECT MIN(o.reportedtime) FROM {objectionable} o WHERE o.resolvedtime IS NULL");
            break;
        case "groups":
            $date = get_field_sql("SELECT MIN(ctime) FROM {group}");
            break;
        case "logins":
            $date = get_field_sql("SELECT MIN(ctime) FROM {usr_login_data}");
            break;
        case "collaboration":
            if ($institution != 'mahara') {
                // base it on when first member joined institution
                $date = get_field_sql("SELECT MIN(ctime) FROM {usr_institution} WHERE institution = ?", array($institution));
            }
            else {
                $date = get_field_sql("SELECT MIN(ctime) FROM {view_access}");
            }
            break;
        case "masquerading":
            if ($institution != 'mahara') {
                $date = get_field_sql("SELECT MIN(el.ctime) FROM {event_log} el
                                       JOIN {usr_institution} ui ON ui.usr = el.usr
                                       WHERE el.event = 'loginas' AND ui.institution = ?", array($institution));
            }
            else {
                if ($all) {
                    $date = get_field_sql("SELECT MIN(el.ctime) FROM {event_log} el
                                           WHERE el.event = 'loginas'");
                }
                else {
                    $date = get_field_sql("SELECT MIN(el.ctime) FROM {event_log} el
                                           WHERE el.event = 'loginas' AND el.usr NOT IN (
                                               SELECT usr FROM {usr_institution}
                                           )");
                }
            }
            break;
        case "useractivity":
            if ($institution != 'mahara') {
                $date = get_field_sql("SELECT MIN(el.ctime) FROM {event_log} el
                                       JOIN {usr_institution} ui ON ui.usr = el.usr
                                       WHERE el.event != 'loginas' AND ui.institution = ?", array($institution));
            }
            else {
                if ($all) {
                    $date = get_field_sql("SELECT MIN(el.ctime) FROM {event_log} el
                                           WHERE el.event != 'loginas'");
                }
                else {
                    $date = get_field_sql("SELECT MIN(el.ctime) FROM {event_log} el
                                           WHERE el.event != 'loginas' AND el.usr NOT IN (
                                               SELECT usr FROM {usr_institution}
                                           )");
                }
            }
            break;
        case "pageactivity":
        case "accesslist":
            if ($institution != 'mahara') {
                $date = get_field_sql("SELECT MIN(v.ctime) FROM {view} v
                                       JOIN {usr_institution} ui ON ui.usr = v.owner
                                       WHERE ui.institution = ?", array($institution));
            }
            else {
                $date = get_field_sql("SELECT MIN(v.ctime) FROM {view} v");
            }
            break;
        case "users":
            $date = get_field_sql("SELECT MIN(ctime) FROM {institution_data}
                                   WHERE institution = ?", array($institution));
            break;
        case "userdetails":
        case "useragreement":
        case "comparisons":
        default:
            if ($institution != 'mahara') {
                $date = get_field_sql("SELECT MIN(ctime) FROM {usr_institution} WHERE institution = ?", array($institution));
            }
            else {
                $date = get_field_sql("SELECT MIN(ctime) FROM {usr}");
            }
            break;
    }
    if (!$date) {
        return false;
    }
    return format_date(strtotime($date), 'strftimedate');
}
