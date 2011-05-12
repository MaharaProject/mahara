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
 *
 */

defined('INTERNAL') || die();

define('MAX_USERNAME_DISPLAY', 30);

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
    if ($prefs = get_records_assoc('usr_activity_preference', 'usr', $userid, '', 'activity,method')) {
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
    if ($field == 'lang') {
        $oldlang = get_field('usr_account_preference', 'value', 'usr', $userid, 'field', 'lang');
        if (empty($oldlang) || $oldlang == 'default') {
            $oldlang = get_config('lang');
        }
        $newlang = (empty($value) || $value == 'default') ? get_config('lang') : $value;
        if ($newlang != $oldlang) {
            change_language($userid, $oldlang, $newlang);
        }
    }
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
 * Change language-specific stuff in the db for a user.  Currently
 * changes the name of the 'assessmentfiles' folder in the user's
 * files area and the views and artefacts tagged for the profile
 * sideblock
 *
 * @param int $userid user id to set preference for
 * @param string $oldlang old language
 * @param string $newlang new language
 */
function change_language($userid, $oldlang, $newlang) {
    if (get_field('artefact_installed', 'active', 'name', 'file')) {
        safe_require('artefact', 'file');
        ArtefactTypeFolder::change_language($userid, $oldlang, $newlang);
    }
    set_field_select('artefact_tag', 'tag', get_string_from_language($newlang, 'profile'), 'WHERE tag = ? AND artefact IN (SELECT id FROM {artefact} WHERE "owner" = ?)', array(get_string_from_language($oldlang, 'profile'), $userid));
    set_field_select('view_tag', 'tag', get_string_from_language($newlang, 'profile'), 'WHERE tag = ? AND "view" IN (SELECT id FROM {view} WHERE "owner" = ?)', array(get_string_from_language($oldlang, 'profile'), $userid));
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


function get_user_language($userid) {
    $langpref = get_account_preference($userid, 'lang');
    if (empty($langpref) || $langpref == 'default') {
        return get_config('lang');
    }
    return $langpref;
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
                 'addremovecolumns' => 0,
                 'maildisabled'   => 0,
                 'tagssideblockmaxtags' => get_config('tagssideblockmaxtags'),
                 'hiderealname'   => 0,
                 'multipleblogs' => 0,
                 'showhomeinfo' => 1,
                 'mobileuploadtoken' => '',
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
    if ($field == 'email') {
        $value = get_field_sql("
            SELECT a.title
            FROM {usr} u
            JOIN {artefact} a ON (a.title = u.email AND a.owner = u.id)
            WHERE a.artefacttype = 'email' AND u.id = ?", array($userid));
    }
    else {
        $value = get_field('artefact', 'title', 'owner', $userid, 'artefacttype', $field);
    }

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
 * @param array  $customheaders email headers
 * @throws EmailException
 * @throws EmailDisabledException
 */ 
function email_user($userto, $userfrom, $subject, $messagetext, $messagehtml='', $customheaders=null) {
    global $IDPJUMPURL;
    static $mnetjumps = array();

    if (!get_config('sendemail')) {
        // You can entirely disable Mahara from sending any e-mail via the 
        // 'sendemail' configuration variable
        return true;
    }

    if (empty($userto)) {
        throw new InvalidArgumentException("empty user given to email_user");
    }

    if (isset($userto->id) && empty($userto->ignoredisabled)) {
        $maildisabled = property_exists($userto, 'maildisabled') ? $userto->maildisabled : get_account_preference($userto->id, 'maildisabled') == 1;
        if ($maildisabled) {
            throw new EmailDisabledException("email for this user has been disabled");
        }
    }

    // If the user is a remote xmlrpc user, trawl through the email text for URLs
    // to our wwwroot and modify the url to direct the user's browser to login at
    // their home site before hitting the link on this site
    if (!empty($userto->mnethostwwwroot) && !empty($userto->mnethostapp)) {
        require_once(get_config('docroot') . 'auth/xmlrpc/lib.php');

        // Form the request url to hit the idp's jump.php
        if (isset($mnetjumps[$userto->mnethostwwwroot])) {
            $IDPJUMPURL = $mnetjumps[$userto->mnethostwwwroot];
        } else {
            $mnetjumps[$userto->mnethostwwwroot] = $IDPJUMPURL = PluginAuthXmlrpc::get_jump_url_prefix($userto->mnethostwwwroot, $userto->mnethostapp);
        }

        $wwwroot = get_config('wwwroot');
        $messagetext = preg_replace_callback('%(' . $wwwroot . '([\w_:\?=#&@/;.~-]*))%',
            'localurl_to_jumpurl',
            $messagetext);
        $messagehtml = preg_replace_callback('%href=["\'`](' . $wwwroot . '([\w_:\?=#&@/;.~-]*))["\'`]%',
            'localurl_to_jumpurl',
            $messagehtml);
    }


    require_once('phpmailer/class.phpmailer.php');

    $mail = new phpmailer();

    // Leaving this commented out - there's no reason for people to know this
    //$mail->Version = 'Mahara ' . get_config('release');
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
            $mail->SMTPAuth   = true;
            $mail->Username   = get_config('smtpuser');
            $mail->Password   = get_config('smtppass');
            $mail->SMTPSecure = get_config('smtpsecure');
            $mail->Port       = get_config('smtpport');
            if (get_config('smtpsecure') && !get_config('smtpport')) {
                // Encrypted connection with no port. Use default one.
                if (get_config('smtpsecure') == 'ssl') {
                    $mail->Port = 465;
                }
                elseif (get_config('smtpsecure') == 'tls') {
                    $mail->Port = 587;
                }
            }
        }
    }

    if (get_config('bounces_handle') && !empty($userto->id) && empty($maildisabled)) {
        $mail->Sender = generate_email_processing_address($userto->id, $userto);
    }
    if (empty($userfrom) || $userfrom->email == get_config('noreplyaddress')) {
        if (empty($mail->Sender)) {
            $mail->Sender = get_config('noreplyaddress');
        }
        $mail->From = get_config('noreplyaddress');
        $mail->FromName = (isset($userfrom->id)) ? display_name($userfrom, $userto) : get_config('sitename');
        $customheaders[] = 'Precedence: Bulk'; // Try to avoid pesky out of office responses
        $messagetext .= "\n\n" . get_string('pleasedonotreplytothismessage') . "\n";
        if ($messagehtml) {
            $messagehtml .= "\n\n<p>" . get_string('pleasedonotreplytothismessage') . "</p>\n";
        }
    }
    else {
        if (empty($mail->Sender)) {
            $mail->Sender = $userfrom->email;
        }
        $mail->From = $userfrom->email;
        $mail->FromName = display_name($userfrom, $userto);
    }
    $replytoset = false;
    if (!empty($customheaders) && is_array($customheaders)) {
        foreach ($customheaders as $customheader) {
            $mail->AddCustomHeader($customheader);
            if (0 === stripos($customheader, 'reply-to')) {
                $replytoset = true;
            }
        }
    }

    if (!$replytoset) {
        $mail->AddReplyTo($mail->From, $mail->FromName);
    }

    $mail->Subject = substr(stripslashes($subject), 0, 900);

    if ($to = get_config('sendallemailto')) {
        // Admins can configure the system to send all email to a given address 
        // instead of whoever would receive it, useful for debugging.
        $mail->addAddress($to);
        $notice = get_string('debugemail', 'mahara', display_name($userto, $userto), $userto->email);
        $messagetext =  $notice . "\n\n" . $messagetext;
        if ($messagehtml) {
            $messagehtml = '<p>' . hsc($notice) . '</p>' . $messagehtml;
        }
        $usertoname = display_name($userto, $userto, true) . ' (' . get_string('divertingemailto', 'mahara', $to) . ')';
    }
    else {
        $usertoname = display_name($userto, $userto);
        $mail->AddAddress($userto->email, $usertoname );
        $to = $userto->email;
    }

    if ($mail->IsError()) {
        // If there's a phpmailer error already, assume it's an invalid address
        throw new InvalidEmailException("Cannot send email to $usertoname with subject $subject. Error from phpmailer was: " . $mail->ErrorInfo);
    }

    $mail->WordWrap = 79;   

    if ($messagehtml) { 
        $mail->IsHTML(true);
        $mail->Encoding = 'quoted-printable';
        $mail->Body    =  $messagehtml;
        $mail->AltBody =  $messagetext;
    } 
    else {
        $mail->IsHTML(false);
        $mail->Body =  $messagetext;
    }

    if ($mail->Send()) {
        if ($logfile = get_config('emaillog')) {
            $docroot = get_config('docroot');
            @$client = (string) $_SERVER['REMOTE_ADDR'];
            @$script = (string) $_SERVER['SCRIPT_FILENAME'];
            if (strpos($script, $docroot) === 0) {
                $script = substr($script, strlen($docroot));
            }
            $line = "$to <- $mail->From - " . str_shorten_text($mail->Subject, 200);
            @error_log('[' . date("Y-m-d h:i:s") . "] [$client] [$script] $line\n", 3, $logfile);
        }

        if (get_config('bounces_handle')) {
            // Update the count of sent mail
            update_send_count($userto);
        }

        return true;
    } 
    throw new EmailException("Couldn't send email to $usertoname with subject $subject. "
                        . "Error from phpmailer was: " . $mail->ErrorInfo );
}

/**
 * Generate an email processing address for VERP handling of email
 *
 * @param int $userid the ID of the user sending the mail
 * @param object $userto an object containing the email address
 * @param char $type The type of address to generate
 *
 * The type of address is typically a Bounce. These are processed by the
 * process_email function.
 */
function generate_email_processing_address($userid, $userto, $type='B') {
    $mailprefix = get_config('bounceprefix');
    $maildomain = get_config('bouncedomain');
    $installation_key = get_config('installation_key');
    $args = $type . base64_encode(pack('V', $userid)) . substr(md5($userto->email), 0, 16);
    return $mailprefix . $args . substr(md5($mailprefix . $userto->email . $installation_key), 0, 16) . '@' . $maildomain;
}

/**
 * Check whether an email account is over the site-wide bounce threshold.
 * If the user is over threshold, then e-mail is disabled for their
 * account, and they are sent a notification to notify them of the change.
 *
 * @param object $mailinfo The row from artefact_internal_profile_email for
 * the user being processed.
 * @return boolean false if the user is not over threshold, true if they
 * are.
 */
function check_overcount($mailinfo) {
    // if we don't handle bounce e-mails, then we can't be over threshold
    if (!get_config('bounces_handle')) {
        return false;
    }

    if ((! $minbounces = get_config('bounces_min')) || (! $bounceratio = get_config('bounces_ratio'))) {
        return false;
    }

    if ($mailinfo->mailssent == 0) {
        return false;
    }

    // If the bouncecount is larger than the allowed amount
    // and the bounce count ratio (bounces/total sent) is larger than the
    // bounceratio, then disable email
    $overlimit = ($mailinfo->mailsbounced >= $minbounces) && ($mailinfo->mailsbounced/$mailinfo->mailssent >= $bounceratio);

    if ($overlimit) {
        if (get_account_preference($mailinfo->owner,'maildisabled') != 1) {
            // Disable the e-mail account
            db_begin();
            set_account_preference($mailinfo->owner, 'maildisabled', 1);

            $lang = get_user_language($mailinfo->owner);

            // Send a notification that e-mail has been disabled
            $message = new StdClass;
            $message->users = array($mailinfo->owner);

            $message->subject = get_string_from_language($lang, 'maildisabled', 'account');
            $message->message = get_string_from_language($lang, 'maildisabledbounce', 'account', get_config('wwwroot') . 'account/');

            require_once('activity.php');
            activity_occurred('maharamessage', $message);

            db_commit();
        }
        return true;
    }
    return false;
}

/**
 * Update the send count for the specified e-mail address
 *
 * @param object $userto object to update count for. Must contain email and
 * user id
 * @param boolean reset Reset the sent mail count to 0 (optional).
 */
function update_send_count($userto, $reset=false) {
    if (!$userto->id) {
        // We need a user id to update the send count.
        return false;
    }
    if ($mailinfo = get_record_select('artefact_internal_profile_email', '"owner" = ? AND email = ? AND principal = 1', array($userto->id, $userto->email))) {
        $mailinfo->mailssent = (!empty($reset)) ? 0 : $mailinfo->mailssent+1;
        update_record('artefact_internal_profile_email', $mailinfo, array('email' => $userto->email, 'owner' => $userto->id));
    }
}

/**
 * Update the bounce count for the specified e-mail address
 *
 * @param object $userto object to update count for. Must contain email and
 * user id
 * @param boolean reset Reset the sent mail count to 0 (optional).
 */
function update_bounce_count($userto, $reset=false) {
    if (!$userto->id) {
        // We need a user id to update the bounce count.
        return false;
    }
    if ($mailinfo = get_record_select('artefact_internal_profile_email', '"owner" = ? AND email = ? AND principal = 1', array($userto->id, $userto->email))) {
        $mailinfo->mailsbounced = (!empty($reset)) ? 0 : $mailinfo->mailsbounced+1;
        update_record('artefact_internal_profile_email', $mailinfo, array('email' => $userto->email, 'owner' => $userto->id));
    }
}

/**
 * Process an incoming email
 *
 * @param string $address the email address to process
 */
function process_email($address) {

    $email = new StdClass;

    if (strlen($address) <= 30) {
        log_debug ('-- Email address not long enough to contain valid data.');
        return $email;
    }

    if (!strstr($address, '@')) {
        log_debug ('-- Email address does not contain @.');
        return $email;
    }

    list($email->localpart,$email->domain) = explode('@',$address);
    // The prefix is stored in the first four characters
    $email->prefix        = substr($email->localpart,0,4);
    // The type of message received is a one letter code
    $email->type          = substr($email->localpart,4,1);
    // The userid should be available immediately afterwards
    list(,$email->userid) = unpack('V',base64_decode(substr($email->localpart,5,8)));
    // Any additional arguments
    $email->args          = substr($email->localpart,13,-16);
    // And a hash of the intended recipient for authentication
    $email->addresshash   = substr($email->localpart,-16);

    if (!$email->userid) {
        log_debug('-- no userid associated with this email address');
        return $email;
    }

    switch ($email->type) {
    case 'B': // E-mail bounces
        if ($user = get_record_select('artefact_internal_profile_email', '"owner" = ? AND principal = 1', array($email->userid))) {
            $mailprefix = get_config('bounceprefix');
            $maildomain = get_config('bouncedomain');
            $installation_key = get_config('installation_key');
            // check the half md5 of their email
            $md5check = substr(md5($mailprefix . $user->email . $installation_key), 0, 16);
            $user->id = $user->owner;
            if ($md5check == substr($email->addresshash, -16)) {
                update_bounce_count($user);
                check_overcount($user);
            }
            // else maybe they've already changed their email address
        }
        break;
        // No more cases yet
    }
    return $email;
}

/**
 * converts a user object to a string representation of the user suitable for
 * the current user (or specified user) to see
 *
 * Both parameters should be objects containing id, preferredname, firstname,
 * lastname, admin
 *
 * @param object $user the user that you're trying to format to a string
 * @param object $userto the user that is looking at the string representation (if left
 * blank, will default to the currently logged in user).
 * @param boolean $nameonly do not append the user's username even if $userto can see it.
 * @param boolean $realname show the user's real name even if preferredname exists
 * @param boolean $username show the user's username even if the viewer is not an admin
 *
 * @returns string name to display
 */
function display_name($user, $userto=null, $nameonly=false, $realname=false, $username=false) {
    global $USER;
    static $tutorcache  = array();
    static $usercache   = array();

    if ($nameonly) {
        return display_default_name($user);
    }

    if (empty($userto)) {
        $userto = new StdClass;
        $userto->id            = $USER->get('id');
        $userto->username      = $USER->get('username');
        $userto->preferredname = $USER->get('preferredname');
        $userto->firstname     = $USER->get('firstname');
        $userto->lastname      = $USER->get('lastname');
        $userto->admin         = $USER->get('admin') || $USER->is_institutional_admin();
        $userto->staff         = $USER->get('staff') || $USER->is_institutional_staff();
    }
    else if (is_numeric($userto)) {
        if (isset($usercache[$userto])) {
            $userto = $usercache[$userto];
        }
        else if ($userto == $USER->get('id')) {
            $userto = new StdClass;
            $userto->id            = $USER->get('id');
            $userto->username      = $USER->get('username');
            $userto->preferredname = $USER->get('preferredname');
            $userto->firstname     = $USER->get('firstname');
            $userto->lastname      = $USER->get('lastname');
            $userto->admin         = $USER->get('admin') || $USER->is_institutional_admin();
            $userto->staff         = $USER->get('staff') || $USER->is_institutional_staff();
            $usercache[$userto->id] = $userto;
        }
        else {
            $userto = $usercache[$userto] = get_record('usr', 'id', $userto);
        }
    }

    if (is_array($user)) {
        $user = (object)$user;
    }
    else if (is_numeric($user)) {
        if (isset($usercache[$user])) {
            $user = $usercache[$user];
        }
        else if ($user == $USER->get('id')) {
            $user = new StdClass;
            $user->id            = $USER->get('id');
            $user->username      = $USER->get('username');
            $user->preferredname = $USER->get('preferredname');
            $user->firstname     = $USER->get('firstname');
            $user->lastname      = $USER->get('lastname');
            $user->admin         = $USER->get('admin') || $USER->is_institutional_admin();
            $user->staff         = $USER->get('staff') || $USER->is_institutional_staff();
            $user->deleted       = 0;
            $usercache[$user->id] = $user;
        }
        else {
            $user = $usercache[$user] = get_record('usr', 'id', $user);
        }
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
        $user->deleted       = $userObj->get('deleted');
    }

    $user->id   = (isset($user->id)) ? $user->id : null;
    $userto->id = (isset($userto->id)) ? $userto->id : null;

    $addusername = $username || !empty($userto->admin) || !empty($userto->staff);

    // if they don't have a preferred name set, just return here
    if (empty($user->preferredname)) {
        $firstlast = full_name($user);
        if ($addusername) {
            return $firstlast . ' (' . display_username($user) . ')';
        }
        return $firstlast;
    }
    else if ($user->id == $userto->id) {
        // If viewing our own name, show it how we like it
        return $user->preferredname;
    }

    // Preferred name is set
    $addrealname = $realname || !empty($userto->admin) || !empty($userto->staff);

    if (!$addrealname) {
        // Tutors can always see the user's real name, so we need to check if the
        // viewer is a tutor of the user whose name is being displayed
        if (!isset($tutorcache[$user->id][$userto->id])) {
            $tutorcache[$user->id][$userto->id] = record_exists_sql('
                SELECT s.member
                FROM {group_member} s
                JOIN {group_member} t ON s.group = t.group
                JOIN {group} g ON (g.id = s.group AND g.deleted = 0)
                JOIN {grouptype_roles} gtr
                    ON (g.grouptype = gtr.grouptype AND gtr.role = t.role AND gtr.see_submitted_views = 1)
                WHERE s.member = ? AND t.member = ?',
                array($user->id, $userto->id)
            );
        }
        $addrealname = $tutorcache[$user->id][$userto->id];
    }

    if ($addrealname) {
        $firstlast = full_name($user);
        if ($addusername) {
            return $user->preferredname . ' (' . $firstlast . ' - ' . display_username($user) . ')';
        }
        return $user->preferredname . ' (' . $firstlast . ')';
    }
    if ($addusername) {
        return $user->preferredname . ' (' . display_username($user) . ')';
    }
    return $user->preferredname;
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
        $user->deleted       = $userObj->get('deleted');
    }

    return empty($user->preferredname) ? full_name($user) : $user->preferredname;
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
        $user->deleted   = $USER->get('deleted');
    }

    return isset($user->deleted) && $user->deleted ? get_string('deleteduser') : $user->firstname . ' ' . $user->lastname;
}

