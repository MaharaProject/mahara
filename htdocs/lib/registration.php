<?php
/**
 * Provides the registration form for registering a site
 *
 * Provides the functionality for registering a Mahara site with the
 * Mahara Project.
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Limited <mahara@catalyst.net.nz>
 * @license    https://www.gnu.org/licenses/gpl-3.0.html GNU GPL version 3 or later
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
 * Builds the register_site form
 *
 * Provides a table of site data and option to register site with Mahara Project
 * @return Pieform The 'registration' form
 * @ingroup Registration
 */
function register_site()  {

    $data = registration_data();
    $registered = get_config('registration_sendweeklyupdates');
    $smarty = smarty_core();
    $smarty->assign('data', registration_data_category($data));
    $html = $smarty->fetch('admin/site/registrationdata.tpl');
    $elements = array();
    $elements['whatsent'] = array(
        'type' => 'html',
        'value'=> $html,
    );
    if (get_config('productionmode')) {
        $elements['registeryesno'] = array(
            'type' => 'switchbox',
            'title' => get_string('registerwithmahara1', 'admin'),
            'description' => get_string('registerwithmaharadescription1', 'admin'),
            'defaultvalue' => !$registered,
        );
        $elements['register'] = array(
            'type' => 'submit',
            'class' => 'btn-primary',
            'value' => get_string('save', 'mahara'),
        );
    }
    else {
        $helplink = get_manual_help_link_array(array('adminhome', 'config', 'productionmode'));
        $helplink = $helplink['prefix'] . '/' . $helplink['language'] . '/' . $helplink['version'] . '/' . $helplink['suffix'];
        $elements['whatsentinprodmode'] = array(
            'type' => 'html',
            'value'=> get_string('whatsentinprodmode', 'statistics', $helplink),
        );
    }
    $form = array(
        'name' => 'register',
        'autofocus' => false,
        'elements' => $elements,
    );
    // Now that they have seen the page we assume they have read the page
    set_config('new_registration_policy', false);
    return pieform($form);
}

/**
 * Runs when registration form is submitted
 * @param  Pieform  $form  Pieform  The'register' pieform
 * @param  array  $values of submitted data via pieform
 */
function register_submit(Pieform $form, $values) {
    global $SESSION;

    if ($values['registeryesno']) {
        // 'Yes' means we are opting out
        set_config('new_registration_policy', false);
        set_config('registration_firstsent', null);
        set_config('registration_lastsent', null);
        set_config('registration_sendweeklyupdates', false);
        $SESSION->add_ok_msg(get_string('registrationoptoutsuccessful', 'admin'));
    }
    else if (!get_config('registration_sendweeklyupdates')) {
        // We were opted out but are opting back in
        list($status, $message) = register_again();
        $messagetype = 'add_' . $status . '_msg';
        $SESSION->$messagetype($message);
    }

    redirect('/admin/registersite.php');
}

/**
 * Send the registration data and set the registered config state
 *
 * @param bool $keepnew Keep the new_registration_policy flag active
 *                      so that one can read it after logging in.
 *                      Useful in upgrade step.
 * @return array  Containing message type and message
 */
function register_again($keepnew = false) {
    $result = registration_send_data();
    $data = json_decode($result->data);

    if ($result->info['http_code'] != 200 || $data->status != 1) {
        return array('error', get_string('registrationfailedtrylater', 'admin', $result->info['http_code'] . ' - ' . $data->error));
    }
    else {
        set_config('registration_lastsent', time());
        if (!get_config('registration_firstsent')) {
            set_config('registration_firstsent', time());
        }
        set_config('registration_sendweeklyupdates', true);
        if (get_config('new_registration_policy') && !$keepnew) {
            set_config('new_registration_policy', false);
        }
        return array('ok', get_string('registrationsuccessfulthanksforregistering', 'admin'));
    }
}

/**
 * Worker - performs sending of registration data to mahara.org
 */
