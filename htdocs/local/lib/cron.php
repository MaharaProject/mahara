<?php
/**
 *
 * @package    mahara local cron
 * @subpackage artefact
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

defined('INTERNAL') || die();
define('PCNZ_AUTHINSTANCE', 1);
define('PCNZ_REMOTEURL', get_config('registerapi_url'));

define('PCNZ_NOTREGISTERED', 1); // The 'Not yet registered' value
define('PCNZ_REGISTEREDCURRENT', 2); // The 'Registered, current' value
define('PCNZ_REGISTEREDINACTIVE', 3); // The 'Registered, Inactive' value
define('PCNZ_REGISTEREDSUSPENDED', 4); // The 'Suspended' value
define('PCNZ_REMOVED', 9); // The 'Removed' value
define('PCNZ_STRUCKOFF', 10); // The 'Struck off' value
define('PCNZ_REMOVING', 11); // The 'Removal request' value

define('PCNZ_INTERVALCHECK', '-1 day'); // The number of 'whatever' in the past to fetch data for
                                          // - should be changed after testing to the interval period
                                          // of the cron, eg '-1 day'
define('PCNZ_DATEFORMAT', 'Y-m-d\TH:i:s\Z'); // The date format that endpoint accepts

/**
 * Is called via cron to see if we can get a User $token
 * so we can access the restricted external API calls
 * and if we can then we fetch changed users from external system
 */
function local_pcnz_sync_users() {

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
        $changes = get_changes($token);
        log_debug('Have ' . count($changes) . ' changes');
        if (!empty($changes)) {
            process_changes($changes);
        }
    }
}

/**
 * Fetch the changes in practicing certificates
 */
