<?php
/**
 *
 * @package    mahara local backstop
 * @subpackage artefact
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();
require_once(get_config('docroot') . 'local/lib/cron.php');

/**
 * Is called via cron to see if we can get a User $token
 * so we can access the restricted external API calls
 * and if we can then we fetch changed users from external system
 */
function local_pcnz_doublecheck() {

    $login_endpoint = PCNZ_REMOTEURL . 'api/Users/login';

    $login_data = json_encode(
        array(
            "username" => get_config('registerapi_username'),
            "password" => get_config('registerapi_password')
        )
    );
    $tokenrequest = array(
        CURLOPT_URL        => $login_endpoint,
        CURLOPT_POST       => 1,
        CURLOPT_POSTFIELDS => $login_data,
        CURLOPT_HTTPHEADER => array('Content-Type:application/json',
                                    'Accept:application/json'),
    );
    $token = false;
    $tokeninfo = mahara_http_request($tokenrequest);
    if (isset($tokeninfo->data) && !empty($tokeninfo->data)) {
        $tokeninfo = json_decode($tokeninfo->data);
        $token = $tokeninfo->id;
    }

    if ($token) {
        $changes = doublecheck_people($token);
        log_debug('Have ' . count($changes) . ' changes');
        if (!empty($changes)) {
            process_changes($changes);
        }
    }
}

/**
 * Fetch the people and see if any has been missed by the cron
 */
function doublecheck_people($token) {
    if (empty($token)) {
        return array();
    }
    $person_endpoint = PCNZ_REMOTEURL . 'api/people';
    $people = array();
    $registeredpeople = json_encode(
        array(
            "where" => array(
                "practitioner" => array(
                    "practicingstatusid" => PCNZ_REGISTEREDCURRENT
                )
            ),
            "fields" => array(
                "id"
            ),
            "include" => array(
                "practitioner"
            )
        )
    );
    $people_endpoint = $person_endpoint . '?filter=' . $registeredpeople . '&access_token=' . $token;
    $peoplerequest = array(
        CURLOPT_URL        => $people_endpoint,
        CURLOPT_HTTPGET    => 1,
        CURLOPT_HTTPHEADER => array('Accept:application/json'),
    );
    log_debug('... fetch all registered people IDs');
    $peopleinfo = mahara_http_request($peoplerequest);
    if (isset($peopleinfo->data) && !empty($peopleinfo->data)) {
        $data = json_decode($peopleinfo->data);
        if ($data) {
            foreach ($data as $person) {
                if (!empty($person->practitioner) && !empty($person->practitioner->practicingstatusid) && $person->practitioner->practicingstatusid == PCNZ_REGISTEREDCURRENT) {
                    $people[] = $person->id;
                }
            }
        }
    }

    // Find any of the Register IDs that do not yet exist in Mahara
    $sql = "SELECT username FROM {usr} WHERE deleted = 0 AND id != 0";
    $existing = get_column_sql($sql);
    $valid1 = array_diff($people, $existing);

    // Find any of the Register IDs that are not listed as registered active in Mahara
    $sql = "SELECT u.username
            FROM {usr} u
            JOIN {usr_account_preference} p ON p.usr = u.id
            WHERE u.username IN (" . join(',', array_map('db_quote', $people)) . ")
            AND p.field = ? AND p.value != ?";
    $valid2 = get_column_sql($sql, array('registerstatus', PCNZ_REGISTEREDCURRENT));

    // Find any of the Register IDs that are already listed as registered active in Mahara
    // and fetch their apc end date to check if it's older than now
    $sql = "SELECT p.value AS enddate, u.username
            FROM {usr} u
            JOIN {usr_account_preference} p ON p.usr = u.id
            WHERE p.field = ? AND p.usr IN (
                SELECT u2.id
                FROM {usr} u2
                JOIN {usr_account_preference} p2 ON p2.usr = u2.id
                WHERE u2.username IN (" . join(',', array_map('db_quote', $people)) . ")
                AND p2.field = ? AND p2.value = ?
            )";
    $valid3 = array();
    $valid3_raw = get_records_sql_array($sql, array('apcstatusdateend', 'registerstatus', PCNZ_REGISTEREDCURRENT));
    // But their APC start/end dates are not correct
    if ($valid3_raw) {
        foreach ($valid3_raw as $apc) {
            if (strtotime($apc->enddate) < time()) {
                $valid3[] = $apc->username;
            }
        }
    }

    $valid = array_merge($valid1, $valid2, $valid3);
    sort($valid, SORT_NUMERIC);
    if (empty($valid)) {
        return array();
    }
    $validpeople = array();
    foreach ($valid as $k => $v) {
        log_debug('Fetching person with Reg ID ' . $v);
        $validperson = get_person($token, $v);
        if ($validperson) {
            $validpeople[$v]['personalinfo'] = $validperson;
        }
    }
    return $validpeople;
}
