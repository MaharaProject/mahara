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
    if ($field == 'mobileuploadtoken'
        && ((!isset($value) || empty($value))
            || (is_array($value) && count($value) == 1 && (!isset($value[0]) || empty($value[0]))))) {
        $value = '';
    }
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
    set_field_select('collection_tag', 'tag', get_string_from_language($newlang, 'profile'), 'WHERE tag = ? AND "collection" IN (SELECT id FROM {collection} WHERE "owner" = ?)', array(get_string_from_language($oldlang, 'profile'), $userid));
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

        // Check for an institution language
        $instlang = get_user_institution_language($userid);
        if (!empty($instlang) && $instlang != 'default') {
            return $instlang;
        }

        // Use the site language
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
                 'licensedefault' => '',
                 'messages'       => 'allow',
                 'lang'           => 'default',
                 'addremovecolumns' => 0,
                 'maildisabled'   => 0,
                 'tagssideblockmaxtags' => get_config('tagssideblockmaxtags'),
                 'groupsideblockmaxgroups' => '',
                 'groupsideblocksortby' => 'alphabetical',
                 'hiderealname'   => 0,
                 'multipleblogs' => get_config('defaultmultipleblogs'),
                 'showhomeinfo' => 1,
                 'showprogressbar' => 1,
                 'mobileuploadtoken' => '',
                 'theme' => '',
                 'resizeonuploaduserdefault' => 1,
                 'devicedetection' => 1,
                 'licensedefault' => '',
                 'viewsperpage' => 20,
                 'orderpagesby' => 'atoz',
                 );
}

function general_account_prefs_form_elements($prefs) {
    global $USER;
    require_once('license.php');
    $elements = array();
    $elements['friendscontrol'] = array(
        'type' => 'radio',
        'defaultvalue' => $prefs->friendscontrol,
        'title'  => get_string('friendsdescr', 'account'),
        'separator' => '<br>',
        'options' => array(
            'nobody' => get_string('friendsnobody', 'account'),
            'auth'   => get_string('friendsauth', 'account'),
            'auto'   => get_string('friendsauto', 'account')
        ),
        'help' => true
    );
    $elements['wysiwyg'] = array(
        'type' => 'switchbox',
        'defaultvalue' => (get_config('wysiwyg')) ? get_config('wysiwyg') == 'enable' : $prefs->wysiwyg,
        'title' => get_string('wysiwygdescr', 'account'),
        'help' => true,
        'disabled' => get_config('wysiwyg'),
    );
    if (get_config('licensemetadata')) {
        $elements['licensedefault'] = license_form_el_basic(null);
        $elements['licensedefault']['title'] = get_string('licensedefault','account');
        if ($USER->get('institutions')) {
            $elements['licensedefault']['options'][LICENSE_INSTITUTION_DEFAULT] = get_string('licensedefaultinherit','account');
        }
        $elements['licensedefault']['description'] = get_string('licensedefaultdescription','account');
        if (isset($prefs->licensedefault)) {
            $elements['licensedefault']['defaultvalue'] = $prefs->licensedefault;
        }
    }
    $elements['maildisabled'] = array(
        'type' => 'switchbox',
        'defaultvalue' => $prefs->maildisabled,
        'title' => get_string('disableemail', 'account'),
        'help' => true,
    );
    $elements['messages'] = array(
        'type' => 'radio',
        'defaultvalue' => $prefs->messages,
        'title' => get_string('messagesdescr', 'account'),
        'separator' => '<br>',
        'options' => array(
            'nobody' => get_string('messagesnobody', 'account'),
            'friends' => get_string('messagesfriends', 'account'),
            'allow' => get_string('messagesallow', 'account'),
        ),
        'help' => true,
    );
    $languages = get_languages();
    // Determine default language.
    $instlang = get_user_institution_language($USER->id, $instlanginstname);
    if (!empty($instlang) && $instlang != 'default') {
        $sitedefaultlabel = get_string('defaultlangforinstitution', 'admin', get_config_institution($instlanginstname, 'displayname')) . ' (' . $languages[$instlang] . ')';
    }
    else {
        $sitedefaultlabel = get_string('sitedefault', 'admin') . ' (' . $languages[get_config('lang')] . ')';
    }
    $elements['lang'] = array(
        'type' => 'select',
        'defaultvalue' => $prefs->lang,
        'title' => get_string('language', 'account'),
        'options' => array_merge(array('default' => $sitedefaultlabel), $languages),
        'help' => true,
        'ignore' => count($languages) < 2,
    );
    $sitethemes = array();
    // Get all available standard site themes
    if (get_config('sitethemeprefs') && !in_admin_section()) {
        // get_user_accessible_themes() returns 'sitedefault' to mean fall back to the site or
        // institution theme.  This won't work for account prefs, where 'sitedefault' is just
        // a theme that doesn't exist.  So change the 'sitedefault' key to '', and the empty
        // preference will be interpreted as "No theme selected".
        $sitethemes = array_reverse(get_user_accessible_themes());
        unset($sitethemes['sitedefault']);
        $sitethemes = array_reverse($sitethemes);
    }
    // Get all user's institution themes
    $institutionthemes = array();
    global $USER;
    if ($institutions = $USER->get('institutions')) {
        $allthemes = get_all_theme_objects();
        foreach ($institutions as $i) {
            if (empty($i->theme)) {
                $institutionthemes['sitedefault' . '/' . $i->institution] = $i->displayname . ' - ' . get_string('sitedefault', 'admin');
            }
            else {
                $institutionthemes[$i->theme . '/' . $i->institution] = $i->displayname . ' - ' . $allthemes[$i->theme]->displayname;
            }
        }
    }
    $themes = array_merge($sitethemes, $institutionthemes);
    natcasesort($themes);
    $currenttheme = $USER->get_themedata();
    if (!isset($currenttheme->basename)) {
        $defaulttheme = 'sitedefault';
    }
    else {
        $defaulttheme = $currenttheme->basename;
    }
    if (isset($currenttheme->institutionname)) {
        $defaulttheme = $defaulttheme . '/' . $currenttheme->institutionname;
    }
    if (!array_key_exists($defaulttheme, $themes)) {
        reset($themes);
        $defaulttheme = key($themes);
    }
    $elements['theme'] = array(
    'type' => 'select',
    'defaultvalue' => $defaulttheme,
    'title' => get_string('theme'),
    'options' => $themes,
    'ignore' => count($themes) < 2,
    'help' => true,
    );

    $elements['addremovecolumns'] = array(
        'type' => 'switchbox',
        'defaultvalue' => $prefs->addremovecolumns,
        'title' => get_string('showviewcolumns', 'account'),
        'help' => 'true'
    );
    // TODO: add a way for plugins (like blog!) to have account preferences
    $elements['multipleblogs'] = array(
        'type' => 'switchbox',
        'title'=> get_string('enablemultipleblogs1' ,'account'),
        'description' => get_string('enablemultipleblogsdescription1', 'account'),
        'defaultvalue' => $prefs->multipleblogs,
    );
    if (get_config('showtagssideblock')) {
        $elements['tagssideblockmaxtags'] = array(
            'type'         => 'text',
            'size'         => 4,
            'title'        => get_string('tagssideblockmaxtags', 'account'),
            'description'  => get_string('tagssideblockmaxtagsdescription', 'account'),
            'defaultvalue' => isset($prefs->tagssideblockmaxtags) ? $prefs->tagssideblockmaxtags : get_config('tagssideblockmaxtags'),
            'rules'        => array('integer' => true, 'minvalue' => 0, 'maxvalue' => 1000),
        );
    }
    $elements['groupsideblockmaxgroups'] = array(
        'type'         => 'text',
        'size'         => 4,
        'title'        => get_string('limitto1', 'blocktype.mygroups'),
        'description'  => get_string('limittodescsideblock1', 'blocktype.mygroups'),
        'defaultvalue' => isset($prefs->groupsideblockmaxgroups) ? $prefs->groupsideblockmaxgroups : '',
        'rules'        => array('regex' => '/^[0-9]*$/', 'minvalue' => 0, 'maxvalue' => 1000),
    );
    $elements['groupsideblocksortby'] = array(
        'type'         => 'select',
        'defaultvalue' => isset($prefs->groupsideblocksortby) ? $prefs->groupsideblocksortby : 'alphabetical',
        'title' => get_string('sortgroups', 'blocktype.mygroups'),
        'options' =>  array(
            'latest' => get_string('latest', 'blocktype.mygroups'),
            'earliest' => get_string('earliest', 'blocktype.mygroups'),
            'alphabetical'  => get_string('alphabetical', 'blocktype.mygroups'),
        ),
    );
    if (get_config('userscanhiderealnames')) {
        $elements['hiderealname'] = array(
            'type'         => 'switchbox',
            'title'        => get_string('hiderealname', 'account'),
            'description'  => get_string('hiderealnamedescription', 'account'),
            'defaultvalue' => $prefs->hiderealname,
        );
    }
    if (get_config('homepageinfo')) {
        $elements['showhomeinfo'] = array(
            'type' => 'switchbox',
            'defaultvalue' => $prefs->showhomeinfo,
            'title' => get_string('showhomeinfo2', 'account'),
            'description' => get_string('showhomeinfodescription1', 'account', hsc(get_config('sitename'))),
            'help' => 'true'
        );
    }
    if (get_config('showprogressbar')) {
        $elements['showprogressbar'] = array(
            'type' => 'switchbox',
            'defaultvalue' => $prefs->showprogressbar,
            'title' => get_string('showprogressbar', 'account'),
            'description' => get_string('showprogressbardescription', 'account', hsc(get_config('sitename'))),
        );
    }
    if (get_config('allowmobileuploads')) {
        $defaultvalue = array();
        $mobileuploadtoken = isset($prefs->mobileuploadtoken) ? $prefs->mobileuploadtoken : get_config('mobileuploadtoken');
        $defaultvalue = explode('|', trim($mobileuploadtoken, '|'));

        $elements['mobileuploadtoken'] = array(
            'type'         => 'multitext',
            'title'        => get_string('mobileuploadtoken', 'account'),
            'defaultvalue' => $defaultvalue,
            'help'         => 'true'
        );
    }
    if (get_config_plugin('artefact', 'file', 'resizeonuploadenable')) {
        $elements['resizeonuploaduserdefault'] = array(
            'type'         => 'switchbox',
            'title'        => get_string('resizeonuploaduserdefault1', 'account'),
            'description'  => get_string('resizeonuploaduserdefaultdescription2', 'account'),
            'defaultvalue' => $prefs->resizeonuploaduserdefault,
        );
    }

    if (get_config('userscandisabledevicedetection')) {
        $elements['devicedetection'] = array(
            'type'         => 'switchbox',
            'title'        => get_string('devicedetection', 'account'),
            'description'  => get_string('devicedetectiondescription', 'account'),
            'defaultvalue' => $prefs->devicedetection,
        );
    }

    return $elements;
}

