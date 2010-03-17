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
    $registrationurl = 'http://mahara.org/api/registration.php';
    $data = registration_data();
    $request = array(
        CURLOPT_URL        => $registrationurl,
        CURLOPT_POST       => 1,
        CURLOPT_POSTFIELDS => $data,
    );
    return mahara_http_request($request);
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

    $data_to_send['newstats'] = 1;

    return $data_to_send;
}

function site_data_current() {
    return array(
        'users' => count_records_select('usr', 'id > 0 AND deleted = 0'),
        'groups' => count_records('group', 'deleted', 0),
        'views' => count_records_select('view', 'owner <> 0'),
        'rank' => array(
            'users' => get_config('usersrank'),
            'groups' => get_config('groupsrank'),
            'views' => get_config('viewsrank'),
        ),
    );
}

function site_statistics($full=false) {
    $data = array();

    if ($full) {
        $data = site_data_current();
        if (file_exists(get_config('dataroot') . 'weekly.png')) {
            $data['weekly'] = get_config('wwwroot') . 'admin/thumb.php?type=weekly';
        }

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
        $data['groupmemberaverage'] = $memberships/$data['users'];
        $data['strgroupmemberaverage'] = get_string('groupmemberaverage', 'admin', $data['groupmemberaverage']);
        $data['viewsperuser'] = get_field_sql("
            SELECT (0.0 + COUNT(id)) / NULLIF(COUNT(DISTINCT owner), 0)
            FROM {view}
            WHERE NOT owner IS NULL AND owner > 0
        ");
        $data['strviewsperuser'] = get_string('viewsperuser', 'admin', $data['viewsperuser']);
    }

    $data['name']        = get_config('sitename');
    $data['release']     = get_config('release');
    $data['version']     = get_config('version');
    $data['latest_version'] = get_config('latest_version');
    $data['installdate'] = format_date(strtotime(get_config('installation_time')), 'strftimedate');
    $data['dbsize']      = db_total_size();
    $data['diskusage']   = get_field('site_data', 'value', 'type', 'disk-usage');
    $data['cronrunning'] = !record_exists_select('cron', 'nextrun < CURRENT_DATE');

    if ($data['release'] == $data['latest_version']) {
        $data['strlatestversion'] = get_string('uptodate', 'admin');
    }
    else {
        $download_page = 'https://launchpad.net/mahara/+download';
        $data['strlatestversion'] = get_string('latestversionis', 'admin', $download_page, $data['latest_version']);
    }

    $data['strrankingsupdated'] = get_string('rankingsupdated', 'admin', date('Y-m-d H:i', get_config('registration_lastsent')));

    return($data);
}

function user_statistics($limit, $offset, &$sitedata) {
    $data = array();
    $data['tableheadings'] = array(
        get_string('date'),
        get_string('Loggedin', 'admin'),
        get_string('Created'),
        get_string('Total'),
    );
    $data['table'] = user_stats_table($limit, $offset);
    $maxfriends = get_records_sql_array("
        SELECT u.id, u.firstname, u.lastname, u.preferredname, SUM(f.friends) AS friends
        FROM {usr} u INNER JOIN (
            SELECT DISTINCT(usr1) AS id, COUNT(usr1) AS friends
            FROM {usr_friend}
            GROUP BY usr1
            UNION SELECT DISTINCT(usr2) AS id, COUNT(usr2) AS friends
            FROM {usr_friend}
            GROUP BY usr2
        ) f ON u.id = f.id
        GROUP BY u.id, u.firstname, u.lastname, u.preferredname
        ORDER BY friends DESC
        LIMIT 1", array());
    $maxfriends = $maxfriends[0];
    $meanfriends = 2 * count_records('usr_friend') / $sitedata['users'];
    if ($maxfriends) {
        $data['strmaxfriends'] = get_string(
            'statsmaxfriends',
            'admin',
            $meanfriends,
            get_config('wwwroot') . 'user/view.php?id=' . $maxfriends->id,
            display_name($maxfriends, null, true),
            $maxfriends->friends
        );
    }
    else {
        $data['strmaxfriends'] = get_string('statsnofriends', 'admin');
    }
    $maxviews = get_records_sql_array("
        SELECT u.id, u.firstname, u.lastname, u.preferredname, COUNT(v.*) AS views
        FROM {usr} u JOIN {view} v ON u.id = v.owner
        WHERE owner <> 0
        GROUP BY u.id, u.firstname, u.lastname, u.preferredname
        ORDER BY views DESC
        LIMIT 1", array());
    $maxviews = $maxviews[0];
    if ($maxviews) {
        $data['strmaxviews'] = get_string(
            'statsmaxviews',
            'admin',
            $sitedata['viewsperuser'],
            get_config('wwwroot') . 'user/view.php?id=' . $maxviews->id,
            display_name($maxviews, null, true),
            $maxviews->views
        );
    }
    else {
        $data['strmaxviews'] = get_string('statsnoviews', 'admin');
    }
    $maxgroups = get_records_sql_array("
        SELECT u.id, u.firstname, u.lastname, u.preferredname, COUNT(m.group) AS groups
        FROM {usr} u JOIN {group_member} m ON u.id = m.member JOIN {group} g ON m.group = g.id
        WHERE g.deleted = 0
        GROUP BY u.id, u.firstname, u.lastname, u.preferredname
        ORDER BY groups DESC
        LIMIT 1", array());
    $maxgroups = $maxgroups[0];
    if ($maxgroups) {
        $data['strmaxgroups'] = get_string(
            'statsmaxgroups',
            'admin',
            $sitedata['groupmemberaverage'],
            get_config('wwwroot') . 'user/view.php?id=' . $maxgroups->id,
            display_name($maxgroups, null, true),
            $maxgroups->groups
        );
    }
    else {
        $data['strmaxgroups'] = get_string('statsnogroups', 'admin');
    }
    $maxquotaused = get_records_sql_array("
        SELECT id, firstname, lastname, preferredname, quotaused
        FROM {usr}
        WHERE deleted = 0 AND id > 0
        ORDER BY quotaused DESC
        LIMIT 1", array());
    $maxquotaused = $maxquotaused[0];
    $data['strmaxquotaused'] = get_string(
        'statsmaxquotaused',
        'admin',
        display_size(get_field('usr', 'AVG(quotaused)', 'deleted', 0)),
        get_config('wwwroot') . 'user/view.php?id=' . $maxquotaused->id,
        display_name($maxquotaused, null, true),
        display_size($maxquotaused->quotaused)
    );

    $smarty = smarty_core();
    $smarty->assign('data', $data);
    $data['summary'] = $smarty->fetch('admin/userstatssummary.tpl');

    return $data;
}

function user_stats_table($limit, $offset) {
    $count = count_records('site_data', 'type', 'user-count-daily');

    $pagination = build_pagination(array(
        'id' => 'stats_pagination',
        'url' => get_config('wwwroot') . 'admin/statistics.php?type=users',
        'jsonscript' => 'admin/statistics.json.php',
        'datatable' => 'statistics_table',
        'count' => $count,
        'limit' => $limit,
        'offset' => $offset,
    ));

    $result = array(
        'tablerows'     => '',
        'pagination'    => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
    );

    if ($count < 1) {
        return $result;
    }

    $day = is_postgres() ? "to_date(t.ctime::text, 'YYYY-MM-DD')" : 'DATE(t.ctime)';

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
        "SELECT ctime, type, value, $day AS date
        FROM {site_data}
        WHERE type IN (?,?) AND ctime >= ? AND ctime < (date(?) + (INTERVAL $dayinterval))
        ORDER BY type = ? DESC, ctime DESC",
        array('user-count-daily', 'loggedin-users-daily', $daterange->mindate, $daterange->maxdate, 'user-count-daily')
    );

    $userscreated = get_records_sql_array(
        "SELECT $day AS date, COUNT(id) AS users
        FROM {usr}
        WHERE NOT ctime IS NULL
        GROUP BY date
        ORDER BY date",
        array(),
        $offset,
        $limit
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
                if (isset($data[$r->date])) {
                    $data[$r->date]['created'] = $r->users;
                }
            }
        }
    }

    $smarty = smarty_core();
    $smarty->assign('data', $data);
    $result['tablerows'] = $smarty->fetch('admin/userstats.tpl');

    return $result;
}

function group_statistics($limit, $offset) {
    $data = array();
    $data['tableheadings'] = array(
        '#',
        get_string('Group', 'group'),
        get_string('Members', 'group'),
        get_string('views'),
        get_string('nameplural', 'interaction.forum'),
        get_string('Posts', 'interaction.forum'),
    );
    $data['table'] = group_stats_table($limit, $offset);

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
    $data['summary'] = $smarty->fetch('admin/groupstatssummary.tpl');

    return $data;
}

function group_stats_table($limit, $offset) {
    $count = count_records('group', 'deleted', 0);

    $pagination = build_pagination(array(
        'id' => 'stats_pagination',
        'url' => get_config('wwwroot') . 'admin/statistics.php?type=groups',
        'jsonscript' => 'admin/statistics.json.php',
        'datatable' => 'statistics_table',
        'count' => $count,
        'limit' => $limit,
        'offset' => $offset,
    ));

    $result = array(
        'tablerows'     => '',
        'pagination'    => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
    );

    if ($count < 1) {
        return $result;
    }

    $groupdata = get_records_sql_array(
        "SELECT
            g.id, g.name, mc.members, vc.views, fc.forums, pc.posts
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
            mc.members DESC",
        array(),
        $offset,
        $limit
    );

    $smarty = smarty_core();
    $smarty->assign('data', $groupdata);
    $smarty->assign('offset', $offset);
    $result['tablerows'] = $smarty->fetch('admin/groupstats.tpl');

    return $result;
}

function view_statistics($limit, $offset) {
    $data = array();
    $data['tableheadings'] = array(
        '#',
        get_string('view'),
        get_string('Owner', 'view'),
        get_string('Visits'),
        get_string('feedback', 'view'),
    );
    $data['table'] = view_stats_table($limit, $offset);

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
    $data['summary'] = $smarty->fetch('admin/viewstatssummary.tpl');

    return $data;
}

function view_stats_table($limit, $offset) {
    $count = count_records('view');

    $pagination = build_pagination(array(
        'id' => 'stats_pagination',
        'url' => get_config('wwwroot') . 'admin/statistics.php?type=views',
        'jsonscript' => 'admin/statistics.json.php',
        'datatable' => 'statistics_table',
        'count' => $count,
        'limit' => $limit,
        'offset' => $offset,
    ));

    $result = array(
        'tablerows'     => '',
        'pagination'    => $pagination['html'],
        'pagination_js' => $pagination['javascript'],
    );

    if ($count < 1) {
        return $result;
    }

    $viewdata = get_records_sql_array(
        "SELECT
            v.id, v.title, v.owner, v.group, v.institution, v.visits,
            u.firstname, u.lastname,
            COUNT(vf.*) AS comments
        FROM {view} v
            LEFT JOIN {view_feedback} vf ON v.id = vf.view
            LEFT JOIN {usr} u ON v.owner = u.id
            LEFT JOIN {group} g ON v.group = g.id
            LEFT JOIN {institution} i ON v.institution = i.name
        GROUP BY v.id, v.title, v.owner, v.group, v.institution, v.visits,
            u.firstname, u.lastname
        ORDER BY v.visits DESC",
        array(),
        $offset,
        $limit
    );

    foreach ($viewdata as &$v) {
        $v->author = $v->owner ? display_name($v->owner) : null;
    }

    $smarty = smarty_core();
    $smarty->assign('data', $viewdata);
    $smarty->assign('offset', $offset);
    $result['tablerows'] = $smarty->fetch('admin/viewstats.tpl');

    return $result;
}

function graph_site_data_weekly() {

    $lastyear = db_format_timestamp(time() - 60*60*12*365);
    $values = array($lastyear, 'view-count', 'user-count', 'group-count');
    $weekly = get_records_sql_array('
        SELECT ctime, type, value, ' . db_format_tsfield('ctime', 'ts') . '
        FROM {site_data}
        WHERE ctime >= ? AND type IN (?,?,?)
        ORDER BY ctime, type', $values);

    if (!count($weekly) > 1) {
        return;
    }

    $dataarray = array();
    foreach ($weekly as &$r) {
        $dataarray[$r->type][strftime("%d\n%b", $r->ts)] = $r->value;
    }

    require_once(get_config('libroot') . "pear/Image/Graph.php");

    $Graph =& Image_Graph::factory('graph', array(300, 200));
    $Font =& $Graph->addNew('font', 'Vera');
    $Font->setSize(8);
    $Graph->setFont($Font);

    $Graph->add(
        Image_Graph::vertical(
            $Plotarea = Image_Graph::factory('plotarea'),
            $Legend = Image_Graph::factory('legend'),
            88
        )
    );

    $Legend->setPlotarea($Plotarea);

    $datasetinfo = array(
        'user-count'  => array('color' => 'red@0.4', 'name' => get_string('users')),
        'view-count'  => array('color' => 'green@0.4', 'name' => get_string('views')),
        'group-count' => array('color' => 'blue@0.4', 'name' => get_string('groups')),
    );

    foreach (array_keys($datasetinfo) as $k) {
        $dataset =& Image_Graph::factory('dataset', array($dataarray[$k]));
        $dataset->setName($datasetinfo[$k]['name']);
        $plot =& $Plotarea->addNew('line', array(&$dataset));
        $linestyle =& Image_Graph::factory('Image_Graph_Line_Solid', array($datasetinfo[$k]['color']));
        $linestyle->setThickness(3);
        $plot->setLineStyle($linestyle);
    }

    $Graph->done(array('filename' => get_config('dataroot') . 'weekly.png'));
}

?>
