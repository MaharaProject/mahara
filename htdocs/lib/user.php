<?php
/**
 * Mahara: Electronic portfolio, weblog, resume builder and social networking
 * Copyright (C) 2006-2007 Catalyst IT Ltd (http://www.catalyst.net.nz)
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
 * @author     Penny Leach <penny@catalyst.net.nz>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL
 * @copyright  (C) 2006,2007 Catalyst IT Ltd http://catalyst.net.nz
 *
 */

defined('INTERNAL') || die();

/** 
 * loads up activity preferences for a given user
 *
 * @param int $userid to load preferences for 
 * @todo caching
 */
function load_activity_preferences($userid) {
    $prefs = array();
    if (empty($userid)) {
        throw new InvalidArgumentException("couldn't load activity preferences, no user id specified");
    }
    if ($prefs = get_records_array('usr_activity_preference', 'usr', $userid)) {
        foreach ($prefs as $p) {
            $prefs[$p->activity] = $p->method;
        }
    }
    return $prefs;
}

/** 
 * loads up account preferences for a given user
 * if you want them for the current user
 * use $SESSION->accountprefs
 *
 * @param int $userid to load preferences for 
 * @todo caching
 * @todo defaults? 
 */
function load_account_preferences($userid) {
    $prefs = array();
    $expectedprefs = expected_account_preferences();
    if (empty($userid)) {
        throw new InvalidArgumentException("couldn't load account preferences, no user id specified");
    }
    if ($prefs = get_records_array('usr_account_preference', 'usr', $userid)) {
        foreach ($prefs as $p) {
            $prefs[$p->field] = $p->value;
        }
    }
    foreach ($expectedprefs as $field => $default) {
        if (!isset($prefs[$field])) {
            $prefs[$field] = $default;
        }
    }
    return $prefs;
}


/** 
 * sets a user preference in the database
 * if you want to set it in the session as well
 * use SESSION->set_account_preference 
 *
 * @param int $userid user id to set preference for
 * @param string $field preference field to set
 * @param string $value preference value to set.
 */
function set_account_preference($userid, $field, $value) {
    if (record_exists('usr_account_preference', 'usr', $userid, 'field', $field)) {
        set_field('usr_account_preference', 'value', $value, 'usr', $userid, 'field', $field);
    }
    else {
        try {
            $pref = new StdClass;
            $pref->usr = $userid;
            $pref->field = $field;
            $pref->value = $value;
            insert_record('usr_account_preference', $pref);
        }
        catch (Exception $e) {
            throw new InvalidArgumentException("Failed to insert account preference "
                ." $value for $field for user $userid");
        }
    }
}

/** 
 * sets an activity preference in the database
 * if you want to set it in the session as well
 * use $SESSION->set_activity_preference 
 *
 * @param int $userid user id to set preference for
 * @param int $activity activity type to set
 * @param string $method notification method to set.
 */
function set_activity_preference($userid, $activity, $method) {
    if (empty($method)) {
        return delete_records('usr_activity_preference', 'activity', $activity, 'usr', $userid);
    }
    if (record_exists('usr_activity_preference', 'usr', $userid, 'activity', $activity)) {
        set_field('usr_activity_preference', 'method', $method, 'usr', $userid, 'activity', $activity);
    }
    else {
        try {
            $pref = new StdClass;
            $pref->usr = $userid;
            $pref->activity = $activity;
            $pref->method = $method;
            insert_record('usr_activity_preference', $pref);
        }
        catch (Exception $e) {
            throw new InvalidArgumentException("Failed to insert activity preference "
                ." $method for $activity for user $userid");
        }
    }
}

/**
 * gets an account preference for the user, 
 * or the default if not set for that user,
 * as specified in {@link expected_account_preferences}
 *
 * @param int $userid id of user
 * @param string $field preference to get
 */
