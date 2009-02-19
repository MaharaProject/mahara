<?php
/**
 * @file Register a mahara site
 */
/**
 * @defgroup Registration Registration
 * Send site information to mahara.org
 * 
 */
 
defined('INTERNAL') || die();
require_once('pieforms/pieform.php');

/**
 * Class to use for registration exceptions
 * @ingroup Registration
 */
class RegistrationException extends SystemException {}

/**
 * @return string that is the registation form
 * @ingroup Registration 
 */
function register_site()  {
    $info = <<<EOF
<table>
    <tr>
        <th>Field</th>
        <th>Value</th>
    </tr>
EOF;
    $data = registration_data();
    foreach($data as $key => $val) {
        $info .= '<tr><td>'. hsc($key) . '</td><td>' . hsc($val) . "</td></tr>\n";
    }
    $info .= '</table>';

    $form = array(
        'name' => 'register',
        'autofocus' => false,
        'elements' => array(
            'whatsent' => array(
                'type' => 'fieldset',
                'legend' => 'Data that will be sent',
                'collapsible' => true,
                'collapsed' => true,
                'elements' => array(
                    'info' => array(
                        'type' => 'markup',
                        'value'=> $info
                    ),
                )
            ),
            'sendweeklyupdates' => array(
                'type' => 'checkbox',
                'title' => 'Send weekly updates?',
                'defaultvalue' => true
            ),
            'register' => array(
                'type' => 'submit',
                'value' => 'Register'
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
    $registrationurl = 'http://mahara.org/mahara-registration.php';

    set_config('registration_sendweeklyupdates', $values['sendweeklyupdates']);

    $data = registration_data();
    $request = array(
        CURLOPT_URL        => $registrationurl,
        CURLOPT_POST       => 1,
        CURLOPT_POSTFIELDS => $data,
    );
    $result = http_request($request);

  
    //TODO Translate needed
    if ($result->data != '1') {
        log_debug($result);
        $SESSION->add_error_msg('Registation failed with error code '. $result->info['http_code'] . '. Please try again later.');
    }
    else {
        set_config('registration_lastsent', strtotime('now'));
        $SESSION->add_ok_msg('Registation successful - thanks for registering!');
    }
    redirect('/admin/');
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
        'usr',
        'usr_friend',
        'usr_institution',
        'group',
        'group_member',
        'block_instance',
        'institution',
        'blocktype_wall_post',
        'host',
        'institution') as $key) {
        $data_to_send['count_' . $key] = count_records($key);
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
