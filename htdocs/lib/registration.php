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

    if ($result->data != '1') {
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

    return $data_to_send;
}

function site_data_current() {
    return array(
        'name' => get_config('sitename'),
        'users' => count_records_select('usr', 'id > 0 AND deleted = 0'),
        'groups' => count_records('group', 'deleted', 0),
        'views' => count_records_select('view', 'owner <> 0'),
    );
}

function site_statistics() {
    $data = site_data_current();

    $lastyear = db_format_timestamp(time() - 60*60*12*365);
    $values = array($lastyear, 'view-count', 'user-count', 'group-count');
    $weekly = get_records_sql_array('
        SELECT ctime, type, value, ' . db_format_tsfield('ctime', 'ts') . '
        FROM {site_data}
        WHERE ctime >= ? AND type IN (?,?,?)
        ORDER BY ctime, type', $values);
    if ($weekly) {
        $data['weekly'] = array('user-count' => array(), 'group-count' => array(), 'view-count' => array());
        $keys = array('user-count' => 0, 'group-count' => 0, 'view-count' => 0);
        foreach ($weekly as &$r) {
            $data['weekly'][$r->type][$keys[$r->type]++] = array($keys[$r->type], $r->value);
        }
    }
    $data['release'] = get_config('release');
    $data['version'] = get_config('version');
    $data['dbsize']  = db_total_size();
    $data['diskusage'] = get_field('site_data', 'value', 'type', 'disk-usage');
    return($data);

}

?>