function get_account_preference($userid, $field) {
    if ($pref = get_record('usr_account_preference', 'usr', $userid, 'field', $field)) {
        return $pref->value;
    }
    $expected = expected_account_preferences();
    return $expected[$field];
}

/**
 * default account settings
 * 
 * @returns array of fields => values
 */
function expected_account_preferences() {
    return array('friendscontrol' => 'auth',
                 'wysiwyg'        =>  1,
                 'messages'       => 'allow',
                 'lang'           => 'default',
                 );
}

function set_profile_field($userid, $field, $value) {
    safe_require('artefact', 'internal');

    // this is a special case that replaces the primary email address with the
    // specified one
    if ($field == 'email') {
        try {
            $email = artefact_instance_from_type('email', $userid);
        }
        catch (ArtefactNotFoundException $e) {
            $email = new ArtefactTypeEmail();
            $email->set('owner', $userid);
        }
        $email->set('title', $value);
        $email->commit();
    }
    else {
        $classname = generate_artefact_class_name($field);
        $profile = new $classname(0, array('owner' => $userid));
        $profile->set('title', $value);
        $profile->commit();
    }
}

/**
 * Return the value of a profile field for a given user
 *
 * @param integer user id to find the profile field for
 * @param field what profile field you want the value for
 * @returns string the value of the profile field (null if it doesn't exist)
 *
 * @todo, this needs to be better (fix email behaviour)
 */
function get_profile_field($userid, $field) {
    $value = get_field('artefact', 'title', 'owner', $userid, 'artefacttype', $field);

    if ($value) {
        return $value;
    }

    return null;
}

/** 
 * Always use this function for all emails to users
 * 
 * @param object $userto user object to send email to. must contain firstname,lastname,preferredname,email
 * @param object $userfrom user object to send email from. If null, email will come from mahara
 * @param string $subject email subject
 * @param string $messagetext text version of email
 * @param string $messagehtml html version of email (will send both html and text)
 * @throws EmailException
 */ 
function email_user($userto, $userfrom, $subject, $messagetext, $messagehtml='') {

    if (empty($userto)) {
        throw new InvalidArgumentException("empty user given to email_user");
    }
    
    require_once('phpmailer/class.phpmailer.php');

    $mail = new phpmailer();

    $mail->Version = 'Mahara ' . get_config('release'); 
    $mail->PluginDir = get_config('libroot')  . 'phpmailer/';
    
    $mail->CharSet = 'UTF-8';

    $smtphosts = get_config('smtphosts');
    if ($smtphosts == 'qmail') {
        // use Qmail system
        $mail->IsQmail();
    } 
    else if (empty($smtphosts)) {
        // use PHP mail() = sendmail
        $mail->IsMail();
    }
    else {
        $mail->IsSMTP();
        // use SMTP directly
        $mail->Host = get_config('smtphosts');
        if (get_config('smtpuser')) {
            // Use SMTP authentication
            $mail->SMTPAuth = true;
            $mail->Username = get_config('smtpuser');
            $mail->Password = get_config('smtppass');
        }
    }

    if (empty($userfrom)) {
        $mail->Sender = get_config('noreplyaddress');
        $mail->From = $mail->Sender;
        $mail->FromName = get_string('emailname');
    }
    else {
        $mail->Sender = $userfrom->email;
        $mail->From = $mail->Sender;
        $mail->FromName = display_name($userfrom, $userto);
    }
           
    $mail->AddReplyTo($mail->From, $mail->FromName);

    $mail->Subject = substr(stripslashes($subject), 0, 78);

    $usertoname = display_name($userto, $userto);
    $mail->AddAddress($userto->email, $usertoname );

    $mail->WordWrap = 79;   

    if ($messagehtml) { 
        $mail->IsHTML(true);
        $mail->Encoding = 'quoted-printable';
        $mail->Body    =  $messagehtml;
        $mail->AltBody =  "\n$messagetext\n";
    } 
    else {
        $mail->IsHTML(false);
        $mail->Body =  "\n$messagetext\n";
    }

    if ($mail->Send()) {
        return true;
    } 
    throw new EmailException("Couldn't send email to $usertoname with subject $subject. "
                        . "Error from phpmailer was: " . $mail->ErrorInfo );
}

