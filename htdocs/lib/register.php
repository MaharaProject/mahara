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
	//TODO translate
  $last = get_config('last_registration_success');
  
	$info = 'Send the following data to mahara.org: <ul>';
	$data = registration_data();
	foreach($data as $key=>$val) {
		$info .= '<li>'. htmlentities("$key = $val") .'</li>'; 
	}
	$info .= '</ul>';

  if ($last) {
    $info .= '<p>Last sent: '. format_date($last) .'</p>';
  }
	
	$form = array(
     'name' => 'register',
     'autofocus' => false,
     'elements' => array(
                          'whatsent' => array('type' => 'fieldset', 'legend' => 'Register your mahara site', 'collapsible' => true, 'collapsed' => ($last ? true : false), //collapse if they've sent before
				'elements' => 
  						array(
 							'info' => array('type' => 'markup', 'value'=> $info),
  						)
						),
				'register' => array('type' => 'submit', 'value' => 'Register'),
         )
       );
     
     return pieform($form);
}
/**
 * Runs when registration form is submitted
 */
function register_submit() {
	$data = registration_data();
	$request = array(
    CURLOPT_URL => 'http://mahara.org.gargi.wgtn.cat-it.co.nz/mahara-registration.php',
		CURLOPT_POST => 1,
		CURLOPT_POSTFIELDS => $data,
	);
	$result = http_request($request);

  GLOBAL $SESSION;
  
  //TODO Translate needed
  if ($result->data != '1') {
  	$SESSION->add_error_msg('Registation failed' . print_r($result, true));
  }
  else {
    set_config('last_registration_success', strtotime('now'));
    $SESSION->add_ok_msg('Registation successful');
  }
	redirect('/admin');
}


function registration_data() {

	foreach(array('dbtype', 'sitename', 'lang', 'wwwroot', 'theme', 'release') as $key) {
		$data_to_send[$key] = get_config($key);
	}
	foreach(array('usr', 'view', 'artefact', 'group', 'block_instance', 'institution') as $key) {
		$data_to_send[$key . '_count'] = count_records($key);
	}

	return $data_to_send;
}
