<?php
/**
 *
 * @package    mahara
 * @subpackage artefact-multirecipientnotification
 * @author     David Ballhausen, Tobias Zeuch
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 *
 */

define('INTERNAL', 1);
define('MENUITEM', 'inbox');
require(dirname(dirname(dirname(__FILE__))) . '/init.php');
require_once(get_config('docroot') . '/lib/pieforms/pieform.php');
require_once(get_config('docroot') . '/lib/searchlib.php');
safe_require('search', 'internal');
safe_require('artefact', 'multirecipientnotification');

$id = param_integer('id', null);
$replytoid = param_integer('replyto', null);
$messages = null;
$users = array();
$user = null;

global $USER;
global $THEME;
global $SESSION;

$subject = '';

if (null !== $id) {
    $user = get_record('usr', 'id', $id);

    if (!$user) {
        throw new UserNotFoundException(get_string('cantmessageuser', 'group'));
    }
    else if ($user->deleted != 0) {
        throw new AccessDeniedException(get_string('cantmessageuserdeleted', 'group'));
    }
    else if (!can_send_message($USER->to_stdclass(), $id)) {
        throw new AccessDeniedException(get_string('cantmessageuser', 'group'));
    }

    $users[] = $id;
}
if (!is_null($replytoid)) {
    // Let us validate what we are going to reply first. The message should exist,
    // addressed to us and originated from the user we are replying to.
    $message = get_message_mr($USER->id, $replytoid);
    if (null === $message) {
        throw new AccessDeniedException(get_string('cantviewmessage', 'group'));
    }

    if (0 === count($users)) {
        foreach ($message->userids as $userrelid) {
            if ($USER->get('id') === $userrelid) {
                continue;
            }
            $deleted = get_field('usr', 'deleted', 'id', $userrelid);
            if (($deleted === '0') && can_send_message($USER->to_stdclass(), $userrelid) &&
                    $USER->id != $userrelid) {
                $users[] = $userrelid;
            }
            else {
                $SESSION->add_info_msg(get_string('removeduserfromlist', 'artefact.multirecipientnotification'));
            }
        }

        if ($USER->get('id') !== $message->fromid) {
            $deleted = get_field('usr', 'deleted', 'id', $message->fromid);
            if (($deleted === '0') && can_send_message($USER->to_stdclass(), $message->fromid) &&
                    $USER->id != $message->fromid) {
                $users[] = $message->fromid;
            }
            else {
                $SESSION->add_info_msg(get_string('removeduserfromlist', 'artefact.multirecipientnotification'));
            }
        }
    }

    // OK, now it is safe to fetch the whole thread.
    $messages = get_message_thread_mr($replytoid);

    if (!is_array($messages) || count($messages) <= 0) {
        throw new AccessDeniedException();
    }

    // there may be deleted users as sender or other recipients, so we format
    // all users here to not link to deleted users or the logged in user. Also
    // count deleted users and wrap them up in one span at the end
    foreach ($messages as $oldmessage) {
        $fromusr = get_user($oldmessage->fromid);
        if ($USER->get('id') === $oldmessage->fromid || $fromusr->deleted) {
            $oldmessage->fromusrlink = null;
        }
        else {
            $oldmessage->fromusrlink = profile_url($oldmessage->fromid);
        }
        if ($fromusr->deleted) {
            $oldmessage->fromusrname = get_string('deleteduser');
        }
        else {
            $oldmessage->fromusrname = display_name($oldmessage->fromid);
        }

        $countdeleted = 0;
        foreach ($oldmessage->userids as $tousrid) {
            if (get_user($tousrid)->deleted) {
                $countdeleted++;
            }
            else {
                $tousrarray = array(
                    'display' => display_name($tousrid),
                    'link' => null,
                );
                if ($tousrid !== $USER->get('id')) {
                    $tousrarray['link'] = profile_url($tousrid);
                }
                $oldmessage->tousrs[] = $tousrarray;
            }
        }
        if ($countdeleted > 0) {
            $oldmessage->tousrs[] = array(
                'display' => $countdeleted . ' ' . get_string('deleteduser', 'artefact.multirecipientnotification'),
                'link' => null,
            );
        }
    }

    $subject = $message->subject;
    $prefix = trim(get_string('replysubjectprefix', 'artefact.multirecipientnotification'));
    if (strpos($subject, $prefix) !== 0) {
        $subject = $prefix . ' ' . $subject;
    }
    // just in case, someone calls with replyto and returnto=view, which shouldn't
    // happen anyway. But in that case, proceed to first user in recipient-list
    if (sizeof($users) > 1) {
        $user = $users[0];
    }
}
define('TITLE', get_string('sendmessageto', 'artefact.multirecipientnotification'));