/**
 * Get account settings elements from plugins.
 *
 * @param stdClass $prefs
 * @return array
 */
function plugin_account_prefs_form_elements(stdClass $prefs) {
    $elements = array();
    $installed = plugin_all_installed();
    foreach ($installed as $i) {
        if (!safe_require_plugin($i->plugintype, $i->name)) {
            continue;
        }
        $elements = array_merge($elements, call_static_method(generate_class_name($i->plugintype, $i->name),
                'get_accountprefs_elements', $prefs));
    }
    return $elements;
}

/**
 * Validate plugin account form values.
 *
 * @param Pieform $form
 * @param array $values
 */
function plugin_account_prefs_validate(Pieform $form, $values) {
    $elements = array();
    $installed = plugin_all_installed();
    foreach ($installed as $i) {
        if (!safe_require_plugin($i->plugintype, $i->name)) {
            continue;
        }
        call_static_method(generate_class_name($i->plugintype, $i->name), 'accountprefs_validate', $form, $values);
    }
}

/**
 * Submit plugin account form values.
 *
 * @param Pieform $form
 * @param array $values
 * @return bool is page need to be refreshed
 */
function plugin_account_prefs_submit(Pieform $form, $values) {
    $reload = false;
    $elements = array();
    $installed = plugin_all_installed();
    foreach ($installed as $i) {
        if (!safe_require_plugin($i->plugintype, $i->name)) {
            continue;
        }
        $reload = call_static_method(generate_class_name($i->plugintype, $i->name), 'accountprefs_submit', $form, $values) || $reload;
    }
    return $reload;
}

/**
 * Save a profile field.
 * Exception is 'socialprofile' field. It is made up of 3 fields:
 * socialprofile_profileurl,
 * socialprofile_service,
 * socialprofile_profiletype
 * @param int $userid
 * @param string $field
 * @param string (or array for socialprofile) $value
 * @param int $new - Whether the user is new (avoid unnecessary queries)
 */
function set_profile_field($userid, $field, $value, $new = FALSE) {
    safe_require('artefact', 'internal');

    // this is a special case that replaces the primary email address with the
    // specified one
    if ($field == 'email') {
        if (!$new) {
            try {
                $email = artefact_instance_from_type('email', $userid);
            }
            catch (ArtefactNotFoundException $e) {
                // We'll create a new artefact then.
            }
        }
        if (!isset($email)) {
            $email = new ArtefactTypeEmail(0, null, TRUE);
            $email->set('owner', $userid);
        }
        $email->set('title', $value);
        $email->commit();
    }
    else if ($field == 'socialprofile') {
        $classname = generate_artefact_class_name($field);
        $profile = new $classname(0, array('owner' => $userid), $new);
        $profile->set('title',       $value['socialprofile_profileurl']);
        $profile->set('description', $value['socialprofile_service']);
        $profile->set('note',        $value['socialprofile_profiletype']);
        $profile->commit();
    }
    else {
        $classname = generate_artefact_class_name($field);
        $profile = new $classname(0, array('owner' => $userid), $new);
        $profile->set('title', $value);
        $profile->commit();
    }
}