/**
 * Creates a string containing a displayable username.
 *
 * If the username is longer than 30 characters (bug #548165), then print
 * the first 30 characters followed by '...'
 *
 * @param object $user The user object to display the username of. If empty,
 *                     the global $USER object is used
 */
function display_username($user=null) {
    global $USER;

    if ($user === null) {
        $user = new StdClass;
        $user->username = $USER->get('username');
    }

    if (strlen($user->username) > MAX_USERNAME_DISPLAY) {
        return substr($user->username, 0, MAX_USERNAME_DISPLAY).'...';
    }
    else {
        return $user->username;
    }
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
    return get_record_select('usr_friend_request', '("owner" = ? AND requester = ?) OR (requester = ? AND "owner" = ?)',
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

    // Try to kick the user from any active login session.
    require_once(get_config('docroot') . 'auth/session.php');
    remove_user_sessions($suspendeduserid);

    $lang = get_user_language($suspendeduserid);
    $message = new StdClass;
    $message->users = array($suspendeduserid);
    $message->subject = get_string_from_language($lang, 'youraccounthasbeensuspended');
    if ($reason == '') {
        $message->message = get_string_from_language($lang, 'youraccounthasbeensuspendedtext2', 'mahara',
            get_config('sitename'), display_name($suspendinguserid, $suspendeduserid));
    }
    else {
        $message->message = get_string_from_language($lang, 'youraccounthasbeensuspendedreasontext', 'mahara',
            get_config('sitename'), display_name($suspendinguserid, $suspendeduserid), $reason);
    }
    require_once('activity.php');
    activity_occurred('maharamessage', $message);

    handle_event('suspenduser', $suspendeduserid);
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

    $lang = get_user_language($userid);
    $message = new StdClass;
    $message->users = array($userid);
    $message->subject = get_string_from_language($lang, 'youraccounthasbeenunsuspended');
    $message->message = get_string_from_language($lang, 'youraccounthasbeenunsuspendedtext2', 'mahara', get_config('sitename'));
    require_once('activity.php');
    activity_occurred('maharamessage', $message);

    handle_event('unsuspenduser', $userid);
}

/**
 * Deletes a user
 *
 * This function ensures that a user is deleted according to how Mahara wants a 
 * deleted user to be. You can call it multiple times on the same user without 
 * harm.
 *
 * @param int $userid The ID of the user to delete
 */
function delete_user($userid) {
    db_begin();

    // We want to append 'deleted.timestamp' to some unique fields in the usr 
    // table, so they can be reused by new accounts
    $fieldstomunge = array('username', 'email');
    $datasuffix = '.deleted.' . time();

    $user = get_record('usr', 'id', $userid, null, null, null, null, implode(', ', $fieldstomunge));

    $deleterec = new StdClass;
    $deleterec->id = $userid;
    $deleterec->deleted = 1;
    foreach ($fieldstomunge as $field) {
        if (!preg_match('/\.deleted\.\d+$/', $user->$field)) {
            $deleterec->$field = $user->$field . $datasuffix;
        }
    }

    // Set authinstance to default internal, otherwise the old authinstance can be blocked from deletion
    // by deleted users.
    $authinst = get_field('auth_instance', 'id', 'institution', 'mahara', 'authname', 'internal');
    if ($authinst) {
        $deleterec->authinstance = $authinst;
    }

    update_record('usr', $deleterec);

    // Remove user from any groups they're in, invited to or want to be in
    $groupids = get_column('group_member', '"group"', 'member', $userid);
    if ($groupids) {
        require_once(get_config('libroot') . 'group.php');
        foreach ($groupids as $groupid) {
            group_remove_user($groupid, $userid, true);
        }
    }
    delete_records('group_member_request', 'member', $userid);
    delete_records('group_member_invite', 'member', $userid);

    // Remove any friend relationships the user is in
    execute_sql('DELETE FROM {usr_friend}
        WHERE usr1 = ?
        OR usr2 = ?', array($userid, $userid));
    execute_sql('DELETE FROM {usr_friend_request}
        WHERE owner = ?
        OR requester = ?', array($userid, $userid));

    delete_records('artefact_access_usr', 'usr', $userid);
    delete_records('auth_remote_user', 'localusr', $userid);
    delete_records('import_queue', 'usr', $userid);
    delete_records('usr_account_preference', 'usr', $userid);
    delete_records('usr_activity_preference', 'usr', $userid);
    delete_records('usr_infectedupload', 'usr', $userid);
    delete_records('usr_institution', 'usr', $userid);
    delete_records('usr_institution_request', 'usr', $userid);
    delete_records('usr_password_request', 'usr', $userid);
    delete_records('usr_watchlist_view', 'usr', $userid);
    delete_records('view_access', 'usr', $userid);

    // Remove the user's views & artefacts
    $viewids = get_column('view', 'id', 'owner', $userid);
    if ($viewids) {
        require_once(get_config('libroot') . 'view.php');
        foreach ($viewids as $viewid) {
            $view = new View($viewid);
            $view->delete();
        }
    }

    $artefactids = get_column('artefact', 'id', 'owner', $userid);
    // @todo: test all artefact bulk_delete stuff, then replace the one-by-one
    // artefact deletion below with ArtefactType::delete_by_artefacttype($artefactids);
    if ($artefactids) {
        foreach ($artefactids as $artefactid) {
            try {
                $a = artefact_instance_from_id($artefactid);
                $a->delete();
            }
            catch (ArtefactNotFoundException $e) {
                // Awesome, it's already gone.
            }
        }
    }

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
 * Get the thread of message up to this point, given the id of
 * the message being replied to.
 */
function get_message_thread($replyto) {
    $message = get_record('notification_internal_activity', 'id', $replyto);
    if (empty($message->parent)) {
        return array($message);
    }
    return array_merge(get_message_thread($message->parent), array($message));
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
function send_user_message($to, $message, $parent, $from=null) {
    // FIXME: permission checking!
    if ($from === null) {
        global $USER;
        $from = $USER;
    }

    $messagepref = get_account_preference($to->id, 'messages');
    if ($messagepref == 'allow' || ($messagepref == 'friends' && is_friend($from->id, $to->id)) || $from->get('admin')) {
        require_once('activity.php');
        activity_occurred('usermessage', 
            array(
                'userto'   => $to->id, 
                'userfrom' => $from->id, 
                'message'  => $message,
                'parent'   => $parent,
            )
        );
    }
    else {
        throw new AccessDeniedException('Cannot send messages between ' . display_name($from) . ' and ' . display_name($to));
    }
}
/**
 * can a user send a message to another?
 *
 * @param int/object from the user to send the message
 * @param int/object to the user to receive the message
 * @return boolean whether userfrom is allowed to send messages to userto
 */
function can_send_message($from, $to) {
    if (empty($from)) {
        return false; // not logged in
    }
	if (!is_object($from)) {
	    $from = get_record('usr', 'id', $from);
	}
	if (is_object($to)) {
	    $to = $to->id;
	}
    $messagepref = get_account_preference($to, 'messages');
    return (is_friend($from->id, $to) && $messagepref == 'friends') || $messagepref == 'allow' || $from->admin;
}

function load_user_institutions($userid) {
    if (!is_numeric($userid) || $userid < 0) {
        throw new InvalidArgumentException("couldn't load institutions, no user id specified");
    }
    if ($institutions = get_records_sql_assoc('
        SELECT u.institution,'.db_format_tsfield('ctime').','.db_format_tsfield('u.expiry', 'membership_expiry').',u.studentid,u.staff,u.admin,i.theme,i.registerallowed
        FROM {usr_institution} u INNER JOIN {institution} i ON u.institution = i.name
        WHERE u.usr = ? ORDER BY i.priority DESC', array($userid))) {
        return $institutions;
    }
    return array();
}


/**
 * Return a username which isn't taken and which is similar to a desired username
 * 
 * @param string $desired
 */
function get_new_username($desired) {
    $maxlen = 30;
    $desired = strtolower(substr($desired, 0, $maxlen));
    $taken = get_column_sql('
        SELECT username FROM {usr}
        WHERE username ' . db_ilike() . " ?",
        array(substr($desired, 0, $maxlen - 6) . '%'));
    if (!$taken) {
        return $desired;
    }
    $taken = array_flip($taken);
    $i = '';
    $newname = substr($desired, 0, $maxlen - 1) . $i;
    while (isset($taken[$newname])) {
        $i++;
        $newname = substr($desired, 0, $maxlen - strlen($i)) . $i;
    }
    return $newname;
}

/**
 * used by user/myfriends.php and user/find.php to get the data (including pieforms etc) for display
 * @param array $userids
 * @return array containing the users in the order from $userids
 */
function get_users_data($userids, $getviews=true) {
	global $USER;
    // $userids is only used by build_userlist_html() in this file and is sanitised there
    $userlist = join(',', $userids);
    $sql = 'SELECT u.id, u.username, u.preferredname, u.firstname, u.lastname, u.admin, u.staff, u.deleted,
                u.profileicon, u.email,
                0 AS pending, ap.value AS hidenamepref,
                COALESCE((SELECT ap.value FROM {usr_account_preference} ap WHERE ap.usr = u.id AND ap.field = \'messages\'), \'allow\') AS messages,
                COALESCE((SELECT ap.value FROM {usr_account_preference} ap WHERE ap.usr = u.id AND ap.field = \'friendscontrol\'), \'auth\') AS friendscontrol,
                (SELECT 1 FROM {usr_friend} WHERE ((usr1 = ? AND usr2 = u.id) OR (usr2 = ? AND usr1 = u.id))) AS friend,
                (SELECT 1 FROM {usr_friend_request} fr WHERE fr.requester = ? AND fr.owner = u.id) AS requestedfriendship,
                (SELECT title FROM {artefact} WHERE artefacttype = \'introduction\' AND owner = u.id) AS introduction,
                NULL AS message
                FROM {usr} u
                LEFT JOIN {usr_account_preference} ap ON (u.id = ap.usr AND ap.field = \'hiderealname\')
                WHERE u.id IN (' . $userlist . ')
            UNION
            SELECT u.id, u.username, u.preferredname, u.firstname, u.lastname, u.admin, u.staff, u.deleted,
                u.profileicon, u.email,
                1 AS pending, ap.value AS hidenamepref,
                COALESCE((SELECT ap.value FROM {usr_account_preference} ap WHERE ap.usr = u.id AND ap.field = \'messages\'), \'allow\') AS messages,
                NULL AS friendscontrol,
                NULL AS friend,
                NULL AS requestedfriendship,
                (SELECT title FROM {artefact} WHERE artefacttype = \'introduction\' AND owner = u.id) AS introduction,
                message
                FROM {usr} u
                LEFT JOIN {usr_account_preference} ap ON (u.id = ap.usr AND ap.field = \'hiderealname\')
                JOIN {usr_friend_request} fr ON fr.requester = u.id
                WHERE fr.owner = ?
                AND u.id IN (' . $userlist . ')';
    $userid = $USER->get('id');
    $data = get_records_sql_assoc($sql, array($userid, $userid, $userid, $userid));
    $allowhidename = get_config('userscanhiderealnames');
    $showusername = get_config('searchusernames');

    $institutionstrings = get_institution_strings_for_users($userids);
    foreach ($data as &$record) {
        $record->messages = ($record->messages == 'allow' || $record->friend && $record->messages == 'friends' || $USER->get('admin')) ? 1 : 0;
        if (isset($institutionstrings[$record->id])) {
            $record->institutions = $institutionstrings[$record->id];
        }
        $record->display_name = display_name($record, null, false, !$allowhidename || !$record->hidenamepref, $showusername);
    }

    if (!$data || !$getviews || !$views = get_views(array_keys($data), null, null)) {
        $views = array();
    }

    if ($getviews) {
        $viewcount = array_map('count', $views);
        // since php is so special and inconsistent, we can't use array_map for this because it breaks the top level indexes.
        $cleanviews = array();
        foreach ($views as $userindex => $viewarray) {
            $cleanviews[$userindex] = array_slice($viewarray, 0, 5);

            // Don't reveal any more about the view than necessary
            foreach ($cleanviews as $userviews) {
                foreach ($userviews as &$view) {
                    foreach (array_keys(get_object_vars($view)) as $key) {
                        if ($key != 'id' && $key != 'title') {
                            unset($view->$key);
                        }
                    }
                }
            }
        }
    }

    foreach ($data as $friend) {
        if ($getviews && isset($cleanviews[$friend->id])) {
            $friend->views = $cleanviews[$friend->id];
        }
        if ($friend->pending) {
            $friend->accept = pieform(array(
                'name' => 'acceptfriend' . $friend->id,
                'successcallback' => 'acceptfriend_submit',
                'renderer' => 'div',
                'autofocus' => 'false',
                'elements' => array(
                    'submit' => array(
                        'type' => 'submit',
                        'value' => get_string('approverequest', 'group')
                    ),
                    'id' => array(
                        'type' => 'hidden',
                        'value' => $friend->id
                    )
                )
            ));
        }
        if (!$friend->friend && !$friend->pending && !$friend->requestedfriendship && $friend->friendscontrol == 'auto') {
            $friend->makefriend = pieform(array(
                'name' => 'addfriend' . $friend->id,
                'successcallback' => 'addfriend_submit',
                'renderer' => 'div',
                'autofocus' => 'false',
                'elements' => array(
                    'submit' => array(
                        'type' => 'submit',
                        'value' => get_string('addtofriendslist', 'group'),
                    ),
                    'id' => array(
                        'type' => 'hidden',
                        'value' => $friend->id,
                    ),
                    // These two fields pass on any query that was running on a 
                    // user search screen. This is so when the form is 
                    // submitted, the correct user search is run again and so 
                    // this pieform will definitely be created and ready to be 
                    // submitted.
                    //
                    // A bit of a hack caused by having one form for each user. 
                    // It would be nice at some point to put the entire 'find 
                    // friends' page into one form and toggle on the submit 
                    // button to work out which friend to add.
                    'query' => array(
                        'type' => 'hidden',
                        'value' => param_variable('query', ''),
                    ),
                    'offset' => array(
                        'type' => 'hidden',
                        'value' => param_integer('offset', 0),
                    ),
                )
            ));
        }
    }
    $order = explode(',', $userlist);
    $ordereddata = array();
    foreach ($order as $id) {
        if (isset($data[$id])) {
            $ordereddata[] = $data[$id];
        }
    }
    return $ordereddata;
}

function build_userlist_html(&$data, $page, $admingroups) {
    if ($data['data']) {
        $userlist = array_map(create_function('$u','return (int)$u[\'id\'];'), $data['data']);
        $userdata = get_users_data($userlist, $page == 'myfriends');
    }
    $smarty = smarty_core();
    $smarty->assign('data', isset($userdata) ? $userdata : null);
    $smarty->assign('page', $page);
    if (isset($data['query'])) {
        $smarty->assign('query', $data['query']);
        $params = '?query=' . $data['query'];
        $resultcounttextsingular = get_string('user', 'group');
        $resultcounttextplural = get_string('users', 'group');
    }
    elseif (isset($data['filter'])) {
        $smarty->assign('filter', $data['filter']);
        $params = '?filter=' . $data['filter'];
        $resultcounttextsingular = get_string('friend', 'group');
        $resultcounttextplural = get_string('friends', 'group');
    }
    $smarty->assign('admingroups', $admingroups);
    $data['tablerows'] = $smarty->fetch('user/userresults.tpl');
    $pagination = build_pagination(array(
        'id' => 'friendslist_pagination',
        'url' => get_config('wwwroot') . 'user/' . $page . '.php' . $params,
        'jsonscript' => 'json/friendsearch.php',
        'datatable' => 'friendslist',
        'count' => $data['count'],
        'limit' => $data['limit'],
        'offset' => $data['offset'],
        'resultcounttextsingular' => $resultcounttextsingular,
        'resultcounttextplural' => $resultcounttextplural,
        'extradata' => array('page' => $page),
    ));
    $data['pagination'] = $pagination['html'];
    $data['pagination_js'] = $pagination['javascript'];
}


function get_institution_strings_for_users($userids) {
    $userlist = join(',', $userids);
    if (!$records = get_records_sql_array('
        SELECT ui.usr, i.displayname
        FROM {usr_institution} ui JOIN {institution} i ON ui.institution = i.name
        WHERE ui.usr IN (' . $userlist . ')', array())) {
        return array();
    }
    $institutions = array();
    foreach ($records as &$ui) {
        if (!isset($institutions[$ui->usr])) {
            $institutions[$ui->usr] = array();
        }
        $institutions[$ui->usr][] = $ui->displayname;
    }
    foreach ($institutions as &$u) {
        $u = get_string('memberofinstitutions', 'mahara', join(', ', $u));
    }
    return $institutions;
}

function get_institution_string_for_user($userid) {
    $strings = get_institution_strings_for_users(array($userid));
    if (empty($strings[$userid])) {
        return '';
    }
    return $strings[$userid];
}

function friends_control_sideblock($returnto='myfriends') {
    global $USER;
    $form = array(
        'name' => 'friendscontrol',
        'plugintype'  => 'core',
        'pluginname'  => 'account',
        'autofocus'   => false,
        'elements' => array(
            'friendscontrol' => array(
                'type' => 'radio',
                'defaultvalue' => $USER->get_account_preference('friendscontrol'),
                'separator' => '<br>',
                'options' => array(
                    'nobody' => get_string('friendsnobody', 'account'),
                    'auth'   => get_string('friendsauth', 'account'),
                    'auto'   => get_string('friendsauto', 'account')
                ),
                'rules' => array(
                    'required' => true
                ),
            ),
            'submit' => array(
                'type' => 'submit',
                'value' => get_string('save')
            ),
            'returnto' => array(
                'type' => 'hidden',
                'value' => $returnto
            )
        )
    );
    // Make a sideblock to put the friendscontrol block in
    return array(
        'name' => 'friendscontrol',
        'weight' => -5,
        'data' => array('form' => pieform($form))
    );
}

function friendscontrol_submit(Pieform $form, $values) {
    global $USER, $SESSION;
    $USER->set_account_preference('friendscontrol', $values['friendscontrol']);
    $SESSION->add_ok_msg(get_string('updatedfriendcontrolsetting', 'account'));
    redirect($values['returnto'] == 'find' ? '/user/find.php' : '/user/myfriends.php');
}

function acceptfriend_submit(Pieform $form, $values) {
    global $USER, $SESSION;

    $user = get_record('usr', 'id', $values['id']);

    // friend db record
    $f = new StdClass;
    $f->ctime = db_format_timestamp(time());
    $f->usr1 = $user->id;
    $f->usr2 = $USER->get('id');

    // notification info
    $n = new StdClass;
    $n->url = get_config('wwwroot') . 'user/view.php?id=' . $USER->get('id');
    $n->users = array($user->id);
    $lang = get_user_language($user->id);
    $displayname = display_name($USER, $user);
    $n->message = get_string_from_language($lang, 'friendrequestacceptedmessage', 'group', $displayname, $displayname);
    $n->subject = get_string_from_language($lang, 'friendrequestacceptedsubject', 'group');

    db_begin();
    delete_records('usr_friend_request', 'owner', $USER->get('id'), 'requester', $user->id);
    insert_record('usr_friend', $f);

    db_commit();

    handle_event('addfriend', array('user' => $f->usr2, 'friend' => $f->usr1));

    $SESSION->add_ok_msg(get_string('friendformacceptsuccess', 'group'));
    redirect('/user/view.php?id=' . $values['id']);
}

// Called when a user adds someone who has friendscontrol set to 'auto'
function addfriend_submit(Pieform $form, $values) {
    global $USER, $SESSION;
    $user = get_record('usr', 'id', $values['id']);

    $loggedinid = $USER->get('id');
    $userid = $user->id;

    // friend db record
    $f = new StdClass;
    $f->ctime = db_format_timestamp(time());

    // notification info
    $n = new StdClass;
    $n->url = get_config('wwwroot') . 'user/view.php?id=' . $loggedinid;
    $n->users = array($user->id);
    $lang = get_user_language($user->id);
    $displayname = display_name($USER, $user);
    $n->urltext = $displayname;

    $f->usr1 = $values['id'];
    $f->usr2 = $loggedinid;
    insert_record('usr_friend', $f);
    $n->subject = get_string_from_language($lang, 'addedtofriendslistsubject', 'group', $displayname);
    $n->message = get_string_from_language($lang, 'addedtofriendslistmessage', 'group', $displayname, $displayname);

    require_once('activity.php');
    activity_occurred('maharamessage', $n);

    handle_event('addfriend', array('user' => $f->usr2, 'friend' => $f->usr1));

    $SESSION->add_ok_msg(get_string('friendformaddsuccess', 'group', display_name($user)));
    redirect('/user/view.php?id=' . $values['id']);
}

/**
 * Create user
 *
 * @param object $user stdclass or User object for the usr table
 * @param array  $profile profile field/values to set
 * @param string $institution Institution the user should joined to
 * @param stdclass $remoteauth authinstance record for a remote authinstance
 * @param string $remotename username on the remote site
 * @return integer id of the new user
 */
function create_user($user, $profile=array(), $institution=null, $remoteauth=null, $remotename=null) {
    db_begin();

    if ($user instanceof User) {
        $user->create();
        $user->quota_init();
        $user->commit();
        $user = $user->to_stdclass();
    }
    else {
        $user->ctime = db_format_timestamp(time());
        if (empty($user->quota)) {
            $user->quota = get_config_plugin('artefact', 'file', 'defaultquota');
        }
        $user->id = insert_record('usr', $user, 'id', true);
    }
    // Bypass access check for 'copynewuser' institution/site views, because this user may not be logged in yet
    $user->newuser = true;

    if (isset($user->email) && $user->email != '') {
        set_profile_field($user->id, 'email', $user->email);
    }
    if (isset($user->firstname) && $user->firstname != '') {
        set_profile_field($user->id, 'firstname', $user->firstname);
    }
    if (isset($user->lastname) && $user->lastname != '') {
        set_profile_field($user->id, 'lastname', $user->lastname);
    }
    foreach ($profile as $k => $v) {
        if (in_array($k, array('firstname', 'lastname', 'email'))) {
            continue;
        }
        set_profile_field($user->id, $k, $v);
    }

    if (!empty($institution) && $institution != 'mahara') {
        if (is_string($institution)) {
            $institution = new Institution($institution);
        }
        if ($institution->name != 'mahara') {
            $institution->addUserAsMember($user); // uses $user->newuser
        }
    }

    if (!empty($remoteauth)) {
        if (isset($remotename) && strlen($remotename) > 0) {
            $un = $remotename;
        }
        else {
            $un = $user->username;
        }
        delete_records('auth_remote_user', 'authinstance', $user->authinstance, 'remoteusername', $un);
        insert_record('auth_remote_user', (object) array(
            'authinstance'   => $user->authinstance,
            'remoteusername' => $un,
            'localusr'       => $user->id,
        ));
    }

    // Copy site views to the new user's profile
    $checkviewaccess = !$user->newuser;
    $userobj = new User();
    $userobj->find_by_id($user->id);
    $userobj->copy_views(get_column('view', 'id', 'institution', 'mahara', 'copynewuser', 1), $checkviewaccess);

    handle_event('createuser', $user);
    db_commit();
    return $user->id;
}


/**
 * Given a user, makes sure they have been added to all groups that are marked 
 * as ones that users should be auto-added to
 *
 * @param array $eventdata Event data passed from activity_occured, the key 'id' = userid
 */
function add_user_to_autoadd_groups($eventdata) {
    require_once('group.php');
    $userid = $eventdata['id'];
    if ($autoaddgroups = get_column('group', 'id', 'usersautoadded', true)) {
        foreach ($autoaddgroups as $groupid) {
            if (!group_user_access($groupid, $userid)) {
                group_add_user($groupid, $userid);
            }
        }
    }
}


/**
 * This function installs the site's default profile view
 *
 * @throws SystemException if the system profile view is already installed
 */
function install_system_profile_view() {
    $viewid = get_field('view', 'id', 'owner', 0, 'type', 'profile');
    if ($viewid) {
        throw new SystemException('A system profile view already seems to be installed');
    }
    require_once(get_config('libroot') . 'view.php');
    require_once(get_config('docroot') . 'blocktype/lib.php');
    $view = View::create(array(
        'type'        => 'profile',
        'owner'       => 0,
        'numcolumns'  => 2,
        'ownerformat' => FORMAT_NAME_PREFERREDNAME,
        'title'       => get_string('profileviewtitle', 'view'),
        'description' => get_string('profiledescription'),
        'template'    => 1,
    ));
    $view->set_access(array(array(
        'type' => 'loggedin'
    )));
    $blocktypes = array('myviews' => 1, 'mygroups' => 1, 'myfriends' => 2, 'wall' => 2);  // column ids
    $installed = get_column_sql('SELECT name FROM {blocktype_installed} WHERE name IN (' . join(',', array_map('db_quote', array_keys($blocktypes))) . ')');
    $weights = array(1 => 0, 2 => 0);
    foreach (array_keys($blocktypes) as $blocktype) {
        if (in_array($blocktype, $installed)) {
            $weights[$blocktypes[$blocktype]]++;
            $newblock = new BlockInstance(0, array(
                'blocktype'  => $blocktype,
                'title'      => get_string('title', 'blocktype.' . $blocktype),
                'view'       => $view->get('id'),
                'column'     => $blocktypes[$blocktype],
                'order'      => $weights[$blocktypes[$blocktype]],
            ));
            $newblock->commit();
        }
    }
    return $view->get('id');
}

/**
 * This function installs the site's default dashboard view
 *
 * @throws SystemException if the system dashboard view is already installed
 */
function install_system_dashboard_view() {
    $viewid = get_field('view', 'id', 'owner', 0, 'type', 'dashboard');
    if ($viewid) {
        throw new SystemException('A system dashboard view already seems to be installed');
    }
    require_once(get_config('libroot') . 'view.php');
    require_once(get_config('docroot') . 'blocktype/lib.php');
    $view = View::create(array(
        'type'        => 'dashboard',
        'owner'       => 0,
        'numcolumns'  => 2,
        'ownerformat' => FORMAT_NAME_PREFERREDNAME,
        'title'       => get_string('dashboardviewtitle', 'view'),
        'template'    => 1,
    ));
    $view->set_access(array(array(
        'type' => 'loggedin'
    )));
    $blocktypes = array(
        array(
            'blocktype' => 'newviews',
            'title' => '',
            'column' => 1,
            'config' => array(
                'limit' => 5,
            ),
        ),
        array(
            'blocktype' => 'myviews',
            'title' => '',
            'column' => 1,
            'config' => null,
        ),
        array(
            'blocktype' => 'inbox',
            'title' => '',
            'column' => 2,
            'config' => array(
                'feedback' => true,
                'groupmessage' => true,
                'institutionmessage' => true,
                'maharamessage' => true,
                'usermessage' => true,
                'viewaccess' => true,
                'watchlist' => true,
                'maxitems' => '5',
            ),
        ),
        array(
            'blocktype' => 'inbox',
            'title' => '',
            'column' => 2,
            'config' => array(
                'newpost' => true,
                'maxitems' => '5',
            ),
        ),
    );
    $installed = get_column_sql('SELECT name FROM {blocktype_installed}');
    $weights = array(1 => 0, 2 => 0);
    foreach ($blocktypes as $blocktype) {
        if (in_array($blocktype['blocktype'], $installed)) {
            $weights[$blocktype['column']]++;
            $newblock = new BlockInstance(0, array(
                'blocktype'  => $blocktype['blocktype'],
                'title'      => $blocktype['title'],
                'view'       => $view->get('id'),
                'column'     => $blocktype['column'],
                'order'      => $weights[$blocktype['column']],
                'configdata' => $blocktype['config'],
            ));
            $newblock->commit();
        }
    }
    return $view->get('id');
}


/**
 * Return profile icon url for a user.  Use this to quickly get a url
 * when you already have a bunch of user records with profileicon id &
 * email address.
 *
 * Avoids reloading the 'no user photo' image for each user separately
 * when we know they have no profile icon, and avoids the redirect to
 * gravatar.
 */
function profile_icon_url($user, $maxwidth=40, $maxheight=40) {
    global $USER, $THEME;
    static $usercache   = array();

    if (is_array($user)) {
        $user = (object)$user;
    }
    else if (is_numeric($user)) {
        if (isset($usercache[$user])) {
            $user = $usercache[$user];
        }
        else if ($user == $USER->get('id')) {
            $user = new StdClass;
            $user->id            = $USER->get('id');
            $user->profileicon   = $USER->get('profileicon');
            $user->email         = $USER->get('email');
            $usercache[$user->id] = $user;
        }
        else {
            $user = $usercache[$user] = get_record('usr', 'id', $user);
        }
    }
    if (!property_exists($user, 'profileicon') || !property_exists($user, 'email')) {
        throw new SystemException('profile_icon_url requires a user with profileicon & email properties');
    }
    if ($maxwidth != $maxheight || ($maxwidth != 20 && $maxwidth != 40 && $maxwidth != 60)) {
        log_warn('profile_icon_url should specify maxwidth, maxheight of 20x20, 40x40 or 60x60');
    }

    $thumb = get_config('wwwroot') . 'thumb.php';
    $sizeparams = 'maxwidth=' . $maxwidth . '&maxheight=' . $maxheight;

    if (!empty($user->profileicon)) {
        return $thumb . '?type=profileiconbyid&' . $sizeparams . '&id=' . $user->profileicon;
    }

    // Assume we have the right size available in docroot, so we don't
    // have to call thumb.php
    $notfound = $THEME->get_url('images/no_userphoto' . $maxwidth . '.png');

    if (get_config('remoteavatars')) {
        return remote_avatar($user->email, array('maxw' => $maxwidth, 'maxh' => $maxheight), $notfound);
    }
    return $notfound;
}

/**
 * Return a Gravatar URL if one exists for the given user.
 *
 * @param string  $email         Email address of the user
 * @param object  $size          Maximum size of the image
 * @param boolean $notfound
 *
 * @returns string The URL of the image or FALSE if none was found
 */
function remote_avatar($email, $size, $notfound) {
    if (!get_config('remoteavatars')) {
        return false;
    }
    require_once('file.php');

    $md5sum = md5(strtolower($email));

    $s = 100;
    $newsize = image_get_new_dimensions($s, $s, $size);
    if ($newsize) {
        $s = min($newsize['w'], $newsize['h']);
    }

    $baseurl = 'http://www.gravatar.com/avatar/';
    if (stripos(get_config('wwwroot'), 'https://') === 0) {
        $baseurl = 'https://secure.gravatar.com/avatar/';
    }
    if (get_config('remoteavatarbaseurl')) {
        $baseurl = get_config('remoteavatarbaseurl');
    }
    return "{$baseurl}{$md5sum}.jpg?r=g&s=$s&d=" . urlencode($notfound);
}

/**
 * Get user records for a user's friends
 *
 * @param integer $userid        id of the user whose friends we're after
 * @param integer $limit
 * @param integer $offset
 *
 * @returns array Total number of friends, along with $limit or fewer user records.
 */
function get_friends($userid, $limit=10, $offset=0) {
    $result = array('count' => 0, 'limit' => $limit, 'offset' => $offset, 'data' => false);

    $from = 'FROM (
            SELECT u.id, u.username, u.firstname, u.lastname, u.preferredname, u.email, u.admin, u.staff, u.profileicon
            FROM {usr} u JOIN {usr_friend} f ON u.id = f.usr1
            WHERE f.usr2 = ? AND u.deleted = 0
            UNION
            SELECT u.id, u.username, u.firstname, u.lastname, u.preferredname, u.email, u.admin, u.staff, u.profileicon
            FROM {usr} u JOIN {usr_friend} f ON u.id = f.usr2
            WHERE f.usr1 = ? AND u.deleted = 0
        ) f';

    $values = array($userid, $userid);

    if (!$result['count'] = count_records_sql('SELECT COUNT(*) ' . $from, $values)) {
        return $result;
    }

    $sql = '
        SELECT f.* ' . $from . "
        ORDER BY CASE WHEN NOT f.preferredname IS NULL AND f.preferredname <> '' THEN f.preferredname ELSE f.firstname || f.lastname END";

    if ($limit === false) {
        $result['data'] = get_records_sql_array($sql, $values);
    }
    else {
        $result['data'] = get_records_sql_array($sql, $values, $offset, $limit);
    }

    return $result;
}
