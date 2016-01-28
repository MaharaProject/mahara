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
define('PUBLIC', 1);
define('SECTION_PLUGINTYPE', 'core');
define('SECTION_PLUGINNAME', 'site');
define('SECTION_PAGE', 'contact');
require('init.php');
require_once('lib/antispam.php');
define('TITLE', get_string('contactus'));

if ($USER->is_logged_in()) {
    $userid = $USER->get('id');
    $name = display_name($userid);
    $email = $USER->get('email');
}
else {
    $userid = 0;
    $name = '';
    $email = '';
}

$elements = array(
    'name' => array(
        'type'  => 'text',
        'title' => get_string('name'),
        'defaultvalue' => $name,
        'rules' => array(
            'required'    => true
        ),
    ),
    'email' => array(
        'type'  => 'text',
        'title' => get_string('email'),
        'defaultvalue' => $email,
        'rules' => array(
            'required'    => true,
            'email' => true,
        ),
    ),
    'subject' => array(
        'type'  => 'text',
        'title' => get_string('subject'),
        'defaultvalue' => '',
    ),
    'message' => array(
        'type'  => 'textarea',
        'rows'  => 10,
        'cols'  => 60,
        'title' => get_string('message'),
        'defaultvalue' => '',
        'rules' => array(
            'required'    => true
        ),
    )
);

$elements['userid'] = array(
    'type'  => 'hidden',
    'value' => $userid,
);
$elements['submit'] = array(
    'type'  => 'submit',
    'value' => get_string('sendmessage'),
    'class' => 'btn-primary submit'
);

$contactform = pieform(array(
    'name'       => 'contactus',
    'method'     => 'post',
    'action'     => '',
    'elements'   => $elements,
    'spam' => array(
        'secret'       => get_config('formsecret'),
        'class' => 'hidden',
        'mintime'      => 5,
        'hash'         => array('name', 'email', 'subject', 'message', 'userid', 'submit'),
        'reorder'      => array('name', 'email'),
    ),
));

function contactus_validate(Pieform $form, $values) {
    global $SESSION;
    $spamtrap = new_spam_trap(array(
        array(
            'type' => 'name',
            'value' => $values['name'],
        ),
        array(
            'type' => 'email',
            'value' => $values['email'],
        ),
        array(
            'type' => 'subject',
            'value' => $values['subject'],
        ),
        array(
            'type' => 'body',
            'value' => $values['message'],
        ),
    ));
    if ($form->spam_error() || $spamtrap->is_spam()) {
        $msg = get_string('formerror');
        $emailcontact = get_config('emailcontact');
        if (!empty($emailcontact)) {
            $msg .= ' ' . get_string('formerroremail', 'mahara', $emailcontact, $emailcontact);
        }
        $form->set_error(null, $msg);
    }
}

function contactus_submit(Pieform $form, $values) {
    global $SESSION;
    $data = new StdClass;
    $data->fromname    = $values['name'];
    $data->fromemail   = $values['email'];
    $data->subject     = $values['subject'];
    $data->message     = $values['message'];
    if ($values['userid']) {
        $data->fromuser = $values['userid'];
    }
    require_once('activity.php');
    activity_occurred('contactus', $data);
    $SESSION->add_ok_msg(get_string('messagesent'));
    redirect();
}

$smarty = smarty();
$smarty->assign('form', $contactform);
$smarty->display('form.tpl');