/**
 * Return the value of a profile field for a given user
 *
 * @param integer user id to find the profile field for
 * @param field what profile field you want the value for
 * @returns string for non-socialprofile fields - the value of the profile field.
 *          array for socialprofile fields - array of the values of the profile fields ('id-<id>'|'<description>|'<title>').
 *          null if it doesn't exist
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
    else if ($field == 'socialprofile') {
        // First check if the block is enabled.
        if (get_record('blocktype_installed', 'active', 1, 'name', 'socialprofile')) {
            // The user can have more than one social profiles. Will need to return an array.
            safe_require('artefact', 'internal');
            $value = ArtefactTypeSocialprofile::get_social_profiles();
        }
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


    require_once('phpmailer/PHPMailerAutoload.php');

    $mail = new PHPMailer(true);

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
            // To prevent duplicated declaration of the field "Message-ID",
            // don't add it into the $mail->CustomHeader[].
            if (false === stripos($customheader, 'message-id')) {
                // Hack the fields "In-Reply-To" and "References":
                // add touser<userID>
                if ((0 === stripos($customheader, 'in-reply-to')) ||
                    (0 === stripos($customheader, 'references'))) {
                    $customheader = preg_replace('/<forumpost(\d+)/', '<forumpost${1}touser' . $userto->id, $customheader);
                }
                $mail->AddCustomHeader($customheader);
            }
            else {
                list($h, $msgid) = explode(':', $customheader, 2);
                // Hack the "Message-ID": add touser<userID> to make sure
                // the "Message-ID" is unique
                $msgid = preg_replace('/<forumpost(\d+)/', '<forumpost${1}touser' . $userto->id, $msgid);
                $mail->MessageID = trim($msgid);
            }
            if (0 === stripos($customheader, 'reply-to')) {
                $replytoset = true;
            }
        }
    }

    $mail->Subject = substr(stripslashes($subject), 0, 900);

    try {
        if ($to = get_config('sendallemailto')) {
            // Admins can configure the system to send all email to a given address
            // instead of whoever would receive it, useful for debugging.
            $usertoname = display_name($userto, $userto, true) . ' (' . get_string('divertingemailto', 'mahara', $to) . ')';
            $mail->addAddress($to);
            $notice = get_string('debugemail', 'mahara', display_name($userto, $userto), $userto->email);
            $messagetext =  $notice . "\n\n" . $messagetext;
            if ($messagehtml) {
                $messagehtml = '<p>' . hsc($notice) . '</p>' . $messagehtml;
            }
        }
        else {
            $usertoname = display_name($userto, $userto);
            $mail->AddAddress($userto->email, $usertoname );
            $to = $userto->email;
        }
        if (!$replytoset) {
            $mail->AddReplyTo($mail->From, $mail->FromName);
        }
    }
    catch (phpmailerException $e) {
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

    try {
        $sent = $mail->Send();
    }
    catch (phpmailerException $e) {
        $sent = false;
    }

    if ($sent) {
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
    // Postfix and other smtp servers don't like the use of / in the extension part of an email
    // Replace it with another valid email character that isn't in base64, like '-'
    $args = $type . preg_replace('/\//', '-', base64_encode(pack('V', $userid))) . substr(md5($userto->email), 0, 16);
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

    $minbounces = get_config('bounces_min');
    $bounceratio = get_config('bounces_ratio');
    // If they haven't set a minbounces value, then we can't proceed
    if (!$minbounces) {
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

    $mailprefix = get_config('bounceprefix');
    $prefixlength = strlen($mailprefix);

    list($email->localpart,$email->domain) = explode('@',$address);
    // The prefix is stored in the first characters denoted by $prefixlength
    $email->prefix        = substr($email->localpart, 0, $prefixlength);
    // The type of message received is a one letter code
    $email->type          = substr($email->localpart, $prefixlength, 1);
    // The userid should be available immediately afterwards
    // Postfix and other smtp servers don't like the use of / in the extension part of an email
    // We may of replaced it with another valid email character which isn't in base64, namely '-'
    // If we didn't, then the preg_replace won't do anything
    list(,$email->userid) = unpack('V',base64_decode(preg_replace('/-/', '/', substr($email->localpart, $prefixlength + 1, 8))));
    // Any additional arguments
    $email->args          = substr($email->localpart, $prefixlength + 9,-16);
    // And a hash of the intended recipient for authentication
    $email->addresshash   = substr($email->localpart,-16);

    if (!$email->userid) {
        log_debug('-- no userid associated with this email address');
        return $email;
    }

    switch ($email->type) {
    case 'B': // E-mail bounces
        if ($user = get_record_select('artefact_internal_profile_email', '"owner" = ? AND principal = 1', array($email->userid))) {
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
 * Check an imap mailbox for new mailbounces
 */
function check_imap_for_bounces() {
    $imapserver = get_config('imapserver');
    if (!$imapserver) {
        return;
    }
    if (!extension_loaded('imap')) {
        log_debug('php imap extension not loaded, can\'t continue');
        return;
    }
    $imapport = get_config('imapport');
    $imap = imap_open("{" . $imapserver . ($imapport ? ':' . $imapport : '') . get_config('imapflags') . '}' . get_config('imapmailbox'),
                        get_config('imapuser'), get_config('imappass'));
    $check = imap_check($imap);
    if ($check->Nmsgs == 0) {
        imap_close($imap);
        return;
    }
    $emails = imap_fetch_overview($imap, "1:" . $check->Nmsgs);
    foreach ($emails as $email) {
        if ($email->deleted) {
            continue;
        }
        $address = $email->to;
        log_debug('---------- started  processing email at ' . date('r', time()) . ' ----------');
        log_debug('-- mail from ' . $address . ' -- delivered ' . $email->date);

        $ret = process_email($address);

        log_debug('---------- finished processing email at ' . date('r', time()) . ' ----------');

        imap_delete($imap, $email->msgno);
    }
    imap_expunge($imap);
    imap_close($imap);
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

    if ($nameonly) {
        return display_default_name($user);
    }

    $nousernames = get_config('nousernames');
    $userto = get_user_for_display($userto);
    $user   = get_user_for_display($user);

    $addusername = ($username && empty($nousernames)) || !empty($userto->admin) || !empty($userto->staff) || $userto === $user;

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
                JOIN {group} g ON (g.id = s.group AND g.deleted = 0 AND g.submittableto = 1)
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
    $user = get_user_for_display($user);
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
 * Takes an array, object or integer identifying a user and returns an object with
 * the properties needed for display_name, display_default_name, or profile_icon_url.
 */
function get_user_for_display($user=null) {
    global $USER;
    static $usercache = array();

    $fields = array(
        'id', 'username', 'preferredname', 'firstname', 'lastname', 'admin', 'staff',
        'profileicon', 'email', 'deleted', 'urlid', 'suspendedctime',
    );

    if (is_numeric($user) && isset($usercache[$user])) {
        return $usercache[$user];
    }

    if (is_array($user)) {
        $user = (object)$user;
    }
    else if (is_null($user) || (is_numeric($user) && $user == $USER->get('id'))) {
        $user = new StdClass;
        foreach ($fields as $f) {
            $user->$f = $USER->get($f);
        }
        $user->admin = $user->admin || $USER->is_institutional_admin();
        $user->staff = $user->staff || $USER->is_institutional_staff();
    }
    else if ($user instanceof User) {
        $userObj = $user;
        $user = new StdClass;
        foreach ($fields as $f) {
            $user->$f = $userObj->get($f);
        }
    }
    else if (is_numeric($user)) {
        $user = get_record('usr', 'id', $user);
    }

    if (!is_object($user)) {
        throw new InvalidArgumentException("Invalid user passed to get_user_for_display");
    }

    if (!isset($user->id)) {
        $user->id = null;
    }

    if (is_numeric($user->id)) {
        if (!isset($usercache[$user->id])) {
            return $usercache[$user->id] = $user;
        }
        foreach ($fields as $f) {
            if (!isset($usercache[$user->id]->$f) && isset($user->$f)) {
                $usercache[$user->id]->$f = $user->$f;
            }
        }
        return $usercache[$user->id];
    }

    return $user;
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
        '*, ' . db_format_tsfield('expiry') . ', ' . db_format_tsfield('lastlogin') .
        ', ' . db_format_tsfield('lastlastlogin') . ', ' . db_format_tsfield('lastaccess') .
        ', ' . db_format_tsfield('suspendedctime') . ', ' . db_format_tsfield('ctime'))) {
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
    $datasuffix = '.deleted.' . microtime(true);

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

    // Free the urlid for another user to use
    $deleterec->urlid = null;

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

    // Delete the user from others' favourites lists
    delete_records('favorite_usr', 'usr', $userid);
    // Delete favourites lists owned by the user
    execute_sql('DELETE FROM {favorite_usr} WHERE favorite IN (SELECT id FROM {favorite} WHERE owner = ?)', array($userid));
    delete_records('favorite', 'owner', $userid);

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
                $a = artefact_instance_from_id($artefactid, true);
                if ($a) {
                    $a->delete();
                }
            }
            catch (ArtefactNotFoundException $e) {
                // Awesome, it's already gone.
            }
        }
    }

    // Remove the user's collections
    $collectionids = get_column('collection', 'id', 'owner', $userid);
    if ($collectionids) {
        require_once(get_config('libroot') . 'collection.php');
        foreach ($collectionids as $collectionid) {
            $collection = new Collection($collectionid);
            $collection->delete();
        }
    }

    handle_event('deleteuser', $userid);

    // Destroy all active sessions of the deleted user
    require_once(get_config('docroot') . 'auth/session.php');
    remove_user_sessions($userid);

    db_commit();
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
    $lifetime = get_config('defaultaccountlifetime');

    $now = time();
    $dbnow = db_format_timestamp($now);

    $values = array($dbnow, $userid, $dbnow);

    if ($lifetime) {
        $newexpiry = '?';
        array_unshift($values, db_format_timestamp($now + $lifetime));
    }
    else {
        $newexpiry = 'NULL';
    }

    // Update the lastaccess time here to stop users who are currently
    // inactive from expiring again on the next cron run.  We can leave
    // inactivemailsent turned on until the user logs in again.

    execute_sql("
        UPDATE {usr} SET expiry = $newexpiry, expirymailsent = 0, lastaccess = ?
        WHERE id = ? AND expiry IS NOT NULL AND expiry < ?",
        $values
    );

    handle_event('unexpireuser', $userid);
}