function registration_send_data() {
    $registrationurl = 'https://mahara.org/api/registration.php';
    $data = registration_data();
    // unset two options that are only there for display purposes
    unset($data['numberofartefacts']['active']);
    unset($data['numberofblocks']['active']);
    $curldata = array();
    foreach ($data as $datakey => $datavalue) {
        if (is_array($datavalue)) {
            foreach ($datavalue as $subdatakey => $subdatavalue) {
                if ($subdatakey === 'info') {
                    $curldata[$datakey] = $subdatavalue;
                }
                else {
                    $curldata[$datakey . '_' . $subdatakey] = $subdatavalue;
                }
            }
        }
        else {
            $curldata[$datakey] = $datavalue;
        }
    }

    $request = array(
        CURLOPT_URL        => $registrationurl,
        CURLOPT_POST       => 1,
        CURLOPT_POSTFIELDS => $curldata,
    );
    return mahara_http_request($request);
}

/**
 * Save the site registration id to database
 */
function registration_store_data() {
    $data = registration_data();
    db_begin();
    $registration_id = insert_record('site_registration', (object)array(
        'time' => db_format_timestamp(time()),
    ), 'id', true);
    foreach ($data as $key => $value) {
        // The new registration_data() has the values as an array. For this process
        // we only want the 'info' on the $value.
        if (is_array($value) && array_key_exists('info', $value)) {
            $value = $value['info'];
        }
        // The value has to be a string.
        if ($value == null) {
            $value = '';
        }
        insert_record('site_registration_data', (object)array(
            'registration_id' => $registration_id,
            'field'           => $key,
            'value'           => ($value == null ? '' : $value)
        ));
    }
    db_commit();
}

/**
 * Categorize the registration data
 */