/**
 * converts a user object to a string representation of the user suitable for
 * the current user (or specified user) to see
 *
 * Both parameters should be objects containing id, preferredname, firstname,
 * lastname, admin
 *
 * @param object the user that you're trying to format to a string
 * @param object the user that is looking at the string representation (if left
 * blank, will default to the currently logged in user).
 *
 * @returns string name to display
 */
function display_name($user, $userto=null) {
    global $USER;
    
    if (empty($userto)) {
        $userto = new StdClass;
        $userto->id            = $USER->get('id');
        $userto->username      = $USER->get('username');
        $userto->preferredname = $USER->get('preferredname');
        $userto->firstname     = $USER->get('firstname');
        $userto->lastname      = $USER->get('lastname');
        $userto->admin         = $USER->get('admin');
        $userto->staff         = $USER->get('staff');
    }
    if (is_array($user)) {
        $user = (object)$user;
    }
    else if (is_numeric($user)) {
        $user = get_record('usr', 'id', $user);
    }
    if (!is_object($user)) {
        throw new InvalidArgumentException("Invalid user passed to display_name");
    }

    if ($user instanceof User) {
        $userObj = $user;
        $user = new StdClass;
        $user->id            = $userObj->get('id');
        $user->username      = $userObj->get('username');
        $user->preferredname = $userObj->get('preferredname');
        $user->firstname     = $userObj->get('firstname');
        $user->lastname      = $userObj->get('lastname');
        $user->admin         = $userObj->get('admin');
        $user->staff         = $userObj->get('staff');
    }

    // if they don't have a preferred name set, just return here
    if (empty($user->preferredname)) {
        if ($userto->admin || $userto->staff) {
            return $user->firstname . ' ' . $user->lastname . ' (' . $user->username . ')';
        }
        return $user->firstname . ' ' . $user->lastname;
    }

    if ($userto->admin || $userto->staff) {
        return $user->preferredname . ' (' . $user->firstname . ' ' . $user->lastname . ' - ' . $user->username . ')';
    }

    $sql = 'SELECT g1.member
            FROM {group_member} g1 
            JOIN {group_member} g2
                ON g1.group = g2.group 
            WHERE g1.member = ? AND g2.member = ? AND g2.tutor = ?';
    if (record_exists_sql($sql, array($user->id, $userto->id, 1))) {
        return $user->preferredname . ' (' . $user->firstname . ' ' . $user->lastname . ')';
    }
    return  $user->preferredname;
}

/**
 * function to format a users name when there is no user to look at them
 * ie when display_name is not going to work..
 */
function display_default_name($user) {
    if (is_array($user)) {
        $user = (object)$user;
    }
    else if (is_numeric($user)) {
        $user = get_record('usr', 'id', $user);
    }
    if (!is_object($user)) {
        throw new InvalidArgumentException("Invalid user passed to display_name");
    }

    if ($user instanceof User) {
        $userObj = $user;
        $user = new StdClass;
        $user->id            = $userObj->get('id');
        $user->preferredname = $userObj->get('preferredname');
        $user->firstname     = $userObj->get('firstname');
        $user->lastname      = $userObj->get('lastname');
        $user->admin         = $userObj->get('admin');
    }

    // if they don't have a preferred name set, just return here
    if (empty($user->preferredname)) {
        return $user->firstname . ' ' . $user->lastname;
    }
    else {
        return $user->preferredname;
    }
}



/**
 * Converts a user object to a full name representation, honouring the language
 * setting.
 *
 * Currently a stub, will need to be improved and completed as demand arises.
 *
 * @param object $user The user object to make a full name out of. If empty,
 *                     the global $USER object is used*/
