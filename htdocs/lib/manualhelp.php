<?php
/**
 *
 * @package    mahara
 * @subpackage core
 * @author     Catalyst IT Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL version 3 or later
 * @copyright  For copyright information on Mahara, please see the README file distributed with this software.
 * @copyright  (C) portions from Moodle, (C) Martin Dougiamas http://dougiamas.com
 */

defined('INTERNAL') || die();

/**
 * Create the help link to the manual
 *
 * @param array $keys The array of the keys to generate the link from
 *
 * @return string The HTML link
 */

function get_manual_help_link($keys) {
    $data = get_manual_help_link_array($keys);

    $manuallink = sprintf('<a id="footerhelp" class="nav-link" href="%s/%s/%s/%s">' . get_string('Help') . '</a>',
        $data['prefix'],
        $data['language'],
        $data['version'],
        $data['suffix']
    );
    return $manuallink;
}

/**
 * Get link information in array format.
 * Useful for json data return
 *
 * @param  array $keys An array of keys that indicate what help file we want
 *
 * @return array
 */
function get_manual_help_link_array($keys) {
    if (!is_array($keys)) {
        $keys = (array)$keys;
    }
    $activeurls = get_config('footercustomlinks');
    $activeurls = $activeurls ? unserialize($activeurls) : null;
    if (isset($activeurls['manualhelp']) && !empty($activeurls['manualhelp'])) {
        $prefix = $activeurls['manualhelp'];
    }
    else {
        $prefix = _get_manual_link_prefix();
    }
    $data = array('prefix' => $prefix,
                  'language' => _get_manual_language(),
                  'version' => _get_mahara_version(),
                  'suffix' => _get_manual_help_link_suffix($keys)
    );
    return $data;
}

/**
 * For the given keys finds the most specific manual link.
 *
 * @return string
 */
function _get_manual_help_link_suffix($keys) {
    for ($i = sizeof($keys); $i > 0; $i--) {
        $link = _get_manual_link($i, $keys);
        if ($link != NULL) {
            return $link;
        }
    }
    return "";
}

function _get_manual_link($length, $keys) {
    global $manual_link_map;
    $key = "";
    for ($i = 0; $i < $length; $i++) {
        if ($i > 0) {
            $key .= "|";
        }
        $key .= $keys[$i];
    }
    if (isset($manual_link_map[$key])) {
        return $manual_link_map[$key];
    }
    return "";
}

/**
 * Get the best language for the manual.
 *
 * The best language would be the current users language if the manual exists in
 * that language.
 *
 * The fallback in all other cases in en.
 *
 * @return string
 */
function _get_manual_language() {
    $user_lang = current_language();
    $manual_langs = array("de", "en", "fr", "nl");
    foreach ($manual_langs as $lang) {
        if (strpos($user_lang, $lang) === 0) {
            return $lang;
        }
    }
    return "en";
}

/**
 * Get the current version of Mahara so we can point to the corresponding version of the manual.
 * If current version is master/release candidate we use the most current stable version
 *
 * @return string
 */
function _get_mahara_version() {
    $release = get_config("release");
    $series = get_config("series");
    if (preg_match('/dev$/', $release)) {
        list($year, $month) = explode('.', $series);
        if ($month == '04') {
            $month = '10';
            $year = (int)$year - 1;
        }
        else {
            $month = '04';
        }
        $series = $year . '.' . $month;
    }
    return $series;
}

function _get_manual_link_prefix() {
    return "http://manual.mahara.org";
}