function get_changes($token) {
    if (empty($token)) {
        return array();
    }
    $auditlog_endpoint = PCNZ_REMOTEURL . 'api/RegisterAuditLogs';
    $practicingstatus_endpoint = PCNZ_REMOTEURL . 'api/PractitionerPracticingStatuses';
    $people = array();
    $lastrun = date(PCNZ_DATEFORMAT, strtotime(PCNZ_INTERVALCHECK, time())); // Check to the nearest hour as the : for minutes can't be handled
    // Fetch any personal details that have changed since last run
    $auditlog_data = json_encode(
        array(
            "where" => array(
                "createdat" => array(
                    "gt" => $lastrun
                ),
                "or" => array(
                    array(
                        "and" => array(
                            array(
                                "changeableType" => "Person",
                                "or" => array(
                                    array(
                                        "fieldnames" => array(
                                           "like" => "%name%"
                                        )
                                    ),
                                    array(
                                        "fieldnames" => array(
                                            "like" => "%contactemailaddress%"
                                        )
                                    )
                                )
                            )
                        )
                    )
/*                  ),
                    array(
                        "and" => array(
                            array(
                                "changeableType" => "Practitioner",
                                "fieldnames" => array(
                                    "like" => "%practicingstatusid%"
                                )
                            )
                        )
                    ) */
                )
            )
        )
    );
    $auditlog_endpoint = $auditlog_endpoint . '?filter=' . $auditlog_data . '&access_token=' . $token;
    $auditlogrequest = array(
        CURLOPT_URL        => $auditlog_endpoint,
        CURLOPT_HTTPGET    => 1,
        CURLOPT_HTTPHEADER => array('Accept:application/json'),
    );
    $auditloginfo = mahara_http_request($auditlogrequest);
    if (isset($auditloginfo->data) && !empty($auditloginfo->data)) {
        $data = json_decode($auditloginfo->data);
        if ($data) {
            foreach ($data as $person) {
                if ($person->changeableType == 'Person') {
                    $people[$person->changeableId]['personalinfo'] = get_person($token, $person->changeableId);
                }
            }
        }
    }
    // Fetch any practising statuses that have changed since last run
    $practicingstatus_data = json_encode(
        array(
            "where" => array(
                "updatedat" => array(
                    "gt" => $lastrun
                )
            ),
            "include" => "practitioner"
        )
    );
    $practicingstatus_endpoint = $practicingstatus_endpoint . '?filter=' . $practicingstatus_data . '&access_token=' . $token;
    $practicingstatusrequest = array(
        CURLOPT_URL        => $practicingstatus_endpoint,
        CURLOPT_HTTPGET    => 1,
        CURLOPT_HTTPHEADER => array('Accept:application/json'),
    );
    $practicingstatusinfo = mahara_http_request($practicingstatusrequest);
    if (isset($practicingstatusinfo->data) && !empty($practicingstatusinfo->data)) {
        $data = json_decode($practicingstatusinfo->data);
        if ($data) {
            foreach ($data as $person) {
                if ($person->practicingStatusId == PCNZ_REGISTEREDSUSPENDED) {
                    // We don't fetch personal info on SUSPENDED users but
                    // just make sure they are suspended on our end too if they exist here
                    if ($suspenduserid = get_field('usr', 'id', 'username', $person->personid)) {
                        if (!get_field('usr', 'suspendedctime', 'id', $suspenduserid)) {
                            // Not currently suspended so suspend them
                            set_apc_status($person->personid, null);
                            suspend_user($suspenduserid, '', 0); // suspend as cron
                            unset($people[$person->personid]);
                        }
                    }
                }
                else if ($person->practicingStatusId == PCNZ_REMOVED ||
                         $person->practicingStatusId == PCNZ_STRUCKOFF ||
                         $person->practicingStatusId == PCNZ_REMOVING) {
                    // We don't fetch personal info on these users but
                    // just make sure they are expired on our end too if they exist here
                    if ($expireuserid = get_field('usr', 'id', 'username', $person->personid)) {
                        if (!get_field('usr', 'expiry', 'id', $expireuserid)) {
                            // Not currently expired so expire them, but don't alert them
                            $now = db_format_timestamp(time());
                            execute_sql("UPDATE {usr} SET expiry = ?, expirymailsent = ?, lastaccess = ?
                                         WHERE id = ?", array($now, 1, $now, $expireuserid));
                            set_apc_status($expireuserid, null);
                            unset($people[$person->personid]);
                        }
                    }
                }
                else if ($person->practicingStatusId == PCNZ_REGISTEREDCURRENT ||
                         $person->practicingStatusId == PCNZ_REGISTEREDINACTIVE) {
                    $people[$person->personid]['practisingstatus'][] = $person;
                    $people[$person->personid]['personalinfo'] = get_person($token, $person->personid);
                }
                else {
                    // should just be an inactive person so we shouldn't do anything with this
                }
            }
        }
    }
    return $people;
}

/**
 * Fetch personal details of a user based on their ID
 */
function get_person($token, $id) {
    if (empty($token) || empty($id)) {
        return false;
    }
    $person_endpoint = PCNZ_REMOTEURL . 'api/people';
    $person_data = json_encode(
        array(
            "where" => array(
                "id" => array(
                    "inq" => array( // using inq allows us to pass in an array of id's rather than doing one call per id (not done yet)
                        $id
                     )
                )
            ),
            "fields" => array(
                "id",
                "contactemailaddress",
                "firstname",
                "middlenames",
                "surname",
                "nickname"
            ),
            "include" => array(
                array("relation" => "practitioner",
                    "scope" => array(
                        "fields" => array(
                             "practicingstatusid" => true
                        )
                    )
                ),
                "apc"
            )
        )
    );
    $person_endpoint = $person_endpoint . '?filter=' . $person_data . '&access_token=' . $token;
    $personrequest = array(
        CURLOPT_URL        => $person_endpoint,
        CURLOPT_HTTPGET    => 1,
        CURLOPT_HTTPHEADER => array('Accept:application/json'),
    );
    $personinfo = mahara_http_request($personrequest);
    if (isset($personinfo->data) && !empty($personinfo->data)) {
        $person = json_decode($personinfo->data);
        return $person[0]; // As we are fetching one
    }
    return false;
}

/**
 * Set the current APC status for a person
 */
function set_apc_status($userid, $personalinfo) {
    if (!empty($personalinfo) && isset($personalinfo->apc)) {
        set_account_preference($userid, 'apcstatusactive', $personalinfo->apc->active);
        set_account_preference($userid, 'apcstatusdate', $personalinfo->apc->startdate);
        set_account_preference($userid, 'apcstatusdateend', $personalinfo->apc->expirydate);
        $logdata = array('apcstatusactive' => $personalinfo->apc->active,
                         'apcstatusdate' => $personalinfo->apc->startdate,
                         'apcstatusdateend' => $personalinfo->apc->expirydate);
    }
    else {
        set_account_preference($userid, 'apcstatusactive', false);
        set_account_preference($userid, 'apcstatusdate', 0); // Can't use null for this
        set_account_preference($userid, 'apcstatusdateend', 0); // Can't use null for this
        $logdata = array('apcstatusactive' => false,
                         'apcstatusdate' => null,
                         'apcstatusdateend' => null);
    }
    $logentry = (object) array(
        'usr'      => $userid,
        'realusr'  => $userid,
        'event'    => 'apcstatuschange',
        'data'     => json_encode($logdata),
        'ctime'    => db_format_timestamp(time()),
    );
    insert_record('event_log', $logentry);
}

/**
 * Create or update a user
 */
function process_changes($changes) {
    require_once(get_config('docroot') . 'lib/institution.php');
    require_once(get_config('docroot') . 'lib/collection.php');
    foreach ($changes as $username => $person) {
        $user = new User();
        try {
            // Update user
            $user->find_by_username((string)$username);
            $oldapcstatus = get_account_preference($user->get('id'), 'apcstatusactive');
            $user->firstname = $person['personalinfo']->firstname;
            $user->lastname = $person['personalinfo']->surname;
            $user->username = $person['personalinfo']->id;
            $user->studentid = $person['personalinfo']->id;
            $user->preferredname = $person['personalinfo']->nickname;
            $user->email = $person['personalinfo']->contactemailaddress;
            $user->commit();
            $institution = get_field('auth_instance', 'institution', 'id', PCNZ_AUTHINSTANCE);
            if (isset($person['personalinfo']->apc)) {
                if ($oldapcstatus != $person['personalinfo']->apc->active && $person['personalinfo']->apc->active === true) {
                    $template = get_active_collection_template($institution);
                    // Check that the person doesn't already have the current template so they don't get it twice
                    if ($template && !record_exists_sql("SELECT collection FROM {collection_template} ct
                                            JOIN {collection} c ON c.id = ct.collection
                                            WHERE c.owner = ? AND ct.originaltemplate = ?", array($user->get('id'), $template->get('id')))) {
                        Collection::create_from_template(array('owner' => $user->get('id')), $template->get('id'), $user->get('id'), false, true, true);
                    }
                }
            }
            set_apc_status($user->get('id'), $person['personalinfo']);
            log_debug('Updating user with internal ID: ' . $user->get('id') . ' and external ID: ' . $user->get('username') . ' done');
        }
        catch (Exception $e) {
            log_debug($e->getMessage());
            $oldapcstatus = false;
            // Create new user if they have the status 'Registered current'
            if ($person['personalinfo']->practitioner->practicingstatusid == PCNZ_REGISTEREDCURRENT) {
                safe_require('auth', 'internal');
                $temp_password = AuthInternal::get_temp_password();
                $new_user = new stdClass();
                $new_user->authinstance = PCNZ_AUTHINSTANCE;
                $new_user->username     = $person['personalinfo']->id;
                $new_user->firstname    = $person['personalinfo']->firstname;
                $new_user->lastname     = $person['personalinfo']->surname;
                $new_user->password     = $temp_password;
                $new_user->email        = $person['personalinfo']->contactemailaddress;
                $new_user->passwordchange = 1;

                // The student id and preferredname get saved as an artefact and to usr table
                $profilefields = new stdClass();
                $profilefields->studentid = $person['personalinfo']->id;
                $new_user->studentid = $person['personalinfo']->id;
                $profilefields->preferredname = $person['personalinfo']->nickname;
                $new_user->preferredname = $person['personalinfo']->nickname;
                $institution = get_field('auth_instance', 'institution', 'id', PCNZ_AUTHINSTANCE);
                $new_user->id = create_user($new_user, $profilefields, $institution);
                $user->find_by_id($new_user->id);
                // Send email to user to so they can login
                if (!empty($user->get('email'))) {
                    try {
                        email_user($user, null, get_string('accountcreated', 'mahara', get_config('sitename')),
                            get_string('accountcreatedchangepasswordtext', 'mahara', $user->firstname, get_config('sitename'), $user->username, $temp_password, get_config('wwwroot'), get_config('sitename')),
                            get_string('accountcreatedchangepasswordhtml', 'mahara', $user->firstname, get_config('wwwroot'), get_config('sitename'), $user->username, $temp_password, get_config('wwwroot'), get_config('wwwroot'), get_config('sitename'))
                        );
                    }
                    catch (EmailException $e) {
                        log_debug('Unable to send email to ' . $user->username);
                    }
                }
                if (isset($person['personalinfo']->apc) && $person['personalinfo']->apc->active === true) {
                    // Need to copy the active collection to the user
                    $template = get_active_collection_template($institution);
                    if ($template) {
                        Collection::create_from_template(array('owner' => $user->get('id')), $template->get('id'), $user->get('id'), false, true, true);
                    }
                }
                set_apc_status($user->get('id'), $person['personalinfo']);
                log_debug('Creating user with internal ID: ' . $user->get('id') . ' and external ID: ' . $user->get('username') . ' done');
            }
            else {
                log_debug('Not creating user with external ID: ' . $user->get('username') . ' because status is ' . $person['personalinfo']->practitioner->practicingstatusid);
            }
        }
        // Do post create / update stuff
        $userid = $user->get('id');
        if ($suspendeduserid = get_field('usr', 'id', 'username', $userid)) {
            unsuspend_user($suspendeduserid); // un-suspend user
        }
    }
}