function full_name($user=null) {
    global $USER;

    if ($user === null) {
        $user = new StdClass;
        $user->firstname = $USER->get('firstname');
        $user->lastname  = $USER->get('lastname');
    }

    return $user->firstname . ' ' . $user->lastname;
}


/**
 * helper function to default to currently
 * logged in user if there isn't an id specified
 * @throws InvalidArgumentException if there is no user and no $USER
 */
function optional_userid($userid) {

    if (!empty($userid)) {
        return $userid;
    }

    if (!is_logged_in()) {
        throw new InvalidArgumentException("optional_userid no userid and no logged in user");
    }
    
    global $USER;
    return $USER->get('id');
}



/**
 * helper function to default to currently
 * logged in user if there isn't an id specified
 * @throws InvalidArgumentException if there is no user and no $USER
 */
function optional_userobj($user) {

    if (!empty($user) && is_object($user)) {
        return $user;
    }

    if (!empty($user) && is_numeric($user)) {
        if ($user = get_record('usr', 'id', $user)) {
            return $user;
        }
        throw new InvalidArgumentException("optional_userobj given id $id no db match found");
    }

    if (!is_logged_in()) {
        throw new InvalidArgumentException("optional_userobj no userid and no logged in user");
    }
    
    global $USER;
    return $USER->to_stdclass();
}




/**
 * helper function for testing logins
 */
function is_logged_in() {
    global $USER;
    if (empty($USER)) {
        return false;
    }

    return $USER->is_logged_in();
}

/**
 * is there a friend relationship between these two users?
 *
 * @param int $userid1 
 * @param int $userid2
 */

function is_friend($userid1, $userid2) {
    return record_exists_select('usr_friend', '(usr1 = ? AND usr2 = ?) OR (usr2 = ? AND usr1 = ?)', 
                                array($userid1, $userid2, $userid1, $userid2));
}

/**
 * has there been a request between these two users?
 *
 * @param int $userid1
 * @param int $userid2
 */
function get_friend_request($userid1, $userid2) {
    return get_record_select('usr_friend_request', '(owner = ? AND requester = ?) OR (requester = ? AND owner = ?)',
                             array($userid1, $userid2, $userid1, $userid2));
        
} 

/**
 * Returns an object containing information about a user, including account
 * and activity preferences
 *
 * @param int $userid The ID of the user to retrieve information about
 * @return object     The user object. Note this is not in the same form as
 *                    the $USER object used to denote the current user -
 *                    the object returned by this method is a simple object.
 */
function get_user($userid) {
    if (!$user = get_record('usr', 'id', $userid, null, null, null, null,
        '*, ' . db_format_tsfield('expiry') . ', ' . db_format_tsfield('lastlogin'))) {
        throw new InvalidArgumentException('Unknown user ' . $userid);
    }

    $user->activityprefs = load_activity_preferences($userid);
    $user->accountprefs  = load_account_preferences($userid);
    return $user;
}


/**
 * Suspends a user
 *
 * @param int $suspendeduserid  The ID of the user to suspend
 * @param string $reason        The reason why the user is being suspended
 * @param int $suspendinguserid The ID of the user who is performing the suspension
 */
function suspend_user($suspendeduserid, $reason, $suspendinguserid=null) {
    if ($suspendinguserid === null) {
        global $USER;
        $suspendinguserid = $USER->get('id');
    }

    $suspendrec = new StdClass;
    $suspendrec->id              = $suspendeduserid;
    $suspendrec->suspendedcusr   = $suspendinguserid;
    $suspendrec->suspendedreason = $reason;
    $suspendrec->suspendedctime  = db_format_timestamp(time());
    update_record('usr', $suspendrec, 'id');

    $message = new StdClass;
    $message->users = array($suspendeduserid);
    $message->subject = get_string('youraccounthasbeensuspended');
    $message->message = get_string('youraccounthasbeensuspendedtext');
    activity_occurred('maharamessage', $message);

    handle_event('suspenduser', $suspendeduserid);
}

