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
function register_site($registered = null)  {
    $strfield = get_string('Field', 'admin');
    $strvalue = get_string('Value', 'admin');
    $info = <<<EOF

<table class="table table-striped table-bordered" id="register-table">
    <thead>
        <tr>
            <th>$strfield</th>
            <th>$strvalue</th>
        </tr>
    </thead>
    <tbody>
EOF;
    $data = registration_data();
    // Format each line of data to be sent.
    foreach($data as $key => $val) {
        $info .= '<tr><th>'. hsc($key) . '</th><td>' . hsc($val) . "</td></tr>\n";
    }
    $info .= '</tbody></table>';

    $form = array(
        'name' => 'register',
        'autofocus' => false,
        'elements' => array(
            'whatsent' => array(
                'type' => 'fieldset',
                'legend' => get_string('dataincluded', 'admin'),
                'collapsible'  => true,
                'collapsed'    => true,
                'class' => 'last',
                'elements' => array(
                    'info' => array(
                        'type' => 'markup',
                        'value'=> $info,
                    ),
                )
            ),
            'registeryesno' => array(
                'type' => 'switchbox',
                'title' => get_string('registerwithmahara', 'admin'),
                'description' => get_string('registerwithmaharadescription', 'admin'),
                'defaultvalue' => $registered,
                'disabled' => $registered,
            ),
            'sendweeklyupdates' => array(
                'type'         => 'switchbox',
                'title'        => get_string('sendweeklyupdates', 'admin'),
                'description'  => get_string('sendweeklyupdatesdescription', 'admin'),
                'defaultvalue' => (!$registered || get_config('registration_sendweeklyupdates')),
                'class'        => 'd-none',
            ),
            'register' => array(
                'type' => 'submitcancel',
                'class' => 'btn-primary',
                'value' => array(get_string('save', 'mahara'), get_string('cancel', 'mahara')),
            ),
        ),
    );
    return pieform($form);
}
/**
 * Runs when registration form is submitted
 */
function register_submit(Pieform $form, $values) {
    global $SESSION;

    // If there is a timecode in this field, the site was registered at this time.
    $registered = get_config('registration_lastsent') && !get_config('new_registration_policy');
    // Depending on if the site was registered previously and what value was submitted in the 'sendweeklyupdates' field,
    // there are three options:
    $registerchanged = (!$registered && $values['registeryesno']);
    $weeklyupdateschanged =
            ($registered || $values['registeryesno']) &&
            (get_config('registration_sendweeklyupdates') != $values['sendweeklyupdates']);

    // 1. cancel (i.e, the user made no changes)
    if (!$registerchanged  && !$weeklyupdateschanged) {
        register_cancel_register();
    }
    // 2. add/remove weekly updates
    else if ($registered && $weeklyupdateschanged) {
        update_weeklyupdates($values);
    }

    // 3. registering, continue
    $result = registration_send_data();
    $data = json_decode($result->data);

    if ($data->status != 1) {
        log_info($result);
        $SESSION->add_error_msg(get_string('registrationfailedtrylater', 'admin', $result->info['http_code']));
    }
    else {
        set_config('registration_lastsent', time());
        if (!get_config('registration_firstsent')) {
            set_config('registration_firstsent', time());
        }
        set_config('registration_sendweeklyupdates', $values['sendweeklyupdates']);
        if (get_config('new_registration_policy')) {
            set_config('new_registration_policy', false);
        }
        $SESSION->add_ok_msg(get_string('registrationsuccessfulthanksforregistering', 'admin'));
        $info = '
<h4>' . get_string('datathathavebeensent', 'admin') . '</h4>
<table class="table table-striped table-bordered" id="register-table">
    <thead>
        <tr>
            <th> ' . get_string('Field', 'admin') . '</th>
            <th> ' . get_string('Value', 'admin') . '</th>
        </tr>
    </thead>
    <tbody>
';
        $datasent = registration_data();
        foreach($datasent as $key => $val) {
            $info .= '<tr><th>'. hsc($key) . '</th><td>' . hsc($val) . "</td></tr>\n";
        }
        $info .= '</tbody></table>';

        $SESSION->add_ok_msg($info, false);
    }
    redirect('/admin/index.php');
}

/**
 * Runs when the 'Weekly updates' switch is changed
 */