function registration_data_category($data) {
    $categories = array(
        'site' => array(
            'sitename' => 'text',
            'wwwroot' => 'text',
            'installation_key' => 'text',
            'release' => 'text',
            'version' => 'text',
            'osversion' => 'text',
            'dbversion' => 'text',
            'webserver' => 'text',
            'phpversion' => 'text',
            'phpsapi' => 'text',
            'phpmodules' => 'text',
            'lang' => 'text',
            'theme' => 'text',
            'country' => 'text',
            'timezone' => 'text',
            'homepageinfo' => 'bool',
            'homepageredirect' => 'bool',
            'skins' => 'bool',
            'licensemetadata' => 'bool',
            'mathjax' => 'bool',
            'exporttoqueue' => 'bool',
            'searchplugin' => 'text',
            'eventlogging' => 'text',
            'enablenetworking' => 'bool',
            'moodlehost' => 'text',
            'institutionstrictprivacy' => 'bool',
        ),
        'institutions_accounts' => array(
            'usersallowedmultipleinstitutions' => 'bool',
            'isolatedinstitutions' => 'bool',
            'institutions' => 'text',
            'count_usr' => 'text',
            'siteadmins' => 'text',
            'sitestaff' => 'text',
            'institutionadmins' => 'text',
            'institutionsupportadmins' => 'text',
            'institutionstaff' => 'text',
            'groups' => 'text',
            'friends' => 'text',
        ),
        'portfolios' => array(
            'allowanonymouspages' => 'bool',
            'allowpublicviews' => 'bool',
            'allowpublicprofiles' => 'bool',
            'numberofpages' => 'text',
            'numberofcollections' => 'text',
            'numberofsecollections' => 'text',
            'numberofpccollections' => 'text',
        ),
        'artefacts' => array(
            'numberofartefacts' => 'text',
            'artefact_type_*' => 'text',
        ),
        'blocktypes' => array(
            'numberofblocks' => 'text',
            'blocktype_*' => 'text',
        ),
    );

    $boolstrings = array(
        0 => get_string('no'),
        1 => get_string('yes')
    );
    $boolicons = array(
        0 => '<span class="icon icon-lg icon-circle-xmark plugin-inactive" title="' . get_string('no') . '">',
        1 => '<span class="icon icon-lg icon-circle-check plugin-active" title="' . get_string('yes') . '">',
    );
    // Now map the data we have to the category structure
    $newdata = array();
    foreach ($categories as $k => $sub) {
        foreach ($sub as $sk => $sv) {
            $keystring = hsc($sk);
            if (string_exists($keystring, 'statistics')) {
                $keystring = get_string($keystring, 'statistics');
            }
            $newdata[$k]['data'][$sk]['key'] = $keystring;
            $newdata[$k]['activecolumn'] = false;
            if (isset($data[$sk])) {
                $newdata[$k]['data'][$sk]['value'] = ($sv === 'bool') ? $boolstrings[(bool)$data[$sk]['info']] : hsc($data[$sk]['info']);
                if (isset($data[$sk]['active'])) {
                    $newdata[$k]['data'][$sk]['active'] = is_null($data[$sk]['active']) ? '' : $boolicons[(bool)$data[$sk]['active']];
                }
            }
            else if (preg_match('/\_\*$/', $sk)) {
                $newdata[$k]['activecolumn'] = true;
                // We have a partial key, one ending in '_*' so want to find all the $data items that start
                // with the string before the '*'
                $partial = substr($sk, 0, -1);
                $partial_array = array_filter($data, function($key) use ($partial) {
                    return strpos($key, $partial) === 0;
                }, ARRAY_FILTER_USE_KEY);
                // Unset the key with '_*'
                unset($newdata[$k]['data'][$sk]);
                // Then add all the partials to $newdata
                foreach ($partial_array as $pk => $pv) {
                    if (isset($data[$pk])) {
                        $keystring = hsc($pk);
                        if (string_exists($keystring, 'statistics')) {
                            $keystring = get_string($keystring, 'statistics');
                        }
                        $newdata[$k]['data'][$pk]['key'] = $keystring;
                        $newdata[$k]['data'][$pk]['value'] = ($sv === 'bool') ? $boolstrings[(bool)$data[$pk]['info']] : hsc($data[$pk]['info']);
                        if (isset($data[$pk]['active'])) {
                            $newdata[$k]['data'][$pk]['active'] = is_null($data[$pk]['active']) ? '' : $boolicons[(bool)$data[$pk]['active']];
                        }
                    }
                }
            }
            else {
                $newdata[$k]['data'][$sk]['value'] = ($sv === 'bool') ? $boolstrings[0] : '';
            }
        }
    }

    return $newdata;
}

/**
 * Builds the data that will be sent by the "register your site" feature
 */
