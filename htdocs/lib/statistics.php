<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
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
            $download_page = 'https://www.mahara.org/download';
            $data['strlatestversion'] = get_string('latestversionis', 'admin', $download_page, $latestversion);
        }
    }
    if ($branchlatest = get_config('latest_branch_version')) {
        if ($data['release'] != $branchlatest) {
            $download_page = 'https://www.mahara.org/download';
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
        $maxfriends = !empty($maxfriends[0]) ? $maxfriends[0] : false;
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
        $maxviews = !empty($maxviews[0]) ? $maxviews[0] : false;
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
        $maxgroups = !empty($maxgroups[0]) ? $maxgroups[0] : false;
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
        $maxquotaused = !empty($maxquotaused[0]) ? $maxquotaused[0] : false;
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
        $maxblocktypes = 1;
        $blocktypecounts = get_records_sql_array("
            SELECT
                b.blocktype,
                COUNT(b.id) AS blocks
            FROM {block_instance} b
            JOIN {blocktype_installed} bi ON (b.blocktype = bi.name)
            JOIN {view} v ON (b.view = v.id AND v.type = 'portfolio')
            WHERE bi.active = 1
            GROUP BY b.blocktype
            ORDER BY blocks DESC",
            array(), 0, $maxblocktypes
        );
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

function institution_data_verifier_current($institution) {
    $current = institution_data_current($institution);

    // How many portfolios are there
    $data['verifierportfolios'] = count_records_sql("SELECT COUNT(*) AS count FROM {collection_template} ct
                                                     JOIN {collection} c ON c.id = ct.collection
                                                     WHERE c.owner IN (" . $current['memberssql'] . ")", $current['memberssqlparams']);

    // How many of those collections currently have a verifier assigned
    $data['verifierportfolios-verifier-count'] = count_records_sql("SELECT COUNT(*) FROM {collection_template} ct
                                                                    JOIN {collection} c ON c.id = ct.collection
                                                                    JOIN {collection_view} cv ON cv.collection = c.id
                                                                    JOIN {view} v ON v.id = cv.view
                                                                    JOIN {view_access} va ON va.view = v.id
                                                                    WHERE c.owner IN (" . $current['memberssql'] . ")
                                                                    AND v.type = 'progress' AND va.role = 'verifier'", $current['memberssqlparams']);

    // How many portfolios each verifier has
    $portfoliosperverifier = get_records_sql_array("SELECT va.usr, ct.originaltemplate, COUNT(*) AS count FROM {collection_template} ct
                                                    JOIN {collection} c ON c.id = ct.collection
                                                    JOIN {collection_view} cv ON cv.collection = c.id
                                                    JOIN {view} v ON v.id = cv.view
                                                    JOIN {view_access} va ON va.view = v.id
                                                    WHERE c.owner IN (" . $current['memberssql'] . ")
                                                    AND v.type = 'progress' AND va.role = 'verifier'
                                                    GROUP BY va.usr, ct.originaltemplate
                                                    ORDER BY COUNT(*)", $current['memberssqlparams']);

    // How many people have a copy of which template
    $ownerspertemplate = get_records_sql_array("SELECT COUNT(ct.originaltemplate) AS count, ct.originaltemplate
                                                FROM {collection_template} ct
                                                JOIN {collection} c on c.id = ct.collection
                                                WHERE c.owner IN (" . $current['memberssql'] . ")
                                                GROUP BY ct.originaltemplate", $current['memberssqlparams']);
    if ($ownerspertemplate) {
        foreach($ownerspertemplate as $item) {
            $data['owners-per-template_' . $item->originaltemplate] = $item->count;
        }
    }

    if ($portfoliosperverifier) {
        foreach ($portfoliosperverifier as $value) {
            if (!isset($data['verifierportfolios-verifier-count_' . $value->originaltemplate])) {
                $data['verifierportfolios-verifier-count_' . $value->originaltemplate] = 0;
            }
            // template count total
            if ($value->count > 0) {
                $data['verifierportfolios-verifier-count_' . $value->originaltemplate] += $value->count;
                if ($value->count < 10) {
                    if (!isset($data['verifierportfolios-verifier-load-' . $value->count . '_' . $value->originaltemplate])) {
                        $data['verifierportfolios-verifier-load-' . $value->count . '_' . $value->originaltemplate] = 0;
                    }
                    $data['verifierportfolios-verifier-load-' . $value->count . '_' . $value->originaltemplate] += 1;
                }
                else {
                    if (!isset($data['verifierportfolios-verifier-load-10' . '_' . $value->originaltemplate ])) {
                        $data['verifierportfolios-verifier-load-10' . '_' . $value->originaltemplate ] = 0;
                    }
                    $data['verifierportfolios-verifier-load-10' . '_' . $value->originaltemplate ] += 1;
                }
            }
        }
    }
    return $data;
}

function institution_statistics($institution, $full=false) {
    global $SESSION;
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
            $maxfriends = !empty($maxfriends[0]) ? $maxfriends[0] : false;
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
            $maxviews = !empty($maxviews[0]) ? $maxviews[0] : false;
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
            $maxgroups = !empty($maxgroups[0]) ? $maxgroups[0] : false;
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
            $maxquotaused = !empty($maxquotaused[0]) ? $maxquotaused[0] : false;
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

        // Verifier for institution graph
        $verifierinfo = institution_data_verifier_current($institution);
        $smarty = smarty_core();
        $smarty->assign('institution', $institution);
        $smarty->assign('verifiertotal', $verifierinfo['verifierportfolios-verifier-count']);
        if ($portfolios = $SESSION->get('portfoliofilter')) {
            $btnstr = ' <button class="btn btn-secondary filter" id="removeportfoliofilter" title="' . get_string('removefilter', 'statistics') . '">
                            <span class="times">×</span>
                            <span class="visually-hidden">' . get_string('removefilter', 'statistics') . '</span>
                        </button>';
            $smarty->assign('verifierportfolios', get_string('countportfolios', 'admin', count($portfolios)) . $btnstr);
        }
        $data['verifierinfo'] = $smarty->fetch('admin/institutionverifierstatssummary.tpl');

        // Display number of current verifiers over time for the institution
        $weeks = count_records_sql("SELECT COUNT(*)
                           FROM (
                               SELECT COUNT(distinct ctime)
                               FROM {institution_data}
                               WHERE type LIKE 'owners-per-template%'
                               GROUP BY ctime
                           ) AS weeks");
        if ($weeks && $weeks >= 4) {
            $data['currentverifiersinfo'] = $smarty->fetch('admin/institutioncurrentverifiersstatssummary.tpl');
        }

        // Verifier load for institution graph
        $smarty = smarty_core();
        $smarty->assign('institution', $institution);
        $smarty->assign('verifiertotal', $verifierinfo['verifierportfolios-verifier-count']);
        if ($portfolios = $SESSION->get('portfoliofilter')) {
            $btnstr = ' <button class="btn btn-secondary filter" id="removeportfoliofilter" title="' . get_string('removefilter', 'statistics') . '">
                            <span class="times">×</span>
                            <span class="visually-hidden">' . get_string('removefilter', 'statistics') . '</span>
                        </button>';
            $smarty->assign('verifierportfolios', get_string('countportfolios', 'admin', count($portfolios)) . $btnstr);
        }
        $data['verifierloadinfo'] = $smarty->fetch('admin/institutionverifierloadsummary.tpl');
    }

    return($data);
}

function institution_verifier_graph_render($type = null, $extradata=null) {
    global $SESSION;

    $data['graph'] = ($type) ? $type : 'pie';
    $verifierinfo = institution_data_verifier_current($extradata->institution);
    $dataarray = array('unallocated' => 0,
                       'allocated' => 0);
    if ($portfolios = $SESSION->get('portfoliofilter')) {
        $subdataarray = array();
        foreach ($verifierinfo as $vi => $v) {
            if (preg_match('/\_(\d+)$/', $vi, $match)) {
                if ($match[1] && in_array($match[1], $portfolios) && $template = get_field('collection', 'name', 'id', $match[1])) {
                    $subdataarray[$match[1]]['unallocated'] = count_records('collection_template', 'originaltemplate', $match[1]) - (int)$verifierinfo['verifierportfolios-verifier-count_' . $match[1]];
                    $subdataarray[$match[1]]['allocated'] = $verifierinfo['verifierportfolios-verifier-count_' . $match[1]];
                }
            }
        }
        // Now we have totals per template we need to aggregate them
        foreach ($subdataarray as $sk => $sv) {
            $dataarray['unallocated'] += $sv['unallocated'];
            $dataarray['allocated'] += $sv['allocated'];
        }
    }
    else {
        $dataarray['unallocated'] = (int)$verifierinfo['verifierportfolios'] - (int)$verifierinfo['verifierportfolios-verifier-count'];
        $dataarray['allocated'] = (int)$verifierinfo['verifierportfolios-verifier-count'];
    }

    $data['graph_function_name'] = 'institution_verifier_type_graph';
    $data['title'] = get_string('verifierpercentage', 'admin');
    $data['labels'] = array(get_string('unallocated', 'admin'), get_string('allocated', 'admin'));
    $data['data'] = $dataarray;
    if ($dataarray) {
        require_once(get_config('libroot') . 'graph.php');
        $graphdata = get_circular_graph_json($data, null, true);
        $data['jsondata'] = json_encode($graphdata[0]);
    }
    return $data;
}

function institution_current_verifiers_graph_render($type = null, $extradata=null) {
    global $SESSION;

    $data['graph'] = ($type) ? $type : 'line';
    $institution = isset($extradata->institution) ? $extradata->institution : 'all';
    $start = get_field_sql("SELECT MIN(ctime)::date FROM {institution_data} WHERE type = 'verifierportfolios'");
    $end = date('Y-m-d', strtotime('today'));
    $portfolios = $SESSION->get('portfoliofilter');
    $extra = array(
        'start' => $start,
        'end' => $end,
        'portfoliofilter' => $portfolios,
        'columns' => array(
            0 => 'date',
            1 => 'hasverifier',
            2 => 'noverifier'
        ),
    );

    $totalrecords = array();
    $percentrecords = array();
    $labels = array();
    if ($results = portfolioswithverifiers_statistics(10, 0, $extra, $institution)) {
        if ($results['table']['rawdata']) {
            $count = 0;
            foreach ($results['table']['rawdata'] as $result => $r) {
                if ($count % 4 || $count == (sizeof($results['table']['rawdata']) -1)) {
                    $labels[] = $result;
                    $totalrecords[$result] = $r->hasverifier + $r->noverifier;
                    if ($totalrecords[$result] > 0) {
                        $percentrecords[$result] = round(($r->hasverifier / ($r->hasverifier + $r->noverifier)) * 100, 2);
                    }
                }
                $count++;
            }
        }
    }

    $data['graph_function_name'] = 'institution_current_verifiers_type_graph';
    $data['title'] = get_string('currentverifiersovertime', 'admin');
    $data['labels'] = $labels;
    $data['labellang'] = 'collection';
    $data['data'] = array('progressportfolios' => $totalrecords,
                          'progressverifiers' => $percentrecords);
    $data['yaxis'] = array('y-axis-1', 'y-axis-2');
    $data['yaxes'] = '2yaxes';

    if (!empty($results)) {
        require_once(get_config('libroot') . 'graph.php');
        $graphdata = get_line_graph_json($data, null, true);
        $data['jsondata'] = json_encode($graphdata[0]);
    }
    return $data;
}

function institution_verifier_load_graph_render($type = null, $extradata=null) {
    global $SESSION;

    $data['graph'] = ($type) ? $type : 'pie';
    $verifierinfo = institution_data_verifier_current($extradata->institution);
    $dataarray = array(1 => 0,
                       2 => 0,
                       3 => 0,
                       4 => 0,
                       5 => 0,
                       6 => 0,
                       7 => 0,
                       8 => 0,
                       9 => 0,
                       10 => 0);

    if ($portfolios = $SESSION->get('portfoliofilter')) {
        $subdataarray = array();
        foreach ($portfolios as $portfolio) {
            for ($i = 1; $i <= 10; $i++) {
                if (isset($verifierinfo['verifierportfolios-verifier-load-' . $i . '_' . $portfolio])) {
                    $subdataarray[$portfolio][$i] = $verifierinfo['verifierportfolios-verifier-load-' . $i . '_' . $portfolio];
                }
            }
        }
        // Now we have totals per template we need to aggregate them
        foreach ($subdataarray as $sk => $sv) {
            for ($i = 1; $i <= 10; $i++) {
                if (isset($sv[$i])) {
                    $dataarray[$i] += $sv[$i];
                }
            }
        }
    }
    else {
        foreach ($verifierinfo as $ikey => $item) {
            for ($i = 1; $i <= 10; $i++) {
                if (preg_match('/^verifierportfolios-verifier-load-' . $i . '_/', $ikey)) {
                    $dataarray[$i] += $item;
                }
            }
        }
    }

    $data['graph_function_name'] = 'institution_verifier_load_type_graph';
    $data['title'] = get_string('verifierload', 'admin');
    $data['labels'] = array(get_string('one', 'statistics'),
                            get_string('two', 'statistics'),
                            get_string('three', 'statistics'),
                            get_string('four', 'statistics'),
                            get_string('five', 'statistics'),
                            get_string('six', 'statistics'),
                            get_string('seven', 'statistics'),
                            get_string('eight', 'statistics'),
                            get_string('nine', 'statistics'),
                            get_string('tenormore', 'statistics'));
    $data['data'] = $dataarray;

    require_once(get_config('libroot') . 'graph.php');
    $graphdata = get_circular_graph_json($data, null, true);
    $data['jsondata'] = json_encode($graphdata[0]);

    return $data;
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
        // Make sure the required ones are always selected
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
    $end = !empty($extra['end']) ? $extra['end'] : date('Y-m-d', strtotime('+1 day'));
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
        $item->lastlogin = $item->lastlogin ? format_date(strtotime($item->lastlogin)) : '';
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
        // Make the lastlogin a data friendly value.
        for ($i = 0; $i < count($data); $i++) {
            if (!empty($data[$i]->lastlogin)) {
                $data[$i]->lastlogin = format_date(strtotime($data[$i]->lastlogin), 'strftimew3cdatetime');
            }
        }
        $USER->set_download_file(generate_csv($data, $csvfields), $institution . 'userdetailsstatistics.csv', 'text/csv', true);
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
    $end = !empty($extra['end']) ? $extra['end'] : date('Y-m-d', strtotime('+1 day'));
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
        $USER->set_download_file(generate_csv($data, $csvfields), $institution . 'useragreementsstatistics.csv', 'text/csv', true);
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

/**
 * @param int $limit                 How many results to return.
 * @param int $offset                Where to start.
 * @param array<string,mixed> $extra Extra search parameters.
 * @param string|null $institution   The Instituion key, or null.
 * @param string $urllink            The base URL for links in the results.
 *
 * @return array<string,mixed> The search results and supporting elements.
 */
function useractivity_stats_table($limit, $offset, $extra, $institution, $urllink) {
    global $USER, $SESSION;

    $start = !empty($extra['start']) ? $extra['start'] : null;
    $end = !empty($extra['end']) ? $extra['end'] : date('Y-m-d', strtotime('+1 day'));
    $users = $SESSION->get('usersforstats');
    $aggregates = [];
    $aggmap = [];

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

    // @TODO: these $sortorder values are scripts. These differ between ES6
    // and ES7.  We're going to have to rework how these are handled.
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

    // Add in the search data if needed.
    if ($search_class = does_search_plugin_have('report_useractivity_stats_table')) {
        $search_result = $search_class::report_useractivity_stats_table($usrids, $result, $sortdirection, $sortdesc, $sortorder, $sortname, $count);
        if (!empty($search_result)) {
            $aggmap = $search_result[0];
            $aggregates = $search_result[1];
        }
    }

    $data = array();
    // Get timezone we are in.
    $timezone = new DateTimeZone(date_default_timezone_get());
    // Work out offset in seconds.
    $offsettime = $timezone->getOffset(new DateTime("now"));

    $have_results = false;

    // Allow for the differences between ES6 and ES7.
    if (array_key_exists('totalresults', $aggregates)) {
        if (array_key_exists('value', $aggregates['totalresults'])) {
            $have_results = ($aggregates['totalresults']['value'] > 0);
        }
        else {
            $have_results = ($aggregates['totalresults'] > 0);
        }
    }
    if ($have_results) {
        foreach ($aggregates['aggregations']['UsrId']['buckets'] as $item) {
            // Convert from UTC milliseconds.
            $date = $item['LastLogin']['value'] / 1000;
            if ($offsettime < 0) {
                $date += $offsettime;
            }
            if ($offsettime > 0) {
                $date -= $offsettime;
            }

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
            $obj->lastlogin = $item['LastLogin']['value'] ? date('d F Y, H:i a', $date) : '';
            $obj->actions = $item['doc_count'];
            $data[] = $obj;
        }
    }
    if (empty($extra['csvdownload'])) {
        $data = array_slice($data, $offset, $limit, true);
    }

    if (!empty($extra['csvdownload'])) {
        $csvfields = [
            'firstname',
            'lastname',
            'displayname',
            'username',
            'artefacts',
            'pages',
            'collections',
            'groups',
            'logins',
            'actions',
            'lastlogin',
            'lastactivity',
        ];
        $csv_string = generate_csv($data, $csvfields);
        $csv_filename = $institution . 'useractivitystatistics.csv';
        $USER->set_download_file($csv_string, $csv_filename, 'text/csv', true);
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

/**
 * Returns the Collaboration Stats report.
 *
 * @param int $limit
 * @param int $offset
 * @param array<string,mixed> $extra
 * @param string|null $institution
 * @param string $urllink
 *
 * @return array<string,mixed>
 */
function collaboration_stats_table($limit, $offset, $extra, $institution, $urllink) {
    global $USER;

    $start = !empty($extra['start']) ? $extra['start'] : date('Y-m-d', strtotime("-1 months"));
    $end = !empty($extra['end']) ? $extra['end'] : date('Y-m-d', strtotime('+1 day'));
    $from = strtotime($start);
    $to = strtotime($end);
    $daterange = [];
    $usrids = [];
    $aggmap = [];
    while ($from < $to) {
        $daterange[date("Y_W", $from)] = date('Y-m-d', $from);
        // Break down the range by weeks.
        $from = $from + (7 * 24 * 60 * 60);
    }
    $daterange[date("Y_W", $to)] = date('Y-m-d', $to);

    $count = count($daterange);

    $pagination = build_pagination([
        'id' => 'stats_pagination',
        'url' => $urllink,
        'jsonscript' => 'admin/users/statistics.json.php',
        'datatable' => 'statistics_table',
        'count' => $count,
        'limit' => $limit,
        'offset' => $offset,
        'setlimit' => true,
        'extradata' => $extra,
    ]);

    $result = [
        'count'         => $count,
        'tablerows'     => '',
        'pagination'    => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
    ];

    $result['settings']['start'] = ($start) ? $start : null;
    $result['settings']['end'] = $end;
    if ($count < 1) {
        return $result;
    }

    $sorttype = !empty($extra['sort']) ? $extra['sort'] : '';

    // Add in the data from search if available.
    if ($search_class = does_search_plugin_have('report_collaboration_stats_table')) {
        if ($institution) {
            // restrict results to users from the institution
            if ($institution == 'mahara') {
                $usrids = get_records_sql_assoc(
                    "
                    SELECT u.id, u.username FROM {usr} u
                    LEFT JOIN {usr_institution} ui ON ui.usr = u.id
                    JOIN {event_log} el ON el.usr = u.id
                    WHERE ui.institution IS NULL
                        AND el.event = 'login'
                        AND el.ctime >= DATE(?) AND el.ctime <= DATE(?)
                    GROUP BY u.id
                    ",
                    [$start, $end]
                );
            }
            else {
                $usrids = get_records_sql_assoc(
                    "
                    SELECT u.id, u.username FROM {usr} u
                    JOIN {usr_institution} ui ON ui.usr = u.id
                    JOIN {event_log} el ON el.usr = u.id
                    WHERE ui.institution = ?
                        AND el.event = 'login'
                        AND el.ctime >= DATE(?) AND el.ctime <= DATE(?)
                    GROUP BY u.id
                    ",
                    [$institution, $start, $end]
                );
            }
            if (empty($usrids)) {
                $result['pagination'] = null;
                return $result;
            }
            else {
                $usrids = array_keys($usrids);
            }
        }
        list($aggmap, $aggregates) = $search_class::report_collaboration_stats_table($usrids, $start, $end);
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
        $obj->date = get_string('weekstartdate', 'statistics', format_date(strtotime($year . "W" . $week . '1'), 'strfdaymonthyearshort'));
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
        $csvfields = [
            'date',
            'comments',
            'annotations',
            'usershare',
            'groupshare',
            'institutionshare',
            'loggedinshare',
            'publicshare',
            'secretshare',
            'friendshare',
        ];
        $csv_data = generate_csv($data, $csvfields);
        $csv_filename = $institution . 'collaborationstatistics.csv';
        $USER->set_download_file($csv_data, $csv_filename, 'text/csv', true);
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

function completionverification_statistics_headers($extra, $urllink) {
    return array(
        array('id' => 'rownum', 'name' => '#'),
        array(
              'id' => 'firstname',
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
              'id' => 'registration_number', 'required' => true,
              'name' => get_string('registrationnumber', 'statistics'),
              'class' => format_class($extra, 'registration_number'),
              'link' => format_goto($urllink . '&sort=registration_number', $extra, array('sort'), 'registration_number')
        ),
        array(
              'id' => 'email',
              'name' => get_string('email'),
              'class' => format_class($extra, 'email'),
              'link' => format_goto($urllink . '&sort=email', $extra, array('sort'), 'email')
        ),
        array(
              'id' => 'portfoliotitle',
              'name' => get_string('portfoliotitle', 'statistics'),
              'class' => format_class($extra, 'portfoliotitle'),
              'link' => format_goto($urllink . '&sort=portfoliotitle', $extra, array('sort'), 'portfoliotitle'),
              'helplink' => get_help_icon('core', 'reports', 'completionverification', 'portfoliotitle')
        ),
        array(
              'id' => 'portfoliocreationdate',
              'name' => get_string('portfoliocreationdate', 'statistics'),
              'class' => format_class($extra, 'portfoliocreationdate'),
              'link' => format_goto($urllink . '&sort=portfoliocreationdate', $extra, array('sort'), 'portfoliocreationdate'),
              'helplink' => get_help_icon('core', 'reports', 'completionverification', 'portfoliocreationdate')
        ),
        array(
              'id' => 'templatetitle',
              'name' => get_string('templatetitle', 'statistics'),
              'class' => format_class($extra, 'templatetitle'),
              'link' => format_goto($urllink . '&sort=templatetitle', $extra, array('sort'), 'templatetitle'),
              'helplink' => get_help_icon('core', 'reports', 'completionverification', 'templatetitle')
        ),
        array(
              'id' => 'verifierfirstname',
              'name' => get_string('verifierfirstname', 'statistics'),
              'class' => format_class($extra, 'verifierfirstname'),
              'link' => format_goto($urllink . '&sort=verifierfirstname', $extra, array('sort'), 'verifierfirstname')
        ),
        array(
              'id' => 'verifierlastname',
              'name' => get_string('verifierlastname', 'statistics'),
              'class' => format_class($extra, 'verifierlastname'),
              'link' => format_goto($urllink . '&sort=verifierlastname', $extra, array('sort'), 'verifierlastname')
        ),
        array(
              'id' => 'verifierdisplayname',
              'name' => get_string('verifierdisplayname', 'statistics'),
              'class' => format_class($extra, 'verifierdisplayname'),
              'link' => format_goto($urllink . '&sort=verifierdisplayname', $extra, array('sort'), 'verifierdisplayname')
        ),
        array(
              'id' => 'verifierusername',
              'name' => get_string('verifierusername', 'statistics'),
              'class' => format_class($extra, 'verifierusername', 'statistics'),
              'link' => format_goto($urllink . '&sort=verifierusername', $extra, array('sort'), 'verifierusername')
        ),
        array(
              'id' => 'verifierstudentid',
              'name' => get_string('verifierregistrationnumber', 'statistics'),
              'class' => format_class($extra, 'verifierstudentid'),
              'link' => format_goto($urllink . '&sort=verifierstudentid', $extra, array('sort'), 'verifierstudentid')
        ),
        array(
              'id' => 'verifieremail',
              'name' => get_string('verifieremail', 'statistics'),
              'class' => format_class($extra, 'verifieremail'),
              // 'link' => format_goto($urllink . '&sort=verifieremail', $extra, array('sort'), 'verifieremail')
        ),
        array(
              'id' => 'accessfromdate',
              'name' => get_string('accessfromdate', 'statistics'),
              'class' => format_class($extra, 'accessfromdate'),
              'link' => format_goto($urllink . '&sort=accessfromdate', $extra, array('sort'), 'accessfromdate'),
              'helplink' => get_help_icon('core', 'reports', 'completionverification', 'accessgranteddate')
        ),
        array(
              'id' => 'accessrevokedbyauthordate',
              'name' => get_string('accessrevokedbyauthordate', 'statistics'),
              'class' => format_class($extra, 'accessrevokedbyauthordate'),
              'link' => format_goto($urllink . '&sort=accessrevokedbyauthordate', $extra, array('sort'), 'accessrevokedbyauthordate'),
              'helplink' => get_help_icon('core', 'reports', 'completionverification', 'revokedbyauthor')
        ),
        array(
              'id' => 'accessrevokedbyaccessordate',
              'name' => get_string('accessrevokedbyaccessordate', 'statistics'),
              'class' => format_class($extra, 'accessrevokedbyaccessordate'),
              'link' => format_goto($urllink . '&sort=accessrevokedbyaccessordate', $extra, array('sort'), 'accessrevokedbyaccessordate'),
              'helplink' => get_help_icon('core', 'reports', 'completionverification', 'revokedbyverifier')
        ),
        array(
              'id' => 'verifiedprimarystatmentdate',
              'name' => get_string('dateverified', 'statistics'),
              'class' => format_class($extra, 'verifiedprimarystatmentdate'),
              'link' => format_goto($urllink . '&sort=verifiedprimarystatmentdate', $extra, array('sort'), 'verifiedprimarystatmentdate'),
              'helplink' => get_help_icon('core', 'reports', 'completionverification', 'dateverified')
        ),
        array(
              'id' => 'completionpercentage',
              'name' => get_string('completionpercentage', 'statistics'),
              'class' => format_class($extra, 'completionpercentage'),
              'helplink' => get_help_icon('core', 'reports', 'completionverification', 'completionpercentage')
        ),
    );
}

function completionverification_statistics($limit, $offset, $extra, $institution = null) {
    $data = array();
    $urllink = get_config('wwwroot') . 'admin/users/statistics.php?type=users&subtype=completionverification';
    $data['tableheadings'] = completionverification_statistics_headers($extra, $urllink);

    $activeheadings = get_active_columns($data, $extra);
    $extra['columns'] = array_keys($activeheadings);

    $data['table'] = completionverification_stats_table($limit, $offset, $extra, $institution, $urllink);
    $data['table']['activeheadings'] = $activeheadings;

    $data['summary'] = $data['table']['count'] == 0 ? get_string('nostats', 'admin') : null;

    return $data;
}

function completionverification_stats_table($limit, $offset, $extra, $institution, $urllink) {
    global $USER, $SESSION;

    $start = !empty($extra['start']) ? $extra['start'] : date('Y-m-d', strtotime("-1 months"));
    $end = !empty($extra['end']) ? $extra['end'] : date('Y-m-d', strtotime('+1 day'));
    $users = $SESSION->get('usersforstats');

    $fromsql = " FROM {usr} u";
    $wheresql = " WHERE u.deleted = 0 AND u.id != 0 AND v.type = 'progress' ";
    $where = array();
    if ($institution && !$extra['portfoliofilter']) {
        $fromsql .= " JOIN {usr_institution} ui ON (ui.usr = u.id AND ui.institution = ?) ";
        $where[] = $institution;
    }
    if ($users) {
        $wheresql .= " AND u.id IN (" . join(',', array_map('db_quote', array_values((array)$users))) . ")";
    }
    $intcast = is_postgres() ? '::int' : '';
    $sorttype = !empty($extra['sort']) ? $extra['sort'] : '';
    switch ($sorttype) {
        case "username":
            $orderby = " u.username " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "email":
            $orderby = " u.email " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "registration_number":
            $orderby = " u.studentid" . $intcast . " " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "displayname":
            $orderby = " u.preferredname " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "verifierfirstname":
            $orderby = " verifierfirstname " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC') . ", u.firstname " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "verifierlastname":
            $orderby = " verifierlastname " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC') . ", u.firstname " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "verifierusername":
            $orderby = " verifierusername " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC') . ", u.firstname " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "verifierstudentid":
            $orderby = " verifierstudentid " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC') . ", u.firstname " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "verifierdisplayname":
            $orderby = " verifierdisplayname " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC') . ", u.firstname " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "portfoliotitle":
            $orderby = " c.name " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "templatetitle":
            $orderby = " templatetitle " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "lastname":
            $orderby = " u.lastname " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC') . ", u.firstname " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "firstname":
        default:
            $orderby = " u.firstname " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC') . ", u.lastname " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
    }
    $crossjoin = is_mysql() ? '' : ' CROSS JOIN json_to_record(el.data::json) AS x(rules text)
                                     CROSS JOIN json_to_record(x.rules::json) AS y(usr int, role text) ';

    $joinsql = " JOIN {collection} c ON (c.owner = u.id)
            JOIN {collection_view} cv ON (cv.collection = c.id)
            JOIN {view} v ON (v.id = cv.view AND cv.displayorder = 0)
            LEFT JOIN {event_log} el ON (cv.view = el.parentresourceid AND el.parentresourcetype = 'view' AND el.event = 'updateviewaccess')
            " . $crossjoin . "
            LEFT JOIN {collection_template} ct ON (ct.collection = cv.collection)
    ";

    if (isset($extra['verifierfilter']) && !empty($extra['verifierfilter'])) {
        if (is_mysql()) {
            $configdatasql = ",
             (SELECT el2.ctime AS verifieddate
             FROM {event_log} el2
             WHERE el2.parentresourceid = c.id
             AND el2.parentresourcetype = 'collection'
             AND el2.event = 'verifiedprogress'
             AND el2.data->>'$.block.verified' = 1
             AND el2.data->>'$.block.primary' IS TRUE
             ORDER BY el2.ctime LIMIT 1) ";
        }
        else {
            $configdatasql = ",
             (SELECT el2.ctime AS verifieddate
             FROM {event_log} el2, json_to_record(el2.data::json)
             AS z(block text), json_to_record(z.block::json)
             AS w(verified int, " . '"primary"' . " boolean)
             WHERE el2.parentresourceid = c.id
             AND el2.parentresourcetype = 'collection'
             AND el2.event = 'verifiedprogress'
             AND w.verified = 1
             AND w.primary IS TRUE
             ORDER BY el2.ctime LIMIT 1) ";
        }
        $rolekey = is_mysql() ? "el.data->>'$.rules.role'" : "y.role";
        if ($extra['verifierfilter'] == 'none') {
            $wheresql .= " AND NOT EXISTS (SELECT data FROM {event_log} WHERE cv.view = parentresourceid AND parentresourcetype = 'view' AND event = 'updateviewaccess' AND " . $rolekey . " = 'verifier') ";
            $configdatasql = '';
        }
        else if ($extra['verifierfilter'] == 'current') {
            $wheresql .= " AND (el.resourcetype = 'user' AND " . $rolekey . " = 'verifier') ";
        }
    }
    if (is_mysql()) {
        $customsql = "
        el.ctime AS accessfromdate,
        (SELECT el2.ctime FROM {event_log} el2 WHERE (c.id = el2.resourceid AND el2.resourcetype = 'collection' AND el.data->>'$.rules.usr' = el2.usr) AND el2.event = 'removeviewaccess' AND el2.ctime > el.ctime AND el2.data->>'$.removedby' = 'accessor' ORDER BY el2.ctime LIMIT 1) AS accessrevokedbyaccessordate,
        (SELECT el2.ctime FROM {event_log} el2 WHERE (c.id = el2.resourceid AND el2.resourcetype = 'collection' AND el2.usr = el.usr AND el2.event = 'removeviewaccess' AND el2.ctime > el.ctime AND el2.data->>'$.removedby' = 'owner') ORDER BY el2.ctime LIMIT 1) AS accessrevokedbyauthordate ";
    }
    else {
        $customsql = "
        el.ctime AS accessfromdate,
        (SELECT el2.ctime AS accessrevokedbyaccessordate FROM {event_log} el2, json_to_record(el2.data::json) AS z(removedby text) WHERE (c.id = el2.resourceid AND el2.resourcetype = 'collection' AND y.usr = el2.usr) AND el2.event = 'removeviewaccess' AND el2.ctime > el.ctime AND z.removedby = 'accessor' ORDER BY el2.ctime LIMIT 1),
        (SELECT el2.ctime AS accessrevokedbyauthordate FROM {event_log} el2, json_to_record(el2.data::json) AS z(removedby text) WHERE (c.id = el2.resourceid AND el2.resourcetype = 'collection' AND el2.usr = el.usr AND el2.event = 'removeviewaccess' AND el2.ctime > el.ctime AND z.removedby = 'owner') ORDER BY el2.ctime LIMIT 1) ";
    }
    if (!empty($configdatasql)) {
        $customsql .= $configdatasql;
    }

    if (isset($extra['portfoliofilter']) && !empty($extra['portfoliofilter'])) {
        $wheresql .= ' AND ct.originaltemplate IN (' . join(',', array_map('db_quote', $extra['portfoliofilter'])) . ')';
    }
    else if ($institution) {
        $joinsql .= " LEFT JOIN {collection} oc ON oc.id = ct.originaltemplate AND oc.institution = ?";
        $where[] = $institution;
    }

    if ($start) {
        $wheresql .= " AND c.ctime >= DATE(?) AND c.ctime <= DATE(?)";
        $where[] = $start;
        $where[] = $end;
    }

    $verifiersql = is_mysql() ? " el.data->>'$.rules.role', " : " y.role, ";
    $usrkey = is_mysql() ? "el.data->>'$.rules.usr'" : "y.usr";
    $sql ="SELECT u.id AS user_id, u.firstname, u.lastname, u.username, u.preferredname AS displayname,
           " . $usrkey . " AS verifierid,
            (SELECT username FROM {usr} WHERE id = " . $usrkey . ") AS verifierusername,
            (SELECT firstname FROM {usr} WHERE id = " . $usrkey . ") AS verifierfirstname,
            (SELECT lastname FROM {usr} WHERE id = " . $usrkey . ") AS verifierlastname,
            (SELECT studentid FROM {usr} WHERE id = " . $usrkey . ") AS verifierstudentid,
            (SELECT preferredname FROM {usr} WHERE id = " . $usrkey . ") AS verifierdisplayname,
        u.email, u.studentid AS registration_number, " . $verifiersql . "
        c.id as collection_id, c.name, c.ctime AS collection_ctime, ct.originaltemplate,  (SELECT name FROM {collection} WHERE id = ct.originaltemplate) AS templatetitle,
        " . $customsql .
        $fromsql . $joinsql . $wheresql;

    $sql .= " ORDER BY " . $orderby;

    if (empty($extra['csvdownload'])) {
        $sql .= " LIMIT $limit OFFSET $offset";
    }

    $count = count_records_sql("SELECT COUNT(*) " . $fromsql . $joinsql .  $wheresql, $where);

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
    $result['settings']['users'] = !empty($users) ? count($users) : 0;
    $data = !empty($where) ? get_records_sql_array($sql, $where) : get_records_sql_array($sql);
    if ($data) {
        foreach ($data as $item) {
            if ($item->user_id) {
                $item->profileurl = profile_url($item->user_id);
            }
            $item->verifierprofileurl = '#';
            $item->verifieremail = '';
            if (isset($item->verifierid) && !empty($item->verifierid)) {
                if (get_field('usr', 'deleted', 'id', $item->verifierid) > 0) {
                    $item->verifierusername = get_string('deleted');
                    $item->verifierfirstname = '-';
                    $item->verifierlastname = '-';
                    $item->verifieremail = '-';
                }
                else {
                    $item->verifierprofileurl = profile_url($item->verifierid);
                    $item->verifieremail = get_field('usr', 'email', 'id', $item->verifierid);
                }
            }
            $item->portfoliotitle = $item->name;
            $item->portfoliocreationdate = format_date(strtotime($item->collection_ctime), 'strftimedateshort');
            $item->templatetitleurl = $item->originaltemplate ? 'collection/progresscompletion.php?id=' . $item->originaltemplate : '';
            $item->verifiedprimarystatmentdate = isset($item->verifieddate) && $item->verifieddate ?  format_date(strtotime($item->verifieddate), 'strftimedateshort') : '';
            $item->accessfromdate = $item->accessfromdate ? format_date(strtotime($item->accessfromdate), 'strftimedateshort') : '';
            $item->accessrevokedbyaccessordate = $item->accessrevokedbyaccessordate ? format_date(strtotime($item->accessrevokedbyaccessordate), 'strftimedateshort') : '';
            $item->accessrevokedbyauthordate = $item->accessrevokedbyauthordate ? format_date(strtotime($item->accessrevokedbyauthordate), 'strftimedateshort') : '';
            require_once('collection.php');
            $collection = new Collection($item->collection_id);
            $collection->get('views');
            if ($item->completionpercentage = $collection->get_signed_off_and_verified_percentage()) {
                $item->completionpercentage = $item->completionpercentage[0]; //first number in array returned
            }
            else {
                $item->completionpercentage = '0';
            }
        }
    }

    if (!empty($extra['csvdownload'])) {
        $csvfields = array('firstname', 'lastname', 'displayname', 'username', 'registration_number', 'email',
                            'portfoliotitle',  'portfoliocreationdate', 'templatetitle', 'verifierfirstname',
                            'verifierlastname', 'verifierdisplayname', 'verifierusername', 'verifierstudentid', 'verifieremail', 'accessfromdate',
                            'accessrevokedbyauthordate', 'accessrevokedbyaccessordate', 'accessrevokedbysystemdate',
                            'verifiedprimarystatmentdate', 'completionpercentage');
        // Format all dates so that they are sortable in the CSV file
        for ($i = 0; $i < count($data); $i++) {
            if (!empty($data[$i]->portfoliocreationdate)) {
                $data[$i]->portfoliocreationdate = format_date(strtotime($data[$i]->portfoliocreationdate), 'strftimew3cdatetime');
            }
            if (!empty($data[$i]->verifiedprimarystatmentdate)) {
                $data[$i]->verifiedprimarystatmentdate = format_date(strtotime($data[$i]->verifiedprimarystatmentdate), 'strftimew3cdatetime');
            }
            if (!empty($data[$i]->accessfromdate)) {
                $data[$i]->accessfromdate = format_date(strtotime($data[$i]->accessfromdate), 'strftimew3cdatetime');
            }
            if (!empty($data[$i]->accessrevokedbyaccessordate)) {
                $data[$i]->accessrevokedbyaccessordate = format_date(strtotime($data[$i]->accessrevokedbyaccessordate), 'strftimew3cdatetime');
            }
            if (!empty($data[$i]->accessrevokedbyauthordate)) {
                $data[$i]->accessrevokedbyauthordate = format_date(strtotime($data[$i]->accessrevokedbyauthordate), 'strftimew3cdatetime');
            }
        }
        $USER->set_download_file(generate_csv($data, $csvfields), $institution . 'completionverificationstatistics.csv', 'text/csv', true);
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

    $result['tablerows'] = $smarty->fetch('admin/users/completionverificationstats.tpl');

    return $result;
}

function portfolioswithverifiers_statistics_headers($extra, $urllink) {
    return array(
        array('id' => 'rownum', 'name' => '#'),
        array(
              'id' => 'date', 'required' => true,
              'name' => get_string('date'),
              'class' => format_class($extra, 'date'),
              'link' => format_goto($urllink . '&sort=date', $extra, array('sort'), 'date')
        ),
        array(
              'id' => 'hasverifier', 'required' => true,
              'name' => get_string('withverifier', 'statistics'),
              'class' => format_class($extra, 'hasverifier'),
              'link' => format_goto($urllink . '&sort=hasverifier', $extra, array('sort'), 'hasverifier')
        ),
        array(
              'id' => 'noverifier', 'required' => true,
              'name' => get_string('withoutverifier', 'statistics'),
              'class' => format_class($extra, 'noverifier'),
              'link' => format_goto($urllink . '&sort=noverifier', $extra, array('sort'), 'noverifier')
        )
    );
}

function portfolioswithverifiers_statistics($limit, $offset, $extra, $institution = null) {
    $data = array();
    $urllink = get_config('wwwroot') . 'admin/users/statistics.php?type=users&subtype=portfolioswithverifiers';
    $data['tableheadings'] = portfolioswithverifiers_statistics_headers($extra, $urllink);

    $activeheadings = get_active_columns($data, $extra);
    $extra['columns'] = array_keys($activeheadings);

    $data['table'] = portfolioswithverifiers_stats_table($limit, $offset, $extra, $institution, $urllink);
    $data['table']['activeheadings'] = $activeheadings;

    $data['summary'] = $data['table']['count'] == 0 ? get_string('nostats', 'admin') : null;

    return $data;
}

function portfolioswithverifiers_stats_table($limit, $offset, $extra, $institution, $urllink) {
    global $USER;

    if (strtotime('1 April') > time()) {
        $april = date('Y-m-d', strtotime('1 April', strtotime('-1 year')));
    }
    else {
        $april = date('Y-m-d', strtotime('1 April'));
    }
    $start = !empty($extra['start']) ? $extra['start'] : $april;
    $end = !empty($extra['end']) ? $extra['end'] : date('Y-m-d', strtotime('+1 day'));

    if ($start) {
        $countwhere = "type = ? AND institution = ? AND ctime >= DATE(?) AND ctime <= DATE(?)";
        $count = count_records_select('institution_data', $countwhere, array('verifierportfolios', $institution, $start, $end));
    }
    else {
        $count = count_records('institution_data', 'type', 'verifierportfolios', 'institution', $institution);
    }

    $from = strtotime($start);
    if (date('w', $from) !== 1) {
        $from = strtotime( $start . ' next Monday');
    }
    $to = strtotime($end);
    if (date('w', $to) !== 1) {
        $to = strtotime( $end . ' last Monday');
    }
    $daterange = array();
    while ($from < $to) {
        $daterange[date("Y_W", $from)] = date('Y-m-d', $from);
        $from = $from + (7 * 24 * 60 * 60); // Break down the range by weeks
    }
    $daterange[date("Y_W", $to)] = date('Y-m-d', $to);
    $day = is_postgres() ? "to_date(ctime::text, 'YYYY-MM-DD')" : 'DATE(ctime)';
    $iday = is_postgres() ? "to_date(i.ctime::text, 'YYYY-MM-DD')" : 'DATE(i.ctime)';

    $ordersql = " ORDER BY ";
    if (!empty($extra['sort'])) {
        if ($extra['sort'] != 'date') {
            $ordersql .= $extra['sort'] . " " . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC') . ", ";
        }
        $ordersql .= " ctime " . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC');
    }
    else {
        $ordersql .= " ctime ASC";
    }

    $result['settings']['start'] = ($start) ? $start : min($daterange);

    $typesql = '';
    $instsql = '';
    $verifiersportfoliosql = '';
    $sql = '';
    if ($institution) {
        if ($institution != 'all') {
            $instsql = " AND institution IN (" . join(',', array_map('db_quote', array($institution))) . ")";
        }
    }
    $groupby = " GROUP BY i.institution, ctime ";
    if (isset($extra['portfoliofilter']) && !empty($extra['portfoliofilter'])) {
        $templates = array();
        $verifierspertemplate = array();
        foreach ($extra['portfoliofilter'] as $template) {
            $templates[$template] = 'owners-per-template_' . $template;
            $verifierspertemplate[$template] = 'verifierportfolios-verifier-count_'. $template;
        }
        $typesql = " type IN (" . join(',', array_map('db_quote', $templates)) . ")";
        $verifiersportfoliosql = " type IN (" . join(',', array_map('db_quote', $verifierspertemplate)) . ")";
    }
    else {
        $typesql = " type LIKE 'owners-per-template_%' ";
        $verifiersportfoliosql = " type = 'verifierportfolios-verifier-count'";
    }
    $intcast = is_postgres() ? '::int' : '';
    $sql = "SELECT $day AS date, institution,
            (SELECT SUM (value" . $intcast . ") FROM {institution_data} WHERE ($day = $iday) AND $verifiersportfoliosql $instsql GROUP BY $iday) AS hasverifier,
            SUM(value" . $intcast . ") - (SELECT SUM (value" . $intcast . ") FROM {institution_data} WHERE ($day = $iday) AND $verifiersportfoliosql $instsql GROUP BY $iday) AS noverifier
            FROM {institution_data} i
            WHERE " . $typesql.
            " AND $day IN (" . join(',', array_map('db_quote', $daterange)) . ")"
            . $instsql . $groupby . $ordersql;

    if (empty($extra['csvdownload'])) {
        $sql .= " LIMIT $limit OFFSET $offset";
    }

    $data = get_records_sql_array($sql);

    $rawdata = array();
    if ($data) {
        $records = array();
        foreach($data as $item) {
            $obj = new stdClass();
            $obj->date = get_string('weekstartdate', 'statistics', $item->date);
            $obj->hasverifier = $item->hasverifier;
            $obj->noverifier = $item->noverifier;
            $rawdata[$item->date] = $obj;
        }
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

    $result['csv'] = true;
    $columnkeys = array();
    foreach ($extra['columns'] as $column) {
        $columnkeys[$column] = 1;
    }

    // Now do the limit / offset for pagination
    if (empty($extra['csvdownload'])) {
        $data = array_slice($rawdata, $offset, $limit, true);
    }
    else {
        $data = $rawdata;
        $csvfields = array('date', 'hasverifier', 'noverifier');
        $csvheaders = array('date' => get_string('date', 'statistics'),
                            'hasverifier' => get_string('withverifier', 'statistics'),
                            'noverifier' => get_string('withoutverifier', 'statistics')
                        );

        for ($i = 0; $i < count($data); $i++) {
            if (!empty($data[$i]->date)) {
                $data[$i]->date = format_date(strtotime($data[$i]->date), 'strftimew3cdatetime');
            }
        }
        $USER->set_download_file(generate_csv($data, $csvfields, $csvheaders), $institution . 'portfolioswithverifiersummarystatistics.csv', 'text/csv', true);
    }

    $smarty = smarty_core();
    $smarty->assign('data', $rawdata);
    $smarty->assign('columns', $columnkeys);
    $smarty->assign('offset', $offset);

    $result['tablerows'] = $smarty->fetch('admin/users/portfolioswithverifiersstats.tpl');
    $result['rawdata'] = $rawdata;

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
    $end = !empty($extra['end']) ? $extra['end'] : date('Y-m-d', strtotime('+1 day'));

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
        $USER->set_download_file(generate_csv($data, $csvfields), 'userstatistics.csv', 'text/csv', true);
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

function verifiersummary_statistics_headers($extra, $urllink) {
    return array(
        array('id' => 'rownum', 'name' => '#'),
        array(
              'id' => 'date', 'required' => true,
              'name' => get_string('date'),
              'class' => format_class($extra, 'date'),
              'link' => format_goto($urllink . '&sort=date', $extra, array('sort'), 'date')
        ),
        array(
              'id' => 'one',
              'name' => get_string('one', 'statistics'),
              'class' => format_class($extra, 'one'),
              'link' => format_goto($urllink . '&sort=one', $extra, array('sort'), 'one')
        ),
        array(
              'id' => 'two',
              'name' => get_string('two', 'statistics'),
              'class' => format_class($extra, 'two'),
              'link' => format_goto($urllink . '&sort=two', $extra, array('sort'), 'two')
        ),
        array(
              'id' => 'three',
              'name' => get_string('three', 'statistics'),
              'class' => format_class($extra, 'three'),
              'link' => format_goto($urllink . '&sort=three', $extra, array('sort'), 'three')
        ),
        array(
              'id' => 'four',
              'name' => get_string('four', 'statistics'),
              'class' => format_class($extra, 'four'),
              'link' => format_goto($urllink . '&sort=four', $extra, array('sort'), 'four')
        ),
        array(
              'id' => 'five',
              'name' => get_string('five', 'statistics'),
              'class' => format_class($extra, 'five'),
              'link' => format_goto($urllink . '&sort=five', $extra, array('sort'), 'five')
        ),
        array(
              'id' => 'six',
              'name' => get_string('six', 'statistics'),
              'class' => format_class($extra, 'six'),
              'link' => format_goto($urllink . '&sort=six', $extra, array('sort'), 'six')
        ),
        array(
              'id' => 'seven',
              'name' => get_string('seven', 'statistics'),
              'class' => format_class($extra, 'seven'),
              'link' => format_goto($urllink . '&sort=seven', $extra, array('sort'), 'seven')
        ),
        array(
              'id' => 'eight',
              'name' => get_string('eight', 'statistics'),
              'class' => format_class($extra, 'eight'),
              'link' => format_goto($urllink . '&sort=eight', $extra, array('sort'), 'eight')
        ),
        array(
              'id' => 'nine',
              'name' => get_string('nine', 'statistics'),
              'class' => format_class($extra, 'nine'),
              'link' => format_goto($urllink . '&sort=nine', $extra, array('sort'), 'nine')
        ),
        array(
              'id' => 'tenormore',
              'name' => get_string('tenormore', 'statistics'),
              'class' => format_class($extra, 'tenormore'),
              'link' => format_goto($urllink . '&sort=tenormore', $extra, array('sort'), 'tenormore')
        ),
    );
}

function verifiersummary_statistics($limit, $offset, $extra, $institution = null) {
    $data = array();
    $urllink = get_config('wwwroot') . 'admin/users/statistics.php?type=users&subtype=verifiersummary';
    if ($institution) {
        $urllink .= '&institution=' . $institution;
    }
    $data['tableheadings'] = verifiersummary_statistics_headers($extra, $urllink);

    $activeheadings = get_active_columns($data, $extra);
    $extra['columns'] = array_keys($activeheadings);
    $data['table'] = verifiersummary_stats_table($limit, $offset, $extra, $institution, $urllink);
    $data['table']['activeheadings'] = $activeheadings;

    $data['summary'] = $data['table']['count'] == 0 ? get_string('nostats', 'admin') : null;

    return $data;
}

function adjust_verifier_values($record, $column_number) {
    $mapping = db_quote('verifierportfolios-verifier-load-' . $column_number . '_' . $record);
    return $mapping;
}

function verifiersummary_stats_table($limit, $offset, $extra, $institution, $urllink) {
    global $USER;

    if (strtotime('1 April') > time()) {
        $april = date('Y-m-d', strtotime('1 April', strtotime('-1 year')));
    }
    else {
        $april = date('Y-m-d', strtotime('1 April'));
    }
    $start = !empty($extra['start']) ? $extra['start'] : $april;
    $end = !empty($extra['end']) ? $extra['end'] : date('Y-m-d', strtotime('+1 day'));

    if ($start) {
        $countwhere = "type = ? AND institution = ? AND ctime >= DATE(?) AND ctime <= DATE(?)";
        $count = count_records_select('institution_data', $countwhere, array('verifierportfolios', $institution, $start, $end));
    }
    else {
        $count = count_records('institution_data', 'type', 'verifierportfolios', 'institution', $institution);
    }
    $from = strtotime($start);
    if (date('w', $from) !== 1) {
        $from = strtotime( $start . ' next Monday');
    }
    $to = strtotime($end);
    if (date('w', $to) !== 1) {
        $to = strtotime( $end . ' last Monday');
    }
    $daterange = array();
    while ($from < $to) {
        $daterange[date("Y_W", $from)] = date('Y-m-d', $from);
        $from = $from + (7 * 24 * 60 * 60); // Break down the range by weeks
    }
    $daterange[date("Y_W", $to)] = date('Y-m-d', $to);

    if (empty($extra['csvdownload'])) {
        array_slice($daterange, $offset, $limit);
    }

    $ordersql = " ORDER BY ";
    if (!empty($extra['sort'])) {
        if ($extra['sort'] != 'date') {
            $types = array('one' => 1, 'two' => 2, 'three' => 3, 'four' => 4,
                           'five' => 5, 'six' => 6, 'seven' => 7, 'eight' => 8,
                           'nine' => 9, 'tenormore' => 10);
            $ordersql .= " count_" . $types[$extra['sort']] . " " . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC') . ", ";
        }
        $ordersql .= " ctime " . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC');
    }
    else {
        $ordersql .= " ctime ASC";
    }

    $result['settings']['start'] = ($start) ? $start : min($daterange);

    $day = is_postgres() ? "to_date(ctime::text, 'YYYY-MM-DD')" : 'DATE(ctime)';
    $iday = is_postgres() ? "to_date(i.ctime::text, 'YYYY-MM-DD')" : 'DATE(i.ctime)';
    $wheresql = " WHERE type LIKE 'verifierportfolios-verifier-count'";
    $instsql = '';

    if ($institution) {
        if ($institution != 'all') {
            $instsql .= " AND institution IN (" . join(',', array_map('db_quote', array($institution))) . ")";
        }
    }
    if (isset($extra['portfoliofilter']) && !empty($extra['portfoliofilter'])) {
        $portfoliofilter = $extra['portfoliofilter'];
        $typesql = ' AND type IN ( ';
        $endtypesql = ' ) ';
        $typesql1 = $typesql . join(',', array_map('adjust_verifier_values', $portfoliofilter, array_fill(0, count($portfoliofilter), 1))) . $endtypesql;
        $typesql2 = $typesql . join(',', array_map('adjust_verifier_values', $portfoliofilter, array_fill(0, count($portfoliofilter), 2))) . $endtypesql;
        $typesql3 = $typesql . join(',', array_map('adjust_verifier_values', $portfoliofilter, array_fill(0, count($portfoliofilter), 3))) . $endtypesql;
        $typesql4 = $typesql . join(',', array_map('adjust_verifier_values', $portfoliofilter, array_fill(0, count($portfoliofilter), 4))) . $endtypesql;
        $typesql5 = $typesql . join(',', array_map('adjust_verifier_values', $portfoliofilter, array_fill(0, count($portfoliofilter), 5))) . $endtypesql;
        $typesql6 = $typesql . join(',', array_map('adjust_verifier_values', $portfoliofilter, array_fill(0, count($portfoliofilter), 6))) . $endtypesql;
        $typesql7 = $typesql . join(',', array_map('adjust_verifier_values', $portfoliofilter, array_fill(0, count($portfoliofilter), 7))) . $endtypesql;
        $typesql8 = $typesql . join(',', array_map('adjust_verifier_values', $portfoliofilter, array_fill(0, count($portfoliofilter), 8))) . $endtypesql;
        $typesql9 = $typesql . join(',', array_map('adjust_verifier_values', $portfoliofilter, array_fill(0, count($portfoliofilter), 9))) . $endtypesql;
        $typesql10 = $typesql . join(',', array_map('adjust_verifier_values', $portfoliofilter, array_fill(0, count($portfoliofilter), 10))) . $endtypesql;
    }
    else {
        $typesql = ' AND type LIKE ';
        $typesql1 = $typesql . " 'verifierportfolios-verifier-load-1%'";
        $typesql2 = $typesql . " 'verifierportfolios-verifier-load-2%'";
        $typesql3 = $typesql . " 'verifierportfolios-verifier-load-3%'";
        $typesql4 = $typesql . " 'verifierportfolios-verifier-load-4%'";
        $typesql5 = $typesql . " 'verifierportfolios-verifier-load-5%'";
        $typesql6 = $typesql . " 'verifierportfolios-verifier-load-6%'";
        $typesql7 = $typesql . " 'verifierportfolios-verifier-load-7%'";
        $typesql8 = $typesql . " 'verifierportfolios-verifier-load-8%'";
        $typesql9 = $typesql . " 'verifierportfolios-verifier-load-9%'";
        $typesql10 = $typesql . " 'verifierportfolios-verifier-load-10%'";
    }
    $intcast = is_postgres() ? '::int' : '';
    $customsql = "SELECT \"value\" AS count, ctime, $day AS date,
                  (SELECT SUM (value" . $intcast . ") AS count_1 FROM {institution_data} WHERE ($day = $iday) $typesql1 $instsql),
                  (SELECT SUM (value" . $intcast . ") AS count_2 FROM {institution_data} WHERE ($day = $iday) $typesql2 $instsql),
                  (SELECT SUM (value" . $intcast . ") AS count_3 FROM {institution_data} WHERE ($day = $iday) $typesql3 $instsql),
                  (SELECT SUM (value" . $intcast . ") AS count_4 FROM {institution_data} WHERE ($day = $iday) $typesql4 $instsql),
                  (SELECT SUM (value" . $intcast . ") AS count_5 FROM {institution_data} WHERE ($day = $iday) $typesql5 $instsql),
                  (SELECT SUM (value" . $intcast . ") AS count_6 FROM {institution_data} WHERE ($day = $iday) $typesql6 $instsql),
                  (SELECT SUM (value" . $intcast . ") AS count_7 FROM {institution_data} WHERE ($day = $iday) $typesql7 $instsql),
                  (SELECT SUM (value" . $intcast . ") AS count_8 FROM {institution_data} WHERE ($day = $iday) $typesql8 $instsql),
                  (SELECT SUM (value" . $intcast . ") AS count_9 FROM {institution_data} WHERE ($day = $iday) $typesql9 $instsql),
                  (SELECT SUM (value" . $intcast . ") AS count_10 FROM {institution_data} WHERE ($day = $iday) $typesql10 $instsql)
                  FROM {institution_data} i" . $wheresql . "
                  AND $day IN (" . join(',', array_map('db_quote', $daterange)) . ")" . $ordersql;

    $records = get_records_sql_array($customsql);

    $pagination = build_pagination(array(
        'id' => 'stats_pagination',
        'url' => get_config('wwwroot') . 'admin/users/statistics.php?type=verifiersummary',
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
    $result['settings']['start'] = ($start) ? $start : min($daterange);

    $sorttype = !empty($extra['sort']) ? $extra['sort'] : '';
    // if sorting by date
    if ($sorttype == 'date' && !empty($extra['sortdesc'])) {
        $daterange = array_reverse($daterange);
    }

    $rawdata = array();
    if ($records) {
        foreach ($records as $record) {
            $obj = new stdClass();
            $obj->date = get_string('weekstartdate', 'statistics', $record->date);
            $obj->one = isset($record->count_1) && !empty($record->count_1) ? $record->count_1 : '';
            $obj->two = isset($record->count_2) && !empty($record->count_2) ? $record->count_2 : '';
            $obj->three = isset($record->count_3) && !empty($record->count_2) ? $record->count_3 : '';
            $obj->four = isset($record->count_4) && !empty($record->count_2) ? $record->count_4 : '';
            $obj->five = isset($record->count_5) && !empty($record->count_2) ? $record->count_5 : '';
            $obj->six = isset($record->count_6) && !empty($record->count_2) ? $record->count_6 : '';
            $obj->seven = isset($record->count_7) && !empty($record->count_2) ? $record->count_7 : '';
            $obj->eight = isset($record->count_8) && !empty($record->count_2) ? $record->count_8 : '';
            $obj->nine = isset($record->count_9) && !empty($record->count_2) ? $record->count_9 : '';
            $obj->tenormore = isset($record->count_10) && !empty($record->count_2) ? $record->count_10 : '';
            $rawdata[$record->date] = $obj;
        }
    }

    // Now do the limit / offset for pagination
    if (empty($extra['csvdownload'])) {
        $data = array_slice($rawdata, $offset, $limit, true);
    }
    else {
        $data = $rawdata;
        $csvfields = array('date', 'one', 'two', 'three', 'four', 'five', 'six', 'seven', 'eight', 'nine', 'tenormore');
        $csvheaders = array('date' => get_string('date', 'statistics'),
                            'one' => get_string('one', 'statistics'),
                            'two' => get_string('two', 'statistics'),
                            'three' =>  get_string('three', 'statistics'),
                            'four' =>  get_string('four', 'statistics'),
                            'five' =>  get_string('five', 'statistics'),
                            'six' =>  get_string('six', 'statistics'),
                            'seven' =>  get_string('seven', 'statistics'),
                            'eight' =>  get_string('eight', 'statistics'),
                            'nine' =>  get_string('nine', 'statistics'),
                            'tenormore' =>  get_string('tenormore', 'statistics'));
        for ($i = 0; $i < count($data); $i++) {
            if (!empty($data[$i]->date)) {
                $data[$i]->date = format_date(strtotime($data[$i]->date), 'strftimew3cdatetime');
            }
        }
        $USER->set_download_file(generate_csv($data, $csvfields, $csvheaders), $institution . 'verifiersummarystatistics.csv', 'text/csv', true);
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

    $result['tablerows'] = $smarty->fetch('admin/users/verifiersummarystats.tpl');

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
    $end = !empty($extra['end']) ? $extra['end'] : date('Y-m-d', strtotime('+1 day'));

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
        $USER->set_download_file(generate_csv($data, $csvfields), $institutiondata['name'] . 'userstatistics.csv', 'text/csv', true);
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

    $aggmap = [];
    $aggregates = [];
    $groupids = [];
    $sortdirection = [];

    $start = !empty($extra['start']) ? $extra['start'] : date('Y-m-d', strtotime("-1 months"));
    $end = !empty($extra['end']) ? $extra['end'] : date('Y-m-d', strtotime('+1 day'));
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

    // Add in the data from search if available.
    if ($search_class = does_search_plugin_have('report_group_stats_table')) {
        list($aggmap, $groupids) = $search_class::report_group_stats_table($start, $end, $sorttype, $count, $sortdesc);
    }

    switch ($sorttype) {
        case "members":
            $ordersql = " mc.members " . $sortdesc;
            break;

        case "views":
            $ordersql = " vc.views " . $sortdesc;
            break;

        case "forums":
            $ordersql = " fc.forums " . $sortdesc;
            break;

        case "posts":
            $ordersql = " posts " . $sortdesc;
            break;

        case "id":
            $ordersql = " g.id " . $sortdesc;
            break;

        case "group":
        default:
            $ordersql = " g.name " . $sortdesc;
    }

    $rangesql = '';
    $rangewhere = array();
    if ($start) {
        $rangesql = " AND ctime >= DATE(?) AND ctime <= DATE(?)";
        $rangewhere[] = $start;
        $rangewhere[] = $end;
    }

    $elasticselect = $elasticfrom = '';

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
        $USER->set_download_file(generate_csv($groupdata, $csvfields, $csvheaders), 'groupstatistics.csv', 'text/csv', true);
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
        array('id' => 'submittedstatus', 'required' => false,
              'name' => get_string('submittedstatus', 'view'),
              'class' => format_class($extra, 'submittedstatus'),
              'link' => format_goto($urllink . '&sort=submittedstatus', $extra, array('sort'), 'submittedstatus'),
              'helplink' => get_help_icon('core', 'reports', 'pageactivity', 'submittedstatus')
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

    $sqlwhere = '';
    $start = !empty($extra['start']) ? $extra['start'] : null;
    $end = !empty($extra['end']) ? $extra['end'] : date('Y-m-d', strtotime('+1 day'));
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
        case "submittedstatus":
            $orderby = " v.submittedstatus " . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC') . ", v.title, v.id";
            break;
        case "visits":
        default:
            $orderby = " v.visits " . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC') . ", v.title, v.id";
    }

    $sql = "SELECT v.id, v.title, v.owner, v.group, v.institution, c.name, v.submittedstatus, v.submissionoriginal,
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
        $v->canbeviewed = $v->id ? can_view_view($v->id) : false;
        $v->submittedstatus = add_submitted_label($v->submittedstatus, (bool)$v->submissionoriginal);
    }

    $daterange = array_map(function ($obj) { return $obj->ctime; }, $viewdata);
    $result['settings']['start'] = ($start) ? $start : min($daterange);
    if (!empty($extra['csvdownload'])) {
        $csvfields = array('displaytitle', 'fullurl', 'collectiontitle','ownername', 'ownerurl',
                           'ctime', 'mtime', 'atime', 'blocks', 'visits', 'comments', 'submittedstatus');
        $USER->set_download_file(generate_csv($viewdata, $csvfields), 'viewstatistics.csv', 'text/csv', true);
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

/**
 * Find the correct submission label based on the submittedstatus value and
 * if the connection to original still exists
 *
 * @param integer $submittedstatus
 * @param boolean $original
 * @return string|false
 */
function add_submitted_label($submittedstatus, $original=false) {
    if (is_null($submittedstatus)) {
        return false;
    }
    if ($original && $submittedstatus == 0) {
        $submittedstatus = 3;
    }
    $options = array(0 => get_string('notsubmitted', 'view'),
                     1 => get_string('Submitted', 'view'),
                     2 => get_string('archiving', 'view'),
                     3 => get_string('released', 'view')
               );

    if (isset($options[$submittedstatus])) {
        return $options[$submittedstatus];
    }
    return $options[0];
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

function block_type_graph($type = null) {
    // Draw a bar graph of 10 most common blocks used broken down by block type.
    $maxblocktypes = 10;
    $blocktypecounts = get_records_sql_array("
        SELECT
            b.blocktype,
        COUNT(b.id) AS blocks
        FROM {block_instance} b
        JOIN {blocktype_installed} bi ON (b.blocktype = bi.name)
        JOIN {view} v ON (b.view = v.id AND v.type = 'portfolio')
        WHERE bi.active = 1
        GROUP BY b.blocktype
        ORDER BY blocks DESC",
        array(), 0, $maxblocktypes
    );
    if (is_array($blocktypecounts)) {
        $dataarray = array();
        foreach ($blocktypecounts as $blocktype) {
            safe_require('blocktype', $blocktype->blocktype);
            $classname = generate_class_name('blocktype', $blocktype->blocktype);
            $blocktype->title = $classname::get_title();
            $dataarray[$blocktype->title] = $blocktype->blocks;
        }
        ksort($dataarray);
        $data['graph'] = 'Bar';
        $data['graph_function_name'] = 'block_type_graph';
        $data['title'] = get_string('blockcountsbytype', 'admin');
        $data['labels'] = array_keys($dataarray);
        $data['data'] = $dataarray;
        require_once(get_config('libroot') . 'graph.php');
        $graphdata = get_bar_graph_json($data);
        $out['graph'] = $data['graph'];
        $out['jsondata'] = json_encode($graphdata[0]);
        return $out;
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
    $end = !empty($extra['end']) ? $extra['end'] : date('Y-m-d', strtotime('+1 day'));
    $users = $SESSION->get('usersforstats');
    $sqlwhere = '';
    $values = array();
    if ($institutiondata['views'] != 0) {
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
        case "submittedstatus":
            $orderby = " v.submittedstatus " . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC') . ", v.title, v.id";
            break;
        case "visits":
        default:
            $orderby = " v.visits " . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC') . ", v.title, v.id";
    }

    $sql = "SELECT v.id, v.title, v.owner, v.group, v.institution, v.submittedstatus, v.submissionoriginal,
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
        $v->canbeviewed = $v->id ? can_view_view($v->id) : false;
        $v->submittedstatus = add_submitted_label($v->submittedstatus, (bool)$v->submissionoriginal);
    }

    $daterange = array_map(function ($obj) { return $obj->ctime; }, $viewdata);
    $result['settings']['start'] = ($start) ? $start : min($daterange);
    if (!empty($extra['csvdownload'])) {
        $csvfields = array('displaytitle', 'fullurl', 'collectiontitle', 'ownername', 'ownerurl',
                           'ctime', 'mtime', 'atime', 'blocks', 'visits', 'comments', 'submittedstatus');
        $USER->set_download_file(generate_csv($viewdata, $csvfields), $institutiondata['name'] . 'viewstatistics.csv', 'text/csv', true);
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

function institution_view_type_graph($type = null, $institutiondata=null) {

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

function institution_view_type_graph_render($type = null, $extradata=null) {

    $data['graph'] = ($type) ? $type : 'pie';
    if ($jsondata = json_decode(get_field('institution_data','value','type','view-type-graph','institution', $extradata->institution))) {
        $data['jsondata'] = json_encode($jsondata[0]);
        return $data;
    }
}

function institution_user_type_graph($type = null, $institutiondata=null) {

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
    $end = !empty($extra['end']) ? $extra['end'] : date('Y-m-d', strtotime('+1 day'));
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
        $USER->set_download_file(generate_csv($contentdata, $csvfields), 'contentstatistics.csv', 'text/csv', true);
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
    $end = !empty($extra['end']) ? $extra['end'] : date('Y-m-d', strtotime('+1 day'));
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
        $USER->set_download_file(generate_csv($data, $csvfields), $institution . 'objectionablestatistics.csv', 'text/csv', true);
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
    $end = !empty($extra['end']) ? $extra['end'] : date('Y-m-d', strtotime('+1 day'));
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
        $USER->set_download_file(generate_csv($contentdata, $csvfields), $institutiondata['name'] . 'contentstatistics.csv', 'text/csv', true);
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
    $end = !empty($extra['end']) ? $extra['end'] : date('Y-m-d', strtotime('+1 day'));
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
    if ($data) {
        foreach ($data as $item) {
            $jsondata = json_decode($item->data);
            $item->reason = $jsondata->reason;
            $item->userurl = profile_url($item->user);
            $item->user = display_name($item->user);
            $item->masqueraderurl = profile_url($item->masquerader);
            $item->masquerader = display_name($item->masquerader);
            $item->date = format_date(strtotime($item->date));
        }
    }

    if (!empty($extra['csvdownload'])) {
        $csvfields = array('user', 'reason', 'masquerader', 'masqueraderurl', 'date');
        $USER->set_download_file(generate_csv($data, $csvfields), $institution . 'masqueradingstatistics.csv', 'text/csv', true);
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


/**
 * Returns the table data for the SmartEvidence report.
 *
 * @param int $limit The number of rows to return.
 * @param int $offset The row to start at.
 * @param array $extra Any additional options.
 * @param string|null $institution The institution to filter by or all if null.
 *
 * @return array<string,mixed>
 */
function smartevidence_statistics($limit, $offset, $extra, $institution = null) {
    userhasaccess($institution, 'accesslist');
    $data = array();
    $urllink = get_config('wwwroot') . 'admin/users/statistics.php?type=users&subtype=smartevidence';
    if ($institution) {
        $urllink .= '&institution=' . $institution;
    }

    // Fetch the header row.
    $data['tableheadings'] = smartevidence_stats_headers($limit, $offset, $extra, $institution, $urllink);
    $activeheadings = get_active_columns($data, $extra);
    $extra['columns'] = array_keys($activeheadings);

    // Fetch the data.
    $data['table'] = smartevidence_stats_table($limit, $offset, $extra, $institution, $urllink);
    $data['table']['activeheadings'] = $activeheadings;

    $data['summary'] = $data['table']['count'] == 0 ? get_string('nostats', 'admin') : null;

    return $data;
}

/**
 * Returns the Columns for the SmartEvidence report config form.
 *
 * @param array $extra Any additional options.
 * @param null $institution Needed for the function signature.
 *
 * @return array<array<string,string>> The report headers.
 */
function smartevidence_statistics_headers($extra = [], $institution = null) {
    $extra['forconfigform'] = true;
    return smartevidence_stats_headers(10, 0, $extra, $institution, null);
}

/**
 * SmartEvidence report headers.
 *
 * Columns:
 * • First name*
 * • Last name*
 * • Display name
 * • User name
 * • Email address
 * • Collection (title of the collection that has a SmartEvidence overview page)*
 * • Pages (number of pages in the portfolio)
 * • Access list (list of entities who have access to the portfolio)
 * • A column each for all available statuses with the sum showing for the entire collection*
 *
 * * required fields.
 *
 * @param int $limit The number of rows to return.
 * @param int $offset The row to start at.
 * @param array $extra Extra information about the report.
 * @param string|null $institution The institution to filter by or all if null.
 * @param string $urllink The URL to link to for the report.
 *
 * @return array<array<string,string>> The report headers
 */
function smartevidence_stats_headers($limit, $offset, $extra, $institution, $urllink) {
    require_once(get_config('libroot') . 'collection.php');
    safe_require('module', 'framework');
    $headers = array(
        array('id' => 'rownum', 'name' => '#'),
        array(
            'id' => 'firstname',
            'required' => true,
            'name' => get_string('firstname'),
            'class' => format_class($extra, 'firstname'),
            'link' => format_goto($urllink . '&sort=firstname', $extra, array('sort'), 'firstname'),
        ),
        array(
            'id' => 'lastname',
            'required' => true,
            'name' => get_string('lastname'),
            'class' => format_class($extra, 'lastname'),
            'link' => format_goto($urllink . '&sort=lastname', $extra, array('sort'), 'lastname'),
        ),
        array(
            'id' => 'displayname',
            'required' => false,
            'name' => get_string('displayname'),
            'class' => format_class($extra, 'displayname'),
            'link' => format_goto($urllink . '&sort=displayname', $extra, array('sort'), 'displayname'),
        ),
        array(
              'id' => 'username',
              'name' => get_string('username'),
              'class' => format_class($extra, 'username'),
              'link' => format_goto($urllink . '&sort=username', $extra, array('sort'), 'username')
        ),
        array(
            'id' => 'email',
            'required' => false,
            'name' => get_string('email'),
            'class' => format_class($extra, 'email'),
        ),
        array(
            'id' => 'collection',
            'required' => true,
            'name' => get_string('Collection', 'collection'),
            'class' => format_class($extra, 'collection'),
            'link' => format_goto($urllink . '&sort=collection', $extra, array('sort'), 'collection'),
            'helplink' => get_help_icon('core', 'reports', 'smartevidence', 'collection'),
        ),
        array(
            'id' => 'pagecount',
            'required' => false,
            'name' => get_string('Views', 'view'),
            'class' => format_class($extra, 'pagecount'),
            'link' => format_goto($urllink . '&sort=pagecount', $extra, array('sort'), 'pagecount'),
            'helplink' => get_help_icon('core', 'reports', 'smartevidence', 'pagecount'),
        ),
        array('id' => 'submittedstatus',
              'name' => get_string('submittedstatus', 'view'),
              'class' => format_class($extra, 'submittedstatus'),
              'link' => format_goto($urllink . '&sort=submittedstatus', $extra, array('sort'), 'submittedstatus'),
              'helplink' => get_help_icon('core', 'reports', 'pageactivity', 'submittedstatus')
        ),
        array(
            'id' => 'accessrules',
            'required' => false,
            'name' => get_string('accesslist', 'view'),
            'class' => format_class($extra, 'accessrules'),
            'helplink' => get_help_icon('core', 'reports', 'smartevidence', 'accessrules'),
        ),
    );

    // Add the columns for each status.
    list($count, $data) = smartevidence_stats_query($limit, $offset, $extra, $institution);
    if ($count < 1) {
        return $headers;
    }
    $status_types = [];
    foreach ($data as $item) {
        // Extra detail from the collection.
        $collection = new Collection($item->collectionid);
        // Process SmartEvidence totals.
        $framework = new Framework($collection->get('framework'));
        $statuses = Framework::get_evidence_statuses_for_display($framework->get('id'));
        foreach ($statuses as $key => $status) {
            $status_types[$key] = $status;
        }
    }
    foreach ($status_types as $key => $status) {
        $classes = $status['classes'];
        $id = $status['statisticsid'];
        $title = $status['statisticstitle'];
        $description = $status['headerdescription'];
        $header = [
            'id' => $id,
            'required' => true,
            'class' => 'assessment ' . format_class($extra, $id),
        ];
        if (isset($extra['forconfigform']) && $extra['forconfigform']) {
            // The config form just needs the title string.
            $header['name'] = get_string('smartevidence', 'collection') . ': ' . $title;
        }
        else {
            // The statistics page uses the SmartEvidence status icon.
            $header['name'] = '<span class="' . $classes . '" title="' . $title . '"></span>' .
                '<span class="visually-hidden">' . $description . '</span>';
            $header['headingishtml'] = true;
        }
        $headers[] = $header;
    }
    return $headers;
}

/**
 * Fetch the data for the SmartEvidence report.
 *
 * @param int $limit The number of rows to return.
 * @param int $offset The row to start at.
 * @param array $extra Extra information about the report.
 * @param string|null $institution The institution to filter by or all if null.
 * @param string $urllink The URL to link to for the report.
 *
 * @return array<string,mixed>
 */
function smartevidence_stats_table($limit, $offset, $extra, $institution, $urllink) {
    global $USER, $SESSION;

    require_once(get_config('libroot') . 'collection.php');
    safe_require('module', 'framework');

    $start = !empty($extra['start']) ? $extra['start'] : null;
    $end = !empty($extra['end']) ? $extra['end'] : date('Y-m-d', strtotime('+1 day'));
    $users = $SESSION->get('usersforstats');
    $framework_statuses = [];

    list($count, $data) = smartevidence_stats_query($limit, $offset, $extra, $institution);

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

    $daterange = array_map(function ($obj) { return $obj->vctime; }, $data);
    $result['settings']['start'] = ($start) ? $start : min($daterange);
    $frameworks_checked = [];
    foreach ($data as $item) {
        $item->userurl = profile_url($item->userid);
        if ($item->views < 1) {
            $item->title = get_string('noviews2', 'view');
        }
        $item->access = get_records_sql_array("
            SELECT *, 0 AS secreturls
            FROM {view_access} WHERE view = ? AND token IS NULL
            UNION
            (SELECT *, (SELECT COUNT(*) FROM {view_access} va2 WHERE token IS NOT NULL AND va2.view = va.view) AS secreturls
            FROM {view_access} va WHERE va.view = ? AND va.token IS NOT NULL LIMIT 1)",
            array($item->viewid, $item->viewid));
        $item->hasaccessrules = !empty($item->access);
        $item->canbeviewed = $item->viewid ? can_view_view($item->viewid) : false;
        $item->pending = is_view_suspended($item->viewid);
        $item->submittedstatus = add_submitted_label($item->submittedstatus, (bool)$item->submissionoriginal);
        // Extra detail from the collection.
        $collection = new Collection($item->collectionid);
        $item->collectionurl = $collection->get_url();
        // Process SmartEvidence totals.
        $framework = new Framework($collection->get('framework'));
        $evidence = $framework->get_evidence($collection->get('id'));
        $item->evidence_begun = 0;
        $item->evidence_incomplete = 0;
        $item->evidence_partialcomplete = 0;
        $item->evidence_completed = 0;
        // If there are no evidences then $evidence will return false.
        if ($evidence) {
            // Each items in $evidence is a view. Each of these has a state and
            // that state is the status
            foreach ($evidence as $ev) {
                switch ($ev->state) {
                    case Framework::EVIDENCE_BEGUN:
                        $item->evidence_begun++;
                        break;

                    case Framework::EVIDENCE_INCOMPLETE:
                        $item->evidence_incomplete++;
                        break;

                    case Framework::EVIDENCE_PARTIALCOMPLETE:
                        $item->evidence_partialcomplete++;
                        break;

                    case Framework::EVIDENCE_COMPLETED:
                        $item->evidence_completed++;
                        break;
                }
                if (!in_array($ev->framework, $frameworks_checked)) {
                    $frameworks_checked[] = $ev->framework;
                    $states = Framework::get_evidence_statuses_for_display($ev->framework);
                    foreach ($states as $key => $state) {
                        $framework_statuses[$key] = $state;
                    }
                }
            }
        }
    }

    if (!empty($extra['csvdownload'])) {
        $csvfields = [
            'firstname',
            'lastname',
            'displayname',
            'username',
            'email',
            'title',
            'collectionid',
            'views',
            'hasaccessrules',
            'userurl',
            'collectionurl',
            'submittedstatus'
        ];
        foreach ($framework_statuses as $key => $state) {
            $csvfields[] = $state['statisticsid'];
        }
        $USER->set_download_file(generate_csv($data, $csvfields), $institution . 'smartevidencestatistics.csv', 'text/csv', true);
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
    $smarty->assign('statusestodisplay', $framework_statuses);
    $result['tablerows'] = $smarty->fetch('admin/users/smartevidencereport.tpl');

    return $result;
}

/**
 * Run the query for the SmartEvidence report.
 *
 * @param int $limit The number of records to return.
 * @param int $offset The offset of the first record to return.
 * @param array $extra Extra information to pass to the query.
 * @param string|null $institution The institution to restrict to.
 *
 * @return array<mixed>
 */
function smartevidence_stats_query($limit, $offset, $extra, $institution) {
    global $SESSION;
    $count = 0;
    $data = [];

    $start = !empty($extra['start']) ? $extra['start'] : null;
    $end = !empty($extra['end']) ? $extra['end'] : date('Y-m-d', strtotime('+1 day'));
    $users = $SESSION->get('usersforstats');

    // Construct the SQL.
    $fromsql = " FROM (
        SELECT
            u.id AS userid,
            u.username as username,
            u.deleted AS udeleted,
            u.email AS email,
            u.firstname AS firstname,
            u.lastname AS lastname,
            CONCAT(u.firstname, ' ', u.lastname) AS displayname,
            cv.view AS viewid,
            c.id AS collectionid,
            (SELECT COUNT(*) FROM {collection_view} WHERE collection = c.id) AS views,
            c.name AS title,
            c.ctime AS vctime,
            c.submittedstatus,
            c.submissionoriginal
        FROM {usr} u JOIN {collection} c ON c.owner = u.id
        JOIN {collection_view} cv ON cv.collection = c.id
        WHERE cv.displayorder = 0
        AND c.framework IS NOT NULL
    ) AS t";
    $wheresql = " WHERE userid != 0 AND udeleted = 0 AND collectionid IS NOT NULL";
    $where = [];
    if ($institution) {
        $fromsql .= " JOIN {usr_institution} ui ON (ui.usr = userid AND ui.institution = ?)";
        $where[] = $institution;
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

    if ($count < 1) {
        // If there are no results, $data is never used.
        return [$count, null];
    }

    // Build the SQL that will actually fetch the results.
    $sorttype = !empty($extra['sort']) ? $extra['sort'] : '';
    switch ($sorttype) {
        case 'firstname':
            $orderby = " firstname " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC') . ", lastname";
            break;
        case 'lastname':
            $orderby = " lastname " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC') . ", firstname";
            break;
        case 'id':
            $orderby = " collectionid " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case 'pagecount':
            $orderby = " views " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "collection":
            $orderby = " title " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC') . ", displayname";
            break;
        case "submittedstatus":
            $orderby = " c.submittedstatus " . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC') . ", title, viewid";
            break;
        case 'displayname':
        case 'username':
            // Order by sort type.
            $orderby = " $sorttype " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "owner":
        default:
            $orderby = " displayname " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC') . ", title, viewid";
    }
    $sql = "SELECT
        userid,
        username,
        firstname,
        lastname,
        displayname,
        email,
        viewid,
        collectionid,
        views,
        title,
        submittedstatus,
        submissionoriginal,
        vctime
        " . $fromsql . $wheresql . "
        ORDER BY " . $orderby;
    if (empty($extra['csvdownload'])) {
        $sql .= " LIMIT $limit OFFSET $offset";
    }
    $data = get_records_sql_array($sql, $where);

    return [$count, $data];
}

function accesslist_statistics_headers($extra, $urllink) {
    return array(
        array('id' => 'rownum', 'name' => '#'),
        array(
              'id' => 'owner', 'required' => true,
              'name' => get_string('Owner', 'view'),
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
        array('id' => 'submittedstatus', 'required' => false,
              'name' => get_string('submittedstatus', 'view'),
              'class' => format_class($extra, 'submittedstatus'),
              'link' => format_goto($urllink . '&sort=submittedstatus', $extra, array('sort'), 'submittedstatus'),
              'helplink' => get_help_icon('core', 'reports', 'pageactivity', 'submittedstatus')
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
    $end = !empty($extra['end']) ? $extra['end'] : date('Y-m-d', strtotime('+1 day'));
    $users = $SESSION->get('usersforstats');

    $fromsql = " FROM (
        SELECT u.id AS userid, u.deleted AS udeleted, CONCAT(u.firstname, ' ', u.lastname) AS displayname, cv.view AS viewid, c.id AS collectionid,
            c.submittedstatus AS submittedstatus, c.submissionoriginal AS submissionoriginal,
            (SELECT COUNT(*) FROM {collection_view} WHERE collection = c.id) AS views,
            c.name AS title, c.ctime AS vctime
        FROM {usr} u JOIN {collection} c ON c.owner = u.id
        JOIN {collection_view} cv ON cv.collection = c.id
        WHERE cv.displayorder = 0
        UNION
        SELECT u.id AS userid, u.deleted AS udeleted, CONCAT(u.firstname, ' ', u.lastname) AS displayname, v.id AS viewid, NULL AS collectionid,
            CASE WHEN v.type != 'profile' THEN v.submittedstatus ELSE NULL END AS submittedstatus,
            CASE WHEN v.type != 'profile' THEN v.submissionoriginal ELSE NULL END AS submissionoriginal,
            1 AS views, v.title, v.ctime AS vctime
        FROM {usr} u JOIN {view} v ON v.owner = u.id
        LEFT JOIN {collection_view} cv ON cv.view = v.id
        WHERE cv.collection IS NULL AND v.type !='dashboard'
        UNION
        SELECT u.id AS userid, u.deleted AS udeleted, CONCAT(u.firstname, ' ', u.lastname) AS displayname, NULL AS viewid, NULL AS collectionid,
             NULL AS submittedstatus, NULL AS submissionoriginal,
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
        case "submittedstatus":
            $orderby = " submittedstatus " . (!empty($extra['sortdesc']) ? 'ASC' : 'DESC') . ", title, viewid";
            break;
        case "owner":
        default:
            $orderby = " displayname " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC') . ", title, viewid";
    }
    $sql = "SELECT userid, displayname, viewid, collectionid, submittedstatus, submissionoriginal, views, title, vctime
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
            $item->title = get_string('noviews2', 'view');
        }
        $item->access = get_records_sql_array("
            SELECT *, 0 AS secreturls
            FROM {view_access} WHERE view = ? AND token IS NULL
            UNION
            (SELECT *, (SELECT COUNT(*) FROM {view_access} va2 WHERE token IS NOT NULL AND va2.view = va.view) AS secreturls
            FROM {view_access} va WHERE va.view = ? AND va.token IS NOT NULL LIMIT 1)",
            array($item->viewid, $item->viewid));
        $item->hasaccessrules = !empty($item->access);
        $item->canbeviewed = $item->viewid ? can_view_view($item->viewid) : false;
        $item->pending = is_view_suspended($item->viewid);
        $item->submittedstatus = add_submitted_label($item->submittedstatus, (bool)$item->submissionoriginal);
    }

    if (!empty($extra['csvdownload'])) {
        $csvfields = array('displayname', 'userurl', 'title', 'views', 'hasaccessrules', 'submittedstatus');
        $USER->set_download_file(generate_csv($data, $csvfields), $institution . 'accessstatistics.csv', 'text/csv', true);
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
    $result['settings']['end'] = date('Y-m-d', strtotime('+1 day'));
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
        $USER->set_download_file(generate_csv($registrationdata, $csvfields), 'institutionstatistics.csv', 'text/csv', true);
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
        $dataarray[$r->type][date("d M", $r->ts)] = $r->value;
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

function graph_institution_data_weekly($type = null, $institutiondata=null) {
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
        $dataarray[$r->type][date("d M", $r->ts)] = $r->value;
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
    $end = !empty($extra['end']) ? $extra['end'] : date('Y-m-d', strtotime('+1 day'));

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
        $USER->set_download_file(generate_csv($rawdata, $csvfields), 'userloginstatistics.csv', 'text/csv', true);
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
function users_active_data($limit=0, $offset=0, $extra=null) {
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
        if (!$USER->get('admin') && !$USER->get('staff') && !$USER->is_institutional_admin($institution)&& !$USER->is_institutional_supportadmin($institution) && !$USER->is_institutional_staff($institution)) {
            throw new AccessDeniedException("Institution::statistics | " . get_string('accessdenied', 'auth.webservice'));
        }
        $showall = false;
    }

    if (!in_array($type, $allowedtypes)) {
        $type = 'users';
    }
    $data = array();
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
            if ($subtype == 'assessments') {
                $data = assessment_statistics($extra->limit, $extra->offset, $extra->extra);
            }
            else {
                $data = group_statistics($extra->limit, $extra->offset, $extra->extra);
            }
            break;
         case 'users':
         default:
            if ($subtype == 'accesslist') {
                $data = accesslist_statistics($extra->limit, $extra->offset, $extra->extra, null);
            }
            else if ($subtype == 'smartevidence') {
                $data = smartevidence_statistics($extra->limit, $extra->offset, $extra->extra);
            }
            else if ($subtype == 'masquerading') {
                if (!in_array(get_config('eventloglevel'), array('masquerade', 'all'))) {
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
            else if ($subtype == 'verifiersummary') {
                $data = verifiersummary_statistics($extra->limit, $extra->offset, $extra->extra, null);
            }
            else if ($subtype == 'completionverification') {
                $data = completionverification_statistics($extra->limit, $extra->offset, $extra->extra, null);
            }
            else if ($subtype == 'portfolioswithverifiers') {
                $data = portfolioswithverifiers_statistics($extra->limit, $extra->offset, $extra->extra, null);
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
            if ($subtype == 'assessments') {
                $data = assessment_statistics($extra->limit, $extra->offset, $extra->extra, $institution);
            }
            else {
                $data = array('notvalid_errorstring' => get_string('nogroupdataperinstitution', 'statistics'));
            }
            break;
         case 'users':
         default:
            if ($subtype == 'accesslist') {
                $data = accesslist_statistics($extra->limit, $extra->offset, $extra->extra, $institution);
            }
            else if ($subtype == 'smartevidence') {
                $data = smartevidence_statistics($extra->limit, $extra->offset, $extra->extra, $institution);
            }
            else if ($subtype == 'masquerading') {
                if (!in_array(get_config('eventloglevel'), array('masquerade', 'all'))) {
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
            else if ($subtype == 'verifiersummary') {
                $data = verifiersummary_statistics($extra->limit, $extra->offset, $extra->extra, $institution);
            }
            else if ($subtype == 'completionverification') {
                $data = completionverification_statistics($extra->limit, $extra->offset, $extra->extra, $institution);
            }
            else if ($subtype == 'portfolioswithverifiers') {
                $data = portfolioswithverifiers_statistics($extra->limit, $extra->offset, $extra->extra, $institution);
            }
            else {
                $data = institution_user_statistics($extra->limit, $extra->offset, $institutiondata, $extra->extra);
            }
        }
    }

    return array($allowedtypes, $data);
}

function get_portfolio_filters($institution) {
    global $SESSION;

    $portfoliofilteroptions = array();
    $records = array();
    $sql = "SELECT c2.name, c2.institution AS parent, ct.originaltemplate
            FROM {collection} c1
            JOIN {collection_template} ct ON ct.collection = c1.id
            JOIN {collection} c2 ON c2.id = ct.originaltemplate
            ";

    if ($institution == 'all') {
        // find all institutions
        $wheresql = ' WHERE c2.institution IS NOT NULL';
        $records = get_records_sql_array($sql . $wheresql);
    }
    else {
        $wheresql = ' WHERE c2.institution = ?';
        $records = get_records_sql_array($sql . $wheresql, array($institution));
    }

    if ($records) {
        foreach ($records as $template) {
            $portfoliofilteroptions[$template->originaltemplate] = $template->name;
        }
    }

    return $portfoliofilteroptions;
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
    global $USER, $SESSION;

    $type = isset($extra->type) ? $extra->type : null;
    $subtype = isset($extra->subtype) ? $extra->subtype : $type;
    $institution = isset($extra->institution) ? $extra->institution : null;

    if (!$institution || !$USER->can_edit_institution($institution, true)) {
        $institution = empty($institutionelement['value']) ? $institutionelement['defaultvalue'] : $institutionelement['value'];
    }
    else if ($institution) {
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
        'class'           => 'form-as-button float-start',
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
        'defaultvalue' => !empty($extra->extra) && isset($extra->extra['end']) ? strtotime($extra->extra['end']) : strtotime('+1 day'),
        'caloptions' => array(
            'showsTime' => false,
        ),
    );

    $verifierfilteroptions = array(
        'all' => get_string('verifieroptions_all', 'statistics'),
        'current' => get_string('verifieroptions_current', 'statistics'),
        'none' => get_string('verifieroptions_none', 'statistics'),
    );

    if ($extra->subtype == 'completionverification' || $extra->subtype == 'verifiersummary' || $extra->subtype == 'portfolioswithverifiers') {
        $portfoliofilteroptions = get_portfolio_filters($extra->institution);
        if ($portfoliofilteroptions) {
            $form['elements']['portfoliofilter'] = array(
                'type' => 'select',
                'isSelect2' => true,
                'title' => get_string('portfoliofilter', 'statistics'),
                'description' => get_string('portfoliofilterdescription', 'statistics'),
                'multiple' => true,
                'collapseifoneoption' => false,
                'options' => $portfoliofilteroptions,
                'defaultvalue' => !empty($extra->extra) && isset($extra->extra['portfoliofilter']) ? $extra->extra['portfoliofilter'] : null,
            );
            if ($portfolios = $SESSION->get('portfoliofilter')) {
                $form['elements']['portfoliofilter']['defaultvalue'] = $portfolios;
            }
        }
    }

    if ($extra->subtype == 'completionverification') {
        $form['elements']['verifierfilter'] = array(
            'type' => 'select',
            'isSelect2' => true,
            'title' => get_string('portfolioverifierfilter', 'statistics'),
            'description' => get_string('portfolioveriferfilterdescription', 'statistics'),
            'multiple' => false,
            'options' => $verifierfilteroptions,
            'defaultvalue' => !empty($extra->extra) && !empty($extra->extra['verifierfilter']) ? $extra->extra['verifierfilter'] : 'all',
        );
        if ($verifiers = $SESSION->get('verifierfilter')) {
            $form['elements']['verifierfilter']['defaultvalue'] = $verifiers;
        }
    }

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
                'class' => 'first last',
                'elements' => $headerelements,
                'legend' => get_string('Columns', 'admin'),
                'collapsible' => true,
                'collapsed'   => true,
            );
        }
    }

    $form['elements']['submit'] = array(
        'type'  => 'submitcancel',
        'subclass' => array('btn-primary'),
        'value' => array(get_string('submit'), get_string('cancel')),
        'goto'  => format_goto(get_config('wwwroot') . 'admin/users/statistics.php?institution=' . $institution, $extra->extra, array('sort', 'sortdesc')),
    );

    return $form;
}

function format_goto($url, $data, $ignore=array(), $currentsort=null) {
    static $allowed_keys = array('id', 'start', 'end', 'users', 'sort', 'sortdesc', 'portfoliofilter', 'verifierfilter');

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
                            $url .= $firstjoin . hsc($key) . '[]=' . hsc($v);
                        }
                        else {
                            $url .= '&' . hsc($key) . '[]=' . hsc($v);
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
    $goto = $form->get_element_option('submit', 'goto');
    redirect($goto);
}

function reportconfigform_submit(Pieform $form, $values) {
    global $SESSION;

    $goto = $form->get_element_option('submit', 'goto');
    // Get the type/subtype values from select field
    list($type, $subtype) = explode('_', $values['typesubtype']);
    $goto .= '&type=' . $type . '&subtype=' . $subtype;

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
    if (isset($values['portfoliofilter'])) {
        $SESSION->set('portfoliofilter', $values['portfoliofilter']);
    }
    if (isset($values['verifierfilter'])) {
        $SESSION->set('verifierfilter', $values['verifierfilter']);
    }

    $form->reply(PIEFORM_OK, array(
        'message' => get_string('applyingfilters', 'statistics'),
        'goto' => $goto,
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

    // Get correct subtypes for 'information' type.
    if (!empty($institution) && $institution != 'all') {
        $infooptions = array(
            'information_information' => get_string('Overview', 'statistics')
        );
    }
    else {
        $infooptions = array('information_information' => get_string('Overview', 'statistics'),
                             'information_comparisons' => get_string('reportinstitutioncomparison', 'statistics'),
                             'information_logins' => get_string('logins', 'statistics'),
        );
    }
    asort($infooptions);

    // Get correct subtypes for 'users' type
    $usersoptions = array(
        'users_users' => get_string('peoplereports', 'statistics'),
        'users_pageactivity' => get_string('reportpageactivity', 'statistics'),
        'users_accesslist' => get_string('reportaccesslist', 'statistics'),
        'users_smartevidence' => get_string('reportsmartevidence', 'statistics'),
        'users_masquerading' => get_string('reportmasquerading', 'statistics'),
        'users_userdetails' => get_string('reportuserdetails', 'statistics'),
        'users_useragreement' => get_string('reportuseragreement', 'statistics'),
        'users_verifiersummary' => get_string('reportverifiersummary', 'statistics'),
        'users_completionverification' => get_string('reportcompletionverification', 'statistics'),
        'users_portfolioswithverifiers' => get_string('reportportfolioswithverifiers', 'statistics')
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
            'options' => array(
                'groups_groups' => get_string('Groups', 'admin'),
            ),
        );
        if (is_plugin_active('assessmentreport', 'module')) {
            $optgroups['groups']['options']['groups_assessments'] = get_string('submissions', 'statistics');
        }
    }
    else {
        if (is_plugin_active('assessmentreport', 'module')) {
            $optgroups['groups'] = array(
                'label' => get_string('Groups', 'admin'),
                'options' => array(
                    'groups_assessments' => get_string('submissions', 'statistics')
                ),
            );
        }
    }

    // But ignore $optgroups above if $USER is only institution staff and only allowed to see old user related reports
    $allstaffstats = get_config('staffstats');
    $userstaffstats = get_config('staffreports'); // The old 'Users/access list/masquerading' reports from users section
    if (!empty($institution)) {
        if (!$USER->get('admin') && !$USER->is_institutional_admin($institution) &&
           ($USER->is_institutional_staff($institution) || $USER->is_institutional_supportadmin($institution)) && empty($allstaffstats) && !empty($userstaffstats)) {
            $infooptions = array(
                'information_information' => get_string('Overview', 'statistics')
            );
            $usersoptions = array(
                'users_accesslist' => get_string('reportaccesslist', 'statistics'),
                'users_smartevidence' => get_string('reportsmartevidence', 'statistics'),
                'users_masquerading' => get_string('reportmasquerading', 'statistics'),
                'users_userdetails' => get_string('reportuserdetails', 'statistics'),
                'users_useragreement' => get_string('reportuseragreement', 'statistics'),
            );
            asort($usersoptions);
            $optgroups = array(
                'information' => array(
                    'label' => get_string('Institution', 'admin'),
                    'options' => $infooptions,
                ),
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
                     <span class="visually-hidden">' . get_string('removefilter', 'statistics') . '</span>
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
    $allstaffstats = get_config('staffstats');
    $userstaffstats = get_config('staffreports'); // The old 'Users/access list/masquerading' reports from users section
    if (($USER->is_institutional_staff($institution) || $USER->is_institutional_supportadmin($institution)) && !empty($allstaffstats)) {
        return true;
    }
    if (($USER->is_institutional_staff($institution) || $USER->is_institutional_supportadmin($institution)) && empty($allstaffstats) && !empty($userstaffstats)) {
        if (in_array($report, array('accesslist', 'masquerading', 'userdetails', 'useragreement'))) {
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
        case "assessments":
            $date = get_field_sql("SELECT MIN(datesubmitted) ctime FROM {module_assessmentreport_history}");
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

/**
 * @param $limit
 * @param $offset
 * @param $extra
 * @param null $institution
 * @return array
 * @throws ParameterException
 * @throws SQLException
 */
function assessment_statistics($limit, $offset, $extra, $institution = null) {
    $data = array();
    $urllink = get_config('wwwroot') . 'admin/users/statistics.php?type=groups&subtype=assessments';
    if ($institution) {
        $urllink .= '&institution=' . $institution;
    }
    $data['tableheadings'] = assessments_statistics_headers($extra, $urllink);

    $activeheadings = get_active_columns($data, $extra);
    $extra['columns'] = array_keys($activeheadings);

    $data['table'] = assessment_statistics_table($limit, $offset, $extra, $institution, $urllink);
    $data['table']['activeheadings'] = $activeheadings;

    $data['summary'] = $data['table']['count'] == 0 ? get_string('nostats', 'admin') : null;

    return $data;
}

/**
 * @param $extra
 * @param $urllink
 * @return array
 */
function assessments_statistics_headers($extra, $urllink) {
    return array(
        array('id' => 'rownum', 'name' => '#'),
        array('id' => 'type',
            'required' => true,
            'name' => get_string('assessmenttype', 'statistics'),
            'class' => format_class($extra, 'type'),
            'link' => format_goto($urllink . '&sort=type', $extra, array('sort'), 'type')
        ),
        array('id' => 'viewname',
            'required' => true,
            'name' => get_string('Portfolio', 'view'),
            'class' => format_class($extra, 'viewname'),
            'link' => format_goto($urllink . '&sort=viewname', $extra, array('sort'), 'viewname'),
        ),
        array('id' => 'owner',
            'required' => true,
            'name' => get_string('Owner', 'view'),
            'link' => format_goto($urllink . '&sort=owner', $extra, array('sort'), 'owner'),
        ),
        array('id' => 'group',
            'required' => true,
            'name' => get_string('Group', 'group'),
            'link' => format_goto($urllink . '&sort=group', $extra, array('sort'), 'group'),
            ),
        array('id' => 'submitted',
            'required' => true,
            'name' => get_string('assessmensubmitted', 'statistics'),
            'class' => format_class($extra, 'assessmensubmitted'),
            'link' => format_goto($urllink . '&sort=submitted', $extra, array('sort'), 'submitted'),
        ),
        array('id' => 'released',
            'required' => true,
            'name' => get_string('assessmentreleaseddate', 'statistics'),
            'class' => format_class($extra, 'assessmentreleaseddate'),
            'link' => format_goto($urllink . '&sort=released', $extra, array('sort'), 'released'),
        ),
        array('id' => 'marker',
            'required' => true,
            'name' => get_string('assessmentmarker', 'statistics'),
            'class' => format_class($extra, 'assessmentmarker'),
            'link' => format_goto($urllink . '&sort=marker', $extra, array('sort'), 'marker'),
        ),
    );
}

/**
 * @param $limit
 * @param $offset
 * @param $extra
 * @param $institution
 * @param $urllink
 * @return array
 * @throws ParameterException
 * @throws SQLException
 */
function assessment_statistics_table($limit, $offset, $extra, $institution, $urllink) {
    global $USER, $SESSION;

    $start = !empty($extra['start']) ? $extra['start'] : date('Y-m-d', strtotime("-1 months"));
    $end = !empty($extra['end']) ? $extra['end'] : date('Y-m-d', strtotime('+1 day'));
    $users = $SESSION->get('usersforstats');

    $fromsql = " FROM {module_assessmentreport_history} ah
                    JOIN {usr} u ON ah.userid = u.id
                    LEFT JOIN {usr} m ON ah.markerid = m.id
                    LEFT JOIN {view} v ON v.id = ah.itemid AND ah.event = 'view'
                    LEFT JOIN {group} g ON g.id = ah.groupid
                    LEFT JOIN {collection} c ON c.id = ah.itemid AND ah.event = 'collection'";

    $wheresql = " WHERE u.id !=0";
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
        $wheresql .= " AND ah.datesubmitted >= DATE(?) AND ah.datesubmitted <= DATE(?)";
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

    $sorttype = !empty($extra['sort']) ? $extra['sort'] : '';
    switch ($sorttype) {
        case "type":
            $orderby = " ah.event " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "viewname":
            $orderby = " viewname " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "owner":
            $orderby = " u.username " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "group":
            $orderby = " g.name " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "released":
            $orderby = " ah.datereleased " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "marker":
            $orderby = " m.username " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
            break;
        case "submitted":
        default:
            $orderby = " ah.datesubmitted " . (!empty($extra['sortdesc']) ? 'DESC' : 'ASC');
    }

    $sql = "SELECT ah.id, ah.itemid, ah.userid, ah.markerid, g.name AS groupname,
            CASE WHEN c.id IS NULL THEN v.title ELSE c.name END AS viewname,
            ah.event, ah.datesubmitted AS submitted, ah.datereleased AS released
           " . $fromsql . $wheresql . " ORDER BY " . $orderby;

    if (empty($extra['csvdownload'])) {
        $sql .= " LIMIT $limit OFFSET $offset";
    }

    $data = get_records_sql_array($sql, $where);

    if ($data) {
        foreach ($data as &$item) {
            $item->owner = display_name($item->userid);
            if ($item->markerid > 0) {
                $item->marker = display_name($item->markerid);
            }
            else {
                $item->marker = 0;
            }
            $item->type = get_string(ucfirst($item->event), $item->event);
        }
    }

    if (!empty($extra['csvdownload'])) {
        $csvfields = array('type', 'viewname', 'owner', 'groupname',  'submitted', 'released', 'marker');
        $USER->set_download_file(generate_csv($data, $csvfields), $institution . 'assessmentstatistics.csv', 'text/csv', true);
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
    $result['tablerows'] = $smarty->fetch('admin/assessmentstats.tpl');

    return $result;
}