/**
 * handle the add/remove/approve/reject friend form
 * @param array $values from pieforms.
 */
function friend_submit(Pieform $form, $values) {
    global $user, $USER;

    $loggedinid = $USER->get('id');
    $userid = $user->id;

    // friend db record
    $f = new StdClass;
    $f->ctime = db_format_timestamp(time());
    
    // notification info
    $n = new StdClass;
    $n->url = get_config('wwwroot') . 'user/view.php?id=' . $loggedinid;
    $n->users = array($user->id);
    $displayname = display_name($USER, $user);

    switch ($values['type']) {
    case 'add':
        $f->usr1 = $values['id'];
        $f->usr2 = $loggedinid;
        insert_record('usr_friend', $f);
        $n->subject = get_string('addedtofriendslistsubject');
        $n->message = get_string('addedtofriendslistmessage', 'mahara', $displayname, $displayname);
        break;
    case 'request':
        $f->owner     = $values['id'];
        $f->requester = $loggedinid;
        $f->reason    = $values['reason'];
        insert_record('usr_friend_request', $f);
        $n->subject = get_string('requestedfriendlistsubject');
        if (isset($values['reason']) && !empty($values['reason'])) {
            $n->message = get_string('requestedfriendlistmessagereason', 'mahara', $displayname) . $values['reason'];
        }
        else {
            $n->message = get_string('requestedfriendlistmessage', 'mahara', $displayname);
        }
        break;
    case 'remove':
        delete_records_select('usr_friend', '(usr1 = ? AND usr2 = ?) OR (usr2 = ? AND usr1 = ?)', 
                                array($userid, $loggedinid, $userid, $loggedinid));
        $n->subject = get_string('removedfromfriendslistsubject');
        if (isset($values['reason']) && !empty($values['reason'])) {
            $n->message = get_string('removedfromfriendslistmessage', 'mahara', $displayname) . $values['reason'];
        }
        else {
            $n->message = get_string('removedfromfriendslistmessage', 'mahara', $displayname);
        }
        break;
    case 'accept':
        if (isset($values['rejectsubmit']) && !empty($values['rejectsubmit'])) {
            delete_records('usr_friend_request', 'owner', $loggedinid, 'requester', $userid);
            $n->subject = get_string('friendrequestrejectedsubject');
            if (isset($values['rejectreason']) && !empty($values['rejectreason'])) {
                $n->message = get_string('friendrequestrejectedmessagereason', 'mahara', $displayname) . $values['rejectreason'];
            }
            else {
                $n->message = get_string('friendrequestrejectedmessage', 'mahara', $displayname);
            }
            $values['type'] = 'reject'; // for json reply message
        } 
        else {
            db_begin();
            delete_records('usr_friend_request', 'owner', $loggedinid, 'requester', $userid);
            $f->usr1 = $userid;
            $f->usr2 = $loggedinid;
            insert_record('usr_friend', $f);
            $n->subject = get_string('friendrequestacceptedsubject');
            $n->message = get_string('friendrequestacceptedmessage', 'mahara', $displayname, $displayname);
            db_commit();
        }
        break;
    }
    activity_occurred('maharamessage', $n);

    if ($form instanceof Pieform) {
        $form->json_reply(PIEFORM_OK, get_string('friendform' . $values['type'] . 'success', 'mahara', display_name($user)));
    }
    else {
        json_reply(false, get_string('friendform' . $values['type'] . 'success', 'mahara', display_name($user)));
    }
}

/**
 * Unsuspends a user
 *
 * @param int $userid The ID of the user to unsuspend
 */