function update_weeklyupdates($values) {
    global $SESSION;

    set_config('registration_sendweeklyupdates', $values['sendweeklyupdates']);
    if (get_config('new_registration_policy')) {
        set_config('new_registration_policy', false);
    }
    if ($values['sendweeklyupdates']) {
        $SESSION->add_ok_msg(get_string('startsendingdata', 'admin'), false);
    }
    else {
        $SESSION->add_ok_msg(get_string('stoppedsendingdata', 'admin'));
    }
    redirect('/admin/index.php');
}

/**
 * Runs when registration form is cancelled
 */
function register_cancel_register() {
    global $SESSION;

    if (get_config('new_registration_policy')) {
        $SESSION->add_ok_msg(get_string('registrationcancelled', 'admin', get_config('wwwroot')), false);
    }

    redirect('/admin/index.php');
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

    // System information
    $data_to_send['phpversion'] = phpversion();
    $data_to_send['dbversion'] = get_field_sql('SELECT VERSION()');
    $osversion = php_uname('s');
    if ($osversion == 'Linux') {
        $lsbversion = exec('lsb_release -d', $execout, $return_val);
        if ($return_val === 0) {
            $osversion = $lsbversion;
        }
        else {
            $osversion = php_uname();
        }
    }
    $data_to_send['osversion'] = $osversion;
    $data_to_send['phpsapi'] = php_sapi_name();
    if (!empty($_SERVER) && !empty($_SERVER['SERVER_SOFTWARE'])) {
        $data_to_send['webserver'] = $_SERVER['SERVER_SOFTWARE'];
    }
    $modules = get_loaded_extensions();
    natcasesort($modules);
    $data_to_send['phpmodules'] = '; ' . implode('; ', $modules) . ';';

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
            $membersquery = 'SELECT id FROM {usr}
                    WHERE deleted = 0 AND id > 0 AND
                    id NOT IN (SELECT usr FROM {usr_institution})';
            $membersqueryparams = array();
        }
        else {
            $membersquery = 'SELECT usr FROM {usr_institution} ui
                    JOIN {usr} u ON (u.id = ui.usr)
                    WHERE u.deleted = 0 AND ui.institution = ?';
            $membersqueryparams = array($institution);
        }
        $inst_data['count_members'] = count_records_sql('SELECT count(*) FROM {usr}
                WHERE id IN (' . $membersquery . ')',
                $membersqueryparams);
        if ($inst_data['count_members'] == 0) {
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
                    WHERE v.owner IS NOT NULL AND v.owner IN (' . $membersquery . ')
                    GROUP BY v.type
                UNION ALL
                    SELECT v.type, COUNT(*) AS count
                    FROM {view} v
                    WHERE v.institution IS NOT NULL AND v.institution = ?
                    GROUP BY v.type
                ) tmp GROUP BY tmp.type', array_merge($membersqueryparams, array($institution)))) {
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
                    WHERE v.owner IS NOT NULL AND v.owner IN (' . $membersquery . ')
                    GROUP BY bi.blocktype
                UNION ALL
                    SELECT bi.blocktype AS type, COUNT(*) AS count
                    FROM {block_instance} bi
                    JOIN {view} v ON v.id = bi.view
                    WHERE v.institution IS NOT NULL AND v.institution = ?
                    GROUP BY bi.blocktype
                ) tmp GROUP BY tmp.type', array_merge($membersqueryparams, array($institution)))) {
            foreach ($data as $blocktypeinfo) {
                $inst_data['blocktype_' . $blocktypeinfo->type] = $blocktypeinfo->count;
                $inst_data['count_blocks'] += $blocktypeinfo->count;
            }
        }
        $inst_data['count_artefacts'] = 0;
        if ($data = get_records_sql_array('SELECT a.artefacttype AS type, COUNT(*) AS count
                FROM {artefact} a
                WHERE a.author IN (' . $membersquery . ')
                GROUP BY a.artefacttype', $membersqueryparams)) {
            foreach ($data as $artefacttypeinfo) {
                $inst_data['artefact_type_' . $artefacttypeinfo->type] = $artefacttypeinfo->count;
                $inst_data['count_artefacts'] += $artefacttypeinfo->count;
            }
        }
        $inst_data['count_interaction_forum_post'] = count_records_select('interaction_forum_post',
                'poster IN (' . $membersquery . ')',
                $membersqueryparams);
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
                WHERE u.id IN (' . $membersquery . ')',
                $membersqueryparams)) {
            $inst_data['usersloggedin'] = isset($data->sum) ? $data->sum : 0;
        }
        else {
            $inst_data['usersloggedin'] = 0;
        }

        $data_to_store[$institution] = $inst_data;
    }
    return $data_to_store;
}
