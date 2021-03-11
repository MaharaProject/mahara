<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('CLI', 1);
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('docroot') . 'auth/lib.php');
require_once(get_config('libroot') . 'cli.php');
require_once(get_config('docroot') . 'local/lib/cron.php');

$cli = get_cli();

$options = array();
$options['type'] = (object) array(
        'shortoptions' => array('t'),
        'description' => 'The registration status we want',
        'required' => false,
        'examplevalue' => '2',
        'multiple' => true,
);
$options['dryrun'] = (object) array(
        'shortoptions' => array('d'),
        'description' => get_string('cli_param_dryrun', 'admin'),
        'required' => false,
        'defaultvalue' => true,
);
$options['limit'] = (object) array(
        'shortoptions' => array('l'),
        'description' => 'The number of people to fetch in the batch',
        'required' => false,
        'defaultvalue' => 300,
);
$options['offset'] = (object) array(
        'shortoptions' => array('o'),
        'description' => 'The batch offset',
        'required' => false,
        'defaultvalue' => 1000,
);
$options['registernumbers'] = (object) array(
        'shortoptions' => array('r'),
        'description' => 'A suuplied set of numbers',
        'required' => false,
        'examplevalue' => '1234',
        'multiple' => true,
);

$settings = (object) array(
        'info' => 'The batch CLI script
                   This should either be run either
                   - listing some register ids that you want to fetch, eg -r=1234,5678,9102 - it will not care about practising status here before processing them
                   or
                   - with a status type, eg -t=inactive (valid options so far are active, inactive) and offset/ limit, eg -o=3600 -l=1000
                   Note registration numbers start from about 1300 and most of the early ones are not valid so fetching
                    sudo -u www-data php htdocs/admin/cli/pcnz_sync_users.php -t=3 -o=3600 -l=1000 -d=true
                   will only result in 47 legitimate people',
        'options' => $options,
);
$cli->setup($settings);

$dryrun = $cli->get_cli_param_boolean('dryrun');
$offset = $cli->get_cli_param('offset');
$limit = $cli->get_cli_param('limit');
$types = $cli->get_cli_param('type');
if (!empty($types) && !is_array($types)) {
    $types = explode(',', $types);
}
$regids = $cli->get_cli_param('registernumbers');
if (!empty($regids) && !is_array($regids)) {
    $regids = explode(',', $regids);
}
if (empty($regids) && !in_array('2', $types) && !in_array('3', $types) && !in_array('inactive', $types) && !in_array('active', $types)) {
    $cli->cli_exit('Need to specifiy "inactive" or "active" or both');
}

// Fetch token we can use for authentication
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
    $cli->cli_print('================== Fetching records for types ' . implode(',', $types) . ' ==================');
    if ($dryrun) {
        $cli->cli_print('Only a dry run');
    }
    if (!empty($regids)) {
        $ids = $regids;
    }
    else {
        $ids = range($offset, $offset + $limit);
    }
    $sublimit = 100;
    $groups = array_chunk($ids, $sublimit);
    $rawpeople = array();
    foreach ($groups as $ids) {
        $newpeople = fetch_person_cli($token, $ids);
        $rawpeople = array_merge($rawpeople, $newpeople);
    }
    $count = 0;
    $people = array();
    $total = count($rawpeople);
    foreach ($rawpeople as $person) {
        if (!empty($regids)) {
            $people[$person->id]['personalinfo'] = $person;
        }
        else if (isset($person->practitioner) && isset($person->practitioner->practicingstatusid) && in_array($person->practitioner->practicingstatusid, $types)) {
            $people[$person->id]['personalinfo'] = $person;
        }
        $count++;
        if (($count % $sublimit) == 0 || $count == $total) {
            $cli->cli_print("$count/$total");
        }
    }

    if (!empty($people)) {
        if ($dryrun) {
            $cli->cli_print('A total of ' . count($people) . ' people matching your types within the records  ' . $offset . ' to ' . ($offset + $limit));
        }
        else {
            process_changes($people);
        }
    }
    else {
        if (!empty($regids)) {
            $cli->cli_print('Nobody matching your supplied register ids ' . implode(', ', $ids));
        }
        else {
            $cli->cli_print('Nobody matching your types was found for records ' . $offset . ' to ' . ($offset + $limit));
        }
    }

    $cli->cli_print('---------------- End --------------------------');
}
else {
    $cli->cli_exit('Unable to fetch token');
}

function fetch_person_cli($token, $ids) {
        if (empty($token) || empty($ids)) {
            return false;
        }
        $person_endpoint = PCNZ_REMOTEURL . 'api/people';
        $person_data = json_encode(
            array(
                "where" => array(
                    "id" => array(
                        "inq" => $ids
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
            $persons = json_decode($personinfo->data);
            return $persons;
        }
        return false;
}
      
$cli->cli_exit(get_string('done'));