/**
 * Marks a user as inactive
 *
 * Sets the account expiry to the current time to disable login.
 *
 * This function is called when a user account is detected to be inactive.
 * It is assumed that the account actually is inactive.
 *
 * @param int $userid The ID of user to mark inactive
 */
function deactivate_user($userid) {
    execute_sql('
        UPDATE {usr} SET expiry = current_timestamp
        WHERE id = ? AND (expiry IS NULL OR expiry > current_timestamp)',
        array($userid)
    );
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
        SELECT u.institution,'.db_format_tsfield('ctime').','.db_format_tsfield('u.expiry', 'membership_expiry').',u.studentid,u.staff,u.admin,i.displayname,i.theme,i.registerallowed, i.showonlineusers,i.allowinstitutionpublicviews, i.logo, i.style, i.licensemandatory, i.licensedefault, i.dropdownmenu, i.skins, i.suspended
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
    if (function_exists('mb_strtolower')) {
        $desired = mb_strtolower(mb_substr($desired, 0, $maxlen, 'UTF-8'), 'UTF-8');
    }
    else {
        $desired = strtolower(substr($desired, 0, $maxlen));
    }
    if (function_exists('mb_substr')) {
        $taken = get_column_sql('
            SELECT LOWER(username) FROM {usr}
            WHERE username ' . db_ilike() . " ?",
            array(mb_substr($desired, 0, $maxlen - 6, 'UTF-8')  . '%'));
    }
    else {
        $taken = get_column_sql('
            SELECT LOWER(username) FROM {usr}
            WHERE username ' . db_ilike() . " ?",
            array(substr($desired, 0, $maxlen - 6) . '%'));
    }
    if (!$taken) {
        return $desired;
    }
    $taken = array_flip($taken);
    $i = '';
    if (function_exists('mb_substr')) {
        $newname = mb_substr($desired, 0, $maxlen - 1, 'UTF-8') . $i;
    }
    else {
        $newname = substr($desired, 0, $maxlen - 1) . $i;
    }
    while (isset($taken[$newname])) {
        $i++;
        if (function_exists('mb_substr')) {
            $newname = mb_substr($desired, 0, $maxlen - strlen($i), 'UTF-8') . $i;
        }
        else {
            $newname = substr($desired, 0, $maxlen - strlen($i)) . $i;
        }
    }
    return $newname;
}

/**
 * Get a unique profile urlid
 *
 * @param string $desired
 */
function get_new_profile_urlid($desired) {
    $maxlen = 30;
    $desired = strtolower(substr($desired, 0, $maxlen));
    $taken = get_column_sql('SELECT urlid FROM {usr} WHERE urlid LIKE ?', array(substr($desired, 0, $maxlen - 6) . '%'));
    if (!$taken) {
        return $desired;
    }

    $i = 1;
    $newname = substr($desired, 0, $maxlen - 2) . '-1';
    while (in_array($newname, $taken)) {
        $i++;
        $newname = substr($desired, 0, $maxlen - strlen($i) - 1) . '-' . $i;
    }
    return $newname;
}

/**
 * Get the profile url for a user
 *
 * @param object $user
 * @param boolean $full return a full url
 * @param boolean $useid Override the cleanurls setting and use a view id in the link
 *
 * @return string
 */
function profile_url($user, $full=true, $useid=false) {
    $wantclean = !$useid && get_config('cleanurls');

    if ($user instanceof User) {
        $id = $user->get('id');
        $urlid = $wantclean ? $user->get('urlid') : null;
    }
    else if (is_array($user)) {
        $id = $user['id'];
        $urlid = $user['urlid'];
    }
    else if (is_numeric($user)) {
        $id = $user;
        $user = get_user_for_display($id);
        $urlid = ($wantclean && !empty($user->urlid)) ? $user->urlid : null;
    }
    else if (isset($user->id)) {
        $id = $user->id;
        $urlid = isset($user->urlid) ? $user->urlid : null;
    }

    if ($wantclean && !is_null($urlid)) {
        // If the host part of the url is not being returned, the user subdomain
        // can't be added here, so ignore the subdomain setting when !$full.
        if ($full && get_config('cleanurlusersubdomains')) {
            list($proto, $rest) = explode('://', get_config('wwwroot'));
            return $proto . '://' . $urlid . '.' . substr($rest, 0, -1);
        }
        $url = get_config('cleanurluserdefault') . '/' . $urlid;
    }
    else if (!is_null($id)) {
        $url = 'user/view.php?id=' . (int) $id;
    }
    else {
        throw new SystemException("profile_url called with no user id");
    }

    if ($full) {
        $url = get_config('wwwroot') . $url;
    }

    return $url;
}

/**
 * used by user/myfriends.php and user/find.php to get the data (including pieforms etc) for display
 * @param array $userids
 * @return array containing the users in the order from $userids
 */
