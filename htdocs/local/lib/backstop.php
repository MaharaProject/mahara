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
                $people[] = $person->id;
            }
        }
    }
    // Find any of the Register IDs that are not listed as registered active in Mahara
    $sql = "SELECT u.username, p.value
            FROM {usr} u
            JOIN {usr_account_preference} p ON p.usr = u.id
            WHERE u.username IN (" . join(',', array_map('db_quote', $people)) . ")
            AND p.field = ? AND p.value != ?";
    $valid = get_records_sql_assoc($sql, array('registerstatus', PCNZ_REGISTEREDCURRENT));
    if (empty($valid)) {
        return array();
    }
    $validpeople = array();
    foreach ($valid as $k => $v) {
        $validperson = get_person($token, $v->username);
        $validpeople[$k]['personalinfo'] = $validperson;
    }
    return $validpeople;
}