$returnto = param_alpha('returnto', 'myfriends');
$offset = param_integer('offset', 0);
switch ($returnto) {
    case 'find':
        $goto = 'user/find.php';
        break;
    case 'view':
        $goto = profile_url($user, false);
        break;
    case 'inbox':
        $goto = 'account/activity';
        break;
    case 'outbox':
        $goto = 'artefact/multirecipientnotification/outbox.php';
        break;
    case 'institution':
        $goto = ($inst = param_alpha('inst', null))
            ? 'institution/index.php?institution=' . $inst
            : 'account/activity';
        break;
    default:
      $goto = 'user/myfriends.php';
}
if ($offset > 0) {
    $goto .= (strpos($goto,'?')) ? '&offset=' . $offset : '?offset=' . $offset;
}

$form = pieform(array(
    'name' => 'sendmessage',
    'autofocus' => false,
    'validatecallback' => 'sendmessage_validate',
    'elements' => array(
        'recipients' => array(
            'type' => 'autocomplete',
            'title' => get_string('titlerecipient', 'artefact.multirecipientnotification'),
            'defaultvalue' => $users,
            'ajaxurl' => get_config('wwwroot') . 'artefact/multirecipientnotification/sendmessage.json.php',
            'initfunction' => 'translate_ids_to_names',
            'multiple' => true,
            'ajaxextraparams' => array(),
            'width' => '400px',
        ),
        'subject' => array(
            'title' => get_string('titlesubject', 'artefact.multirecipientnotification'),
            'type' => 'text',
            'name' => 'subject',
            'size' => '80',
            'defaultvalue' => $subject,
        ),
        'message' => array(
            'type'  => 'textarea',
            'title' => $messages ? get_string('Reply', 'group') : get_string('message'),
            'cols'  => 80,
            'rows'  => 10,
            'rules' => array('maxlength' => 65536),
        ),
        'goto' => array(
            'type' => 'hidden',
            'value' => $goto,
        ),
        'submit' => array(
            'type' => 'submitcancel',
            'value' => array($messages ? get_string('Reply', 'group') : get_string('sendmessage', 'group'), get_string('cancel')),
            'goto' => get_config('wwwroot') . $goto,
        )
    )
));

$javascripts = array(
    'artefact/multirecipientnotification/js/sendmessage.js',
);
$extrastylesheets = $THEME->get_url('style.css', false, 'artefact/multirecipientnotification');
$css = array(
    '<link rel="stylesheet" type="text/css" href="' . $extrastylesheets . '">',
);
$smarty = smarty($javascripts, $css);
$smarty->assign('PAGEHEADING', TITLE);
$smarty->assign('form', $form);
$smarty->assign('user', $USER);
$smarty->assign('messages', $messages);
$smarty->assign('link', get_config('wwwroot') . '/artefact/multirecipientnotification/sendmessage.php');
$smarty->assign('returnto', $returnto);
$smarty->display('artefact:multirecipientnotification:sendmessage.tpl');

function sendmessage_submit(Pieform $form, $values) {
    global $SESSION;

    send_user_message_mr($values['recipients'], $values['subject'], $values['message'], param_integer('replyto', null));
    $SESSION->add_ok_msg(get_string('messagesent', 'group'));
    redirect(get_config('wwwroot') . $values['goto']);
}

function sendmessage_validate(Pieform $form, $values) {
    if (empty($values['subject'])) {
        $form->set_error('subject', get_string('cantsendemptysubject', 'artefact.multirecipientnotification'));
    }
    if (empty($values['message'])) {
        $form->set_error('message', get_string('cantsendemptytext', 'artefact.multirecipientnotification'));
    }
    $recipients = array_diff($values['recipients'], array(''));
    if (empty($recipients)) {
        $form->set_error('recipients', get_string('cantsendnorecipients', 'artefact.multirecipientnotification'));
    }
}

function translate_ids_to_names(array $ids) {
    global $USER;
    // for an empty list, the element '' is transmitted
    $ids = array_diff($ids, array(''));
    $results = array();
    foreach ($ids as $id) {
        $deleted = get_field('usr', 'deleted', 'id', $id);
        if (($deleted === '0') && is_numeric($id) && can_send_message($USER->to_stdclass(), $id)) {
            $results[] = (object) array('id' => $id, 'text' => display_name($id));
        }
    }
    return $results;
}