$manual_link_map = array(
    "" => "", // default - means go to manual homepage
    "adminhome|home" => "administration/overview.html#admin-home",
    "adminhome|registersite" => "administration/overview.html#register-your-mahara-site",
    "blocktype|blocks" => "blocks/blocks.html",
    "configextensions|cleanurls" => "administration/extensions.html#clean-urls",
    "configextensions|filters" => "administration/extensions.html#html-filters",
    "configextensions|frameworks" => "administration/smartevidence.html#smartevidence-admin",
    "configextensions|frameworks|frameworkmanager" => "administration/smartevidence.html#smartevidence-admin", // Need to point to new place once created
    "configextensions|iframesites" => "administration/extensions.html#allowed-iframe-sources",
    "configextensions|pluginadmin|plugins" => "administration/extensions.html",
    "configextensions|pluginadmin|pluginconfig|artefact|comment" => "administration/extensions.html#artefact-type-comments",
    "configextensions|pluginadmin|pluginconfig|artefact|file" => "administration/extensions.html#artefact-type-file",
    "configextensions|pluginadmin|pluginconfig|artefact|internal" => "administration/extensions.html#artefact-type-profile",
    "configextensions|pluginadmin|pluginconfig|auth|saml" => "administration/extensions.html#authentication-saml",
    "configextensions|pluginadmin|pluginconfig|blocktype|file/folder" => "administration/extensions.html#blocktype-file-folder",
    "configextensions|pluginadmin|pluginconfig|blocktype|file/gallery" => "administration/extensions.html#blocktype-file-gallery",
    "configextensions|pluginadmin|pluginconfig|blocktype|file/internalmedia" => "administration/extensions.html#blocktype-file-internalmedia",
    "configextensions|pluginadmin|pluginconfig|blocktype|file/text" => "administration/extensions.html#blocktype-text",
    "configextensions|pluginadmin|pluginconfig|blocktype|file/wall" => "administration/extensions.html#blocktype-wall",
    "configextensions|pluginadmin|pluginconfig|interaction|forum" => "administration/extensions.html#interaction-forum",
    "configextensions|pluginadmin|pluginconfig|module|lti" => "administration/extensions.html#module-lti",
    "configextensions|pluginadmin|pluginconfig|module|mobileapi" => "administration/extensions.html#module-mobile-api",
    "configextensions|pluginadmin|pluginconfig|search|elasticsearch" => "administration/extensions.html#search-elasticsearch",
    "configextensions|pluginadmin|pluginconfig|search|internal" => "administration/extensions.html#search-internal",
    "configsite|blogs" => "administration/config_site.html#site-journals",
    "configsite|cookieconsent" => "administration/config_site.html#cookie-consent",
    "configsite|networking" => "administration/config_site.html#networking",
    "configsite|share" => "administration/config_site.html#share",
    "configsite|sitefiles" => "administration/config_site.html#files",
    "configsite|sitefonts" => "administration/config_site.html#fonts",
    "configsite|sitefonts|install" => "administration/config_site.html#install-a-local-font",
    "configsite|sitefonts|installgwf" => "administration/config_site.html#install-google-font-s",
    "configsite|sitelicenses" => "administration/config_site.html#licenses",
    "configsite|sitemenu" => "administration/config_site.html#menus",
    "configsite|siteoptions" => "administration/config_site.html",
    "configsite|sitepages" => "administration/config_site.html#static-pages",
    "configsite|siteskins" => "administration/config_site.html#site-skins",
    "configsite|siteviews" => "administration/config_site.html#site-pages-and-collections",
    "configusers|adduser" => "administration/users.html#add-user",
    "configusers|adminusers" => "administration/users.html#site-administrators",
    "configusers|exportqueue" => "administration/users.html#export-queue",
    "configusers|staffusers" => "administration/users.html#site-staff",
    "configusers|suspendedusers" => "administration/users.html#suspended-and-expired-users",
    "configusers|uploadcsv" => "administration/users.html#add-and-update-users-by-csv",
    "configusers|usersearch" => "administration/users.html#user-search",
    "configusers|usersearch|bulkedit" => "administration/users.html#user-bulk-actions",
    "configusers|usersearch|edit" => "administration/users.html#user-account-settings",
    "create|blogs|index" => "content/journal.html#work-with-multiple-journals",
    "create|blogs|new" => "content/journal.html#change-your-journal-settings",
    "create|blogs|post" => "content/journal.html#add-a-journal-entry",
    "create|blogs|settings" => "content/journal.html#change-your-journal-settings",
    "create|blogs|view" => "content/journal.html",
    "create|files|index" => "content/files.html",
    "create|notes" => "content/notes.html",
    "create|plans|index" => "content/plans.html",
    "create|plans|newplan" => "content/plans.html#create-a-new-plan",
    "create|plans|newtask" => "content/plans.html#add-tasks-to-a-plan",
    "create|plans|plans" => "content/plans.html#view-all-tasks-of-a-plan",
    "create|resume|index|index" => "content/resume.html#introduction",
    "create|resume|index|employment" => "content/resume.html#education-and-employment",
    "create|resume|index|achievements" => "content/resume.html#achievements",
    "create|resume|index|goalsandskills" => "content/resume.html#goals-and-skills",
    "create|resume|index|interests" => "content/resume.html#interests",
    "create|resume|index|license" => "content/resume.html#license",
    "create|skins" => "portfolio/skins.html",
    "create|skins|design" => "portfolio/skins.html#create-a-skin",
    "create|tags" => "portfolio/tags.html",
    "create|views" => "portfolio/pages.html#overview-page",
    "create|views|add" => "portfolio/pages.html#add-a-page",
    "create|views|blocks" => "portfolio/page_editor.html",
    "create|views|versioning" => "portfolio/pages.html#new-in-mahara-18-10-timeline-of-a-page-s-development",
    "engage|people|index" => "groups/find_friends.html#find-people",
    "engage|people|index|current" => "groups/my_friends.html",
    "engage|people|index|pending" => "groups/my_friends.html",
    "engage|people|requestfriendship" => "groups/find_friends.html#send-a-friend-request",
    "engage|people|denyrequest" => "groups/find_friends.html#deny-a-friend-request",
    "engage|people|removefriend" => "groups/my_friends.html#remove-a-friend",
    "engage|institutions" => "groups/institution_membership.html",
    "engage|index" => "groups/my_groups.html",
    "engage|index|blocks" => "groups/inside_group.html#id3",
    "engage|index|edittopic|forums" => "groups/inside_group.html#add-a-forum-topic",
    "engage|index|editpost|forums" => "groups/inside_group.html#reply-to-a-topic-or-subsequent-post",
    "engage|index|forums" => "groups/inside_group.html#set-up-a-new-forum",
    "engage|index|groupviews" => "groups/inside_group.html#pages-and-collections",
    "engage|index|index|canjoin" => "groups/find_group.html",
    "engage|index|index|forums" => "groups/inside_group.html#forums",
    "engage|index|info" => "groups/inside_group.html",
    "engage|index|members" => "groups/inside_group.html#members",
    "engage|index|view|forums" => "groups/inside_group.html#add-a-forum-topic",
    "engage|index|topic|forums" => "groups/inside_group.html#add-a-forum-topic",
    "engage|topics" => "groups/topics.html",
    "forgotpass" => "intro/dashboard.html#login",
    "home" => "intro/dashboard.html#overview",
    "inbox" => "account/notifications.html#send-a-message",
    "inbox|inbox" => "account/notifications.html",
    "inbox|outbox" => "account/notifications.html#sent",
    "loggedouthome" => "intro/dashboard.html",
    "manage|export" => "portfolio/export.html",
    "manage|import" => "portfolio/import.html",
    "managegroups|archives" => "administration/groups.html#archived-submissions",
    "managegroups|categories" => "administration/groups.html#group-categories",
    "managegroups|groups" => "administration/groups.html#administer-groups",
    "managegroups|groups|manage" => "administration/groups.html#group-file-quota",
    "managegroups|uploadcsv" => "administration/groups.html#add-and-update-groups-by-csv",
    "managegroups|uploadmemberscsv" => "administration/groups.html#update-group-members-by-csv",
    "manageinstitutions|adminnotifications" => "administration/institutions.html#admin-notifications",
    "manageinstitutions|blogs" => "administration/institutions.html#institution-journals",
    "manageinstitutions|institutions" => "administration/institutions.html#overview",
    "manageinstitutions|institutions|institutionedit" => "administration/institutions.html#add-an-institution",
    "manageinstitutions|institutionadmins" => "administration/institutions.html#institution-administrators",
    "manageinstitutions|institutionfiles" => "administration/institutions.html#files",
    "manageinstitutions|institutionstaff" => "administration/institutions.html#institution-staff",
    "manageinstitutions|institutionusers" => "administration/institutions.html#members",
    "manageinstitutions|institutionviews" => "administration/institutions.html#institution-pages-and-collections",
    "manageinstitutions|pendingregistrations" => "administration/institutions.html#review-pending-registrations",
    "manageinstitutions|progressbar" => "administration/institutions.html#profile-completion",
    "manageinstitutions|share" => "administration/institutions.html#share-institution-pages-and-collections",
    "manageinstitutions|sitepages|institutionstaticpages" => "administration/institutions.html#institution-static-pages",
    "profileicons" => "content/profile_pictures.html",
    "profile|index" => "content/profile.html",
    "register" => "administration/institutions.html#self-register-for-an-internal-account",
    "reports" => "administration/reports.html",
    "reports|content|content" => "administration/reports.html#content-report",
    "reports|content|objectionable" => "administration/reports.html#new-in-mahara-18-10-objectionable-content-report",
    "reports|groups|groups" => "administration/reports.html#groups-report",
    "reports|information|comparisons" => "administration/reports.html#institution-comparison-report",
    "reports|information|information" => "administration/reports.html#overview-report",
    "reports|information|logins" => "administration/reports.html#logins-report",
    "reports|users|collaboration" => "administration/reports.html#collaboration-report",
    "reports|users|masquerading" => "administration/reports.html#masquerading-sessions-report",
    "reports|users|pageactivity" => "administration/reports.html#page-activity-report",
    "reports|users|useractivity" => "administration/reports.html#user-activity-report",
    "reports|users|userdetails" => "administration/reports.html#user-details-report",
    "reports|users|users" => "administration/reports.html#people-overview-report",
    "reports|users|accesslist" => "administration/reports.html#portfolio-access-report",
    "settings|account|preferences" => "account/account_settings.html",
    "settings|notifications" => "account/notification_settings.html",
    "settings|webservice" => "account/apps.html",
    "share|sharedbyme|accessurl" => "portfolio/page_editor.html#edit-access",
    "share|sharedbyme|editaccess" => "portfolio/share.html#edit-access-for-users-with-an-account",
    "share|sharedbyme|share" => "portfolio/share.html",
    "share|sharedbyme|urls" => "portfolio/share.html#edit-access-for-users-without-an-account",
    "share|sharedviews" => "portfolio/shared.html",
    "userdashboard" => "portfolio/pages.html#profile-page",
    "view" => "portfolio/pages.html#view-a-page",
    "view|profile" => "portfolio/pages.html#profile-page",
    "webservices|apps" => "administration/web_services.html#application-connections",
    "webservices|config" => "administration/web_services.html#configuration",
    "webservices|conections" => "administration/web_services.html#connection-manager",
    "webservices|oauthconfig" => "administration/web_services.html#registration-of-external-apps",
    "webservices|logs" => "administration/web_services.html#web-services-logs",
    "webservices|testclient" => "administration/web_services.html#web-services-test-client",
);