function unsuspend_user($userid) {
    $suspendedrec = new StdClass;
    $suspendedrec->id = $userid;
    $suspendedrec->suspendedcusr = null;
    $suspendedrec->suspendedreason = null;
    $suspendedrec->suspendedctime  = null;
    update_record('usr', $suspendedrec);

    $message = new StdClass;
    $message->users = array($userid);
    $message->subject = get_string('youraccounthasbeenunsuspended');
    $message->message = get_string('youraccounthasbeenunsuspendedtext');
    activity_occurred('maharamessage', $message);

    handle_event('unsuspenduser', $userid);
}

/**
 * Deletes a user
 *
 * @param int $userid The ID of the user to delete
 */
function delete_user($userid) {
    db_begin();

    $emailsuffix = '.deleted.' . time();

    $deleterec = new StdClass;
    $deleterec->id = $userid;
    $deleterec->deleted = 1;
    $deleterec->email = get_field('usr', 'email', 'id', $userid) . $emailsuffix;
    update_record('usr', $deleterec);

    // Because the user is being deleted, but their email address may be wanted 
    // for a new user, we change their email addresses to add 
    // 'deleted.[timestamp]'
    execute_sql('UPDATE {artefact_internal_profile_email}
    SET email = email || ?
    WHERE owner = ?', array($emailsuffix, $userid));

    db_commit();

    handle_event('deleteuser', $userid);
}

/**
 * Undeletes a user
 *
 * NOTE: changing their email addresses to remove the .deleted.timestamp part 
 * has not been implemented yet! This function is not actually used anywhere in 
 * Mahara, so hasn't really been tested because of this. It's a simple enough 
 * job for the first person who gets there - see how delete_user works to see 
 * what you must undo.
 *
 * @param int $userid The ID of the user to undelete
 */
function undelete_user($userid) {
    $deleterec = new StdClass;
    $deleterec->id = $userid;
    $deleterec->deleted = 0;
    update_record('usr', $deleterec);

    handle_event('undeleteuser', $userid);
}

/**
 * Expires a user
 *
 * Nothing amazing needs to happen here, but this function is here for
 * consistency.
 *
 * This function is called when a user account is detected to be expired.
 * It is assumed that the account actually is expired.
 *
 * @param int $userid The ID of user to expire
 */
function expire_user($userid) {
    handle_event('expireuser', $userid);
}

/**
 * Unexpires a user
 *
 * @param int $userid The ID of user to unexpire
 */
function unexpire_user($userid) {
    handle_event('unexpireuser', $userid);
}

/**
 * Marks a user as inactive
 *
 * Nothing amazing needs to happen here, but this function is here for
 * consistency.
 *
 * This function is called when a user account is detected to be inactive.
 * It is assumed that the account actually is inactive.
 *
 * @param int $userid The ID of user to mark inactive
 */
function deactivate_user($userid) {
    handle_event('deactivateuser', $userid);
}

/**
 * Activates a user
 *
 * @param int $userid The ID of user to reactivate
 */
function activate_user($userid) {
    handle_event('activateuser', $userid);
}

/**
 * Sends a message from one user to another
 *
 * @param object $to User to send the message to
 * @param string $message The message to send
 * @param object $from Who to send the message from. If not set, defaults to 
 * the currently logged in user
 * @throws AccessDeniedException if the message is not allowed to be sent (as 
 * configured by the 'to' user's settings)
 */
function send_user_message($to, $message, $from=null) {
    // FIXME: permission checking!
    if ($from === null) {
        global $USER;
        $from = $USER;
    }

    $messagepref = get_account_preference($to->id, 'messages');
    if ((is_friend($from->id, $to->id) && $messagepref == 'friends') || $messagepref == 'allow' || $from->get('admin')) {
        activity_occurred('usermessage', 
            array(
                'userto'   => $to->id, 
                'userfrom' => $from->id, 
                'message'  => $message,
            )
        );
    }
    else {
        throw new AccessDeniedException('Cannot send messages between ' . display_name($from) . ' and ' . display_name($to));
    }
}

?>