function get_users_data($userids, $getviews=true) {
    global $USER;

    $userids = array_map('intval', $userids);

    $sql = 'SELECT u.id, u.username, u.preferredname, u.firstname, u.lastname, u.admin, u.staff, u.deleted,
                u.profileicon, u.email, u.urlid,
                fp.requester AS pending,
                fp.ctime AS pending_time,
                ap.value AS hidenamepref,
                COALESCE((SELECT ap.value FROM {usr_account_preference} ap WHERE ap.usr = u.id AND ap.field = \'messages\'), \'allow\') AS messages,
                COALESCE((SELECT ap.value FROM {usr_account_preference} ap WHERE ap.usr = u.id AND ap.field = \'friendscontrol\'), \'auth\') AS friendscontrol,
                (SELECT 1 FROM {usr_friend} WHERE ((usr1 = ? AND usr2 = u.id) OR (usr2 = ? AND usr1 = u.id))) AS friend,
                (SELECT 1 FROM {usr_friend_request} fr WHERE fr.requester = ? AND fr.owner = u.id) AS requestedfriendship,
                (SELECT title FROM {artefact} WHERE artefacttype = \'introduction\' AND owner = u.id) AS introduction,
                fp.message
                FROM {usr} u
                LEFT JOIN {usr_account_preference} ap ON (u.id = ap.usr AND ap.field = \'hiderealname\')
                LEFT JOIN {usr_friend_request} fp ON fp.owner = ? AND fp.requester = u.id
                WHERE u.id IN (' . join(',', array_fill(0, count($userids), '?')) . ')';
    $userid = $USER->get('id');
    $data = get_records_sql_assoc($sql, array_merge(array($userid, $userid, $userid, $userid), $userids));
    $allowhidename = get_config('userscanhiderealnames');
    $showusername = !get_config('nousernames');

    $institutionstrings = get_institution_strings_for_users($userids);
    foreach ($data as &$record) {
        $record->pending_time = format_date(strtotime($record->pending_time), 'strftimedaydate');
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
                        if ($key != 'id' && $key != 'title' && $key != 'url' && $key != 'fullurl') {
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
            $friend->accept = acceptfriend_form($friend->id);
        }
        if (!$friend->friend && !$friend->pending && !$friend->requestedfriendship && $friend->friendscontrol == 'auto') {
            $friend->makefriend = addfriend_form($friend->id);
        }
    }

    $ordereddata = array();
    foreach ($userids as $id) {
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
    $smarty->assign('offset', $data['offset']);

    $params = array();
    if (isset($data['query'])) {
        $smarty->assign('query', 1);
        $params['query'] = $data['query'];
    }
    if (isset($data['filter'])) {
        $params['filter'] = $data['filter'];
    }

    if ($page == 'myfriends') {
        $resultcounttextsingular = get_string('friend', 'group');
        $resultcounttextplural = get_string('friends', 'group');
    }
    else {
        $resultcounttextsingular = get_string('user', 'group');
        $resultcounttextplural = get_string('users', 'group');
    }

    $smarty->assign('admingroups', $admingroups);
    $data['tablerows'] = $smarty->fetch('user/userresults.tpl');
    $pagination = build_pagination(array(
        'id' => 'friendslist_pagination',
        'url' => get_config('wwwroot') . 'user/' . $page . '.php?' . http_build_query($params),
        'jsonscript' => 'json/friendsearch.php',
        'datatable' => 'friendslist',
        'searchresultsheading' => 'searchresultsheading',
        'count' => $data['count'],
        'setlimit' => true,
        'limit' => $data['limit'],
        'offset' => $data['offset'],
        'jumplinks' => 6,
        'numbersincludeprevnext' => 2,
        'resultcounttextsingular' => $resultcounttextsingular,
        'resultcounttextplural' => $resultcounttextplural,
        'extradata' => array('page' => $page),
    ));
    $data['pagination'] = $pagination['html'];
    $data['pagination_js'] = $pagination['javascript'];
}

function build_onlinelist_html(&$data, $page) {
    if ($data['data']) {
        $userdata = get_users_data($data['data'], false);
    }
    $smarty = smarty_core();
    $smarty->assign('data', isset($userdata) ? $userdata : null);
    $smarty->assign('page', $page);
    $resultcounttextsingular = get_string('user', 'group');
    $resultcounttextplural = get_string('users', 'group');
    $data['tablerows'] = $smarty->fetch('user/onlineuserresults.tpl');
    $pagination = build_pagination(array(
        'id' => 'onlinelist_pagination',
        'url' => get_config('wwwroot') . 'user/' . $page . '.php',
        'datatable' => 'onlinelist',
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

/**
 * Build the html for a list of staff information
 *
 * @param object $data
 * @param string $page
 * @param string $listtype
 * @param string $institution
 */
function build_stafflist_html(&$data, $page, $listtype, $inst='mahara') {
    global $USER;
    if ($data) {
        $data = get_users_data($data, false);
    }
    $smarty = smarty_core();
    $smarty->assign('page', $page);
    $smarty->assign('listtype', $listtype);
    $smarty->assign('inst', $inst);
    $smarty->assign('USER', $USER);
    if (count($data) > 5) {
        $split = ceil(count($data) / 2);
        $columns = array_chunk($data, $split);
    }
    if (isset($columns) && count($columns) == 2) {
        $smarty->assign('columnleft', $columns[0]);
        $smarty->assign('columnright', $columns[1]);
    }
    else {
        $smarty->assign('data', isset($data) ? $data : null);
    }
    $data['tablerows'] = $smarty->fetch('institution/stafflist.tpl');
}

function get_institution_strings_for_users($userids) {
    $userlist = join(',', $userids);
    if (!$records = get_records_sql_array('
        SELECT ui.usr, i.displayname, ui.staff, ui.admin, i.name
        FROM {usr_institution} ui JOIN {institution} i ON ui.institution = i.name
        WHERE ui.usr IN (' . $userlist . ')', array())) {
        return array();
    }
    $institutions = array();
    foreach ($records as &$ui) {
        if (!isset($institutions[$ui->usr])) {
            $institutions[$ui->usr] = array();
        }
        $key = ($ui->admin ? 'admin' : ($ui->staff ? 'staff' : 'member'));
        $institutions[$ui->usr][$key][$ui->name] = $ui->displayname;
    }
    foreach ($institutions as &$userinst) {
        foreach ($userinst as $key => &$value) {
            $links = array();
            foreach ($value as $k => $v) {
                $url = get_config('wwwroot').'institution/index.php?institution='.$k;
                $links[] = get_string('institutionlink', 'mahara', $url, hsc($v));
            }
            switch ($key) {
                case 'admin':
                    $value = get_string('adminofinstitutions', 'mahara', join(', ', $links));
                    break;
                case 'staff':
                    $value = get_string('staffofinstitutions', 'mahara', join(', ', $links));
                    break;
                default:
                    $value = get_string('memberofinstitutions', 'mahara', join(', ', $links));
                    break;
            }
        }
    }
    foreach ($institutions as &$userinst) {
        $userinst = join('. ',$userinst);
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

function acceptfriend_form($friendid) {
    return pieform(array(
        'name' => 'acceptfriend' . (int) $friendid,
        'validatecallback' => 'acceptfriend_validate',
        'successcallback'  => 'acceptfriend_submit',
        'renderer' => 'oneline',
        'class' => 'oneline inline',
        'autofocus' => 'false',
        'elements' => array(
            'acceptfriend_submit' => array(
                'type' => 'submit',
                'value' => get_string('approverequest', 'group'),
            ),
            'id' => array(
                'type' => 'hidden',
                'value' => (int) $friendid,
            ),
        ),
    ));
}

function acceptfriend_validate(Pieform $form, $values) {
    global $USER, $SESSION;

    $friendid = (int) $values['id'];

    if (!get_record('usr_friend_request', 'owner', $USER->get('id'), 'requester', $friendid)) {
        if (!is_friend($USER->get('id'), $friendid) && get_account_preference($friendid, 'friendscontrol') != 'auto') {
            // Because the request is no longer valid, this form won't be redrawn in the new list of users.  So
            // the error message is added to the $SESSION messages area, and a dummy error message is set on the
            // form to stop submission from continuing.
            $SESSION->add_error_msg(get_string('acceptfriendshiprequestfailed', 'group'));
            $form->set_error(null, -1);
        }
    }
}

function acceptfriend_submit(Pieform $form, $values) {
    global $USER, $SESSION;

    $user = get_record('usr', 'id', $values['id']);

    if (is_friend($USER->get('id'), $user->id)) {
        $SESSION->add_info_msg(get_string('alreadyfriends', 'group', display_name($user)));
        delete_records('usr_friend_request', 'owner', $USER->get('id'), 'requester', $user->id);
        redirect(profile_url($user));
    }

    // friend db record
    $f = new StdClass;
    $f->ctime = db_format_timestamp(time());
    $f->usr1 = $user->id;
    $f->usr2 = $USER->get('id');

    // notification info
    $n = new StdClass;
    $n->url = profile_url($USER);
    $n->users = array($user->id);
    $n->fromuser = $USER->get('id');
    $lang = get_user_language($user->id);
    $displayname = display_name($USER, $user);
    $n->message = get_string_from_language($lang, 'friendrequestacceptedmessage', 'group', $displayname, $displayname);
    $n->subject = get_string_from_language($lang, 'friendrequestacceptedsubject', 'group');

    db_begin();
    delete_records('usr_friend_request', 'owner', $USER->get('id'), 'requester', $user->id);
    insert_record('usr_friend', $f);

    db_commit();

    require_once('activity.php');
    activity_occurred('maharamessage', $n);

    handle_event('addfriend', array('user' => $f->usr2, 'friend' => $f->usr1));

    $SESSION->add_ok_msg(get_string('friendformacceptsuccess', 'group'));
    redirect(profile_url($user));
}

// Form to add someone who has friendscontrol set to 'auto'
function addfriend_form($friendid) {
    return pieform(array(
        'name' => 'addfriend' . (int) $friendid,
        'validatecallback' => 'addfriend_validate',
        'successcallback'  => 'addfriend_submit',
        'renderer' => 'div',
        'autofocus' => 'false',
        'elements' => array(
            'addfriend_submit' => array(
                'type' => 'submit',
                'value' => get_string('addtofriendslist', 'group'),
            ),
            'id' => array(
                'type' => 'hidden',
                'value' => (int) $friendid,
            ),
        ),
    ));
}

function addfriend_validate(Pieform $form, $values) {
    global $USER, $SESSION;

    $friendid = (int) $values['id'];

    if (get_account_preference($friendid, 'friendscontrol') != 'auto') {
        if (!is_friend($USER->get('id'), $friendid) && !get_record('usr_friend_request', 'owner', $USER->get('id'), 'requester', $friendid)) {
            // Because friendscontrol has changed, this form won't be redrawn in the new list of users.  So
            // the error message is added to the $SESSION messages area, and a dummy error message is set on
            // the form to stop submission from continuing.
            $SESSION->add_error_msg(get_string('addtofriendsfailed', 'group', display_name($friendid)));
            $form->set_error(null, -1);
        }
    }
}

// Called when a user adds someone who has friendscontrol set to 'auto'
function addfriend_submit(Pieform $form, $values) {
    global $USER, $SESSION;
    $user = get_record('usr', 'id', $values['id']);

    $loggedinid = $USER->get('id');

    if (is_friend($loggedinid, $user->id)) {
        $SESSION->add_info_msg(get_string('alreadyfriends', 'group', display_name($user)));
        delete_records('usr_friend_request', 'owner', $loggedinid, 'requester', $user->id);
        redirect(profile_url($user));
    }

    // friend db record
    $f = new StdClass;
    $f->ctime = db_format_timestamp(time());

    // notification info
    $n = new StdClass;
    $n->url = profile_url($USER, false);
    $n->users = array($user->id);
    $lang = get_user_language($user->id);
    $displayname = display_name($USER, $user);
    $n->urltext = $displayname;

    $f->usr1 = $values['id'];
    $f->usr2 = $loggedinid;

    db_begin();
    delete_records('usr_friend_request', 'owner', $loggedinid, 'requester', $user->id);
    insert_record('usr_friend', $f);
    db_commit();

    $n->subject = get_string_from_language($lang, 'addedtofriendslistsubject', 'group', $displayname);
    $n->message = get_string_from_language($lang, 'addedtofriendslistmessage', 'group', $displayname, $displayname);

    require_once('activity.php');
    activity_occurred('maharamessage', $n);

    handle_event('addfriend', array('user' => $f->usr2, 'friend' => $f->usr1));

    $SESSION->add_ok_msg(get_string('friendformaddsuccess', 'group', display_name($user)));
    redirect(profile_url($user));
}

/**
 * Create user
 *
 * @param object $user stdclass or User object for the usr table
 * @param array  $profile profile field/values to set
 * @param string|object $institution Institution the user should joined to (name or Institution object)
 * @param bool $remoteauth authinstance record for a remote authinstance
 * @param string $remotename username on the remote site
 * @param array $accountprefs user account preferences to set
 * @return integer id of the new user
 */
function create_user($user, $profile=array(), $institution=null, $remoteauth=null, $remotename=null, $accountprefs=array(), $quickhash=false) {
    db_begin();

    if ($user instanceof User) {
        $user->create();
        $user->quota_init();
        $user->commit();
        $user = $user->to_stdclass();
    }
    else {
        $user->ctime = db_format_timestamp(time());
        // Ensure this user has a profile urlid
        if (get_config('cleanurls') && (!isset($user->urlid) || is_null($user->urlid))) {
            $user->urlid = generate_urlid($user->username, get_config('cleanurluserdefault'), 3, 30);
            $user->urlid = get_new_profile_urlid($user->urlid);
        }
        if (empty($user->quota)) {
            $user->quota = get_config_plugin('artefact', 'file', 'defaultquota');
        }
        if (get_config('defaultaccountlifetime')) {
            // we need to set the user expiry to the site default one
            $user->expiry = date('Y-m-d',mktime(0, 0, 0, date('m'), date('d'), date('Y')) + (int)get_config('defaultaccountlifetime'));
        }
        $user->id = insert_record('usr', $user, 'id', true);
    }

    if (isset($user->email) && $user->email != '') {
        set_profile_field($user->id, 'email', $user->email, TRUE);
    }
    if (isset($user->firstname) && $user->firstname != '') {
        set_profile_field($user->id, 'firstname', $user->firstname, TRUE);
    }
    if (isset($user->lastname) && $user->lastname != '') {
        set_profile_field($user->id, 'lastname', $user->lastname, TRUE);
    }
    foreach ($profile as $k => $v) {
        if (in_array($k, array('firstname', 'lastname', 'email'))) {
            continue;
        }
        set_profile_field($user->id, $k, $v, TRUE);
    }

    if (!empty($institution)) {
        if (is_string($institution)) {
            $institution = new Institution($institution);
        }
        if ($institution->name != 'mahara') {
            $institution->addUserAsMember($user); // uses $user->newuser
            if (empty($accountprefs['licensedefault'])) {
                $accountprefs['licensedefault'] = LICENSE_INSTITUTION_DEFAULT;
            }
        }
    }
    $authobj = get_record('auth_instance', 'id', $user->authinstance);
    $authinstance = AuthFactory::create($authobj->id);
    // For legacy compatibility purposes, we'll also put the remote auth on there if it has been
    // specifically requested.
    if ($authinstance->needs_remote_username() || (!empty($remoteauth))) {
        if (isset($remotename) && strlen($remotename) > 0) {
            $un = $remotename;
        }
        else {
            $un = $user->username;
        }
        // remote username must not already exist
        if (record_exists('auth_remote_user', 'remoteusername', $un, 'authinstance', $user->authinstance)) {
            throw new InvalidArgumentException("user_create: remoteusername already exists: ({$un}, {$user->authinstance})");
        }
        insert_record('auth_remote_user', (object) array(
            'authinstance'   => $user->authinstance,
            'remoteusername' => $un,
            'localusr'       => $user->id,
        ));
    }

    // Set account preferences
    if (!empty($accountprefs)) {
        $expectedprefs = expected_account_preferences();
        foreach ($expectedprefs as $eprefkey => $epref) {
            if (isset($accountprefs[$eprefkey]) && $accountprefs[$eprefkey] != $epref) {
                set_account_preference($user->id, $eprefkey, $accountprefs[$eprefkey]);
            }
        }
    }

    // Copy site views and collections to the new user's profile
    $userobj = new User();
    $userobj->find_by_id($user->id);
    $userobj->copy_site_views_collections_to_new_user();

    reset_password($user, false, $quickhash);

    handle_event('createuser', $user);
    db_commit();
    return $user->id;
}

/**
 * Update user
 *
 * @param object $user stdclass for the usr table
 * @param object $profile profile field/values to set
 * @param string $remotename username on the remote site
 * @param array $accountprefs user account preferences to set
 * @param bool $forceupdateremote force delete of remotename before update attempted
 * @return array list of updated fields
 */
function update_user($user, $profile, $remotename=null, $accountprefs=array(), $forceupdateremote=false, $quickhash=false) {
    require_once(get_config('docroot') . 'auth/session.php');

    if (!empty($user->id)) {
        $oldrecord = get_record('usr', 'id', $user->id);
    }
    else {
        $oldrecord = get_record('usr', 'username', $user->username);
    }
    $userid = $oldrecord->id;

    db_begin();

    // Log the user out, otherwise they can overwrite all this on the next request
    remove_user_sessions($userid);

    $updated = array();
    $newrecord = new StdClass;
    foreach (get_object_vars($user) as $k => $v) {
        if (!empty($v) && ($k == 'password' || empty($oldrecord->$k) || $oldrecord->$k != $v)) {
            $newrecord->$k = $v;
            $updated[$k] = $v;
        }
    }

    if (count(get_object_vars($newrecord))) {
        $newrecord->id = $userid;
        update_record('usr', $newrecord);
        if (!empty($newrecord->password)) {
            $newrecord->authinstance = $user->authinstance;
            reset_password($newrecord, false, $quickhash);
        }
    }

    foreach (get_object_vars($profile) as $k => $v) {
        if (get_profile_field($userid, $k) != $v) {
            set_profile_field($userid, $k, $v);
            $updated[$k] = $v;
        }
    }

    if ($remotename) {
        $oldremote = get_field('auth_remote_user', 'remoteusername', 'authinstance', $oldrecord->authinstance, 'localusr', $userid);
        if ($remotename != $oldremote) {
            $updated['remoteuser'] = $remotename;
        }
        delete_records('auth_remote_user', 'authinstance', $user->authinstance, 'localusr', $userid);
        // force the update of the remoteuser - for the case of a series of user updates swapping the remoteuser name
        if ($forceupdateremote) {
            delete_records('auth_remote_user', 'authinstance', $user->authinstance, 'remoteusername', $remotename);
        }
        else {
            // remote username must not already exist
            if (record_exists('auth_remote_user', 'remoteusername', $remotename, 'authinstance', $user->authinstance)) {
                throw new InvalidArgumentException("user_update: remoteusername already in use: ".$remotename);
            }
        }
        insert_record('auth_remote_user', (object) array(
            'authinstance'   => $user->authinstance,
            'remoteusername' => $remotename,
            'localusr'       => $userid,
        ));
    }

    // Update account preferences
    if (!empty($accountprefs)) {
        $expectedprefs = expected_account_preferences();
        foreach ($expectedprefs as $eprefkey => $epref) {
            if (isset($accountprefs[$eprefkey]) && $accountprefs[$eprefkey] != get_account_preference($userid, $eprefkey)) {
                set_account_preference($userid, $eprefkey, $accountprefs[$eprefkey]);
                $updated[$eprefkey] = $accountprefs[$eprefkey];
            }
        }
    }

    db_commit();

    return $updated;
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
        'numrows'     => 1,
        'columnsperrow' => array((object)array('row' => 1, 'columns' => 2)),
        'ownerformat' => FORMAT_NAME_PREFERREDNAME,
        'title'       => get_string('profileviewtitle', 'view'),
        'description' => get_string('profiledescription'),
        'template'    => 1,
    ));
    $view->set_access(array(array(
        'type' => 'loggedin'
    )));
    $blocktypes = array('profileinfo' => 1, 'myviews' => 1, 'mygroups' => 1, 'myfriends' => 2, 'wall' => 2);  // column ids
    $installed = get_column_sql('SELECT name FROM {blocktype_installed} WHERE name IN (' . join(',', array_map('db_quote', array_keys($blocktypes))) . ')');
    $weights = array(1 => 0, 2 => 0);
    foreach (array_keys($blocktypes) as $blocktype) {
        if (in_array($blocktype, $installed)) {
            $weights[$blocktypes[$blocktype]]++;
            $title = ($blocktype == 'profileinfo') ? get_string('aboutme', 'blocktype.internal/profileinfo') : get_string('title', 'blocktype.' . $blocktype);
            $newblock = new BlockInstance(0, array(
                'blocktype'  => $blocktype,
                'title'      => $title,
                'view'       => $view->get('id'),
                'row'        => 1,
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
        'numrows'     => 1,
        'columnsperrow' => array((object)array('row' => 1, 'columns' => 2)),
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
            'row'   => 1,
            'column' => 1,
            'config' => array(
                'limit' => 5,
            ),
        ),
        array(
            'blocktype' => 'myviews',
            'title' => '',
            'row'   => 1,
            'column' => 1,
            'config' => null,
        ),
        array(
            'blocktype' => 'inbox',
            'title' => '',
            'row'   => 1,
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
            'row'   => 1,
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
                'row'        => $blocktype['row'],
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
 *
 * @param int|object|array $user A user ID, user object, or user data array. If an
 * object, should contain profileicon or email attributes, or a user ID.
 * @param int $maxwidth
 * @param int $maxheight
 * @return bool|string The URL of the image or FALSE if none was found
 */
function profile_icon_url($user, $maxwidth=40, $maxheight=40) {

    // Getting icon when feedback is done by anonymous user
    if (empty($user)) {
        return anonymous_icon_url($maxwidth, $maxheight);
    }
    $user = get_user_for_display($user);

    // If we were originally passed a $user that was lacking profileicon and email,
    // get_user_for_display() usually won't have found it for us. So we should try
    // to fill that in now, and then cache it for later calls.
    if (!isset($user->profileicon) && !isset($user->email)) {
        if (!isset($user->id) || !is_numeric($user->id)) {
            // No data. We'll just show the anonymous icon, but log a warning message for the devs.
            log_debug("profile_icon_url was passed a user object without a numeric id, a profileicon, or an email address. This is probably a coding error.");
        }
        else {
            $user = get_record('usr', 'id', $user->id, null, null, null, null, 'id, profileicon, email');
            // Cache this for subsequent calls
            $user = get_user_for_display($user);
        }
    }

    // Available sizes of the 'no_userphoto' image:
    $allowedsizes = array(16, 20, 25, 40, 50, 60, 100);
    if ($maxwidth != $maxheight || !in_array($maxwidth, $allowedsizes)) {
        log_warn('profile_icon_url: maxwidth, maxheight should be equal and in (' . join(', ', $allowedsizes) . ')');
    }

    $thumb = get_config('wwwroot') . 'thumb.php';
    $sizeparams = 'maxwidth=' . $maxwidth . '&maxheight=' . $maxheight;

    if (!empty($user->profileicon)) {
        return $thumb . '?type=profileiconbyid&' . $sizeparams . '&id=' . $user->profileicon;
    }

    return anonymous_icon_url($maxwidth, $maxheight, (!empty($user->email) ? $user->email : null));
}

/**
 * Return icon to show when there is no user profile icon.
 *
 * @param int  $maxwidth       Maximum width of image
 * @param int  $maxheight      Maximum height of image
 * @param string $email        email address to use for remote_avatar, if any.
 */
function anonymous_icon_url($maxwidth=40, $maxheight=40, $email=null) {
    global $THEME;

    // Assume we have the right size available in docroot, so we don't
    // have to call thumb.php
    $notfoundwidth = $maxwidth == 100 ? '' : $maxwidth;
    $notfound = $THEME->get_image_url('no_userphoto' . $notfoundwidth);

    if (!empty($email) && get_config('remoteavatars')) {
        return remote_avatar($email, array('maxw' => $maxwidth, 'maxh' => $maxheight), $notfound);
    }
    return $notfound;
}

/**
 * Return the remote avatar associated to the email.
 * If the avatar does not exist, return anonymous avatar
 *
 * @param string  $email         Email address of the user
 * @param mixed  $size           Size of the image
 * @returns string $url          The remote avatar URL
 */
function remote_avatar_url($email, $size) {
    global $THEME;

    $s = 100;
    $newsize = image_get_new_dimensions($s, $s, $size);
    if ($newsize) {
        $s = min($newsize['w'], $newsize['h']);
    }
    // Available sizes of the 'no_userphoto' image:
    $allowedsizes = array(16, 20, 25, 40, 50, 60, 100);
    if (!in_array($s, $allowedsizes)) {
        log_warn('remote_avatar_url: size should be in (' . join(', ', $allowedsizes) . ')');
    }
    else {
        $s = 40;
    }
    $notfound = $THEME->get_image_url('no_userphoto' . $s);
    if (!empty($email) && get_config('remoteavatars')) {
        return remote_avatar($email, $s, $notfound);
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
    if (is_https() === true) {
        $baseurl = 'https://secure.gravatar.com/avatar/';
    }
    if (get_config('remoteavatarbaseurl')) {
        $baseurl = get_config('remoteavatarbaseurl');
    }
    // Check if it is a valid avatar
    $result = @get_headers("{$baseurl}{$md5sum}.jpg?d=404");
    if (!$result || preg_match("#^HTTP/\d+\.\d+ 404 #i", $result[0])) {
        return $notfound;
    }
    return "{$baseurl}{$md5sum}.jpg?r=g&s=$s";
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
            SELECT u.id, u.username, u.firstname, u.lastname, u.preferredname, u.email, u.admin, u.staff, u.profileicon, u.urlid
            FROM {usr} u JOIN {usr_friend} f ON u.id = f.usr1
            WHERE f.usr2 = ? AND u.deleted = 0
            UNION
            SELECT u.id, u.username, u.firstname, u.lastname, u.preferredname, u.email, u.admin, u.staff, u.profileicon, u.urlid
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

/**
 * Get user records for online users page
 *
 * @param integer $limit
 * @param integer $offset
 *
 * @returns array Total number of users, along with $limit or fewer user records.
 */
function get_onlineusers($limit=10, $offset=0, $orderby='firstname,lastname') {
    global $USER;

    // Determine what level of users to show
    // 0 = none, 1 = institution/s only, 2 = all users
    $showusers = 2;
    $institutions = $USER->institutions;
    if (!empty($institutions)) {
        $showusers = 0;
        foreach ($institutions as $i) {
            if ($i->showonlineusers == 2) {
                $showusers = 2;
                break;
            }
            if ($i->showonlineusers == 1) {
                $showusers = 1;
            }
        }
    }

    $result = array('count' => 0, 'limit' => $limit, 'offset' => $offset, 'data' => false);
    switch ($showusers) {
        case 0: // show none
            return $result;
        case 1: // show institution only
            $sql = "SELECT DISTINCT u.* FROM {usr} u JOIN {usr_institution} i ON id = i.usr
                WHERE deleted = 0 AND lastaccess > ? AND i.institution IN (" . join(',',array_map('db_quote', array_keys($institutions))) . ")
                ORDER BY $orderby";
            $countsql = 'SELECT count(DISTINCT id) FROM {usr} JOIN {usr_institution} i ON id = i.usr
                WHERE deleted = 0 AND lastaccess > ? AND i.institution IN (' . join(',',array_map('db_quote', array_keys($institutions))) . ')';
            break;
        case 2: // show all
            $sql = "SELECT * FROM {usr} WHERE deleted = 0 AND lastaccess > ? ORDER BY $orderby";
            $countsql = 'SELECT count(id) FROM {usr} WHERE deleted = 0 AND lastaccess > ?';
            break;
    }

    $lastaccess = db_format_timestamp(time() - get_config('accessidletimeout'));
    if (!$result['count'] = count_records_sql($countsql, array($lastaccess))) {
        return $result;
    }

    $onlineusers = get_records_sql_array($sql, array($lastaccess), $offset, $limit);
    if ($onlineusers) {
        foreach ($onlineusers as &$user) {
            $user->profileiconurl = profile_icon_url($user, 20, 20);

            // If the user is an MNET user, show where they've come from
            $authobj = AuthFactory::create($user->authinstance);
            if ($authobj->authname == 'xmlrpc') {
                $peer = get_peer($authobj->wwwroot);
                $user->loggedinfrom = $peer->name;
            }
        }
    }
    else {
        $onlineusers = array();
    }
    $result['data'] = array_map(create_function('$a', 'return $a->id;'), $onlineusers);

    return $result;
}

/**
 * Get a list of userids from a list of usernames
 *
 * @param $usernames array list of usernames
 *
 * @returns array list of userids
 */
function username_to_id($usernames) {
    if (!empty($usernames)) {
        $ids = get_records_sql_menu('
            SELECT username, id FROM {usr}
            WHERE deleted = 0
            AND LOWER(username) IN (' . join(',', array_fill(0, count($usernames), '?')) . ')',
            array_map('strtolower', $usernames)
        );
    }
    return empty($ids) ? array() : $ids;
}

/**
 * Update or create a favourites list for a user
 *
 * @param $owner integer owner of the favorites list
 * @param $shortname string name for the favorites list
 * @param $institution string institution with permission to update the favorites list
 * @param $userlist array array of userids to add to the list
 */
function update_favorites($owner, $shortname, $institution, $userlist) {
    global $USER;

    if (empty($institution)) {
        // User-editable favorites lists are not implemented yet.
        return;
    }

    if (!$USER->can_edit_institution($institution)) {
        throw new AccessDeniedException("update_favorites: access denied");
    }

    $owner = (int) $owner;

    if ($institution == 'mahara') {
        if (!record_exists('usr', 'id', $owner, 'deleted', 0)) {
            throw new NotFoundException("update_favorites: user $owner not found");
        }
    }
    else {
        $sql = '
            SELECT u.id
            FROM {usr} u JOIN {usr_institution} ui ON u.id = ui.usr AND ui.institution = ?
            WHERE u.id = ? AND u.deleted = 0';
        if (!record_exists_sql($sql, array($institution, $owner))) {
            throw new NotFoundException("update_favorites: user $owner not found in institution $institution");
        }
    }

    $listdata = get_record('favorite', 'owner', $owner, 'shortname', $shortname);

    if ($listdata && $listdata->institution != $institution) {
        throw new AccessDeniedException("update_favorites: user $owner already has a favorites list called $shortname which is updated by another institution");
    }

    if (!is_array($userlist)) {
        throw new SystemException("update_favorites: userlist is not an array");
    }

    if (!empty($userlist)) {
        $userids = get_column_sql('
            SELECT id FROM {usr} WHERE id IN (' . join(',', array_fill(0, count($userlist), '?')) . ') AND deleted = 0',
            array_map('intval', $userlist)
        );
    }

    if (empty($userids)) {
        $userids = array();
    }

    db_begin();

    $now = db_format_timestamp(time());

    if ($listdata) {
        delete_records('favorite_usr', 'favorite', $listdata->id);
        $listdata->mtime = $now;
        update_record('favorite', $listdata, 'id');
    }
    else {
        $listdata = (object) array(
            'owner'       => $owner,
            'shortname'   => $shortname,
            'institution' => $institution,
            'ctime'       => $now,
            'mtime'       => $now,
        );
        $listdata->id = insert_record('favorite', $listdata, 'id', true);
    }

    foreach ($userids as $userid) {
        insert_record('favorite_usr', (object) array('favorite' => $listdata->id, 'usr' => $userid));
    }

    db_commit();
}

/**
 * Returns a list of a user's favourite users for display, most recently
 * updated users first.
 *
 * @param $userid integer id of a user to get favourites for
 * @param $limit integer
 * @param $offset integer
 *
 * @returns array of stdclass objects containing userids & names
 */
function get_user_favorites($userid, $limit=5, $offset=0) {
    $users = get_records_sql_array('
        SELECT u.id, u.username, u.preferredname, u.firstname, u.lastname
        FROM {usr} u JOIN (
            SELECT fu.usr, MAX(f.mtime) AS mtime
            FROM {favorite_usr} fu JOIN {favorite} f ON fu.favorite = f.id
            WHERE f.owner = ?
            GROUP BY fu.usr
        ) uf ON uf.usr = u.id
        WHERE u.deleted = 0
        ORDER BY uf.mtime DESC, u.preferredname, u.firstname, u.lastname',
        array($userid),
        $offset,
        $limit
    );

    if (empty($users)) {
        return array();
    }

    foreach ($users as &$u) {
        $u->name = display_name($u);
        unset($u->username);
        unset($u->preferredname);
        unset($u->firstname);
        unset($u->lastname);
    }

    return $users;
}