function registration_data() {
    foreach (array(
        'wwwroot',
        'installation_key',
        'sitename',
        'lang',
        'theme',
        'enablenetworking',
        'allowpublicviews',
        'allowpublicprofiles',
        'version',
        'release',
        'country',
        'timezone',
        'homepageinfo',
        'homepageredirect',
        'skins',
        'licensemetadata',
        'mathjax',
        'exporttoqueue',
        'searchplugin',
        'institutionstrictprivacy',
        'usersallowedmultipleinstitutions',
        'isolatedinstitutions',
        'allowanonymouspages') as $key) {
        $data_to_send[$key]['info'] = get_config($key);
    }

    // Event logging
    $data_to_send['eventlogging']['info'] = get_config('eventloglevel');
    if (get_config('eventlogenhancedsearch')) {
        $data_to_send['eventlogging']['info'] = 'enhanced';
    }

    // Count records
    $data_to_send['institutions']['info'] = count_records('institution');
    $data_to_send['count_usr']['info'] = count_records_sql("SELECT COUNT(*) FROM {usr} WHERE deleted = 0 AND id != 0");
    $data_to_send['siteadmins']['info'] = count_records('usr', 'admin', 1);
    $data_to_send['sitestaff']['info'] = count_records('usr', 'staff', 1);
    $data_to_send['institutionadmins']['info'] = count_records('usr_institution', 'admin', 1);
    $data_to_send['institutionsupportadmins']['info'] = count_records('usr_institution', 'supportadmin', 1);
    $data_to_send['institutionstaff']['info'] = count_records('usr_institution', 'staff', 1);
    $data_to_send['groups']['info'] = count_records_sql("SELECT COUNT(*) FROM {group} WHERE deleted = 0");
    $data_to_send['friends']['info'] = count_records('usr_friend');
    $data_to_send['numberofpages']['info'] = count_records_sql("SELECT COUNT(*) FROM {view} WHERE type = 'portfolio'");
    $data_to_send['numberofcollections']['info'] = count_records('collection');
    $data_to_send['numberofsecollections']['info'] = count_records_sql("SELECT COUNT(*) FROM {collection} WHERE framework > 0");
    $data_to_send['numberofpccollections']['info'] = count_records_sql("SELECT COUNT(*) FROM {collection} WHERE progresscompletion = 1");
    $data_to_send['numberofartefacts'] = array('info' => count_records('artefact'), 'active' => null);
    $data_to_send['numberofblocks'] = array('info' => count_records('block_instance'), 'active' => null);
    $data_to_send['moodlehost']['info'] = count_records_select('host', 'deleted = 0');

    // System information
    $data_to_send['phpversion']['info'] = phpversion();
    if (is_mysql()) {
        $dbversion = get_records_sql_array("SHOW VARIABLES LIKE 'version%'");
        $dbversionstr = 'MySQL';
        foreach ($dbversion as $dbk => $dbv) {
            $dbversionstr .= ' ' . $dbv->Value;
        }
        $data_to_send['dbversion']['info'] = $dbversionstr;
    }
    else {
        $data_to_send['dbversion']['info'] = get_field_sql('SELECT VERSION()');
    }
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
    $data_to_send['osversion']['info'] = $osversion;
    $data_to_send['phpsapi']['info'] = php_sapi_name();
    if (!empty($_SERVER) && !empty($_SERVER['SERVER_SOFTWARE'])) {
        $data_to_send['webserver']['info'] = $_SERVER['SERVER_SOFTWARE'];
    }
    $modules = get_loaded_extensions();
    natcasesort($modules);
    $data_to_send['phpmodules']['info'] = implode('; ', $modules);

    // Slightly more drilled down information
    if ($data = get_records_sql_array('SELECT ait.name, COUNT(a.id) AS count, (SELECT active FROM {artefact_installed} WHERE name = ait.plugin) AS active
        FROM {artefact_installed_type} ait
        LEFT JOIN {artefact} a ON a.artefacttype = ait.name
        GROUP BY ait.name
        ORDER BY ait.name', array())) {
        foreach ($data as $artefacttypeinfo) {
            $data_to_send['artefact_type_' . $artefacttypeinfo->name]['info'] = $artefacttypeinfo->count;
            $data_to_send['artefact_type_' . $artefacttypeinfo->name]['active'] = $artefacttypeinfo->active;
        }
    }

    if ($data = get_records_sql_array('SELECT bti.name, COUNT(bi.id) AS count, (SELECT bti2.active FROM {blocktype_installed} bti2 WHERE bti2.name = bti.name) AS active
        FROM {blocktype_installed} bti
        LEFT JOIN {block_instance} bi ON bi.blocktype = bti.name
        GROUP BY bti.name
        ORDER BY bti.name', array())) {
        foreach ($data as $blocktypeinfo) {
            $data_to_send['blocktype_' . $blocktypeinfo->name]['info'] = $blocktypeinfo->count;
            $data_to_send['blocktype_' . $blocktypeinfo->name]['active'] = $blocktypeinfo->active;
        }
    }

    $data_to_send['newstats'] = 1;

    return $data_to_send;
}

/**
 * Save the registration id per institution to the database
 */
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

/**
 *  Query the database to return stats on institution members, views, blocks
 *  and artefacts and forum interactions during a week
 */
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
